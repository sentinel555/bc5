<?php
namespace App\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;
use \Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Session;
use DateTime;

class Bond extends Eloquent {

	protected $table = 'bonds';
	protected $fillable = array('id', 'insurer', 'policy_number', 'investment', 'encashment_proceeds', 'auto_update_segments', 'commencement_date', 'encashment_date', 'auto_update', 'segments', 'offshore_bond', 'mode', 'user_id', 'created_at', 'updated_at');

	public static function policyholders($bond_id, $user_id){

	$policyholders = DB::table('bonds')
		->join('ownerships', 'bonds.id', '=', 'ownerships.bond_id')
		->join('policyholders', 'policyholders.id', '=', 'ownerships.policyholder_id')
		->where('policyholders.user_id', '=', $user_id)
		->where('bonds.user_id', '=', $user_id)
		->where('bonds.id', '=', $bond_id)
		->select('policyholders.first_name AS first_name', 'policyholders.surname AS surname', 'policyholders.id AS policyholder_id', 'bonds.id AS bond_id', 'bonds.insurer AS insurer', 'bonds.policy_number AS policy_number' )
		->distinct()
		->get();

		return($policyholders);

	}


	public static function search($bond_id, $term, $user_id){


		if($term == '' || $term == null) {

		$policyholders = DB::table('bonds')
		->join('ownerships', 'bonds.id', '=', 'ownerships.bond_id')
		->join('policyholders', 'policyholders.id', '=', 'ownerships.policyholder_id')
		->where('policyholders.user_id', '=', $user_id)
		->where('policyholders.surname', 'LIKE', $term)
		->where('bonds.user_id', '=', $user_id)
		->where('bonds.id', '=', $bond_id)
		->select('policyholders.first_name AS first_name', 'policyholders.surname AS surname', 'policyholders.id AS policyholder_id', 'bonds.id AS bond_id'  )
		->get();
		}
		else
		{

		$policyholders = DB::table('bonds')
		->join('ownerships', 'bonds.id', '=', 'ownerships.bond_id')
		->join('policyholders', 'policyholders.id', '=', 'ownerships.policyholder_id')
		->where('policyholders.user_id', '=', $user_id)
		->where('bonds.user_id', '=', $user_id)
		->where('bonds.id', '=', $bond_id)
		->where('policyholders.surname', 'LIKE', $term.'%')
		->select('policyholders.first_name AS first_name', 'policyholders.surname AS surname', 'policyholders.id AS policyholder_id', 'bonds.id AS bond_id'  )
		->get();
		}


		return($policyholders);

	}

	public static function create_new_bond($input, $user_id)
    {	
    		$bond = new Bond;	    


		$cd  = preg_split('/[\/\.-]/', $input['commencement_date']);
		$ed  = preg_split('/[\/\.-]/', $input['encashment_date']);

		$commencement_date = $cd[2].$cd[1].$cd[0];
		$encashment_date = $ed[2].$ed[1].$ed[0];

		settype($commencement_date, "integer");
		settype($encashment_date, "integer");

		$bond->insurer = $input['insurer'];
		$bond->policy_number = $input['policy_number'];
		$bond->investment = round($input['investment'], 6, PHP_ROUND_HALF_EVEN);
		$bond->encashment_proceeds = round($input['encashment_proceeds'], 6, PHP_ROUND_HALF_EVEN);
		$bond->commencement_date = $commencement_date;
		$bond->encashment_date = $encashment_date;
		$bond->segments = $input['segments'];
		$bond->user_id = $user_id;
		$bond->auto_update = isset($input['auto_update']) ? 1 : 0;
		$bond->auto_update_segments = isset($input['auto_update_segments']) ? 1 : 0;
		$bond->offshore_bond = isset($input['offshore_bond']) ? 1 : 0;

		$bond->save();
		$bond->touch();
	

		$id = DB::table('bonds')->where('user_id', $user_id)->where('insurer', $input['insurer'])->where('policy_number', $input['policy_number'])->where('investment', $input['investment'])->where('segments', $input['segments'])->pluck('id');


			Session::put('bond_id', $id);
			Session::put('bond_insurer', $bond->insurer);
			Session::put('bond_policy_number', $bond->policy_number);

			Session::put('ammend_bond', 1);


		$segment_amount = round($bond->investment / $input['segments'], 6, PHP_ROUND_HALF_EVEN);
		$encashment_proceeds = round($bond->encashment_proceeds / $input['segments'], 6, PHP_ROUND_HALF_EVEN);
		$date = new \DateTime;
	
		for($z=0; $z < $input['segments']; $z++) {

			DB::table('segments')->insert(array('bond_id' => $id, 'segment_amount' => $segment_amount, 'encashment_proceeds' => $encashment_proceeds, 'user_id' => $user_id, 'created_at' => $date, 'updated_at' => $date));	

    		}


    }


public static function update_existing_bond($input, $id, $user_id)
    	{


		$bond = Bond::where('id', '=', $id)->where('user_id', '=', $user_id)->first();

		$segments = DB::table('bonds')->where('user_id', $user_id)->where('id', $id)->pluck('segments');
		$investment= DB::table('bonds')->where('user_id', $user_id)->where('id', $id)->pluck('investment');
		$encashment_proceeds = DB::table('bonds')->where('user_id', $user_id)->where('id', $id)->pluck('encashment_proceeds');



		$cd  = preg_split('/[\/\.-]/', $input['commencement_date']);
		$ed  = preg_split('/[\/\.-]/', $input['encashment_date']);

		$commencement_date = $cd[2].$cd[1].$cd[0];
		$encashment_date = $ed[2].$ed[1].$ed[0];

		settype($commencement_date, "integer");
		settype($encashment_date, "integer");

		$bond->insurer = $input['insurer'];
		$bond->policy_number = $input['policy_number'];
		$bond->investment = round( $input['investment'], 6, PHP_ROUND_HALF_EVEN);
		$bond->encashment_proceeds = round( $input['encashment_proceeds'], 6, PHP_ROUND_HALF_EVEN);
		$bond->commencement_date = $commencement_date;
		$bond->encashment_date = $encashment_date;
		$bond->segments = $input['segments'];
		$bond->user_id = $user_id;
		$bond->auto_update = isset($input['auto_update']) ? 1 : 0;
		$bond->auto_update_segments = isset($input['auto_update_segments']) ? 1 : 0;
		$bond->offshore_bond = isset($input['offshore_bond']) ? 1 : 0;

		$bond->save();
		$bond->touch();

		if($segments != $input['segments'] || $investment != $input['investment'] || $encashment_proceeds != $input['encashment_proceeds'])

		{
			DB::table('segments')->where('bond_id', '=', $bond->id)->where('user_id', '=', $user_id)->delete();

		

		$segment_amount = round($bond->investment / $input['segments'], 6, PHP_ROUND_HALF_EVEN);
		$encashment_proceeds = round($bond->encashment_proceeds / $input['segments'], 6, PHP_ROUND_HALF_EVEN);
		$date = new \DateTime;
	
		for($z=0; $z < $input['segments']; $z++) {

			DB::table('segments')->insert(array('bond_id' => $id, 'segment_amount' => $segment_amount, 'encashment_proceeds' => $encashment_proceeds, 'user_id' => $user_id, 'created_at' => $date, 'updated_at' => $date));	

    			}
		}

    }
}
