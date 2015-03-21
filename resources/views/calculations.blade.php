@extends('layouts.master')

@section('header')
<h4>Add &#47; Edit Calculation</h4>
<script>
$().ready(function() {
document.body.scrollTop = document.documentElement.scrollTop = 0;
});

function edit_calculation(id) {


        $('#calculation_id').val(id);
        $('#process').val("edit");
        $('#calculations').submit();

        }


function delete_calculation(id, dt) {

bootbox.confirm("<p>Are you SURE that you want to delete the calculation last updated at:</p><p>&nbsp;</p><p>"+dt+" ?</p>", function(result) {
        if(result == true) {


        $('#calculation_id').val(id);
        $('#process').val("delete");
        $('#calculations').submit();

        }
	});
}

</script>
@stop
@section('content')
{!! Form::open(['id' => 'calculations', 'route' => 'calculations']) !!}
{!! Form::hidden('process', '', array('id' => 'process')) !!}
{!! Form::hidden('step', '', array('id' => 'step')) !!}
{!! Form::hidden('calculation_id', '', array('id' => 'calculation_id')) !!}
@if (Session::has('message'))<div class="row-fluid"><div class="col-xs-12 error">
<strong>{{ Session::get('message') }}</strong></div></div>
@endif 
<?php if(Session::has('message')) { Session::forget('message'); }?>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-8 form-inline">Search for calculation by surname: {!! Form::text('term', '', array('class' => 'form-control')) !!}</div>
    <div class="col-xs-2"><button type="submit" class="btn btn-default" alt="Search for Calculation" onClick = "search(this)"><span class="glyphicon glyphicon-search"></span> Search</button></div>
    <div class="col-xs-2">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12 greyline">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>

@if (count($calculation) > 0) 
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12"><strong>EXISTING CALCULATIONS</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-2"><strong>Date Last Modified</strong></div>
    <div class="col-xs-4"><strong>Policyholder&#40;s&#41;</strong></div>
    <div class="col-xs-4"><strong>Bond Name&#40;s&#41;</strong></div>
    <div class="col-xs-1"><strong>Edit Calculation</strong></div>
    <div class="col-xs-1"><strong>Delete Calculation</strong></div>
  </div>
@for($i = ((($step * 20) - 19) - 1); $i <= (($step * 20) - 1); $i++)
@if(array_key_exists($i, $calculation))
@for($j = 0; $j < count($calculation[$i]); $j++) 
@if($j == 0)
<div class="row-fluid">
    <div class="col-xs-12 greyline">&nbsp;</div>
  </div>
@endif
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>

<div class="row-fluid">
    <div class="col-xs-2">
@if($j == 0) {{{ $calculation[$i][$j][0]['updated_at'] }}} @endif
</div>
<div class="col-xs-4">
@for($k = 0; $k < count($calculation[$i][$j]); $k++) 
{{{ $calculation[$i][$j][$k]['surname'] }}}, {{{ $calculation[$i][$j][$k]['first_name'] }}}
@if(count($calculation[$i][$j][$k]) > 1)
@if($k < count($calculation[$i][$j]) - 1) 
&nbsp;&nbsp;&#47;&nbsp;&nbsp;
@endif
@endif
@endfor
</div>
<div class="col-xs-4">
{{{ $calculation[$i][$j][0]['insurer'] }}} &#32;&#47;&#32; {{{ $calculation[$i][$j][0]['policy_number'] }}}
</div>
<div class="col-xs-1">
	@if($j == 0)	<button type='button' class='btn btn-default' alt='Edit Details'  onClick='edit_calculation(" {{{ $calculation[$i][$j][0]['calculation_id'] }}} ")'><span class='glyphicon glyphicon-edit'></span></button> @endif
</div>
<div class="col-xs-1">
	@if($j == 0) <button type='button' class='btn btn-default' alt='Delete Calculation'  onClick='delete_calculation("{{{ $calculation[$i][$j][0]['calculation_id'] }}}", "{{{ $calculation[$i][$j][0]['updated_at'] }}}")'><span class='glyphicon glyphicon-trash'></span></button> @endif
</div>
</div>
@endfor
@endif
@endfor
@endif
<div class="row-fluid">
    <div class="col-xs-12 greyline">&nbsp;</div>
  </div>
@if($pages > 1)
<div class="row-fluid">
    <div class="col-xs-12 greyline"><strong>Results:</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12 greyline">Page&#58;&nbsp;
    @for($q = 1; $q <= $pages; $q++)
<a href="{!! URL::route('showCalculations', array('step' => $q)) !!}">@if ($step == $q) <strong> @endif {{{ $q }}} @if ($step == $q) </strong> @endif</a>
@endfor
</div>
</div>
@endif
<div class="row-fluid">
    <div class="col-xs-12 greyline">&nbsp;</div>
  </div>
{!! Form::close() !!}
@stop
