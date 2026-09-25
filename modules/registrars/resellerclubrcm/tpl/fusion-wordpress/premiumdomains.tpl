{if !$iscartorigin} 

	{*-- from domainchecker --*}
	
	{if $premiumdomainfound}
		<div class="domain-checker-result-headline">
			<p class="domain-checker-available">
				{$buyavailable1} <strong>{$domain}</strong> {$buyavailable2}
			</p>
		</div>
		<div id="stepResults">
			<div class="row">
				<div class="col-md-8 col-md-offset-2">
					<form method="post" action="cart.php?a=add&domain=transfer">
					<div id="suggestionSearchResults" class="domainresults">
						<div>
							{$LANG.domainssearchresults}
						</div>
						<table id="suggestionResults" class="table table-curved table-hover">
							{foreach from=$premiumdomainfound key=num item=result}
							<tr>
								<td><strong>{$num}</strong></td>
								<td class="text-center">{$result}</td>
								<td class="text-right">
									<input name="domains[]" value="{$num}" type="hidden">
									<input name="domainsregperiod[{$num}]" value="1" type="hidden">
									<input name="premiumft" value="{$num}" type="hidden">
									<input name="premiumprice" value="{$result}" type="hidden">
									<input type="submit" value="{$LANG.ordernowbutton} &raquo;" class="btn btn-danger" />
								</td>
							</tr>
							{/foreach}
						</table>
					</div>
					</form>
				</div>
			</div>
		</div>
	{/if}
	
	{if $premiumavailable}
		<div id="stepResults">
			<div class="row">
				<div class="col-md-8 col-md-offset-2">
					<div id="suggestionSearchResults" class="domainresults">
						<div>
							Premium <span>{$buyalternative1}</span>
						</div>
						<table id="suggestionResults" class="table table-curved table-hover">
							{foreach from=$premiumavailable key=num item=result}
							<tr>
								<td><strong>{$num}</strong></td>
								<td class="text-center">{$result}</td>
								<td class="text-right">
									<form method="post" action="cart.php?a=add&domain=transfer">
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
					</div>
				</div>
			</div>
		</div>
	{/if}

{else} 
	
	{*-- from cart --*}

	{*-- Top --*}
		
	{if $is_whmcs_version < '7.0.0'}
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
	{else}
		<div class="panel-heading" style="border-bottom: 2px solid #62cb31;">
			{$buyalternative1}
		</div>	
	{/if}
	
	{*-- Content --*}
	
	<table class="{if $carttpl eq 'boxes'}styled{elseif $carttpl eq 'cart'}domains{elseif $carttpl eq 'verticalsteps'}styled textcenter{elseif $carttpl eq 'web20cart'}textcenter{elseif $carttpl eq 'modern' || $carttpl eq 'slider' || $carttpl eq 'ajaxcart'}domainsuggestions{elseif $carttpl eq 'comparison'}centertext{else}table{/if}">
		<tr class="{if $carttpl eq 'boxes'}carttableheading{/if}">
			<th>{$LANG.domainname}</th>
			<th>{$LANG.lcdrm_premiumprice}</th>
			<th>{$LANG.lcdrm_addtocart}</th>
		</tr>
		{foreach from=$premiumavailable key=num item=result}
		<tr class="{if $carttpl eq 'boxes'}text-center{elseif $carttpl eq 'cart'}carttablerow{/if}{if $nummatch eq $num} {$premiumdomainmatch}{/if}">
			<td>{if $nummatch eq $num}<strong>{$num}</strong>{else}{$num}{/if}</td>
			<td>{if $nummatch eq $num}<strong>{$result}</strong>{else}{$result}{/if}</td>
			<td>
				<form method="post" action="cart.php?a=add&domain=transfer">
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
	
	{*-- Bottom --*}
	
	{if $carttpl eq 'comparison'}
		</div>
	{/if}

{/if}