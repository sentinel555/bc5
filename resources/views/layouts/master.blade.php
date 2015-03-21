<!DOCTYPE html>
<html lang="en">
<head>
<title>BC5 - Investment Bond Calculator</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
  {!! Html::script('js/jquery.js') !!}
  {!! Html::script('js/jquery.maskedinput.js') !!}
  {!! Html::script('js/bootstrap.min.js') !!}
  {!! Html::script('js/bootstrap-datepicker.js') !!}
  {!! Html::script('js/bootbox.min.js') !!}
{!! Html::style('http://fonts.googleapis.com/css?family=Noto+Sans:400,700,400italic,700italic') !!}
{!! Html::style('css/bootstrap.min.css') !!}
{!! Html::style('css/bootstrap-theme.min.css') !!}
{!! Html::style('css/datepicker.css') !!}
{!! Html::style('css/general.css') !!}
<script>
$().ready(function() {
    $( "#dropdown-toggle" ).dropdown(); 
});
</script>
</head>
<body>
<div class="container-fluid">
<div class="dropdown" id="dropdown-nav">
 <ul class="nav nav-pills">
       <li class="dropdown">
        <a id="drop2" role="button" data-toggle="dropdown" href="#">Policyholders<b class="caret"></b></a>
        <ul id="menu2" class="dropdown-menu" role="menu" aria-labelledby="drop2">
		<li role="presentation"><a role="menuitem" tabindex="-1" href="/newpolicyholder">Add New Policyholder &#47; Beneficiary</a></li>
          <li role="presentation"><a role="menuitem" tabindex="-1" href="/policyholders">Manage Policyholder Details</a></li>
        </ul>
      </li>
 <li class="dropdown">
        <a id="drop3" role="button" data-toggle="dropdown" href="#">Policies<b class="caret"></b></a>
        <ul id="menu3" class="dropdown-menu" role="menu" aria-labelledby="drop3">
		<li role="presentation"><a role="menuitem" tabindex="-1" href="/newbond">New Bond</a></li>
          <li role="presentation"><a role="menuitem" tabindex="-1" href="/bonds">Manage Bonds</a></li>
        </ul>
      </li>
 <li class="dropdown">
        <a id="drop4" role="button" data-toggle="dropdown" href="#">Calculations<b class="caret"></b></a>
        <ul id="menu4" class="dropdown-menu" role="menu" aria-labelledby="drop4">
	<li role="presentation"><a role="menuitem" tabindex="-1" href="/newcalculation">New Calculation</a></li>
          <li role="presentation"><a role="menuitem" tabindex="-1" href="/calculations">Load Previous Calculation</a></li>
          <li role="presentation" class="divider"></li>
          <li role="presentation" class="disabled"><a role="menuitem" tabindex="-1" href="newencashment">New Encashment &#47; Withdrawal Scenario Analysis</a></li>
          <li role="presentation" class="disabled"><a role="menuitem" tabindex="-1" href="loadencashment">Load Encashment &#47; Withdrawal Scenario Analysis</a></li>
	        </ul>
      </li>
 <li class="dropdown">
        <a id="drop5" role="button" data-toggle="dropdown" href="#">Help<b class="caret"></b></a>
        <ul id="menu5" class="dropdown-menu" role="menu" aria-labelledby="drop5">
		<li role="presentation"><a role="menuitem" tabindex="-1" href="/help">Help Manual</a></li>
          <li role="presentation"><a role="menuitem" tabindex="-1" href="/about">About</a></li>
        </ul>
      </li>
<li class="dropdown">
        <a id="drop6" role="button" data-toggle="dropdown" href="#">Logout<b class="caret"></b></a>
        <ul id="menu5" class="dropdown-menu" role="menu" aria-labelledby="drop6">
		<li role="presentation"><a role="menuitem" tabindex="-1" href="/auth/logout">Logout</a></li>
        </ul>
      </li>

</ul>

</div>
<div class="row-fluid">
    <div class="col-xs-12 header"  id="header">
	<div class="subheader">
  @yield('header')
</div>
</div>
</div>
@yield('content')
</div>
</body>
</html>
