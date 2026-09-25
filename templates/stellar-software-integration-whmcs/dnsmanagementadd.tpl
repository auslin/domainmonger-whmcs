<div class="alert alert-info">
	<p><strong>{$LANG.rcdns_note}:</strong></p>
	<ul>
		<li>{$LANG.rcdns_howtonsdesc1} &quot;<strong>*</strong>&quot;</li>
		<li>{$LANG.rcdns_howtonsdesc2} &quot;<strong>@</strong>&quot;</li>
	</ul>
</div>

{if $addrecorderror}
<br />
<div class="alert alert-danger">
    <p>{$LANG.clientareaerrors}</p>
    <ul>
        {$addrecorderror}
    </ul>
</div>
{/if}

{if $addrecordsuccess}
<br />
<div class="alert alert-success">
	<p>{$LANG.moduleactionsuccess}</p>
    <ul>
        {$addrecordsuccess}
    </ul>
</div>
{/if}


<form method="post" action="dnsmanagement.php?action=managednszoneadd">
	<input type="hidden" name="nsrecordtype" value="{$recordtype}"/>
	<input type="hidden" name="freednshosting" value="{$freednshosting}"/>
	<input type="hidden" name="domain" value="{$domain}"/>
	<input type="hidden" name="domainid" value="{$domainid}"/>
	<input type="hidden" name="add" value="true"/>
	<input type="hidden" name="value" value="{$valuerecord}"/>
	<table class="table table-bordered table-hover dm-rcdns-zone-table">
		<tr>
			<td class="textcenter" colspan="2">
				<h3>{$LANG.rcdns_onlyaddword} {$recordtype} Record {$LANG.rcdns_onlyforword}: {$domain}</h3>
			</td>
		</tr>
		<tr>
			<td>
				{if $recordtype eq "SRV"}
				<strong>Service Host</strong>
				{else}
				<strong>Hostname</strong>
				{/if}
			</td>
			<td>
				<div class="input-group">
					<input name="host" class="form-control" type="text" value="{if $hostrecord}{$hostrecord}{/if}" size="10" />
					<span class="input-group-addon" id="basic-addon2">.{$domain}</span>
				</div>
			</td>
		</tr>
		<tr>
			<td>
				{if $recordtype eq "A"}
				<strong>IPv4 Address</strong>
				{elseif $recordtype eq "AAAA"}
				<strong>IPv6 Address</strong>
				{elseif $recordtype eq "CNAME"}
				<strong>CNAME Target</strong>
				{elseif $recordtype eq "NS"}
				<strong>Nameserver</strong>
				{elseif $recordtype eq "TXT"}
				<strong>Value</strong>
				{elseif $recordtype eq "MX"}
				<strong>Mail Server</strong>
				{elseif $recordtype eq "SRV"}
				<strong>SRV Target</strong>
				{/if}
			</td>
			<td>
				{if $recordtype eq "TXT"}
				<textarea name="value" class="form-control" cols="50" rows="7">{if $valuerecord}{$valuerecord}{/if}</textarea>
				{else}
				<input name="value" class="form-control" type="text" value="{if $valuerecord}{$valuerecord}{/if}" size="60"/>
				{/if}
			</td>
		</tr>
		<tr>
			<td>
				<strong>TTL</strong>
			</td>
			<td>
				<input name="ttl" class="form-control" type="text" value="{if $ttlrecord}{$ttlrecord}{/if}" size="4"/> 
				{$LANG.rcdns_ttladddesc}
			</td>
		</tr>
		{if $recordtype eq "MX"}
		<tr>
			<td>
				<strong>Priority</strong>
			</td>
			<td>
				<input name="priority" class="form-control" type="text" value="{if $priorityrecord}{$priorityrecord}{/if}" size="4"/>
			</td>
		</tr>
		{/if}
		{if $recordtype eq "SRV"}
		<tr>
			<td>
				<strong>Priority</strong>
			</td>
			<td>
				<input name="priority" class="form-control" type="text" value="{if $priorityrecord}{$priorityrecord}{/if}" size="4"/>
			</td>
		</tr>
		<tr>
			<td>
				<strong>Weight</strong>
			</td>
			<td>
				<input name="weight" class="form-control" type="text" value="{if $weightrecord}{$weightrecord}{/if}" size="4"/>
			</td>
		</tr>
		<tr>
			<td>
				<strong>Port</strong></td>
			<td>
				<input name="port" class="form-control" type="text" value="{if $portrecord}{$portrecord}{/if}" size="4"/>
			</td>
		</tr>
		{/if}
		<tr>
			<td colspan="3">
				<p align="center"><input type="submit" value="Add Record" class="btn btn-success"/></p>
			</td>
		</tr>
	</table>
</form>