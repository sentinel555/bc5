<?php

namespace App\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;
use \Illuminate\Support\Facades\DB;

class Policyloans extends Eloquent {

	protected $table = 'policy_loans';
	protected $fillable = array('id', 'bond_id', 'policy_loan', 'policy_loan_date', 'capital_repayment', 'user_id', 'created_at', 'updated_at');


	    public static function create_policy_loan($input, $bond_id, $user_id) {


		$policyloan = new Policyloans;
		$policy_loan_date = null;

		if($input['policy_loan_date'] != "") {

		$ad = preg_split('/[\/\.-]/', $input['policy_loan_date']);

                $policy_loan_date = $ad[2].$ad[1].$ad[0];

	    } else {$policy_loan_date= "00000000";}

		$policyloan->bond_id = $bond_id;
		$policyloan->policy_loan = round($input['policy_loan'], 6, PHP_ROUND_HALF_EVEN);
		$policyloan->policy_loan_date = $policy_loan_date;
		$policyloan->capital_repayment = round($input['capital_repayment'], 6, PHP_ROUND_HALF_EVEN);
	        $policyloan->user_id = $user_id;

		$policyloan->save();
		$policyloan->touch();

	return TRUE;
    }


public static function load_policyloans($bond_id, $user_id) { 

	$policyloan = array();
	$counter = 0;

	$pl = DB::table('policy_loans')
		->where('policy_loans.user_id', '=', $user_id)
		->where('policy_loans.bond_id', '=', $bond_id)
		->select('policy_loans.id AS id', 'policy_loans.bond_id AS bond_id', 'policy_loans.policy_loan AS policy_loan', 'policy_loans.policy_loan_date AS policy_loan_date', 'policy_loans.capital_repayment AS capital_repayment')
		->orderBy('policy_loans.policy_loan_date', 'asc')
		->distinct()
		->get();

	foreach ($pl as $id => $output) {
		
		$policyloan[$counter]['id'] = $output->id;
		$policyloan[$counter]['bond_id'] = $output->bond_id;
		$policyloan[$counter]['policy_loan'] = $output->policy_loan;
		$policyloan[$counter]['policy_loan_date'] = $output->policy_loan_date;
		$policyloan[$counter]['capital_repayment'] = $output->capital_repayment;

		$counter++;
		}
            
    return $policyloan;

    }


 
}
