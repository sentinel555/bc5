<?php

namespace App\Http\Controllers;  
use App\Models\Bonds as Bonds;
use \Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Input;
use \Illuminate\Support\Facades\Session;
use \Illuminate\Support\Facades\Validator;
use \Illuminate\Support\Facades\View;
use \Illuminate\Support\Facades\Redirect;
use \Illuminate\Support\Facades\Auth;

class BondsController extends Controller {

	/**
	 * Routing Information
	 */
	public function getIndex() {


	if (Auth::check() == false) {

		return redirect('auth/login');
	}	 


	$bonds = array();
	$name = array();
	$counter = 0;
	
	$b1= Bonds::orderBy('id', 'asc')->where('user_id', '=', Auth::user()->id)->get();	

	foreach($b1 as $id => $output) {

		$bonds[$counter] = Bonds::load_bonds($output->id, Auth::user()->id);
		$counter++;
	}

	foreach($bonds as $bond) {
		
		if(isset($bond[0]->surname)) {

			$name[] = $bond[0]->surname;
		} else 
			{ $name[] = 'Not yet assigned to a policyholder'; }

	}

        array_multisort($name, SORT_ASC, $bonds);

//ref::config('expLvl', 3);
//	~r($bonds);

	$step = 1;
	$pages = ceil(count($bonds) / 20);
	
		
		Session::put('filter_bonds', 0);

		return View::make('bonds')->with('bonds', $bonds)->with('step', $step)->with('pages', $pages);

	}


public function showResults($step) {


	if (Auth::check() == false) {

		return redirect('auth/login');
	}	 


	$bonds = array();
	$name = array();
	$counter = 0;
	
	$b1= Bonds::orderBy('id', 'asc')->where('user_id', '=', Auth::user()->id)->get();	

	foreach($b1 as $id => $output) {

		$bonds[$counter] = Bonds::load_bonds($output->id, Auth::user()->id);
		$counter++;
	}


	foreach($bonds as $bond) {
		
		$name[] = $bond[0]->surname;

	}

        array_multisort($name, SORT_ASC, $bonds);


	$pages = ceil(count($bonds) / 20);
	
		
		Session::put('filter_bonds', 0);

		return View::make('bonds')->with('bonds', $bonds)->with('step', $step)->with('pages', $pages);

	}


	public function postIndex() {


		$policyholders = array();
		$rules = array(
			'term' => 'min:2|max:50|alpha_spaces'
		);


		$bond_id = Input::get('bond_id');
		$process = Input::get('process');


		$bond = DB::table('bonds')->where('user_id', '=', Auth::user()->id)->where('id', '=', $bond_id)->first();	

		if($process == "redirect") {

			Session::put('bond_id', $bond_id);
			Session::put('bond_insurer', $bond->insurer);
			Session::put('bond_policy_number', $bond->policy_number);
			Session::put('ammend_bond', 1);
			return Redirect::to('bondview');

		}

		if($process == "delete") {

			if(Bonds::delete_bond($bond_id, Auth::user()->id) == true) {
			
			Session::flash('message', 'Bond record successfully deleted.'); } else 
			{ Session::flash('message', 'There was an error. Bond record not deleted.'); }
			return Redirect::to('bonds');
		}
		
		$validation = Validator::make(Input::all(), $rules);
		if ($validation->fails())
		{

		Session::flash('message', 'Please note that only alphanumeric characters can be submitted.');
		return Redirect::to('bonds')->withErrors($validation)->withInput();
		}
	
		
		$term = Input::get('term');

		
		$b1 = Bonds::orderBy('id', 'asc')->where('user_id', '=', Auth::user()->id)->get();	


 		$counter = 0;

		$bonds = array();
		$name = array();

               foreach($b1 as $id => $key) {

                       $bonds[$counter] = Bonds::search($key['id'], $term, Auth::user()->id);

		       if(count($bonds[$counter]) == 0) {  unset($bonds[$counter]); }

                       $counter++;

               }

	foreach($bonds as $bond) {
		
		$name[] = $bond[0]->surname;

	}

	$step = 1;
	$pages = ceil(count($bonds) / 20);
	

		
		
		if(count($bonds) == 0) { Session::flash('message', 'The search returned no results.'); } else { array_multisort($name, SORT_ASC, $bonds); }

		
		Session::put('filter_bonds', 1);
		return View::make('bonds')->with('bonds', $bonds)->with('step', $step)->with('pages', $pages);
   
	}

}
?>
