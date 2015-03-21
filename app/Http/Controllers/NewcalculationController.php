<?php

namespace App\Http\Controllers;  
use \Illuminate\Support\Facades\Session;
use \Illuminate\Support\Facades\Redirect;
use \Illuminate\Support\Facades\Auth;


class NewcalculationController extends Controller {

    /**
     * Routing Information
     */
	public function getIndex() {

	if (Auth::check() == false) {

		return redirect('auth/login');
	}	 


		if(Session::has('calculation_id')) {Session::forget('calculation_id');}
		if(Session::has('bond_id')) {Session::forget('bond_id');}
		return redirect('calculation');
	}
	
}
?>
