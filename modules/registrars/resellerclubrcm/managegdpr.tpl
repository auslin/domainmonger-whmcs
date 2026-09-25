{if $gdpr_success}
	<div class="alert alert-success">
    	<p>{$gdpr_success}</p>
	</div>
{/if}
{if $gdpr_error}
	<div class="alert alert-danger">
    	<p>{$gdpr_error}</p>
	</div>
{/if}

{if $gdprpending}{include file="$gdprverify"}{/if}

<div>
	<h3>{$LANG.lcdrm_gdprmanagetitle}</h3>
	<div class="alert alert-info">
		{$LANG.lcdrm_gdprmanagedesc}
	</div>
	<br />
	<h2 class="text-center">{$LANG.lcdrm_gdprstatustitle} <span class="label label-{if $gdpr_status eq $LANG.lcdrm_labelgdprenabled}success{else}danger{/if}">{$gdpr_status}</span></h2>
	<br />
	<br />
	<form method="post" action="clientarea.php?action=domaindetails">
		<input type="hidden" name="id" value="{$domainid}" />
		<input type="hidden" name="modop" value="custom" />
		<input type="hidden" name="a" value="ManageGdpr" />
		<input type="hidden" name="gdprstatus" value="{$gdpr_status}" />
		<p class="text-center">
			<input class="btn btn-lg btn-{if $gdpr_status eq $LANG.lcdrm_labelgdprenabled || $gdpr_status eq $LANG.lcdrm_labelgdprpending}danger{else}success{/if}" value="{$gdpr_button}" type="submit">
		</p>
	</form>
</div>

{if $gdprwhoisconflict == 1}
<div class="alert alert-info">
	<p><strong>{$LANG.lcdrm_noteword}</strong> {$LANG.lcdrm_gdprprotectionconflict} &laquo;{$LANG.lcdrm_buttonwhoismanage}&raquo;</p>
</div>
{/if}

<br />
<br />
<br />