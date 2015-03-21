<script language="JavaScript" type="text/javascript">
$().ready(function() {

	
	$('#dob').mask('99/99/9999');
        $('#deceased_on').mask('99/99/9999');
        
	$("#dob").datepicker({format: 'dd/mm/yyyy', autoclose: true, keyboardNavigation : true });

	$("#deceased_on").datepicker({format: 'dd/mm/yyyy', autoclose: true, keyboardNavigation : true });

	if(!$('#deceased').prop('checked')) { $('#deceased_on').prop('disabled', true); }

	
	$('body').on('change', '#deceased', function() { 

		if(!$('#deceased').prop('checked')) { $('#deceased_on').prop('disabled', true); $('#deceased_on').val(''); }
		if($('#deceased').prop('checked')) { $('#deceased_on').prop('disabled', false); }

		});

	
		
	$('#validatePolicyholder').click(function(e) 
   {            $('#deceased_on').prop('disabled', false);
                e.preventDefault();
                var formData = $('#policyholder :hidden, #policyholder :input, #policyholder :checkbox').serialize();
                $.post('{!! URL::Route('update_policyholder') !!}', formData,
                 function(data){
                         $("#containerPolicyholder").html(data);
                 });
        });

	@if (Session::get('ammend_policyholder') == 1) sessionStorage.setItem('new_policyholder', 1); @endif

	 document.body.scrollTop = document.documentElement.scrollTop = 0;
	});
</script>
<div id="containerPolicyholder">

@if (Session::has('message')) <div class="error" id="errPolicyholder"><strong>{{ Session::get('message') }}</strong></div> @endif
<?php Session::forget('message'); ?>
{!! Form::open(array('id' => 'policyholder', 'name' => 'policyholder')) !!}
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
 <div class="row-fluid">   
    <div class="col-xs-4">Title</div>
    <div class="col-xs-6">{!! Form::select('title', array(0 => 'Mr', 1 => 'Ms', 2 => 'Mrs', 3 => 'Miss', 4 => 'Dr', 5 => 'Sir', 6 => 'No Title'), isset($policyholder->title) ? $policyholder->title : 0, array('id' => 'title', 'class' => 'form-control')) !!}</div>
    <div class="col-xs-2"></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">   
    <div class="col-xs-4">First Name(s): (*)</div>
    <div class="col-xs-6">{!! Form::text('first_name', isset($policyholder->first_name) ? $policyholder->first_name : '', array('id' => 'first_name', 'class' => 'form-control')) !!}</div>
    <div class="col-xs-2"></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@if($errors->has('first_name'))
<div class="row-fluid">
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-6">{!! $errors->first('first_name', '<span class="error">:message</span>') !!}</div>
 <div class="col-xs-2">&nbsp;</div>
 </div> 
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endif
<div class="row-fluid">   
    <div class="col-xs-4">Surname: (*)</div>
    <div class="col-xs-6">{!! Form::text('surname', isset($policyholder->surname) ? $policyholder->surname : '', array('id' => 'surname', 'class' => 'form-control')) !!}</div>
    <div class="col-xs-2"></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@if($errors->has('surname'))
<div class="row-fluid">
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-6">{!! $errors->first('surname', '<span class="error">:message</span>') !!}</div>
 <div class="col-xs-2">&nbsp;</div>
 </div> 
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endif
<div class="row-fluid">   
    <div class="col-xs-4">Date of Birth (*)</div>
    <div class="col-xs-6">{!! Form::text('dob', isset($policyholder->dob) ? date('d/m/Y', strtotime($policyholder->dob)) : '', array('id' => 'dob', 'class' => 'form-control', 'size' => '16')) !!}
	    <br>(dd/mm/yyyy format)</div>
    <div class="col-xs-2"></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@if($errors->has('dob'))
<div class="row-fluid">
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-6">{!! $errors->first('dob', '<span class="error">:message</span>') !!}</div>
 <div class="col-xs-2">&nbsp;</div>
 </div> 
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endif
<div class="row-fluid">   
    <div class="col-xs-4">Allowances and Deductions for the current tax year: (*)</div>
    <div class="col-xs-6">{!! Form::text('allowances', isset($policyholder->allowances) ? round($policyholder->allowances, 2) : round($personal_allowance, 2), array('class' => 'form-control')) !!}</div>
    <div class="col-xs-2"></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@if($errors->has('allowances'))
<div class="row-fluid">
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-6">{!! $errors->first('allowances', '<span class="error">:message</span>') !!}</div>
 <div class="col-xs-2">&nbsp;</div>
 </div> 
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endif
<div class="row-fluid">   
    <div class="col-xs-4">Enter Gross Income for current tax year: (*)</div>
    <div class="col-xs-6">{!! Form::text('gross_income', isset($policyholder->gross_income) ? number_format($policyholder->gross_income,2, '.', '') : '', array('class' => 'form-control')) !!}</div>
    <div class="col-xs-2"></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@if($errors->has('gross_income'))
<div class="row-fluid">
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-6">{!! $errors->first('gross_income', '<span class="error">:message</span>') !!}</div>
 <div class="col-xs-2">&nbsp;</div>
 </div> 
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endif
<div class="row-fluid">   
    <div class="col-xs-4">Is the policyholder UK Resident:</div>
    <div class="col-xs-6">{!! Form::checkbox('uk_resident', '1', !isset($policyholder->uk_resident) ? true : (isset($policyholder->uk_resident) AND $policyholder->uk_resident == 1 ? true:false) ) !!}</div>
    <div class="col-xs-2"></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">   
    <div class="col-xs-4">Click if the policyholder is deceased:</div>
    <div class="col-xs-6">{!! Form::checkbox('deceased', '1', isset($policyholder->deceased) AND $policyholder->deceased == 1 ? true:false,  array('id' => 'deceased')) !!}</div>
    <div class="col-xs-2"></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">   
    <div class="col-xs-4">[If deceased, enter the date of death]:</div>
    <div class="col-xs-6">
  <?php $value=''; if(isset($policyholder->deceased_on)) { if($policyholder->deceased_on == "0000-00-00") {$value = '';} else {$value = date('d/m/Y', strtotime($policyholder->deceased_on));} } ?> {!! Form::text('deceased_on', $value, array('id' => 'deceased_on', 'class' => 'form-control', 'size' => '16')) !!} <br>(dd/mm/yyyy format)
    </div>
    <div class="col-xs-2"></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@if($errors->has('deceased_on'))
<div class="row-fluid">
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-6">{!! $errors->first('deceased_on', '<span class="error">:message</span>') !!}</div>
 <div class="col-xs-2">&nbsp;</div>
 </div> 
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endif
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">   
    <div class="col-xs-4"></div>
    <div class="col-xs-6">{!! Form::button('<span class="glyphicon glyphicon-ok"></span> Validate', array('id' => 'validatePolicyholder', 'class' => 'btn btn-primary center')) !!}</div>
    <div class="col-xs-2"></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12 blue">All entries marked with an asterisk (*) must be completed.</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
{!! Form::close() !!}
</div>
