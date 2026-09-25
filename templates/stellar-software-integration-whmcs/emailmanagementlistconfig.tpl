<style>
.dm-rc-email-page .table {
    width: 100%;
    max-width: 100%;
    margin-left: 0 !important;
    margin-right: 0 !important;
}
.dm-rc-email-page .table td,
.dm-rc-email-page .table th {
    vertical-align: top;
}
.dm-rc-email-page .btn {
    white-space: nowrap;
}
.dm-rc-email-page .label,
.dm-rc-email-page .badge {
    display: inline-block;
    line-height: 1.25;
}
.dm-rc-email-page input[type="text"],
.dm-rc-email-page input[type="email"],
.dm-rc-email-page input[type="password"],
.dm-rc-email-page select,
.dm-rc-email-page textarea {
    max-width: 100%;
}
@media (max-width: 767px) {
    .dm-rc-email-page {
        overflow-x: auto;
    }
    .dm-rc-email-page .table {
        min-width: 640px;
    }
}
</style>
<div class="dm-rc-email-page">
{literal}
	<script language="javascript" type="text/javascript">
		function cleanField(){if( document.cfg.replytomail.value!="")document.cfg.replytomail.value=""}
	</script>
{/literal}

{include file="$template/includes/alert.tpl" type="info" msg=$LANG.rcmail_configlistdesc}

<ul>
	<li>
		<strong>{$LANG.rcmail_listquestion1}</strong> = {$LANG.rcmail_listanswer1}
	</li>
	<li>
		<strong>{$LANG.rcmail_listquestion2}</strong> = {$LANG.rcmail_listanswer2}
	</li>
	<li>
		<strong>{$LANG.rcmail_listquestion3}</strong> = {$LANG.rcmail_listanswer3}
	</li>
	<li>
		<strong>{$LANG.rcmail_listquestion4}</strong> = {$LANG.rcmail_listanswer4}
	</li>
</ul>

<p>{$LANG.rcmail_listotherconfdesc1} <a href="supporttickets.php">Click here</a> {$LANG.rcmail_listotherconfdesc2}</p>
<br />
{if $configlistsuccess}
<br />
<div class="alert alert-success">
    <p>{$LANG.moduleactionsuccess}</p>
    <ul>
        {$configlistsuccess}
    </ul>
</div>
{/if}

{if $configlisterror}
<br />
<div class="alert alert-danger">
	<p>{$LANG.clientareaerrors}</p>
    <ul>
        {$configlisterror}
    </ul>
</div>
{/if}

<form method="post" name="cfg" action="emailmanagement.php?action=configmailinglist">
	<input type="hidden" name="domainid" value="{$domainid}"/>
	<input type="hidden" name="domain" value="{$domain}"/>
	<input type="hidden" name="configlist" value="true"/>
	<input type="hidden" name="listaddress" value="{$xmlmailinglist.mailingList.listname}@{$xmlmailinglist.mailingList.domainname}"/>
	<table class="table table-bordered table-hover">
		<tr>
			<td colspan="2">
				<h4>{$LANG.rcmail_configlisttitle} {$xmlmailinglist.mailingList.listname}@{$xmlmailinglist.mailingList.domainname}</h4>
			</td>
		</tr>
		<tr>
			<td>
				<strong>{$LANG.rcmail_listconfigis}</strong>
			</td>
			<td>
				<ul>
					<li>
						{if $xmlmailinglist.mailingList.subscriptionHeld eq false}{$LANG.rcmail_listtypeopen}{else}{$LANG.rcmail_listtypeclosed}{/if}
					</li>
					<li>
						{if $xmlmailinglist.mailingList.whoCanPost eq "ANYONE"}{$LANG.rcmail_anyonepost}{elseif $xmlmailinglist.mailingList.whoCanPost eq "ONLY_MEMBERS"}{$LANG.rcmail_membersonlypost}{elseif $xmlmailinglist.mailingList.whoCanPost eq "ONLY_MODERATORS"}{$LANG.rcmail_moderatorsonlypost}{/if}
					</li>
					<li>
						{if $xmlmailinglist.mailingList.messagesHeldForModeration eq false}{$LANG.rcmail_messagecontrolno}{else}{$LANG.rcmail_messagecontrolyes}{/if}
					</li>
					<li>
						{if $xmlmailinglist.mailingList.replyToAddress eq "SENDER"}{$LANG.rcmail_responsetosender}
						{elseif $xmlmailinglist.mailingList.replyToAddress eq "ENTIRE_LIST"}{$LANG.rcmail_responsetomembers}
						{else}{$LANG.rcmail_responsetoemail} {$xmlmailinglist.mailingList.replyToAddress}
						{/if}
					</li>
				</ul>
			</td>
		</tr>
		<tr>
			<td>
				<strong>{$LANG.rcmail_listquestion1}</strong>
			</td>
			<td>
				<input name="subscriptionheld" value="false" {if $xmlmailinglist.mailingList.subscriptionHeld eq "false" || empty($xmlmailinglist.mailingList.subscriptionHeld)}checked="checked"{/if} type="radio"> 
				{$LANG.rcmail_listanswer11}<br />
				<input name="subscriptionheld" value="true" {if $xmlmailinglist.mailingList.subscriptionHeld eq "true"}checked="checked"{/if} type="radio"> 
				{$LANG.rcmail_listanswer1}
			</td>
		</tr>
		<tr>
			<td>
				<strong>{$LANG.rcmail_listquestion2}</strong>
			</td>
			<td>
				<input name="whocanpost" value="anyone" {if $xmlmailinglist.mailingList.whoCanPost eq "ANYONE" || empty($xmlmailinglist.mailingList.whoCanPost)}checked="checked"{/if} type="radio"> 
				{$LANG.rcmail_listanswer22}<br />
				<input name="whocanpost" value="only_members" {if $xmlmailinglist.mailingList.whoCanPost eq "ONLY_MEMBERS"}checked="checked"{/if} type="radio"> 
				{$LANG.rcmail_listanswer222}<br/>
				<input name="whocanpost" value="only_moderators" {if $xmlmailinglist.mailingList.whoCanPost eq "ONLY_MODERATORS"}checked="checked"{/if} type="radio"> 
				{$LANG.rcmail_listanswer2}
			</td>
		</tr>
		<tr>
			<td>
				<strong>{$LANG.rcmail_listquestion3}</strong>
			</td>
			<td>
				<input name="moderation" value="false" {if $xmlmailinglist.mailingList.messagesHeldForModeration eq "false" || empty($xmlmailinglist.mailingList.messagesHeldForModeration)}checked="checked"{/if} type="radio"> 
				{$LANG.rcmail_listanswer333}<br />
				<input name="moderation" value="true" {if $xmlmailinglist.mailingList.messagesHeldForModeration eq "true"}checked="checked"{/if} type="radio"> 
				{$LANG.rcmail_listanswer33}
			</td>
		</tr>
		<tr>
			<td>
				<strong>{$LANG.rcmail_listquestion4}</strong>
			</td>
			<td>
				<input name="replyto" value="sender" {if $xmlmailinglist.mailingList.replyToAddress == "SENDER" || empty($xmlmailinglist.mailingList.replyToAddress)}checked="checked"{/if} type="radio" onclick="return cleanField();"> 
				{$LANG.rcmail_listanswer44} <br />
				<input name="replyto" value="entire_list" {if $xmlmailinglist.mailingList.replyToAddress == "ENTIRE_LIST"}checked="checked"{/if} type="radio" onclick="return cleanField();"> 
				{$LANG.rcmail_listanswer444} <br />
				{if $xmlmailinglist.mailingList.replyToAddress == "ENTIRE_LIST"}
				<input name="replyto" value="custom_address" type="radio"> 
				{$LANG.rcmail_listanswer4}:
				<input class="form-control" name="replytomail" value="" size="25"/>
				{elseif $xmlmailinglist.mailingList.replyToAddress == "SENDER"}
				<input name="replyto" value="custom_address" type="radio"> 
				{$LANG.rcmail_listanswer4}:
				<input class="form-control" name="replytomail" value="" size="25"/>
				{else}
				<input name="replyto" type="radio" value="custom_address" checked="checked"> 
				{$LANG.rcmail_listanswer4}:<br />
				<input class="form-control" name="replytomail" value="{$xmlmailinglist.mailingList.replyToAddress}" size="25"/>
				{/if}
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<p align="center"><input type="submit" value="Apply Changes" class="btn btn-success"/></p>
			</td>
		</tr>
	</table>
</form>
</div>
