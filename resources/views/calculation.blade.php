@extends('layouts.master')

@section('header')
<h4>Add &#47; Edit Calculation</h4>
<script>
$().ready(function() {
	document.body.scrollTop = document.documentElement.scrollTop = 0;
});

function add(bond_id) {

        $('#bond_id').val(bond_id);
        $('#process').val("add");
        $('#calculation').submit();

        }


function delete_bond(bond_id, bond_insurer, bond_policy_number) {

bootbox.confirm("<p>Are you sure that you want to remove the bond from the calculation:</p><p>&nbsp;</p><p>"+bond_insurer+"</p><p>"+bond_policy_number+" ?</p>", function(result) {
        if(result == true) {

        $('#bond_id').val(bond_id);
        $('#process').val("delete");
        $('#calculation').submit();

        }
	});
}

function redirect() {

        $('#process').val("generate");
        $('#calculation').submit();

}

$(document).on('hidden.bs.modal', function (e) {
    $(e.target).removeData('bs.modal');
});

</script>
@stop
@section('content')
@if (Session::has('message')) <div class="col-xs-12 error"><strong>{{ Session::get('message') }}</strong></div> @endif
<?php if(Session::has('message')) { Session::forget('message'); } ?>
@if (count($items) > 0) 
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12"><strong>BONDS TO BE INCLUDED IN THE CALCULATION</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-5"><strong>Policyholder&#40;s&#41;</strong></div>
    <div class="col-xs-5"><strong>Bond Name</strong></div>
    <div class="col-xs-2"><strong>Delete</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12 greyline">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@for($z = 0; $z < count($items); $z++)
<div class="row-fluid">
<div class="col-xs-5">
@for($j = 0; $j < count($items[$z]); $j++) 
{{{ $items[$z][$j]->surname }}}, {{{ $items[$z][$j]->first_name }}}
@if(count($items[$z]) > 1)
@if($j < count($items[$z]) - 1)  
&nbsp;&nbsp;&#47;&nbsp;&nbsp;
@endif
@endif
@endfor
</div>
<div class="col-xs-5">{{{ $items[$z][0]->insurer }}}&#32;&#47;&#32;{{{ $items[$z][0]->policy_number }}}</div>
<div class="col-xs-2"><button type='button' class='btn btn-default' alt='Delete Bond'  onClick='delete_bond("{{{ $items[$z][0]->bond_id }}}", "{{{ $items[$z][0]->insurer }}}", "{{{ $items[$z][0]->policy_number }}}")'><span class='glyphicon glyphicon-trash'></span></button>
   </div>
</div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@endfor
<div class="row-fluid">
    <div class="col-xs-12 center"><button type='button' class='btn btn-primary' alt='Generate Report' onClick='redirect()'><span class='glyphicon glyphicon-book'></span> Generate Report</button></div>
  </div>
 @else
<div class="row-fluid">
    <div class="col-xs-12 center"><strong>No Bonds currently selected.</strong></div>
  </div>
@endif
<div class="row-fluid">
    <div class="col-xs-12 greyline">&nbsp;</div>
  </div>

<p>&nbsp;</p>
{!! Form::open(['id' => 'calculation', 'route' => 'calculation']) !!}
{!! Form::hidden('bond_id', '', array('id' => 'bond_id')) !!}
{!! Form::hidden('process', '', array('id' => 'process')) !!}
{!! Form::hidden('step', '', array('id' => 'step')) !!}
{!! Form::hidden('calculation_id', $calculation_id, array('id' => 'calculation_id')) !!}
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
  </div>
<div class="row-fluid">
    <div class="col-xs-8 form-inline">Search for bond by owners surname: {!! Form::text('term', '', array('class' => 'form-control')) !!}</div>
    <div class="col-xs-2"><button type="submit" class="btn btn-default" alt="Search for Bond" onClick = "search(this)"><span class="glyphicon glyphicon-search"></span> Search</button></div>
    <div class="col-xs-2">&nbsp;</div>
  </div>

<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
@if (count($bonds) > 0) 
<div class="row-fluid">
    <div class="col-xs-12"><strong>ADD BOND TO CALCULATION</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
</div>
<div class="row-fluid">
    <div class="col-xs-5"><strong>Policyholder&#40;s&#41;</strong></div>
    <div class="col-xs-5"><strong>Bond Name</strong></div>
    <div class="col-xs-1"><strong>Add Bond</strong></div>
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
    <div class="col-xs-5">
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
{{{ $bonds[$i][0]->insurer}}} &#32;&#47;&#32; {{{ $bonds[$i][0]->policy_number }}}
</div>
<div class="col-xs-1 center">
	<button type='button' class='btn btn-default' alt='Add Bond'  onClick='add("{{{ $bonds[$i][0]->bond_id }}}")'><span class='glyphicon glyphicon-hand-right'></span></button>
</div>
<div class="col-xs-1 center">
<a href="{!! URL::Route('extra_details',array('bond' => $bonds[$i][0]->bond_id)) !!}"  data-target="#myModal" role="button" class="btn btn-default" id="modal-link" data-toggle="modal"><span class='glyphicon glyphicon-info-sign'></span></a>
</div>
@endif
<div class="row-fluid">
    <div class="col-xs-12 greyline">&nbsp;</div>
  </div>
@endfor
@endif
<div class="row-fluid">
    <div class="col-xs-12 greyline">&nbsp;</div>
  </div>
@if($pages > 1)
<div class="row-fluid">
    <div class="col-xs-12"><strong>Results:</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12 greyline">Page&#58;&nbsp;
@for($q = 1; $q <= $pages; $q++)
<a href="{!! URL::route('displayBonds', array('step' => $q)) !!}">@if ($step == $q) <strong> @endif {{{ $q }}} @if ($step == $q) </strong> @endif</a>
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
