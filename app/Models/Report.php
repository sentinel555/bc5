<?php

namespace App\Models;
use App\Models\Withdrawals as Withdrawals;
use \Illuminate\Database\Eloquent\Model as Eloquent;
use \Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Session;
use \Illuminate\Support\Facades\Auth;
use DateTime;



class Report extends Eloquent {

	protected $fillable = [];

public static function generate_string($length=6,$level=2){

   list($usec, $sec) = explode(' ', microtime());
   srand((float) $sec + ((float) $usec * 100000));

   $validchars[1] = "0123456789abcdfghjkmnpqrstvwxyz";
   $validchars[2] = "0123456789abcdfghjkmnpqrstvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
   $validchars[3] = "0123456789_abcdfghjkmnpqrstvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_";

   $string  = "";
   $counter   = 0;

   while ($counter < $length) {
     $actChar = substr($validchars[$level], rand(0, strlen($validchars[$level])-1), 1);

     // All character must be different
     if (!strstr($string, $actChar)) {
        $string .= $actChar;
        $counter++;
     }
   }

   return $string;

}

public static function calculate_years($end_year,$startYear,$endMonth,$startMonth,$endDay,$startDay) {
	
		$calc = null;

		$calc = $end_year - $startYear;	

		if($endMonth > $startMonth) {$calc++;}

		if ($endDay == $startDay && $endMonth == $startMonth) {$calc++;}
		if($endDay == 28 && $startDay == 29 && $startMonth == 2 && $endMonth == 2) {$calc++;}

	return ($calc);


		}

public static function reset_array($arr, $offset) {

		$cc = $offset;
		$temp = array();

		foreach($arr as $id => $output) {
			
			$temp[$cc] = $output;
			$cc++;

			}

		return $temp;

	}


 public static function count_segments($segment, $segment_match) {

                $seg_count = 0;

                for($l = 0; $l < count($segment_match); $l++) {

                        $sm = $segment_match[$l];

                        if(in_array(($segment), $sm)) { $seg_count = count($sm);  }             

                }

                return $seg_count;

        }


public static function date_overlap($s1, $e1, $s2, $e2) {

	$start_one = new DateTime($s1);
	$end_one = new DateTime($e1);
	$start_two = new DateTime($s2);
	$end_two = new DateTime($e2);

   if($start_one <= $end_two && $start_two <= $end_one) { //If the dates overlap
        return min($end_one,$end_two)->diff(max($start_two,$start_one))->days + 1; //return how many days overlap
   }

   return 0; //Return 0 if there is no overlap
}

public static function load_segments($bond_id, $user_id) { 

        $segments = array();
        $counter = 0;
	$commencement_date = null;

	$se = DB::table('segments')
		->join('bonds', 'bonds.id', '=', 'segments.bond_id')	
	        ->where('segments.user_id','=', $user_id)
	        ->where('segments.bond_id', '=', $bond_id)
	        ->where('segments.increment_id', '=', 0)
	        ->select('segments.segment_amount AS segment_amount', 'segments.id AS id', 'bonds.commencement_date AS commencement_date')
	        ->orderBy('segments.id', 'asc')
		->distinct()
		->get();

        if (count($se > 0)) {
     	   foreach ($se as $id => $output) {

                $segments[$counter]['segment_amount'] = $output->segment_amount;
                $segments[$counter]['segment_annual_allowance'] = ($output->segment_amount / 100.0) * 5.0;
                $segments[$counter]['year'] = 0;
		$commencement_date = preg_split('/[\/\.-]/', $output->commencement_date);
                $counter++;

                }

        }

	unset($se);
		
	$se = DB::table('segments')
		->join('increments', 'increments.id', '=', 'segments.increment_id')	
	        ->where('segments.user_id','=', $user_id)
	        ->where('segments.bond_id', '=', $bond_id)
	        ->where('segments.increment_id', '>', 0)
	        ->select('segments.segment_amount AS segment_amount', 'segments.id AS id', 'segments.increment_id AS increment_id', 'increments.increment_commencement_date AS increment_commencement_date', 'increments.increment_amount AS increment_amount')
	        ->orderBy('segments.id', 'asc')
	        ->orderBy('increments.increment_commencement_date', 'asc')
		->distinct()
		->get();

	if (count($se > 0)) {
     	   foreach ($se as $id => $output) {
		
		$segments[$counter]['segment_amount'] = $output->segment_amount;
                $segments[$counter]['segment_annual_allowance'] = ($output->segment_amount / 100.0) * 5.0;
		$increment_commencement_date = preg_split('/[\/\.-]/', $output->increment_commencement_date);
                $segments[$counter]['year'] = Withdrawals::calculate_years($increment_commencement_date[0], $commencement_date[0], $increment_commencement_date[1], $commencement_date[1], $increment_commencement_date[2], $commencement_date[2]) - 1;
                $counter++;

                }

        }

    	return $segments;

    }


public static function load_segment_blocks($bond_id, $user_id) {


        $blocks = array();
        $column = array();
        $counter = 0;
	
	$wd = DB::table('withdrawals')
	        ->where('withdrawals.user_id','=', $user_id)
	        ->where('withdrawals.bond_id', '=', $bond_id)
	        ->select('id', 'year_ending', 'withdrawal_amount', 'withdrawal_percentage', 'segment_start', 'segment_end')
	        ->orderBy('year_ending', 'asc')
	        ->orderBy('segment_start', 'asc')
		->get();
		
		if (count($wd > 0)) {
     	  	 foreach ($wd as $id => $output) {

                $blocks[$counter]['segment_start'] = $output->segment_start;
                $blocks[$counter]['segment_end'] = $output->segment_end;
                $counter++;

               	 }


		foreach($blocks as $sortarray) {
			$column[] = $sortarray['segment_start'];
		}
		
		array_multisort($column, SORT_ASC, $blocks);
		
		foreach ($blocks as $key => $value) {
		    $blocks[$key] = serialize($value);
		}

		$blocks = array_unique($blocks);

		foreach ($blocks as $key => $value) {
		    $blocks[$key] = unserialize($value);
		}
		
		$blocks = array_values($blocks);
	

        	}

		if(empty($blocks)) {

			$seg = DB::table('segments')->select(DB::raw('count(id) AS segments'))->where('bond_id', $bond_id)->where('user_id', $user_id)->get();


			$blocks[0]['segment_start'] = 1;
			$blocks[0]['segment_end'] = $seg[0]->segments;
			$blocks[0]['start'] = true;


		}

               return (isset($blocks) && is_array($blocks)) ? $blocks: array();

}


public static function check_segments($bond_id, $user_id) {


	$segments = array();
	$counter = 0;
	$commencement_date = null;

	$se = DB::table('segments')
		->join('bonds', 'bonds.id', '=', 'segments.bond_id')	
	        ->where('segments.user_id','=', $user_id)
	        ->where('segments.bond_id', '=', $bond_id)
	        ->where('segments.increment_id', '=', 0)
	        ->select('bonds.commencement_date AS commencement_date', DB::raw('count(segments.segment_amount) as segments'))
	        ->groupBy('bonds.commencement_date')
		->get();

	if (count($se > 0)) {
     	   foreach ($se as $id => $output) {

                $segments[$counter]['segments'] = $output->segments;
                $segments[$counter]['year'] = 0;
		$commencement_date = preg_split('/[\/\.-]/', $output->commencement_date);
                $counter++;

                }
        }

	unset($se);

	$se = DB::table('segments')
		->join('increments', 'increments.id', '=', 'segments.increment_id')	
	        ->where('segments.user_id','=', $user_id)
	        ->where('segments.bond_id', '=', $bond_id)
	        ->where('segments.increment_id', '>', 0)
	        ->select('increments.increment_commencement_date AS increment_commencement_date', DB::raw('count(segments.segment_amount) as segments'))
	        ->orderBy('segments.id', 'asc')
	        ->orderBy('increments.increment_commencement_date', 'asc')
		->distinct()
		->get();

	if(count($se > 0)) {	
     	   foreach ($se as $id => $output) {
		   if(!empty($output->increment_commencement_date) && $output->increment_commencement_date != '0000-00-00') {

		$increment_commencement_date = preg_split('/[\/\.-]/', $output->increment_commencement_date);
                $segments[$counter]['segments'] = $output->segments;
		$segments[$counter]['year'] = Withdrawals::calculate_years($increment_commencement_date[0], $commencement_date[0], $increment_commencement_date[1], $commencement_date[1], $increment_commencement_date[2], $commencement_date[2]) - 1;

                $counter++;

			   }
                }
	}

	$year = array();

	for($i = 0; $i < count($segments); $i++) {

		$year[] = $segments[$i]['year'];

	}

	array_multisort($year, SORT_ASC, $segments);

	
	for($i = 0; $i < count($segments); $i++) {

		if($i == 0) { 
				$segments[$i]['segment_start'] = 1;
				$segments[$i]['segment_end'] = $segments[$i]['segments'];
			} else {
				$segments[$i]['segment_start'] = $segments[$i - 1]['segment_end'] + 1;
				$segments[$i]['segment_end'] = $segments[$i - 1]['segments'] + $segments[$i]['segments'];
			}
	}
		
	return $segments;	

}


public static function calculate_allowance($total_years, $allowance, $increment, $encashment)  {

		$rows = array();
		$inc = 0;
		$enc = 0;

		for($i=0; $i < $total_years; $i++) {


			//////////////////////////////////////////////
			//
			//	Loop through increments and encashments
			//	If found, adjust the annual allowance
			//
			/////////////////////////////////////////////


			for($z=0; $z < count($increment); $z++) {

				if(isset($increment[$z]['increment_annual_allowance'])) {

				if($increment[$z]['year'] == $i) {
	
					$inc += $increment[$z]['increment_annual_allowance'];
	
					}

				if($increment[$z]['end_year'] + 1 == $i) {
	
					$inc -= $increment[$z]['increment_annual_allowance'];
	
					}
			}
			}


			

			for($z=0; $z < count($encashment); $z++) {

				if(isset($encashment[$z]['segments_annual_allowance'])) {

				if($encashment[$z]['year'] + 1 == $i) {
	
					$enc += $encashment[$z]['segments_annual_allowance'];
	
					}

				}

			}


		if($i > 19) {

			$rows[$i] = $inc - $enc;

			if($rows[$i] < 0) {$rows[$i] = 0;}

		} else {

			$rows[$i] = ($allowance + $inc) - $enc;

			if($rows[$i] < 0) {$rows[$i] = 0;}

		}
		}

		return $rows;

	}


public static function calculate_allowance_extended($total_years, $segments, $segment_break_points, $segment_match, $segment_start, $segment_end, $increment, $encashment)  {

		$inc = 0;
		$enc = 0;
		$counter = 0;
		$rows = array();

	for($x = 0; $x < count($segment_break_points); $x++) {
		if(!empty($segment_match[$x])) {

			if($x > 0) {$counter = $segment_break_points[$x]['year'];}

		for($i = $segment_break_points[$x]['year']; $i < $total_years; $i++) {

			if(!isset($rows[$counter])) { $rows[$counter] = 0; }

			$q = count($segment_match[$x]) - 1;

			for($h = $segment_match[$x][0]; $h <= $segment_match[$x][$q]; $h++) {

			//////////////////////////////////////////////
			//
			//	Loop through increments and encashments
			//	If found, adjust the annual allowance
			//
			/////////////////////////////////////////////


			for($z=0; $z < count($increment); $z++) {

				if(isset($increment[$z]['increment_annual_allowance'])) {

				if($increment[$z]['year'] == $i && $increment[$z]['increment_segments'] == 0) {
	
					$inc += $increment[$z]['increment_annual_allowance'];
	
					}

				if($increment[$z]['end_year'] + 1 == $i && $increment[$z]['increment_segments'] == 0) {
	
					$inc -= $increment[$z]['increment_annual_allowance'];
	
					}
			}
			}

			

			for($z=0; $z < count($encashment); $z++) {

				if(isset($encashment[$z]['segments_annual_allowance'])) {

				if($encashment[$z]['year'] + 1 == $i) {
	
					$enc += $encashment[$z]['segments_annual_allowance'];
	
					}

				}

			}


		if($i > 19) {

			$rows[$counter] = $inc - $enc;

			if($rows[$counter] < 0) {$rows[$counter] = 0;}

		}	       
		else {

			$rows[$counter] += ($segments[($h - 1)]['segment_annual_allowance'] + $inc) - $enc;

				 
			}
		}

			$counter++;
		}

	}
	}
		return $rows;

	}

public static function calculate_cumulative_allowance($segment, $withdrawal, $policy_loans, $segment_break_points, $start_year, $increment, $initialSegments, $encashment_details,  $total_years, $allowance)  {


                $rows = array();

		$segments = 0;

		$year  = 0;

              	  for($i=0; $i < $total_years; $i++) {

                        //////////////////////////////////////////////
                        //
			//      Check to see if any increments have increased
			//	the number of segments
                        //
                        /////////////////////////////////////////////


			for($j=0; $j < count($segment_break_points); $j++) {

				if($segment_break_points[$j]['year'] == $i) {

					$segments += $segment_break_points[$j]['segments'];
					$year = $segment_break_points[$j]['year'];

				}


			}	



			for($j=0; $j < count($encashment_details); $j++) {

				if(isset($encashment_details[$j]['year'])) {

				if($encashment_details[$j]['year'] == $i) {

					$segments -= $encashment_details[$j]['segments_encashed'];

				}

				}

			}	


                        //////////////////////////////////////////////
                        //
                        //      Main calculation
                        //
                        /////////////////////////////////////////////


			$pL = 0;


                        if($i == 0) {

                                $rows[$i] = 0;

			} 
			
			
			else {

				if($policy_loans[$i - 1] > 0) {$pL = $policy_loans[$i - 1] / $segments;}

				$rows[$i] = $rows[$i-1] + ($allowance - (($withdrawal[$i-1]['withdrawal_amount'] / $segments) + $pL));

			}



			if ($start_year > $i) {

                                $rows[$i] = 0;

			}

                                         if($rows[$i] < 0) {$rows[$i] = 0;}



		}


                return $rows;

                }


public static function calculate_cumulative_allowance_extended($segment, $withdrawal, $policy_loans, $segment_break_points, $segment_match, $match,  $segments, $encashment_details,  $total_years, $segment_start, $segment_end, $temp, $seg_count)  {

                $rows =  array();

		$start_year = $segments[$segment]['year'];

		if($start_year > 0) { $withdrawal = Report::reset_array($withdrawal, $start_year); }

		$remove = 0; //remove first array segment from rows if it crosses a segment break point - it is only there for temporary purposes...

		for($i = $start_year; $i < $total_years; $i++) {


                        //////////////////////////////////////////////
                        //
                        //      Main calculation
                        //
                        /////////////////////////////////////////////


			$pL = 0;


                        if($i == $start_year) {

				$rows[$i] = 0;

		
			} 			
		  
				if($i == $start_year && $start_year > 0) {


					$rows[$i - 1] = 0;
					$withdrawal[$i - 1]['withdrawal_amount'] = 0;
				}

			if($i > $start_year) {

				if($policy_loans[$i - 1] > 0) {$pL = $policy_loans[$i - 1] / $seg_count;}

					if(isset($withdrawal[$i - 1]['withdrawal_percentage']) && $withdrawal[$i- 1]['withdrawal_percentage'] > 0) {

					$rows[$i] = $rows[$i - 1] + (($segments[$segment]['segment_amount'] * 0.05) - (($segments[$segment]['segment_amount'] / 100) * $withdrawal[$i - 1]['withdrawal_percentage'])) + $pL;

				} else {

			if($start_year > 0) {echo $i. ' '.$segment.' '. $start_year.' '.$total_years.' ';	echo $rows[$i - 1]; var_dump($segments);echo '<br><br>'; print_r($withdrawal);echo '<br><br>'; print_r($seg_count);exit; }

				$rows[$i] = $rows[$i - 1] + ($segments[$segment]['segment_annual_allowance'] - ($withdrawal[$i - 1]['withdrawal_amount'] / $seg_count[$i-1]) + $pL);


			}

		}

		//if($start_year > 0 && isset($rows[$start_year - 1])) { unset($rows[$start_year - 1]); }
			if($rows[$i] < 0) {$rows[$i] = 0;}
          	}

                return $rows;

	}


public static function calculate_excess($segment, $withdrawal, $policy_loans, $segment_break_points, $start_year, $increment, $initial_segments, $encashment_details, $cumulative_allowance, $total_years, $allowance, $final_year)  {

		$rows = array();
		$year  = 0;

		$segments = 0;

                for($i=0; $i < $total_years; $i++) {


                        //////////////////////////////////////////////
                        //
			//      Check to see if any increments have increased
			//	the number of segments
                        //
                        /////////////////////////////////////////////


			for($j=0; $j < count($segment_break_points); $j++) {

				if($segment_break_points[$j]['year'] == $i) {


					$segments += $segment_break_points[$j]['segments'];
					$year = $segment_break_points[$j]['year'];

				}


			}	



			for($j=0; $j < count($encashment_details); $j++) {

				if(isset($encashment_details[$j]['year'])) {
				if($encashment_details[$j]['year'] == $i) {


					$segments -= $encashment_details[$j]['segments_encashed'];

				}
			}

			}	


	if($segment <= $initial_segments) {
	for($z=0; $z < count($increment); $z++) {

		if(isset($increment[$z]['increment_segments'])) {
			if($increment[$z]['increment_segments'] == 0 && $increment[$z]['year'] == $i) {
	
					$allowance += ($increment[$z]['segments_annual_allowance'] / $initial_segments);
	
				}
		}


		if(isset($increment[$z]['increment_segments'])) {

				if($increment[$z]['increment_segments'] == 0 && $increment[$z]['end_year'] == $i) {
	
					$allowance -= ($increment[$z]['segments_annual_allowance'] / $initial_segments);
	
					}
				}
				
				}
			}


			$pL = $policy_loans[$i];

			if($policy_loans[$i] > 0) {$pL = $policy_loans[$i] / $segments;}



			if($i == 0) {
			
				if((($withdrawal[$i]['withdrawal_amount'] / $segments) + $pL) > $allowance){
					
					$rows[$i] = ((($withdrawal[$i]['withdrawal_amount'] / $segments) + $pL) - $allowance);
				} else {

					$rows[$i] = 0;
				}

			} else {

				
					if((($withdrawal[$i]['withdrawal_amount'] / $segments) + $pL) > ($allowance + $cumulative_allowance[$i])) {
						$rows[$i] = (($withdrawal[$i]['withdrawal_amount'] / $segments) + $pL) - ($allowance + $cumulative_allowance[$i]);

					} else {

						$rows[$i] = 0;

					}

			}

			if ($start_year > $i) {

                                $rows[$i] = 0;

			}


		if ($final_year == 2 && $i == $total_years - 2) { $rows[$i] = 0;}
                elseif ($final_year == 2 && $i == $total_years - 1) { $rows[$i] = 0;}
                elseif ($final_year == 1 && $i == $total_years - 1) { $rows[$i] = 0;}



	}


		return $rows;


	}


public static function calculate_excess_extended($segment, $segments, $withdrawal, $policy_loans, $segment_break_points, $start_year,  $encashment_details,  $cumulative_allowance, $total_years, $allowance, $final_year, $seg_count, $segment_start, $segment_end)  {

		$rows = array();
		$year  = 0;

		if($start_year > 0) { $withdrawal = Report::reset_array($withdrawal, $start_year); }
                for($i = $start_year; $i < $total_years; $i++) {


                        //////////////////////////////////////////////
                        //
			//      Check to see if any increments have increased
			//	the number of segments
                        //
                        /////////////////////////////////////////////


//			for($j=0; $j < count($segment_break_points); $j++) {
//
//				if($segment_break_points[$j]['year'] == $i) {
//
//					$year = $segment_break_points[$j]['year'];
//
//				}
//
//
//			}	



			for($j=0; $j < count($encashment_details); $j++) {

				if(isset($encashment_details[$j]['year'])) {

				if($encashment_details[$j]['year'] == $i) {

					$segments -= $encashment_details[$j]['segments_encashed'];
				}
				}

			}	


			$pL = $policy_loans[$i];

			if($policy_loans[$i] > 0) {$pL = $policy_loans[$i] / $seg_count[$i];}



			if($i == $start_year) {

			if(isset($withdrawal[$i]['withdrawal_percentage']) && $withdrawal[$i]['withdrawal_percentage'] > 0) {
				if((($segments[$segment]['segment_amount'] / 100) * $withdrawal[$i]['withdrawal_percentage']) > $allowance){

				$rows[$i] = (((($segments[$segment]['segment_amount'] / 100) * $withdrawal[$i]['withdrawal_percentage']) + $pL) - $allowance);
				} else {

					$rows[$i] = 0;
				}


				} else {

				if((($withdrawal[$i]['withdrawal_amount'] / $seg_count[$i]) + $pL) > $allowance){
					
					$rows[$i] = ((($withdrawal[$i]['withdrawal_amount'] / $seg_count[$i]) + $pL) - $allowance);
				} else {

					$rows[$i] = 0;
				}
			}
			} else {

				if(isset($withdrawal[$i]['withdrawal_percentage']) && $withdrawal[$i]['withdrawal_percentage'] > 0) {
				if((($segments[$segment]['segment_amount'] / 100) * $withdrawal[$i]['withdrawal_percentage']) + $pL > ($allowance + $cumulative_allowance[$i])){

				$rows[$i] = ((($segments[$segment]['segment_amount'] / 100) * $withdrawal[$i]['withdrawal_percentage']) - $allowance);
				} else {

					$rows[$i] = 0;
				}


				} else {		
					if((($withdrawal[$i]['withdrawal_amount'] / $seg_count[$i]) + $pL) > ($allowance + $cumulative_allowance[$i])) {
						$rows[$i] = (($withdrawal[$i]['withdrawal_amount'] / $seg_count[$i]) + $pL) - ($allowance + $cumulative_allowance[$i]);

					} else {

						$rows[$i] = 0;

					}

				}
			}
		

			if ($start_year > $i) {

                                $rows[$i] = 0;

			}


		if ($final_year == 2 && $i == $total_years - 2) { $rows[$i] = 0;}
                elseif ($final_year == 2 && $i == $total_years - 1) { $rows[$i] = 0;}
                elseif ($final_year == 1 && $i == $total_years - 1) { $rows[$i] = 0;}



	}


		return $rows;


	}


public static function load_withdrawal_details($bond_id, $user_id) {

	    $counter = 0;
	    $withdrawals = array();

	$wd = DB::table('withdrawals')
	        ->where('user_id','=', $user_id)
	        ->where('bond_id', '=', $bond_id)
	        ->select('id', 'year_ending', 'withdrawal_amount', 'withdrawal_percentage')
	        ->orderBy('year_ending', 'asc')
		->distinct()
		->get();

        if (count($wd > 0)) {
     	   foreach ($wd as $id => $output) {

                $withdrawals[$counter]['year_ending'] = $output->year_ending;
                $withdrawals[$counter]['withdrawal_amount'] = round($output->withdrawal_amount, 2);
                $withdrawals[$counter]['withdrawal_percentage'] = round($output->withdrawal_percentage, 2);
                $withdrawals[$counter]['id'] = $output->id;
                $counter++;

                }

        }


    return $withdrawals;

    }


	public static function load_withdrawal_details_extended($bond_id, $user_id, $segment_start, $segment_end) {

	$counter = 0;
	$withdrawals = array();

	$wd = DB::table('withdrawals')
	        ->where('user_id','=', $user_id)
	        ->where('bond_id', '=', $bond_id)
	        ->where('segment_start', '=', $segment_start)
	        ->where('segment_end', '=', $segment_end)
	        ->select('id', 'year_ending', 'withdrawal_amount', 'withdrawal_percentage', 'segment_start', 'segment_end')
	        ->orderBy('segment_start', 'asc')
	        ->orderBy('year_ending', 'asc')
		->distinct()
		->get();

         if (count($wd > 0)) {
     	   foreach ($wd as $id => $output) {

                $withdrawals[$counter]['year_ending'] = $output->year_ending;
                $withdrawals[$counter]['withdrawal_amount'] = round($output->withdrawal_amount, 2);
                $withdrawals[$counter]['withdrawal_percentage'] = round($output->withdrawal_percentage, 2);
                $withdrawals[$counter]['segment_start'] = $output->segment_start;
                $withdrawals[$counter]['segment_end'] = $output->segment_end;
                $counter++;

                }

        }


    return $withdrawals;

    }


public static function total_withdrawals($bond_id, $user_id)
    {

        $counter = 0;
	$total = 0;

	$wdl = DB::table('withdrawals')
	->where('bond_id', '=', $bond_id)
	->where('user_id', '=', $user_id)
	->select('withdrawal_amount')
	->orderBy('year_ending', 'asc')
	->get();

        if (count($wdl) > 0) {
        foreach ($wdl as $id => $row ) {

                $total += $row->withdrawal_amount;
                }

        } else { $total = 0; }


    return $total;

    }


public static function load_bonds($bond_id, $user_id) {

		$bonds = DB::table('bonds')
		->join('ownerships', 'bonds.id', '=', 'ownerships.bond_id')
		->join('policyholders', 'policyholders.id', '=', 'ownerships.policyholder_id')
		->where('policyholders.user_id', '=', $user_id)
		->where('bonds.user_id', '=', $user_id)
		->where('bonds.id', '=', $bond_id)
		->select('policyholders.first_name AS first_name', 'policyholders.surname AS surname', 'policyholders.id AS policyholder_id', 'bonds.id AS bond_id', 'bonds.insurer AS insurer', 'bonds.policy_number AS policy_number', 'bonds.mode AS mode', 'bonds.investment AS investment', 'bonds.encashment_proceeds AS encashment_proceeds', 'bonds.auto_update AS auto_update', 'bonds.commencement_date AS commencement_date', 'bonds.encashment_date AS encashment_date', 'bonds.segments AS segments', 'bonds.offshore_bond AS offshore_bond' )
		->orderBy('policyholders.surname', 'asc')
		->distinct()
		->get();
		return($bonds);

	}


//public static function load_increments($bond_id, $user_id) {
//	
//	$increments = array();	
//	
//	$ims = DB::table('increments')
//		->where('increments.user_id', '=', $user_id)
//		->where('increments.bond_id', '=', $bond_id)
//		->select('increments.id AS id', 'increments.bond_id AS bond_id', 'increments.increment_amount AS increment_amount', 'increments.increment_commencement_date AS increment_commencement_date', 'increments.increment_segments AS segments')
//		->orderBy('increments.increment_commencement_date')
//		->distinct()
//		->get();
//
//	$counter = 0;
//
//	foreach ($ims as $id => $output) {
//
//		$increments[$counter]['id'] = $output->id;
//		$increments[$counter]['bond_id'] = $output->bond_id;
//		$increments[$counter]['increment_amount'] = $output->increment_amount;
//		$increments[$counter]['increment_segments'] = $output->segments;
//		
//		if($increments[$counter]['increment_segments'] == 0) {$increments[$counter]['increment_segments'] = "Across all segments";}
//
//
//		$increments[$counter]['increment_commencement_date'] = $output->increment_commencement_date;
//		$counter++;
//
//		}
//
//
//            
//    return $increments;
//
//    }


public static function load_increment_details($bond_id, $user_id, $cDay, $cMonth, $cYear) {

        $rows = array();
        $counter = 0;

	$inc = DB::table('increments')
        ->where('user_id', $user_id)
        ->where('bond_id', $bond_id)
	->get();

        if (count($inc) > 0) {
        foreach ($inc as $id => $row ) {

                $rows[$counter]['increment_id'] = $row->id;
                $rows[$counter]['increment_amount'] = $row->increment_amount;
                $rows[$counter]['bond_id'] = $row->bond_id;
                $rows[$counter]['increment_annual_allowance'] = ($row->increment_amount / 100) * 5.0;
                $rows[$counter]['increment_segments'] = $row->increment_segments;


 		if($rows[$counter]['increment_segments'] > 0) {

                        $rows[$counter]['segments_annual_allowance'] = (($row->increment_amount / $row->increment_segments) / 100.0) * 5.0;

                } else {

                        $rows[$counter]['segments_annual_allowance'] = $rows[$counter]['increment_annual_allowance'];

                }

                $incrementCommencementDate1  = preg_split('/[\/\.-]/',$row->increment_commencement_date);

		$rows[$counter]['increment_commencement_date'] = $incrementCommencementDate1[2]."/".$incrementCommencementDate1[1]."/".$incrementCommencementDate1[0];

		$year =	$incrementCommencementDate1[0] - $cYear;

		if($cMonth > $incrementCommencementDate1[1]) { $year--; }

		if($cMonth == $incrementCommencementDate1[1] && $cDay > $incrementCommencementDate1[2]) { $year--; }


		$rows[$counter]['year'] = $year;
                $rows[$counter]['cmonth'] = $cMonth;
                $rows[$counter]['end_year'] = $year + 20;

                $counter++;
                }

        }


    return $rows;

    }

public static function load_encashment_details($bond_id, $user_id, $cDay, $cMonth, $cYear) {

	$counter = 0;
	$encashments = array();

	$en = DB::table('encashments')
		->join('bonds', 'bonds.id', '=', 'encashments.bond_id')	
	        ->where('encashments.user_id','=', $user_id)
	        ->where('encashments.bond_id', '=', $bond_id)
	        ->select('bonds.investment AS investment', 'bonds.segments AS segments', 'encashments.segment_start AS segment_start', 'encashments.segment_end AS segment_end', 'encashments.segments_proceeds AS segments_proceeds', 'encashments.segments_encashment_date AS segments_encashment_date', 'encashments.id AS id')
	        ->orderBy('encashments.segments_encashment_date', 'asc')
		->distinct()
		->get();

	if (count($en > 0)) {
     	   foreach ($en as $id => $output) {

                $encashments[$counter]['id'] = $output->id;
                $encashments[$counter]['bond_id'] = $bond_id;
                $encashments[$counter]['segments_encashed_initial_value'] = ($output->investment / $output->segments) * count(range($output->segment_start, $output->segment_end));
                //$encashments[$counter]['seg_value'] = ($output->investment / $output->segments) * count(range($output->segment_start, $output->segment_end));
                $encashments[$counter]['segments_annual_allowance'] = ($encashments[$counter]['segments_encashed_initial_value'] / 100.0) * 5.0;
		$encashments[$counter]['segments_encashed'] = count(range($output->segment_start, $output->segment_end));

                $segments_encashment_date1  = preg_split('/[\/\.-]/',$output->segments_encashment_date);
                $encashments[$counter]['segments_encashment_date'] = $segments_encashment_date1[2]."/".$segments_encashment_date1[1]."/".$segments_encashment_date1[0];

		$year =	$segments_encashment_date1[0] - $cYear;

		if($cMonth > $segments_encashment_date1[1]) { $year--; }

		if($cMonth == $segments_encashment_date1[1] && $cDay > $segments_encashment_date1[2]) { $year--; }


                $encashments[$counter]['year'] = $year;

                $counter++;

                }

        }

    return ($encashments);

    }


public static function load_policy_loans($bond_id, $user_id, $cDay, $cMonth, $cYear) {

        $policy_loans = array();
        $counter = 0;

	$pl = DB::table('policy_loans')
	        ->where('policy_loans.user_id','=', $user_id)
	        ->where('policy_loans.bond_id', '=', $bond_id)
	        ->select('id', 'policy_loan', 'capital_repayment', 'policy_loan_date')
	        ->orderBy('policy_loan_date', 'asc')
		->distinct()
		->get();

	if (count($pl > 0)) {
     	   foreach ($pl as $id => $output) {

                $policy_loans[$counter]['id'] = $output->id;
                $policy_loans[$counter]['bond_id'] = $bond_id;
                $policy_loans[$counter]['policy_loan'] = $output->policy_loan;
                $policy_loans[$counter]['capital_repayment'] = $output->capital_repayment;

                $policy_loan_date1  = preg_split('/[\/\.-]/',$output->policy_loan_date);
		$policy_loan_date = $policy_loan_date1[2]."/".$policy_loan_date1[1]."/".$policy_loan_date1[0];

		$policy_loans[$counter]['policy_loan_date'] = $policy_loan_date;

		$year =	$policy_loan_date1[0] - $cYear;

		if($cMonth > $policy_loan_date1[1]) { $year--; }

		if($cMonth == $policy_loan_date1[1] && $cDay > $policy_loan_date1[2]) { $year--; }
                $policy_loans[$counter]['year'] = $year + 1;

		$policy_loans[$counter]['year'] = $year;
                $policy_loans[$counter]['cmonth'] = $cMonth;
                $policy_loans[$counter]['end_year'] = $year + 20;


                $counter++;

                }

        }

    return $policy_loans;

    }


public static function load_policy_loan_years($policy_loans, $total_years, $user_id) {

        $rows = array();
        $counter = 0;
	$rows = array_fill(0, ($total_years), 0);

	for($a=0; $a < $total_years; $a++) {

		for($b=0; $b < count($policy_loans); $b++) {

			if($policy_loans[$b]['year'] == $a) {

				$rows[$a] += $policy_loans[$b]['policy_loan'];

			}
		}
	}

    return $rows;

    }




public static function load_ownerships($bond_id, $user_id) {

        $rows = array();
	$sorted_rows = array();
	$total_segments = 0;
        $counter = 0;
        $counter3 = 0;

	$own = DB::table('ownerships')
        ->join('policyholders','policyholders.id', '=', 'ownerships.policyholder_id')
        ->where('policyholders.user_id', $user_id)
        ->where('ownerships.user_id', $user_id)
        ->where('ownerships.bond_id', $bond_id)
        ->orderBy('ownerships.policyholder_id', 'asc')
	->get();

        if (count($own) > 0) {
        foreach ($own as $id => $row ) {

                $rows[$counter]['percentage_split'] = $row->percentage_split;
                $rows[$counter]['policyholder_id'] = $row->policyholder_id;
                $rows[$counter]['segment_start'] = $row->segment_start; // remove afterwards
                $rows[$counter]['segment_end'] = $row->segment_end; // remove afterwards
                $rows[$counter]['segments'] = ($rows[$counter]['segment_end'] - $rows[$counter]['segment_start']) + 1; // remove afterwards
		$total_segments += ($rows[$counter]['segments'] / 100) * $row->percentage_split; // remove afterwards
                $rows[$counter]['first_name'] = $row->first_name;
                $rows[$counter]['surname'] = $row->surname;
                $rows[$counter]['assignment_date'] = $row->assignment_date;
                $rows[$counter]['security_debt_date'] = $row->security_debt_date;
                $rows[$counter]['trustee_investment'] = $row->trustee_investment;

                $counter++;
                }

	for($a = 0; $a <= ($counter - 1); $a++) {

		$rows[$a]['percentage'] = ((($rows[$a]['segments'] / $total_segments) * 100) / 100) * $rows[$a]['percentage_split'];


		}
		

	for($a = 0; $a <= ($counter - 1); $a++) {


			if($a == 0) {

				$sorted_rows[$counter3]['percentage_split'] = $rows[$a]['percentage_split'];
				$sorted_rows[$counter3]['policyholder_id'] = $rows[$a]['policyholder_id'];
				$sorted_rows[$counter3]['first_name'] = $rows[$a]['first_name'];
				$sorted_rows[$counter3]['surname'] = $rows[$a]['surname'];
				$sorted_rows[$counter3]['percentage'] = $rows[$a]['percentage'];	
				$sorted_rows[$counter3]['assignment_date'] = $rows[$a]['assignment_date'];	
				$sorted_rows[$counter3]['security_debt_date'] = $rows[$a]['security_debt_date'];	
				$sorted_rows[$counter3]['trustee_investment'] = $rows[$a]['trustee_investment'];	
			}	


				
			if($a > 0) { 
				
				if($sorted_rows[$counter3]['policyholder_id'] == $rows[$a]['policyholder_id']) {
			$sorted_rows[$counter3]['percentage'] += $rows[$a]['percentage'];

			    } else {

				$counter3++;
				
				$sorted_rows[$counter3]['percentage_split'] = $rows[$a]['percentage_split'];
				$sorted_rows[$counter3]['policyholder_id'] = $rows[$a]['policyholder_id'];
				$sorted_rows[$counter3]['first_name'] = $rows[$a]['first_name'];
				$sorted_rows[$counter3]['surname'] = $rows[$a]['surname'];
				$sorted_rows[$counter3]['percentage'] = $rows[$a]['percentage'];  
				$sorted_rows[$counter3]['assignment_date'] = $rows[$a]['assignment_date'];	
				$sorted_rows[$counter3]['security_debt_date'] = $rows[$a]['security_debt_date'];	
				$sorted_rows[$counter3]['trustee_investment'] = $rows[$a]['trustee_investment'];	

				}
			}
		}

   	}

    return $sorted_rows;
  }



public static function array_search_values( $m_needle, $a_haystack, $b_strict = false){
    return array_intersect_key( $a_haystack, array_flip( array_keys( $a_haystack, $m_needle, $b_strict)));
}

public static function find_bond_type($bond, $increments, $ownerships) {

	$type  = 0;

	$start_date = new DateTime($bond->commencement_date);

	$pre_2013 = new DateTime('2013-04-05');

	$increment_post_2013  = 0;
	$assignment_post_2013 = 0;
	$security_post_2013 = 0;

	if($bond->offshore_bond == 0 && $bond->commencement_date <= $pre_2013) { $type = 0; }
	if($bond->offshore_bond == 0 && $bond->commencement_date > $pre_2013) { $type = 1; }
	if($bond->offshore_bond == 1 && $bond->commencement_date <= $pre_2013) { $type = 2; }
	if($bond->offshore_bond == 1 && $bond->commencement_date > $pre_2013) { $type = 3; }



	for($z=0; $z < count($increments); $z++) {

		$increment_date = DateTime::createFromFormat('d/m/Y', $increments[$z]['increment_commencement_date']);
		
		if($increment_date > $pre_2013) { $increment_post_2013 = 1; }

	}

	for($z=0; $z < count($ownerships); $z++) {
	
	 if($ownerships[$z]['assignment_date'] != '0000-00-00') {

		$assignment_date = DateTime::createFromFormat('Y-m-d', $ownerships[$z]['assignment_date']);
		if($assignment_date > $pre_2013) { $assignment_post_2013 = 1; }

		}
	}

	for($z=0; $z < count($ownerships); $z++) {

	 if($ownerships[$z]['security_debt_date'] != '0000-00-00') {

		$security_debt_date = DateTime::createFromFormat('Y-m-d', $ownerships[$z]['security_debt_date']);
		
		if($security_debt_date > $pre_2013) { $security_post_2013 = 1; }

		}
	}

	if($type == 0 && ($increment_post_2013 == 1 || $assignment_post_2013 == 1 || $security_post_2013 == 1)) { $type = 1; }

	if($type == 2 && ($increment_post_2013 == 1 || $assignment_post_2013 == 1 || $security_post_2013 == 1)) { $type = 3; }

	return($type);
}


public static function find_bond_type_cdr($bond, $increments, $ownerships, $excess) {

	$commencement = 0;
	$increment_post_2004 = 0;
	$assignment_post_2004 = 0;
	$security_post_2004 = 0;

	$start_date = new DateTime($bond->commencement_date);
	$pre_2004 = new DateTime('2004-03-03');

	if($start_date >= $pre_2004) { $commencement = 1; }

	if($start_date < $pre_2004) {

		for($z=0; $z < count($increments); $z++) {

		$increment_date = DateTime::createFromFormat('d/m/Y', $increments[$z]['increment_commencement_date']);
		
		if($increment_date > $pre_2004) { $increment_post_2004 = 1; }

		}

		for($z=0; $z < count($ownerships); $z++) {
	
			 if($ownerships[$z]['assignment_date'] != '0000-00-00') {

			$assignment_date = DateTime::createFromFormat('Y-m-d', $ownerships[$z]['assignment_date']);
		
			if($assignment_date > $pre_2004) { $assignment_post_2004 = 1; }

		}
	}

	for($z=0; $z < count($ownerships); $z++) {

	 if($ownerships[$z]['security_debt_date'] != '0000-00-00') {

		$security_debt_date = DateTime::createFromFormat('Y-m-d', $ownerships[$z]['security_debt_date']);
		
		if($security_debt_date > $pre_2004) { $security_post_2004 = 1; }

		}
		}

	if($increment_post_2004 ==  1 || $assignment_post_2004 ==  1 || $security_post_2004 == 1) { $commencement = 1; }

	}

	
	$last_excess = new DateTime();
	$assignment_date = new DateTime();

	if($commencement == 1) {
		

		for($z = 0; $z < count($excess); $z++) {

		

			if($excess[$z] > 0) { $last_excess = clone $start_date; $last_excess->modify('+'.$z.' years');  }
		

			for($y = 0; $y < count($ownerships); $y++) {


			if($ownerships[$y]['assignment_date'] != '0000-00-00') {

				$assignment_date = DateTime::createFromFormat('Y-m-d', $ownerships[$y]['assignment_date']); 
				if($assignment_date > $last_excess) { $commencement = 3; } elseif($assignment_date <= $last_excess) { $commencement = 1; } 
		
				}
		
			}
		}

	}

	return($commencement);
}


public static function calculate_time_apportionment_factors($pid, $percentage_split, $bond_type, $bond, $foreign_days, $material_interest_period, $uk_days, $years) {

	$tar = array();

	if($bond_type == 2) {

		$tar['chargeable_gain_factor'] =  $uk_days / $material_interest_period;	
		$tar['top_slicing_factor'] =  (floor($material_interest_period / 365.25)) - (floor($foreign_days / 365.25));


	}	else   {

		$tar['chargeable_gain_factor'] =  $foreign_days / $material_interest_period;	
		$tar['top_slicing_factor'][$pid] =   (floor($material_interest_period / 365.25)) - (floor($foreign_days / 365.25));


	}
		$tar['applied'] = true;

	return($tar);
}



public static function calculate_gain($years, $encashment_proceeds, $total_withdrawals, $total_investment, $total_excess, $offshore_bond, $tax_credit_rate) {

		$loss = false;
		$top_slice = 0;
		$result = array();

		$calc = (($encashment_proceeds + $total_withdrawals) - ($total_investment + $total_excess));
		$true_gain = $calc; // actual gain - not the formatted gain.....

		if($true_gain < 0) { $true_gain = 0; }
		
		if ($calc < 0) {

			$calc *= -1;

			$loss = true;


		$result['deficiency'] = $calc;
		$result['loss'] = number_format($calc, 2, '.', ','). " (Deficiency)";
		$result['positive'] = false;

		} else {
 
		$result['positive'] = true;
		$result['deficiency'] = 0;

			}


				$result['true_gain'] = round($true_gain,2);
		
		
		
		return $result;
	}


public static function calculate_top_slice($gain, $years, $factor, $commencement_date, $offshore_bond, $tax_credit_rate) {

		$loss = false;
		$top_slice = 0;
		$result = array();


		if($factor > 1) { $top_slice = $gain / $factor; } else { $top_slice = $gain / $years; }

		if($gain <= 0) {$top_slice = 0;}

		$result['top_slice'] = round($top_slice,2);
		
		$pre_1983 = new DateTime('1983-11-18');
		$cd = new DateTime($commencement_date);
		
		if($offshore_bond == 0 || ($offshore_bond == 1 && $cd < $pre_1983)) {
			if($gain > 0) {	
				$result['tax_credit'] = ($gain / 100) * $tax_credit_rate;
			} 	
			else {
		       $result['tax_credit'] = 0;	
				}

			}	
		else {
		       $result['tax_credit'] = 0;	
		}




		return $result;
	}

public static function load_policyholders($calculation_id, $user_id) {

        $policyholder = array();
        $counter = 0;

	$pol = DB::table('calculation_set')
	->join('bonds','bonds.id', '=', 'calculation_set.bond_id')
        ->join('calculations','calculations.id', '=', 'calculation_set.calculation_id')
        ->join('ownerships','bonds.id', '=', 'ownerships.bond_id')
        ->join('policyholders','policyholders.id', '=', 'ownerships.policyholder_id')
	->where('calculation_set.calculation_id', '=', $calculation_id)
	->orderBy('calculation_set.calculation_id', 'asc')
	->select('calculation_set.calculation_id', 'policyholders.first_name', 'policyholders.surname', 'policyholders.id AS policyholder_id', 'policyholders.gross_income', 'policyholders.allowances', 'policyholders.dob')
	->distinct()
	->get();

        if (count($pol) > 0) {
        foreach ($pol as $id => $output ) {

                $policyholder[$counter]['first_name'] = $output->first_name;
                $policyholder[$counter]['surname'] = $output->surname;
                $policyholder[$counter]['calculation_id'] = $output->calculation_id;
                $policyholder[$counter]['policyholder_id'] = $output->policyholder_id;
                $policyholder[$counter]['allowances'] = $output->allowances;
                $policyholder[$counter]['gross_income'] = $output->gross_income;
                $policyholder[$counter]['aggregate_top_slice'] = 0;
                $policyholder[$counter]['aggregate_tax_credit'] = 0;
                $policyholder[$counter]['aggregate_slice_tax_credit'] = 0;
                $policyholder[$counter]['aggregate_gain'] = 0;
                $policyholder[$counter]['corresponding_deficiency_relief'] = 0;
                $policyholder[$counter]['aggregate_years'] = 0;
                $policyholder[$counter]['taxation'] = array();
                $policyholder[$counter]['final_tax'] = 0;
                $policyholder[$counter]['deficiency'] = 0; // Corresponding Deficiency Relief

                $policyholder[$counter]['net_income'] = ($output->gross_income - $output->allowances);

			if($policyholder[$counter]['net_income'] < 0) {$policyholder[$counter]['net_income'] = 0;}

		$dob1 = preg_split('/[\/\.-]/',$output->dob);

                $dob = $dob1[2]."/".$dob1[1]."/".$dob1[0];

		$age = date("Y") - $dob1[0];

		if(date("m") < $dob[1]) { $age--; }

		if(date("m") == $dob1[1] && date("d") < $dob1[2]) { $age--; }

		$policyholder[$counter]['age'] = $age;

                $counter++;

                }

        }


    return $policyholder;

    }


public static function load_non_residence($policyholder_id, $user_id, $bond) {

        $non_residence = array();
        $counter = 0;
	$encashment_date = null;

	if($bond->auto_update == 1) { $encashment_date = date('Y').'-'.date('m').'-'.date('d'); } else { $encashment_date = $bond->encashment_date; } 

	$nr = DB::table('non_residence')
	->where('policyholder_id', '=', $policyholder_id)
	->where('start_date', '>=', $bond->commencement_date)
	->where('end_date', '<=', $encashment_date)
	->orderBy('start_date', 'asc')
	->distinct()
	->get();

        if (count($nr) > 0) {
        foreach ($nr as $id => $output ) {

                $non_residence[$counter]['id'] = $output->id;
                $non_residence[$counter]['policyholder_id'] = $output->policyholder_id;
                $non_residence[$counter]['start_date'] = $output->start_date;
                $non_residence[$counter]['end_date'] = $output->end_date;

                $counter++;

                }
        }

    return $non_residence;

    }


public static function calculate_deficiency($tax_rates, $deficiency, $bond, $excess, $increments, $ownerships, $bond_type) {

	$loss = 0;

	if($deficiency == 0) { return($loss); }

	if(array_sum($excess) == 0) { return($loss); }

	if($bond_type == 3) { return($loss); }

	$exs = 0; // cumulative excess if bond taken out after 2004.
	$start_date = new DateTime($bond->commencement_date);
	$last_excess = new DateTime();
	$assignment_date = new DateTime();
	$assignment  = 0;

	if($bond_type == 1) {

		for($y = 0; $y < count($ownerships); $y++) {


			if($ownerships[$y]['assignment_date'] != '0000-00-00') {

				$assignment_date = DateTime::createFromFormat('Y-m-d', $ownerships[$y]['assignment_date']); 
				$assignment = 1;

				}
			}
		
		for($z = 0; $z < count($excess); $z++) {

			if($excess[$z] > 0) { $last_excess = clone $start_date; $last_excess->modify('+'.$z.' years');  }
		

			if($excess[$z] > 0 && ($assignment == 0 || ($assignment == 1 && $assignment_date < $last_excess))) { $exs += $excess[$z]; } 	
		}

		$excess = $exs;}

		if($excess > $deficiency) { $loss = $deficiency; } else { $loss = $excess; }

		

	return($loss);
}



public static function calculate_additional_tax($tax_rates, $gross_income, $allowances, $aggregate_top_slice) {

		$remaining_allowances = 0;
       		$remaining_basic_rate = 0;		
       		$remaining_higher_rate = 0;		
		$remaining_slice = 0;
		$additional_rate = 0;
		$rows = array();

		if($gross_income < $allowances) {
			
			$remaining_allowances = $allowances - $gross_income;
		
					} else {
						
					$remaining_allowances = 0;
				       
					}


		if($gross_income > ($allowances + $tax_rates[0]->basic_rate_limit)) {
			
			
			$remaining_basic_rate = 0;

			if($gross_income > ($allowances + $tax_rates[0]->higher_rate_limit)) {
			
			
			$remaining_higher_rate = 0; $additional_rate = 1;} else {

									
					$remaining_higher_rate =  ($tax_rates[0]->higher_rate_limit) - ($gross_income - $allowances);

		}
		
		} else {

				if($remaining_allowances > 0) {
					
					$remaining_basic_rate = $tax_rates[0]->basic_rate_limit;
				
				} else {
					
					$remaining_basic_rate = $tax_rates[0]->basic_rate_limit - ($gross_income - $allowances);}

		}

		

		if($remaining_allowances > 0) {

			if($aggregate_top_slice <= $remaining_allowances) {

				$rows['slice_untaxed'] = $aggregate_top_slice;}

			else {


				$rows['slice_untaxed'] = $remaining_allowances;}

			}

 				else {


					$rows['slice_untaxed'] = 0;
				
				}

		if($rows['slice_untaxed'] == $aggregate_top_slice) {
			
			$remaining_slice = 0;
		
		} else {

			$remaining_slice = $aggregate_top_slice - $rows['slice_untaxed'];

		}

		if($remaining_slice <=  $remaining_basic_rate) {

			$rows['slice_basic_rate'] = $remaining_slice;

			} else {

			$rows['slice_basic_rate'] = $remaining_basic_rate;

			}		


		if($remaining_slice - $rows['slice_basic_rate'] < 0) {

			$remaining_slice = 0;

		} else {

			$remaining_slice = $remaining_slice - $rows['slice_basic_rate'];
		}



		if($remaining_slice <= ($tax_rates[0]->higher_rate_limit - $tax_rates[0]->basic_rate_limit) && $additional_rate == 0) {

			$rows['slice_higher_rate'] = $remaining_slice;

			} elseif($remaining_slice > ($tax_rates[0]->higher_rate_limit - $tax_rates[0]->basic_rate_limit) && $additional_rate == 0) {

			$rows['slice_higher_rate'] = ($tax_rates[0]->higher_rate_limit - $tax_rates[0]->basic_rate_limit);

			} elseif($additional_rate == 1) {

			$rows['slice_higher_rate'] = 0;
			
			}	

		if($remaining_slice - $rows['slice_higher_rate'] < 0) {

			$remaining_slice = 0;

		} else {

			$remaining_slice = $remaining_slice - $rows['slice_higher_rate'];
		}

		$rows['slice_additional_rate'] = $remaining_slice;


		//
		//
		//**	Calculate Tax Payable on Slices
		//
		//



		if($rows['slice_basic_rate'] == 0) {

			$rows['basic_rate_tax'] = 0;

		} else {

			$rows['basic_rate_tax'] = round(($rows['slice_basic_rate'] / 100) * $tax_rates[0]->bond_basic_rate,2);

		}



		if($rows['slice_higher_rate'] == 0) {

			$rows['higher_rate_tax'] = 0;

		} else {

			$rows['higher_rate_tax'] = round(($rows['slice_higher_rate'] / 100) * $tax_rates[0]->bond_higher_rate,2);

		}

	
		if($rows['slice_additional_rate'] == 0) {

			$rows['additional_rate_tax'] = 0;

		} else {

			$rows['additional_rate_tax'] = round(($rows['slice_additional_rate'] / 100) * $tax_rates[0]->bond_additional_rate,2);

		}



	 $rows['total_tax'] = round($rows['basic_rate_tax'] + $rows['higher_rate_tax'] + $rows['additional_rate_tax'], 2);

	 return $rows;
    }




public static function calculate_deficiency_relief($tax_rates, $gross_income, $allowances, $taxation, $deficiency, $gain) {

	$max_deficiency = $tax_rates[0]->higher_rate_limit = $tax_rates[0]->basic_rate_limit;

	$cdr = array();

	$cdr['deficiency'] = 0;
	$cdr['remainder'] = 0;
	$cdr['relief_slice'] = 0;
	$cdr['relief_higher_rate'] = 0;
	$cdr['tax'] = 0;

	$remainder = $deficiency; // Calculate any tax due on other slices first

	if($deficiency <= 0) { return($cdr); }


	if($gross_income - $allowances <= $tax_rates[0]->basic_rate_limit) { return ($cdr); }

	if($taxation['slice_higher_rate'] > 0) {

		if($deficiency >= $taxation['slice_higher_rate']) { $deficiency =  $taxation['slice_higher_rate'] ; $remainder = $deficiency - $taxation['slice_higher_rate'];  }
		else
	       	{ $deficiency = $taxation['slice_higher_rate'] - $deficiency; $remainder = 0; }

		$cdr['deficiency'] = $deficiency;
		$cdr['relief_slice'] = $deficiency / 100 * ($tax_rates[0]->bond_higher_rate- $tax_rates[0]->bond_basic_rate);

	}

	if($remainder > 0) {

		$higher_rate_available =  ($gross_income - $allowances) - $tax_rates[0]->basic_rate_limit;

		if($higher_rate_available > $max_deficiency) { $higher_rate_available = $max_deficiency; }

		if($remainder >= $higher_rate_available) { $remainder = $higher_rate_available; }
		else {
			 $remainder = $remainder; 
		}

		$cdr['remainder'] = $remainder;
		$cdr['relief_higher_rate'] = $remainder / 100 * ($tax_rates[0]->bond_higher_rate- $tax_rates[0]->bond_basic_rate);


	}
		
	$cdr['tax'] = $cdr['relief_slice'] + $cdr['relief_higher_rate'];

	return($cdr);
}


	public static function start_date_final_policy_year($commencement_date, $encashment_date) {

		$start_year_tax_year_ending = 0;
		$end_year_tax_year_ending = 0;
		$temp_year_tax_year_ending = 0;
		$temp_end_year = 0;
		$temp_final_policy_year_start = array();
		$final_policy_year_start = array();


		if($commencement_date[1] > $encashment_date[1]) {$temp_end_year = $encashment_date[0] - 1;}

		elseif(($commencement_date[1] == $encashment_date[1]) && $commencement_date[2] > $encashment_date[2]) {$temp_end_year = $encashment_date[0] - 1;}

		else {$temp_end_year = $encashment_date[0];}

		$temp_final_policy_year_start[0] = $temp_end_year;
		$temp_final_policy_year_start[1] = $commencement_date[1];
		$temp_final_policy_year_start[2] = $commencement_date[2];

		if($commencement_date[1] > 4) {$start_year_tax_year_ending = $commencement_date[0] + 1;}
		if($commencement_date[1] == 4 && $commencement_date[2] < 6) {$start_year_tax_year_ending = $commencement_date[0];}
		if($commencement_date[1] == 4 && $commencement_date[2] >= 6) {$start_year_tax_year_ending = $commencement_date[0] + 1;}
		if($commencement_date[1] < 4) {$start_year_tax_year_ending = $commencement_date[0];}

		if($encashment_date[1] > 4) {$end_year_tax_year_ending = $encashment_date[0] + 1;}
		if($encashment_date[1] == 4 && $encashment_date[2] < 6) {$end_year_tax_year_ending = $encashment_date[0];}
		if($encashment_date[1] == 4 && $encashment_date[2] >= 6) {$end_year_tax_year_ending = $encashment_date[0] + 1;}
		if($encashment_date[1] < 4) {$end_year_tax_year_ending = $encashment_date[0];}

		if($temp_final_policy_year_start[1] > 4) {$temp_year_tax_year_ending = $temp_final_policy_year_start[0] + 1;}
		if($temp_final_policy_year_start[1] == 4 && $temp_final_policy_year_start[2] < 6) {$temp_year_tax_year_ending = $temp_final_policy_year_start[0];}
		if($temp_final_policy_year_start[1] == 4 && $temp_final_policy_year_start[2] >= 6) {$temp_year_tax_year_ending = $temp_final_policy_year_start[0] + 1;}
		if($temp_final_policy_year_start[1] < 4) {$temp_year_tax_year_ending = $temp_final_policy_year_start[0];}

		$final_policy_year_start[1] = $commencement_date[1];		
		$final_policy_year_start[2] = $commencement_date[2];		

		if($temp_year_tax_year_ending <> $end_year_tax_year_ending) {$final_policy_year_start[0] =  $temp_final_policy_year_start[0]; $final_policy_year_start[3] = 1;}
		else {$final_policy_year_start[0] =  $temp_final_policy_year_start[0] - 1; $final_policy_year_start[3] = 2;}

		return $final_policy_year_start;

			}


public static function calculate_top_slicing_relief( $tax_rates, $aggregate_gain,$gross_income, $allowances, $final_tax) {


	$remaining = 0;
	$net_income = $gross_income - $allowances;
	$taxable_gain = 0;
	$tax = 0;

	if($net_income < 0) { $net_income = 0; }

	if($net_income < $tax_rates[0]->basic_rate_limit) {

		$remaining = $tax_rates[0]->basic_rate_limit - $net_income; 

	}

	$taxable_gain = $aggregate_gain - $remaining;

	$higher_rate_gain_band = $tax_rates[0]->higher_rate_limit - $tax_rates[0]->basic_rate_limit;
	if($taxable_gain <= $higher_rate_gain_band) { $tax = $taxable_gain / 100 * ($tax_rates[0]->bond_higher_rate - $tax_rates[0]->bond_basic_rate); }

		if($taxable_gain > $higher_rate_gain_band) { 

			$additional_rate_gain = $taxable_gain - $higher_rate_gain_band;

			$tax = ($higher_rate_gain_band / 100 * ($tax_rates[0]->bond_higher_rate - $tax_rates[0]->bond_basic_rate)) + ($additional_rate_gain / 100 * ($tax_rates[0]->bond_additional_rate - $tax_rates[0]->bond_basic_rate));

		}

	$result = $tax - $final_tax;

	if($result < 0) { $result = 0; }

	return number_format($result, 2, '.', ',');
}



public static function calculate_pension_premiums($tax_rates, $gross_income, $allowances, $age, $slice_higher_rate, $final_tax) {


	 $basic_rate_reduction = 0;
	 $rows = array();
  	 $rows['net_pension_premium'] = 0;
	 $rows['effective_tax_relief_pension'] = 0;

	 if($slice_higher_rate == 0) {return $rows;}

	 if($age > 76) { return $rows;}

	
	if(($gross_income - $allowances) <= $tax_rates[0]->basic_rate_limit) {

		$basicRateReduction = 0;
	} 
	
	elseif

	
	(($gross_income - $allowances) > $tax_rates[0]->basic_rate_limit) {

		$basic_rate_reduction = ($gross_income - $allowances) - $tax_rates[0]->basic_rate_limit;

	}

		$rows['net_pension_premium'] = (($basic_rate_reduction + $slice_higher_rate) / 100) * (100 - $tax_rates[0]->income_tax_basic_rate);
		$basic_rate_pension_relief = ($basic_rate_reduction + $slice_higher_rate) - $rows['net_pension_premium'];
		$rows['effective_tax_relief_pension'] = round(((($basic_rate_pension_relief + $final_tax) / $slice_higher_rate) * 100) , 2);


  	 $rows['net_pension_premium'] = number_format($rows['net_pension_premium'], 2,'.',',');




	 return $rows;
 }



public static function calculate_age_allowance($tax_rates, $gross_income, $allowances, $taxation, $age, $gain) {

	 $rows = array();
	 $rows['excess'] = 0;
	 $rows['reduction'] = 0;
	 $rows['lost'] = 0;
	 $diff_age = 0;


	 if($age >= 65 && $age < 75) {

		$diff_age = $tax_rates[0]->personal_allowance_65 - $tax_rates[0]->personal_allowance;

	 }

	 elseif($age >= 75) {

		$diff_age = $tax_rates[0]->personal_allowance_75 - $tax_rates[0]->personal_allowance;

	 }

	 else { return $rows;}

	if(($gross_income - $allowances) > ($tax_rates[0]->age_allowance_limit + ($diff_age * 2))) { return $rows;}



		 if(($gross_income + $gain) > $tax_rates[0]->age_allowance_limit) {


			 if($allowances > $tax_rates[0]->personal_allowance) {


				$tgain = $gross_income + $gain;

				if($tgain <= $tax_rates[0]->age_allowance_limit) {$rows['excess'] = 0;} else {$rows['excess'] = $tgain - $tax_rates[0]->age_allowance_limit;}

				if($rows['excess'] > ($diff_age * 2))  {$rows['reduction'] = $allowances - $diff_age;} 
					else { $rows['reduction'] = $allowances - ($rows['excess'] / 2);}


				$rows['lost'] = $allowances - $rows['reduction'];

				$rows['additional_tax'] = ($rows['lost'] / 100) * $tax_rates[0]->income_tax_basic_rate;

				$rows['excess'] = number_format($rows['excess'], 2, '.',',');
				$rows['reduction'] = number_format($rows['reduction'], 2, '.',',');
				$rows['lost'] = number_format($rows['lost'], 2, '.',',');
				$rows['additional_tax'] = number_format($rows['additional_tax'], 2, '.',',');
			 }


		 }

	 return $rows;
 }


public static function generate_report() {

	$bond = array();
	$bond_type = array();
	$results = array();
	$policyholders = array();
	$ownerships = array();
	$segment_blocks = array();
	$allowance = array();
	$cumulative_allowance = array();
	$increment_details = array();
	$encashment_details = array();
	$policy_loans_years = array();
	$excess = array();
	$policy_loans_years = array();
	$additional_taxation = array();
	$cdr_excess = array();
	$total_excess = array();
	$total_withdrawals = array();
	$total_years = array();
	$foreign_days = array();
	$material_interest_period = array();
	$tar = array(); //time apportionment relief
	$withdrawal_details = array();
	$w = array(); // temp array to hold withdrawals if mode = 1
	$segment_match = array();

	$day = date('d');
        $month = date('m');
        $year = date('Y');
        $tax_year = $year;

        if($month > 4 || ($month == 4 && $day >= 6)) { $tax_year = ++$year;} else {$tax_year = $year;}
	$tax_rates = DB::table('tax_rates')->where('tax_year_ending', '=', $tax_year)->get();

	$tax_year = $tax_rates[0]->tax_year_ending;

	$bonds = DB::table('calculation_set')->where('calculation_id', '=', Session::get('calculation_id'))->select('bond_id')->get(); 

	$policyholder = Report::load_policyholders(Session::get('calculation_id'), Auth::user()->id);

	$non_residence = array();

	for($i = 0; $i < count($policyholder); $i++) {


	}


	for($bd = 0; $bd < count($bonds); $bd++) {

		$total_increment= 0;
		$total_encashment= 0;
		$total_policy_loans = 0;
		$total_capital_repayments = 0;

		
		$bond[$bd] = Report::load_bonds($bonds[$bd]->bond_id, Auth::user()->id);
		

		$segments = Report::load_segments($bond[$bd][0]->bond_id, Auth::user()->id);

	$segment_start = 1;
	$segment_end = count($segments);
	$segment_break_points = Report::check_segments($bond[$bd][0]->bond_id, Auth::user()->id);


	$segment_blocks = Report::load_segment_blocks($bond[$bd][0]->bond_id, Auth::user()->id);

	if($bond[$bd][0]->mode == 1) {

		for($sb = 0; $sb < count($segment_blocks); $sb++) {
			for($i = 0; $i < count($segment_break_points); $i++) {

			$segment_match[$sb][$i] = Report::reset_array(array_intersect(range($segment_break_points[$i]['segment_start'], $segment_break_points[$i]['segment_end']), range($segment_blocks[$sb]['segment_start'], $segment_blocks[$sb]['segment_end'])), 0); // Check for matching segments

				}
			}

		}

	$ownerships[$bd] = Report::load_ownerships($bond[$bd][0]->bond_id, Auth::user()->id);

	
	
		//////////////////////////////////////////////////
	 	//
	 	// Work out how long the bond has been in force...
	 	//
	 	// Also, work out the allowances
	 	//
		//////////////////////////////////////////////////

                $cd  = preg_split('/[\/\.-]/', $bond[$bd][0]->commencement_date);

                $ed = preg_split('/[\/\.-]/', $bond[$bd][0]->encashment_date);


		$commencement_date[$bd] = $cd[2].'/'.$cd[1].'/'.$cd[0];
		$encashment_date[$bd] = $ed[2].'/'.$ed[1].'/'.$ed[0];


		// Find out the start date of the final policy year
		$start_date_final_policy_year = Report::start_date_final_policy_year($cd, $ed);

		$total_years[$bd] = Report::calculate_years($ed[0],$cd[0],$ed[1],$cd[1],$ed[2],$cd[2]);

		$start_year = $cd[0] + 1;

		if($cd[2] == '01' && $cd[1] == '01') {

			$start_year = $cd[0];
		
		}


		$total_withdrawals[$bd] = Report::total_withdrawals($bond[$bd][0]->bond_id, Auth::user()->id);


	////// 	Check if there have been any increments or encashments, and factor them in...

		$increment_details[$bd] = Report::load_increment_details($bond[$bd][0]->bond_id, Auth::user()->id, $cd[2],$cd[1],$cd[0]);

		$encashment_details[$bd] = Report::load_encashment_details($bond[$bd][0]->bond_id, Auth::user()->id, $cd[2],$cd[1],$cd[0]);
		
		$policy_loans[$bd] = Report::load_policy_loans($bond[$bd][0]->bond_id, Auth::user()->id, $cd[2],$cd[1],$cd[0]);

		$policy_loans_years[$bd] = Report::load_policy_loan_years($policy_loans[$bd], $total_years[$bd], Auth::user()->id);

		if($bond[$bd][0]->mode == 0) {
			
			$allowance = Report::calculate_allowance($total_years[$bd], (($bond[$bd][0]->investment / 100) *5.0), $increment_details[$bd], $encashment_details[$bd]);
		
		} elseif($bond[$bd][0]->mode == 1) {


			


			for($sb = 0; $sb < count($segment_blocks); $sb++) {
		       	$allowance[$sb] = Report::calculate_allowance_extended($total_years[$bd], $segments, $segment_break_points, $segment_match[$sb], $segment_blocks[$sb]['segment_start'], $segment_blocks[$sb]['segment_end'], $increment_details[$bd], $encashment_details[$bd]);
		}
			}



	for($i=0; $i < count($increment_details[$bd]); $i++) {

		if(isset($increment_details[$bd][$i]['increment_amount'])) {

			$total_increment += $increment_details[$bd][$i]['increment_amount'];

		}
	}


	for($i=0; $i < count($encashment_details[$bd]); $i++) {

		if(isset($encashment_details[$bd][$i]['seg_value'])) {

			$total_encashment += $encashment_details[$bd][$i]['seg_value'];

		}
	}


	for($i=0; $i < count($policy_loans[$bd]); $i++) {

		if(isset($policy_loans[$bd][$i]['policy_loan'])) {

			$total_policy_loans += $policy_loans[$bd][$i]['policy_loan'];

		}


		if(isset($policy_loans[$bd][$i]['capital_repayment'])) {

			$total_capital_repayments += $policy_loans[$bd][$i]['capital_repayment'];

		}

	}


	
	$total_investment[$bd] = ($bond[$bd][0]->investment + $total_increment + $total_capital_repayments) - $total_encashment;


	for($seg = 0; $seg < count($segment_blocks); $seg++) {


		if($bond[$bd][0]->mode == 0) {
			
			$withdrawal_details[$bd] = Report::load_withdrawal_details($bond[$bd][0]->bond_id, Auth::user()->id);

		} elseif($bond[$bd][0]->mode == 1) {

			for($sb = 0; $sb < count($segment_blocks); $sb++) {

				$w[$sb] = Report::load_withdrawal_details_extended($bond[$bd][0]->bond_id, Auth::user()->id, $segment_blocks[$sb]['segment_start'], $segment_blocks[$sb]['segment_end']);

			}
			
			
			for($y = 0; $y < count($w); $y++) {
				for($x = 0; $x < count($w[$y]); $x++) {
				
				if($y == 0) { $withdrawal_details[$bd][$x]['year_ending'] = $w[$y][$x]['year_ending']; $withdrawal_details[$bd][$x]['withdrawal_amount'] = 0;}
		
				$withdrawal_details[$bd][$x]['withdrawal_amount'] += $w[$y][$x]['withdrawal_amount'];

				if($w[$y][$x]['withdrawal_percentage'] > 0) { $withdrawal_details[$bd][$x]['withdrawal_percentage'] = $w[$y][$x]['withdrawal_percentage']; } else $withdrawal_details[$bd][$x]['withdrawal_percentage'] = 0; }

				}
			}
		}


		for($z=0; $z < $total_years[$bd]; $z++) {

				if(!isset($withdrawal_details[$bd][$z]['withdrawal_amount'])) {

					$withdrawal_details[$bd][$z]['withdrawal_amount'] = 0;
					$withdrawal_details[$bd][$z]['year_ending'] = $start_year + $z;
				
				}

			}



		 while ($withdrawal_details[$bd][0]['year_ending'] != $start_year) {

			if($withdrawal_details[$bd][0]['year_ending'] > $start_year) {
			 
				array_unshift($withdrawal_details[$bd], array('year_ending' => $withdrawal_details[$bd][0]['year_ending'] - 1, 'withdrawal_amount' => 0, 'id' => 0));

			}

			if($withdrawal_details[$bd][0]['year_ending'] < $start_year)  {

				array_shift($withdrawal_details);	

			}


		} 

	
		
	  if($bond[$bd][0]->mode == 0) {

		  for($i = 0; $i < count($segments); $i++) {
		  
		  $cumulative_allowance[$i] = Report::calculate_cumulative_allowance($i, $withdrawal_details[$bd], $policy_loans_years[$bd],  $segment_break_points, $segments[$i]['year'], $increment_details[$bd], $bond[$bd][0]->segments, $encashment_details[$bd],  $total_years[$bd], $segments[$i]['segment_annual_allowance']);
	  
				
		$excess[$i] = Report::calculate_excess($i, $withdrawal_details[$bd], $policy_loans_years[$bd],  $segment_break_points, $segments[$i]['year'], $increment_details[$bd], $bond[$bd][0]->segments, $encashment_details[$bd],  $cumulative_allowance[$i], $total_years[$bd], $segments[$i]['segment_annual_allowance'], $start_date_final_policy_year[3]);


  		}


	  } elseif($bond[$bd][0]->mode == 1) {


		for($sb = 0; $sb < count($segment_blocks); $sb++) {
		
		$j = $segment_blocks[$sb]['segment_start'] - 1;
		$temp_allowance = array_fill($j, $total_years[$bd], 0); // temporary array

		$match = array_fill($segment_blocks[$sb]['segment_start'] - 1,  ($segment_blocks[$sb]['segment_end'] - ($segment_blocks[$sb]['segment_start'] - 1)), 0); // temporary array
		
		$t = 0; // warning variable to flag up new segment start point

		$seg_count = array();

		if(count($segment_match[$sb] > 0)) {
			
			for($i = 1; $i < count($segment_match[$sb]); $i++) { if(!empty($segment_match[$sb][$i])) {$z = ($segment_match[$sb][$i][0]) - 1; $match[$z] = $z;} }

		}	

	
		$percentage_used = false;

		if($withdrawal_details[$bd][0]['withdrawal_percentage'] > 0) { $percentage_used = true; }
		$start_seg = 0; // Temporary placeholder - in the very unlikely case that that a segment block crosses an increment and the withdrawals are fixed.

		///////////////////////////////////////
		//
		//	Work out the number of segments
		//	to divide the withdrawal by 
		//	on each pass - bit of a kludge, but it 
		//	is it a quirk of how the bonds work...
		//
		///////////////////////////////////////	

	$seg_start = 0;

	for($i = $segment_blocks[$sb]['segment_start'] - 1; $i <= $segment_blocks[$sb]['segment_end'] - 1; $i++) {

		$year = $segments[$i]['year'];	
		for($l = 0; $l < count($segment_match[$sb]); $l++) {

			$sm = $segment_match[$sb][$l];
			if(in_array(($i + 1), $sm)) { $seg_count[$i][$year] = count($sm);  }		
			if($year == 0) { $seg_start = $seg_count[$i][$year]; }

		}

		for($m = $year; $m < $total_years[$bd]; $m++) {

		if($percentage_used == false) {

			if(!isset($seg_count[$i - 1][$m - 1])) { $seg_count[$i - 1][$m - 1] = 0;  }

			if($year > 0 && $m == $year) { $seg_count[$i][$m] += $seg_start; $seg_count[$i][$m - 1] = $seg_count[$i - 1][$m - 1];  }	
		if($m > $year) {  $seg_count[$i][$m] = $seg_count[$i][$m - 1]; 	} 

			for($n = 0; $n < count($segment_break_points); $n++) {

			if($m > $year && $n > 0 && $m == $segment_break_points[$n]['year']) {  $seg_count[$i][$m] +=  $segment_break_points[$n]['segments'];  } 

		}

			for($j = $segment_blocks[$sb]['segment_start'] - 1; $j <= $segment_blocks[$sb]['segment_end'] - 1; $j++) {

				if(isset($encashment_details[$bd][$j])) {
				if($encashment_details[$bd][$j]['year'] == $i) {

					$seg_count[$i][$m] -= $encashment_details[$bd][$j]['segments_encashed'];
					}
				}

			}
					
		} else {
			if($m > $year && $i == $match[$i]) { $t = $i + 1;  $seg_count[$i][$m] = Report::count_segments($t, $segment_match[$sb]);    } 
			elseif($m > $year && $i != $match[$i]) {  $seg_count[$i][$m] = $seg_count[$i][$m - 1]; }

			for($j = $segment_blocks[$sb]['segment_start'] - 1; $j <= $segment_blocks[$sb]['segment_end'] - 1; $j++) {

				if(isset($encashment_details[$bd][$j])) {
				if($encashment_details[$bd][$j]['year'] == $i) {

					$seg_count[$i][$m] -= $encashment_details[$bd][$j]['segments_encashed'];
					}
				}

			}
		}
		}

	}

		//////////////////////////////////////
		//
		//	Work out the CA's, Excess, etc...
		//
		///////////////////////////////////////
	

	for($i = $segment_blocks[$sb]['segment_start'] - 1; $i <= $segment_blocks[$sb]['segment_end'] - 1; $i++) {

		//r($w[$sb]);r($policy_loans_years[$bd]);exit;

	  	$cumulative_allowance[$i] = Report::calculate_cumulative_allowance_extended($i, $w[$sb], $policy_loans_years[$bd],  $segment_break_points, $segment_match[$sb], $match, $segments, $encashment_details[$bd], $total_years[$bd],  $segment_blocks[$sb]['segment_start'], $segment_blocks[$sb]['segment_end'], $temp_allowance, $seg_count[$i]);

		$temp_allowance = $cumulative_allowance[$i];

	//	$ce[$sb][$i] = Report::calculate_chargeable_event_extended($i, $segments, $withdrawal_details, $policy_loan_years,  $segment_break_points, $segments[$i]['year'], $encashment_details, $cumulative_allowance[$i], $total_years, $segments[$i]['segment_annual_allowance'], $start_date_final_policy_year[3], $seg_count[$i], $segment_blocks[$sb]['segment_start'], $segment_blocks[$sb]['segment_end']);

		$excess[$i] = Report::calculate_excess_extended($i, $segments, $w[$sb], $policy_loans_years[$bd], $segment_break_points, $segments[$i]['year'], $encashment_details, $cumulative_allowance[$i], $total_years[$bd], $segments[$i]['segment_annual_allowance'], $start_date_final_policy_year[3], $seg_count[$i], $segment_blocks[$sb]['segment_start'], $segment_blocks[$sb]['segment_end']);

				}

			}
	} 


	$total_excess[$bd] = 0;

		for($i=0; $i < count($excess); $i++) {
		
			foreach($excess[$i] as $key => $value) {

			$total_excess[$bd] += $value;

                	}

	}

	// Work out Excess for CDR purposes
	
	unset($cdr_excess);
	$cdr_excess = array();

	for($z=0; $z < count($excess); $z++) {
		
		$cdr_excess[$z] = 0;

			foreach($excess[$z] as $key => $value) {

			$cdr_excess[$z] += $value;

                	}

	}






		//////////////////////////////////////////////////
	 	//
	 	// Check if there have been periods of non-residence
	 	//
		// If so, check if time apportionment relief applies, 
		// and work out the factors
	 	//
		//////////////////////////////////////////////////

		$bond_type[$bd] = Report::find_bond_type($bond[$bd][0], $increment_details[$bd], $ownerships[$bd]);
		$bond_type_cdr[$bd] = Report::find_bond_type_cdr($bond[$bd][0], $increment_details[$bd], $ownerships[$bd], $cdr_excess);



	for($i=0; $i < count($policyholder); $i++) {

		$non_residence[$i] = Report::load_non_residence($policyholder[$i]['policyholder_id'], Auth::user()->id, $bond[$bd][0]);


		for($j=0; $j < count($ownerships[$bd]); $j++) {
		
		$foreign_days = 0;


		if($policyholder[$i]['policyholder_id'] == $ownerships[$bd][$j]['policyholder_id']) {
		if(count($non_residence[$i]) > 0 && $bond_type[$bd] != 0) {	
		
			for($z =0; $z < count($non_residence[$i]); $z++) {

			$foreign_days += Report::date_overlap($bond[$bd][0]->commencement_date, $bond[$bd][0]->encashment_date, $non_residence[$i][$z]['start_date'], $non_residence[$i][$z]['end_date']);
			}
		
	
		$mip = date_diff(new DateTime($bond[$bd][0]->commencement_date), new DateTime($bond[$bd][0]->encashment_date));
		$material_interest_period = $mip->format('%a');
		$uk_days = $material_interest_period - $foreign_days;
		
		$tar[$i] = Report::calculate_time_apportionment_factors($i, $ownerships[$bd][$j]['percentage_split'], $bond_type[$bd], $bond[$bd][0], $foreign_days, $material_interest_period, $uk_days, $total_years[$bd]);

		} 		
		} else {
		}


		}


		if(!isset($tar[$i]['chargeable_gain_factor'])) {
			$tar[$i]['chargeable_gain_factor'] = 1;
			$tar[$i]['top_slicing_factor'] = 1;
			$tar[$i]['applied'] = false;
		}

	}

		//////////////////////////////////////////////////
	 	//
	 	// Time to work out the gain, loss, top-slices for each bond 
	 	//
		//////////////////////////////////////////////////



	$results[$bd] = Report::calculate_gain($total_years[$bd], $bond[$bd][0]->encashment_proceeds, $total_withdrawals[$bd], $total_investment[$bd], $total_excess[$bd], $bond[$bd][0]->offshore_bond, ($tax_rates[0]->bond_higher_rate - $tax_rates[0]->bond_basic_rate));

	 $cdr[$bd] = Report::calculate_deficiency($tax_rates, $results[$bd]['deficiency'],  $bond[$bd][0],  $cdr_excess, $increment_details[$bd], $ownerships[$bd], $bond_type_cdr[$bd]);

	$gain = 0;

	for($i=0; $i < count($policyholder); $i++) {

		if($tar[$i]['applied'] == true) { $results[$bd]['tar'] = true; } else {$results[$bd]['tar'] = false; }
		
		for($j=0; $j < count($ownerships[$bd]); $j++) {


		if($policyholder[$i]['policyholder_id'] == $ownerships[$bd][$j]['policyholder_id']) {
		

		$policyholder[$i]['aggregate_gain'] += round(((($results[$bd]['true_gain'] / 100) * $ownerships[$bd][$j]['percentage']) * $tar[$i]['chargeable_gain_factor']), 2, PHP_ROUND_HALF_EVEN);


	 	$policyholder[$i]['deficiency'] += round((($cdr[$bd] / 100) * $ownerships[$bd][$j]['percentage']), 2, PHP_ROUND_HALF_EVEN);
				

		$gain += ((($results[$bd]['true_gain'] / 100) * $ownerships[$bd][$j]['percentage']) * $tar[$i]['chargeable_gain_factor']);

		}

		}

	}

	if($gain != $results[$bd]['true_gain']) {$results[$bd]['true_gain'] = $gain; }
	
		$t1 = 0; // Temporary var in case TAR is applied, to alter total years.



	for($i=0; $i < count($policyholder); $i++) {

	$results[$bd] += Report::calculate_top_slice($gain, $total_years[$bd], $tar[$i]['top_slicing_factor'], $bond[$bd][0]->commencement_date, $bond[$bd][0]->offshore_bond, ($tax_rates[0]->bond_higher_rate - $tax_rates[0]->bond_basic_rate));

		for($j=0; $j < count($ownerships[$bd]); $j++) {


		if($policyholder[$i]['policyholder_id'] == $ownerships[$bd][$j]['policyholder_id']) {

		$policyholder[$i]['aggregate_top_slice'] += round((($results[$bd]['top_slice'] / 100) * $ownerships[$bd][$j]['percentage']),2, PHP_ROUND_HALF_EVEN);


		$policyholder[$i]['aggregate_tax_credit'] += round((($results[$bd]['tax_credit'] / 100) * $ownerships[$bd][$j]['percentage']),2, PHP_ROUND_HALF_EVEN);

		}

		if($tar[$i]['top_slicing_factor'] != 1) {

		$t1 += $tar[$i]['top_slicing_factor'] * ($ownerships[$bd][$j]['percentage_split'] / 100);
		}
		}

	}

	if($t1 > 0) { $total_years[$bd] = $t1; }



			
		
	}


	for($i=0; $i < count($policyholder); $i++) {

		if($policyholder[$i]['aggregate_top_slice'] <= 0){

			$policyholder[$i]['aggregate_years'] == 0;

		} else {

			$policyholder[$i]['aggregate_years'] = round(($policyholder[$i]['aggregate_gain'] / $policyholder[$i]['aggregate_top_slice']), 3);

		}


		if($policyholder[$i]['aggregate_gain'] <= 0) {

			$policyholder[$i]['aggregate_slice_tax_credit'] = 0;

		} else {

		$policyholder[$i]['aggregate_slice_tax_credit'] = round((($policyholder[$i]['aggregate_tax_credit'] / $policyholder[$i]['aggregate_gain']) * $policyholder[$i]['aggregate_top_slice']),2, PHP_ROUND_HALF_EVEN);

		}

		$policyholder[$i]['taxation'] = Report::calculate_additional_tax($tax_rates, $policyholder[$i]['gross_income'], $policyholder[$i]['allowances'], $policyholder[$i]['aggregate_top_slice'] );


		$policyholder[$i]['final_tax'] = round(($policyholder[$i]['taxation']['total_tax'] - $policyholder[$i]['aggregate_slice_tax_credit']) * $policyholder[$i]['aggregate_years'],2, PHP_ROUND_HALF_EVEN);

		$policyholder[$i]['top_slicing_relief'] = Report::calculate_top_slicing_relief($tax_rates, $policyholder[$i]['aggregate_gain'], $policyholder[$i]['gross_income'], $policyholder[$i]['allowances'], $policyholder[$i]['final_tax']);

		$policyholder[$i]['corresponding_deficiency_relief'] = Report::calculate_deficiency_relief($tax_rates, $policyholder[$i]['gross_income'], $policyholder[$i]['allowances'], $policyholder[$i]['taxation'], $policyholder[$i]['deficiency'], $policyholder[$i]['aggregate_gain']);

		$policyholder[$i]['age_allowance'] = Report::calculate_age_allowance($tax_rates, $policyholder[$i]['gross_income'], $policyholder[$i]['allowances'], $policyholder[$i]['taxation'], $policyholder[$i]['age'], $policyholder[$i]['aggregate_gain']);

		$policyholder[$i]['pension_premiums'] = Report::calculate_pension_premiums($tax_rates, $policyholder[$i]['gross_income'], $policyholder[$i]['allowances'], $policyholder[$i]['age'], $policyholder[$i]['taxation']['slice_higher_rate'] , $policyholder[$i]['final_tax']);

			}



	return array('bond' => $bond, 'policyholder' => $policyholder, 'ownerships' => $ownerships, 'results' => $results, 'tax_year' => $tax_year, 'segment_blocks' => $segment_blocks, 'commencement_date' => $commencement_date, 'encashment_date' => $encashment_date, 'increment_details' => $increment_details, 'encashment_details' => $encashment_details, 'policy_loans' => $policy_loans, 'total_investment' => $total_investment, 'cumulative_allowance' => $cumulative_allowance, 'excess' => $excess, 'total_excess' => $total_excess, 'total_withdrawals' => $total_withdrawals, 'total_years' => $total_years);


}



}
