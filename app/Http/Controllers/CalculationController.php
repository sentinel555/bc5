<?php

namespace App\Http\Controllers;  
use App\Models\Calculation as Calculation;
use \Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Input;
use \Illuminate\Support\Facades\Session;
use \Illuminate\Support\Facades\Validator;
use \Illuminate\Support\Facades\View;
use \Illuminate\Support\Facades\Redirect;
use \Illuminate\Support\Facades\Auth;

class CalculationController extends Controller {

	/**
	 * Routing Information
	 */
	public function getIndex() {

	if (Auth::check() == false) {

		return redirect('auth/login');
	}	 


	if(!Session::has('calculation_id')) { return redirect('calculations'); }	

	$bonds = array();
	$name = array();
	$items = array();
	$counter = 0;
	
	$calculation_id = Session::has('calculation_id') ? Session::get('calculation_id') : 0;

	$b1 = DB::table('bonds')->orderBy('id', 'asc')->where('user_id', '=', Auth::user()->id)->get();	

	foreach($b1 as $id => $output) {

		$bonds[$counter] = Calculation::load_bonds($output->id, Auth::user()->id);
		$counter++;
	}

	foreach($bonds as $bond) {
		
		$name[] = $bond[0]->surname;

	}

        array_multisort($name, SORT_ASC, $bonds);

	if($calculation_id > 0) {

		$counter = 0;
		$n = array();
	
		$i1 = DB::table('calculation_set')->orderBy('bond_id', 'asc')->where('calculation_id', '=', $calculation_id)->where('user_id', '=', Auth::user()->id)->get();

		foreach($i1 as $id => $output) {

			$items[$counter] = Calculation::load_bonds($output->bond_id, Auth::user()->id);
			$counter++;
		
		}


			foreach($items as $item) {
		
				$n[] = $item[0]->surname;

			}

        		array_multisort($n, SORT_ASC, $items);

	}


	$step = 1;
	$pages = ceil(count($bonds) / 20);
	

		Session::put('filter_bonds', 0);

		return View::make('calculation')->with('bonds', $bonds)->with('items', $items)->with('step', $step)->with('pages', $pages)->with('calculation_id', $calculation_id);

	}


public function showResults($step) {


	$bonds = array();
	$name = array();
	$items = array();
	$counter = 0;

	$calculation_id = Session::has('calculation_id') ? Session::get('calculation_id') : 0;

	$b1= DB::table('bonds')->orderBy('id', 'asc')->where('user_id', '=', Auth::user()->id)->get();	

	foreach($b1 as $id => $output) {

		$bonds[$counter] = Calculation::load_bonds($output->id, Auth::user()->id);
		$counter++;
	}


	foreach($bonds as $bond) {
		
		$name[] = $bond[0]->surname;

	}

        array_multisort($name, SORT_ASC, $bonds);

	if($calculation_id > 0) {
	
		$counter = 0;
		$n = array();

		$i1 = DB::table('calculation_set')->orderBy('bond_id', 'asc')->where('calculation_id', '=', $calculation_id)->where('user_id', '=', Auth::user()->id)->get();

		foreach($i1 as $id => $output) {

			$items[$counter] = Calculation::load_bonds($output->bond_id, Auth::user()->id);
			$counter++;
		
		}


			foreach($items as $item) {

				if(isset($item[0]->surname)) {
		
					$n[] = $item[0]->surname;

				} else { $n[] = ''; }

			}

        		array_multisort($n, SORT_ASC, $items);

	}

	$pages = ceil(count($bonds) / 20);
	
		
		Session::put('filter_bonds', 0);

		return View::make('calculation')->with('bonds', $bonds)->with('items', $items)->with('step', $step)->with('pages', $pages)->with('calculation_id', $calculation_id);

	}


	public function postIndex() {

		$items = array();
		$bonds = array();
		$name = array();

		$policyholders = array();
		$rules = array(
			'term' => 'min:2|max:50|alpha_spaces'
		);

		$bond_id = Input::get('bond_id');
		$process = Input::get('process');

		$calculation_id = 0;

		if(Input::get('calculation_id') > 0) { $calculation_id = Input::has('calculation_id') ? Input::get('calculation_id') : (Session::has('calculation_id') ? Session::get('calculation_id') : 0); }

		$bond = DB::table('bonds')->where('user_id', '=', Auth::user()->id)->where('id', '=', $bond_id)->first();	


		if($process == "generate") {

			return Redirect::to('report');

		}

		if($process == "delete") {

			DB::table('calculation_set')->where('bond_id', '=', $bond_id)->where('user_id', '=', Auth::user()->id)->where('calculation_id', '=', $calculation_id)->delete();

		Session::flash('message', 'Bond successfully removed from calculation.');
		return Redirect::to('calculation');

		}

		if($process == "add") {
		
			if($calculation_id == 0) { 
				
				$calculation_id = DB::table('calculations')->insertGetId(array('user_id' => Auth::user()->id));
				Session::put('calculation_id', $calculation_id); 
			
				}

			$existing = DB::table('calculation_set')->where('user_id', '=', Auth::user()->id)->where('bond_id', '=', $bond_id)->where('calculation_id', '=', $calculation_id)->first();
			if(is_null($existing)) {
			
			$date = new \DateTime;
			
			DB::table('calculation_set')->insert(array('calculation_id' => $calculation_id, 'bond_id' => $bond_id, 'user_id' => Auth::user()->id, 'created_at' => $date, 'updated_at' => $date));

		Session::flash('message', 'Bond successfully added to calculation.');
		return Redirect::to('calculation');

			} else {

			Session::flash('message', 'The bond you have selected already exists in the current calculation.');
			return Redirect::to('calculation');

			}

		}	
		
		$validation = Validator::make(Input::all(), $rules);
		if ($validation->fails())
		{

		Session::flash('message', 'Please note that only alphanumeric characters can be submitted.');
		return Redirect::to('calculation')->withErrors($validation)->withInput();
		}
	
		
		$term = Input::get('term');

		
		$b1 = DB::table('bonds')->orderBy('id', 'asc')->where('user_id', '=', Auth::user()->id)->get();	


 		$counter = 0;

//ref::config('expLvl', 3);
//~r($b1);
               foreach($b1 as $id => $key) {

                       $bonds[$counter] = Calculation::search($key->id, $term, Auth::user()->id);

		       if(count($bonds[$counter]) == 0) {  unset($bonds[$counter]); }

                       $counter++;

               }

	foreach($bonds as $bond) {
		
		$name[] = $bond[0]->surname;

	}
		
	if($calculation_id > 0) {
	
		$counter = 0;
		$n = array();

		$i1 = DB::table('calculation_set')->orderBy('bond_id', 'asc')->where('calculation_id', '=', $calculation_id)->where('user_id', '=', Auth::user()->id)->get();

		foreach($i1 as $id => $output) {

			$items[$counter] = Calculation::load_bonds($output->bond_id, Auth::user()->id);
			$counter++;
		
		}


			foreach($items as $item) {
		
				$n[] = $item[0]->surname;

			}

        		array_multisort($n, SORT_ASC, $items);

	}

	$step = 1;
	$pages = ceil(count($bonds) / 20);
	

		
		
		if(count($bonds) == 0) { Session::flash('message', 'The search returned no results.'); }  else { array_multisort($name, SORT_ASC, $bonds); }

		
		Session::put('filter_bonds', 1);
		return View::make('calculation')->with('bonds', $bonds)->with('items', $items)->with('step', $step)->with('pages', $pages)->with('calculation_id', $calculation_id);
   
	}

}
?>
