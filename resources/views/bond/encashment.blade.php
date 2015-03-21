<script>
$().ready(function() {
$("#segments_encashment_date").mask("99/99/9999");
$("#segments_encashment_date").datepicker({format: 'dd/mm/yyyy', autoclose: true, keyboardNavigation : true });

$('#validateEncashments').click(function(e) 
   {
		e.preventDefault();
        	$('input[name="control_encashment"]').val('2');
                var formData = $('#encashment :hidden, #encashment :input, #encashment :checkbox').serialize();
                $.post('{!! URL::Route('update_encashment') !!}', formData,
                 function(data){
                         $("#containerEncashments").html(data);
                 });
        });

document.body.scrollTop = document.documentElement.scrollTop = 0;
});

	function delete_encashment(bondId, encashmentId, segmentsProceeds, segmentsEncashmentDate) {
	bootbox.confirm("<p>Are you sure that you want to delete the following:</p><p>&nbsp;</p><p>Encashment Date: "+segmentsEncashmentDate+"</p><p>Amount: "+segmentsProceeds+" ?</p>", function(result) {
	if(result == true) {

        $('input[name="bond_id"]').val(bondId);
        $('input[name="encashment_id"]').val(encashmentId);
        $('input[name="control_encashment"]').val('1');
	var formData = $('#encashment :hidden').serialize();
		$.post('{!! URL::Route('update_encashment') !!}', formData,
                 function(data){
                         $("#containerEncashments").html(data);
		 });
	}
	});
      }
</script>
<div id="containerEncashments">
@if (Session::has('message'))<div class="row-fluid"><div class="col-xs-12 error">
<strong>{{ Session::get('message') }}</strong></div></div>
@endif 
<?php if(Session::has('message')) { Session::forget('message'); }?>
@if (count($encashments) > 0)
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12"><strong>ENCASHMENTS MADE FROM BOND:</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-3 greyline"><strong>Encashment Date</strong></div>
    <div class="col-xs-3 greyline"><strong>Segment Number&#40;s&#41;</strong></div>
    <div class="col-xs-3 greyline"><strong>Encashment Proceeds</strong></div>
    <div class="col-xs-3 greyline"><strong>Delete ?</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@for($z = 0; $z < count($encashments); $z++) 
<div class="row-fluid">
    <div class="col-xs-3">{{{ date('d/m/Y', strtotime($encashments[$z]['segments_encashment_date'])) }}}</div>
    <div class="col-xs-3">
@if($encashments[$z]['segment_start'] == $encashments[$z]['segment_end'])
{{{ $encashments[$z]['segment_start'] }}}
@else
{{{ $encashments[$z]['segment_start']}}} - {{{ $encashments[$z]['segment_end'] }}}
@endif
  </div>
    <div class="col-xs-3">&pound;{{{ number_format($encashments[$z]['segments_proceeds'],2,'.',',') }}}</div>
    <div class="col-xs-3"><button type='button' class='btn btn-default' alt='Delete Increment'  onClick='delete_encashment("{{{ $encashments[$z]['bond_id'] }}}", "{{{ $encashments[$z]['id'] }}}", "&pound;{{{ number_format($encashments[$z]['segments_proceeds'],2,'.',',') }}}", "{{{ date('d/m/Y', strtotime($encashments[$z]['segments_encashment_date'])) }}}")'><span class='glyphicon glyphicon-trash'></span> Delete</button>
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
{!! Form::open(array('id' => 'encashment', 'name' => 'encashment')) !!}
{!! Form::hidden('control_encashment', '', array('id' => 'control_encashment')) !!}
{!! Form::hidden('bond_id', '', array('id' => 'bond_id')) !!}
{!! Form::hidden('encashment_id', '', array('id' => 'encashment_id')) !!}
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12"><strong>ADD NEW ENCASHMENT</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">   
    <div class="col-xs-4">Segment Numbers that were encashed:</div>
    <div class="col-xs-2">{!! Form::selectRange('segment_start', 1, $segments, 1, array('class' => 'form-control')) !!}</div>
    <div class="col-xs-2">&nbsp;to Segment&nbsp;</div>
    <div class="col-xs-2">{!! Form::selectRange('segment_end', 1, $segments, $segments, array('class' => 'form-control')) !!}</div>
    <div class="col-xs-2">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">   
    <div class="col-xs-4"><p>Encashment Proceeds &#40;&pound;;&#41;:</p></div>
    <div class="col-xs-6">{!! Form::text('segments_proceeds', (Input::old('segments_proceeds') ? Input::old('segments_proceeds') :  ''), array('class' => 'form-control')) !!}</div>
    <div class="col-xs-2">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
 @if($errors->has('segments_proceeds'))
<div class="row-fluid">
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-6">{!! $errors->first('segments_proceeds', '<span class="error">:message</span>') !!}</div>
 <div class="col-xs-2">&nbsp;</div>
</div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endif
<div class="row-fluid">   
    <div class="col-xs-4">Date of Encashment (*)</div>
    <div class="col-xs-6">
{!! Form::text('segments_encashment_date', (Input::old('segments_encashment_date') ? Input::old('segments_encashment_date') : ''), array('id' => 'segments_encashment_date', 'class' => 'form-control', 'size' => '16')) !!}
    <br>(dd/mm/yyyy format)
    </div>
    <div class="col-xs-2">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
 @if($errors->has('segments_encashment_date'))
<div class="row-fluid">
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-6">{!! $errors->first('segments_encashment_date', '<span class="error">:message</span>') !!}</div>
 <div class="col-xs-2">&nbsp;</div>
</div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endif
<div class="row-fluid">   
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-6">{!! Form::button('<span class="glyphicon glyphicon-ok"></span> Validate', array('id' => 'validateEncashments', 'class' => 'btn btn-primary')) !!}</div>
    <div class="col-xs-2">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12 blue">All entries marked with an asterisk (*) must be completed.<div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
{!! Form::close() !!}
</div>
