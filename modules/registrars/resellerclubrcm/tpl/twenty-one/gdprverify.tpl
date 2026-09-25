{if $gdpr_resent_success}
	<div class="alert alert-success">
		<p>{$LANG.moduleactionsuccess}</p>
		<ul>
			<p>{$gdpr_resent_success}</p>
		</ul>
	</div>
{/if}

{if $gdpr_resent_error}
	<div class="alert alert-danger">
		<p>{$LANG.moduleactionfailed}</p>
		<ul>
			<p>{$gdpr_resent_error}</p>
		</ul>
	</div>
{/if}

<div class="alert alert-warning">
	<p><strong>{$lcdrm_gdprauthpendingtitle}</strong></p><br />
	<ul>
		<p>{$lcdrm_gdprauthpendingdesc}</p>
		{if $noraabutton}
		<form method="post" action="clientarea.php?action=domaindetails">
			<input type="hidden" name="domain" value="{$isdomain}"/>
			<input type="hidden" name="id" value="{$isdomainid}"/>
			<input type="hidden" name="gdprauth" value="resend"/>
			<p><input class="btn btn-success" type="submit" value="{$lcdrm_buttongdprsendemail}"/></p>
		</form>	
		{/if}
	</ul>
</div>