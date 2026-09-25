{if $raa_success}
	<div class="alert alert-success">
		<p>{$raa_success}</p>
	</div>
{/if}

{if $raa_error}
	<div class="alert alert-error">
		{$raa_error}
	</div>
{/if}

<div class="alert alert-warning">
	<p class="bold">{$lcdrm_raaverifytitle}</p>
	<p>{$lcdrm_raaverifybefore1} &quot;<strong>{$reg_c_mailaddr}</strong>&quot; {$lcdrm_raaverifybefore2} <strong>{$raa_endtime}</strong>{$lcdrm_raaverifybefore3}</p>
	{if $noraabutton}
	{$lcdrm_raaverifybefore4}<br />
	<form method="post" action="clientarea.php?action=domaindetails">
		<input type="hidden" name="domain" value="{$isdomain}"/>
		<input type="hidden" name="id" value="{$isdomainid}"/>
		<input type="hidden" name="raa" value="resend"/>
		<p><input class="btn btn-info" type="submit" value="{$lcdrm_raasendbutton}"/></p>
	</form>	
	{/if}
</div>
