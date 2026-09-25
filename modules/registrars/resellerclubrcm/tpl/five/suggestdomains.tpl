{if !$iscartorigin} 
	
	{*-- from domainchecker --*}
	
	{if $suggestdomainresults}
	
	<br />
	<p class="fontsize3 domcheckersuccess textcenter">{$suggesttitle}</p>
	<br />
	
	<div class="center80">
	<form method="post" action="cart.php?a=add&domain=register">
	
	<table class="table table-striped table-framed">
		<thead>
			<tr>
				<th></th>
				<th>{$LANG.domainname}</th>
				<th class="textcenter">{$LANG.domainstatus}</th>
				<th class="textcenter">{$LANG.domainmoreinfo}</th>
			</tr>
		</thead>
		<tbody>
	{foreach from=$suggestdomainresults key=num item=result}
			<tr>
				<td class="textcenter"><input type="checkbox" name="domains[]" value="{$result.domain}" /><input type="hidden" name="domainsregperiod[{$result.domain}]" value="{$result.period}" /></td>
				<td>{$result.domain}</td>
				<td class="textcenter domcheckersuccess">{$LANG.domainavailable}</td>
				<td class="textcenter"><select name="domainsregperiod[{$result.domain}]">{foreach key=period item=regoption from=$result.regoptions}<option value="{$period}">{$period} {$LANG.orderyears} @ {$regoption.register}</option>{/foreach}</select></td>
			</tr>
		</tbody>
	{/foreach}
	</table>
	
	<p align="center"><input type="submit" value="{$LANG.ordernowbutton} &raquo;" class="btn btn-danger" /></p>
	
	</form>
	
	</div>
	
	{/if}

{else} 
	
	{*-- from cart --*}
	
	{if $suggestdomainresults}
	
		{if $carttpl eq 'cloud_silder' || $carttpl eq 'premium_comparison' || $carttpl eq 'pure_comparison' || $carttpl eq 'standard_cart'}
			<div class="sub-heading">
				<span>{$suggesttitle}</span>
			</div>
		{elseif $carttpl eq 'modern' || $carttpl eq 'slider'}
			<div class="domainsuggestions">{$suggesttitle}</div>
		{elseif $carttpl eq 'boxes'}
			<h2>{$suggesttitle}</h2>
		{elseif $carttpl eq 'cart'}
			<h1>{$suggesttitle}</h1>
		{elseif $carttpl eq 'comparison'}
			<div class="center80">
			<div class="domainsuggestions">{$suggesttitle}</div>
		{else}
			<h2>{$suggesttitle}</h2>
		{/if}
		
		<form method="post" action="cart.php?a=add&domain={$domain}">
		
		<table class="{if $carttpl eq 'boxes'}styled{elseif $carttpl eq 'cart'}domains{elseif $carttpl eq 'verticalsteps'}styled textcenter{elseif $carttpl eq 'web20cart'}textcenter{elseif $carttpl eq 'modern' || $carttpl eq 'slider' || $carttpl eq 'ajaxcart'}domainsuggestions{elseif $carttpl eq 'comparison'}centertext{else}table{/if}">
			<tr class="{if $carttpl eq 'boxes'}carttableheading{/if}">
				<th>{$LANG.domainname}</th>
				<th>{$LANG.domainstatus}</th>
				<th>{$LANG.domainmoreinfo}</th>
			</tr>
			{foreach key=num item=result from=$suggestdomainresults}
				<tr class="{if $carttpl eq 'boxes'}clientareatableactive{elseif $carttpl eq 'cart'}carttablerow{/if}">
					<td>{$result.domain}</td><td class="textgreen"><input type="checkbox" name="domains[]" value="{$result.domain}" /> {$LANG.domainavailable}</td>
					<td><select class="form-control" name="domainsregperiod[{$result.domain}]">{foreach key=period item=regoption from=$result.regoptions}<option value="{$period}">{$period} {$LANG.orderyears} @ {$regoption.register}</option>{/foreach}</select></td>
				</tr>
			{/foreach}
		</table>
		{if $carttpl eq 'comparison'}
			</div>
		{/if}	
		
		<br />
		<p align="center"><input type="submit" value="{$LANG.addtocart}" class="btn btn-success" /></p>
		
		</form>
	
	{/if}

{/if}
