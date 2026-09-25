{if !$iscartorigin} 
	
	{*-- from domainchecker --*}
	
	{if $suggestdomainresults}
	
	<br />
	<p align="center" class="textgreen" style="font-size:18px;">{$suggesttitle}</p>
	<br />
	<form method="post" action="{$systemsslurl}cart.php?a=add&domain=register">
	<table class="clientareatable" cellspacing="1">
		<tr class="clientareatableheading">
			<td></td>
			<td>{$LANG.domainname}</td>
			<td>{$LANG.domainstatus}</td>
			<td>{$LANG.domainmoreinfo}</td>
		</tr>
		{foreach from=$suggestdomainresults key=num item=result}
		<tr class="clientareatableactive">
			<td><input type="checkbox" name="domains[]" value="{$result.domain}" /><input type="hidden" name="domainsregperiod[{$result.domain}]" value="{$result.period}" /></td>
			<td>{$result.domain}</td>
			<td class="textgreen">{$LANG.domainavailable}</td>
			<td><select name="domainsregperiod[{$result.domain}]">{foreach key=period item=regoption from=$result.regoptions}<option value="{$period}">{$period} {$LANG.orderyears} @ {$regoption.register}</option>{/foreach}</select></td>
		</tr>
	{/foreach}
	</table>
	<p align="center"><input type="submit" value="{$LANG.ordernowbutton} &raquo;" class="buttongo" /></p>
	</form>
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
		
		<form method="post" action="{$smarty.server.PHP_SELF}?a=add&domain={$domain}">
		
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
