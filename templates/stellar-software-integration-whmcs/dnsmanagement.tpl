<style id="dm-rcdns-header-status-fix-713">
.dm-rcdns-zone-table > tbody > tr:first-child > td,
.dm-rcdns-zone-table > tr:first-child > td {
    font-weight: 700;
    color: #163a5f;
    background: #f8fafc;
    white-space: nowrap;
}
.dm-rcdns-zone-table > tbody > tr:first-child > td a,
.dm-rcdns-zone-table > tr:first-child > td a {
    color: #163a5f !important;
    text-decoration: none !important;
}
.dm-rcdns-zone-table .label.label-success {
    display: inline-block;
    min-width: 52px;
    padding: 4px 8px;
    text-align: center;
}
</style>
<style id="dm-rcdns-action-button-fix-712">
.dm-rcdns-actions .btn,
.dm-rcdns-record-actions .btn,
.dm-rcdns-bulk-actions .btn {
    min-width: 70px;
    line-height: 1.2;
    text-align: center;
}
.dm-rcdns-record-actions form {
    display: inline-block;
    margin: 0 3px 3px 0;
}
.dm-rcdns-top-actions form {
    display: inline-block;
    margin: 0 4px 6px 0;
}
</style>
{include file="$template/includes/alert.tpl" type="info" msg=$LANG.rcdns_dnsmanageintrotitle}

<script language="javascript" type="text/javascript">
	function confirmDelete(){literal}{{/literal}return confirm("{$LANG.rcdns_deleterecordwarn}");{literal}}{/literal}
</script>

{if $recorddeletederror}
<br />
<div class="alert alert-danger">
    <p>{$LANG.clientareaerrors}</p>
    <ul>
        {$recorddeletederror}
    </ul>
</div>
{/if}

{if $recorddeletedsuccess}
<br />
<div class="alert alert-success">
	<p>{$LANG.moduleactionsuccess}</p>
    <ul>
        {$recorddeletedsuccess}
    </ul>
</div>
{/if}

{if $nschangeerror}
<br />
<div class="alert alert-danger">
    <p>{$LANG.clientareaerrors}</p>
    <ul>
        {$nschangeerror}
    </ul>
</div>
{/if}

{if $nschangesuccess}
<br />
<div class="alert alert-success">
	<p>{$LANG.moduleactionsuccess}</p>
    <ul>
        {$nschangesuccess}
    </ul>
</div>
{/if}

{if $nsserversok != 1}
<script language="javascript" type="text/javascript">
	{literal}function showdnscheckform(){jQuery("#dnscheck").slideToggle();}{/literal}
</script>

<div class="alert alert-warning">
	{$LANG.rcdns_domdnsconfigerror1}&nbsp;<a href="#" onclick="showdnscheckform();return false;">{$LANG.rcdns_clickherelink}</a><br/>
</div>

<div style="display:none;" id="dnscheck">
	<br/>
	<h3>
		{$LANG.rcdns_nameservertitle}
	</h3>
	<p>
		{$LANG.rcdns_nsserversolutiontitle}
	</p>
	<p>
		<strong>{$LANG.rcdns_note}:</strong> {$LANG.rcdns_dnsconfigapplied}
	</p>
	<table class="table table-bordered table-hover dm-rcdns-zone-table">
		<tr>
			<td class="textcenter" colspan="2">
				{$LANG.rcdns_nsserversolution}
			</td>
		</tr>
		<tr>
			<td width="50%" style="vertical-align:top">
				<p class="label label-danger">
					Current Nameservers
				</p><br />
				{foreach key=num item=actualdns from=$configurednsservers.0}
				{$actualdns}<br />
				{/foreach}
			</td>
			<td width="50%" style="vertical-align:top">
				<p class="label label-success">
					Recommended Nameservers
				</p><br />
				{foreach key=num item=defaultdns from=$requirednsservers.0}
				{$defaultdns}<br />
				{/foreach}
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<form method="post" action="dnsmanagement.php?action=managednszone">
					<input type="hidden" name="domainid" value="{if $domainid}{$domainid}{else}{$id}{/if}"/>
					<input type="hidden" name="domain" value="{$domain}"/>
					{foreach key=num item=defaultdns from=$requirednsservers.0}
					<input type="hidden" name="ns[]" value="{$defaultdns}"/>
					{/foreach}
					<input type="hidden" name="changens" value="true"/>
					<p align="center"><input type="submit" {if $nsserversok == 1}disabled="disabled"{/if} value="Update Nameservers" class="btn btn-success"/></p>
				</form>
			</td>
		</tr>
	</table>
</div>
{/if}

{if $recordtype neq "SOA"}
<div class="dm-rcdns-top-actions dm-rcdns-actions">
	<div style="float:left;padding:0px 3px 2px 0px;">
		<form method="POST" action="dnsmanagement.php?action=managednszoneadd">
			<input type="hidden" name="nsrecordtype" value="{$recordtype}"/>
			<input type="hidden" name="domainid" value="{$domainid}"/>
			<input type="hidden" name="domain" value="{$domain}"/>
			<input name="freednshosting" value="{$freednshosting}" type="hidden"/>
			<input type="hidden" name="page" value="{$pagenumber}"/>
			<input type="hidden" name="itemlimit" value="{$itemlimit}"/>
			<input type="hidden" name="q" value="{$q}"/>
			<input type="submit" value="Add {$recordtype} Record" class="btn btn-success btn-sm"/>
		</form>
	</div>
	{if $googleapps}
		{if $recordtype eq "A" || $recordtype eq "AAAA" || $recordtype eq "MX" || $recordtype eq "CNAME" || $recordtype eq "SRV" || $recordtype eq "TXT"}
			<div style="float:left;padding:0px 3px 3px 0px;">
				<form method="POST" action="dnsmanagement.php?action=managednszone">
					<input type="hidden" name="googleapps" value="true"/>
					<input type="hidden" name="nsrecordtype" value="{$recordtype}"/>
					<input type="hidden" name="domainid" value="{$domainid}"/>
					<input type="hidden" name="domain" value="{$domain}"/>
					<input name="freednshosting" value="{$freednshosting}" type="hidden"/>
					<input type="hidden" name="page" value="{$pagenumber}"/>
					<input type="hidden" name="itemlimit" value="{$itemlimit}"/>
					<input type="hidden" name="q" value="{$q}"/>
					<input type="submit" value="Create Google {$recordtype} Records" class="btn btn-success btn-sm"/>
				</form>
			</div>
		{/if}
	{/if}
</div>
{/if}
	
<br /><br />
<h3>{$LANG.rcdns_domainadminister} {$recordtype} Records</h3>

{if $recordtype neq "SOA"}
<div class="input-group">
    <form method="post" action="dnsmanagement.php?action=managednszone">
		<input type="hidden" name="nsrecordtype" value="{$recordtype}"/>
		<input type="hidden" name="domainid" value="{$domainid}"/>
		<input type="hidden" name="domain" value="{$domain}"/>
		<input name="freednshosting" value="{$freednshosting}" type="hidden"/>
        <span class="input-group-btn">
			<input type="text" name="q" value="{if $q}{$q}{else}{$LANG.rcdns_searchenterdomain}{/if}" class="form-control input-sm" onfocus="if(this.value=='{$LANG.rcdns_searchenterdomain}')this.value=''" />
			<button type="submit" class="btn btn-primary btn-sm">Search</button>
		</span>
    </form>
</div>
{/if}

<br />
<p>{$numproducts} Records Found, Page {$pagenumber} of {$totalpages}</p>

<script language="javascript" type="text/javascript">
	{literal}
		jQuery(document).ready(function() {
			jQuery("#select-all-records").click(function() {
				var checkBoxes = jQuery("input[name=multidelete\\[\\]]");
				checkBoxes.prop("checked", !checkBoxes.prop("checked"));
			});                 
		});	
	{/literal}
</script>


{if $recordtype eq "A" || $recordtype eq "AAAA" || $recordtype eq "CNAME" || $recordtype eq "NS" || $recordtype eq "TXT"}
<table class="table table-bordered table-hover dm-rcdns-zone-table">
	<tr>
		<td {if $orderby eq "host"} class="headerSort{$sort}"{/if}><a href="dnsmanagement.php?action=managednszone{if $q}&q={$q}{/if}&orderby=host&domainid={$domainid}&domain={$domain}&freednshosting={$freednshosting}&nsrecordtype={$recordtype}&sort={if $sort eq "desc"}asc{else}desc{/if}&page={$pagenumber}&itemlimit={$itemlimit}">Name</a></td>
		<td {if $orderby eq "value"} class="headerSort{$sort}"{/if}><a href="dnsmanagement.php?action=managednszone{if $q}&q={$q}{/if}&orderby=value&domainid={$domainid}&domain={$domain}&freednshosting={$freednshosting}&nsrecordtype={$recordtype}&sort={if $sort eq "desc"}asc{else}desc{/if}&page={$pagenumber}&itemlimit={$itemlimit}">Value</a></td>
		<td><a href="#" onclick="return false">TTL</a></td>
		<td {if $orderby eq "status"} class="headerSort{$sort}"{/if}><a href="dnsmanagement.php?action=managednszone{if $q}&q={$q}{/if}&orderby=status&domainid={$domainid}&domain={$domain}&freednshosting={$freednshosting}&nsrecordtype={$recordtype}&sort={if $sort eq "desc"}asc{else}desc{/if}&page={$pagenumber}&itemlimit={$itemlimit}">Status</a></td>
		<td colspan="2" align="center"><a href="#" onclick="return false">Actions</a></td>
		<td><input id="select-all-records" type="checkbox" /></td>
	</tr>
	{foreach key=num item=service from=$searchdataKey}
	<tr>
		<td>{$service.host}.{$domain}</td>
		<td><div style="width: 250px; word-wrap: break-word;">{$service.value}</div></td>
		<td>{$service.timetolive}</td>
		<td>{if $service.status eq "Active"}<span class="label label-success">Active</span>{elseif $service.status}<span class="label label-suspended">{$service.status}</span>{else}<span class="label label-suspended">Inactive</span>{/if}</td>
		<td>
			<form method="POST" action="dnsmanagement.php?action=managednszonemodify">
				<input type="hidden" name="domainid" value="{$domainid}"/>
				<input name="freednshosting" value="{$freednshosting}" type="hidden">
				<input type="hidden" name="nsrecordtype" value="{$recordtype}"/>
				<input type="hidden" name="domain" value="{$domain}"/>
				<input type="hidden" name="host" value="{$service.host}"/>
				<input type="hidden" name="value" value="{$service.value}"/>
				<input type="hidden" name="currentvalue" value="{$service.value}"/>
				<input type="hidden" name="ttl" value="{$service.timetolive}"/>
				<input type="submit" value="Modify" class="btn btn-primary btn-sm"/>
			</form>
		</td>
		<td>
			<form method="POST" action="dnsmanagement.php?action=managednszone" onclick="return confirmDelete();">
				<input type="hidden" name="delete" value="true"/>
				<input type="hidden" name="domainid" value="{$domainid}"/>
				<input name="freednshosting" value="{$freednshosting}" type="hidden"/>
				<input type="hidden" name="nsrecordtype" value="{$recordtype}"/>
				<input type="hidden" name="page" value="{$pagenumber}"/>
				<input type="hidden" name="itemlimit" value="{$itemlimit}"/>
				<input type="hidden" name="q" value="{$q}"/>
				<input type="hidden" name="domain" value="{$domain}"/>
				<input type="hidden" name="host" value="{$service.host}"/>
				<input type="hidden" name="value" value="{$service.value}"/>
				<input type="submit" value="Delete" class="btn btn-danger btn-sm"/>
			</form>
		</td>
		<td><input name="multidelete[]" type="checkbox" value="{$service.host},{$service.value}" class="checkbox"/></td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="7">{$LANG.norecordsfound}</td>
    </tr>
	{/foreach}
	<tr>
		<td colspan="7">
			{if $searchdataKey}
			<div style="float:right;">
				<form id="multidelete" method="POST" action="dnsmanagement.php?action=managednszone">
				<input type="hidden" name="delete" value="true"/>
				<input type="hidden" name="domain" value="{$domain}"/>
				<input type="hidden" name="domainid" value="{$domainid}"/>
				<input name="freednshosting" value="{$freednshosting}" type="hidden"/>
				<input type="hidden" name="nsrecordtype" value="{$recordtype}"/>
				<input type="hidden" name="page" value="{$pagenumber}"/>
				<input type="hidden" name="itemlimit" value="{$itemlimit}"/>
				<input type="hidden" name="q" value="{$q}"/>
				<input type="submit" value="Delete Selected" class="btn btn-danger btn-sm" onclick="return confirmDelete();" />
				</form>
			</div>
			{/if}
			{if $q neq ""}
			<div style="float:left;">
				<form method="post" action="dnsmanagement.php?action=managednszone">
					<input type="hidden" name="q" value=""/> 
					<input type="hidden" name="nsrecordtype" value="{$recordtype}"/>
					<input type="hidden" name="domainid" value="{$domainid}"/>
					<input type="hidden" name="domain" value="{$domain}"/>
					<input name="freednshosting" value="{$freednshosting}" type="hidden"/>
					<input type="submit" value="Clear Search" class="btn btn-success"/>
				</form>
			</div>
			{/if}
		</td>
	</tr>
</table>
{/if}

{if $recordtype eq "MX"}
<table class="table table-bordered table-hover dm-rcdns-zone-table">
	<tr>
		<td {if $orderby eq "host"} class="headerSort{$sort}"{/if}><a href="dnsmanagement.php?action=managednszone{if $q}&q={$q}{/if}&orderby=host&domainid={$domainid}&domain={$domain}&freednshosting={$freednshosting}&nsrecordtype={$recordtype}&sort={if $sort eq "desc"}asc{else}desc{/if}&page={$pagenumber}&itemlimit={$itemlimit}">Name</a></td>
		<td {if $orderby eq "value"} class="headerSort{$sort}"{/if}><a href="dnsmanagement.php?action=managednszone{if $q}&q={$q}{/if}&orderby=value&domainid={$domainid}&domain={$domain}&freednshosting={$freednshosting}&nsrecordtype={$recordtype}&sort={if $sort eq "desc"}asc{else}desc{/if}&page={$pagenumber}&itemlimit={$itemlimit}">Value</a></td>
		<td><a href="#" onclick="return false">TTL</a></td>
		<td><a href="#" onclick="return false">Priority</a></td>
		<td {if $orderby eq "status"} class="headerSort{$sort}"{/if}><a href="dnsmanagement.php?action=managednszone{if $q}&q={$q}{/if}&orderby=status&domainid={$domainid}&domain={$domain}&freednshosting={$freednshosting}&nsrecordtype={$recordtype}&sort={if $sort eq "desc"}asc{else}desc{/if}&page={$pagenumber}&itemlimit={$itemlimit}">Status</a></td>
		<td colspan="2" align="center"><a href="#" onclick="return false">Actions</a></td>
		<td><input id="select-all-records" type="checkbox" /></td>
	</tr>
	{foreach key=num item=service from=$searchdataKey}
	<tr>
		<td>{$service.host}.{$domain}</td>
		<td><div style="width: 250px; word-wrap: break-word;">{$service.value}</div></td>
		<td>{$service.timetolive}</td>
		<td>{$service.priority}</td>
		<td>{if $service.status eq "Active"}<span class="label label-success">Active</span>{elseif $service.status}<span class="label label-suspended">{$service.status}</span>{else}<span class="label label-suspended">Inactive</span>{/if}</td>
		<td>
			<form method="POST" action="dnsmanagement.php?action=managednszonemodify">
				<input type="hidden" name="domainid" value="{$domainid}"/>
				<input name="freednshosting" value="{$freednshosting}" type="hidden"/>
				<input type="hidden" name="nsrecordtype" value="{$recordtype}"/>
				<input type="hidden" name="domain" value="{$domain}"/>
				<input type="hidden" name="host" value="{$service.host}"/>
				<input type="hidden" name="value" value="{$service.value}"/>
				<input type="hidden" name="currentvalue" value="{$service.value}"/>
				<input type="hidden" name="ttl" value="{$service.timetolive}"/> 
				<input type="hidden" name="priority" value="{$service.priority}"/>
				<input type="submit" value="Modify" class="btn btn-primary btn-sm"/>
			</form>
		</td>
		<td>
			<form method="POST" action="dnsmanagement.php?action=managednszone" onclick="return confirmDelete();">
				<input type="hidden" name="delete" value="true"/>
				<input type="hidden" name="domainid" value="{$domainid}"/>
				<input name="freednshosting" value="{$freednshosting}" type="hidden">
				<input type="hidden" name="nsrecordtype" value="{$recordtype}"/>
				<input type="hidden" name="page" value="{$pagenumber}"/>
				<input type="hidden" name="itemlimit" value="{$itemlimit}"/>
				<input type="hidden" name="q" value="{$q}"/>
				<input type="hidden" name="domain" value="{$domain}"/>
				<input type="hidden" name="host" value="{$service.host}"/>
				<input type="hidden" name="value" value="{$service.value}"/>
				<input type="submit" value="Delete" class="btn btn-danger btn-sm"/>
			</form>
		</td>
		<td>
			<input name="multidelete[]" type="checkbox" value="{$service.host},{$service.value}" class="checkbox"/>
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="8">{$LANG.norecordsfound}</td>
    </tr>
	{/foreach}
	<tr>
		<td colspan="8">
			{if $searchdataKey}
			<div style="float:right;">
				<form id="multidelete" method="POST" action="dnsmanagement.php?action=managednszone">
				<input type="hidden" name="delete" value="true"/>
				<input type="hidden" name="domain" value="{$domain}"/>
				<input type="hidden" name="domainid" value="{$domainid}"/>
				<input name="freednshosting" value="{$freednshosting}" type="hidden"/>
				<input type="hidden" name="nsrecordtype" value="{$recordtype}"/>
				<input type="hidden" name="page" value="{$pagenumber}"/>
				<input type="hidden" name="itemlimit" value="{$itemlimit}"/>
				<input type="hidden" name="q" value="{$q}"/>
				<input type="submit" value="Delete Selected" class="btn btn-danger btn-sm" onclick="return confirmDelete();" />
				</form>
			</div>
			{/if}
			{if $q neq ""}
			<div style="float:left;">
				<form method="post" action="dnsmanagement.php?action=managednszone">
					<input type="hidden" name="q" value=""/> 
					<input type="hidden" name="nsrecordtype" value="{$recordtype}"/>
					<input type="hidden" name="domainid" value="{$domainid}"/>
					<input type="hidden" name="domain" value="{$domain}"/>
					<input name="freednshosting" value="{$freednshosting}" type="hidden"/>
					<input type="submit" value="Clear Search" class="btn btn-success"/>
				</form>
			</div>
			{/if}
		</td>
	</tr>
</table>
{/if}

{if $recordtype eq "SRV"}
<table class="table table-bordered table-hover dm-rcdns-zone-table">
	<tr>
		<td {if $orderby eq "host"} class="headerSort{$sort}"{/if}><a href="dnsmanagement.php?action=managednszone{if $q}&q={$q}{/if}&orderby=host&domainid={$domainid}&domain={$domain}&freednshosting={$freednshosting}&nsrecordtype={$recordtype}&sort={if $sort eq "desc"}asc{else}desc{/if}&page={$pagenumber}&itemlimit={$itemlimit}">Name</a></td>
		<td {if $orderby eq "value"} class="headerSort{$sort}"{/if}><a href="dnsmanagement.php?action=managednszone{if $q}&q={$q}{/if}&orderby=value&domainid={$domainid}&domain={$domain}&freednshosting={$freednshosting}&nsrecordtype={$recordtype}&sort={if $sort eq "desc"}asc{else}desc{/if}&page={$pagenumber}&itemlimit={$itemlimit}">Value</a></td>
		<td><a href="#" onclick="return false">TTL</a></td>
		<td><a href="#" onclick="return false">Priority</a></td>
		<td><a href="#" onclick="return false">Weight</a></td>
		<td><a href="#" onclick="return false">Port</a></td>
		<td {if $orderby eq "status"} class="headerSort{$sort}"{/if}><a href="dnsmanagement.php?action=managednszone{if $q}&q={$q}{/if}&orderby=status&domainid={$domainid}&domain={$domain}&freednshosting={$freednshosting}&nsrecordtype={$recordtype}&sort={if $sort eq "desc"}asc{else}desc{/if}&page={$pagenumber}&itemlimit={$itemlimit}">Status</a></td>
		<td align="center" colspan="2"><a href="#" onclick="return false">Actions</a></td>
		<td><input id="select-all-records" type="checkbox" /></td>
	</tr>
	{foreach key=num item=service from=$searchdataKey}
	<tr>
		<td>{$service.host}.{$domain}</td>
		<td><div style="width: 130px; word-wrap: break-word;">{$service.value}</div></td>
		<td>{$service.timetolive}</td>
		<td>{$service.priority}</td>
		<td>{$service.weight}</td>
		<td>{$service.port}</td>
		<td>{if $service.status eq "Active"}<span class="label label-success">Active</span>{elseif $service.status}<span class="label label-suspended">{$service.status}</span>{else}<span class="label label-suspended">Inactive</span>{/if}</td>
		<td>
			<form method="POST" action="dnsmanagement.php?action=managednszonemodify">
				<input type="hidden" name="domainid" value="{$domainid}"/>
				<input name="freednshosting" value="{$freednshosting}" type="hidden"/>
				<input type="hidden" name="nsrecordtype" value="{$recordtype}"/>
				<input type="hidden" name="domain" value="{$domain}"/>
				<input type="hidden" name="host" value="{$service.host}"/>
				<input type="hidden" name="value" value="{$service.value}"/>
				<input type="hidden" name="currentvalue" value="{$service.value}"/>
				<input type="hidden" name="ttl" value="{$service.timetolive}"/>
				<input type="hidden" name="priority" value="{$service.priority}"/>
				<input type="hidden" name="weight" value="{$service.weight}"/>
				<input type="hidden" name="port" value="{$service.port}"/>
				<input type="submit" value="Modify" class="btn btn-primary btn-sm"/>
			</form>
		</td>
		<td>
			<form method="POST" action="dnsmanagement.php?action=managednszone" onclick="return confirmDelete();">
				<input type="hidden" name="delete" value="true"/>
				<input type="hidden" name="domainid" value="{$domainid}"/>
				<input name="freednshosting" value="{$freednshosting}" type="hidden"/>
				<input type="hidden" name="nsrecordtype" value="{$recordtype}"/>
				<input type="hidden" name="page" value="{$pagenumber}"/>
				<input type="hidden" name="itemlimit" value="{$itemlimit}"/>
				<input type="hidden" name="q" value="{$q}"/>
				<input type="hidden" name="domain" value="{$domain}"/>
				<input type="hidden" name="host" value="{$service.host}"/>
				<input type="hidden" name="value" value="{$service.value}"/>
				<input type="hidden" name="weight" value="{$service.weight}"/>
				<input type="hidden" name="port" value="{$service.port}"/>
				<input type="submit" value="Delete" class="btn btn-danger btn-sm"/>
			</form>
		</td>
		<td>
			<input name="multidelete[]" type="checkbox" value="{$service.host},{$service.value},{$service.port},{$service.weight}" class="checkbox"/>
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="10">{$LANG.norecordsfound}</td>
    </tr>
	{/foreach}
	<tr>
		<td colspan="10">
			{if $searchdataKey}
			<div style="float:right;">
				<form id="multidelete" method="POST" action="dnsmanagement.php?action=managednszone">
				<input type="hidden" name="delete" value="true"/>
				<input type="hidden" name="domain" value="{$domain}"/>
				<input type="hidden" name="domainid" value="{$domainid}"/>
				<input name="freednshosting" value="{$freednshosting}" type="hidden"/>
				<input type="hidden" name="nsrecordtype" value="{$recordtype}"/>
				<input type="hidden" name="page" value="{$pagenumber}"/>
				<input type="hidden" name="itemlimit" value="{$itemlimit}"/>
				<input type="hidden" name="q" value="{$q}"/>
				<input type="submit" value="Delete Selected" class="btn btn-danger btn-sm" onclick="return confirmDelete();" />
				</form>
			</div>
			{/if}
			{if $q neq ""}
			<div style="float:left;">
				<form method="post" action="dnsmanagement.php?action=managednszone">
					<input type="hidden" name="q" value=""/> 
					<input type="hidden" name="nsrecordtype" value="{$recordtype}"/>
					<input type="hidden" name="domainid" value="{$domainid}"/>
					<input type="hidden" name="domain" value="{$domain}"/>
					<input name="freednshosting" value="{$freednshosting}" type="hidden"/>
					<input type="submit" value="Clear Search" class="btn btn-success"/>
				</form>
			</div>
			{/if}
		</td>
	</tr>
</table>
{/if}

{if $recordtype eq "SOA"}
<table class="table table-bordered table-hover dm-rcdns-zone-table">
	<tr>
		<td><a href="#" onclick="return false">Primary Nameserver</a></td>
		<td><a href="#" onclick="return false">Responsible Person</a></td>
		<td><a href="#" onclick="return false">TTL</a></td>
		<td><a href="#" onclick="return false">Expire</a></td>
		<td><a href="#" onclick="return false">Refresh</a></td>
		<td><a href="#" onclick="return false">Retry</a></td>
		<td><a href="#" onclick="return false">Status</a></td>
		<td align="center"><a href="#" onclick="return false">Actions</a></td>
	</tr>
	{foreach key=num item=service from=$searchdataKey}
	<tr>
		<td><div style="width: 140px; word-wrap: break-word;">{$service.primaryns}</div></td>
		<td><div style="width: 140px; word-wrap: break-word;">{$service.responsibleperson}</div></td>
		<td>{$service.ttl}</td>
		<td>{$service.expire}</td>
		<td>{$service.refresh}</td>
		<td>{$service.retry}</td>
		<td>{if $service.status eq "Active"}<span class="label label-success">Active</span>{elseif $service.status}<span class="label label-suspended">{$service.status}</span>{else}<span class="label label-suspended">Inactive</span>{/if}</td>
		<td>
			<form method="POST" action="dnsmanagement.php?action=managednszonemodify">
				<input type="hidden" name="domainid" value="{$domainid}"/>
				<input name="freednshosting" value="{$freednshosting}" type="hidden"/>
				<input type="hidden" name="nsrecordtype" value="{$recordtype}"/>
				<input type="hidden" name="domain" value="{$domain}"/>
				<input type="hidden" name="host" value="{$service.primaryns}"/>
				<input type="hidden" name="responsibleperson" value="{$service.responsibleperson}"/>
				<input type="hidden" name="ttl" value="{$service.ttl}"/>
				<input type="hidden" name="soaexpire" value="{$service.expire}"/>
				<input type="hidden" name="soarefresh" value="{$service.refresh}"/>
				<input type="hidden" name="soaretry" value="{$service.retry}"/>
				<input type="submit" value="Modify" class="btn btn-primary btn-sm"/>
			</form>
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td align="center" colspan="11">{$LANG.norecordsfound}</td>
	</tr>
	{/foreach}
</table>
{/if}

<script language="javascript" type="text/javascript">
	{literal}
		jQuery('#multidelete').on('submit',function(e){
			$form=jQuery(this);
			jQuery('input[type=checkbox]:checked').each(function(index,elem){
				var val=jQuery(elem).val(),name=jQuery(elem).attr('name');
				var hiddenInput=jQuery('<input type="hidden" value="'+val+'" name="'+name+'"/>');
				$form.append(hiddenInput);
			});
		});
	{/literal}
</script>

<br />

<div class="pull-right">
	<form action="dnsmanagement.php">
		<input type="hidden" name="action" value="managednszone" />
		<select name="itemlimit" id="itemlimit" onchange="this.form.submit();" class="form-control input-sm">
			<option>{$LANG.resultsperpage}</option>
			<option value="10"{if $itemlimit==10} selected{/if}>10</option>
			<option value="25"{if $itemlimit==25} selected{/if}>25</option>
			<option value="50"{if $itemlimit==50} selected{/if}>50</option>
			<option value="100"{if $itemlimit==100} selected{/if}>100</option>
			<option value="all"{if $itemlimit > 100} selected{/if}>{$LANG.clientareaunlimited}</option>
		</select>
		<input type="hidden" name="domainid" value="{$domainid}" />
		<input type="hidden" name="domain" value="{$domain}" />
		<input type="hidden" name="freednshosting" value="{$freednshosting}" />
		<input type="hidden" name="nsrecordtype" value="{$recordtype}" />
	</form>
</div>

<div class="pull-left">
	<ul class="pagination" style="margin-top:0;">
		<li class="prev{if !$prevpage} disabled{/if}"><a href="{if $prevpage}dnsmanagement.php?action=managednszone{if $q}&q={$q}{/if}&amp;page={$prevpage}&domainid={$domainid}&domain={$domain}&freednshosting={$freednshosting}&nsrecordtype={$recordtype}&itemlimit={$itemlimit}{else}javascript:return false;{/if}">&larr; {$LANG.previouspage}</a></li>
		<li class="next{if !$nextpage || $nextpage==$pageend} disabled{/if}"><a href="{if $nextpage != $pageend}dnsmanagement.php?action=managednszone{if $q}&q={$q}{/if}&amp;page={$nextpage}&domainid={$domainid}&domain={$domain}&freednshosting={$freednshosting}&nsrecordtype={$recordtype}&itemlimit={$itemlimit}{else}javascript:return false;{/if}">{$LANG.nextpage} &rarr;</a></li>
	</ul>
</div>