@extends('layouts.master')

@section('header')
@if (Session::has('bond_insurer'))<h4>Bond&#58; {{{Session::get('bond_insurer')}}}&#32;&#45;&#45;&#45;&#32;{{{Session::get('bond_policy_number') }}} </h4>
@endif
<script>

$().ready(function() {

	sessionStorage.setItem('new_bond', {{{ $new_bond }}});

$('#bondtab a[href="#bond"], #bondtab a[href="#segments"], #bondtab a[href="#ownership"], #bondtab a[href="#increment"], #bondtab a[href="#encashment"], #bondtab a[href="#policyloan"], #bondtab a[href="#withdrawal"]').click(function (e) {


  if (sessionStorage.getItem('new_bond') == 1) {
  e.preventDefault()
    var self = $(this);
    var url = self.attr('data-url');
    var h = this.hash;
    var href = h.substring(1);
	$.get(url, function(data) {$('#'+href).html(data)});
  $(this).tab('show') }
})

// load first tab content
$('#bond').load($('.active a').attr("data-url"),function(result){
  $('.active a').tab('show');
});
   
});

</script>
@stop
@section('content')
<p>&nbsp;</p>
<div id="tabs">
<ul class="nav nav-tabs" id="bondtab" data-tabs="tabs">
 <li class="active"><a href="#bond" data-toggle="tab" data-url="/bond">Bond Details</a></li>
 <li><a href="#ownership" data-toggle="tab" data-url="/ownership">Ownership</a></li>
 <li><a href="#segments" data-toggle="tab" data-url="/segments">Segments</a></li>
 <li><a href="#encashment" data-toggle="tab" data-url="/encashment">Encashments</a></li>
 <li><a href="#increment" data-toggle="tab" data-url="/increment">Increments</a></li>
 <li><a href="#policyloan" data-toggle="tab" data-url="/policyloan">Policy Loans</a></li>
 <li><a href="#withdrawal" data-toggle="tab" data-url="/withdrawal">Withdrawals</a></li>
</ul>
<div class="tab-content active" id="bondtabcontent">
  <div class="tab-pane fade active in" id="bond"></div>
  <div class="tab-pane" id="ownership"></div>
  <div class="tab-pane" id="segments"></div>
  <div class="tab-pane" id="encashment"></div>
  <div class="tab-pane" id="increment"></div>
  <div class="tab-pane" id="policyloan"></div>
  <div class="tab-pane" id="withdrawal"></div>
</div>
@stop
</div>
