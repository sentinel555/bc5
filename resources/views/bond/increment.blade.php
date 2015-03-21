<script>
$().ready(function() {
$("#increment_commencement_date").mask("99/99/9999");
$("#increment_commencement_date").datepicker({format: 'dd/mm/yyyy', autoclose: true, keyboardNavigation : true });

$('#validateIncrements').click(function(e) 
   {
		e.preventDefault();
        	$('input[name="control_increment"]').val("2");
                var formData = $('#increment :hidden, #increment :input, #increment :checkbox').serialize();
                $.post('{!! URL::Route('update_increment') !!}', formData,
                 function(data){
                         $("#containerIncrements").html(data);
                 });
        });

document.body.scrollTop = document.documentElement.scrollTop = 0;
});

function delete_increment(bondId, incrementId, incDate, amount) {

bootbox.confirm("<p>Are you sure that you want to delete the following Increment:</p><p>&nbsp;</p><p>Start Date:"+incDate+"</p><p>Amount £: "+amount+" ?</p>", function(result) {
	if(result == true) {

        $('input[name="bond_id"]').val(bondId);
        $('input[name="increment_id"]').val(incrementId);
        $('input[name="control_increment"]').val("1");
 	var formData = $('#increment :hidden').serialize();
                $.post('{!! URL::Route('update_increment') !!}', formData,
                 function(data){
                         $("#containerIncrements").html(data);
                 });
		}
		});
        }

</script>
<div id="containerIncrements">
@if (Session::has('message'))<div class="row-fluid"><div class="col-xs-12 error">
<strong>{{ Session::get('message') }}</strong></div></div>
@endif 
<?php if(Session::has('message')) { Session::forget('message'); }?>
@if (count($increments) > 0)
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12"><strong>INCREMENTS APPLIED TO BOND:</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-3 greyline"><strong>Increment Date</strong></div>
    <div class="col-xs-3 greyline"><strong>Consideration</strong></div>
    <div class="col-xs-3 greyline"><strong>Additional Segments</strong></div>
    <div class="col-xs-3 greyline"><strong>Delete</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@for($z = 0; $z < count($increments); $z++) 
<div class="row-fluid">
    <div class="col-xs-3">{{{ date('d/m/Y', strtotime($increments[$z]['increment_commencement_date'])) }}}</div>
    <div class="col-xs-3">&pound;{{{ number_format($increments[$z]['increment_amount'],2,'.',',') }}}</div>
    <div class="col-xs-3">{{{ $increments[$z]['increment_segments'] }}}</div>
    <div class="col-xs-3"><button type='button' class='btn btn-default' alt='Delete Increment'  onClick='delete_increment("{{{ $increments[$z]['bond_id'] }}}", "{{{ $increments[$z]['id'] }}}", "{{{ date('d/m/Y', strtotime($increments[$z]['increment_commencement_date'])) }}}", "{{{ number_format($increments[$z]['increment_amount'],2,'.',',') }}}")'><span class='glyphicon glyphicon-trash'></span> Delete</button></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endfor
<div class="row-fluid">
    <div class="col-xs-12 greyline">&nbsp;</div>
  </div>
@endif
{!! Form::open(array('id' => 'increment', 'name' => 'increment')) !!}
{!! Form::hidden('control_increment', '', array('id' => 'control_increment')) !!}
{!! Form::hidden('bond_id', '', array('id' => 'bond_id')) !!}
{!! Form::hidden('increment_id', '', array('id' => 'increment_id')) !!}
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12"><strong>ADD NEW INCREMENT</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">   
    <div class="col-xs-4">Increment Amount &#40;&pound;&#41;:</div>
    <div class="col-xs-6">{!! Form::text('increment_amount', (Input::old('increment_amount') ? Input::old('increment_amount') : ''), array('class' => 'form-control')) !!}</div>
    <div class="col-xs-2">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@if($errors->has('increment_amount'))
<div class="row-fluid">
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-6">{!! $errors->first('increment_amount', '<span class="error">:message</span>') !!}</div>
 <div class="col-xs-2">&nbsp;</div>
</div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endif
<div class="row-fluid">   
    <div class="col-xs-4">Number of additional Segments purchased (+):</div>
    <div class="col-xs-6">{!! Form::text('increment_segments', (Input::old('increment_segments') ? Input::old('increment_segments') :  0), array('class' => 'form-control')) !!}</div>
    <div class="col-xs-2">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@if($errors->has('increment_segments'))
<div class="row-fluid">
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-6">{!! $errors->first('increment_segments', '<span class="error">:message</span>') !!}</div>
 <div class="col-xs-2">&nbsp;</div>
</div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endif
<div class="row-fluid">   
    <div class="col-xs-4">Date of Increment (*):</div>
    <div class="col-xs-6">{!! Form::text('increment_commencement_date', Input::old('increment_commencement_date') ? Input::old('increment_commencement_date') : '',  array('id' => 'increment_commencement_date', 'class' => 'form-control', 'size' => '16')) !!}
    <br>(dd/mm/yyyy format)
   </div>
    <div class="col-xs-2">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@if($errors->has('increment_commencement_date'))
<div class="row-fluid">
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-6">{!! $errors->first('increment_commencement_date', '<span class="error">:message</span>') !!}</div>
 <div class="col-xs-2">&nbsp;</div>
</div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endif
<div class="row-fluid">   
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-6">{!! Form::button('<span class="glyphicon glyphicon-ok"></span> Validate', array('id' => 'validateIncrements', 'class' => 'btn btn-primary')) !!}</div>
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
    <div class="col-xs-12 blue">(+) If the increment was used to purchase an additional number of segments, enter the number of segments. If the increment was used to enhance the existing segments, leave the value as 0.</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
{!! Form::close() !!}
</div>
