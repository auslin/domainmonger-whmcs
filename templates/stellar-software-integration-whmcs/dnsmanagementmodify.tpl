<div class="alert alert-info">
	<p><strong>{$LANG.rcdns_note}:</strong></p>
	<ul>
		<li>{$LANG.rcdns_howtonsdesc1} &quot;<strong>*</strong>&quot;</li>
		<li>{$LANG.rcdns_howtonsdesc2} &quot;<strong>@</strong>&quot;</li>
	</ul>
</div>

{if $modifyrecorderror}
<br />
<div class="alert alert-danger">
    <p>{$LANG.clientareaerrors}</p>
    <ul>
        {$modifyrecorderror}
    </ul>
</div>
{/if}

{if $modifyrecordsuccess}
<br />
<div class="alert alert-success">
	<p>{$LANG.moduleactionsuccess}</p>
    <ul>
        {$modifyrecordsuccess}
    </ul>
</div>
{/if}


<form method="post" action="dnsmanagement.php?action=managednszonemodify">
	<input type="hidden" name="nsrecordtype" value="{$recordtype}"/>
	<input type="hidden" name="freednshosting" value="{$freednshosting}"/>
	<input type="hidden" name="domain" value="{$domain}"/>
	<input type="hidden" name="domainid" value="{$domainid}"/>
	<input type="hidden" name="modify" value="true"/>
	<input type="hidden" name="currentvalue" value="{if $smarty.post.modify eq "true" && !$modifyrecorderror}{$valuerecord}{else}{$currentvalue}{/if}"/>
	<input type="hidden" name="isvalue" value="{$currentvalue}"/>
	<table class="table table-bordered table-hover dm-rcdns-zone-table">
		<tr>
			<td class="textcenter" colspan="2">
				<h3>
					{$LANG.rcdns_onlymodifyword}
					{if $recordtype eq "SOA"}
					{$recordtype} Record: {$domain}
					{else}
					{$recordtype} Record: {$hostrecord}.{$domain}
					{/if}
				</h3>
			</td>
		</tr>
		<tr>
			<td>
				{if $recordtype eq "SRV"}
				<strong>Service Host</strong>
				{elseif $recordtype eq "SOA"}
				<strong>Primary Nameserver</strong>
				{else}
				<strong>Hostname</strong>
				{/if}
			</td>
			<td>
				{if $recordtype eq "SOA"}
				<input name="host" type="hidden" value="{$hostrecord}"/>
				<span><strong>{$hostrecord}</strong></span>
				{else}
				<input name="currenthost" type="hidden" value="{$currenthost}"/>
				<div class="input-group">
					<input class="form-control" name="host" type="text" value="{$hostrecord}"/>
					<span class="input-group-addon">.{$domain}</span>
				</div>
				{/if}
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
				{elseif $recordtype eq "SOA"}
				<strong>Responsible Person</strong>
				{/if}
			</td>
			<td>
				{if $recordtype eq "TXT"}
				<textarea name="value" class="form-control" cols="50" rows="7">{if $valuerecord}{$valuerecord}{/if}</textarea>
				{elseif $recordtype eq "SOA"}
				<input class="form-control" name="responsibleperson" type="text" value="{if $responsibleperson}{$responsibleperson}{/if} " size="60"/>
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
				{$LANG.rcdns_ttlmodifydesc}
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
				<strong>Port</strong>
			</td>
			<td>
				<input name="port" class="form-control" type="text" value="{if $portrecord}{$portrecord}{/if}" size="4"/>
			</td>
		</tr>
		{/if}
		{if $recordtype eq "SOA"}
		<tr>
			<td>
				<strong>Expire</strong>
			</td>
			<td>
				<input name="soaexpire" class="form-control" type="text" value="{if $soaexpire}{$soaexpire}{/if}" size="4"/>
			</td>
		</tr>
		<tr>
			<td>
				<strong>Retry</strong>
			</td>
			<td>
				<input name="soaretry" class="form-control" type="text" value="{if $soaretry}{$soaretry}{/if}" size="4"/>
			</td>
		</tr>
		<tr>
			<td>
				<strong>Refresh</strong>
			</td>
			<td>
				<input name="soarefresh" class="form-control" type="text" value="{if $soarefresh}{$soarefresh}{/if}" size="4"/>
			</td>
		</tr>
		{/if}
		<tr>
			<td colspan="3">
				<p align="center"><input type="submit" value="Save Changes" class="btn btn-success"/></p>
			</td>
		</tr>
</table>
</form>