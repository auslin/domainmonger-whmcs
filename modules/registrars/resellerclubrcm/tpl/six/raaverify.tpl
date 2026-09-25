{if $raa_success}
	<div class="alert alert-success">
		<p>{$LANG.moduleactionsuccess}</p>
		<ul>
			<p>{$raa_success}</p>
		</ul>
	</div>
{/if}

{if $raa_error}
	<div class="alert alert-danger">
		<p>{$LANG.moduleactionfailed}</p>
		<ul>
			<p>{$raa_error}</p>
		</ul>
	</div>
{/if}

<div class="alert alert-warning">
	<p><strong>{$lcdrm_raaverifytitle}</strong></p><br />
	<ul>
		<p>{$lcdrm_raaverifybefore1} <strong>{$reg_c_mailaddr}</strong>. {$lcdrm_raaverifybefore2} <strong>{$raa_endtime}</strong>{$lcdrm_raaverifybefore3}</p>
		{if $noraabutton}
		<form method="post" action="clientarea.php?action=domaindetails">
			<input type="hidden" name="domain" value="{$isdomain}"/>
			<input type="hidden" name="id" value="{$isdomainid}"/>
			<input type="hidden" name="raa" value="resend"/>
			<p><input class="btn btn-success" type="submit" value="{$lcdrm_raasendbutton}"/></p>
		</form>	
		{/if}
	</ul>
</div>
