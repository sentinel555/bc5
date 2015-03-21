<?php

namespace App\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;
use \Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Session;



class Policyholder extends Eloquent {

	protected $table = 'policyholders';
	protected $fillable = array('policyholder_id', 'first_name', 'surname', 'dob', 'allowances', 'gross_income', 'user_id', 'uk_resident', 'deceased', 'deceased_on');


public static function create_new_policyholder($input, $user_id)
    {
		
    		$policyholder = new Policyholder;	    

		$deceased_on = '00000000';

		$dob1  = preg_split('/[\/\.-]/', $input['dob']);

		$dob = $dob1[2].$dob1[1].$dob1[0];

		settype($dob, "integer");

		if(isset($input['deceased']) && $input['deceased'] == 1) {

			if($input['deceased_on'] != '' || $input['deceased_on'] != '00000000') {

			$deceasedOn1  = preg_split('/[\/\.-]/', $input['deceased_on']);

			$deceased_on = $deceasedOn1[2].$deceasedOn1[1].$deceasedOn1[0];

			}

		}

		//settype($deceased_on, "integer");

		$policyholder->title = $input['title'];
		$policyholder->first_name = $input['first_name'];
		$policyholder->surname = $input['surname'];
		$policyholder->dob = $dob;
		$policyholder->gross_income = $input['gross_income'];
		$policyholder->allowances = $input['allowances'];
		$policyholder->user_id = $user_id;
		$policyholder->deceased = isset($input['deceased']) ? 1 : 0;
		$policyholder->deceased_on = $deceased_on;
		$policyholder->uk_resident = isset($input['uk_resident']) ? 1 : 0;

		$policyholder->save();
		$policyholder->touch();
	

		$id = DB::table('policyholders')->where('user_id', $user_id)->where('first_name', $input['first_name'])->where('surname', $input['surname'])->where('dob', $dob)->where('gross_income', $input['gross_income'])->pluck('id');

			Session::put('policyholder_id', $id);
			Session::put('ammend_policyholder', 1);


    }


public static function update_existing_policyholder($input, $id, $user_id)
    	{
		$policyholder = Policyholder::where('id', '=', $id)->where('user_id', '=', $user_id)->first();

		$deceased_on = '00000000';

		$dob1  = preg_split('/[\/\.-]/', $input['dob']);

		$dob = $dob1[2].$dob1[1].$dob1[0];

		settype($dob, "integer");

		if(isset($input['deceased']) && $input['deceased'] == 1) {


			$deceasedOn1  = preg_split('/[\/\.-]/', $input['deceased_on']);

			$deceased_on = $deceasedOn1[2].$deceasedOn1[1].$deceasedOn1[0];

		}

		//settype($deceased_on, "integer");

		$policyholder->title = $input['title'];
		$policyholder->first_name = $input['first_name'];
		$policyholder->surname = $input['surname'];
		$policyholder->dob = $dob;
		$policyholder->gross_income = $input['gross_income'];
		$policyholder->allowances = $input['allowances'];
		$policyholder->deceased = isset($input['deceased']) ? 1 : 0;
		$policyholder->deceased_on = $deceased_on;
		$policyholder->uk_resident = isset($input['uk_resident']) ? 1 : 0;

		$policyholder->save();
		$policyholder->touch();

    }


}
