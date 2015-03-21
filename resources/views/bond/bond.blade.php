<script>
$().ready(function() {
$("#commencement_date").mask("99/99/9999");
$("#encashment_date").mask("99/99/9999");
$("#commencement_date").datepicker({format: 'dd/mm/yyyy', autoclose: true, keyboardNavigation : true });
$("#encashment_date").datepicker({format: 'dd/mm/yyyy', autoclose: true, keyboardNavigation : true });

	$('#validate').click(function(e) {
	 e.preventDefault();
          var formData = $('#bond :input, #bond :checkbox, #bond :hidden').serialize();
	 $.post('{!! URL::Route('update_bond') !!}', formData,
                 function(data){

                         $("#containerBond").html(data);
                 });
		
	 	});

	@if (Session::get('ammend_bond') == 1) sessionStorage.setItem('new_bond', 1); @endif

	document.body.scrollTop = document.documentElement.scrollTop = 0;
});
</script>
<div id="containerBond">
{!! Form::open(array('id' => 'bond', 'name' => 'bond', 'class' => 'form-horizontal')) !!}
{!! Form::hidden('control', '', array('id' => 'control')) !!}
@if (Session::has('message'))<div class="row-fluid"><div class="col-xs-12 error">
<strong>{{ Session::get('message') }}</strong></div></div>
@endif 
<?php if(Session::has('message')) { Session::forget('message'); }?>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
 <div class="row-fluid">   
    <div class="col-xs-4"><p>Insurance Company / Provider:</p></div>
    <div class="col-xs-6">{!! Form::text('insurer', (isset($bond->insurer) ? $bond->insurer : ''), array('class' => 'form-control')) !!}</div>
    <div class="col-xs-2">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@if($errors->has('insurer'))
<div class="row-fluid">
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-6">{!! $errors->first('insurer', '<span class="error">:message</span>') !!}</div>
 <div class="col-xs-2">&nbsp;</div>
 </div> 
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endif
<div class="row-fluid">
    <div class="col-xs-4"><p>Policy Number / Reference:</p></div>
    <div class="col-xs-6">{!! Form::text('policy_number', (isset($bond->policy_number) ? $bond->policy_number : ''), array('class' => 'form-control')) !!}</div>
 <div class="col-xs-2">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@if($errors->has('policy_number'))
<div class="row-fluid">
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-6">{!! $errors->first('policy_number', '<span class="error">:message</span>') !!}</div>
 <div class="col-xs-2">&nbsp;</div>
</div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endif
<div class="row-fluid">
    <div class="col-xs-4"><p>Investment Amount (&pound;):</p></div>
    <div class="col-xs-6">{!! Form::text('investment', (isset($bond->investment) ? number_format($bond->investment, 2,'.','') : ''), array('class' => 'form-control')) !!}</div>
 <div class="col-xs-2">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@if($errors->has('investment'))
<div class="row-fluid">
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-6">{!! $errors->first('investment', '<span class="error">:message</span>') !!}</div>
 <div class="col-xs-2">&nbsp;</div>
     </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endif
<div class="row-fluid">
    <div class="col-xs-4"><p>Surrender Proceeds / Current Value (&pound;):</p></div>
    <div class="col-xs-6">{!! Form::text('encashment_proceeds', (isset($bond->encashment_proceeds) ? number_format($bond->encashment_proceeds, 2,'.','') : ''), array('class' => 'form-control')) !!}{!! Form::checkbox('auto_update_segments', '1', !isset($bond->auto_update_segments) ? true : (isset($bond->auto_update_segments) && $bond->auto_update_segments == 1 ? true:false)) !!}&nbsp;Auto-Update Segment Values upon change &#63; (+)</div>
 <div class="col-xs-2">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@if($errors->has('encashment_proceeds'))
<div class="row-fluid">
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-6">{!! $errors->first('encashment_proceeds', '<span class="error">:message</span>') !!}</div>
    <div class="col-xs-2">&nbsp;</div>
    </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endif
<div class="row-fluid">
    <div class="col-xs-4"><p>Commencement Date (*)</p></div>
    <div class="col-xs-6">{!! Form::text('commencement_date', (isset($bond->commencement_date) ? date('d/m/Y', strtotime($bond->commencement_date)) : ''), array('id' => 'commencement_date', 'class' => 'form-control', 'size' => '16')) !!}</div>
 <div class="col-xs-2">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@if($errors->has('commencement_date'))
<div class="row-fluid">
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-6">{!! $errors->first('commencement_date', '<span class="error">:message</span>') !!}</div>
 <div class="col-xs-2">&nbsp;</div>
 </div>  
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endif
<div class="row-fluid">
    <div class="col-xs-4"><p>Encashment Date (*)</p></div>
    <div class="col-xs-6">{!! Form::text('encashment_date', (isset($bond->encashment_date) ? date('d/m/Y', strtotime($bond->encashment_date)) : date('d').'/'.date('m').'/'.date('Y')),  array('id' => 'encashment_date', 'class' => 'form-control', 'size' => '16')) !!}{!! Form::checkbox('auto_update', '1', !isset($bond->auto_update) ? true : (isset($bond->auto_update) && $bond->auto_update == 1 ? true:false)) !!}&nbsp;Auto-Update to current date&#63; (+)</div>
 <div class="col-xs-2">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@if($errors->has('encashment_date'))
<div class="row-fluid">
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-6">{!! $errors->first('encashment_date', '<span class="error">:message</span>') !!}</div>
 <div class="col-xs-2">&nbsp;</div>
 </div>    
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endif
<div class="row-fluid">
    <div class="col-xs-4"><p>Offshore Bond ?</p></div>
    <div class="col-xs-6"><label class="checkbox-inline">{!! Form::checkbox('offshore_bond', '1', !isset($bond->offshore_bond) ? false : (isset($bond->offshore_bond) && $bond->offshore_bond == 1 ? true : false) ) !!}</label></div>
 <div class="col-xs-2">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
 <div class="row-fluid">     
    <div class="col-xs-4"><p>Number of Segments within bond at date of issue:</p></div>
    <div class="col-xs-6">{!! Form::text('segments', (isset($bond->segments) ? $bond->segments : '10'), array('class' => 'form-control')) !!}</div>
 <div class="col-xs-2">&nbsp;</div>
 </div> 
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@if($errors->has('segments'))
<div class="row-fluid">
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-6">{!! $errors->first('segments', '<span class="error">:message</span>') !!}</div>
 <div class="col-xs-2">&nbsp;</div>
 </div>   
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endif
<div class="row-fluid">
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-6">{!! Form::button('<span class="glyphicon glyphicon-ok"></span> Validate', array('id' => 'validate', 'class' => 'btn btn-primary')) !!}</div>
 <div class="col-xs-2">&nbsp;</div>
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

<div class="row-fluid">
    <div class="col-xs-12 blue">(+) The 'Auto-Update Segment Values' checkbox is set to checked as a default, as the value of each individual segment will be adjusted if the current / surrender value is changed. This is recomended in the majority of cases. However, there are instances, such as when extra segments are added by way of increments, that you may wish to disable this option and edit the segment values manually. The current / surrender value of increments is ordinarily calculated proportionately unless this option is disabled.</div>
</div>  
     <div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>

<div class="row-fluid">
    <div class="col-xs-12 blue">(+) If you select the 'Auto-Update' checkbox, the encashment date will always be updated to the current date. Effectively, this sets a hypothetical encashment date, and is useful when you are working out potential rather than actual liabilities. Please note that this field MUST be set to a valid date.</div>
</div>  
  <div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>

<div class="row-fluid">
    <div class="col-xs-12 blue"><span style="text-decoration: underline;">If you subsequently ammend the original Investment Amount or Number of Segments, this will remove any existing data from the segments tab. This is usually only of concern if the segments are NOT of equal value.</span></div>
</div>
   <div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
{!! Form::close() !!}
</div>
