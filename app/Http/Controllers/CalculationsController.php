<?php

namespace App\Http\Controllers;  
use App\Models\Calculations as Calculations;
use \Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Input;
use \Illuminate\Support\Facades\Session;
use \Illuminate\Support\Facades\Validator;
use \Illuminate\Support\Facades\View;
use \Illuminate\Support\Facades\Redirect;
use \Illuminate\Support\Facades\Auth;

class CalculationsController extends Controller {

	/**
	 * Routing Information
	 */
	public function getIndex() {

	if (Auth::check() == false) {

		return redirect('auth/login');
	}	 



	$c2 = array();
	$calculation = array();
	$counter = 0;
	



	// LOAD CALCULATIONS

	$c1 = DB::table('calculations')->orderBy('id', 'asc')->where('user_id', '=', Auth::user()->id)->get();	

	// LOAD SUB-CALCULATIONS

	foreach($c1 as $id => $output) {

		$c2[$counter] = DB::table('calculation_set')->where('user_id', '=', Auth::user()->id)->where('calculation_id', "=", $output->id)->orderBy('updated_at', 'desc')->get();
		$counter++;

	}

	// LOAD BONDS AND POLICYHOLDERS

	for($x = 0; $x < count($c2); $x++) {

		for($z = 0; $z < count($c2[$x]); $z++) {

			$calculation[$x][$z] = Calculations::load_bonds($c2[$x][$z]->bond_id, Auth::user()->id, $c2[$x][$z]->calculation_id, $c2[$x][$z]->updated_at);

		}

	}

	for($i = 0; $i < count($calculation); $i++) {
		
		$name = array();
		
		foreach($calculation[$i] as $calc) {
                
                	$name[] = $calc[0]['surname'];

		}
		
		array_multisort($name, SORT_ASC, $calculation[$i]);
	}

	$name = array();
	$counter = 0;
	for($i = 0; $i < count($calculation); $i++) {

		$name[] = $calculation[$i][0][0]['surname'];	

	}
		array_multisort($name, SORT_ASC, $calculation);


	$step = 1;
	$pages = ceil(count($calculation) / 20);
	

		Session::put('filter_bonds', 0);

		return View::make('calculations')->with('calculation', $calculation)->with('step', $step)->with('pages', $pages);

	}


public function showResults($step) {

	if (Auth::check() == false) {

		return redirect('auth/login');
	}	 


	$c2 = array();
	$calculation = array();
	$counter = 0;
	
	// LOAD CALCULATIONS

	$c1 = DB::table('calculations')->orderBy('id', 'asc')->where('user_id', '=', Auth::user()->id)->get();	

	// LOAD SUB-CALCULATIONS

	foreach($c1 as $id => $output) {

		$c2[$counter] = DB::table('calculation_set')->where('user_id', '=', Auth::user()->id)->where('calculation_id', '=', $output->id)->orderBy('updated_at', 'desc')->get();
		$counter++;

	}

	// LOAD BONDS AND POLICYHOLDERS

	for($x = 0; $x < count($c2); $x++) {

		for($z = 0; $z < count($c2[$x]); $z++) {

			$calculation[$x][$z] = Calculations::load_bonds($c2[$x][$z]->bond_id, Auth::user()->id, $c2[$x][$z]->calculation_id, $c2[$x][$z]->updated_at);

		}

	}

	for($i = 0; $i < count($calculation); $i++) {
		
		$name = array();
		
		foreach($calculation[$i] as $calc) {
                
                	$name[] = $calc[0]['surname'];

		}
		
		array_multisort($name, SORT_ASC, $calculation[$i]);
	}

	$name = array();
	$counter = 0;
	for($i = 0; $i < count($calculation); $i++) {

		$name[] = $calculation[$i][0][0]['surname'];	

	}
		array_multisort($name, SORT_ASC, $calculation);


	$pages = ceil(count($calculation) / 20);
	

		Session::put('filter_bonds', 0);

		return View::make('calculations')->with('calculation', $calculation)->with('step', $step)->with('pages', $pages);



	}


	public function postIndex() {

		$calculation = array();
		$rules = array(
			'term' => 'min:2|max:50|alpha_spaces'
		);

		$calculation_id = Input::get('bond_id');
		$process = Input::get('process');


		if(Input::get('calculation_id') > 0) { $calculation_id = Input::has('calculation_id') ? Input::get('calculation_id') : (Session::has('calculation_id') ? Session::get('calculation_id') : 0); }


		if($process == "delete") {

			DB::table('calculation_set')->where('user_id', '=', Auth::user()->id)->where('calculation_id', '=', $calculation_id)->delete();

			DB::table('calculations')->where('user_id', '=', Auth::user()->id)->where('id', '=', $calculation_id)->delete();

			Session::flash('message', 'Calculation successfully deleted.');
			
			return Redirect::to('calculations');

		}


		if($process == "edit") {
		
				Session::put('calculation_id', $calculation_id); 
			
				return Redirect::to('calculation');

			}

			
		
		$validation = Validator::make(Input::all(), $rules);
		if ($validation->fails())
		{

		Session::flash('message', 'Please note that only alphanumeric characters can be submitted.');
		return Redirect::to('calculation')->withErrors($validation)->withInput();
		}
	
		
		$term = Input::get('term');

	
	$c2 = array();
	$calculation = array();
	$counter = 0;
	
	// LOAD CALCULATIONS

	$c1 = DB::table('calculations')->orderBy('id', 'asc')->where('user_id', '=', Auth::user()->id)->get();	

	// LOAD SUB-CALCULATIONS

	foreach($c1 as $id => $output) {

		$c2[$counter] = DB::table('calculation_set')->where('user_id', '=', Auth::user()->id)->where('calculation_id', "=", $output->id)->orderBy('updated_at', 'desc')->get();
		$counter++;

	}

	// SEARCH FOR POLICYHOLDERS

	for($x = 0; $x < count($c2); $x++) {

		for($z = 0; $z < count($c2[$x]); $z++) {

			$calculation[$x][$z] = Calculations::search($c2[$x][$z]->bond_id, Auth::user()->id, $term, $c2[$x][$z]->calculation_id, $c2[$x][$z]->updated_at);

		}

	}

	$calculation = array_filter(array_map('array_filter', $calculation));


	$calcs = array(); // Array to hold calculation ID's linked to the policyholder.
	
	foreach($calculation as $calc) {

		foreach($calc as $c) {

			foreach($c as $x) {

				if(isset($x['calculation_id'])) {$calcs[] = $x['calculation_id']; }


			}
		}

	}

	$calcs = array_unique($calcs);


	// REPEAT THE ARGUMENT, BUT WITH THE VALUES CONTAINED IN CALCS
	
	// LOAD SUB-CALCULATIONS

	unset($c2);
	unset($calculation);

	$c2 = array();
	$calculation = array();

	$counter = 0;

	foreach($calcs as $value) {

		$c2[$counter] = DB::table('calculation_set')->where('user_id', '=', Auth::user()->id)->where('calculation_id', "=", $value)->orderBy('updated_at', 'desc')->get();
		$counter++;

	}

	// SEARCH FOR POLICYHOLDERS

	for($x = 0; $x < count($c2); $x++) {

		for($z = 0; $z < count($c2[$x]); $z++) {

			$calculation[$x][$z] = Calculations::load_bonds($c2[$x][$z]->bond_id, Auth::user()->id, $c2[$x][$z]->calculation_id, $c2[$x][$z]->updated_at);

		}

	}


	if(!empty($calcs)) {

	for($i = 0; $i < count($calculation); $i++) {
		
		$name = array();
		
		foreach($calculation[$i] as $calc) {
                
                	$name[] = $calc[0]['surname'];

		}
		
		array_multisort($name, SORT_ASC, $calculation[$i]);
	}

	$name = array();
	$counter = 0;

	for($i = 0; $i < count($calculation); $i++) {

		$name[] = $calculation[$i][0][0]['surname'];	

	}
	
	array_multisort($name, SORT_ASC, $calculation);

	}


	if(empty($calcs)) { Session::flash('message', 'The search returned no results.'); }

	$step = 1;
	$pages = ceil(count($calculation) / 20);
	

		Session::put('filter_bonds', 0);

		return View::make('calculations')->with('calculation', $calculation)->with('step', $step)->with('pages', $pages);

	
   
	}

}
?>
