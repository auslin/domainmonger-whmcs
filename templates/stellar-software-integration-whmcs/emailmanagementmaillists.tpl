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
<script language="javascript" type="text/javascript">
	function confirmDelete(){literal}{{/literal}return confirm("{$LANG.rcmail_deletelistwarning}");{literal}}{/literal}
</script>

{if $listdeletedsuccess}
<br />
<div class="alert alert-success">
    <p>{$LANG.moduleactionsuccess}</p>
    <ul>
        {$listdeletedsuccess}
    </ul>
</div>
{/if}

<div class="input-group">
    <form method="post" action="emailmanagement.php?action=managelists">
		<input type="hidden" name="domainid" value="{$domainid}"/>
		<input type="hidden" name="domain" value="{$domain}"/>
        <span class="input-group-btn">
			<input type="text" name="q" value="{if $q}{$q}{else}{$LANG.rcmail_searchentercriteria}{/if}" class="form-control input-sm" onfocus="if(this.value=='{$LANG.rcmail_searchentercriteria}')this.value=''" />
			<button type="submit" class="btn btn-primary btn-sm">{$LANG.searchfilter}</button>
		</span>		
    </form>
</div>

<br />
<p>{$numproducts} {$LANG.recordsfound}, {$LANG.page} {$pagenumber} {$LANG.pageof} {$totalpages}</p>

<table class="table table-bordered table-hover">
	<tr>
		<td {if $orderby eq "listname"} class="headerSort{$sort}"{/if}><a href="emailmanagement.php?action=managelists{if $q}&q={$q}{/if}&orderby=listname&domainid={$domainid}&domain={$domain}&sort={if $sort eq "desc"}asc{else}desc{/if}&page={$pagenumber}&itemlimit={$itemlimit}">Mailing Lists</a></td>
		<td {if $orderby eq "subscribers"} class="headerSort{$sort}"{/if}><a href="emailmanagement.php?action=managelists{if $q}&q={$q}{/if}&orderby=subscribers&domainid={$domainid}&domain={$domain}&sort={if $sort eq "desc"}asc{else}desc{/if}&page={$pagenumber}&itemlimit={$itemlimit}">Subscribers</a></td>
		<td {if $orderby eq "moderators"} class="headerSort{$sort}"{/if}><a href="emailmanagement.php?action=managelists{if $q}&q={$q}{/if}&orderby=moderators&domainid={$domainid}&domain={$domain}&sort={if $sort eq "desc"}asc{else}desc{/if}&page={$pagenumber}&itemlimit={$itemlimit}">Moderators</a></td>
		<td colspan="3"><a href="#" onclick="return false">Actions</a></td>
	</tr>
	{foreach key=num item=service from=$searchdataKey}
	<tr>
		<td>
			<div style="width:180px;word-wrap:break-word;">{$service.listname}@{$service.domainname}</div>
		</td>
		<td>
			{$service.subscribers}
		</td>
		<td>
			{$service.moderators}
		</td>
		<td>
			<form method="post" action="emailmanagement.php?action=configmailinglist">
				<input type="hidden" name="domainid" value="{$domainid}"/>
				<input type="hidden" name="domain" value="{$domain}"/>
				<input type="hidden" name="listaddress" value="{$service.listname}@{$service.domainname}"/>
				<input type="submit" value="Configure" class="btn btn-primary btn-sm"/>
			</form>
		</td>
		<td>
			<form method="post" action="emailmanagement.php?action=managelist">
				<input type="hidden" name="domainid" value="{$domainid}"/>
				<input type="hidden" name="domain" value="{$domain}"/>
				<input type="hidden" name="listaction" value="manage"/>
				<input type="hidden" name="listaddress" value="{$service.listname}@{$service.domainname}"/>
				<input type="submit" value="Manage Members / Moderators" class="btn btn-primary btn-sm"/>
			</form>
		</td>
		<td>
			<form method="post" action="emailmanagement.php?action=managelists" onclick="return confirmDelete();">
				<input type="hidden" name="delete" value="true"/>
				<input type="hidden" name="domainid" value="{$domainid}"/>
				<input type="hidden" name="domain" value="{$domain}"/>
				<input type="hidden" name="listname" value="{$service.listname}"/>
				<input type="hidden" name="page" value="{$pagenumber}"/>
				<input type="hidden" name="itemlimit" value="{$itemlimit}"/>
				<input type="hidden" name="q" value="{$q}"/>
				<input type="submit" value="Delete" class="btn btn-danger btn-sm"/>
			</form>
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="7">
			{$LANG.norecordsfound}
		</td>
	</tr>
	{/foreach}
	{if $q neq ""}
	<tr>
		<td colspan="7">
			<form method="post" action="emailmanagement.php?action=managelists">
				<input type="hidden" name="q" value=""/> 
				<input type="hidden" name="domainid" value="{$domainid}"/>
				<input type="hidden" name="domain" value="{$domain}"/>
				<input type="submit" value="Clear Search" class="btn btn-sm"/>
			</form>
		</td>
	</tr>
	{/if}
</table>

<br/>
<div class="pull-right">
	<form action="emailmanagement.php">
		<input type="hidden" name="action" value="managelists" />
		<select class="form-control input-sm" name="itemlimit" id="itemlimit" onchange="this.form.submit();">
			<option>{$LANG.resultsperpage}</option>
			<option value="10"{if $itemlimit==10} selected{/if}>10</option>
			<option value="25"{if $itemlimit==25} selected{/if}>25</option>
			<option value="50"{if $itemlimit==50} selected{/if}>50</option>
			<option value="100"{if $itemlimit==100} selected{/if}>100</option>
			<option value="all"{if $itemlimit > 100} selected{/if}>{$LANG.clientareaunlimited}</option>
		</select>
		<input type="hidden" name="domainid" value="{$domainid}" />
		<input type="hidden" name="domain" value="{$domain}" />
	</form>
</div>

<div class="pull-left">
	<ul class="pagination" style="margin-top:0;">
        <li class="prev{if !$prevpage} disabled{/if}"><a href="{if $prevpage}emailmanagement.php?action=managelists{if $q}&q={$q}{/if}&amp;page={$prevpage}&domainid={$domainid}&domain={$domain}&itemlimit={$itemlimit}{else}javascript:return false;{/if}">&larr; {$LANG.previouspage}</a></li>
        <li class="next{if !$nextpage || $nextpage==$pageend} disabled{/if}"><a href="{if $nextpage != $pageend}emailmanagement.php?action=managelists{if $q}&q={$q}{/if}&amp;page={$nextpage}&domainid={$domainid}&domain={$domain}&itemlimit={$itemlimit}{else}javascript:return false;{/if}">{$LANG.nextpage} &rarr;</a></li>
    </ul>
</div>
</div>
