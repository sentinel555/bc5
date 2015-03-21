@section('nav')
<ul id="menu">
<li><a href="">Start</a>
	<ul>
		<li class="sub">{{ HTML::link('newcase', 'New Calculation') }}</li>
	<li class="sub">{{ HTML::link('load', 'Load Previous Calculation') }}</li>
		<li class="sub">{{ HTML::link('newenc', 'New Encashment &#47; Withdrawal Scenario Analysis') }}</li>
		<li class="sub">{{ HTML::link('loadenc', 'Load Encashment &#47; Withdrawal Scenario Analysis') }}</li>
		<li class="sub">{{ HTML::link('logout', 'Logout') }}</li>
	</ul>
</li>
<li><a href="">Policyholders</a>
	<ul>
		<li class="sub">{{ HTML::link('process_policyholders', 'Add New Policyholder &#47; Beneficiary') }}</li>
		<li class="sub">{{ HTML::link('policyholders', 'Manage Policyholder Details') }}</li>
	</ul>
</li>
<li><a href="">Policies</a>
	<ul>
		<li class="sub">{{ HTML::link('newbond', 'New Bond') }}</li>
		<li class="sub">{{ HTML::link('bonds', 'Manage Bonds') }}</li>
	</ul>
</li>
<li><a href="">Calculations</a>
	<ul>
		<li class="sub">{{ HTML::link('results', 'Calculation Results') }}</li>
		<li class="sub">{{ HTML::link('encashmentResults', 'Encashment Analysis Results') }}</li>
	</ul>
</li>
<li><a href="">Help</a>
	<ul>
		<li class="sub">{{ HTML::link('help', 'Help Manual') }}</li>
		<li class="sub">{{ HTML::link('about', 'About') }}</li>
	</ul>
</li>
</ul>
@stop
