<script language="JavaScript" type="text/javascript">

$().ready(function() {
document.body.scrollTop = document.documentElement.scrollTop = 0;
});

function delete_relationship(linked_policyholder, relationship_id, client_name) {

bootbox.confirm("<p>Are you sure that you want to delete the following individual:</p><p>&nbsp;</p><p>"+client_name+" ?</p>", function(result) {
	if(result == true) {

        $('#linked_policyholder').val(linked_policyholder);
        $('#relationship_id').val(relationship_id);
	$('#r_process').val('delete');


		var formData = $('#relationships :hidden').serialize();
                $.post('{!! URL::Route('update_relationships') !!}', formData,
                 function(data){
                         $("#containerRelationships").html(data);
                 });

        }
	});
        }


$('#validateRelationships').click(function(e) {

		$('#linked_policyholder').val($('#policyholders option:selected').val());
		$('#relationship_id').val($('#relationship_type option:selected').val());
		$('#r_process').val('add');
                e.preventDefault();
                var formData = $('#relationships :hidden').serialize();
                $.post('{!! URL::Route('update_relationships') !!}', formData,
                 function(data){
                         $("#containerRelationships").html(data);
                 });
        });

</script>
<div id="containerRelationships">
@if (Session::has('message'))<div class="row-fluid"><div class="col-xs-12 error">
<strong>{{ Session::get('message') }}</strong></div></div>
@endif 
<?php if(Session::has('message')) { Session::forget('message'); }?>
  @if (count($relationships) > 0)
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12"><strong>INDIVIDUALS LINKED TO CURRENT POLICYHOLDER:</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">   
    <div class="col-xs-5 greyline"><strong>Individual</strong></div>
    <div class="col-xs-5 greyline"><strong>Relationship</strong></div>
    <div class="col-xs-2 greyline"><strong>Delete</strong></div>
  </div>
  @for($i= 0; $i < count($relationships); $i++)
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-5">
{{{$relationships[$i]['surname']}}}, {{{$relationships[$i]['first_name']}}}
	</div>
    <div class="col-xs-5">
{{{$relationships[$i]['relationship_type']}}}
	</div>
    <div class="col-xs-2">
<button type='button' class='btn btn-default' alt='Delete Relationship'  onClick='delete_relationship({{{$relationships[$i]['policyholder_id']}}}, {{{$relationships[$i]['id']}}}, "{{{$relationships[$i]['surname'].', '.$relationships[$i]['first_name']}}}")'>
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
{!! Form::open(array('id' => 'relationships', 'name' => 'relationships')) !!}
{!! Form::hidden('relationship_id', '', array('id' => 'relationship_id')) !!}
{!! Form::hidden('linked_policyholder', '', array('id' => 'linked_policyholder')) !!}
{!! Form::hidden('r_process', '', array('id' => 'r_process')) !!}
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12"><strong>Add New Relationship</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">   
    <div class="col-xs-4">Individual:</div>
    <div class="col-xs-6">{!! Form::select('policyholders', $policyholders, '', array('id' => 'policyholders', 'class' => 'form-control')) !!}</div>
    <div class="col-xs-2"></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">   
    <div class="col-xs-4">Relationship Type</div>
    <div class="col-xs-6">{!! Form::select('relationship_type', $relationship_types, '', array('id' => 'relationship_type', 'class' => 'form-control')) !!}</div>
    <div class="col-xs-2"></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">   
    <div class="col-xs-4">&nbsp;</div>
    <div class="col-xs-6">{!! Form::button('<span class="glyphicon glyphicon-plus"></span> Add', array('id' => 'validateRelationships', 'class' => 'btn btn-primary')) !!}</div>
    <div class="col-xs-2"></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
 {!! Form::close() !!}
</div>
