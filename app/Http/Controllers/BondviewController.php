<?php

namespace App\Http\Controllers;  
use \Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Input;
use \Illuminate\Support\Facades\Session;
use \Illuminate\Support\Facades\View;
use \Illuminate\Support\Facades\Auth;

class BondviewController extends Controller {

	/**
	 * Routing Information
	 */
	public function getIndex() {

	if (Auth::check() == false) {

		return redirect('auth/login');
	}	 


		$bond = array();
		$new_bond = 0;

		if(Session::has('all_segments')) {
			Session::forget('all_segments');
		}
		
		if(Session::has('sb')) {
			Session::forget('sb');
		}

		if(Session::has('ammend_bond')) { 
			if(Session::get('ammend_bond') == 1)
		{       $bond = DB::table('bonds')->where('id', Session::get('bond_id'))->where('user_id', Auth::user()->id)->first();            
			$new_bond = 1;	 }              
	       	


		$cd = preg_split('/[\/\.-]/', $bond->commencement_date);
		$ed = preg_split('/[\/\.-]/', $bond->encashment_date);

                $bond->commencement_date = $cd[2].'/'.$cd[1].'/'.$cd[0];
                $bond->encashment_date = $ed[2].'/'.$ed[1].'/'.$ed[0];
			

		if($bond->auto_update == 1) {


                	$bond->encashment_date = date('d').'/'.date('m').'/'.date('Y');

			}

		}

                return View::make('bondview')->with('bond', $bond)->with('new_bond', $new_bond);

                return dd(Input::old());
		
	}


	public function postIndex() {

   
	}

}
?>
