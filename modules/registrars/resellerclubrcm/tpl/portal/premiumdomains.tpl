{if !$iscartorigin} 
	
	{*-- from domainchecker --*}

	{if $premiumdomainfound}
	<br />
	<p align="center" class="textgreen" style="font-size:18px;">{$buyavailable1} <strong>{$domain}</strong> {$buyavailable2}</p>
	<br />
	<form method="post" action="{$systemsslurl}cart.php?a=add&domain=transfer">
	<table width="100%" border="0" cellpadding="10" cellspacing="0" class="data">
		<tr>
			<th>{$LANG.domainname}</th>
			<th>{$LANG.lcdrm_premiumprice}</th>
			<th>{$LANG.lcdrm_addtocart}</th>
		</tr>
		{foreach from=$premiumdomainfound key=num item=result}
		<tr>
			<td>{$num}</td>
			<td>{$result}</td>
			<td>
				<input name="domains[]" value="{$num}" type="hidden">
				<input name="domainsregperiod[{$num}]" value="1" type="hidden">
				<input name="premiumft" value="{$num}" type="hidden">
				<input name="premiumprice" value="{$result}" type="hidden">
				<input type="submit" value="{$LANG.ordernowbutton} >>" />
			</td>
		</tr>
		{/foreach}
	</table>
	</form>
	{/if}
	
	{if $premiumavailable}
	<br />
	<p align="center" class="textgreen" style="font-size:18px;">{$buyalternative1}</p>
	<table width="100%" border="0" cellpadding="10" cellspacing="0" class="data">
		<tr>
			<th>{$LANG.domainname}</th>
			<th>{$LANG.lcdrm_premiumprice}</th>
			<th>{$LANG.lcdrm_addtocart}</th>
		</tr>
		{foreach from=$premiumavailable key=num item=result}
		<tr>
			<td>{$num}</td>
			<td>{$result}</td>
			<td>
				<form method="post" action="{$systemsslurl}cart.php?a=add&domain=transfer">
				<input name="domains[]" value="{$num}" type="hidden">
				<input name="domainsregperiod[{$num}]" value="1" type="hidden">
				<input name="premiumft" value="{$num}" type="hidden">
				<input name="premiumprice" value="{$result}" type="hidden">
				<input type="submit" value="{$LANG.ordernowbutton} >>" />
				</form>
			</td>
		</tr>
		{/foreach}
	</table>
	{/if}

{else} 
	
	{*-- from cart --*}
	
	{if $carttpl eq 'cloud_silder' || $carttpl eq 'premium_comparison' || $carttpl eq 'pure_comparison' || $carttpl eq 'standard_cart'}
		<div class="sub-heading">
			<span>{$buyalternative1}</span>
		</div>
	{elseif $carttpl eq 'modern' || $carttpl eq 'slider'}
		<div class="domainsuggestions">{$buyalternative1}</div>
	{elseif $carttpl eq 'boxes'}
		<h2>{$buyalternative1}</h2>
	{elseif $carttpl eq 'cart'}
		<h1>{$buyalternative1}</h1>
	{elseif $carttpl eq 'comparison'}
		<div class="center80">
		<div class="domainsuggestions">{$buyalternative1}</div>
	{else}
		<h2>{$buyalternative1}</h2>
	{/if}
	<table class="{if $carttpl eq 'boxes'}styled{elseif $carttpl eq 'cart'}domains{elseif $carttpl eq 'verticalsteps'}styled textcenter{elseif $carttpl eq 'web20cart'}textcenter{elseif $carttpl eq 'modern' || $carttpl eq 'slider' || $carttpl eq 'ajaxcart'}domainsuggestions{elseif $carttpl eq 'comparison'}centertext{else}table{/if}">
		<tr class="{if $carttpl eq 'boxes'}carttableheading{/if}">
			<th>{$LANG.domainname}</th>
			<th>{$LANG.lcdrm_premiumprice}</th>
			<th>{$LANG.lcdrm_addtocart}</th>
		</tr>
		{foreach from=$premiumavailable key=num item=result}
		<tr class="{if $carttpl eq 'boxes'}text-center{elseif $carttpl eq 'cart'}carttablerow{/if}">
			<td>{$num}</td>
			<td>{$result}</td>
			<td>
				<form method="post" action="{$systemsslurl}cart.php?a=add&domain=transfer">
				<input name="domains[]" value="{$num}" type="hidden">
				<input name="domainsregperiod[{$num}]" value="1" type="hidden">
				<input name="premiumft" value="{$num}" type="hidden">
				<input name="premiumprice" value="{$result}" type="hidden">
				<input type="submit" value="{$LANG.ordernowbutton} &raquo;" class="btn btn-danger" />
				</form>
			</td>
		</tr>
		{/foreach}
	</table>
	{if $carttpl eq 'comparison'}
		</div>
	{/if}	
	
{/if}