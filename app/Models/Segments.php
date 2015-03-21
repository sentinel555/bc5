<?php

namespace App\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;
use \Illuminate\Support\Facades\DB;


class Segments extends Eloquent {

	protected $table = 'segments';
	protected $fillable = array('id', 'bond_id', 'segment_amount', 'encashment_proceeds', 'increment_id', 'user_id', 'created_at', 'updated_at');


public static function load_segments($bond_id, $user_id)
    {
		
	
	$seg = DB::table('segments')
                ->join('bonds', 'bonds.id', '=', 'segments.bond_id')
                ->where('segments.user_id', '=', $user_id)
                ->where('segments.bond_id', '=', $bond_id)
                ->where('segments.increment_id', '=', 0)
                ->select('segments.segment_amount AS segment_amount', 'segments.encashment_proceeds AS encashment_proceeds', 'segments.id AS id', 'bonds.segments AS segments', 'bonds.investment AS investment')
		->orderBy('segments.increment_id', 'asc')
                ->distinct()
                ->get();

		$counter = 0;
		$segments = array();

		foreach($seg as $id => $output) {

			$segments[$counter]['id'] = $output->id;	
			$segments[$counter]['segment_amount'] = $output->segment_amount;	
			$segments[$counter]['encashment_proceeds'] = $output->encashment_proceeds;	
			$segments[$counter]['segments'] = $output->segments;	
			$segments[$counter]['investment'] = $output->investment;	
			
			$counter++;

		}

		return $segments;
    }

	 public static function update_segments($input, $all_segments, $bond_id, $user_id, $seg_count) {
	//print_r($input['segment_amount']);print_r($input['encashment_proceeds']);exit;
	 	for($z = 0; $z < $seg_count; $z++)  {

		$segment = Segments::where('bond_id', '=', $bond_id)->where('user_id', '=', $user_id)->where('id', '=', $all_segments[$z]['id'])->first(); 

		$segment->segment_amount = $input['segment_amount'][$z];
		$segment->encashment_proceeds = $input['encashment_proceeds'][$z];

		$segment->save();
		$segment->touch();
		}
            
    return TRUE;

    }

public static function load_increments($bond_id, $user_id) { 

	$increments = array();
	$counter = 0;

	$inc = DB::table('increments')
		->where('increments.user_id', '=', $user_id)
		->where('increments.bond_id', '=', $bond_id)
		->select('increments.id AS id', 'increments.bond_id AS bond_id', 'increments.increment_amount AS increment_amount', 'increments.increment_segments AS increment_segments', 'increments.increment_commencement_date AS increment_commencement_date')
		->orderBy('increments.increment_commencement_date', 'asc')
		->distinct()
		->get();

	foreach ($inc as $id => $output) {
		
		$increments[$counter]['id'] = $output->id;
		$increments[$counter]['bond_id'] = $output->bond_id;
		$increments[$counter]['increment_amount'] = $output->increment_amount;
		$increments[$counter]['increment_segments'] = $output->increment_segments;
		$increments[$counter]['increment_commencement_date'] = $output->increment_commencement_date;

		$counter++;
		}

	return (isset($increments) && is_array($increments)) ? $increments : array();

    }


public static function load_segment_increments($bond_id, $user_id, $is) { 

	$increments = array();
	$inc = null;
	$counter = 0;

	if(count($is) > 0) {

	for($a=0; $a < count($is); $a++) {

	$inc = DB::table('segments')
		->join('increments', 'increments.id', '=', 'segments.increment_id')
		->where('increments.user_id', '=', $user_id)
		->where('increments.bond_id', '=', $bond_id)
		->where('segments.increment_id', '=', $is[$a]['id'])
		->select('segments.segment_amount AS segment_amount', 'segments.encashment_proceeds AS encashment_proceeds', 'segments.id AS id', 'segments.increment_id AS increment_id', 'increments.increment_commencement_date AS increment_commencement_date', 'increments.increment_amount AS increment_amount')
		->orderBy('increments.increment_commencement_date', 'asc')
		->distinct()
		->get();

	foreach ($inc as $id => $output) {

                $increments[$counter]['segment_amount'] = $output->segment_amount;
                $increments[$counter]['encashment_proceeds'] = $output->encashment_proceeds;
                $increments[$counter]['id'] = $output->id;
                $increments[$counter]['increment_id'] = $output->increment_id;
               	$increments[$counter]['segment_percentage'] = (($output->segment_amount / $output->increment_amount) * 100);
                $counter++;
                }

		}
	}
		
		return (isset($increments) && is_array($increments)) ? $increments : array();
	}

public static function policyholders($bond_id, $user_id, $segment_id, $segment_number)
    {

        $policyholders = array();
        $counter = 0;
	
	$inc = DB::table('ownerships')
        	->join('segments','segments.bond_id', '=', 'ownerships.bond_id')
        	->join('policyholders','policyholders.id', '=', 'ownerships.policyholder_id')
		->where('segments.id', '=', $segment_id)
		->where('segments.user_id', '=', $user_id)
		->where('segments.bond_id', '=', $bond_id)
		->where('ownerships.bond_id', '=', $bond_id)
		->where('ownerships.segment_start', '<=', $segment_number)
		->where('ownerships.segment_end', '>=', $segment_number)
		->select('policyholders.first_name AS first_name', 'policyholders.surname AS surname', 'ownerships.assignment_date AS assignment_date')
		->orderBy('policyholders.surname', 'asc')
		->distinct()
		->get();

        if (count($inc) > 0) {
        foreach ($inc as $id => $output) {

                $policyholders[$counter]['first_name'] = $output->first_name;
                $policyholders[$counter]['surname'] = $output->surname;
                $policyholders[$counter]['segment_number'] = $segment_number;

		if($output->assignment_date !="0000-00-00") {

                	$policyholders[$counter]['assigned'] = "Yes";

		} 

		else 

		{

			$policyholders[$counter]['assigned'] = "No";

		}

                $counter++;
                }

        }


    return $policyholders;

    }
 
}
