{if $xxxsuccess}
	<div class="alert alert-success">
    	<p>{$LANG.changessavedsuccessfully}</p>
	</div>
{/if}
{if $xxxerror}
	<div class="alert alert-danger">
    	<p>{$xxxerror}</p>
	</div>
{/if}

<div>
	<h3>{$LANG.lcdrm_membershipidtitle}</h3>
	<div class="alert alert-info">
		{$LANG.lcdrm_membershipiddesc}
	</div>

	<div class="internalpadding">
		<div class="well">
			<div>
				<form method="post" action="{$smarty.server.REQUEST_URI}">
				{$LANG.lcdrm_membershipidtitle}:
				<input type="text" name="xxxtokenid" value="{$xxxtokenid}" />
				<input type="hidden" name="id" value="{$domainid}" />
				<input type="hidden" name="modop" value="custom" />
				<input type="hidden" name="a" value="MembershipTokenId" />
				<input class="btn btn-primary info" type="submit" name="submit" value="{$LANG.clientareasavechanges}" />
				</form>
			</div>
		</div>
	</div>	
</div>