@extends('layouts.master')

@section('header')
<h4>Search For / Edit Existing Policyholders</h4>
<script>
$().ready(function() {
	document.body.scrollTop = document.documentElement.scrollTop = 0;
});


function delete_plan(first_name, surname, policyholder_id) {

bootbox.confirm("<p>Are you sure that you want to delete the record under the name of:</p><p>&nbsp;</p><p>"+first_name+" "+surname+" ?</p>", function(result) {
        if(result == true) {
        $('#first_name').val(first_name);
        $('#surname').val(surname);
        $('#policyholder_id').val(policyholder_id);
        $('#process').val("delete");
        $('#policyholders').submit();
	}

        });
}

function go_to(first_name, surname, policyholder_id) {

        $('#first_name').val(first_name);
        $('#surname').val(surname);
        $('#policyholder_id').val(policyholder_id);
        $('#process').val("redirect");
        $('#policyholders').submit();

        }

</script>
@stop
@section('content')
{!! Form::open(['id' => 'policyholders', 'route' => 'policyholders']) !!}
{!! Form::hidden('first_name', '', array('id' => 'first_name')) !!}
{!! Form::hidden('surname', '', array('id' => 'surname')) !!}
{!! Form::hidden('policyholder_id', 0, array('id' => 'policyholder_id')) !!}
{!! Form::hidden('process', '', array('id' => 'process')) !!}
{!! Form::hidden('step', '', array('id' => 'step')) !!}
@if (Session::has('message')) <div class="col-xs-12 error"><strong>{!! Session::get('message') !!}</strong></div> @endif
<?php if(Session::has('message')) { Session::forget('message'); } ?>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-8 form-inline">Search for policyholder by surname: {!! Form::text('term', '', array('class' => 'form-control')) !!}</div>
    <div class="col-xs-2"><button type="submit" class="btn btn-default" alt="Search for Bond" onClick = "search(this)"><span class="glyphicon glyphicon-search"></span> Search</button></div>
    <div class="col-xs-2">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12 greyline">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@if (count($policyholders) > 0)
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12"><strong>POLICYHOLDERS AND BENEFICIARIES</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-8 left"><strong>Name</strong></div>
    <div class="col-xs-2 center"><strong>Edit</strong></div>
    <div class="col-xs-2 center"><strong>Delete</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12 greyline">&nbsp;</div>
  </div>
@for($i = ((($step * 20) - 19) - 1); $i <= (($step * 20) - 1); $i++)
@if(array_key_exists($i, $policyholders))
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-8 left">
{{{$policyholders[$i]->surname}}}, {{{$policyholders[$i]->first_name}}} &#40;{{{date('d/m/Y', strtotime($policyholders[$i]->dob))}}}&#41;
	</div>
    <div class="col-xs-2 center">
<button type="button" class="btn btn-default" alt="Edit Policyholder" onClick = "go_to('{{{$policyholders[$i]->first_name}}}', '{{{$policyholders[$i]->surname}}}', '{{{$policyholders[$i]->id}}}')"><span class="glyphicon glyphicon-edit"></span> Edit</button>
	</div>
    <div class="col-xs-2 center">
<button type="button" class="btn btn-default" alt="Delete Policyholer" onClick = "delete_plan('{{{$policyholders[$i]->first_name}}}', '{{{$policyholders[$i]->surname}}}', '{{{$policyholders[$i]->id}}}')"><span class="glyphicon glyphicon-trash"></span> Delete</button>
	</div>
    </div>
<div class="row-fluid">
    <div class="col-xs-12 greyline">&nbsp;</div>
  </div>
@endif
@endfor
@endif
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@if($pages > 1)
<div class="row-fluid">
    <div class="col-xs-12 greyline"><strong>Results:</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12 greyline">Page&#58;&nbsp;
@for($q = 1; $q <= $pages; $q++)
<a href="{!! URL::route('showPolicyholders', array('step' => $q)) !!}">@if ($step == $q) <strong> @endif {{{ $q }}} @if ($step == $q) </strong> @endif</a>
@endfor
</div>
@endif
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
{!! Form::close() !!}
@stop
