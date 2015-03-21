<!DOCTYPE html>
<html lang="en">
<head>
<title>BC5 - Investment Bond Calculator</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
  {{ HTML::script('js/jquery.js')}}
  {{ HTML::script('js/jquery-ui.js')}}
  {{ HTML::script('js/jquery.maskedinput.js')}}
  {{ HTML::script('js/bootstrap.min.js')}}
  {{ HTML::script('js/bootstrap-datepicker.js')}}
{{ HTML::style('css/jquery-ui.min.css')}}
{{ HTML::style('css/bootstrap.min.css')}}
{{ HTML::style('css/bootstrap-theme.min.css')}}
{{ HTML::style('css/datepicker.css')}}
{{ HTML::style('css/general.css')}}
<script>
$().ready(function() {
    $( "#dropdown-toggle" ).dropdown(); 
});
</script>
</head>
<body>
<div class="dropdown" id="dropdown-nav">
 <ul class="nav nav-pills">
 <li class="dropdown">
        <a id="drop1" role="button" data-toggle="dropdown" href="#">Start<b class="caret"></b></a>
        <ul id="menu1" class="dropdown-menu" role="menu" aria-labelledby="drop1">
		<li role="presentation"><a role="menuitem" tabindex="-1" href="newcase">New Calculation</a></li>
          <li role="presentation"><a role="menuitem" tabindex="-1" href="load">Load Previous Calculation</a></li>
          <li role="presentation" class="divider"></li>
          <li role="presentation"><a role="menuitem" tabindex="-1" href="newenc">New Encashment &#47; Withdrawal Scenario Analysis</a></li>
          <li role="presentation"><a role="menuitem" tabindex="-1" href="loadenc">Load Encashment &#47; Withdrawal Scenario Analysis</a></li>
          <li role="presentation" class="divider"></li>
          <li role="presentation"><a role="menuitem" tabindex="-1" href="logout">Logout</a></li>
        </ul>
      </li>
       <li class="dropdown">
        <a id="drop2" role="button" data-toggle="dropdown" href="#">Policyholders<b class="caret"></b></a>
        <ul id="menu2" class="dropdown-menu" role="menu" aria-labelledby="drop2">
		<li role="presentation"><a role="menuitem" tabindex="-1" href="newpolicyholder">Add New Policyholder &#47; Beneficiary</a></li>
          <li role="presentation"><a role="menuitem" tabindex="-1" href="policyholders">Manage Policyholder Details</a></li>
        </ul>
      </li>
 <li class="dropdown">
        <a id="drop3" role="button" data-toggle="dropdown" href="#">Policies<b class="caret"></b></a>
        <ul id="menu3" class="dropdown-menu" role="menu" aria-labelledby="drop3">
		<li role="presentation"><a role="menuitem" tabindex="-1" href="newbond">New Bond</a></li>
          <li role="presentation"><a role="menuitem" tabindex="-1" href="bonds">Manage Bonds</a></li>
        </ul>
      </li>
 <li class="dropdown">
        <a id="drop4" role="button" data-toggle="dropdown" href="#">Calculations<b class="caret"></b></a>
        <ul id="menu4" class="dropdown-menu" role="menu" aria-labelledby="drop4">
		<li role="presentation"><a role="menuitem" tabindex="-1" href="results">Calculation Results</a></li>
          <li role="presentation"><a role="menuitem" tabindex="-1" href="encashmentResults">Encashment Analysis Results</a></li>
        </ul>
      </li>
 <li class="dropdown">
        <a id="drop5" role="button" data-toggle="dropdown" href="#">Help<b class="caret"></b></a>
        <ul id="menu5" class="dropdown-menu" role="menu" aria-labelledby="drop5">
		<li role="presentation"><a role="menuitem" tabindex="-1" href="help">Help Manual</a></li>
          <li role="presentation"><a role="menuitem" tabindex="-1" href="about">About</a></li>
        </ul>
      </li>
</ul>
</div>
<div class="ui-widget-header ui-corner-all">
@yield('header')
</div>
@yield('content')
</body>
</html>
