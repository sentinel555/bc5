<?php

namespace App\Http\Controllers;  
use App\Models\Policyholders as Policyholders;
use \Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Input;
use \Illuminate\Support\Facades\Session;
use \Illuminate\Support\Facades\Validator;
use \Illuminate\Support\Facades\View;
use \Illuminate\Support\Facades\Redirect;
use \Illuminate\Support\Facades\Auth;

class PolicyholdersController extends Controller {

    /**
     * Routing Information
     */
	public function getIndex() {
		
	if (Auth::check() == false) {

		return redirect('auth/login');
	}	 



		$policyholders = DB::table('policyholders')->orderBy('surname', 'asc')->where('user_id', '=', Auth::user()->id)->get();	

		$step = 1;
        	$pages = ceil(count($policyholders) / 20);
		

		return View::make('policyholders')->with('policyholders', $policyholders)->with('step', $step)->with('pages', $pages);
	
	}
	
	
	public function showResults($step) {
		

		$policyholders = DB::table('policyholders')->orderBy('surname', 'asc')->where('user_id', '=', Auth::user()->id)->get();	


        	$pages = ceil(count($policyholders) / 20);
		
		return View::make('policyholders')->with('policyholders', $policyholders)->with('step', $step)->with('pages', $pages);
	
	}


	public function postIndex() {


		$policyholders = array();
		$rules = array(
		'term' => 'min:2|max:50|alpha_dash'
		);


		$first_name = Input::get('first_name');
		$surname = Input::get('surname');
		$policyholder_id = Input::get('policyholder_id');
		$process = Input::get('process');

		if($process == "redirect") {
			
			Session::put('ammend_policyholder', 1);
			Session::put('policyholder_id', $policyholder_id);
			return Redirect::to('policyholderview');

		}

		if($process == "delete") {

			$existing = Policyholders::existing_bonds($policyholder_id, Auth::user()->id);
		if($existing == true) {	
			Policyholder::where('id', '=', $policyholder_id)->where('user_id', '=', Auth::user()->id)->where('surname', '=', $surname)->delete();
			DB::table('relationships')->where('policyholder_id', '=', $policyholder_id)->delete();
			DB::table('non_residence')->where('policyholder_id', '=', $policyholder_id)->delete();

		Session::flash('message', 'Policyholder record successfully deleted.');
		return Redirect::to('policyholders');

		} else {


			Session::flash('message', 'There are investment bonds still linked to the policyholder&#39;s records. Please delete the individual bonds before you delete the policyholder&#39s details.');
			return Redirect::to('policyholders');
			}
		}
		
		$validation = Validator::make(Input::all(), $rules);
		if ($validation->fails())
		{

		Session::flash('message', 'Please note that only alphanumeric characters can be submitted.');
		return Redirect::to('policyholders')->withErrors($validation)->withInput();
		}
	
		
		$term = Input::get('term');

		if($term == '' || $term == null) {

		$policyholders = DB::table('policyholders')->orderBy('surname', 'asc')->where('surname', 'LIKE' , $term)->where('user_id', '=', Auth::user()->id)->get();	
		} else {

		$policyholders = DB::table('policyholders')->orderBy('surname', 'asc')->where('surname', 'LIKE' , $term.'%')->where('user_id', '=', Auth::user()->id)->get();	
		}

		if(count($policyholders) == 0) {Session::flash('message', 'The search returned no results.');
		}


		$step = 1;
        	$pages = ceil(count($policyholders) / 20);

		return View::make('policyholders')->with('policyholders', $policyholders)->with('step', $step)->with('pages', $pages);
;
   
	}

}
?>
