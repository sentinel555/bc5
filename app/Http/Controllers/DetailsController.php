<?php

namespace App\Http\Controllers;  
use App\Models\Details as Details;
use \Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Input;
use \Illuminate\Support\Facades\Session;
use \Illuminate\Support\Facades\View;
use \Illuminate\Support\Facades\Auth;

class DetailsController extends Controller {

	/**
	 * Routing Information
	 */
	public function getIndex() {

			}

	public function getDetails($b) {
		
	if (Auth::check() == false) {

		return redirect('auth/login');
	}	 


		$bond = Details::load_bonds($b, Auth::user()->id);

		$inc = DB::table('increments')->select(DB::Raw('SUM(increment_segments) as increment_segments'))->where('bond_id', '=', $b)->where('user_id', '=', Auth::user()->id)->first();

		if($inc->increment_segments == null) { $inc->increment_segments = 0; }


		$tw = DB::table('withdrawals')->select(DB::Raw('SUM(withdrawal_amount) AS total_withdrawals'))->where('bond_id', '=', $b)->where('user_id', '=', Auth::user()->id)->first(); 
		if($tw->total_withdrawals == null) { $tw->total_withdrawals = 0; }

$html = '<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <title>Bond Details</title>  
</head>
<body>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                 <h4 class="modal-title">Bond Details</h4>
            </div>			<!-- /modal-header -->
            <div class="modal-body">
            <p><div class="left">Provider &#47; Insurer&#58;</div><div class="right">'.$bond[0]->insurer.'</div></p>
            <p><div class="left">Policy Number&#58;</div><div class="right">'.$bond[0]->policy_number.'</div></p>
	    <p><div class="left">Bond Type&#58;</div><div class="right">';
		if($bond[0]->offshore_bond == 0) { $html .= 'Onshore Bond'; } else { $html .= 'Offshore Bond'; }
$html .= '</div></p>
            <p><div class="left">Commencement Date&#58;</div><div class="right">'.date('d/m/Y', strtotime($bond[0]->commencement_date)).'</div></p>
	    <p><div class="left">Encashment Date&#58;</div><div class="right">';
if($bond[0]->auto_update == 0) { $html .= date('d/m/Y', strtotime($bond[0]->commencement_date)); } else { $html .= date('d/m/Y'); }
	$html .= '</div></p>';
	if($bond[0]->auto_update == 1) {$html .='<p><div class="left">&nbsp;</div><div class="right">&#40;Still in force&#41;</div></p>'; }
	$html .= '<p><div class="left">Original Investment&#58;</div><div class="right">&pound;'.number_format($bond[0]->investment, 2, '.', ',').'</div></p>
		<p><div class="left">Current &#47; Surrender Value&#58;</div><div class="right">&pound;'.number_format($bond[0]->encashment_proceeds, 2, '.', ',').'</div></p>
		<p><div class="left">Number of segments&#58;</div><div class="right">'.($bond[0]->segments + $inc->increment_segments).'</div></p>
		<p><div class="left">Total withdrawals to date&#58;</div><div class="right">&pound;'.number_format($tw->total_withdrawals,2,'.',',').'</div></p>
		<p>&nbsp;</p>
		</div>			<!-- /modal-body -->
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
            </div>			<!-- /modal-footer -->
</body>
</html>';
                return $html;


			}

	public function postIndex() {
	
			}
}	
?>
