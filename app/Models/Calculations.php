<?php

namespace App\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;
use \Illuminate\Support\Facades\DB;



class Calculations extends Eloquent {

	protected $table = 'calculations';
	protected $fillable = array('id', 'user_id', 'created_at', 'updated_at');



public static function load_bonds($bond_id, $user_id, $calculation_id, $updated_at) {

		$bonds = array();
		$counter = 0;

		$bd = DB::table('bonds')
		->join('ownerships', 'bonds.id', '=', 'ownerships.bond_id')
		->join('policyholders', 'policyholders.id', '=', 'ownerships.policyholder_id')
		->where('policyholders.user_id', '=', $user_id)
		->where('bonds.user_id', '=', $user_id)
		->where('bonds.id', '=', $bond_id)
		->select('policyholders.first_name AS first_name', 'policyholders.surname AS surname', 'policyholders.id AS policyholder_id', 'bonds.id AS bond_id', 'bonds.insurer AS insurer', 'bonds.policy_number AS policy_number' )
		->orderBy('policyholders.surname', 'asc')
		->distinct()
		->get();

		if(count($bd) > 0) {

			foreach($bd as $id => $output) {
		
				$bonds[$counter]['first_name'] = $output->first_name;
				$bonds[$counter]['surname'] = $output->surname;
				$bonds[$counter]['policyholder_id'] = $output->policyholder_id;
				$bonds[$counter]['bond_id'] = $output->bond_id;
				$bonds[$counter]['insurer'] = $output->insurer;
				$bonds[$counter]['policy_number'] = $output->policy_number;
				$bonds[$counter]['calculation_id'] = $calculation_id;
				$bonds[$counter]['updated_at'] = date('d/m/Y H:i:s', strtotime($updated_at));

				$counter++;
			}

		}


		return($bonds);

	}
	

	
	public static function search($bond_id, $user_id, $term, $calculation_id, $updated_at){

		$bonds = array();
		$counter = 0;

		if($term == '' || $term == null) {

		$bd = DB::table('bonds')
		->join('ownerships', 'bonds.id', '=', 'ownerships.bond_id')
		->join('policyholders', 'policyholders.id', '=', 'ownerships.policyholder_id')
		->where('policyholders.user_id', '=', $user_id)
		->where('bonds.user_id', '=', $user_id)
		->where('bonds.id', '=', $bond_id)
		->select('policyholders.first_name AS first_name', 'policyholders.surname AS surname', 'policyholders.id AS policyholder_id', 'bonds.id AS bond_id', 'bonds.insurer AS insurer', 'bonds.policy_number AS policy_number' )
		->orderBy('policyholders.surname', 'asc')
		->distinct()
		->get();

		if(count($bd) > 0) {

			foreach($bd as $id => $output) {
		
				$bonds[$counter]['first_name'] = $output->first_name;
				$bonds[$counter]['surname'] = $output->surname;
				$bonds[$counter]['policyholder_id'] = $output->policyholder_id;
				$bonds[$counter]['bond_id'] = $output->bond_id;
				$bonds[$counter]['insurer'] = $output->insurer;
				$bonds[$counter]['policy_number'] = $output->policy_number;
				$bonds[$counter]['calculation_id'] = $calculation_id;
				$bonds[$counter]['updated_at'] = date('d/m/Y H:i:s', strtotime($updated_at));

				$counter++;
			}

		}


			}
		else

		{
		
		$bd = DB::table('bonds')
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

		if(count($bd) > 0) {

			foreach($bd as $id => $output) {
		
				$bonds[$counter]['first_name'] = $output->first_name;
				$bonds[$counter]['surname'] = $output->surname;
				$bonds[$counter]['policyholder_id'] = $output->policyholder_id;
				$bonds[$counter]['bond_id'] = $output->bond_id;
				$bonds[$counter]['insurer'] = $output->insurer;
				$bonds[$counter]['policy_number'] = $output->policy_number;
				$bonds[$counter]['calculation_id'] = $calculation_id;
				$bonds[$counter]['updated_at'] = date('d/m/Y H:i:s', strtotime($updated_at));

				$counter++;
			}

		}



				}


		return($bonds);

	}







}
