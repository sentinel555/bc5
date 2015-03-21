<?php

namespace App\Http\Controllers;  
use App\Models\Segments as Segments;
use \Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Input;
use \Illuminate\Support\Facades\Session;
use \Illuminate\Support\Facades\Validator;
use \Illuminate\Support\Facades\View;
use \Illuminate\Support\Facades\Redirect;
use \Illuminate\Support\Facades\Auth;

class SegmentsController extends Controller {

	/**
	 * Routing Information
	 */
	public function getIndex() {

	if (Auth::check() == false) {

		return redirect('auth/login');
	}	 


		if(!Session::has('bond_id')) { return redirect('bonds'); }

		$bond = array();
		$segments = array();
		$increments = array();
		$segments_increments = array();
		$segment_offset = array();
		$ownership_segments = array();
		$policyholders = array();
		$assignment = array();

			 $bond = DB::table('bonds')->where('id', Session::get('bond_id'))->where('user_id', Auth::user()->id)->first();             
			$segments = Segments::load_segments(Session::get('bond_id'), Auth::user()->id);
			$increments = Segments::load_increments(Session::get('bond_id'), Auth::user()->id);

	if(count($increments) > 0) {


		$counter = 1;
		$segment_offset[0] = $bond->segments;

		for($a=0; $a < count($increments); $a++) {

			$segment_offset[$counter] = $segment_offset[$counter - 1] + $increments[$a]['increment_segments'];

			$counter++;

			}
		}

			$segments_increments = Segments::load_segment_increments(Session::get('bond_id'), Auth::user()->id, $increments);


	$all_segments = array_merge($segments, $segments_increments);

	Session::put('all_segments', $all_segments);

        $seg_count = count($all_segments);

             $counter = 0;

               for($a = 0; $a < $seg_count; $a++) {

                       $policyholders[$a] = Segments::policyholders($bond->id, Auth::user()->id, $all_segments[$a]['id'], ($a+1));
               }

	$ownership_segments = array_fill(0, $seg_count, '');
	     	
            for($x = 0; $x < $seg_count; $x++) {

		for($i=0; $i < count($policyholders); $i++) {

                        for($z=0; $z < count($policyholders[$i]); $z++) {


                        if($policyholders[$i][$z]['segment_number'] == $x+1) {

                                $ownership_segments[$x] .= $policyholders[$i][$z]['surname'].', '.$policyholders[$i][$z]['first_name'];

				$assignment[$x] = $policyholders[$i][$z]['assigned'];


                                if($z < (count($policyholders[$i]) - 1)) {$ownership_segments[$x] .= '&nbsp;&nbsp;&#47;&nbsp;&nbsp;';}

                                }
                        }
		}

	}

        
                return View::make('bond.segments')->with('segments', $segments)->with('increments', $increments)->with('seg_count', $seg_count)->with('segment_offset', $segment_offset)->with('ownership_segments', $ownership_segments)->with('assignment', $assignment)->with('all_segments', $all_segments);
               // return dd(Input::old());
		
	}


	public function postIndex() {


		if(!Session::has('bond_id')) { Redirect::to('bonds'); }

		Input::flashExcept('_token');
		//ref::config('expLvl', 3);
	//	~r(Input::all());

		$bond = array();
		$segments = array();
		$increments = array();
		$segments_increments = array();
		$segment_offset = array();
		$ownership_segments = array();
		$policyholders = array();
		$assignment = array();
		$rules = array();
		$messages = array();

		$sa = Input::get('segment_amount');
		$ep = Input::get('encashment_proceeds');
		$error = 0;

			for($z = 0; $z < count($sa); $z++) {

				$rules['segment_amount.'.$z] = 'required|min:1|max:50000000|numeric|regex:/^[\d]{1,8}\.{0,1}[\d]{0,2}$/'; 
				$rules['encashment_proceeds.'.$z] = 'required|min:1|max:50000000|numeric|regex:/^[\d]{1,8}\.{0,1}[\d]{0,2}$/';
			}

			for($z = 0; $z < count($sa); $z++) {

				$messages += array(

					'segment_amount.'.$z.'.required' => 'The initial investment for segment number '.($z + 1).' must be entered',
					'segment_amount.'.$z.'.min' => 'The minimum initial investment for segment number '.($z + 1).' is 0',
					'segment_amount.'.$z.'.max' => 'The maximum initial investment for segment number '.($z + 1).' is 50000000',
					'segment_amount.'.$z.'.numeric' => 'Only numeric values can be entered for the inital investment of segment number '.($z + 1),
					'segment_amount.'.$z.'.regex' => 'Values for the initial investment for segment number '.($z + 1).' must be entered as a monetary value',

					'encashment_proceeds.'.$z.'.required' => 'The current &#47; surrender value for segment number '.($z + 1).' must be entered',
					'encashment_proceeds.'.$z.'.min' => 'The minimum current &#47; surrender value for segment number '.($z + 1).' is 0',
					'encashment_proceeds.'.$z.'.max' => 'The maximum current &#47; surrender value for segment number '.($z + 1).' is 50000000',
					'encashment_proceeds.'.$z.'.numeric' => 'Only numeric values can be entered for the current &#47; surrender value of segment number '.($z + 1),
					'encashment_proceeds.'.$z.'.regex' => 'Values for the current &#47; surrender value for segment number '.($z + 1).' must be entered as a monetary value'



			);

			}


		$validation = Validator::make(Input::all(), $rules, $messages);

		if ($validation->fails())
		{

		Session::flash('message', '&nbsp;Errors have been detected. Please review the page and re-validate.');

			$error = 1;
		}

		$bond = DB::table('bonds')->where('id', Session::get('bond_id'))->where('user_id', Auth::user()->id)->first();             


		$seg_total = 0;
		$enc_total = 0;

		for($z = 0; $z < Input::get('seg_count'); $z++) {

			$seg_total += $sa[$z];
			$enc_total += $ep[$z];

		}	

		//Check for minor rounding errors when calculating total...

		$st = array(0 => $seg_total, $bond->investment);
		$et = array(0 => $enc_total, $bond->encashment_proceeds);
		sort($st, SORT_NUMERIC);
		sort($et, SORT_NUMERIC);

			$seg_diff = abs((($st[1] - $st[0]) / $bond->investment) * 100);
			$enc_diff = abs((($et[1] - $et[0]) / $bond->encashment_proceeds) * 100);


			
			if($seg_total != $bond->investment && $seg_diff > 0.001 && $error == 0) {

				Session::flash('message', '&nbsp;The combined value of the segments must be equal to the original value of the bond (&pound;'.number_format($bond->investment,2,'.',',').').');
				$error = 1;
			}	


			if($enc_total != $bond->encashment_proceeds && $enc_diff > 0.001 && $error == 0) {

				Session::flash('message', '&nbsp;The combined current value (or surrender value) of the segments must be equal to the Surrender Proceeds / Current Value of the bond as specified in the Bond Details Tab (&pound;'.number_format($bond->encashment_proceeds,2,'.',',').').');
				$error = 1;
			}


			if($error == 0) {

				Segments::update_segments(Input::all(), Session::get('all_segments'), Session::get('bond_id'), Auth::user()->id, Input::get('seg_count'));

				 Session::flash('message', '&nbsp;Information successfully updated.');
			
			}
			
			
			$segments = Segments::load_segments(Session::get('bond_id'), Auth::user()->id);
			$increments = Segments::load_increments(Session::get('bond_id'), Auth::user()->id);

	if(count($increments) > 0) {


		$counter = 1;
		$segment_offset[0] = $bond->segments;

		for($a=0; $a < count($increments); $a++) {

			$segment_offset[$counter] = $segment_offset[$counter - 1] + $increments[$a]['increment_segments'];

			$counter++;

			}
		}

			$segments_increments = Segments::load_segment_increments(Session::get('bond_id'), Auth::user()->id, $increments);

	$all_segments = array_merge($segments, $segments_increments);

	Session::put('all_segments', $all_segments);

        $seg_count = count($all_segments);

             $counter = 0;

               for($a = 0; $a < $seg_count; $a++) {

                       $policyholders[$a] = Segments::policyholders($bond->id, Auth::user()->id, $all_segments[$a]['id'], ($a+1));

               }

	$ownership_segments = array_fill(0, $seg_count, '');
	     	
            for($x = 0; $x < $seg_count; $x++) {

		for($i=0; $i < count($policyholders); $i++) {

                        for($z=0; $z < count($policyholders[$i]); $z++) {


                        if($policyholders[$i][$z]['segment_number'] == $x+1) {

                                $ownership_segments[$x] .= $policyholders[$i][$z]['surname'].', '.$policyholders[$i][$z]['first_name'];

				$assignment[$x] = $policyholders[$i][$z]['assigned'];


                                if($z < (count($policyholders[$i]) - 1)) {$ownership_segments[$x] .= '&nbsp;&nbsp;&#47;&nbsp;&nbsp;';}

                                }
                        }
		}

	}

		$html = View::make('bond.segments')->withErrors($validation)->with('segments', $segments)->with('increments', $increments)->with('seg_count', $seg_count)->with('segment_offset', $segment_offset)->with('ownership_segments', $ownership_segments)->with('assignment', $assignment)->with('all_segments', $all_segments);
		return($html);

		
		}
}
?>
