<script>
$().ready(function() {
$('#validateSegments').click(function(e) 
   {
	   	e.preventDefault();
		$('#seg_count').val('{{{ $seg_count }}}');
		var formData = $('#segments :hidden, #segments :input').serialize();
		$.post('{!! URL::Route('update_segments') !!}', formData,
                 function(data){
                         $("#containerSegments").html(data);
		 });
	});

document.body.scrollTop = document.documentElement.scrollTop = 0;
});
</script>
<div id="containerSegments">
@if (Session::has('message'))<div class="row-fluid"><div class="col-xs-12 error">
<strong>{{ Session::get('message') }}</strong></div></div>
@endif 
<?php if(Session::has('message')) { Session::forget('message'); }?>
{!! Form::open(array('id' => 'segments', 'name' => 'segments')) !!}
{!! Form::hidden('control_segments', '', array('id' => 'control_segments')) !!}
{!! Form::hidden('seg_count', '', array('id' => 'seg_count')) !!}
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12"><strong>This screen is used to represent the number of segments at the inception date of the bond. You should only ever need to ammend these figures if the bond was not segmented in equal proportions at outset. i.e. If a greater amount was allocated to specific segments, with smaller amounts applied to the remaining segments. Do not use this screen to manipulate the segments if increments or segment surrenders have occured; instead use the Increments tab to add a new increment, then return to this tab to ammend the segments if necessary.</strong></div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<table class="table table-hover borderless">
<tbody>
<tr align="center" valign="middle">
          <td class="main" align="center"><strong>Segment Number</strong></td>
          <td class="main" align="center" ><strong>Investment</strong></td>
          <td class="main" align="center" ><strong>5% Allowance</strong></td>
          <td class="main" align="center" ><strong>Current Value</strong></td>
          <td class="main" align="center" ><strong>Ownership</strong></td>
          <td class="main" align="center" ><strong>Assigned</strong></td>
        </tr>
        <tr>
          <td class="main">&nbsp;</td>
          <td class="main" colspan="4">&nbsp;</td>
          <td class="main">&nbsp;</td>
        </tr>
	@for ($i=0; $i < $seg_count; $i++) 
		
		@if (count($increments) > 0) 

		@for ($a=0; $a < count($segment_offset); $a++) 

			@if ($i == $segment_offset[$a]) 
			<tr class="b2">
		          <td>&nbsp;</td>
		          <td>&nbsp;</td>
		          <td>&nbsp;</td>
		          <td>&nbsp;</td>
		          <td>&nbsp;</td>
		          <td>&nbsp;</td>
		        </tr>

			<tr class="b2"> 
		           <td>&nbsp;</td>
		         <td colspan="4" class="b2"><strong>Increment Number {{{ ($a+1) }}} (&pound;{{{ number_format($increments[$a]["increment_amount"], 2, ".", ",") }}}) - Commencement Date: {{{ date('d/m/Y', strtotime($increments[$a]["increment_commencement_date"])) }}}</td></strong>
		    <td>&nbsp;</td>
		        </tr>
		        <tr>
		          <td>&nbsp;</td>
		          <td>&nbsp;</td>
		          <td>&nbsp;</td>
		          <td>&nbsp;</td>
		          <td>&nbsp;</td>
		          <td>&nbsp;</td>
		        </tr>
		@endif
		@endfor
		@endif
	       @if($i % 2 == 0) <tr class="b1">
		@else <tr class="b2"> @endif
		<td class="main" align="center">{{{ ($i+1) }}}</td>
          <td class="main" align="center">
	    {!! Form::text('segment_amount['.$i.']', (isset($all_segments[$i]['segment_amount']) ? number_format($all_segments[$i]['segment_amount'], 2,'.','') : ''), array('size' => 10, 'class' => 'form-control')) !!}</td>
          <td class="main" align="center">&pound;{{{ number_format(($all_segments[$i]['segment_amount'] / 100 * 5),2,'.',',') }}}
	  </td>
          <td class="main" align="center">{!! Form::text('encashment_proceeds['.$i.']', (isset($all_segments[$i]['encashment_proceeds']) ?  number_format($all_segments[$i]['encashment_proceeds'], 2,'.','') : ''), array('size' => 10, 'class' => 'form-control')) !!}</td>
	  <td class="main" align="center">
	  @if (isset($ownership_segments[$i])) 
	  {{{ $ownership_segments[$i] }}} 
	  @else 
	&nbsp;
	  @endif
	</td>
		<td class="main" align="center">
	@if (isset($assignment[$i])) 
	{{{ $assignment[$i] }}}
	@else 
	&nbsp;
	@endif
	</td>
        </tr> 
	@if($errors->has("segment_amount.".$i))
	<tr><td colspan="6">{!! $errors->first("segment_amount.".$i, "<span class='error'>:message</span>") !!}</td></tr> 
	@endif
	@if($errors->has("encashment_proceeds.".$i))
	<tr><td colspan="6">{!! $errors->first("encashment_proceeds.".$i, "<span class='error'>:message</span>") !!}</td></tr>
	@endif
@endfor	
</tbody>
</table>
<div class="row-fluid">
    <div class="col-xs-5">&nbsp;</div>
    <div class="col-xs-2">{!! Form::button('<span class="glyphicon glyphicon-ok"></span> Validate', array('id' => 'validateSegments', 'class' => 'btn btn-primary')) !!}</div>
    <div class="col-xs-5">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12">&nbsp;</div>
  </div>
<div class="row-fluid">
    <div class="col-xs-12 blue">Important! If you subsequently ammend the number of segments or the initial investment amount on the Bond Details Tab, this will reset these values. This may not matter if the bond was segmented equally, but you may need to ammend these figures if the bond was set up on a non-standard basis. Do not use this Tab to take into account the effects of Increments or Segments Surrenders, as this may give rise to incorrect calculations.</div>
  </div>
{!! Form::close() !!}
</div>
