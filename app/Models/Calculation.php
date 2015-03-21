<?php

namespace App\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;
use \Illuminate\Support\Facades\DB;



class Calculation extends Eloquent {

	protected $table = 'calculations';
	protected $fillable = array('id', 'user_id', 'created_at', 'updated_at');

   

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







}
