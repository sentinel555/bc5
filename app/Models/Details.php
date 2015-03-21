<?php

namespace App\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;
use \Illuminate\Support\Facades\DB;


class Details extends Eloquent {

	protected $table = 'bonds';
	protected $fillable = array('id', 'insurer', 'policy_number', 'investment', 'encashment_proceeds', 'commencement_date', 'encashment_date', 'auto_update', 'segments', 'offshore_bond', 'mode', 'user_id', 'created_at', 'updated_at');


	public static function load_bonds($bond_id, $user_id) {

		$bonds = DB::table('bonds')
		->join('ownerships', 'bonds.id', '=', 'ownerships.bond_id')
		->join('policyholders', 'policyholders.id', '=', 'ownerships.policyholder_id')
		->where('policyholders.user_id', '=', $user_id)
		->where('bonds.user_id', '=', $user_id)
		->where('bonds.id', '=', $bond_id)
		->select('policyholders.first_name AS first_name', 'policyholders.surname AS surname', 'policyholders.id AS policyholder_id', 'bonds.id AS bond_id', 'bonds.insurer AS insurer', 'bonds.policy_number AS policy_number', 'bonds.auto_update AS auto_update', 'bonds.investment AS investment', 'bonds.encashment_proceeds AS encashment_proceeds', 'bonds.commencement_date AS commencement_date', 'bonds.encashment_date AS encashment_date', 'bonds.offshore_bond AS offshore_bond', 'bonds.segments AS segments' )
		->orderBy('policyholders.surname', 'asc')
		->distinct()
		->get();
		return($bonds);

	}
	




	
}
