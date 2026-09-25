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
{include file="$template/includes/alert.tpl" type="info" msg=$LANG.rcmail_createforwarderdesc1|cat:"@"|cat:$domain|cat:$LANG.rcmail_createforwarderdesc2|cat:"@"|cat:$domain}

{if $mailfwdcreateerror}
<br />
<div class="alert alert-danger">
    <p>{$LANG.clientareaerrors}</p>
    <ul>
        {$mailfwdcreateerror}
    </ul>
</div>
{/if}

{if $mailfwdcreatesuccess}
<br />
<div class="alert alert-success">
	<p>{$LANG.moduleactionsuccess}</p>
    <ul>
        {$mailfwdcreatesuccess}
    </ul>
</div>
{/if}

<form method="post" action="emailmanagement.php?action=createmailfwd">
	<input type="hidden" name="page" value="{$smarty.post.page}"/>
	<input type="hidden" name="domainid" value="{$domainid}"/>
	<input type="hidden" name="domain" value="{$domain}"/>
	<input name="freemailhosting" value="{$freemailhosting}" type="hidden"/>
	<input type="hidden" name="createmailfwd" value="true"/>
	<table class="table table-bordered table-hover">
		<tr>
			<td style="vertical-align:top">
				{$LANG.rcmail_ifmailarrives}
			</td>
			<td style="vertical-align:top">
				<div class="input-group">
					<input class="form-control" name="mailalias" type="text" value="{if $mailfwdcreateerror}{$smarty.post.mailalias}{/if}" size="30"/>
					<span class="input-group-addon" id="basic-addon2">@{$domain}</span>
				</div>
			</td>
		</tr>
		<tr>
			<td>
				<strong>Redirect To</strong>
			</td>
			<td>
				Forward To<br />
				<textarea class="form-control" name="forwardto" cols="50" rows="7">{if $mailfwdcreateerror}{$smarty.post.forwardto}{/if}</textarea>
			</td>
		</tr>
		<tr>
			<td>
				<strong>Note:</strong>
			</td>
			<td>
				{if $freemailhosting eq "false"} {$LANG.rcmail_maxrecipientsallowed} <strong>10</strong>{else}{$LANG.rcmail_maxrecipientsallowed} <strong>5</strong>{/if}
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<p align="center"><input type="submit" value="Create Forwarder" class="btn btn-success"/></p>
			</td>
		</tr>
	</table>
</form>
</div>
