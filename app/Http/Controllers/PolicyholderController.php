<?php

namespace App\Http\Controllers;  
use App\Models\Policyholder as Policyholder;
use \Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Input;
use \Illuminate\Support\Facades\Session;
use \Illuminate\Support\Facades\Validator;
use \Illuminate\Support\Facades\View;
use \Illuminate\Support\Facades\Redirect;
use \Illuminate\Support\Facades\Auth;

class PolicyholderController extends Controller {

    /**
     * Routing Information
     */
	public function getIndex() {
	
	if (Auth::check() == false) {

		return redirect('auth/login');
	}	 


	$day = date('d');
        $month = date('m');
        $year = date('Y');
        $taxYear = $year;

        if($month > 4 || ($month == 4 && $day >= 6)) { $taxYear = ++$year;} else {$taxYear = $year;}

		$policyholder = array();
		$personal_allowance = DB::table('tax_rates')->where('tax_year_ending', $taxYear)->pluck('personal_allowance');

		if(Session::has('ammend_policyholder')) { if(Session::get('ammend_policyholder') == 1)
		{	$policyholder = DB::table('policyholders')->where('id', '=', Session::get('policyholder_id'))->where('user_id', '=', Auth::user()->id)->first();}		
		}

		return View::make('policyholder')->with('policyholder', $policyholder)->with('personal_allowance', $personal_allowance);

		
	
		return dd(Input::old());


	}
	

	public function postIndex() {

		Input::flashExcept('_token');
	
		$day = date('d');
	        $month = date('m');
	        $year = date('Y');
	        $taxYear = $year;
	
	        if($month > 4 || ($month == 4 && $day >= 6)) { $taxYear = ++$year;} else {$taxYear = $year;}

		$personal_allowance = DB::table('tax_rates')->where('tax_year_ending', $taxYear)->pluck('personal_allowance');

		$policyholder = array();

		$rules = array(
		'first_name' => 'required|min:2|max:50|alpha_spaces',
		'surname' => 'required|min:2|max:50|alpha_spaces',
		'dob' => 'required|date_format:"d/m/Y"|before:"now"',
		'allowances' => 'required|max:30000|numeric',
		'gross_income' => 'required|max:9999999|numeric',
		'deceased_on' => 'required_with:deceased|date_format:"d/m/Y"|before:"now +1 day"'
		);
		$validation = Validator::make(Input::all(), $rules);
	
		if ($validation->fails())
		{

		Session::flash('message', 'Errors have been detected. Please review the page and re-validate.');
		$html = View::make('policyholder')->with('policyholder', $policyholder)->with('personal_allowance', $personal_allowance)->withErrors($validation);

		return($html);
		}
		
		if(Session::has('ammend_policyholder')) { if(Session::get('ammend_policyholder') == 1) {

			Policyholder::update_existing_policyholder(Input::all(), Session::get('policyholder_id'), Auth::user()->id);
		}

		} else {

			Policyholder::create_new_policyholder(Input::all(), Auth::user()->id);
		}



		Session::flash('message', 'Information successfully updated.');

		$policyholder = DB::table('policyholders')->where('id', Session::get('policyholder_id'))->where('user_id', Auth::user()->id);

		$html = View::make('policyholder')->with('personal_allowance', $personal_allowance)->with('policyholder', $policyholder)->withErrors($validation);

		return($html);
   
	}

}
?>
