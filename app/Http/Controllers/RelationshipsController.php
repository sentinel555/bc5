<?php

namespace App\Http\Controllers;  
use App\Models\Relationships as Relationships;
use \Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Input;
use \Illuminate\Support\Facades\Session;
use \Illuminate\Support\Facades\View;
use \Illuminate\Support\Facades\Redirect;
use \Illuminate\Support\Facades\Auth;

class RelationshipsController extends Controller {

	/**
	 * Routing Information
	 */
	public function getIndex() {


	if (Auth::check() == false) {

		return redirect('auth/login');
	}	 



		$relationship_types = array();
		$policyholders = array();


		foreach(DB::table('relationship_types')->orderBy('relationship_id', 'asc')->get() as $r) {
			$relationship_types[$r->relationship_id] =  $r->relationship_type;

		}

		foreach(DB::table('policyholders')->orderBy('surname', 'asc')->where('user_id', '=', Auth::user()->id)->get() as $p) {

			$policyholders[$p->id] =  $p->surname.', '.$p->first_name;

		}


		$relationships = Relationships::load_relationships(Session::get('policyholder_id'), Auth::user()->id);
                
                return View::make('relationships')->with('relationships', $relationships)->with('relationship_types', $relationship_types)->with('policyholders', $policyholders);

        
                return dd(Input::old());
		
	}


	public function postIndex() {

		
		$process = Input::get('r_process');
		$relationship_id = Input::get('relationship_id');
		$linked_policyholder = Input::get('linked_policyholder');
				
		$relationship_types = array();
		$policyholders = array();

		$deleted = 0;

				if($process == "delete") {

			DB::table('relationships')->where('policyholder_id', '=', Session::get('policyholder_id'))->where('linked_policyholder', '=', $linked_policyholder)->where('relationship_id', '=', $relationship_id)->delete();

		Session::flash('message', 'Ownership record successfully deleted.');

		$deleted = 1;

		}
		

		

		if($process == "add") {

			$existing = 0;

			$check = DB::table('relationships')->where('policyholder_id', '=', Session::get('policyholder_id'))->where('linked_policyholder', '=', $linked_policyholder)->where('relationship_id', '=', $relationship_id)->get();

			if(count($check) > 0) { Session::flash('message', 'The selected relationship already exists.'); $existing = 1; }

			if($linked_policyholder == Session::get('policyholder_id')) {  Session::flash('message', 'You have selected the existing policyholder. Please select another individual to link.'); $existing = 1; }

			if( $deleted == 0 && $existing == 0) {	

				Relationships::add_relationship(Session::get('policyholder_id'), $linked_policyholder, $relationship_id);

			Session::flash('message', 'Relationship successfully added.');

		 	}


		}	

	foreach(DB::table('relationship_types')->orderBy('relationship_id', 'asc')->get() as $r) {
			$relationship_types[$r->relationship_id] =  $r->relationship_type;

		}

		foreach(DB::table('policyholders')->orderBy('surname', 'asc')->where('user_id', '=', Auth::user()->id)->get() as $p) {

			$policyholders[$p->id] =  $p->surname.', '.$p->first_name;

		}


		$relationships = Relationships::load_relationships(Session::get('policyholder_id'), Auth::user()->id);


                $html = View::make('relationships')->with('relationships', $relationships)->with('relationship_types', $relationship_types)->with('policyholders', $policyholders);

	
		return($html);
		

	}



}
?>
