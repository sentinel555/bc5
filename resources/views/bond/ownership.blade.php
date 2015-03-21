<script language="JavaScript" type="text/javascript">
$().ready(function() {
$("#assignment_date").mask("99/99/9999");
$("#security_debt_date").mask("99/99/9999");
$("#assignment_date").datepicker({format: 'dd/mm/yyyy', autoclose: true, keyboardNavigation : true });
$("#security_debt_date").datepicker({format: 'dd/mm/yyyy', autoclose: true, keyboardNavigation : true });

$('#validateOwnership').click(function(e) 
   {
	   	e.preventDefault();
        	$('input[name="bond_id"]').val({{ Session::get('bond_id') }});
        	$('input[name="control_ownership"]').val("2");
		var formData = $('#ownership :hidden, #ownership :input, #ownership :checkbox').serialize();
		$.post('{!! URL::Route('update_ownership') !!}', formData,
                 function(data){
                         $("#containerOwnership").html(data);
		 });
	});

document.body.scrollTop = document.documentElement.scrollTop = 0;
});

function delete_split(bondId, policyholderId, ownershipId, policyholder, segments, percentageSplit) {

bootbox.confirm("<p>Are you sure that you want to delete the following split:</p><p>&nbsp;</p><p>Policyholder: "+policyholder+"</p><p>Segments: "+segments+"</p><p>Split: "+percentageSplit+"% ?</p>", function(result) {
	if(result == true) {


        $('input[name="bond_id"]').val(bondId);
        $('input[name="policyholder_id"]').val(policyholderId);
        $('input[name="ownership_id"]').val(ownershipId);
        $('input[name="control_ownership"]').val("1");
	var formData = $('#ownership  :hidden').serialize();
		$.post('{!! URL::Route('update_ownership') !!}', formData,
                 function(data){
                         $("#containerOwnership").html(data);
		 });

        	}
		});
        }

</script>
<div id="containerOwnership">
@if (Session::has('message'))<div class="row-fluid"><div class="col-xs-12 error">
<strong>{{ Session::get('message') }}</strong></div></div>
@endif 
<?php if(Session::has('message')) { Session::forget('message'); }?>
@if (count($ownership) > 0)
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12"><strong>CURRENT OWNERSHIP SPLIT (BY SEGMENTS AND PERCENTAGE):</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-2 greyline"><strong>Policyholder</strong></div>
    <div class="col-xs-2 greyline"><strong>Segments</strong></div>
    <div class="col-xs-2 greyline"><strong>Percentage</strong></div>
    <div class="col-xs-2 greyline"><strong>Trustee ?</strong></div>
    <div class="col-xs-2 greyline"><strong>Delete</strong></div>
    <div class="col-xs-2 greyline">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>

 @foreach($ownership as $id => $output) 
<div class="row-fluid">
    <div class="col-xs-2">{{{$output->surname}}}, {{{$output->first_name}}}</div>
    <div class="col-xs-2">
 @if($output->segment_start == $output->segment_end) {{{$output->segment_start}}} @else
	{{{$output->segment_start}}}&nbsp;&#45;&nbsp;{{{$output->segment_end}}} @endif
</div>
    <div class="col-xs-2">{{{$output->percentage_split}}}&#037;</div>
    <div class="col-xs-2">{{{($output->trustee_investment == 0 ? 'No ' : 'Yes')}}}</div>
    <div class="col-xs-2"><button type='button' class='btn btn-default' alt='Delete Ownership'  onClick='delete_split("{{{$output->bond_id}}}", "{{{$output->policyholder_id}}}", "{{{$output->ownership_id}}}", "{{{$output->surname}}}, {{{$output->first_name}}}", "{{{$output->segment_start}}} - {{{$output->segment_end}}}", "{{{$output->percentage_split}}}")'><span class='glyphicon glyphicon-trash'></span> Delete</button>
</div>
    <div class="col-xs-2"></div>
  </div>
@if($output->assignment_date != "0000-00-00")
<div class="row-fluid">
    <div class="col-xs-12">The above segments were assigned to policyholder on {{{date('d/m/Y', strtotime($output->assignment_date))}}}</div>
  </div>
@endif
@if($output->security_debt_date != "0000-00-00")
<div class="row-fluid">
    <div class="col-xs-12">The above segments became used as a security for a debt on {{{date('d/m/Y', strtotime($output->security_debt_date))}}}</div>
  </div>
 @endif
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
  @endforeach
<div class="row-fluid">
    <div class="col-xs-2 greyline"><strong>TOTAL</strong></div>
    <div class="col-xs-2 greyline">{{{$score}}}&#37; allocated</div>
    <div class="col-xs-2 greyline">&nbsp;</div>
    <div class="col-xs-2 greyline">&nbsp;</div>
    <div class="col-xs-2 greyline">&nbsp;</div>
    <div class="col-xs-2 greyline">&nbsp;</div>
  </div>
@endif
{!! Form::open(array('id' => 'ownership', 'name' => 'ownership')) !!}
{!! Form::hidden('control_ownership', '', array('id' => 'control_ownership')) !!}
{!! Form::hidden('bond_id', '', array('id' => 'bond_id')) !!}
{!! Form::hidden('ownership_id', '', array('id' => 'ownership_id')) !!}
{!! Form::hidden('policyholder_id', '', array('id' => 'policyholder_id')) !!}
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12"><strong>ADD NEW POLICYHOLDER</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">   
    <div class="col-xs-4"><p>Policyholder:</p></div>
    <div class="col-xs-6">{!! Form::select('policyholder', $owners, 0, array('class' => 'form-control')) !!}</div>
    <div class="col-xs-2">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">   
    <div class="col-xs-4"><p>Percentage Split &#40;&#37;&#41;:</p></div>
    <div class="col-xs-6">{!! Form::text('percentage_split', Input::old('percentage_split') ? Input::old('percentage_split') : (isset($ownership->percentage_split) ? $ownership->percentage_split : ''), array('class' => 'form-control')) !!}</div>
    <div class="col-xs-2">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@if($errors->has('percentage_split'))
<div class="row-fluid">
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-6">{!! $errors->first('percentage_split', '<span class="error">:message</span>') !!}</div>
 <div class="col-xs-2">&nbsp;</div>
</div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endif
<div class="row-fluid">   
    <div class="col-xs-4"><p><strong>OF</strong></p></div>
    <div class="col-xs-2">{!! Form::selectRange('segment_start', 1, $total_segments, 1, array('class' => 'form-control')) !!}</div>
    <div class="col-xs-2">&nbsp;to Segment&nbsp;</div>
    <div class="col-xs-2">{!! Form::selectRange('segment_end', 1, $total_segments, $total_segments, array('class' => 'form-control')) !!}</div>
    <div class="col-xs-2">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">   
    <div class="col-xs-4"><p>If these segments have been assigned to the policyholder, enter the date of the assignment</p></div>
    <div class="col-xs-6">{!! Form::text('assignment_date', Input::old('assignment_date') ? Input::old('assignment_date') : (isset($ownership->assignment_date) ? date('d/m/Y', strtotime($ownership->assignment_date)) : ''), array('id' => 'assignment_date', 'class' => 'form-control', 'size' => '16')) !!}<br>(dd/mm/yyyy format)
</div>
    <div class="col-xs-2">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@if($errors->has('assignment_date'))
<div class="row-fluid">
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-6">{!! $errors->first('assignment_date', '<span class="error">:message</span>') !!}</div>
 <div class="col-xs-2">&nbsp;</div>
</div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endif
<div class="row-fluid">   
    <div class="col-xs-4"><p>If these segments have become used for a security for a debt, please enter the date of the transaction</p></div>
    <div class="col-xs-6">{!! Form::text('security_debt_date', Input::old('security_debt_date') ? Input::old('security_debt_date') : (isset($ownership->security_debt_date) ? date('d/m/Y', strtotime($ownership->security_debt_date)) : ''), array('id' => 'security_debt_date', 'class' => 'form-control', 'size' => '16')) !!}
<br>(dd/mm/yyyy format)
</div>
    <div class="col-xs-2">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@if($errors->has('security_debt_date'))
<div class="row-fluid">
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-6">{!! $errors->first('security_debt_date', '<span class="error">:message</span>') !!}</div>
 <div class="col-xs-2">&nbsp;</div>
</div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endif
<div class="row-fluid">   
    <div class="col-xs-4"><p>Is the policyholder acting in the capacity of Trustee ?</p></div>
    <div class="col-xs-6">{!! Form::checkbox('trustee_investment', '1', !isset($ownership->trustee_investment) ? false : (isset($ownership->trustee_investment) && $ownership->trustee_investment == 1 ? true:false) ) !!}</div>
    <div class="col-xs-2">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">   
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-6">{!! Form::button('<span class="glyphicon glyphicon-ok"></span> Validate', array('id' => 'validateOwnership', 'class' => 'btn btn-primary')) !!}</div>
    <div class="col-xs-2">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
{!! Form::close() !!}
</div>
