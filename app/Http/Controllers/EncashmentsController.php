<?php

namespace App\Http\Controllers;  
use App\Models\Encashments as Encashments;
use \Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Input;
use \Illuminate\Support\Facades\Session;
use \Illuminate\Support\Facades\Validator;
use \Illuminate\Support\Facades\View;
use \Illuminate\Support\Facades\Redirect;
use \Illuminate\Support\Facades\Auth;

class EncashmentsController extends Controller {

	/**
	 * Routing Information
	 */
	public function getIndex() {

	if (Auth::check() == false) {

		return redirect('auth/login');
	}	 

		if(!Session::has('bond_id')) { return redirect('bonds'); }

		$bond = array();
		$encashments = array();
		$segments = array();

			 $bond = DB::table('bonds')->where('id', Session::get('bond_id'))->where('user_id', Auth::user()->id)->first();             
			$encashments = Encashments::load_encashments(Session::get('bond_id'), Auth::user()->id);
			$segments = DB::table('segments')->where('bond_id', Session::get('bond_id'))->where('user_id', Auth::user()->id)->count('id');



                return View::make('bond.encashment')->with('bond', $bond)->with('encashments', $encashments)->with('segments', $segments);

        
                return dd(Input::old());
		
	}


	public function postIndex() {


		Input::flashExcept('_token');

		$bond_id = Input::get('bond_id');
		$control_encashment = Input::get('control_encashment');
		$increment_id = Input::get('increment_id');

		$error = 0;
		$deleted  = 0;
		$rules = array();
		
		
		
		$rules = array(
			'segments_proceeds' => 'required|min:1|max:50000000|numeric|regex:/^[\d]{1,8}\.{0,1}[\d]{0,2}$/',
			'segments_encashment_date' => 'required|date_format:"d/m/Y"|before:"now +1 day"',
		);


		$attributes = array(
                	'segments_proceeds' => 'encashment proceeds',
               		'segments_encashment_date' => 'encashment date'
                );

		$bond = DB::table('bonds')->where('id', Session::get('bond_id'))->where('user_id', Auth::user()->id)->first();             
		
		$encashments = Encashments::load_encashments(Session::get('bond_id'), Auth::user()->id);

		$segments = DB::table('segments')->where('bond_id', Session::get('bond_id'))->where('user_id', Auth::user()->id)->count('id');
		

		


		if($control_encashment == 1) {

			Encashments::where('id', '=', Input::get('encashment_id'))->where('bond_id', '=', Input::get('bond_id'))->where('user_id', '=', Auth::user()->id)->delete();

			Session::flash('message', '&nbsp;Encashment record successfully deleted.');
		
			$encashments = Encashments::load_encashments(Session::get('bond_id'), Auth::user()->id);

			$segments = DB::table('segments')->where('bond_id', Session::get('bond_id'))->where('user_id', Auth::user()->id)->count('id');
		
			$html = View::make('bond.encashment')->with('bond', $bond)->with('encashments', $encashments)->with('segments', $segments);
			return($html);


		}
				
		$validation = Validator::make(Input::all(), $rules);
		$validation->setAttributeNames($attributes);
		if ($validation->fails())
		{

		Session::flash('message', '&nbsp;Errors have been detected. Please review the page and re-validate.');

		$html = View::make('bond.encashment')->withErrors($validation)->with('bond', $bond)->with('encashments', $encashments)->with('segments', $segments);
		return($html);

		}

		if($control_encashment == 2) {

		$sd = preg_split('/[\/\.-]/', Input::get('segments_encashment_date'));
                $ed = preg_split('/[\/\.-]/', $bond->encashment_date);
                $zd = preg_split('/[\/\.-]/', $bond->commencement_date);

		$e1 = null;
		
		$s1 = strtotime($sd[2].$sd[1].$sd[0]); 
		if($bond->auto_update == 1) {$e1 = strtotime('now');} else {$e1 = strtotime($ed[0].$ed[1].$ed[2]); }
		$z1 = strtotime($zd[0].$zd[1].$zd[2]); 
		
		if ($s1 >= $e1) {

                        Session::flash('message', '&nbsp;The date of the segment encashment must be before the final encashment date of the bond.');

			$error = 1;

		}

		if ($s1 <= $z1) {

                        Session::flash('message', '&nbsp;The date of the segment encashment cannot be before the commencement date of the bond.');

			$error = 1;

		}



		$new_segment_range = range(Input::get('segment_start'), Input::get('segment_end'));

	if(count($encashments) > 0) {

		for($z = 0; $z < count($encashments); $z++) {

			$segment_range = range($encashments[$z]['segment_start'], $encashments[$z]['segment_end']);

	                $diff = array_intersect($segment_range, $new_segment_range);
	
			if(count($diff) > 0) {

				sort($diff);

				$segmentString = null;

				for($x=0; $x < count($diff); $x++) {

					$segmentString .= $diff[$x];

					if(count($diff) > 1 && $x < count($diff) - 1) {$segmentString .=", ";}

						}

				Session::flash('message', '&nbsp;Segment Numbers '.$segmentString.' in the range that you have selected are already flagged as having been encashed. Please alter the selected range, or enter multiple ranges if you have already encashed a segment in the middle of a required range.');

				$error = 1;

					}
				}
			}


	if($error == 0 && $deleted == 0) {	

		 Encashments::create_new_encashment(Input::all(), Session::get('bond_id'), Auth::user()->id);

 		Session::flash('message', '&nbsp;Information successfully updated.');

	}


	}	

		$bond = DB::table('bonds')->where('id', Session::get('bond_id'))->where('user_id', Auth::user()->id)->first();             

		$encashments = Encashments::load_encashments(Session::get('bond_id'), Auth::user()->id);

		$segments = DB::table('segments')->where('bond_id', Session::get('bond_id'))->where('user_id', Auth::user()->id)->count('id');

		$html =  View::make('bond.encashment')->withErrors($validation)->with('bond', $bond)->with('encashments', $encashments)->with('segments', $segments);

		return($html);
		

	}
}
?>
