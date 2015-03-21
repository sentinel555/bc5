<script language="JavaScript" type="text/javascript">

$().ready(function() {
$("#start_date").mask("99/99/9999");
$("#end_date").mask("99/99/9999");
$("#start_date").datepicker({format: 'dd/mm/yyyy', autoclose: true, keyboardNavigation : true });
$("#end_date").datepicker({format: 'dd/mm/yyyy', autoclose: true, keyboardNavigation : true });
document.body.scrollTop = document.documentElement.scrollTop = 0;
});

function delete_non_residence(id, start_date, end_date) {

bootbox.confirm("<p>Are you sure that you want to delete the following period of non-residency:</p><p>&nbsp;</p><p>Start Date: "+start_date+"</p><p>End Date: "+end_date+" ?</p>", function(result) {
	if(result == true) {
        $('#n_id').val(id);
	$('#n_process').val('delete');


		var formData = $('#non_residence :hidden, #non_residence :input').serialize();
                $.post('{!! URL::Route('update_nonresidence') !!}', formData,
                 function(data){
                         $("#containerNonresidence").html(data);
		 });

	}

        });

        }


$('#validateNonresidence').click(function(e) {

		$('#n_process').val('add');
                e.preventDefault();
                var formData = $('#non_residence :hidden, #non_residence :input').serialize();
                $.post('{!! URL::Route('update_nonresidence') !!}', formData,
                 function(data){
                         $("#containerNonresidence").html(data);
                 });
        });

</script>
<div id="containerNonresidence">
@if (Session::has('message'))<div class="row-fluid"><div class="col-xs-12 error">
<strong>{{ Session::get('message') }}</strong></div></div>
@endif 
<?php if(Session::has('message')) { Session::forget('message'); }?>
 @if (count($non_residence) > 0)
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12"><strong>PERIODS OF NON-RESIDENCE (RESIDENCE OUTSIDE THE UK):</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-5 greyline"><strong>Start Date</strong></div>
    <div class="col-xs-5 greyline"><strong>End Date</strong></div>
    <div class="col-xs-2 greyline"><strong>Delete</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
  @for($i= 0; $i < count($non_residence); $i++)
<div class="row-fluid">
<div class="col-xs-5">{{{$non_residence[$i]['start_date']}}}</div>
<div class="col-xs-5">{{{$non_residence[$i]['end_date']}}}</div>
<div class="col-xs-2"><button type='button' class='btn btn-default' alt='Delete Non Residence'  onClick='delete_non_residence({{{ $non_residence[$i]['id'] }}}, "{{{ date('d/m/Y', strtotime($non_residence[$i]['start_date'])) }}}", "{{{ date('d/m/Y', strtotime($non_residence[$i]['end_date'])) }}}")'>
<span class='glyphicon glyphicon-trash'></span> Delete</button>
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
{!! Form::open(array('id' => 'non_residence', 'name' => 'non_residence')) !!}
{!! Form::hidden('n_id', '', array('id' => 'n_id')) !!}
{!! Form::hidden('n_process', '', array('id' => 'n_process')) !!}
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12"><strong>Add New Period of Non-Residency</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-4">Start Date:</div>
    <div class="col-xs-6">{!! Form::text('start_date', '', array('id' => 'start_date', 'class' => 'form-control')) !!}</div>
    <div class="col-xs-2">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@if($errors->has('start_date'))
<div class="row-fluid">
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-6">{!! $errors->first('start_date', '<span class="error">:message</span>') !!}</div>
 <div class="col-xs-2">&nbsp;</div>
 </div> 
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endif
<div class="row-fluid">
    <div class="col-xs-4">End Date:</div>
    <div class="col-xs-6">{!! Form::text('end_date', '', array('id' => 'end_date', 'class' => 'form-control')) !!}</div>
    <div class="col-xs-2">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@if($errors->has('end_date'))
<div class="row-fluid">
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-6">{!! $errors->first('end_date', '<span class="error">:message</span>') !!}</div>
 <div class="col-xs-2">&nbsp;</div>
 </div> 
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endif

<div class="row-fluid">   
    <div class="col-xs-4"></div>
    <div class="col-xs-6">{!! Form::button('<span class="glyphicon glyphicon-ok"></span> Validate', array('id' => 'validateNonresidence', 'class' => 'btn btn-primary')) !!}</div>
    <div class="col-xs-2"></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
{!! Form::close() !!}
</div>
