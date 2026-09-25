<div>
	<h3>{$LANG.lcdrm_premiumdnsmanagetitle}</h3>
	<div class="alert alert-info">
		{$LANG.lcdrm_premiumdnsmanagedesc}
	</div>

	{if $dnsmatch != 1}
	<div class="alert alert-warning">
		<p>{$LANG.rcdns_domdnsconfigerror1} <strong>&laquo;{$LANG.rcdns_changednsbutton}&raquo;</strong> {$LANG.rcdns_domdnsconfigerror2} <a href="clientarea.php?action=domaindetails&id={$domainid}#tabNameservers">{$LANG.lcdrm_hereword}</a></p>
	</div>
	{/if}

	{if $nschangeerror}
	<br />
	<div class="alert alert-danger">
		<p>{$LANG.clientareaerrors}</p>
		<ul>
			{$nschangeerror}
		</ul>
	</div>
	{/if}
	
	{if $nschangesuccess}
	<br />
	<div class="alert alert-success">
		<p>{$LANG.moduleactionsuccess}</p>
		<ul>
			{$nschangesuccess}
		</ul>
	</div>
	{/if}

	<table class="table table-bordered table-hover">
		<tr>
			<td class="textcenter" colspan="2">
				{$LANG.rcdns_nsserversolution}
			</td>
		</tr>
		<tr>
			<td width="50%">
				<p class="label label-danger">
					{$LANG.currentnameservers}
				</p><br />
				{foreach key=num item=actualdns from=$configurednsservers.0}
				{$actualdns}<br />
				{/foreach}
			</td>
			<td width="50%">
				<p class="label label-success">
					{$LANG.recommendednameservers}
				</p><br />
				{foreach key=num item=defaultdns from=$requirednsservers.0}
				{$defaultdns}<br />
				{/foreach}
			</td>
		</tr>
		{if $dnsmatch != 1}
		<tr>
			<td colspan="2">
				<form method="post" action="clientarea.php?action=domaindetails">
					<input type="hidden" name="domainid" value="{if $domainid}{$domainid}{else}{$id}{/if}"/>
					<input type="hidden" name="a" value="ManagePremiumDns"/>
					<input type="hidden" name="modop" value="custom"/>
					{foreach key=num item=defaultdns from=$requirednsservers.0}
					<input type="hidden" name="ns[]" value="{$defaultdns}"/>
					{/foreach}
					<input type="hidden" name="changens" value="true"/>
					<p align="center"><input type="submit" value="{$LANG.rcdns_changednsbutton}" class="btn btn-success"/></p>
				</form>
			</td>
		</tr>
		{/if}
	</table>


	
</div>

<hr />

<div>
	<p class="text-center">
		<a class="button btn btn-default btn-lg" href="clientarea.php?action=domaindetails&id={$domainid}&modop=custom&a=ManagePremiumDns&service-name=premiumdns&lbsrvmanage=true" target="_blank">{$LANG.lcdrm_buttonpremiumdns}</a>
	</p>
</div>

<br />
<br />
<br />