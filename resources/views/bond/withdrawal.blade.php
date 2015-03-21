<script language="JavaScript" type="text/javascript">
$().ready(function() {

$('#mode').change(function(e) {

	bootbox.confirm("<p>If you change the mode, it will completely erase the existing withdrawal history. Are you sure that you want to do this ?</p>", function(result) {
	if(result == true) {

	e.preventDefault();
	$('input[name="cmode"]').val($('#mode option:selected').index());
        $('input[name="control_withdrawal"]').val("1");
 	var formData = $('#withdrawal :hidden').serialize();
                $.post('{!! URL::Route('update_withdrawal') !!}', formData,
                 function(data){
                         $("#containerWithdrawal").html(data);
                 });
		}
	});

});


$('#populateWithdrawal').click(function(e) {

	
	e.preventDefault();
	$('input[name="control_withdrawal"]').val("2");
	var formData = $('#withdrawal :hidden, #withdrawal :input').serialize();
	$.post('{!! URL::Route('update_withdrawal') !!}', formData,
	 function(data){
		 $("#containerWithdrawal").html(data);
	 });	
});

$('#validateWithdrawal').click(function(e) {

	e.preventDefault();
	$('input[name="control_withdrawal"]').val("3");
	var formData = $('#withdrawal :hidden, #withdrawal :input').serialize();
	$.post('{!! URL::Route('update_withdrawal') !!}', formData,
	 function(data){
		 $("#containerWithdrawal").html(data);
	 });	
	
});

@if($mode == 1)
@if(!isset($segment_blocks[0]['start']))
$('#segment_start').val('{{{ $segment_blocks[$sb]['segment_start'] }}}');
$('#segment_end').val('{{{ $segment_blocks[$sb]['segment_end'] }}}');
@endif
@endif


document.body.scrollTop = document.documentElement.scrollTop = 0;
});

function delete_segments(segment_start, segment_end, segment_block) {

bootbox.confirm("<p>Are you sure that you want to delete the following segments:</p><p>&nbsp;</p><p>"+segment_start+" - "+segment_end+" ?</p>", function(result) {
	if(result == true) {

$('input[name="ss"]').val(segment_start);
$('input[name="se"]').val(segment_end);
$('input[name="segment_block"]').val(segment_block);
$('input[name="delete"]').val("1");
$('input[name="control_withdrawal"]').val("4");
var formData = $('#withdrawal :hidden, #withdrawal :input').serialize();
$.post('{!! URL::Route('update_withdrawal') !!}', formData,
	 function(data){
		 $("#containerWithdrawal").html(data);
	 });
	}
	});
}


function go_to_segments(segment_start, segment_end, segment_block) {


$('input[name="ss"]').val(segment_start);
$('input[name="se"]').val(segment_end);
$('input[name="segment_block"]').val(segment_block);
$('input[name="control_withdrawal"]').val("5");
var formData = $('#withdrawal :hidden, #withdrawal :input').serialize();
$.post('{{ URL::Route('update_withdrawal') }}', formData,
	 function(data){
		 $("#containerWithdrawal").html(data);
	 });
}

function new_segment_block() {

	var x = 0;

	 @if($mode == 1) 	
		var ss = $("#segment_start").val();
		var se = $("#segment_end").val();


		if(parseInt(ss) > parseInt(se)) { 

	bootbox.alert("<p>The start segment is greater than the end segment. Please choose segment ranges in ascending order</p>"); x = 1;
}
	@endif
	if(x == 0) {
$('input[name="ss"]').val($("#segment_start").val());
$('input[name="se"]').val($("#segment_end").val());
$('input[name="control_withdrawal"]').val("6");
var formData = $('#withdrawal :hidden, #withdrawal :input').serialize();
$.post('{{ URL::Route('update_withdrawal') }}', formData,
	 function(data){
		 $("#containerWithdrawal").html(data);
	 });
	}
}


$('#validateWithdrawal').click(function(e) {

	e.preventDefault();
	$('input[name="control_withdrawal"]').val("3");
	var formData = $('#withdrawal :hidden, #withdrawal :input').serialize();
	$.post('{{ URL::Route('update_withdrawal') }}', formData,
	 function(data){
		 $("#containerWithdrawal").html(data);
	 });	
	
});



</script>
<div id="containerWithdrawal">	
@if (Session::has('message'))<div class="row-fluid"><div class="col-xs-12 error">
<strong>{{ Session::get('message') }}</strong></div></div>
@endif 
<?php if(Session::has('message')) { Session::forget('message'); }?>
@if (Session::has('seg_message'))
<div class="row-fluid">
    <div class="col-xs-12 error"><strong>{{ Session::get('seg_message') }}</strong></div>
  </div>
@endif
<?php if(Session::has('seg_message')) { Session::forget('seg_message'); }?>
{!! Form::open(array('id' => 'withdrawal', 'name' => 'withdrawal')) !!}
{!! Form::hidden('control_withdrawal', '', array('id' => 'control_withdrawal')) !!}
{!! Form::hidden('cmode', '', array('id' => 'cmode')) !!}
{!! Form::hidden('delete', '', array('id' => 'delete')) !!}
{!! Form::hidden('segment_block', $sb, array('id' => 'segment_block')) !!}
{!! Form::hidden('ss', (isset($segment_blocks[$sb]['segment_start']) ? $segment_blocks[$sb]['segment_start'] : 0), array('id' => 'ss')) !!}
{!! Form::hidden('se', (isset($segment_blocks[$sb]['segment_end']) ?  $segment_blocks[$sb]['segment_end'] : 0), array('id' => 'se')) !!}
@if ($mode == 1 && count($segment_blocks) > 0) 
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12"><strong>WITHDRAWALS MADE FROM GROUPED SEGMENTS:</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-3 greyline"><strong>Segment Start</strong></div>
    <div class="col-xs-3 greyline"><strong>Segment End</strong></div>
    <div class="col-xs-3 greyline"><strong>Edit</strong></div>
    <div class="col-xs-3 greyline"><strong>Delete</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@if(!isset($segment_blocks[0]['start']))
@foreach($segment_blocks as $id => $output) 
<div class="row-fluid">
    <div class="col-xs-3 @if($id == $sb) yellow @endif">{{{ $output['segment_start'] }}}</div>
    <div class="col-xs-3 @if($id == $sb) yellow @endif">{{{ $output['segment_end'] }}}</div>
    <div class="col-xs-3"><button type='button' class='btn btn-default' alt='Go To Segment'  onClick='go_to_segments("{{{ $output['segment_start'] }}}", "{{{ $output['segment_end'] }}}", "{{{ $id }}}")'><span class='glyphicon glyphicon-edit'></span></button></div>
    <div class="col-xs-3"><button type='button' class='btn btn-default' alt='Delete Segment'  onClick='delete_segments("{{{ $output['segment_start'] }}}", "{{{ $output['segment_end'] }}}", "{{{ $id }}}")'><span class='glyphicon glyphicon-trash'></span></button></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endforeach
@endif
<div class="row-fluid">
    <div class="col-xs-12 greyline">&nbsp;</div>
  </div>
@endif
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@if($mode == 1)
<div class="row-fluid">
    <div class="col-xs-3"><strong>Segment Start</strong></div>
    <div class="col-xs-3"><strong>Segment End</strong></div>
    <div class="col-xs-3">&nbsp;</div>
    <div class="col-xs-3">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-3">{!! Form::selectRange('segment_start', $segment_start, $segment_end, $segment_blocks[$sb]['segment_start'], array('id' => 'segment_start', 'class' => 'form-control')) !!}</div>
    <div class="col-xs-3">{!! Form::selectRange('segment_end', $segment_start, $segment_end, $segment_blocks[$sb]['segment_end'], array('id' => 'segment_end', 'class' => 'form-control')) !!}</div>
    <div class="col-xs-3"><button type='button' class='btn btn-default' alt='New Segment Block'  onClick='new_segment_block()'>Create New Segment Block</button></div>
    <div class="col-xs-3"></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endif
@if ($errors->has('withdrawal_fixed')) 
<div class="row-fluid">
    <div class="col-xs-12">{!! $errors->first('withdrawal_fixed', '<span class="error">:message</span>') !!}</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endif
@if ($errors->has('withdrawal_percentage')) 
<div class="row-fluid">
    <div class="col-xs-12">{!! $errors->first('withdrawal_percentage', '<span class="error">:message</span>') !!}</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endif
@if($mode == 1)
<div class="row-fluid">
    <div class="col-xs-12 greyline">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endif
<div class="row-fluid">   
    <div class="col-xs-4 center">If the withdrawals are the same amount each year, please enter the amount and click the 'populate' button below</div>
    <div class="col-xs-1 center"><strong>OR</strong></div>
    <div class="col-xs-4 center">Enter the figure as a percentage (%) of the @if($mode == 0) original investment @else selected segments @endif</div>
    <div class="col-xs-3 center">Select Mode &#40;&#42;&#41;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">   
    <div class="col-xs-4 center">{!! Form::text('withdrawal_fixed', '', array('size' => 10, 'class' => 'form-control')) !!}</div>
    <div class="col-xs-1 center"></div>
    <div class="col-xs-4 center">{!! Form::text('withdrawal_percentage', '', array('size' => 10, 'class' => 'form-control')) !!}</div>
    <div class="col-xs-3 center">{!! Form::select('mode', array('0' => 'Normal', '1' => 'Extended'), $mode, array('id' => 'mode', 'class' => 'form-control')) !!}</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>

<div class="row-fluid">   
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-2 center">{!! Form::button('Populate', array('id' => 'populateWithdrawal', 'class' => 'btn btn-primary')) !!}</div>
    <div class="col-xs-6">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<table class="table table-hover borderless">
<tbody>
        <tr align="center" valign="middle">
          <td><strong>Policy Year Ending</strong></td>
          <td><strong>Withdrawals&nbsp;<a href="#myModal" role="button" data-toggle="modal" data-target="#myModal"><small><span id="show_wdls" class="glyphicon glyphicon glyphicon-info-sign" data-toggle="modal"></span></small></a></strong></td>
          <td><strong>Policy Loan</strong></td>
          <td><strong>5% Allowance</strong></td>
          <td><strong>5% Cumulative Allowance</strong></td>
          <td><strong>Chargeable Event</strong></td>
          <td><strong>Excess</strong></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
	@for($i = $pointer; $i < $total_years; $i++)
		@if ($i % 2 == 0) <tr class="b1">
		@else <tr class="b2">
		@endif
		@if ($final_year == 2 && $i == $total_years - 2) <tr class="finalyear">
		@elseif ($final_year == 2 && $i == $total_years - 1) <tr class="finalyear">
		@elseif ($final_year == 1 && $i == $total_years - 1) <tr class="finalyear">
		@endif
		<td align="center"> {{{ $start_year + $i }}}</td>
          <td align="center">{!! Form::text('withdrawal['.$i.']', (isset($withdrawal[$i]['withdrawal_amount']) ? $withdrawal[$i]['withdrawal_amount'] :  0), array('size' => 7, 'class' => 'form-control')) !!}
	            </td>
	    <td align="center">
		@if ($policy_loan_years[$i] == '0') N/A
		@else &pound;{{{ number_format((float)($policy_loan_years[$i]), 2,'.',',') }}}
		@endif
	  </td>
          <td align="center">&pound;{{{ number_format($allowance[$i], 2,'.',',') }}}
	  </td>
          <td align="center">&pound;{{{ number_format($cumulative_allowance[$i], 2,'.',',') }}}</td>
          <td align="center"> {{{ $chargeable_event[$i] }}}</td>
          <td align="center">&pound; {{{ number_format($excess[$i], 2,'.',',') }}}</td>
        </tr> 
@if($errors->has('withdrawal.'.$i)) 
{!! $errors->first('withdrawal.'.$i, '<tr><td colspan="7"><span class="error">:message</span></td><td>&nbsp;</td></tr>') !!}
@endif
@endfor	
</tbody>
</table>
<div class="row-fluid">
    <div class="col-xs-5">&nbsp;</div>
    <div class="col-xs-2">{!! Form::button('<span class="glyphicon glyphicon-ok"></span> Validate', array('id' => 'validateWithdrawal', 'class' => 'btn btn-primary')) !!}</div>
    <div class="col-xs-5">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12 blue"><strong>&#40;&#42;&#41; Use Extended Mode if you need to enter different amounts of withdrawals for individual segments or groups of segments. Otherwise, use standard mode to equally apply withdrawals accross all segments.</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12 blue">If you have encashed individual policy segments during the lifetime of this bond, enter them in the Encashments tab, re-run the calculation, then return to this page.</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
	 <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                 <h4 class="modal-title">Withdrawal Details</h4>
            </div>			<!-- /modal-header -->
            <div class="modal-body">
	<p>
@if (isset($withdrawal[$pointer]['withdrawal_percentage']) && $withdrawal[$pointer]['withdrawal_percentage'] > 0) The annual withdrawals represent {{{ $withdrawal[$pointer]['withdrawal_percentage'] }}}&#37; of the initial investment each year. @else The annual withdrawals are user-defined, and do not represent a fixed percentage of the initial investment.  @endif

	</p>
	</div>           
 <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
            </div>			<!-- /modal-footer -->

	 </div> <!-- /.modal-content -->
    </div> <!-- /.modal-dialog -->
</div> <!-- /.modal -->
{!! Form::close() !!}
</div>
