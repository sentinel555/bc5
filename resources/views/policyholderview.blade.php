@extends('layouts.master')

@section('header')
<h4>@if(isset($policyholder->first_name)) Policyholder&#58; {{{$policyholder->surname}}}, {{{$policyholder->first_name}}} @else Edit Policyholder Details @endif</h4>
<script>

$().ready(function() {

	sessionStorage.setItem('new_policyholder', {{{ $new_policyholder}}});

$('#policyholdertab a[href="#policyholder"], #policyholdertab a[href="#relationships"], #policyholdertab a[href="#nonresidence"]').click(function (e) {


  if (sessionStorage.getItem('new_policyholder') == 1) {
  e.preventDefault()
    var self = $(this);
    var url = self.attr('data-url');
    var h = this.hash;
    var href = h.substring(1);
	$.get(url, function(data) {$('#'+href).html(data)});
  $(this).tab('show') }
})

// load first tab content
$('#policyholder').load($('.active a').attr("data-url"),function(result){
  $('.active a').tab('show');
});
   
});

</script>
@stop
@section('content')
<p>&nbsp;</p>
<div id="tabs">
<ul class="nav nav-tabs" id="policyholdertab" data-tabs="tabs">
 <li class="active"><a href="#policyholder" data-toggle="tab" data-url="/policyholder">Policyholder Details</a></li>
 <li><a href="#relationships" data-toggle="tab" data-url="/relationships">Relationships</a></li>
 <li><a href="#nonresidence" data-toggle="tab" data-url="/nonresidence">Periods of non-residence</a></li>
 </ul>
<div class="tab-content active" id="policyholdertabcontent">
  <div class="tab-pane fade active in" id="policyholder"></div>
  <div class="tab-pane" id="relationships"></div>
  <div class="tab-pane" id="nonresidence"></div>
</div>
@stop
</div>
