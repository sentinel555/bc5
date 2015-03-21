<?php

namespace App\Http\Controllers;  
use App\Models\Policyloans as Policyloans;
use \Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Input;
use \Illuminate\Support\Facades\Session;
use \Illuminate\Support\Facades\Validator;
use \Illuminate\Support\Facades\View;
use \Illuminate\Support\Facades\Redirect;
use \Illuminate\Support\Facades\Auth;

class PolicyloansController extends Controller {

	/**
	 * Routing Information
	 */
	public function getIndex() {

	if (Auth::check() == false) {

		return redirect('auth/login');
	}	 


		if(!Session::has('bond_id')) { return redirect('bonds'); }

		$bond = array();
		$policyloans = array();

			 $bond = DB::table('bonds')->where('id', Session::get('bond_id'))->where('user_id', Auth::user()->id)->first();             
			$policyloans = Policyloans::load_policyloans(Session::get('bond_id'), Auth::user()->id);


                return View::make('bond.policyloan')->with('bond', $bond)->with('policyloans', $policyloans);

        
                return dd(Input::old());
		
	}


	public function postIndex() {


		Input::flashExcept('_token');

		$bond_id = Input::get('bond_id');
		$control_policyloan = Input::get('control_policyloan');
		$policyloan_id = Input::get('policyloan_id');

		$error = 0;

		$rules = array(
			'policy_loan' => 'required|min:1|max:50000000|numeric',
			'policy_loan_date' => 'required|date_format:"d/m/Y"|before:"now +1 day"',
			'capital_repayment' => 'required|min:0|max:50000000|numeric'
		);


		$bond = DB::table('bonds')->where('id', Session::get('bond_id'))->where('user_id', Auth::user()->id)->first();             

		$policyloans = Policyloans::load_policyloans(Session::get('bond_id'), Auth::user()->id);


		if($control_policyloan == 1) {

		Policyloans::where('id', '=', $policyloan_id)->where('bond_id', '=', $bond_id)->where('user_id', '=', Auth::user()->id)->delete();

		$policyloans = Policyloans::load_policyloans(Session::get('bond_id'), Auth::user()->id);

		Session::flash('message', '&nbsp;Policy Loan record successfully deleted.');

		$html = View::make('bond.policyloan')->with('bond', $bond)->with('policyloans', $policyloans);
		return($html);

		}
		
		$validation = Validator::make(Input::all(), $rules);
		if ($validation->fails())
		{

		Session::flash('message', '&nbsp;Errors have been detected. Please review the page and re-validate.');

		$html = View::make('bond.policyloan')->withErrors($validation)->with('bond', $bond)->with('policyloans', $policyloans);
		return($html);

		}


		if($control_policyloan == 2) {

		$sd = preg_split('/[\/\.-]/', Input::get('policy_loan_date'));
                $ed = preg_split('/[\/\.-]/', $bond->encashment_date);
                $zd = preg_split('/[\/\.-]/', $bond->commencement_date);

		$e1 = null;
		
		$s1 = strtotime($sd[2].$sd[1].$sd[0]); 
		if($bond->auto_update == 1) {$e1 = strtotime('now');} else {$e1 = strtotime($ed[0].$ed[1].$ed[2]); }
		$z1 = strtotime($zd[0].$zd[1].$zd[2]); 
		
		if ($s1 >= $e1) {

                        Session::flash('message', '&nbsp;The date of the policy loan must be before the final encashment date of the bond.');

			$error = 1;

		}

		if ($s1 <= $z1) {

                        Session::flash('message', '&nbsp;The date of the policy loan cannot be before the commencement date of the bond.');

			$error = 1;

		}


	if($error == 0) {	

		 Policyloans::create_policy_loan(Input::all(), Session::get('bond_id'), Auth::user()->id);

 		Session::flash('message', 'Information successfully updated.');

	}


	}	

		$bond = DB::table('bonds')->where('id', Session::get('bond_id'))->where('user_id', Auth::user()->id)->first();             

		$policyloans = Policyloans::load_policyloans(Session::get('bond_id'), Auth::user()->id);

		$html =  View::make('bond.policyloan')->withErrors($validation)->with('bond', $bond)->with('policyloans', $policyloans);

		return($html);
		

	}
}
?>
