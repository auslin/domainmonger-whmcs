{if !$iscartorigin} 
	
	{*-- from domainchecker --*}
	
	{if $suggestdomainresults}
	
		<div id="stepResults">
			<div class="row">
				<div class="col-md-8 col-md-offset-2">
					<div id="suggestionSearchResults" class="domainresults">
						<div>
							{$LANG.domainssuggestions} <span>{$suggesttitle}</span>
						</div>
						<form method="post" action="cart.php?a=add&domain=register">
							<table id="suggestionResults" class="table table-curved table-hover">
							{foreach from=$suggestdomainresults key=num item=result}
								<tr>
									<td>
										<input type="checkbox" name="domains[]" value="{$result.domain}" />
										<input type="hidden" name="domainsregperiod[{$result.domain}]" value="{$result.period}" />
									</td>
									<td class="text-center">{$result.domain}</td>
									<td class="text-right">
										<select class="form-control" name="domainsregperiod[{$result.domain}]">
											{foreach key=period item=regoption from=$result.regoptions}
												<option value="{$period}">{$period} {$LANG.orderyears} @ {$regoption.register}</option>
											{/foreach}
										</select>
									</td>
								</tr>
							{/foreach}
							</table>
							<p align="center"><input type="submit" value="{$LANG.ordernowbutton} &raquo;" class="btn btn-success" /></p>
						</form>
					</div>
				</div>
			</div>
		</div>

	{/if}

{else} 
	
	{*-- from cart --*}
	
	{if $suggestdomainresults}
	
		{*-- Top --*}
		
		{if $is_whmcs_version < '7.0.0'}
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
		{else}
			<div class="panel-heading" style="border-bottom: 2px solid #62cb31;">
				{$suggesttitle}
			</div>
		{/if}
		
		{*-- Content --*}
		
		<form method="post" action="cart.php?a=add&domain={$domain}">
		
		<table class="{if $carttpl eq 'boxes'}styled{elseif $carttpl eq 'cart'}domains{elseif $carttpl eq 'verticalsteps'}styled textcenter{elseif $carttpl eq 'web20cart'}textcenter{elseif $carttpl eq 'modern' || $carttpl eq 'slider' || $carttpl eq 'ajaxcart'}domainsuggestions{elseif $carttpl eq 'comparison'}centertext{else}table{/if}">
			<tr class="{if $carttpl eq 'boxes'}carttableheading{/if}">
				<th>{$LANG.domainname}</th>
				<th>{$LANG.domainstatus}</th>
				<th>{$LANG.domainmoreinfo}</th>
			</tr>
			{foreach key=num item=result from=$suggestdomainresults}
				<tr class="{if $carttpl eq 'boxes'}clientareatableactive{elseif $carttpl eq 'cart'}carttablerow{/if}">
					<td>{$result.domain}</td><td class="{if $is_whmcs_version < '7.0.0'}textgreen{else}domain-available domain-checker-available{/if}"><input type="checkbox" name="domains[]" value="{$result.domain}" /> {$LANG.domainavailable}</td>
					<td><select class="form-control" name="domainsregperiod[{$result.domain}]">{foreach key=period item=regoption from=$result.regoptions}<option value="{$period}">{$period} {$LANG.orderyears} @ {$regoption.register}</option>{/foreach}</select></td>
				</tr>
			{/foreach}
		</table>

		{*-- Bottom --*}
		
		{if $carttpl eq 'comparison'}
			</div>
		{/if}
		
		<br />
		<p align="center"><input type="submit" value="{$LANG.addtocart}" class="btn btn-success" /></p>
		
		</form>
	
	{/if}

{/if}
