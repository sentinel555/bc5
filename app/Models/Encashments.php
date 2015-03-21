<?php

namespace App\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;
use \Illuminate\Support\Facades\DB;



class Encashments extends Eloquent {

	protected $table = 'encashments';
	protected $fillable = array('id', 'bond_id', 'segment_start', 'segment_end', 'segments_proceeds', 'segments_encashment_date', 'increment_id', 'user_id', 'created_at', 'updated_at');


	    public static function create_new_encashment($input, $bond_id, $user_id) {


		$encashment = new Encashments;
		$segments_encashment_date = null;

		if($input['segments_encashment_date'] != "") {

		$ad = preg_split('/[\/\.-]/', $input['segments_encashment_date']);

                $segments_encashment_date= $ad[2].$ad[1].$ad[0];

	    } else {$segments_encashment_date= "00000000";}

		$encashment->bond_id = $bond_id;
		$encashment->segments_proceeds = round($input['segments_proceeds'] , 6, PHP_ROUND_HALF_EVEN);
		$encashment->segments_encashment_date = $segments_encashment_date;
		$encashment->segment_start = $input['segment_start'];
		$encashment->segment_end = $input['segment_end'];
	        $encashment->user_id = $user_id;

		$encashment->save();
		$encashment->touch();

	return TRUE;
    }


public static function load_encashments($bond_id, $user_id) { 

	$encashments = array();
	$counter = 0;

	$enc = DB::table('encashments')
		->where('encashments.user_id', '=', $user_id)
		->where('encashments.bond_id', '=', $bond_id)
		->select('encashments.id AS id', 'encashments.bond_id AS bond_id', 'encashments.increment_id AS increment_id', 'encashments.segment_start AS segment_start', 'encashments.segment_end AS segment_end', 'encashments.segments_proceeds AS segments_proceeds', 'encashments.segments_encashment_date AS segments_encashment_date')
		->orderBy('encashments.segments_encashment_date', 'asc')
		->distinct()
		->get();

	foreach ($enc as $id => $output) {
		
		$encashments[$counter]['id'] = $output->id;
		$encashments[$counter]['bond_id'] = $output->bond_id;
		$encashments[$counter]['segments_proceeds'] = round($output->segments_proceeds, 2);
		$encashments[$counter]['segment_start'] = $output->segment_start;
		$encashments[$counter]['segment_end'] = $output->segment_end;
                $encashments[$counter]['segments_encashment_date'] = $output->segments_encashment_date;

		$counter++;
		}
            
    return $encashments;

    }


 
}
