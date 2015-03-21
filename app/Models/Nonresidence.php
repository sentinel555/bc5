<?php

namespace App\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;
use \Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Session;
use DateTime;


class Nonresidence extends Eloquent {

	protected $table = 'non_residence';
	protected $fillable = array('id', 'policyholder_id', 'start_date', 'end_date', 'created_at', 'updated_at');


	public static function date_overlap($policyholder_id, $start_date, $end_date) { 

		$times = array();
		$counter = 0;

		$n = DB::table('non_residence')->orderBy('start_date', 'asc')->where('policyholder_id', '=', Session::get('policyholder_id'))->get() ;

		foreach($n as $id => $output) {

			$times[$counter]["start"] = $output->start_date;	
			$times[$counter]["end"] = $output->end_date;	
			$counter++;

		}

 		$ustart = DateTime::createFromFormat('d/m/Y', $start_date);
 		$uend = DateTime::createFromFormat('d/m/Y', $end_date);

	 
	    foreach($times as $time){ 
	        $start = new DateTime($time["start"]); 
	        $end   = new DateTime($time["end"]); 
	        if($ustart <= $end && $start <= $uend){ 
	            return true; 
	        } 
	    } 
	    return false; 
	} 

	public static function add_non_residence($policyholder_id, $start_date, $end_date) {

			$sd1 = preg_split('/[\/\.-]/', $start_date);
			$ed1 = preg_split('/[\/\.-]/', $end_date);

			$sd = $sd1[2].$sd1[1].$sd1[0];
			$ed = $ed1[2].$ed1[1].$ed1[0];

			$nr = new Nonresidence;

			$nr->policyholder_id = $policyholder_id;
			$nr->start_date = $sd;
			$nr->end_date = $ed;
			$nr->save();
			$nr->touch();




	}
}
