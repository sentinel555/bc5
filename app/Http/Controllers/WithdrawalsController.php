<?php

namespace App\Http\Controllers;  
use App\Models\Withdrawals as Withdrawals;
use \Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Input;
use \Illuminate\Support\Facades\Session;
use \Illuminate\Support\Facades\Validator;
use \Illuminate\Support\Facades\View;
use \Illuminate\Support\Facades\Redirect;
use \Illuminate\Support\Facades\Auth;

class WithdrawalsController extends Controller {

    /**
     * Routing Information
     */
	public function getIndex() {


	if (Auth::check() == false) {

		return redirect('auth/login');
	}	 


		$annualAllowance = 0;
                $investment = 0;
                $user_data = array();
                $segmented_cumulative_allowance = array();
                $segmented_chargeable_event = array();
                $segmented_excess = array();
                $withdrawal_details = array();
		$sb = (Session::has('sb') ? Session::get('sb') : 0);
		if(Session::has('seg_count')) { Session::forget('seg_count'); }
	
		$wd = Withdrawals::display_withdrawals(Session::get('bond_id'), Auth::user()->id, $sb);

		return View::make('bond.withdrawal')->with('bond', $wd['bond'])->with('mode', $wd['mode'])->with('segment_blocks', $wd['segment_blocks'])->with('segment_start', $wd['segment_start'])->with('segment_end', $wd['segment_end'])->with('total_years', $wd['total_years'])->with('final_year', $wd['final_year'])->with('start_year', $wd['start_year'])->with('policy_loan_years', $wd['policy_loan_years'])->with('allowance', $wd['allowance'])->with('cumulative_allowance', $wd['cumulative_allowance'])->with('chargeable_event', $wd['chargeable_event'])->with('excess', $wd['excess'])->with('withdrawal', $wd['withdrawal_details'])->with('sb', $sb)->with('pointer', $wd['pointer']);

	//	return dd(Input::old());
	}


	public function postIndex() {

		if(!Session::has('bond_id')) { Redirect::to('bonds'); }
		
		
		$annualAllowance = 0;
                $investment = 0;
                $user_data = array();
                $segmented_cumulative_allowance = array();
                $segmented_chargeable_event = array();
                $segmented_excess = array();
                $withdrawal_details = array();
		$rules = array();
		$messages = array(); // array to output custom error messages
		$ctl = Input::get('control_withdrawal');

		$sb = (Input::has('segment_block') ? Input::get('segment_block') : (Session::has('sb') ? Session::get('sb') : 0));
		
		if(Session::has('seg_count')) { Session::forget('seg_count'); }

		$wd = Withdrawals::display_withdrawals(Session::get('bond_id'), Auth::user()->id, $sb);

 		////////////////////////////////////////////////////
		//
		//	Change Mode	
		//
		////////////////////////////////////////////////////


		if($ctl == 1) {

			DB::table('withdrawals')->where('user_id', '=',  Auth::user()->id)->where('bond_id', '=',  Session::get('bond_id'))->delete();
	
			DB::table('bonds')->where('id', Session::get('bond_id'))->where('user_id', Auth::user()->id)->update(array('mode' => Input::get('cmode')));


		Session::flash('message', '&nbsp;Withdrawal Mode successfully changed.');

		$wd = Withdrawals::display_withdrawals(Session::get('bond_id'), Auth::user()->id, $sb);

		$html =  View::make('bond.withdrawal')->with('bond', $wd['bond'])->with('mode', $wd['mode'])->with('segment_blocks', $wd['segment_blocks'])->with('segment_start', $wd['segment_start'])->with('segment_end', $wd['segment_end'])->with('total_years', $wd['total_years'])->with('final_year', $wd['final_year'])->with('start_year', $wd['start_year'])->with('policy_loan_years', $wd['policy_loan_years'])->with('allowance', $wd['allowance'])->with('cumulative_allowance', $wd['cumulative_allowance'])->with('chargeable_event', $wd['chargeable_event'])->with('excess', $wd['excess'])->with('withdrawal', $wd['withdrawal_details'])->with('sb', $sb)->with('pointer', $wd['pointer']);

		return($html);
		}



		////////////////////////////////////////////////////
		//
		//	Delete Segments (Extended Mode only)
		//
		////////////////////////////////////////////////////


		if($ctl == 4) {

			DB::table('withdrawals')->where('user_id', '=',  Auth::user()->id)->where('bond_id', '=',  Session::get('bond_id'))->where('segment_start', '=', Input::get('ss'))->where('segment_end', '=', Input::get('se'))->delete();
	
		Session::flash('message', '&nbsp;Withdrawals successfully deleted accross segments '.Input::get('ss').' - '.Input::get('se').'.');

		if(Session::has('sb')) {
                        Session::forget('sb');
                }

		$sb = 0; // Reset Segment Blocks

		$wd = Withdrawals::display_withdrawals(Session::get('bond_id'), Auth::user()->id, $sb);
		
		$html =  View::make('bond.withdrawal')->with('bond', $wd['bond'])->with('mode', $wd['mode'])->with('segment_blocks', $wd['segment_blocks'])->with('segment_start', $wd['segment_start'])->with('segment_end', $wd['segment_end'])->with('total_years', $wd['total_years'])->with('final_year', $wd['final_year'])->with('start_year', $wd['start_year'])->with('policy_loan_years', $wd['policy_loan_years'])->with('allowance', $wd['allowance'])->with('cumulative_allowance', $wd['cumulative_allowance'])->with('chargeable_event', $wd['chargeable_event'])->with('excess', $wd['excess'])->with('withdrawal', $wd['withdrawal_details'])->with('sb', $sb)->with('pointer', $wd['pointer']);

		return($html);
		}


		////////////////////////////////////////////////////
		//
		//	Jump to Segments Block (Extended Mode only)
		//
		////////////////////////////////////////////////////


		if($ctl == 5) {

			Session::put('segment_start', Input::get('ss'));	
			Session::put('segment_end', Input::get('se'));	
			//$sb = Input::get('segment_block');
			Session::forget('sb');
			Session::put('sb', $sb);
		
			$wd = Withdrawals::display_withdrawals(Session::get('bond_id'), Auth::user()->id, $sb);

			$html = View::make('bond.withdrawal')->with('bond', $wd['bond'])->with('mode', $wd['mode'])->with('segment_blocks', $wd['segment_blocks'])->with('segment_start', $wd['segment_start'])->with('segment_end', $wd['segment_end'])->with('total_years', $wd['total_years'])->with('final_year', $wd['final_year'])->with('start_year', $wd['start_year'])->with('policy_loan_years', $wd['policy_loan_years'])->with('allowance', $wd['allowance'])->with('cumulative_allowance', $wd['cumulative_allowance'])->with('chargeable_event', $wd['chargeable_event'])->with('excess', $wd['excess'])->with('withdrawal', $wd['withdrawal_details'])->with('sb', $sb)->with('pointer', $wd['pointer']);

			return($html);
		}


		////////////////////////////////////////////////////
		//
		//	Create New Segment Block (Extended Mode only)
		//
		////////////////////////////////////////////////////


		if($ctl == 6) {
			
			$error = 0;

			$a = Withdrawals::validate_segments(Input::get('ss'), Input::get('se'), $wd['segment_blocks']);

				if($a == 1) {

				Session::flash('seg_message', '&nbsp;You have selected a range of segments that conflict with an existing range of segments. You should either alter the range, or delete an existing range and re-enter the segments.');

					$error = 1;
				}

			       if($error == 0) 
			
				{ 

		$temp = Withdrawals::create_new_segment_block(Session::get('bond_id'), Auth::user()->id, Input::get('ss'), Input::get('se'), $wd['start_year'], $wd['total_years']);
			Session::forget('sb');
			Session::put('sb', $temp['segment_block']);
			$sb = $temp['segment_block'];
				Session::flash('seg_message', '&nbsp;New segment block successfully created.');

				}
				
			$wd = Withdrawals::display_withdrawals(Session::get('bond_id'), Auth::user()->id, $sb);

		$html = View::make('bond.withdrawal')->with('bond', $wd['bond'])->with('mode', $wd['mode'])->with('segment_blocks', $wd['segment_blocks'])->with('segment_start', $wd['segment_start'])->with('segment_end', $wd['segment_end'])->with('total_years', $wd['total_years'])->with('final_year', $wd['final_year'])->with('start_year', $wd['start_year'])->with('policy_loan_years', $wd['policy_loan_years'])->with('allowance', $wd['allowance'])->with('cumulative_allowance', $wd['cumulative_allowance'])->with('chargeable_event', $wd['chargeable_event'])->with('excess', $wd['excess'])->with('withdrawal', $wd['withdrawal_details'])->with('sb', $sb)->with('pointer', $wd['pointer']);

			return($html);
			
		}

			
 		////////////////////////////////////////////////////
		//
		//	Validate Input
		//
		////////////////////////////////////////////////////

	
		$wdl = Input::get('withdrawal');

                $error = 0;

			
			$rules['withdrawal_fixed'] = array("numeric", "regex:/^[\d]{1,8}\.{0,1}[\d]{0,4}$/");
			$rules['withdrawal_percentage'] = array("numeric", "max:100", "regex:/^[\d]{1,8}\.{0,1}[\d]{0,4}$/");
		
			if($ctl != 2) {

                        for($z = 0; $z < count($wdl); $z++) {
                        	$rules['withdrawal.'.$z] = array("required", "min:0", "max:50000000", "numeric", "regex:/^[\d]{1,8}\.{0,1}[\d]{0,4}$/");
                        }

			for($z = 0; $z < count($wdl); $z++) {
				$messages += array(

					'withdrawal.'.$z.'.required' => 'The withdrawals for year ending '.($wd['start_year'] + $z).' must be entered',
					'withdrawal.'.$z.'.min' => 'The minimum withdrawal for year ending '.($wd['start_year'] + $z).' is 0',
					'withdrawal.'.$z.'.max' => 'The maximum withdrawal for year ending '.($wd['start_year'] + $z).' is 50000000',
					'withdrawal.'.$z.'.numeric' => 'Only numeric values can be entered for for year ending '.($wd['start_year'] + $z),
					'withdrawal.'.$z.'.regex' => 'Values for year ending '.($wd['start_year'] + $z).' must be entered as a monetary value'
				
				);
                        }

			}

		$validation = Validator::make(Input::all(), $rules, $messages);

		
		if ($validation->fails())
                {

                Input::flashExcept('_token');
                Session::flash('message', '&nbsp;Errors have been detected. Please review the page and re-validate.');
		$html = View::make('bond.withdrawal')->withErrors($validation)->with('bond', $wd['bond'])->with('mode', $wd['mode'])->with('withdrawal', $wd['withdrawal_details'])->with('segment_blocks', $wd['segment_blocks'])->with('segment_start', $wd['segment_start'])->with('segment_end', $wd['segment_end'])->with('total_years', $wd['total_years'])->with('final_year', $wd['final_year'])->with('start_year', $wd['start_year'])->with('policy_loan_years', $wd['policy_loan_years'])->with('allowance', $wd['allowance'])->with('cumulative_allowance', $wd['cumulative_allowance'])->with('chargeable_event', $wd['chargeable_event'])->with('excess', $wd['excess'])->with('sb', $sb)->with('pointer', $wd['pointer']); 


		return($html);


                $error = 1;

                }


		////////////////////////////////////////////////////
		//
		//	Populate Withdrawals
		//
		////////////////////////////////////////////////////

		if($ctl == 2) {
			
			$error = 0;

			$a = 1; $b = 1; // Temp vars for segment_start, segment_end

			if(Input::get('withdrawal_fixed') == "" && Input::get('withdrawal_percentage') == "") {
				Session::flash('message', '&nbsp;Errors have been detected. You need to enter a value for either fixed withdrawals or an annual percentage.');

				$error = 1;
			} 

			if(Input::get('withdrawal_fixed') != "" && Input::get('withdrawal_percentage') != "") {
				Session::flash('message', '&nbsp;Errors have been detected. You cannot enter values for both fixed withdrawals or an annual percentage. Please remove one and repopulate.');

				$error = 1;
			}
			
			if(Input::get('withdrawal_fixed') != "" &&  Input::get('withdrawal_fixed') > $wd['bond']->encashment_proceeds) {

				Session::flash('message', '&nbsp;Errors have been detected. You cannot enter a withdrawal value that is greater than the current value of the bond.');

				$error = 1;
			} 

			
			
			if($error == 0) {

                        if($wd['mode'] == 1) {$a = Input::get('ss'); $b = Input::get('se');}

                        if(is_numeric(Input::get('withdrawal_percentage'))) {


				DB::table('withdrawals')->where('user_id', '=', Auth::user()->id)->where('bond_id', '=', Session::get('bond_id'))->where('segment_start', '=', $a)->where('segment_end', '=', $b)->delete();	

				$segment_break_points = Withdrawals::check_segments(Session::get('bond_id'), Auth::user()->id);

				Withdrawals::populate_withdrawal_percentages(Session::get('bond_id'), Auth::user()->id, $wd['start_year'], $wd['total_years'], $wd['pointer'], $wd['bond']->investment, Input::get('withdrawal_percentage'), $wd['increment_details'], $wd['encashment_details'], $wd['segments'], $segment_break_points, $a, $b, $wd['mode']);

				}

			if(is_numeric(Input::get('withdrawal_fixed'))) {

				DB::table('withdrawals')->where('user_id', '=', Auth::user()->id)->where('bond_id', '=', Session::get('bond_id'))->where('segment_start', '=', $a)->where('segment_end', '=', $b)->delete();	
				Withdrawals::populate_withdrawal_fixed(Session::get('bond_id'), Auth::user()->id, $wd['start_year'], $wd['total_years'], $wd['pointer'],  Input::get('withdrawal_fixed'), $a, $b);

			}	

		if($wd['mode'] == 1 && $error == 0) {

			$sb1 = Withdrawals::load_segment_blocks(Session::get('bond_id'), Auth::user()->id);	
			for($j = 0; $j < count($sb1); $j++) {

				if($sb1[$j]['segment_start'] == $a && $sb1[$j]['segment_end'] == $b) {$sb = $j;}

			}

		}

			
		Session::flash('message', '&nbsp;Withdrawals Successfully Updated.');

	
		}
		}	



		////////////////////////////////////////////////////
		//
		//	Validate Withdrawals
		//
		////////////////////////////////////////////////////

		if($ctl == 3) {


			if($error == 0) {

			$a = 1; $b = 1; // Temp vars for segment_start, segment_end

                        if($wd['mode'] == 0) {$a = 1; $b = 1;} elseif($wd['mode'] == 1) {$a = Input::get('ss'); $b = Input::get('se');}
			
				DB::table('withdrawals')->where('user_id', '=', Auth::user()->id)->where('bond_id', '=', Session::get('bond_id'))->where('segment_start', '=', $a)->where('segment_end', '=', $b)->delete();	
				Withdrawals::create_new_withdrawal($wdl, Session::get('bond_id'), Auth::user()->id, $wd['start_year'], $wd['total_years'], $wd['pointer'],  $a, $b);


		if($wd['mode'] == 1) {

			$sb1 = Withdrawals::load_segment_blocks(Session::get('bond_id'), Auth::user()->id);	
			for($j = 0; $j < count($sb1); $j++) {

				if($sb1[$j]['segment_start'] == $a && $sb1[$j]['segment_end'] == $b) {$sb = $j;}

			}

		}
			
			Session::flash('message', '&nbsp;Withdrawals Successfully Updated.');

			}
	}



		////////////////////////////////////////////////////
		//
		// 	If everything is OK, refresh page with new data...
		//
		////////////////////////////////////////////////////


				unset($wd);
				Session::forget('_old_input');	
				$wd = Withdrawals::display_withdrawals(Session::get('bond_id'), Auth::user()->id, $sb);
				
			$html = View::make('bond.withdrawal')->withErrors($validation)->with('bond', $wd['bond'])->with('mode', $wd['mode'])->with('withdrawal', $wd['withdrawal_details'])->with('segment_blocks', $wd['segment_blocks'])->with('segment_start', $wd['segment_start'])->with('segment_end', $wd['segment_end'])->with('total_years', $wd['total_years'])->with('final_year', $wd['final_year'])->with('start_year', $wd['start_year'])->with('policy_loan_years', $wd['policy_loan_years'])->with('allowance', $wd['allowance'])->with('cumulative_allowance', $wd['cumulative_allowance'])->with('chargeable_event', $wd['chargeable_event'])->with('excess', $wd['excess'])->with('sb', $sb)->with('pointer', $wd['pointer']); 



		return($html);
		
	
	}

}
?>
