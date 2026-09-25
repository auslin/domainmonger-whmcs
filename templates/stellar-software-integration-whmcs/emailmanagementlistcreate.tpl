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
		function cleanField(){if(document.cfg.replytomail.value!="")document.cfg.replytomail.value=""}
	</script>
{/literal}

{include file="$template/includes/alert.tpl" type="info" msg=$LANG.rcmail_createnewlistdesc}

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
{if $createlistsuccess}
<br />
<div class="alert alert-success">
	<p>{$LANG.moduleactionsuccess}</p>
    <ul>
        {$createlistsuccess}
    </ul>
</div>

<table class="table table-bordered table-hover">
	<tr>
		<td>
			<strong>Mailing List Email</strong>			
		</td>
		<td colspan="2">
			{$xmlmailinglist.mailingList.listname}@{$xmlmailinglist.mailingList.domainname}			
		</td>
	</tr>
	<tr>
		<td>
			<strong>{$LANG.rcmail_moderatorsemailtitle}</strong>			
		</td>
		<td colspan="2">
			{$moderators}			
		</td>
	</tr>
	<tr>
		<td>
			<strong>{$LANG.rcmail_listmailtype}</strong>			
		</td>
		<td colspan="2">
			{if $xmlmailinglist.mailingList.subscriptionHeld eq false}{$LANG.rcmail_listtypeopen}{else}{$LANG.rcmail_listtypeclosed}{/if}
		</td>
	</tr>
	<tr>
		<td>
			<strong>{$LANG.rcmail_listmailsendtitle}</strong>			
		</td>
		<td colspan="2">
			{if $xmlmailinglist.mailingList.whoCanPost eq "ANYONE"}{$LANG.rcmail_anyonepost}
			{elseif $xmlmailinglist.mailingList.whoCanPost eq "ONLY_MEMBERS"}{$LANG.rcmail_membersonlypost}
			{elseif $xmlmailinglist.mailingList.whoCanPost eq "ONLY_MODERATORS"}{$LANG.rcmail_moderatorsonlypost}{/if}
		</td>
	</tr>
	<tr>
		<td>
			<strong>List Controls</strong>
		</td>
		<td colspan="2">
			{if $xmlmailinglist.mailingList.messagesHeldForModeration eq false}{$LANG.rcmail_messagecontrolno}{else}{$LANG.rcmail_messagecontrolyes}{/if}
		</td>
	</tr>
	<tr>
		<td>
			<strong>{$LANG.rcmail_listanswertitle}</strong>			
		</td>
		<td colspan="2">
			{if $xmlmailinglist.mailingList.replyToAddress eq "SENDER"}{$LANG.rcmail_responsetosender}
			{elseif $xmlmailinglist.mailingList.replyToAddress eq "ENTIRE_LIST"}{$LANG.rcmail_responsetomembers}
			{else}{$LANG.rcmail_responsetoemail} {$xmlmailinglist.mailingList.replyToAddress}{/if}
		</td>
	</tr>
	<tr>
		<td>
			<strong>{$LANG.rcmail_subscriptionmailaddress}</strong>
		</td>
		<td colspan="2">
			{$xmlmailinglist.mailingList.listname}-subscribe@{$domain}
		</td>
	</tr>
	<tr>
		<td>
			<strong>{$LANG.rcmail_unsubscriptionmailaddress}</strong>
		</td>
		<td colspan="2">
			{$xmlmailinglist.mailingList.listname}-unsubscribe@{$domain}
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>
			<form method="post" action="emailmanagement.php?action=configmailinglist">
				<input type="hidden" name="domainid" value="{$domainid}"/>
				<input type="hidden" name="domain" value="{$domain}"/>
				<input type="hidden" name="listaddress" value="{$xmlmailinglist.mailingList.listname}@{$xmlmailinglist.mailingList.domainname}"/>
				<input type="submit" value="Configure List" class="btn btn-primary"/>
			</form>
		</td>
	    <td>
			<form method="post" action="emailmanagement.php?action=managelist">
				<input type="hidden" name="domainid" value="{$domainid}"/>
				<input type="hidden" name="domain" value="{$domain}"/>
				<input type="hidden" name="listaddress" value="{$xmlmailinglist.mailingList.listname}@{$xmlmailinglist.mailingList.domainname}"/>
				<input type="submit" value="Manage Members / Moderators" class="btn btn-primary"/>
			</form>
		</td>
	</tr>
</table>
<br/>
{/if}

{if $createlisterror}
<br />
<div class="alert alert-danger">
    <p>{$LANG.clientareaerrors}</p>
    <ul>
        {$createlisterror}
    </ul>
</div>
{/if}

<form method="post" name="cfg" action="emailmanagement.php?action=createmaillist">
	<input type="hidden" name="domainid" value="{$domainid}"/>
	<input type="hidden" name="domain" value="{$domain}"/>
	<input type="hidden" name="createlist" value="true"/>
	<table class="table table-bordered table-hover">
		<tr>
			<td colspan="2">
				<h4>{$LANG.rcmail_createnewlisttitle}</h4>
			</td>
		</tr>
		<tr>
			<td>
				<strong>List Alias</strong>
			</td>
			<td>
				<div class="input-group">
					<input class="form-control" name="listalias" value="{if $createlisterror}{$smarty.post.listalias}{/if}" size="40"/>
					<span class="input-group-addon" id="basic-addon2">@{$domain}</span>
				</div>
			</td>
		</tr>
		<tr>
			<td>
				<strong>{$LANG.rcmail_moderatorsemailtitle}</strong>
			</td>
			 <td>
				{$LANG.rcmail_listmoderatordesc} {$LANG.rcmail_listmoderatormaxdesc}: <strong>5</strong>
				<p><strong>Note:</strong> {$LANG.rcmail_listmoderatormaildesc}</p>
				<textarea class="form-control"  name="moderators" cols="40" rows="4">{if $createlisterror}{$smarty.post.moderators}{/if}</textarea>
			</td>
		</tr>
		<tr>
			<td>
				<strong>{$LANG.rcmail_listquestion1}</strong>
			</td>
			<td>
				<input name="subscriptionheld" value="false" checked="checked" type="radio">&nbsp;{$LANG.rcmail_listanswer11}<br/>
				<input name="subscriptionheld" value="true" type="radio">&nbsp;{$LANG.rcmail_listanswer1}
			</td>
		</tr>
		<tr>
			<td>
				<strong>{$LANG.rcmail_listquestion2}</strong>
			</td>
			<td>
				<input name="whocanpost" value="anyone" checked="checked" type="radio">&nbsp;{$LANG.rcmail_listanswer22}<br/>
				<input name="whocanpost" value="only_members" type="radio">&nbsp;{$LANG.rcmail_listanswer222}<br/>
				<input name="whocanpost" value="only_moderators" type="radio">&nbsp;{$LANG.rcmail_listanswer2}
			</td>
		</tr>
		<tr>
			<td>
				<strong>{$LANG.rcmail_listquestion3}</strong>
			</td>
			<td>
				<input name="moderation" value="false" checked="checked" type="radio">&nbsp;{$LANG.rcmail_listanswer333}<br/>
				<input name="moderation" value="true" type="radio">&nbsp;{$LANG.rcmail_listanswer33}
			</td>
		</tr>
		<tr>
			<td>
				<strong>{$LANG.rcmail_listquestion4}</strong>
			</td>
			<td>
				<input name="replyto" value="sender" checked="checked" type="radio" onclick="return cleanField();">&nbsp;{$LANG.rcmail_listanswer44} <br/>
				<input name="replyto" value="entire_list" type="radio" onclick="return cleanField();">&nbsp;{$LANG.rcmail_listanswer444} <br/>
				<input name="replyto" value="custom_address" type="radio">&nbsp;{$LANG.rcmail_listanswer4}:<br />
				<input class="form-control" name="replytomail" value="{if $createlisterror}{$smarty.post.replytomail}{/if}" size="25"/>
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<p align="center"><input type="submit" value="Create Mailing List" class="btn btn-success"/></p>
			</td>
		</tr>
	</table>
</form>
</div>
