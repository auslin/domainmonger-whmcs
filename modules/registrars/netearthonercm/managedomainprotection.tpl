{if $whois_success}
	<div class="alert alert-success">
    	<p>{$whois_success}</p>
	</div>
{/if}
{if $whois_error}
	<div class="alert alert-danger">
    	<p>{$whois_error}</p>
	</div>
{/if}

<div>
	<h3>{$LANG.lcdrm_whoismanagetitle}</h3>
	<div class="alert alert-info">
		{$LANG.lcdrm_whoismanagedesc}
	</div>
	<br />
	<h2 class="text-center">{$LANG.lcdrm_whoisstatustitle} <span class="label label-{if $whois_status eq $LANG.lcdrm_labelwhoisenabled}success{else}danger{/if}">{$whois_status}</span></h2>
	<br />
	<br />
	<form method="post" action="clientarea.php?action=domaindetails">
		<input type="hidden" name="id" value="{$domainid}" />
		<input type="hidden" name="modop" value="custom" />
		<input type="hidden" name="a" value="ManageWhoisProtection" />
		<input type="hidden" name="whoisstatus" value="{$whois_status}" />
		<p class="text-center">
			<input class="btn btn-lg btn-{if $whois_status eq $LANG.lcdrm_labelwhoisenabled}danger{else}success{/if}" value="{$whois_button}" type="submit">
		</p>
	</form>
</div>

{if $protectionplus_button != "on"}
<hr />

<div>
	<p class="text-center">
		<a class="button btn btn-default btn-lg" href="clientarea.php?action=domaindetails&id={$domainid}&modop=custom&a=ManageWhoisProtection&service-name=domainprotectionservice&lbsrvmanage=true" target="_blank">{$LANG.lcdrm_buttonmanagedomainprotection}</a>
	</p>
</div>
{/if}

<br />
<br />
<br />