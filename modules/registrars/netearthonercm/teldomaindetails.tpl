<div>
	<h3>{$LANG.lcdrm_teldomaintitle}</h3>
	<div class="alert alert-info">
		{$LANG.lcdrm_teldetailsdesc}
	</div>

{if $publish_success}
	<div class="alert alert-success">
    	<p>{$publish_success}</p>
	</div>
{/if}
{if $publish_error}
	<div class="alert alert-danger">
    	<p>{$publish_error}</p>
	</div>
{/if}
	
	<div class="internalpadding">
		<div class="well">

			<p>{$LANG.lcdrm_privacystatus} <strong>{$whois_status}</strong></p>
			
			<div>
				<form method="post" action="clientarea.php?action=domaindetails">
					<input type="hidden" name="id" value="{$domainid}" />
					<input type="hidden" name="modop" value="custom" />
					<input type="hidden" name="a" value="TelDomainDetails" />
					<input type="radio" name="publishwhois" {if $publish eq "y"} checked {/if} value="n"> {$LANG.lcdrm_enableprivacy}
					<br />
					<input type="radio" name="publishwhois" {if $publish eq "n"} checked {/if} value="y"> {$LANG.lcdrm_disableprivacy}
					<br />
					<p><input type="submit" class="btn btn-primary info" value="{$LANG.lcdrm_updateprivacy}"></p>
				</form>
			</div>
			
			<p><a class="btn btn-primary info" href="https://www.managemy.tel/g3/login.action" target="_blank">{$LANG.lcdrm_telcontrolpanelbutton}</a></p>
			
		</div>
	</div>
</div>