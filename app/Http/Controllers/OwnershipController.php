<?php

namespace App\Http\Controllers;  
use App\Models\Ownerships as Ownerships;
use \Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Input;
use \Illuminate\Support\Facades\Session;
use \Illuminate\Support\Facades\Validator;
use \Illuminate\Support\Facades\View;
use \Illuminate\Support\Facades\Redirect;
use \Illuminate\Support\Facades\Auth;


class OwnershipController extends Controller {

	/**
	 * Routing Information
	 */
	public function getIndex() {

	if (Auth::check() == false) {

		return redirect('auth/login');
	}	 


		if(!Session::has('bond_id')) { return redirect('bonds'); }

		$bond = array();
		$ownership = array();
		$policyholders = array();
		$increments = array();
		$total_segments = 0;
		$score = 0;
		$maxscore = 0;

		$owners = Ownerships::owners(Auth::user()->id);



			 $bond = DB::table('bonds')->where('id', Session::get('bond_id'))->where('user_id', Auth::user()->id)->first();             
			$ownership = Ownerships::ownership_details(Session::get('bond_id'), Auth::user()->id);
			$policyholders = Ownerships::policyholders(Session::get('bond_id'), Auth::user()->id);
			$increments = Ownerships::increments(Session::get('bond_id'), Auth::user()->id);
			$total_segments = $bond->segments;

			foreach($increments as $id => $output)  {

                        	$total_segments += $output->increment_segments;

                	}


			$splits = Ownerships::total_percentage_split(Session::get('bond_id'), Auth::user()->id);

			$maxscore = $total_segments * 100;

			for($z =0; $z < count($splits); $z++) {

				$score += (($splits[$z]['segment_end'] - $splits[$z]['segment_start']) + 1) * $splits[$z]['percentage_split'];

			}
			

			$score = number_format((($score / $maxscore) * 100), 2, '.', ',');

                return View::make('bond.ownership')->with('owners', $owners)->with('bond', $bond)->with('ownership', $ownership)->with('policyholders', $policyholders)->with('increments', $increments)->with('total_segments', $total_segments)->with('score', $score);

        
                return dd(Input::old());
		
	}


	public function postIndex() {


		Input::flashExcept('_token');

		$bond_id = Input::get('bond_id');
		$control_ownership = Input::get('control_ownership');
		$policyholder_id = Input::get('policyholder_id');
		$ownership_id = Input::get('ownership_id');

		$error = 0;
		$deleted  = 0;

		$policyholders = array();
		$rules = array(
			'percentage_split' => 'min:1|max:100|numeric|required',
			'assignment_date' => 'date_format:"d/m/Y"|before:"now +1 day"',
			'security_debt_date' => 'date_format:"d/m/Y"|before:"now +1 day"'
		);

		$messages = array(
			'assignment_date.date_format' => 'The assignment date must be entered in a valid date format.',
			'security_debt_date.date_format' => 'The date upon which the the segments were used as security for a debt must be entered in a valid date format.'
		);

		$attributes = array(
			'security_debt_date' => 'date when the segments were used as security for a debt'
                );

		$bond_id = Input::get('bond_id');
		$control_ownership = Input::get('control_ownership');
		$policyholder_id = Input::get('policyholder_id');
		$ownership_id = Input::get('ownership_id');

		$owners = Ownerships::owners(Auth::user()->id);

			 $bond = DB::table('bonds')->where('id', Session::get('bond_id'))->where('user_id', Auth::user()->id)->first();             
			$ownership = Ownerships::ownership_details(Session::get('bond_id'), Auth::user()->id);
			$policyholders = Ownerships::policyholders(Session::get('bond_id'), Auth::user()->id);
			$increments = Ownerships::increments(Session::get('bond_id'), Auth::user()->id);
			$total_segments = $bond->segments;

			foreach($increments as $id => $output)  {

                        	$total_segments += $output->increment_segments;

                	}


			$splits = Ownerships::total_percentage_split(Session::get('bond_id'), Auth::user()->id);

			$maxscore = $total_segments * 100;
			$score = 0;

			for($z =0; $z < count($splits); $z++) {

				$score += (($splits[$z]['segment_end'] - $splits[$z]['segment_start']) + 1) * $splits[$z]['percentage_split'];

			}
			

			$temp_score = $score;


			$score = number_format((($score / $maxscore) * 100), 2, '.', ',');

		


		if($control_ownership == 1) {

		DB::table('ownerships')->where('id', '=', $ownership_id)->where('user_id', '=', Auth::user()->id)->where('policyholder_id', '=', $policyholder_id)->where('bond_id', '=', $bond_id)->where('id', $ownership_id)->delete();

		Session::flash('message', '&nbsp;Ownership record successfully deleted.');

		$deleted = 1;

		}
		

		$validation = Validator::make(Input::all(), $rules, $messages);
		$validation->setAttributeNames($attributes);

		if ($validation->fails() && $deleted == 0)
		{

		Session::flash('message', '&nbsp;Errors have been detected. Please review the page and re-validate.');

		$html = View::make('bond.ownership')->withErrors($validation)->with('owners', $owners)->with('bond', $bond)->with('ownership', $ownership)->with('policyholders', $policyholders)->with('increments', $increments)->with('score', $score)->with('total_segments', $total_segments);

		return($html);

		}

		if($control_ownership == 2) {

			if($deleted == 0) {

			if($temp_score + (((Input::get('segment_end') - Input::get('segment_start'))+1) *  Input::get('percentage_split')) > $maxscore) {

		Session::flash('message', '&nbsp;Errors have been detected. The value entered in combination with any existing values cannot be greater than 100 percent of all segments.');

		$error = 1;

		}


		$segRange = range(Input::get('segment_start'), Input::get('segment_end'));

			for ($z = 0; $z < count($splits); $z++) {

				$match = array_intersect(range($splits[$z]['segment_start'], $splits[$z]['segment_end']), $segRange);
				if(count($match) > 0) {
					sort($match);

					for($x = 0; $x < count($match); $x++) {

						if($splits[$z]['percentage_split'] + Input::get('percentage_split') > 100) { Session::flash('message', '&nbsp;Errors have been detected. You cannot allocate more than 100% for segment number '.($match[$x]).'. Either check your input, or delete existing segments and enter the correct number of new segments.');

		
				$error = 1; break;
}
					}
				
					}
			}
		}

	if($error == 0 && $deleted == 0) {	

		 Ownerships::create_new_ownership(Input::all(), Session::get('bond_id'), Auth::user()->id);

 		Session::flash('message', '&nbsp;Information successfully updated.');

	}


	}	

			$owners = Ownerships::owners(Auth::user()->id);

			 $bond = DB::table('bonds')->where('id', Session::get('bond_id'))->where('user_id', Auth::user()->id)->first();             
			$ownership = Ownerships::ownership_details(Session::get('bond_id'), Auth::user()->id);
			$policyholders = Ownerships::policyholders(Session::get('bond_id'), Auth::user()->id);
			$increments = Ownerships::increments(Session::get('bond_id'), Auth::user()->id);
			$total_segments = $bond->segments;

			foreach($increments as $id => $output)  {

                        	$total_segments += $output->increment_segments;

                	}


			$splits = Ownerships::total_percentage_split(Session::get('bond_id'), Auth::user()->id);

			$maxscore = $total_segments * 100;
			$score = 0;


			for($z =0; $z < count($splits); $z++) {

				$score += (($splits[$z]['segment_end'] - $splits[$z]['segment_start']) + 1) * $splits[$z]['percentage_split'];

			}
			

			$temp_score = $score;

			$score = number_format((($score / $maxscore) * 100), 2, '.', ',');

	$html = null;

	if($deleted == 0) { $html = View::make('bond.ownership')->withErrors($validation)->with('owners', $owners)->with('bond', $bond)->with('ownership', $ownership)->with('policyholders', $policyholders)->with('increments', $increments)->with('score', $score)->with('total_segments', $total_segments); }

	if($deleted == 1) { $html = View::make('bond.ownership')->with('owners', $owners)->with('bond', $bond)->with('ownership', $ownership)->with('policyholders', $policyholders)->with('increments', $increments)->with('score', $score)->with('total_segments', $total_segments); }

		return($html);
		

	}



}
?>
