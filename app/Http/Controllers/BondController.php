<?php
namespace App\Http\Controllers;  
use App\Models\Bond as Bond;
use \Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Input;
use \Illuminate\Support\Facades\Session;
use \Illuminate\Support\Facades\Validator;
use \Illuminate\Support\Facades\Auth;
use \Illuminate\Support\Facades\Redirect;
use \Illuminate\Support\Facades\View;

class BondController extends Controller {


	public function getIndex() {


	if (Auth::check() == false) {

		return redirect('auth/login');
	}	 


		$bond = array();

		if(Session::has('ammend_bond')) { 

			if(Session::get('ammend_bond') == 1) {
				$bond = DB::table('bonds')->where('id', '=', Session::get('bond_id'))->where('user_id', '=', Auth::user()->id)->first();
			}             

		if($bond->auto_update == 1) {$bond->encashment_date = date('Y').'-'.date('m').'-'.date('d');}

                }

                return View::make('bond.bond')->with('bond', $bond);

                return dd(Input::old());
		
	}


	public function postIndex() {
	
		Input::flashExcept('_token');

		$error = 0;

		$bond = null;

	$rules = array(
		'insurer' => 'required|min:2|max:50|alpha_spaces',
		'policy_number' => 'required|min:2|max:50|alpha_spaces',
		'investment' => 'required|min:1|max:50000000|numeric|regex:/^[\d]{1,8}\.{0,1}[\d]{0,2}$/',
		'encashment_proceeds' => 'required|max:50000000|numeric|regex:/^[\d]{1,8}\.{0,1}[\d]{0,2}$/',
		'segments' => 'required|max:1000|numeric|regex:/^[\d]{1,4}$/',
		'commencement_date' => 'required|date_format:"d/m/Y"|before:"now"',
		'encashment_date' => 'required|date_format:"d/m/Y"|before:"now +1 day"'
		);
	
	$attributes = array(
		'policy_number' => 'policy number',
		'investment' => 'initial investment amount',
		'encashment_proceeds' => 'surrender proceeds &#47; current value',
		'segments' => 'number of segments'
		);

		if(Session::has('ammend_bond')) { 
			if(Session::get('ammend_bond') == 1) {
				$bond = DB::table('bonds')->where('id', Session::get('bond_id'))->where('user_id', Auth::user()->id)->first();
			}
		}


		$validation = Validator::make(Input::all(), $rules);
		$validation->setAttributeNames($attributes);
		if ($validation->fails())
		{

		Session::flash('message', '&nbsp;Errors have been detected. Please review the page and re-validate.');

		$html = null;
		
		if(Session::has('ammend_bond')) { 
			if(Session::get('ammend_bond') == 1) {
				$html = View::make('bond.bond')->withErrors($validation)->with('bond', $bond);
				}
			} else { $html = View::make('bond.bond')->withErrors($validation); }

		return($html);
		
		} else {

		

                $sd = preg_split('/[\/\.-]/', Input::get('commencement_date'));
                $ed = preg_split('/[\/\.-]/', Input::get('encashment_date'));

		$s1 = strtotime($sd[2].$sd[1].$sd[0]); 
		$e1 = strtotime($ed[2].$ed[1].$ed[0]); 

		//echo $s1.' '.$e1;exit;
		if(Session::has('ammend_bond')) { 
			if(Session::get('ammend_bond') == 1) {
				if($bond->auto_update == 1) {$e1 = strtotime('now');}
			}
		}
		
		if ($e1 <= $s1) {

                        Session::flash('message', '&nbsp;The commencement date of the bond must be before the encashment date of the bond.');

			$error = 1;

		}

		if($error == 0) {
		
		if(Session::has('ammend_bond')) { if(Session::get('ammend_bond') == 1) {


			Bond::update_existing_bond(Input::all(), Session::get('bond_id'), Auth::user()->id);
			Session::flash('message', '&nbsp;Information successfully updated.');
			}
		}

		 else {

			Bond::create_new_bond(Input::all(), Auth::user()->id);
			Session::flash('message', '&nbsp;New bond record successfully created.');
		}

		
		}

		$html = null;

		if($error == 0) { $bond = DB::table('bonds')->where('id', Session::get('bond_id'))->where('user_id', Auth::user()->id)->first();

		$html = View::make('bond.bond')->withErrors($validation)->with('bond', $bond); } else 
	{$html = View::make('bond.bond')->withErrors($validation);}

		return($html);
  
	}

	}
}	
?>
