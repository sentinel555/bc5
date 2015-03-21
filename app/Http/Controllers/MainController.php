<?php

namespace App\Http\Controllers;  
use \Illuminate\Support\Facades\Session;
use \Illuminate\Support\Facades\Auth;
use \Illuminate\Support\Facades\Redirect;
use \Illuminate\Support\Facades\URL;
use \Illuminate\Support\Facades\View;

class MainController extends Controller {

    /**
     * Routing Information
     */
	public function getIndex() {


	if (Auth::check() == false) {

		return redirect('auth/login');
	}	 


		//Session::put('id', Auth::user()->id);		
		return View::make('main');
	}	
	
	
}
?>
