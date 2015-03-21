<?php

namespace App\Http\Controllers;  
use App\Models\Increments as Increments;
use \Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Input;
use \Illuminate\Support\Facades\Session;
use \Illuminate\Support\Facades\Validator;
use \Illuminate\Support\Facades\View;
use \Illuminate\Support\Facades\Redirect;
use \Illuminate\Support\Facades\Auth;


class IncrementsController extends Controller {

	/**
	 * Routing Information
	 */
	public function getIndex() {

	if (Auth::check() == false) {

		return redirect('auth/login');
	}	 



		if(!Session::has('bond_id')) { return redirect('bonds'); }

		$bond = array();
		$increments = array();

			 $bond = DB::table('bonds')->where('id', Session::get('bond_id'))->where('user_id', Auth::user()->id)->first();             
			$increments = Increments::load_increments(Session::get('bond_id'), Auth::user()->id);

		

			for($z=0; $z < count($increments); $z++)  {

                        	$bond->segments += $increments[$z]['increment_segments'];

                	}


                return View::make('bond.increment')->with('bond', $bond)->with('increments', $increments);

        
                return dd(Input::old());
		
	}


	public function postIndex() {


		Input::flashExcept('_token');

		$bond_id = Input::get('bond_id');
		$control_increment= Input::get('control_increment');
		$increment_id = Input::get('increment_id');

		$error = 0;
		$deleted  = 0;

		$rules = array(
			'increment_amount' => 'required|min:1|max:50000000|numeric|regex:/^[\d]{1,8}\.{0,1}[\d]{0,2}$/',
			'increment_commencement_date' => 'required|date_format:"d/m/Y"|before:"now"',
			'increment_segments' => 'required|min:0|max:1000|numeric'
		);

		$attributes = array(
                        'increment_segments' => 'number of segments'
                );

		$bond = DB::table('bonds')->where('id', Session::get('bond_id'))->where('user_id', Auth::user()->id)->first();             

		$increments = Increments::load_increments(Session::get('bond_id'), Auth::user()->id);

		$inc_seg = DB::table('increments')->where('bond_id', Session::get('bond_id'))->where('user_id', Auth::user()->id)->where('id', Input::get('increment_id'))->pluck('increment_segments'); // count number of segments of the increment we are going to delete

		$segments = $bond->segments;

			
			for($z=0; $z < count($increments); $z++)  {

                        	$bond->segments += $increments[$z]['increment_segments'];

                	}
	


		if($control_increment == 1) {

		Increments::delete_increments(Auth::user()->id, $increment_id, $bond, $segments, $inc_seg);

		Session::flash('message', '&nbsp;Increment record successfully deleted.');
		
		$bond = DB::table('bonds')->where('id', Session::get('bond_id'))->where('user_id', Auth::user()->id)->first();             

		$increments = Increments::load_increments(Session::get('bond_id'), Auth::user()->id);

		$inc_seg = DB::table('increments')->where('bond_id', Session::get('bond_id'))->where('user_id', Auth::user()->id)->where('id', Input::get('increment_id'))->pluck('increment_segments'); // count number of segments of the increment we are going to delete

		$segments = $bond->segments;

			
			for($z=0; $z < count($increments); $z++)  {

                        	$bond->segments += $increments[$z]['increment_segments'];

                	}


		$html =  View::make('bond.increment')->with('bond', $bond)->with('increments', $increments);

		return($html);

		}
		
		$validation = Validator::make(Input::all(), $rules);
		$validation->setAttributeNames($attributes);
		if ($validation->fails())
		{

		Session::flash('message', '&nbsp;Errors have been detected. Please review the page and re-validate.');

		$html = View::make('bond.increment')->withErrors($validation)->with('bond', $bond)->with('increments', $increments);
		return($html);

		}


		if($control_increment == 2) {

		$sd = preg_split('/[\/\.-]/', Input::get('increment_commencement_date'));
                $ed = preg_split('/[\/\.-]/', $bond->encashment_date);
                $zd = preg_split('/[\/\.-]/', $bond->commencement_date);

		$e1 = null;
		
		$s1 = strtotime($sd[2].$sd[1].$sd[0]); 
		if($bond->auto_update == 1) {$e1 = strtotime('now');} else {$e1 = strtotime($ed[0].$ed[1].$ed[2]); }
		$z1 = strtotime($zd[0].$zd[1].$zd[2]); 


		if ($s1 >= $e1) {

                        Session::flash('message', '&nbsp;The date of the increment must be before the encashment date of the bond.');

			$error = 1;

		}

		if ($s1 <= $z1) {

                        Session::flash('message', '&nbsp;The date of the increment cannot be before the commencement date of the bond.');

			$error = 1;

		}

	if($error == 0) {	


		 Increments::create_new_increment(Input::all(), Session::get('bond_id'), Auth::user()->id);

 		Session::flash('message', '&nbsp;Information successfully updated.');

	}


	}	

		$bond = DB::table('bonds')->where('id', Session::get('bond_id'))->where('user_id', Auth::user()->id)->first();             

		$increments = Increments::load_increments(Session::get('bond_id'), Auth::user()->id);

		$html =  View::make('bond.increment')->withErrors($validation)->with('bond', $bond)->with('increments', $increments);

		return($html);
		

	}
}
?>
