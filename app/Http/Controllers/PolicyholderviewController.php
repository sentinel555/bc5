<?php

namespace App\Http\Controllers;  
use \Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Session;
use \Illuminate\Support\Facades\View;
use \Illuminate\Support\Facades\Redirect;
use \Illuminate\Support\Facades\Auth;

class PolicyholderviewController extends Controller {

	/**
	 * Routing Information
	 */
	public function getIndex() {

	if (Auth::check() == false) {

		return redirect('auth/login');
	}	 


		$policyholder = array();
		$new_policyholder = 0;

	
		if(Session::has('ammend_policyholder')) { 
			if(Session::get('ammend_policyholder') == 1)
		{       $policyholder = DB::table('policyholders')->where('id', Session::get('policyholder_id'))->where('user_id', Auth::user()->id)->first();            
			$new_policyholder  = 1;	 }              
	       	}

                return View::make('policyholderview')->with('policyholder', $policyholder)->with('new_policyholder', $new_policyholder);

                return dd(Input::old());
		
			

	}


	public function postIndex() {

	}

}
?>
