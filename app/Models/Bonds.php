<?php

namespace App\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;
use \Illuminate\Support\Facades\DB;

class Bonds extends Eloquent {

	protected $table = 'bonds';
	protected $fillable = array('id', 'insurer', 'policy_number', 'investment', 'encashment_proceeds', 'commencement_date', 'encashment_date', 'auto_update', 'segments', 'offshore_bond', 'mode', 'user_id', 'created_at', 'updated_at');


	public static function load_bonds($bond_id, $user_id) {

		$bonds = DB::table('bonds')
		->join('ownerships', 'bonds.id', '=', 'ownerships.bond_id')
		->join('policyholders', 'policyholders.id', '=', 'ownerships.policyholder_id')
		->where('policyholders.user_id', '=', $user_id)
		->where('bonds.user_id', '=', $user_id)
		->where('bonds.id', '=', $bond_id)
		->select('policyholders.first_name AS first_name', 'policyholders.surname AS surname', 'policyholders.id AS policyholder_id', 'bonds.id AS bond_id', 'bonds.insurer AS insurer', 'bonds.policy_number AS policy_number' )
		->orderBy('policyholders.surname', 'asc')
		->distinct()
		->get();
		return($bonds);

	}
	


public static function policyholders($bond_id, $user_id){

        $policyholders = DB::table('bonds')
                ->join('ownerships', 'bonds.id', '=', 'ownerships.bond_id')
                ->join('policyholders', 'policyholders.id', '=', 'ownerships.policyholder_id')
                ->where('policyholders.user_id', '=', $user_id)
                ->where('bonds.user_id', '=', $user_id)
                ->where('bonds.id', '=', $bond_id)
                ->select('policyholders.first_name AS first_name', 'policyholders.surname AS surname', 'policyholders.id AS policyholder_id', 'bonds.id AS bond_id', 'bonds.insurer AS insurer', 'bonds.policy_number AS policy_number' )
                ->distinct()
                ->get();

                return($policyholders);

        }

	public static function search($bond_id, $term, $user_id){


		if($term == '' || $term == null) {

		$bonds = DB::table('bonds')
		->join('ownerships', 'bonds.id', '=', 'ownerships.bond_id')
		->join('policyholders', 'policyholders.id', '=', 'ownerships.policyholder_id')
		->where('policyholders.user_id', '=', $user_id)
		->where('bonds.user_id', '=', $user_id)
		->where('bonds.id', '=', $bond_id)
		->select('policyholders.first_name AS first_name', 'policyholders.surname AS surname', 'policyholders.id AS policyholder_id', 'bonds.id AS bond_id', 'bonds.insurer AS insurer', 'bonds.policy_number AS policy_number' )
		->orderBy('policyholders.surname', 'asc')
		->distinct()
		->get();


			}
		else
		{
		$bonds = DB::table('bonds')
		->join('ownerships', 'bonds.id', '=', 'ownerships.bond_id')
		->join('policyholders', 'policyholders.id', '=', 'ownerships.policyholder_id')
		->where('policyholders.user_id', '=', $user_id)
		->where('bonds.user_id', '=', $user_id)
		->where('bonds.id', '=', $bond_id)
		->where('policyholders.surname', 'LIKE', $term.'%')
		->select('policyholders.first_name AS first_name', 'policyholders.surname AS surname', 'policyholders.id AS policyholder_id', 'bonds.id AS bond_id', 'bonds.insurer AS insurer', 'bonds.policy_number AS policy_number' )
		->orderBy('policyholders.surname', 'asc')
		->distinct()
		->get();


				}


		return($bonds);

	}


public static function delete_bond($bond_id, $user_id) {

			Bonds::where('id', '=', $bond_id)->where('user_id', '=', $user_id)->delete();
			DB::table('ownerships')->where('bond_id', '=', $bond_id)->where('user_id', '=', $user_id)->delete();
			DB::table('increments')->where('bond_id', '=', $bond_id)->where('user_id', '=', $user_id)->delete();
			DB::table('encashments')->where('bond_id', '=', $bond_id)->where('user_id', '=', $user_id)->delete();
			DB::table('policy_loans')->where('bond_id', '=', $bond_id)->where('user_id', '=', $user_id)->delete();
			DB::table('segments')->where('bond_id', '=', $bond_id)->where('user_id', '=', $user_id)->delete();
			DB::table('withdrawals')->where('bond_id', '=', $bond_id)->where('user_id', '=', $user_id)->delete();

		$calculation_set = DB::table('calculation_set')
		->where('user_id', '=', $user_id)
		->where('bond_id', '=', $bond_id)
		->distinct()
		->get();

		foreach($calculation_set as $id => $output) {

			DB::table('calculation_set')->where('calculation_id', '=', $output->calculation_id)->where('user_id', '=', $user_id)->delete();
			DB::table('calculations')->where('id', '=', $output->calculation_id)->where('user_id', '=', $user_id)->delete();

		}

		return(true);

}

}
