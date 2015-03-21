<?php

namespace App\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;
use \Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Session;

class Withdrawals extends Eloquent {

	protected $table = 'withdrawals';
	protected $fillable = array('id', 'bond_id', 'year_ending', 'withdrawal_amount', 'withdrawal_percentage', 'segment_start', 'segment_end', 'user_id', 'created_at', 'updated_at');


	public static function reset_array($arr) {

		$cc = 0;
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


	public static function calculate_years($end_year,$startYear,$endMonth,$startMonth,$endDay,$startDay) {
	
		$calc = null;

		$calc = $end_year - $startYear;	

		if($endMonth > $startMonth) {$calc++;}

		if ($endDay == $startDay && $endMonth == $startMonth) {$calc++;}
		if($endDay == 28 && $startDay == 29 && $startMonth == 2 && $endMonth == 2) {$calc++;}

	return ($calc);


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

				if($encashment_details[$j]['year'] == $i) {

					$segments -= $encashment_details[$j]['segments_encashed'];

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



	        public static function calculate_chargeable_event($segment, $withdrawal, $policy_loans, $segment_break_points, $start_year, $increment, $initial_segments, $encashment_details,  $cumulative_allowance, $total_years, $allowance, $final_year)  {

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

				if($encashment_details[$j]['year'] == $i) {


					$segments -= $encashment_details[$j]['segments_encashed'];

				}

			}	

	if($segment <= ($initial_segments - 1)) {
	for($z=0; $z < count($increment); $z++) {

		if($increment[$z]['increment_segments'] == 0 && $increment[$z]['year'] == $i) {
	
					$allowance += ($increment[$z]['segments_annual_allowance'] / $initial_segments);
	
					}

				if($increment[$z]['increment_segments'] == 0 && $increment[$z]['end_year'] == $i) {
	
					$allowance -= ($increment[$z]['segmentsAnnualAllowance'] / $initial_segments);
	
					}

					}
				}

			$pL = $policy_loans[$i];

			if($policy_loans[$i] > 0) {$pL = $policy_loans[$i] / $segments;}


                        if($i == 0) {



                                if((($withdrawal[$i]['withdrawal_amount'] / $segments ) + $pL) > $allowance){

                                        $rows[$i] = "Yes";
                                } else {

                                        $rows[$i] = "No";
                                }

			}
		       
			
		
			else {


                                        if((($withdrawal[$i]['withdrawal_amount'] / $segments) + $pL) > ($allowance + $cumulative_allowance[$i])) {
                                                $rows[$i] = "Yes";

                                        } else {

                                                $rows[$i] = "No";

                                        }

                                }

			if ($start_year > $i) {

                                $rows[$i] = "No";

			}

		if ($final_year == 2 && $i == $total_years - 2) { $rows[$i] = "No";}
                elseif ($final_year == 2 && $i == $total_years - 1) { $rows[$i] = "No";}
                elseif ($final_year == 1 && $i == $total_years - 1) { $rows[$i] = "No";}


                        

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

				if($encashment_details[$j]['year'] == $i) {


					$segments -= $encashment_details[$j]['segments_encashed'];

				}


			}	


	if($segment <= $initial_segments) {
	for($z=0; $z < count($increment); $z++) {

			if($increment[$z]['increment_segments'] == 0 && $increment[$z]['year'] == $i) {
	
					$allowance += ($increment[$z]['segments_annual_allowance'] / $initial_segments);
	
					}

				if($increment[$z]['increment_segments'] == 0 && $increment[$z]['end_year'] == $i) {
	
					$allowance -= ($increment[$z]['segments_annual_allowance'] / $initial_segments);
	
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


	public static function calculate_cumulative_allowance_extended($segment, $withdrawal, $policy_loans, $segment_break_points, $segment_match, $match,  $segments, $encashment_details,  $total_years, $segment_start, $segment_end, $temp, $seg_count)  {

                $rows =  array();

		$start_year = $segments[$segment]['year'];

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
			else {  
				if($i == $start_year && $start_year > 0) {


					$rows[$i - 1] = 0;

					}


				if($policy_loans[$i - 1] > 0) {$pL = $policy_loans[$i - 1] / $seg_count;}

					if(isset($withdrawal[$i-1]['withdrawal_percentage']) && $withdrawal[$i-1]['withdrawal_percentage'] > 0) {

					$rows[$i] = $rows[$i - 1] + (($segments[$segment]['segment_amount'] * 0.05) - (($segments[$segment]['segment_amount'] / 100) * $withdrawal[$i - 1]['withdrawal_percentage'])) + $pL;

				} else {

				$rows[$i] = $rows[$i - 1] + ($segments[$segment]['segment_annual_allowance'] - ($withdrawal[$i - 1]['withdrawal_amount'] / $seg_count[$i-1]) + $pL);


			}

		}

		//if($start_year > 0 && isset($rows[$start_year - 1])) { unset($rows[$start_year - 1]); }
			if($rows[$i] < 0) {$rows[$i] = 0;}
          	}

                return $rows;

	}


	        public static function calculate_chargeable_event_extended($segment, $segments, $withdrawal, $policy_loans, $segment_break_points, $start_year, $encashment_details,  $cumulative_allowance, $total_years, $allowance, $final_year, $seg_count, $segment_start, $segment_end)  {

                $rows = array();
		$year  = 0;


                for($i = $start_year; $i < $total_years; $i++) {



			$pL = $policy_loans[$i];

			if($policy_loans[$i] > 0) {$pL = $policy_loans[$i] / $seg_count[$i];}


                        if($i == $start_year) {
		
			if(isset($withdrawal[$i]['withdrawal_percentage']) && $withdrawal[$i]['withdrawal_percentage'] > 0) {
				if((($segments[$segment]['segment_amount'] / 100) * $withdrawal[$i]['withdrawal_percentage']) + $pL > $allowance){
                                        $rows[$i] = "Yes";
                                } else {

                                        $rows[$i] = "No";
                                }

			}  else { if((($withdrawal[$i]['withdrawal_amount'] / $seg_count[$i]) + $pL) > $allowance){
                                        $rows[$i] = "Yes";
                                } else {

                                        $rows[$i] = "No";
                                }
				}
			}
		       
			
		
			else {
				
				if(isset($withdrawal[$i]['withdrawal_percentage']) && $withdrawal[$i]['withdrawal_percentage'] > 0) {
				if((($segments[$segment]['segment_amount'] / 100) * $withdrawal[$i]['withdrawal_percentage']) + $pL > ($allowance + $cumulative_allowance[$i])){
	if((($segments[$segment]['segment_amount'] / 100) * $withdrawal[$i - 1]['withdrawal_percentage']) > $allowance){
                                        $rows[$i] = "Yes";
                                } else {

                                        $rows[$i] = "No";
                                }

				}
			       
				}	else {

                                        if((($withdrawal[$i]['withdrawal_amount'] / $seg_count[$i]) + $pL) > ($allowance + $cumulative_allowance[$i])) {
                                                $rows[$i] = "Yes";

                                        } else {

                                                $rows[$i] = "No";

                                        }
					
					}

                                }

			if ($start_year > $i) {

                                $rows[$i] = "No";

			}

		if ($final_year == 2 && $i == $total_years - 2) { $rows[$i] = "No";}
                elseif ($final_year == 2 && $i == $total_years - 1) { $rows[$i] = "No";}
                elseif ($final_year == 1 && $i == $total_years - 1) { $rows[$i] = "No";}


                        

		}

                return $rows;
        }



	public static function calculate_excess_extended($segment, $segments, $withdrawal, $policy_loans, $segment_break_points, $start_year,  $encashment_details,  $cumulative_allowance, $total_years, $allowance, $final_year, $seg_count, $segment_start, $segment_end)  {

		$rows = array();
		$year  = 0;

//		$seg_count = $segment_end - $segment_start + 1;

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

				if($encashment_details[$j]['year'] == $i) {


					$segments -= $encashment_details[$j]['segments_encashed'];

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



	public static function populate_withdrawal_percentages ($bond_id, $user_id, $start_year, $total_years, $pointer, $investment, $percentage, $increment, $encashment, $segments, $segment_break_points, $segment_start, $segment_end, $mode) {

		$i = 0;
		$segment_match = array();

		for($x = 0; $x < count($segment_break_points); $x++) {

		$segment_match[$x] = array_intersect(range($segment_break_points[$x]['segment_start'], $segment_break_points[$x]['segment_end']), range($segment_start, $segment_end)); // Check for matching segments

			if(!empty($segment_match[$x])) {
				
				$segment_match[$x] = Withdrawals::reset_array($segment_match[$x]);
			}



		}


	for($a = ($start_year + $pointer); $a < ($start_year + $total_years); $a++) {

	


			//////////////////////////////////////////////
			//
			//	Loop through increments and encashments
			//	If found, adjust the annual allowance
			//
			/////////////////////////////////////////////




			for($z=0; $z < count($increment); $z++) {

				if(isset($increment[$z]['increment_amount'])) {

				if($increment[$z]['year'] == $i) {
	
					$investment += $increment[$z]['increment_amount'];
	
					}

				if($increment[$z]['end_year'] + 1 == $i) {
	
					$investment -= $increment[$z]['increment_amount'];
	
					}
			}
			}


			

			for($z=0; $z < count($encashment); $z++) {

				if(isset($encashment[$z]['seg_value'])) {

				if($encashment[$z]['year'] + 1 == $a) {
	
					$investment -= $encashment[$z]['seg_value'];
	
					}

				}

			}

	$wa = 0;


	if($mode == 1) {
		
		for($h = $segment_start; $h <= $segment_end; $h++) {


		for($l = 0; $l < count($segment_break_points); $l++) {

			$sm = $segment_match[$l];

			if($a >= ($segment_break_points[$l]['year'] + $start_year) && in_array($h, $sm)) {


			$wa += round(($segments[$h - 1]['segment_amount'] / 100) * $percentage, 6, PHP_ROUND_HALF_EVEN);
			}
		}	
		}
	}
		else { $wa = round(($investment / 100) * $percentage, 6, PHP_ROUND_HALF_EVEN); }


	$withdrawal = new Withdrawals;
		
                $withdrawal->bond_id = $bond_id;
                $withdrawal->year_ending = $a;
                $withdrawal->withdrawal_amount = $wa;
                $withdrawal->withdrawal_percentage = $percentage;
		$withdrawal->segment_start = $segment_start;
		$withdrawal->segment_end = $segment_end;
                $withdrawal->user_id = $user_id;
		$withdrawal->save();
		$withdrawal->touch();

	
		$i++;
	}
	
		return TRUE;
	}



	public static function populate_withdrawal_fixed($bond_id, $user_id, $start_year, $total_years, $pointer, $fixed, $segment_start, $segment_end) {

	for($a = ($start_year + $pointer); $a < ($start_year + $total_years); $a++) {
		
		$withdrawal = new Withdrawals;
		
                $withdrawal->bond_id = $bond_id;
                $withdrawal->year_ending = $a;
                $withdrawal->withdrawal_amount = round($fixed, 6, PHP_ROUND_HALF_EVEN);
                $withdrawal->withdrawal_percentage = 0;
		$withdrawal->segment_start = $segment_start;
		$withdrawal->segment_end = $segment_end;
                $withdrawal->user_id = $user_id;
		$withdrawal->save();
		$withdrawal->touch();

			}
	return TRUE;

		}


	public static function create_new_withdrawal($wdl, $bond_id, $user_id, $start_year, $total_years, $pointer, $segment_start, $segment_end) {

		 $counter = $pointer;

	for($a = ($start_year  + $pointer); $a < ($start_year + $total_years); $a++) {

	$withdrawal = new Withdrawals;
		
                $withdrawal->bond_id = $bond_id;
                $withdrawal->year_ending = $a;
                $withdrawal->withdrawal_amount = round($wdl[$counter], 6, PHP_ROUND_HALF_EVEN);
                $withdrawal->withdrawal_percentage = 0;
		$withdrawal->segment_start = $segment_start;
		$withdrawal->segment_end = $segment_end;
                $withdrawal->user_id = $user_id;
		$withdrawal->save();
		$withdrawal->touch();

		$counter++;

		}

			 return TRUE;
		}



public static function load_withdrawal_details($bond_id, $user_id)
    {

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
                $withdrawals[$counter]['withdrawal_amount'] = round($output->withdrawal_amount, 2, PHP_ROUND_HALF_EVEN);
                $withdrawals[$counter]['withdrawal_percentage'] = round($output->withdrawal_percentage, 2, PHP_ROUND_HALF_EVEN);
                $counter++;

                }

        }


    return $withdrawals;

    }


	public static function load_withdrawal_details_extended($bond_id, $user_id, $segment_start, $segment_end)
    		{

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
                $withdrawals[$counter]['withdrawal_amount'] = round($output->withdrawal_amount, 2, PHP_ROUND_HALF_EVEN);
                $withdrawals[$counter]['withdrawal_percentage'] = round($output->withdrawal_percentage, 2, PHP_ROUND_HALF_EVEN);
                $withdrawals[$counter]['segment_start'] = $output->segment_start;
                $withdrawals[$counter]['segment_end'] = $output->segment_end;
                $counter++;

                }

        }


    return $withdrawals;

    }

public static function create_new_segment_block($bond_id, $user_id, $segment_start, $segment_end, $start_year, $total_years) {
		
		$segment = array();
		$segment_block = 0;
		$segment_start_year = 0;
		$segment_break_points = Withdrawals::check_segments($bond_id, $user_id);


		for($i = 0; $i < count($segment_break_points); $i++) {

			$segment_match[$i] = array_intersect(range($segment_break_points[$i]['segment_start'], $segment_break_points[$i]['segment_end']), range($segment_start, $segment_end)); // Check for matching segments

			if(!empty($segment_match[$i])) {
			
				
				$segment_match[$i] = Withdrawals::reset_array($segment_match[$i]);
				$q = count($segment_match[$i]) - 1;

				for($z = $segment_match[$i][0]; $z <= $segment_match[$i][$q]; $z++) {

					if($z == $segment_start) {$segment_start_year = $segment_break_points[$i]['year']; }

				}

//				$match = range($segment_break_points[$i]['segment_start'], $segment_break_points[$i]['segment_end']);
//				
//				for($h = $segment_match[$i][0]; $h <= $segment_match[$i][$q]; $h++) {
//					
//					if(in_array($segment_match[$i][$h], $match)) { $segment[$h]['segment'] = $segment_match[$i][$h]; $segment[$h]['year'] = $segment_break_points[$i]['year']; }
//					
//
//				}
				

			}

		}

		 $counter = 0;

	for($a = ($start_year + $segment_start_year); $a < ($start_year + $total_years); $a++) {

	$withdrawal = new Withdrawals;
		
                $withdrawal->bond_id = $bond_id;
                $withdrawal->year_ending = $a;
                $withdrawal->withdrawal_amount = 0;
                $withdrawal->withdrawal_percentage = 0;
		$withdrawal->segment_start = $segment_start;
		$withdrawal->segment_end = $segment_end;
                $withdrawal->user_id = $user_id;
		$withdrawal->save();
		$withdrawal->touch();

		$counter++;

		}

		$blocks = Withdrawals::load_segment_blocks($bond_id, $user_id);

		for($q = 0; $q < count($blocks); $q++) {

			if($blocks[$q]['segment_start'] == $segment_start) {$segment_block = $q;} 
		}	

		$temp = array('start_year' => ($start_year + $segment_start_year), 'segment_block' => $segment_block);

			 return $temp;
		}



   public static function load_segments($bond_id, $user_id)
    {

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

public static function validate_segments($segment_start, $segment_end, $segment_blocks) {

	$error = 0;

	if(!isset($segment_blocks[0]['start'])) {
	foreach($segment_blocks as $id=>$output) {

	$a = range($output['segment_start'], $output['segment_end']);	
	$b = range($segment_start, $segment_end);	

	$r = array_intersect($a, $b);

	if(count($r) > 0) { $error = 1; }

	}
	}

	return $error;
	
}




public static function load_increment_details($bond_id, $user_id, $cDay, $cMonth, $cYear)
    {

        $increments= array();
        $counter = 0;

	$ic = DB::table('increments')
	        ->where('increments.user_id','=', $user_id)
	        ->where('increments.bond_id', '=', $bond_id)
	        ->select('id', 'increment_amount', 'increment_commencement_date', 'increment_segments')
	        ->orderBy('increment_commencement_date', 'asc')
		->distinct()
		->get();

	if (count($ic > 0)) {
     	   foreach ($ic as $id => $output) {

                $increments[$counter]['id'] = $output->id;
                $increments[$counter]['increment_segments'] = $output->increment_segments;
                $increments[$counter]['increment_amount'] = $output->increment_amount;
                $increments[$counter]['increment_annual_allowance'] = ($output->increment_amount / 100.0) * 5.0;

		if($increments[$counter]['increment_segments'] > 0) {

			$increments[$counter]['segments_annual_allowance'] = (($output->increment_amount / $output->increment_segments) / 100.0) * 5.0;

		} else {

			$increments[$counter]['segments_annual_allowance'] = $increments[$counter]['increment_annual_allowance'];

		}

		$increment_commencement_date1 = preg_split('/[\/\.-]/',$output->increment_commencement_date);
		$increment_commencement_date = $increment_commencement_date1[2].'/'.$increment_commencement_date1[1].'/'.$increment_commencement_date1[0];

		$year =	$increment_commencement_date1[0] - $cYear;


		if($cMonth > $increment_commencement_date1[1]) { $year--; }

		if($cMonth == $increment_commencement_date1[1] && $cDay > $increment_commencement_date1[2]) { $year--; }


                $increments[$counter]['year'] = $year;
                $increments[$counter]['cmonth'] = $cMonth;
                $increments[$counter]['end_year'] = $year + 20;
                $counter++;

                }

        }

    return $increments;

    }



public static function load_encashment_details($bond_id, $user_id, $cDay, $cMonth, $cYear)
    {
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
                $encashments[$counter]['seg_value'] = ($output->investment / $output->segments) * count(range($output->segment_start, $output->segment_end));
                $encashments[$counter]['segments_annual_allowance'] = ($encashments[$counter]['seg_value'] / 100.0) * 5.0;
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


public static function load_policy_loans($bond_id, $user_id, $cDay, $cMonth, $cYear)
    {

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



public static function load_policy_loan_years($bond_id, $user_id, $policy_loans, $total_years)
    {

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



public static function display_withdrawals($bond_id, $user_id, $sb) {

                $investment = 0;
		$selected_block = 0;
		$allowance = array();
                $user_data = array();
                $segmented_cumulative_allowance = array();
                $segmented_chargeable_event = array();
		$segment_blocks = array();
		$segment_match = array();
                $segmented_excess = array();
                $withdrawal_details = array();
		$cumulative_allowance = array();
		$chargeable_event = array();
		$excess = array();
		$pointer = 0;

//ref::config('expLvl', 5);
		$bond = DB::table('bonds')->where('id', $bond_id)->where('user_id', $user_id)->first(); 

		$segments = Withdrawals::load_segments($bond_id, $user_id);


		$segment_break_points = Withdrawals::check_segments($bond_id, $user_id);

	$segment_start = 1;
	$segment_end =  count($segments);

	
	Session::put('bond', $bond);
	Session::put('segments', $segments);
	Session::put('segment_break_points', $segment_break_points);
	Session::put('total_segments', count($segments));
	
	if($bond->mode == 1) {

		$segment_blocks = Withdrawals::load_segment_blocks($bond_id, $user_id);
	
		for($i = 0; $i < count($segment_break_points); $i++) {

			$segment_match[$i] = Withdrawals::reset_array(array_intersect(range($segment_break_points[$i]['segment_start'], $segment_break_points[$i]['segment_end']), range($segment_blocks[$sb]['segment_start'], $segment_blocks[$sb]['segment_end']))); // Check for matching segments

		}

		$withdrawal_details = Withdrawals::load_withdrawal_details_extended($bond_id, $user_id, $segment_blocks[$sb]['segment_start'], $segment_blocks[$sb]['segment_end']);
			
	} else {


		$withdrawal_details = Withdrawals::load_withdrawal_details($bond_id, $user_id);
	}

	$annual_allowance = 0;


	 	$annual_allowance = ($bond->investment / 100) * 5.0;

		//////////////////////////////////////////////////
	 	//
	 	// Work out how long the bond has been in force...
	 	//
	 	// Also, work out the allowances
	 	//
		//////////////////////////////////////////////////

                $commencement_date  = preg_split('/[\/\.-]/', $bond->commencement_date);

                $encashment_date  = preg_split('/[\/\.-]/', $bond->encashment_date);

		// Find out the start date of the final policy year

		$start_date_final_policy_year = Withdrawals::start_date_final_policy_year($commencement_date, $encashment_date);

		$final_year = $start_date_final_policy_year[3];

		$total_years = Withdrawals::calculate_years($encashment_date[0], $commencement_date[0],$encashment_date[1], $commencement_date[1], $encashment_date[2], $commencement_date[2]);
		

		$start_year = $commencement_date[0] + 1;

		if($commencement_date[2] == '01' && $commencement_date[1] == '01') {

			$start_year = $commencement_date[0];
		
		}

	/////////////////////////////////////////////////////////////////////////////////////
	////// 	Check if there have been any increments or encashments, and factor them in...
	/////////////////////////////////////////////////////////////////////////////////////
		
		$policy_loans = Withdrawals::load_policy_loans($bond_id, $user_id, $commencement_date[2], $commencement_date[1], $commencement_date[0]);


	$policy_loan_years = Withdrawals::load_policy_loan_years($bond_id, $user_id, $policy_loans, $total_years);


		$increment_details = Withdrawals::load_increment_details($bond_id, $user_id, $commencement_date[2], $commencement_date[1], $commencement_date[0]);

		$encashment_details = Withdrawals::load_encashment_details($bond_id, $user_id, $commencement_date[2], $commencement_date[1], $commencement_date[0]);

		if($bond->mode == 0) { $allowance = Withdrawals::calculate_allowance($total_years, $annual_allowance, $increment_details, $encashment_details); } 

		elseif($bond->mode == 1) { $allowance = Withdrawals::calculate_allowance_extended($total_years, $segments, $segment_break_points, $segment_match, $segment_blocks[$sb]['segment_start'], $segment_blocks[$sb]['segment_end'], $increment_details, $encashment_details); }

	if(isset($withdrawal_details[0]['year_ending'])) {
		 while ($withdrawal_details[0]['year_ending'] != $start_year) {

			if($withdrawal_details[0]['year_ending'] > $start_year) {
			 
				array_unshift($withdrawal_details, array('year_ending' => $withdrawal_details[0]['year_ending'] - 1, 'withdrawal_amount' => 0, 'withdrawal_percentage' => 0, 'id' => 0));

			}

			if($withdrawal_details[0]['year_ending'] < $start_year)  {

				array_shift($withdrawal_details);	

			}


		} 

	}

			for($z=0; $z < $total_years; $z++) {

				if(!isset($withdrawal_details[$z]['withdrawal_amount'])) {
					$withdrawal_details[$z]['withdrawal_amount'] = 0;
					$withdrawal_details[$z]['withdrawal_percentage'] = 0;
				
				}

			}

	$withdrawal = array();

	if($bond->mode == 0) {

	for($i = 0; $i < count($segments); $i++) {

			  $cumulative_allowance[$i] = Withdrawals::calculate_cumulative_allowance($i, $withdrawal_details, $policy_loan_years,  $segment_break_points, $segments[$i]['year'], $increment_details, $bond->segments, $encashment_details, $total_years, $segments[$i]['segment_annual_allowance']);

		$chargeable_event[$i] = Withdrawals::calculate_chargeable_event($i, $withdrawal_details, $policy_loan_years,  $segment_break_points, $segments[$i]['year'], $increment_details, $bond->segments, $encashment_details, $cumulative_allowance[$i], $total_years, $segments[$i]['segment_annual_allowance'], $start_date_final_policy_year[3]);


		$excess[$i] = Withdrawals::calculate_excess($i, $withdrawal_details, $policy_loan_years, $segment_break_points, $segments[$i]['year'], $increment_details, $bond->segments, $encashment_details, $cumulative_allowance[$i], $total_years, $segments[$i]['segment_annual_allowance'], $start_date_final_policy_year[3] );

	}


	} elseif($bond->mode == 1) {
		
		$j = $segment_blocks[$sb]['segment_start'] - 1;
		$temp_allowance = array_fill($j, $total_years, 0); // temporary array

		$match = array_fill($segment_blocks[$sb]['segment_start'] - 1,  ($segment_blocks[$sb]['segment_end'] - ($segment_blocks[$sb]['segment_start'] - 1)), 0); // temporary array
		
		$t = 0; // warning variable to flag up new segment start point

		$seg_count = array();

		if(count($segment_match > 0)) {
			
			for($i = 1; $i < count($segment_match); $i++) { if(!empty($segment_match[$i])) {$z = ($segment_match[$i][0]) - 1; $match[$z] = $z;} }

		}	

	
		$percentage_used = false;

		if($withdrawal_details[0]['withdrawal_percentage'] > 0) { $percentage_used = true; }
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
//r($segment_blocks);~r($segment_match);
	for($i = $segment_blocks[$sb]['segment_start'] - 1; $i <= $segment_blocks[$sb]['segment_end'] - 1; $i++) {

		$year = $segments[$i]['year'];	
		for($l = 0; $l < count($segment_match); $l++) {

			$sm = $segment_match[$l];
			if(in_array(($i + 1), $sm)) { $seg_count[$i][$year] = count($sm);  }		
			if($year == 0) { $seg_start = $seg_count[$i][$year]; }

		}

		for($m = $year; $m < $total_years; $m++) {

		if($percentage_used == false) {

			if(!isset($seg_count[$i - 1][$m - 1])) { $seg_count[$i - 1][$m - 1] = 0;  }

			if($year > 0 && $m == $year) { $seg_count[$i][$m] += $seg_start; $seg_count[$i][$m - 1] = $seg_count[$i - 1][$m - 1];  }	
		if($m > $year) {  $seg_count[$i][$m] = $seg_count[$i][$m - 1]; 	} 

			for($n = 0; $n < count($segment_break_points); $n++) {

			if($m > $year && $n > 0 && $m == $segment_break_points[$n]['year']) {  $seg_count[$i][$m] +=  $segment_break_points[$n]['segments'];  } 

		}

			for($j = $segment_blocks[$sb]['segment_start'] - 1; $j <= $segment_blocks[$sb]['segment_end'] - 1; $j++) {

				if(isset($encashment_details[$j])) {
				if($encashment_details[$j]['year'] == $i) {

					$seg_count[$i][$m] -= $encashment_details[$j]['segments_encashed'];
					}
				}

			}
					
		} else {
			if($m > $year && $i == $match[$i]) { $t = $i + 1;  $seg_count[$i][$m] = Withdrawals::count_segments($t, $segment_match);    } 
			elseif($m > $year && $i != $match[$i]) {  $seg_count[$i][$m] = $seg_count[$i][$m - 1]; }

			for($j = $segment_blocks[$sb]['segment_start'] - 1; $j <= $segment_blocks[$sb]['segment_end'] - 1; $j++) {

				if(isset($encashment_details[$j])) {
				if($encashment_details[$j]['year'] == $i) {

					$seg_count[$i][$m] -= $encashment_details[$j]['segments_encashed'];
					}
				}

			}
		}
		}

	}

	//var_dump($seg_count);exit;	
		//////////////////////////////////////
		//
		//	Work out the CA's, Excess, etc...
		//
		///////////////////////////////////////

	for($i = $segment_blocks[$sb]['segment_start'] - 1; $i <= $segment_blocks[$sb]['segment_end'] - 1; $i++) {

	  	$cumulative_allowance[$i] = Withdrawals::calculate_cumulative_allowance_extended($i, $withdrawal_details, $policy_loan_years,  $segment_break_points, $segment_match, $match, $segments, $encashment_details, $total_years,  $segment_blocks[$sb]['segment_start'], $segment_blocks[$sb]['segment_end'], $temp_allowance, $seg_count[$i]);

		$temp_allowance = $cumulative_allowance[$i];

		$chargeable_event[$i] = Withdrawals::calculate_chargeable_event_extended($i, $segments, $withdrawal_details, $policy_loan_years,  $segment_break_points, $segments[$i]['year'], $encashment_details, $cumulative_allowance[$i], $total_years, $segments[$i]['segment_annual_allowance'], $start_date_final_policy_year[3], $seg_count[$i], ['segment_start'], $segment_blocks[$sb]['segment_end']);

		$excess[$i] = Withdrawals::calculate_excess_extended($i, $segments, $withdrawal_details, $policy_loan_years, $segment_break_points, $segments[$i]['year'], $encashment_details, $cumulative_allowance[$i], $total_years, $segments[$i]['segment_annual_allowance'], $start_date_final_policy_year[3], $seg_count[$i],  $segment_blocks[$sb]['segment_start'], $segment_blocks[$sb]['segment_end']);

	}
	}

	if($bond->mode == 1) {

		// If in extended mode, determine the year where the current segment block begins

		$s1 = array(0 => $segment_blocks[$sb]['segment_start']);
		$sm = array();
		$ss = null;

		for($i = 0; $i < count($segment_break_points); $i++) {
		
			$match = range($segment_break_points[$i]['segment_start'], $segment_break_points[$i]['segment_end']);
			if(in_array($segment_blocks[$sb]['segment_start'], $match)) { $ss = $segment_break_points[$i]['year']; }

	
			$pointer = $ss;

		}
	}


	if($bond->mode == 0) {

		for($z=0; $z < $total_years; $z++) {

		$segmented_cumulative_allowance[$z] = 0;
		$segmented_chargeable_event[$z] = "No";
		$segmented_excess[$z] = 0;



		for($i=0; $i < count($segments); $i++) {

			$segmented_cumulative_allowance[$z] += $cumulative_allowance[$i][$z];

				if($chargeable_event[$i][$z] == "Yes") {

					$segmented_chargeable_event[$z] = "Yes";

				}

			$segmented_excess[$z] += $excess[$i][$z];

		}
		} 
	
	} elseif($bond->mode == 1) {

		for($z = $pointer; $z < $total_years; $z++) {

		$segmented_cumulative_allowance[$z] = 0;
		$segmented_chargeable_event[$z] = "No";
		$segmented_excess[$z] = 0;
		//print_r($cumulative_allowance);exit;

			for($i = ($segment_blocks[$sb]['segment_start'] - 1); $i <= ($segment_blocks[$sb]['segment_end'] - 1); $i++) {

			if(isset($cumulative_allowance[$i][$z])) {

			$segmented_cumulative_allowance[$z] += $cumulative_allowance[$i][$z];

			if(isset($chargeable_event[$i][$z])) {

				if($chargeable_event[$i][$z] == "Yes") {

					$segmented_chargeable_event[$z] = "Yes";

				}
			}
				
			if(isset($excess[$i][$z])) {
				$segmented_excess[$z] += $excess[$i][$z];

				}
			}

			}
			}

	}

unset($cumulative_allowance);

	if($bond->mode == 0) {


                for($i=0; $i < $total_years; $i++) {


                        $cumulative_allowance[$i] = $allowance[$i] + $segmented_cumulative_allowance[$i];
                        $chargeable_event[$i] = $segmented_chargeable_event[$i];
                        $excess[$i] = $segmented_excess[$i];

                }

	} elseif ($bond->mode == 1) {

                for($i = $pointer; $i < $total_years; $i++) {

                        if($i == $pointer) {$cumulative_allowance[$i] = $allowance[$pointer]; } else { $cumulative_allowance[$i] = $allowance[$pointer]  + $segmented_cumulative_allowance[$i]; }
                        $chargeable_event[$i] = $segmented_chargeable_event[$i];
                        $excess[$i] = $segmented_excess[$i];

		}

	}

//r($withdrawal_details);r($allowance);r($segmented_cumulative_allowance);exit;	

	return array('bond' => $bond, 'mode' => $bond->mode, 'segment_blocks' => $segment_blocks,'segment_start' => $segment_start, 'segment_end' => $segment_end, 'total_years' => $total_years, 'final_year' => $final_year, 'start_year' => $start_year, 'policy_loan_years' => $policy_loan_years, 'allowance' => $allowance, 'cumulative_allowance' => $cumulative_allowance, 'chargeable_event' => $chargeable_event, 'excess' => $excess, 'withdrawal_details' => $withdrawal_details, 'increment_details' => $increment_details, 'encashment_details' => $encashment_details, 'segments' => $segments, 'pointer' => $pointer);

}


}
