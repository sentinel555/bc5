<?php

namespace App\Http\Controllers;  
use \Illuminate\Support\Facades\Session;
use \Illuminate\Support\Facades\Redirect;
use \Illuminate\Support\Facades\Auth;


class NewbondController extends Controller {

    /**
     * Routing Information
     */
	public function getIndex() {

	if (Auth::check() == false) {

		return redirect('auth/login');
	}	 


		if(Session::has('ammend_bond')) {Session::forget('ammend_bond');}
		if(Session::has('bond_id')) {Session::forget('bond_id');}
		if(Session::has('bond_insurer')) {Session::forget('bond_insurer');}
		if(Session::has('bond_policy_number')) {Session::forget('bond_policy_number');}
		return redirect('bondview');
	}
	
}
?>
