<?php

namespace App\Http\Controllers;  
use App\Models\Nonresidence as Nonresidence;
use \Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Input;
use \Illuminate\Support\Facades\Session;
use \Illuminate\Support\Facades\Validator;
use \Illuminate\Support\Facades\View;
use \Illuminate\Support\Facades\Redirect;
use \Illuminate\Support\Facades\Auth;


class NonresidenceController extends Controller {

	/**
	 * Routing Information
	 */
	public function getIndex() {

	if (Auth::check() == false) {

		return redirect('auth/login');
	}	 


		$non_residence = array();
		$counter = 0;

	
		$n = DB::table('non_residence')->orderBy('start_date', 'asc')->where('policyholder_id', '=', Session::get('policyholder_id'))->get() ;

		foreach($n as $id => $output) {

			$non_residence[$counter]['id'] = $output->id;	
			$non_residence[$counter]['policyholder_id'] = $output->policyholder_id;	
			$non_residence[$counter]['start_date'] = date('d/m/Y', strtotime($output->start_date));	
			$non_residence[$counter]['end_date'] = date('d/m/Y', strtotime($output->end_date));	
			$counter++;

		}

                
                return View::make('nonresidence')->with('non_residence', $non_residence);

        
                return dd(Input::old());
		
	}


	public function postIndex() {

		Input::flashExcept('_token');
		$process = Input::get('n_process');
		//$policyholder_id = Input::get('policyholder_id');
		$id = Input::get('n_id');
				
		$deleted = 0;
		$error = 0;

		$non_residence = array();
		$counter = 0;
		$validation = array();

		if($process == "delete") {

			DB::table('non_residence')->where('policyholder_id', '=', Session::get('policyholder_id'))->where('id', '=', $id)->delete();

		Session::flash('message', 'Period of Non-Residence successfully deleted.');

		$deleted = 1;

		}

	
	if($deleted == 0) {

	$rules = array(
                'start_date' => 'required|date_format:"d/m/Y"|before:"now"',
                'end_date' => 'required|date_format:"d/m/Y"|before:"now +1 day"'
                );

		$validation = Validator::make(Input::all(), $rules);

		if ($validation->fails())
                {

                Session::flash('message', 'Errors have been detected. Please review the page and re-validate.');
			$error = 1;


		}
		

		if($process == "add" && $error == 0) {

			$sd = preg_split('/[\/\.-]/', Input::get('start_date'));
               		 $ed = preg_split('/[\/\.-]/', Input::get('end_date'));

               		 $s1 = strtotime($sd[2].$sd[1].$sd[0]); 
                	$e1 = strtotime($ed[2].$ed[1].$ed[0]);


			if ($e1 <= $s1) {

                        	Session::flash('message', 'The start date of the period of non-residence must be before the end date.');

                        	$error = 1;

                	}


			$overlap = Nonresidence::date_overlap(Session::get('policyholder_id'), Input::get('start_date'), Input::get('end_date'));

			if($overlap == true) {

				Session::flash('message', 'The period of non-residence that has been entered overlaps an existing period of non-residence. Please check the dates and resubmit.');

                        	$error = 1;



			}


			if( $error == 0) {	

				Nonresidence::add_non_residence(Session::get('policyholder_id'),  Input::get('start_date'), Input::get('end_date'));

			Session::flash('message', 'Period of Non-Residence successfully added.');

		 	}


		}	


	}

	
		$n = DB::table('non_residence')->orderBy('start_date', 'asc')->where('policyholder_id', '=', Session::get('policyholder_id'))->get();

		foreach($n as $id => $output) {

			$non_residence[$counter]['id'] = $output->id;	
			$non_residence[$counter]['policyholder_id'] = $output->policyholder_id;	
			$non_residence[$counter]['start_date'] = date('d/m/Y', strtotime($output->start_date));	
			$non_residence[$counter]['end_date'] = date('d/m/Y', strtotime($output->end_date));	
			$counter++;

		}

                
                $html = View::make('nonresidence')->with('non_residence', $non_residence)->withErrors($validation);


	
		return($html);
		

	}



}
?>
