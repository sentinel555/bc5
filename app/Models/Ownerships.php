<?php

namespace App\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;
use \Illuminate\Support\Facades\DB;


class Ownerships extends Eloquent {

	protected $table = 'ownerships';
	protected $fillable = array('id', 'bond_id', 'percentage_split', 'segment_start', 'segment_end', 'trustee_investment', 'policyholder_id', 'assignment_date', 'security_debt_date', 'user_id', 'created_at', 'updated_at');


public static function create_new_ownership($input, $bond_id, $user_id) {

	   $ownership = new Ownerships;

	    $assignment_date = null;
	    $security_debt_date = null;

	    if($input['assignment_date'] != "") {

		$ad = preg_split('/[\/\.-]/', $input['assignment_date']);

                $assignment_date = $ad[2].$ad[1].$ad[0];

	    } else {$assignmentDate = "00000000";}

                settype($assignment_date, "integer");


	    if($input['security_debt_date'] != "") {

                $sdd = preg_split('/[\/\.-]/', $input['security_debt_date']);

                $security_debt_date = $sdd[2].$sdd[1].$sdd[0];

	    } else {$security_debt_date = "00000000";}

                settype($security_debt_date , "integer");


		$ownership->bond_id = $bond_id;
		$ownership->percentage_split = $input['percentage_split'];
		$ownership->segment_start = $input['segment_start'];
		$ownership->segment_end = $input['segment_end'];
	        $ownership->trustee_investment = isset($input['trustee_investment']) ? 1 : 0;
		$ownership->policyholder_id = $input['policyholder'];
		$ownership->assignment_date = $assignment_date;
		$ownership->security_debt_date = $security_debt_date;
	        $ownership->user_id = $user_id;

		$ownership->save();
		$ownership->touch();
    }


	public static function policyholders($bond_id, $user_id) {

	$policyholders = DB::table('policyholders')
		->join('ownerships', 'policyholders.id', '=', 'ownerships.policyholder_id')
		->join('bonds', 'bonds.id', '=', 'ownerships.bond_id')
		->where('policyholders.user_id', '=', $user_id)
		->where('bonds.user_id', '=', $user_id)
		->where('bonds.id', '=', $bond_id)
		->select('policyholders.first_name AS first_name', 'policyholders.surname AS surname', 'policyholders.id AS policyholder_id', 'bonds.id AS bond_id', 'bonds.insurer AS insurer', 'bonds.policy_number AS policy_number' )
		->distinct()
		->get();

		return($policyholders);

	}

	public static function owners($user_id) {

	$owners = array();

	$policyholders = DB::table('policyholders')
		->where('policyholders.user_id', '=', $user_id)
		->select('policyholders.first_name AS first_name', 'policyholders.surname AS surname', 'policyholders.id AS policyholder_id')
		->distinct()
		->get();

		foreach ($policyholders as $id => $output) {

			$owners[$output->policyholder_id] = $output->surname.', '.$output->first_name;
		}

		asort($owners);

		return($owners);

	}


	public static function ownership_details($bond_id, $user_id) {
		
		$ownership_details = DB::table('ownerships')
		->join('policyholders','policyholders.id', '=', 'ownerships.policyholder_id')
		->where('policyholders.user_id', '=', $user_id)
		->where('ownerships.user_id', '=', $user_id)
		->where('ownerships.bond_id', '=', $bond_id)
		->select('ownerships.bond_id AS bond_id', 'ownerships.percentage_split AS percentage_split', 'ownerships.segment_start AS segment_start', 'ownerships.segment_end AS segment_end', 'ownerships.policyholder_id AS policyholder_id', 'ownerships.id AS ownership_id', 'policyholders.first_name AS first_name', 'policyholders.surname AS surname', 'ownerships.trustee_investment AS trustee_investment', 'ownerships.assignment_date AS assignment_date', 'ownerships.security_debt_date AS security_debt_date'  )
		->distinct()
		->get();

    		return ($ownership_details);

    }



	public static function total_percentage_split($bond_id, $user_id) {

		$counter = 0;
		$split = array();
	
		$sp = DB::table('ownerships')
		->join('policyholders','policyholders.id', '=', 'ownerships.policyholder_id')
		->where('policyholders.user_id', '=', $user_id)
		->where('ownerships.user_id', '=', $user_id)
		->where('ownerships.bond_id', '=', $bond_id)
		->select('ownerships.percentage_split AS percentage_split', 'ownerships.segment_start AS segment_start', 'ownerships.segment_end AS segment_end')
		->get();
		
		
		foreach ($sp as $id => $output) {

			$split[$counter]['percentage_split']  = $output->percentage_split;
			$split[$counter]['segment_start']  = $output->segment_start;
			$split[$counter]['segment_end']  = $output->segment_end;
			$counter++;
		}

		return($split);
	}

            

public static function increments($bond_id, $user_id) {

	$increments = DB::table('increments') 
        ->where('user_id', $user_id)
        ->where('bond_id', $bond_id)
		->select('id', 'bond_id', 'increment_amount', 'increment_segments', 'increment_commencement_date' )
		->distinct()
		->get();

    return ($increments);

    }

}
