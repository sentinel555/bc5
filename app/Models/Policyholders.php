<?php

namespace App\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;
use \Illuminate\Support\Facades\DB;



class Policyholders extends Eloquent {

	protected $table = 'policyholders';
	protected $fillable = array('policyholders_id', 'first_name', 'surname', 'dob');

	public function policyholders(){
		return $this->belongsTo('Policyholders');
	}


	
	public static function existing_bonds($policyholder_id, $user_id){

		$policyholders = DB::table('bonds')
		->join('ownerships', 'bonds.id', '=', 'ownerships.bond_id')
		->join('policyholders', 'policyholders.id', '=', 'ownerships.policyholder_id')
		->where('policyholders.user_id', '=', $user_id)
		->where('bonds.user_id', '=', $user_id)
		->where('ownerships.policyholder_id', '=', $policyholder_id)
		->select('policyholders.first_name AS first_name', 'policyholders.surname AS surname', 'policyholders.id AS policyholder_id', 'bonds.id AS bond_id'  )
		->get();

		if(count($policyholders) == 0) {return true;} else {return false;}
	}
}
