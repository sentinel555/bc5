<script>
$().ready(function() {
$("#policy_loan_date").mask("99/99/9999");
$("#policy_loan_date").datepicker({format: 'dd/mm/yyyy', autoclose: true, keyboardNavigation : true });

$('#validatePolicyLoan').click(function(e) 
   {
		e.preventDefault();
        	$('input[name="control_policyloan"]').val('2');
                var formData = $('#policyloan :hidden, #policyloan :input, #policyloan :checkbox').serialize();
                $.post('{!! URL::Route('update_policyloan') !!}}', formData,
                 function(data){
                         $("#containerPolicyLoan").html(data);
                 });
        });

document.body.scrollTop = document.documentElement.scrollTop = 0;
});

function delete_plan(bondId, policyLoanId, policyLoanDate, policyLoan) {

bootbox.confirm("<p>Are you sure that you want to delete the following Policy Loan:</p><p>&nbsp;</p><p>Start Date: "+policyLoanDate+"</p><p>Amount: £"+policyLoan+" ?</p>", function(result) {
	if(result == true) {


        $('input[name="bond_id"]').val(bondId);
        $('input[name="policyloan_id"]').val(policyLoanId);
        $('input[name="control_policyloan"]').val('1');
	var formData = $('#policyloan :hidden').serialize();
                $.post('{!! URL::Route('update_policyloan') !!}', formData,
                 function(data){
                         $("#containerPolicyLoan").html(data);
                 });
        }
	});
}
</script>
<div id="containerPolicyLoan">
@if (Session::has('message'))<div class="row-fluid"><div class="col-xs-12 error">
<strong>{{ Session::get('message') }}</strong></div></div>
@endif 
<?php if(Session::has('message')) { Session::forget('message'); }?>
@if (count($policyloans) > 0)
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12"><strong>POLICY LOANS SECURED UPON THE BOND:</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-3 greyline"><strong>Date of Policy Loan</strong></div>
    <div class="col-xs-3 greyline"><strong>Loan Amount</strong></div>
    <div class="col-xs-3 greyline"><strong>Capital Repaid to Date</strong></div>
    <div class="col-xs-3 greyline"><strong>Delete</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
  @for($z = 0; $z < count($policyloans);  $z++)
<div class="row-fluid">
    <div class="col-xs-3">{{{ date('d/m/Y', strtotime($policyloans[$z]['policy_loan_date'])) }}}</div>
    <div class="col-xs-3">&pound;{{{ number_format($policyloans[$z]['policy_loan'],2,'.',',') }}}</div>
    <div class="col-xs-3">&pound;{{{ number_format($policyloans[$z]['capital_repayment'],2,'.',',') }}}</div>
    <div class="col-xs-3"><button type='button' class='btn btn-default' alt='Delete Ownership' onClick='delete_plan("{{{ $policyloans[$z]['bond_id'] }}}", "{{{ $policyloans[$z]['id'] }}}", "{{{ date('d/m/Y', strtotime($policyloans[$z]['policy_loan_date'])) }}}", "{{{ number_format($policyloans[$z]['policy_loan'],2,'.',',') }}}")'><span class='glyphicon glyphicon-trash'></span> Delete</button>
</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
 @endfor
<div class="row-fluid">
    <div class="col-xs-12 greyline">&nbsp;</div>
  </div>
@endif
{!! Form::open(array('id' => 'policyloan', 'name' => 'policyloan')) !!}
{!! Form::hidden('control_policyloan', '', array('id' => 'control_policyloan')) !!}
{!! Form::hidden('bond_id', '', array('id' => 'bond_id')) !!}
{!! Form::hidden('policyloan_id', '', array('id' => 'policyloan_id')) !!}
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12"><strong>ADD POLICY LOAN</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">   
    <div class="col-xs-4">Policy Loan Amount &#40;&pound;&#41;:</div>
    <div class="col-xs-6">{!! Form::text('policy_loan', (Input::old('policy_loan') ? Input::old('policy_loan') : ''), array('class' => 'form-control')) !!}</div>
    <div class="col-xs-2">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@if($errors->has('policy_loan'))
<div class="row-fluid">
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-6">{!! $errors->first('policy_loan', '<span class="error">:message</span>') !!}</div>
 <div class="col-xs-2">&nbsp;</div>
</div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endif
<div class="row-fluid">   
    <div class="col-xs-4">Date of Policy Loan (*)</div>
    <div class="col-xs-6"> {!! Form::text('policy_loan_date', Input::old('policy_loan_date') ? Input::old('policy_loan_date') : '', array('id' => 'policy_loan_date', 'class' => 'form-control', 'size' => '16')) !!}
    <br>(dd/mm/yyyy format)
    </div>
    <div class="col-xs-2">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@if($errors->has('policy_loan_date'))
<div class="row-fluid">
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-6">{!! $errors->first('policy_loan_date', '<span class="error">:message</span>') !!}</div>
 <div class="col-xs-2">&nbsp;</div>
</div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endif
<div class="row-fluid">   
    <div class="col-xs-4">Capital Repaid to Date &#40;&pound;&#41;:</div>
    <div class="col-xs-6">{!! Form::text('capital_repayment', (Input::old('capital_repayment') ? Input::old('capital_repayment') : 0), array('class' => 'form-control')) !!}</div>
    <div class="col-xs-2">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@if($errors->has('capital_repayment'))
<div class="row-fluid">
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-6">{!! $errors->first('capital_repayment', '<span class="error">:message</span>') !!}</div>
 <div class="col-xs-2">&nbsp;</div>
</div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endif
<div class="row-fluid">   
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-6">{!! Form::button('<span class="glyphicon glyphicon-ok"></span> Validate', array('id' => 'validatePolicyLoan', 'class' => 'btn btn-primary')) !!}</div>
    <div class="col-xs-2">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12 blue">You should add any capital repaid on the original loan, but ignore any interest payments.</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12 blue">Any loan interest that has been capitalized will be treated as a further part surrender. Therefore, you will need to add this as additional policy loan(s) for the relevant year(s).</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12 blue">&nbsp;</div>
 <div class="row-fluid">
    <div class="col-xs-12 blue">All entries marked with an asterisk (*) must 
      be completed.</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
{!! Form::close() !!}
</div>
