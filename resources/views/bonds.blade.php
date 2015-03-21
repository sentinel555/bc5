@extends('layouts.master')

@section('header')
<h4>Search For / Edit Existing Bonds</h4>
<script>
$().ready(function() {
	document.body.scrollTop = document.documentElement.scrollTop = 0;
});

function go_to(bond_id) {


        $('#bond_id').val(bond_id);
        $('#process').val("redirect");
        $('#bonds').submit();

        }

function delete_bond(bond_id, bond_insurer, bond_policy_number) {

bootbox.confirm("<p>Are you sure that you want to delete the bond:</p><p>&nbsp;</p><p>Insurer: "+bond_insurer+"</p><p>Policy Number: "+bond_policy_number+" ?</p>", function(result) {
	if(result == true) {


        $('#bond_id').val(bond_id);
        $('#process').val("delete");
        $('#bonds').submit();

	}
	});

   }

$(document).on('hidden.bs.modal', function (e) {
    $(e.target).removeData('bs.modal');
});

</script>
@stop
@section('content')
{!! Form::open(['id' => 'bonds', 'route' => 'bonds']) !!}
{!! Form::hidden('bond_id', '', array('id' => 'bond_id')) !!}
{!! Form::hidden('process', '', array('id' => 'process')) !!}
{!! Form::hidden('step', '', array('id' => 'step')) !!}
@if (Session::has('message')) <div class="col-xs-12 error"><strong>{!! Session::get('message') !!}</strong></div> @endif
<?php if(Session::has('message')) { Session::forget('message'); } ?>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-3">&nbsp;</div>
    <div class="col-xs-6">&nbsp;</div>
    <div class="col-xs-1">&nbsp;</div>
    <div class="col-xs-1">&nbsp;</div>
    <div class="col-xs-1">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-8 form-inline">Search for bond by owners surname: {!! Form::text('term', '', array('class' => 'form-control')) !!}</div>
    <div class="col-xs-2"><button type="submit" class="btn btn-default" alt="Search for Bond" onClick = "search(this)"><span class="glyphicon glyphicon-search"></span> Search</button></div>
    <div class="col-xs-2">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12 greyline">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@if (count($bonds) > 0) 
<div class="row-fluid">
    <div class="col-xs-12"><strong>MANAGE SAVED BONDS</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-4"><strong>Policyholder&#40;s&#41;</strong></div>
    <div class="col-xs-5"><strong>Bond Name</strong></div>
    <div class="col-xs-1"><strong>Edit</strong></div>
    <div class="col-xs-1"><strong>Delete</strong></div>
    <div class="col-xs-1"><strong>Extra Details</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12 greyline">&nbsp;</div>
  </div>
@for($i = ((($step * 20) - 19) - 1); $i <= (($step * 20) - 1); $i++)
@if(array_key_exists($i, $bonds))
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-4">
@for($j = 0; $j < count($bonds[$i]); $j++) 
{{{ $bonds[$i][$j]->surname }}}, {{{ $bonds[$i][$j]->first_name }}}
@if(count($bonds[$i]) > 1)
@if($j < count($bonds[$i]) - 1) 
&nbsp;&nbsp;&#47;&nbsp;&nbsp;
@endif
@endif
@endfor
</div>
<div class="col-xs-5">
{{{ $bonds[$i][0]->insurer }}} &#32;&#47;&#32; {{{ $bonds[$i][0]->policy_number }}}
</div>
<div class="col-xs-1 center">
	<button type='button' class='btn btn-default' alt='Edit Bond'  onClick='go_to("{{{ $bonds[$i][0]->bond_id }}}")'><span class='glyphicon glyphicon-edit'></span></button>
</div>
<div class="col-xs-1 center">
	<button type='button' class='btn btn-default' alt='Delete Bond'  onClick='delete_bond("{{{ $bonds[$i][0]->bond_id }}}", "{{{ $bonds[$i][0]->insurer }}}", "{{{ $bonds[$i][0]->policy_number }}}")'><span class='glyphicon glyphicon-trash'></span></button>
</div>
<div class="col-xs-1 center">
<a href="{!! URL::Route('extra_details',array('bond' => $bonds[$i][0]->bond_id)) !!}"  data-target="#myModal" role="button" class="btn btn-default" id="modal-link" data-toggle="modal"><span class='glyphicon glyphicon-info-sign'></span></a>
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
<a href="{!! URL::route('showBonds', array('step' => $q)) !!}">@if ($step == $q) <strong> @endif {{{ $q }}} @if ($step == $q) </strong> @endif</a>
@endfor
</div>
@endif
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
                    </div> <!-- /.modal-content -->
    </div> <!-- /.modal-dialog -->
</div> <!-- /.modal -->
{!! Form::close() !!}
@stop
