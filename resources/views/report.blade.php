@extends('layouts.master')

@section('header')
<h4>Investment Bond Encashment Report</h4>
<script language="JavaScript" type="text/javascript">

$().ready(function() {

 $('#toPDF').click(function(e) {

		var w = window.open('{!! url("genPdf") !!}','_blank','fullscreen=yes,resizable=yes,scrollbars=yes,toolbar=yes,location=no,directories=no,status=no,menubar=no,replace=false');
		w.focus();
        });

	
});

</script>
@stop
@section('content')
{!! Form::open(['id' => 'report']) !!}
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@for ($a = 0; $a < count($bond); $a++)
<div class="row-fluid">
    <div class="col-xs-12 bondbox"> 

<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>

<div class="row-fluid">
    <div class="col-xs-4"><strong>Bond Name:</strong></div>
    <div class="col-xs-4">{{{$bond[$a][0]->insurer}}}&nbsp;&nbsp;&nbsp;{{{$bond[$a][0]->policy_number}}}</div>
    <div class="col-xs-4">@if($bond[$a][0]->offshore_bond == 0)<span class="blue">&#91;Onshore Bond&#93;</span>@else <span class="green">&#91;Offshore Bond&#93;</span>@endif
</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
</div>
@for($z = 0; $z < count($ownerships[$a]); $z++)
<div class="row-fluid">
    <div class="col-xs-4"><strong>Policyholder:</strong></div>
    <div class="col-xs-4">{{{$ownerships[$a][$z]['surname']}}}, {{{$ownerships[$a][$z]['first_name']}}}</div>
    <div class="col-xs-4">&#40;Ownership Ratio: {{{$ownerships[$a][$z]['percentage']}}}&#37;&#41;</div>
  </div>
@endfor
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-4"><strong>Commencement Date:</strong></div>
    <div class="col-xs-4">{{{ $commencement_date[$a] }}}</div>
    <div class="col-xs-4">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-4"><strong>Encashment Date:</strong></div>
    <div class="col-xs-4">@if($bond[$a][0]->auto_update == 1) {{{date('d')}}}&#47;{{{date('m')}}}&#47;{{{date('Y')}}}@else{{{$encashment_date[$a] }}}@endif</div>
    <div class="col-xs-4">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-4"><strong>Initial Investment:</strong></div>
    <div class="col-xs-4">&pound;{{{ number_format($bond[$a][0]->investment,2,'.',',') }}}</div>
    <div class="col-xs-4">&nbsp;</div>
  </div>
@for($b =0; $b < count($increment_details[$a]); $b++) 
@if($increment_details[$a][$b]['bond_id'] == $bond[$a][0]->bond_id) 
<div class="row-fluid">
    <div class="col-xs-4 bluelinetop"><strong>Additional Increment:</strong></div>
    <div class="col-xs-4 bluelinetop">&pound;{{{number_format($increment_details[$a][$b]['increment_amount'],2,'.',',')}}}</div>
    <div class="col-xs-4 bluelinetop">Applied on: {{{$increment_details[$a][$b]['increment_commencement_date']}}}</div>
  </div>
@endif
@endfor
@for($b = 0; $b < count($policy_loans[$a]); $b++) 
@if($policy_loans[$a][$b]['bond_id'] == $bond[$a][0]->bond_id)
@if($policy_loans[$a][$b]['capital_repayment'] > 0)
<div class="row-fluid">
    <div class="col-xs-4 bluelinetop"><strong>Capital Repayment (Policy Loan):</strong></div>
    <div class="col-xs-4 bluelinetop">&pound;{{{number_format($policy_loans[$a][$b]['capital_repayment'],2,'.',',')}}}</div>
    <div class="col-xs-4 bluelinetop">&nbsp;</div>
  </div>
@endif
@endif
@endfor
@for($b = 0; $b < count($encashment_details[$a]); $b++) 
@if($encashment_details[$a][$b]['bond_id'] == $bond[$a][0]->bond_id)
<div class="row-fluid">
    <div class="col-xs-4 bluelinetop"><strong>Encashment of Segments (Initial Value):</strong></div>
    <div class="col-xs-4 bluelinetop">&pound;{{{number_format($encashment_details[$a][$b]['segments_encashed_initial_value'],2,'.',',')}}}</div>
    <div class="col-xs-4 bluelinetop">Encashment Date: {{{$encashment_details[$a][$b]['segments_encashment_date']}}}</div>
  </div>
@endif
@endfor
@if(count($bond) > 1)
<div class="row-fluid">
    <div class="col-xs-4 bluelines"><strong>Total Investment:</strong></div>
    <div class="col-xs-4 bluelines"><em>&pound;{{{number_format($total_investment[$a],2,'.',',')}}}</em></div>
    <div class="col-xs-4 bluelines">&nbsp;</div>
  </div>
@endif	
<div class="row-fluid">
    <div class="col-xs-4"><strong>Proceeds upon Encashment:</strong></div>
    <div class="col-xs-4">&pound;{{{number_format($bond[$a][0]->encashment_proceeds,2,'.',',')}}}</div>
    <div class="col-xs-4">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-2 bluelinetop">&#40;Proceeds</div>
    <div class="col-xs-1 bluelinetop">&#43;</div>
    <div class="col-xs-2 bluelinetop">Withdrawals&#41;</div>
    <div class="col-xs-1 bluelinetop">&#45;</div>
    <div class="col-xs-2 bluelinetop">&#40;Investment</div>
    <div class="col-xs-1 bluelinetop">&#43;</div>
    <div class="col-xs-2 bluelinetop">Previous Excess&#41;</div>
    <div class="col-xs-1 bluelinetop">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-2 bluelinebottom">&#40;&pound;{{{number_format($bond[$a][0]->encashment_proceeds,2,'.',',')}}}</div>
    <div class="col-xs-1 bluelinebottom">&#43;</div>
    <div class="col-xs-2 bluelinebottom">&pound;{{{number_format($total_withdrawals[$a],2,'.',',')}}}&#41;</div>
    <div class="col-xs-1 bluelinebottom">&#45;</div>
    <div class="col-xs-2 bluelinebottom">&#40;&pound;{{{number_format($total_investment[$a],2,'.',',')}}}</div>
    <div class="col-xs-1 bluelinebottom">&#43;</div>
    <div class="col-xs-2 bluelinebottom">&pound;{{{number_format($total_excess[$a],2,'.',',')}}}&#41;</div>
    <div class="col-xs-1 bluelinebottom">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-3 center">&pound;@if($results[$a]['positive'] == true) {{{number_format($results[$a]['true_gain'], 2, '.', ',')}}}@else{{{$results[$a]['loss']}}}@endif
</div>
    <div class="col-xs-9">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-3 center"><s>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</s></div>
    <div class="col-xs-1">&#61;</div>
    <div class="col-xs-8"><strong>&pound;{{{number_format($results[$a]['top_slice'], 2, '.', ',')}}}&nbsp;&nbsp;&nbsp;Top Slice</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-3 center">{{{$total_years[$a]}}} relevant years</div>
    <div class="col-xs-9">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>

@if($results[$a]['tar'] == true)
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12"><strong>An element of Time Apportionment Relief has been applied to reflect periods of non-UK residency.</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endif
 
  </div>
  </div>
 
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endfor
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>

<div class="row-fluid">
    <div class="col-xs-5"><strong>Tax Calculation &#40;{{{($tax_year - 1)}}}&#47;{{{$tax_year}}} Tax Year&#41;</strong></div>
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-3">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>

@for($a =0; $a < count($policyholder); $a++)
<div class="row-fluid">
    <div class="col-xs-12 plainbox">

<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-5"><strong>Policyholder:</strong></div>
    <div class="col-xs-4">{{{$policyholder[$a]['surname']}}}, {{{$policyholder[$a]['first_name']}}}</div>
    <div class="col-xs-3">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-5"><strong>Total Income:</strong></div>
    <div class="col-xs-4">&pound;{{{number_format($policyholder[$a]['gross_income'],2,'.',',')}}}</div>
    <div class="col-xs-3">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-5"><strong>Minus Available Allowances & Deductions:</strong></div>
    <div class="col-xs-4">&pound;{{{number_format($policyholder[$a]['allowances'],2,'.',',')}}}</div>
    <div class="col-xs-3">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-5 bluelines"><strong>Net Income:</strong></div>
    <div class="col-xs-4 bluelines"><strong>&pound;{{{number_format($policyholder[$a]['net_income'],2,'.',',')}}}</strong></div>
    <div class="col-xs-3 bluelines">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-5 bluelinebottom"><strong>Aggregate Gain:</strong></div>
    <div class="col-xs-4 bluelinebottom"><strong>&pound;{{{number_format($policyholder[$a]['aggregate_gain'],2,'.',',')}}}</strong></div>
    <div class="col-xs-3 bluelinebottom">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-5 bluelinebottom"><strong>Aggregate Top Slice:</strong></div>
    <div class="col-xs-4 bluelinebottom"><strong>&pound;{{{number_format($policyholder[$a]['aggregate_top_slice'],2,'.',',')}}}</strong></div>
    <div class="col-xs-3 bluelinebottom">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-5">&nbsp;</div>
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-3"><strong><em>Tax</em></strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-5"><strong>Top Slice absorbed by Personal Allowances:</strong></div>
    <div class="col-xs-4">&pound;{{{number_format($policyholder[$a]['taxation']['slice_untaxed'],2,'.',',')}}}</div>
    <div class="col-xs-3">&pound;0.00</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-5"><strong>Top Slice Chargeable at Basic Rate:</strong></div>
    <div class="col-xs-4">&pound;{{{number_format($policyholder[$a]['taxation']['slice_basic_rate'],2,'.',',')}}}</div>
    <div class="col-xs-3">&pound;{{{number_format($policyholder[$a]['taxation']['basic_rate_tax'],2,'.',',')}}}</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-5"><strong>Top Slice Chargeable at Higher Rate:</strong></div>
    <div class="col-xs-4">&pound;{{{number_format($policyholder[$a]['taxation']['slice_higher_rate'],2,'.',',')}}}</div>
    <div class="col-xs-3">&pound;{{{number_format($policyholder[$a]['taxation']['higher_rate_tax'],2,'.',',')}}}</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-5"><strong>Top Slice Chargeable at Additional Rate:</strong></div>
    <div class="col-xs-4">&pound;{{{number_format($policyholder[$a]['taxation']['slice_additional_rate'],2,'.',',')}}}</div>
    <div class="col-xs-3">&pound;{{{number_format($policyholder[$a]['taxation']['additional_rate_tax'],2,'.',',')}}}</div>
  </div>

<div class="row-fluid">
    <div class="col-xs-5 bluelinetop">&nbsp;</div>
    <div class="col-xs-4 bluelinetop">&nbsp;</div>
    <div class="col-xs-3 bluelinetop">&pound;{{{number_format($policyholder[$a]['taxation']['total_tax'],2,'.',',')}}}</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-5"><strong>Less Aggregate Tax Credit:</strong></div>
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-3">&#40;&pound;{{{number_format($policyholder[$a]['aggregate_slice_tax_credit'],2,'.',',')}}}&#41;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-5 bluelines">&nbsp;</div>
    <div class="col-xs-4 bluelines">&nbsp;</div>
    <div class="col-xs-3 bluelines"><strong>&pound;{{{number_format(($policyholder[$a]['taxation']['total_tax'] - $policyholder[$a]['aggregate_slice_tax_credit']),2,'.',',')}}}</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-5"><strong>Multiplied by aggregate Relevant Years:</strong></div>
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-3">{{{$policyholder[$a]['aggregate_years']}}}</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-5 bluelines"><strong>Total Tax Payable:</strong></div>
    <div class="col-xs-4 bluelines">&nbsp;</div>
    <div class="col-xs-3 bluelines"><strong>&pound;{{{number_format($policyholder[$a]['final_tax'],2,'.',',')}}}</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-5"><strong>&#40;Top Slicing Relief:&#41;</strong></div>
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-3"><strong>@if($policyholder[$a]['top_slicing_relief'] == 0)&#40;N&#47;A&#41; @else&#40;&pound;{{{$policyholder[$a]['top_slicing_relief']}}}&#41; @endif</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@if($policyholder[$a]['deficiency'] > 0 && $policyholder[$a]['corresponding_deficiency_relief']['tax'] > 0)
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12 blue">Due to the fact that the policyholder is a higher-rate taxpayer and a deficiency has occured, it may be possible to claim deficiency relief to offset against any higher-rate tax liability.</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-5">&nbsp;</div>
    <div class="col-xs-4"><strong>Relief Available:</strong></div>
    <div class="col-xs-3"><strong>Tax Saving:</strong></div>
  </div>
@if($policyholder[$a]['corresponding_deficiency_relief']['deficiency'] > 0)
<div class="row-fluid">
    <div class="col-xs-5"><strong>Higher Rate Slice reduced by Deficiency Relief:</strong></div>
    <div class="col-xs-4">&pound;{{{number_format($policyholder[$a]['corresponding_deficiency_relief']['deficiency'],2, '.', ',')}}}</div>
    <div class="col-xs-3">&pound;{{{number_format($policyholder[$a]['corresponding_deficiency_relief']['relief_slice'],2,'.',',')}}}</div>
  </div>
@endif
@if($policyholder[$a]['corresponding_deficiency_relief']['remainder'] > 0)
<div class="row-fluid">
    <div class="col-xs-5"><strong>&#40;Higher Rate Band Reduction:&#41;</strong></div>
    <div class="col-xs-4">&pound;{{{number_format($policyholder[$a]['corresponding_deficiency_relief']['remainder'], 2, '.', ',')}}}</div>
    <div class="col-xs-3">&pound;{{{number_format($policyholder[$a]['corresponding_deficiency_relief']['relief_higher_rate'],2,'.',',')}}}</div>
  </div>
@endif
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endif
</div>
</div>

@if($policyholder[$a]['age_allowance']['reduction'] > 0)
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12 redbox"><strong>
If the proposed encashments proceed, Age Allowance will be reduced by &pound;{{{$policyholder[$a]['age_allowance']['lost']}}}, resulting in a lower personal allowance of &pound;{{{$policyholder[$a]['age_allowance']['reduction']}}}. This will incur an additional tax charge of &pound;{{{$policyholder[$a]['age_allowance']['additional_tax']}}} upon the main income.</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endif
@if($policyholder[$a]['pension_premiums']['net_pension_premium'] > 0 && $policyholder[$a]['age'] < 75)
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12 bluebox"><strong>
This tax liability can be reduced to zero by making a contribution to a Personal Pension of &pound;{{{$policyholder[$a]['pension_premiums']['net_pension_premium']}}} (net of basic rate tax). The effective rate of tax relief will be {{{$policyholder[$a]['pension_premiums']['effective_tax_relief_pension']}}}&#37;.</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>

@endif
@endfor
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12 center">{!! Form::button('<span class="glyphicon glyphicon-book"></span> Create PDF Report', array('id' => 'toPDF', 'class' => 'btn btn-primary')) !!}</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div id="pdf"></div>
{!! Form::close() !!}
@stop
