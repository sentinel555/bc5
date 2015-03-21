<?php

namespace App\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;
use \Illuminate\Support\Facades\DB;

class Relationships extends Eloquent {

	protected $table = 'relationships';
	protected $fillable = array('id', 'policyholder_id', 'linked_policyholder', 'relationship_id', 'created_at', 'updated_at');


	public static function load_relationships($policyholder_id, $user_id) {

		$counter = 0;
		$relationships = array();
		
		$relationship_types = array();
		
		foreach(DB::table('relationship_types')->orderBy('relationship_id', 'asc')->get() as $z) {
			$relationship_types[$z->relationship_id] =  $z->relationship_type;

		}

		$r = DB::table('relationships')->orderBy('id', 'asc')->where('policyholder_id', '=', $policyholder_id)->get();


		foreach($r as $id => $output) {

			$policyholder = DB::table('policyholders')->select('id', 'first_name', 'surname')->where('id', '=', $output->linked_policyholder)->where('user_id', '=', $user_id)->get();

			$relationships[$counter]['id'] = $output->relationship_id;
			$relationships[$counter]['policyholder_id'] = $policyholder[0]->id;
			$relationships[$counter]['first_name'] = $policyholder[0]->first_name;
			$relationships[$counter]['surname'] = $policyholder[0]->surname;
			$relationships[$counter]['relationship_type'] = $relationship_types[$output->relationship_id];
			$counter++;

		}

		return($relationships);
	}


public static function add_relationship($policyholder_id, $linked_policyholder, $relationship_id) {
		$check = 0;
		$relationship = new Relationships;

		$relationship->policyholder_id = $policyholder_id;
		$relationship->linked_policyholder = $linked_policyholder;
		$relationship->relationship_id = $relationship_id;
		$relationship->save();
		$relationship->touch();

	if($relationship_id == 1) {$check = 2;}	
	if($relationship_id == 2) {$check = 1;}	


		if($check > 0) {
		
		$r = DB::table('relationships')->where('policyholder_id', '=', $linked_policyholder)->where('linked_policyholder', '=', $policyholder_id)->where('relationship_id', '=', $check)->get();

		if(empty($r)) {

			$relationship = new Relationships;

			$relationship->policyholder_id = $linked_policyholder;
			$relationship->linked_policyholder = $policyholder_id;
			$relationship->relationship_id = $check;
			$relationship->save();
			$relationship->touch();
	
			}
		}
	}


}
