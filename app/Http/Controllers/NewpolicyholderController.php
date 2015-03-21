<?php

namespace App\Http\Controllers;  
use \Illuminate\Support\Facades\Session;
use \Illuminate\Support\Facades\Redirect;
use \Illuminate\Support\Facades\Auth;


class NewpolicyholderController extends Controller {

    /**
     * Routing Information
     */
	public function getIndex() {


	if (Auth::check() == false) {

		return redirect('auth/login');
	}	 



		if(Session::has('ammend_policyholder')) {Session::forget('ammend_policyholder');}
		if(Session::has('policyholder_id')) {Session::forget('policyholder_id');}
		return Redirect::to('policyholderview');
	}
	
}
?>
