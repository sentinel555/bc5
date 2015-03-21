<?php

namespace App\Http\Controllers;  
use App\Models\Report as Report;
use mPDF;
use \Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Input;
use \Illuminate\Support\Facades\Session;
use \Illuminate\Support\Facades\View;
use \Illuminate\Support\Facades\Redirect;
use \Illuminate\Support\Facades\Auth;

class ReportController extends Controller {

    /**
     * Routing Information
     */
	public function getIndex() {
//ref::config('expLvl', 3);

	if (Auth::check() == false) {

		return redirect('auth/login');
	}	 


		$rp = Report::generate_report();

		$html = View::make('report')->with('bond', $rp['bond'])->with('policyholder', $rp['policyholder'])->with('ownerships', $rp['ownerships'])->with('results', $rp['results'])->with('tax_year', $rp['tax_year'])->with('segment_blocks', $rp['segment_blocks'])->with('commencement_date', $rp['commencement_date'])->with('encashment_date', $rp['encashment_date'])->with('increment_details', $rp['increment_details'])->with('encashment_details', $rp['encashment_details'])->with('policy_loans', $rp['policy_loans'])->with('total_investment', $rp['total_investment'])->with('cumulative_allowance', $rp['cumulative_allowance'])->with('excess', $rp['excess'])->with('total_excess', $rp['total_excess'])->with('total_withdrawals', $rp['total_withdrawals'])->with('total_years', $rp['total_years']);

                return ($html);
	}

	public function genPdf() {
		//require base_path('vendor/mpdf/mpdf/mpdf.php');
		//include ('/var/www/bc5/vendor/mpdf/mpdf/mpdf.php');
		$rp = Report::generate_report();

		$html = View::make('pdfreport')->with('bond', $rp['bond'])->with('policyholder', $rp['policyholder'])->with('ownerships', $rp['ownerships'])->with('results', $rp['results'])->with('tax_year', $rp['tax_year'])->with('segment_blocks', $rp['segment_blocks'])->with('commencement_date', $rp['commencement_date'])->with('encashment_date', $rp['encashment_date'])->with('increment_details', $rp['increment_details'])->with('encashment_details', $rp['encashment_details'])->with('policy_loans', $rp['policy_loans'])->with('total_investment', $rp['total_investment'])->with('cumulative_allowance', $rp['cumulative_allowance'])->with('excess', $rp['excess'])->with('total_excess', $rp['total_excess'])->with('total_withdrawals', $rp['total_withdrawals'])->with('total_years', $rp['total_years']);

		$mpdf = new mPDF('','A4');

	 	$pp = public_path();

 		$stylesheet = file_get_contents($pp.'/css/kv-mpdf-bootstrap.min.css');
		$stylesheet .= file_get_contents($pp.'/css/pdf.css');

      		$mpdf->WriteHTML($stylesheet,1);
		$mpdf->WriteHTML(utf8_encode($html),2);

		$rand = Report::generate_string(6,2);

		$filename = date("Y").date("m").date("d").'-'.$rand.'.pdf';

		return $mpdf->Output($filename, 'I');


	}


	
}
?>
