<?php

namespace App\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;
use \Illuminate\Support\Facades\DB;
use DateTime;

class Increments extends Eloquent {

	protected $table = 'increments';
	protected $fillable = array('id', 'bond_id', 'increment_amount', 'increment_commencement_date', 'increment_segments', 'user_id', 'created_at', 'updated_at');


	    public static function create_new_increment($input, $bond_id, $user_id) {


		$increment = new Increments;
		$increment_commencement_date = null;

		if($input['increment_commencement_date'] != "") {

		$ad = preg_split('/[\/\.-]/', $input['increment_commencement_date']);

                $increment_commencement_date = $ad[2].$ad[1].$ad[0];

	    } else {$increment_commencement_date = "00000000";}

		$increment->bond_id = $bond_id;
		$increment->increment_amount = round($input['increment_amount'], 6, PHP_ROUND_HALF_EVEN);
		$increment->increment_commencement_date = $increment_commencement_date;
		$increment->increment_segments = $input['increment_segments'];
	        $increment->user_id = $user_id;

		$increment->save();
		$increment->touch();

	 	$segment_amount  = 0;

		if ($input['increment_segments'] > 0) {
			
			
			$segment_amount = round($input['increment_amount'] / $input['increment_segments'], 6, PHP_ROUND_HALF_EVEN);

 	
			$increment_id = DB::table('increments')->where('user_id', $user_id)->where('bond_id', $bond_id)->where('increment_amount', $input['increment_amount'])->where('increment_segments', $input['increment_segments'])->pluck('id');

			$ss = DB::table('segments')->where('user_id', $user_id)->where('bond_id', $bond_id)->where('increment_id', 0)->lists('id');

			$iv = DB::table('bonds')->where('user_id', $user_id)->where('id', $bond_id)->pluck('investment');

			$ep = DB::table('bonds')->where('user_id', $user_id)->where('id', $bond_id)->pluck('encashment_proceeds');

			$aus = DB::table('bonds')->where('user_id', $user_id)->where('id', $bond_id)->pluck('auto_update_segments');
		 		
		  $date = new \DateTime;

		if($aus == 0) {
			
			for($z = 0; $z < $input['increment_segments']; $z++) {

			DB::table('segments')->insert(array('bond_id' => $bond_id, 'segment_amount' => $segment_amount, 'encashment_proceeds' => $segment_amount, 'increment_id' => $increment_id, 'user_id' => $user_id, 'created_at' => $date, 'updated_at' => $date));  

			} 
		
		} elseif($aus == 1) {

			$segments = count($ss) + $input['increment_segments'];

     		  	$encashment_proceeds = round($ep / $segments, 6, PHP_ROUND_HALF_EVEN);

			$original_segments = $iv / $segments;

			
			for($z = 0; $z < count($ss); $z++) {

			DB::table('segments')->where('bond_id', $bond_id)->where('user_id', '=', $user_id)->where('increment_id', 0)->where('id', $ss[$z])->update(array('encashment_proceeds' => $encashment_proceeds));  

			}

			for($z = 0; $z < $input['increment_segments']; $z++) {

			DB::table('segments')->insert(array('bond_id' => $bond_id, 'segment_amount' => $segment_amount, 'encashment_proceeds' => $encashment_proceeds, 'increment_id' => $increment_id, 'user_id' => $user_id, 'created_at' => $date, 'updated_at' => $date));  

			} 

		}


    		}

	return TRUE;
    }



public static function load_increments($bond_id, $user_id) {
	
	$increments = array();	
	
	$ims = DB::table('increments')
		->where('increments.user_id', '=', $user_id)
		->where('increments.bond_id', '=', $bond_id)
		->select('increments.id AS id', 'increments.bond_id AS bond_id', 'increments.increment_amount AS increment_amount', 'increments.increment_commencement_date AS increment_commencement_date', 'increments.increment_segments AS segments')
		->orderBy('increments.increment_commencement_date')
		->distinct()
		->get();

	$counter = 0;

	foreach ($ims as $id => $output) {

		$increments[$counter]['id'] = $output->id;
		$increments[$counter]['bond_id'] = $output->bond_id;
		$increments[$counter]['increment_amount'] = $output->increment_amount;
		$increments[$counter]['increment_segments'] = $output->segments;
		
		if($increments[$counter]['increment_segments'] == 0) {$increments[$counter]['increment_segments'] = "Across all segments";}


		$increments[$counter]['increment_commencement_date'] = $output->increment_commencement_date;
		$counter++;

		}


            
    return $increments;

    }





 public static function delete_increments($user_id, $increment_id, $bond, $original_segments, $inc_seg) {


	$segments = array();
	$higherSegments = array();
	$allSegments = array();
	$ownership = array();
	$incrementRange = array();
	$segmentTimestamp = null;
	$counter = 0;
	$end = 0;

	/////////////////////////////////////////////
	//
	//	Firstly, check if the increment is applied accross all segments
	//
	//////////////////////////////////////////////
	
	if($inc_seg == 0) {

		DB::table('increments')->where('id', '=', $increment_id)->where('user_id', '=', $user_id)->where('bond_id', '=', $bond->id)->delete();


		DB::table('segments')->where('increment_id', '=', $increment_id)->where('user_id', '=', $user_id)->where('bond_id', '=', $bond->id)->delete();

		return TRUE;

	}

	$ss = DB::table('segments')
		->join('increments', 'increments.id', '=', 'segments.increment_id')
		->where('segments.user_id', '=', $user_id)
		->where('increments.user_id', '=', $user_id)
		->where('segments.bond_id', '=', $bond->id)
		->where('increments.bond_id', '=', $bond->id)
		->where('segments.increment_id', '=', $increment_id)
		->select('segments.segment_amount AS segment_amount', 'segments.id AS segment_id', 'segments.increment_id AS increment_id', 'increments.increment_commencement_date as tStamp')
		->orderBy('increments.increment_commencement_date', 'asc')
		->orderBy('segments.id', 'asc')
		->distinct()
                ->get();


	if (count($ss) > 0) {

	foreach ($ss as $id => $output) {

                 $segments[$counter]['segment_id'] = $output->segment_id;
                 $segments[$counter]['timestamp'] = strtotime($output->tStamp);
		 $segmentTimestamp = strtotime($output->tStamp);
		 $counter++;

		}	 

	}




	$counter = 0;

	$s1 = DB::table('segments')
		->where('user_id', '=', $user_id)
		->where('bond_id', '=', $bond->id)
		->where('increment_id', '=', 0)
		->select('id')
		//->select('segment_amount, segment_id');
		->orderBy('id', 'asc')
		->distinct()
		->get();

 	if (count($s1) > 0) {
	foreach ($s1 as $id => $output) {
		
		$allSegments[$counter]['segment_number'] = $counter + 1;
		$allSegments[$counter]['segment_id'] = $output->id;
                $allSegments[$counter]['timestamp'] = 0;
		$counter++;

		}
	}

	$s2 = DB::table('segments')
		->join('increments', 'increments.id', '=', 'segments.increment_id')
		->where('segments.user_id', '=', $user_id)
		->where('segments.bond_id', '=', $bond->id)
		->select('segments.segment_amount', 'segments.id AS segment_id', 'segments.increment_id', 'increments.increment_commencement_date as tStamp')
		->orderBy('increments.increment_commencement_date', 'asc')
		->orderBy('segments.id', 'asc')
		->distinct()
		->get();

 	if (count($s2) > 0) {
	foreach ($s2 as $id => $output) {
		
		$allSegments[$counter]['segment_number'] = $counter + 1;
		$allSegments[$counter]['segment_id'] = $output->segment_id;
                $allSegments[$counter]['timestamp'] = strtotime($output->tStamp);
		$counter++;

		}
	}

	for($a = 0; $a < count($segments); $a++) {

		for($b = 0; $b < count($allSegments); $b++) {

		if($segments[$a]['segment_id'] == $allSegments[$b]['segment_id']) {

			$segments[$a]['segment_number'] = $allSegments[$b]['segment_number'];

			}
	
		}
	}

//	echo count($segments); print_r($segments);exit;

	$end = count($segments) - 1; 
	$truncate = count($segments); // number of segment to reduce other segments by...
	$incrementRange = range($segments[0]['segment_number'], $segments[$end]['segment_number']);



	$counter = 0;

	for($i = 0; $i < count($allSegments); $i++) {

		if($allSegments[$i]['timestamp'] > $segmentTimestamp) {
	
			$higherSegments[$counter]['segment_number'] = $allSegments[$i]['segment_number'];
			$higherSegments[$counter]['segment_id'] = $allSegments[$i]['segment_id'];
			$higherSegments[$counter]['timestamp'] = $allSegments[$i]['timestamp'];

			$counter++;

		}	

	}

	$o1 = DB::table('ownerships')
		->where('user_id', '=', $user_id)
		->where('bond_id', '=', $bond->id)
		->select('id', 'segment_start', 'segment_end')
		->orderBy('segment_start', 'asc')
		->orderBy('segment_end', 'asc')
		->distinct()
		->get();

 	if (count($o1) > 0) {
	foreach ($o1 as $id => $output) {

		$range = range($output->segment_start, $output->segment_end);
		
		$ownership[$counter]['ownership_id'] = $output->id;
		$ownership[$counter]['segment_range'] = $range;
		$counter++;

		}
	}

	DB::table('increments')->where('id', '=', $increment_id)->where('user_id', '=', $user_id)->where('bond_id', '=', $bond->id)->delete();


	DB::table('segments')->where('increment_id', '=', $increment_id)->where('user_id', '=', $user_id)->where('bond_id', '=', $bond->id)->delete();


	for($i=0; $i < count($ownership); $i++) {


		$same = array_diff($ownership[$i]['segment_range'], $incrementRange);
		
		if(count($same) > 0) { sort($same); }

		$diff = array_intersect($ownership[$i]['segment_range'], $incrementRange);
		
		if(count($diff) > 0) { sort($diff); }

		$same_max = count($same) - 1;
		$diff_max = count($diff) - 1;


		if(count($same) == 0) {

			DB::table('ownerships')->where('id', '=', $ownership[$i]['ownership_id'])->where('user_id', '=', $user_id)->where('bond_id', '=', $bond->id)->delete();

		} elseif (count($diff) == 0 && $same[$same_max] < $incrementRange[0]) {


			// do nothing...


		} elseif (count($diff) == 0 && $same[0] > $incrementRange[$end])  {

			// update existing ownership data...

			$segment_start = ($same[0] - count($segments));
			$segment_end = ($same[$same_max] - count($segments));

			 $o3 = DB::table('ownerships')->where('bond_id', '=', $bond->id)->where('user_id', '=', $user_id)->where('id', $ownership[$i]['ownership_id'])->update(array('segment_start' => $segment_start));
			 $o3 = DB::table('ownerships')->where('bond_id', '=', $bond->id)->where('user_id', '=', $user_id)->where('id', $ownership[$i]['ownership_id'])->update(array('segment_end' => $segment_end));

			}

			elseif((count($same) > 0 && count($diff) > 0) && $same[0] < $diff[0] && $same[$same_max] > $diff[$diff_max]) {

			$segment_end = ($same[$same_max] - count($segments));

			 $o3 = DB::table('ownerships')->where('bond_id', '=', $bond->id)->where('user_id', '=', $user_id)->where('id', $ownership[$i]['ownership_id'])->update(array('segment_end' => $segment_end));

			}

		elseif((count($same) > 0 && count($diff) > 0) && $same[0] > $diff[$diff_max]) {
			
			$segment_end = ($same[$same_max] - count($segments));

			 $o3 = DB::table('ownerships')->where('bond_id', '=', $bond->id)->where('user_id', '=', $user_id)->where('id', $ownership[$i]['ownership_id'])->update(array('segment_end' => $segment_end));

					
		}


		elseif((count($same) > 0 && count($diff) > 0) && $same[$same_max] < $diff[0]) {

			$segment_end = ($same[$same_max] - count($segments));

			 $o3 = DB::table('ownerships')->where('bond_id', '=', $bond->id)->where('user_id', '=', $user_id)->where('id', $ownership[$i]['ownership_id'])->update(array('segment_end' => $segment_end));



		}

		}

	
	$inc = Increments::load_increments($bond->id, $user_id);

 	foreach($inc as $id => $output)  {

                        $original_segments += $output['increment_segments'];

                }

     		  $encashmentValue = round($bond->encashment_proceeds / $original_segments, 6, PHP_ROUND_HALF_EVEN);


		DB::table('segments')->where('bond_id', $bond->id)->where('user_id', $user_id)->update(array('encashment_proceeds' => $encashmentValue));

    return TRUE;

    }


//function load_bond_details($bondId, $userId)
//    {
//
//	$rows = array();	
//	$this->db->select('bondName, bondId, segments, encashmentProceeds');
//	$this->db->limit(1);
//	$this->db->where('bondId', $bondId);
//	$this->db->where('userId', $userId);
//	$query = $this->db->get('bondDetails');
//
// 	if ($query->num_rows() > 0) {
//	foreach ($query->result_array() as $row ) {
// 		foreach ($row as $col_name => $col_value) {
//		
//		$rows[$col_name] = $col_value;
//		}
//
//	}
//
//
//	}
//
//            
//    return $rows;
//
//    }
//
//
//  function get_bond_commencement_date($userId, $bondId) {
//
//        $date = array();
//
//        $this->db->select('commencementDate');
//        $this->db->limit(1);
//        $this->db->where('bondId', $bondId);
//	$this->db->where('userId', $userId);
//        $query = $this->db->get('bondDetails');
//
//	foreach ($query->result_array() as $row ) {
//                foreach ($row as $col_name => $col_value) {
//
//                $rows[$col_name] = $col_value;
//                }
//        }
//
//
//        $date = preg_split('/[\/\.-]/', $rows['commencementDate']);
//
//        return $date;
//
//
//    }
//
//
//    function get_bond_encashment_date($userId, $bondId) {
//
//        $date = array();
//
//        $this->db->select('encashmentDate, autoUpdate');
//        $this->db->limit(1);
//        $this->db->where('bondId', $bondId);
//	$this->db->where('userId', $userId);
//        $query = $this->db->get('bondDetails');
//
//        foreach ($query->result_array() as $row ) {
//                foreach ($row as $col_name => $col_value) {
//
//                $rows[$col_name] = $col_value;
//                }
//        }
//
//
// 	if($rows['autoUpdate'] == "1") {$rows['encashmentDate'] = date("Y").'-'.date("m").'-'.date("d");}
//        $date = preg_split('/[\/\.-]/', $rows['encashmentDate']);
//
//        return $date;
//
//
//    }


}
