<style id="dm-rcdns-dsigner-header-fix-713">
.dm-rcdns-zone-table > tbody > tr:first-child > td,
.dm-rcdns-zone-table > tr:first-child > td {
    font-weight: 700;
    color: #163a5f;
    background: #f8fafc;
    white-space: nowrap;
}
</style>
<script language="javascript" type="text/javascript">
	function confirmDelete(){literal}{{/literal}return confirm("{$LANG.rcdns_deletednssecwarn}");{literal}}{/literal}
	{literal}function showdnssecform(){jQuery("#dnssecform").slideToggle();}{/literal}
</script>

{if $deleterecorderror}
<br />
<div class="alert alert-danger">
    <p>{$LANG.clientareaerrors}</p>
    <ul>
        {$deleterecorderror}
    </ul>
</div>
{/if}

{if $deleterecordsuccess}
<br />
<div class="alert alert-success">
	<p>{$LANG.moduleactionsuccess}</p>
    <ul>
        {$deleterecordsuccess}
    </ul>
</div>
{/if}

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

<h3 class="dm-rcdns-legacy-page-title">{$LANG.rcdns_domainadminister} {$recordtype} Records</h3>

<table class="table table-bordered table-hover dm-rcdns-zone-table">
	<tr>
		<td>Key Tag</td>
		<td>Algorithm</td>
		<td>Digest Type</td>
		<td>Digest</td>
		<td>Actions</td>
	</tr>
	{foreach key=num item=service from=$dnssec_details}
	<tr>
		<td>{$service.keytag}</td>
		<td>{$service.algorithm}</td>
		<td>{$service.digesttype}</td>
		<td><div style="max-width: 250px; word-wrap: break-word;">{$service.digest}</div></td>
		<td>
			<form method="POST" action="dnsmanagement.php?action=dnsseczone" onclick="return confirmDelete();">
				<input type="hidden" name="dsdelete" value="true"/>
				<input type="hidden" name="domainid" value="{$domainid}"/>
				<input name="freednshosting" value="{$freednshosting}" type="hidden"/>
				<input type="hidden" name="nsrecordtype" value="{$recordtype}"/>
				<input type="hidden" name="domain" value="{$domain}"/>
				<input type="hidden" name="keytag" value="{$service.keytag}"/>
				<input type="hidden" name="algorithm" value="{$service.algorithm}"/>
				<input type="hidden" name="digesttype" value="{$service.digesttype}"/>
				<input type="hidden" name="digest" value="{$service.digest}"/>
				<input type="submit" value="Delete" class="btn btn-danger btn-sm"/>
			</form>
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="7">{$LANG.norecordsfound}</td>
    </tr>
	{/foreach}
</table>

<div>
	<a href="#" class="btn btn-success btn-sm" onclick="showdnssecform();return false;">Add {$recordtype} Record</a>
</div>

<br />

<div style="display:{if $toggleoff}block{else}none{/if};" id="dnssecform">
	<form method="POST" action="dnsmanagement.php?action=dnsseczone">
	<table class="table table-bordered table-hover dm-rcdns-zone-table">
		<tr>
			<td class="textcenter" colspan="2">
				<h3>{$LANG.rcdns_onlyaddword} {$recordtype} Record {$LANG.rcdns_onlyforword} {$domain}</h3>
			</td>
		</tr>
		<tr>
			<td><strong>Key Tag</strong></td>
			<td>
				<input name="keytag" class="form-control" type="text" value="{if $smarty.post.keytag}{$smarty.post.keytag}{/if}" /> 
				{$LANG.rcdns_dskeytagdesc}
			</td>
		</tr>
		<tr>
			<td><strong>Algorithm</strong></td>
			<td>
				<select name="algorithm" class="form-control">
					<option value="1" {if $smarty.post.algorithm eq 1}selected="selected"{/if}>RSA/MD5 (1)</option>
					<option value="2" {if $smarty.post.algorithm eq 2}selected="selected"{/if}>Diffie-Hellman (2)</option>
					<option value="3" {if $smarty.post.algorithm eq 3}selected="selected"{/if}>DSA-SHA1 (3)</option>
					<option value="4" {if $smarty.post.algorithm eq 4}selected="selected"{/if}>Elliptic Curve (ECC) (4)</option>
					<option value="5" {if $smarty.post.algorithm eq 5}selected="selected"{/if}>RSA-SHA1 (5)</option>
					<option value="6" {if $smarty.post.algorithm eq 6}selected="selected"{/if}>DSA-SHA1-NSEC3 (6)</option>
					<option value="7" {if $smarty.post.algorithm eq 7}selected="selected"{/if}>RSA-SHA1-NSEC3 (7)</option>
					<option value="8" {if $smarty.post.algorithm eq 8}selected="selected"{/if}>RSA-SHA256 (8)</option>
					<option value="10" {if $smarty.post.algorithm eq 10}selected="selected"{/if}>RSA-SHA512 (10)</option>
					<option value="13" {if $smarty.post.algorithm eq 13}selected="selected"{/if}>ECDSA Curve P-256 with SHA-256 (13)</option>
					<option value="14" {if $smarty.post.algorithm eq 14}selected="selected"{/if}>ECDSA Curve P-384 with SHA-384 (14)</option>
					<option value="252" {if $smarty.post.algorithm eq 252}selected="selected"{/if}>Indirect (252)</option>
					<option value="253" {if $smarty.post.algorithm eq 253}selected="selected"{/if}>Private [PRIVATEDNS] (253)</option>
					<option value="254" {if $smarty.post.algorithm eq 254}selected="selected"{/if}>Private [PRIVATEOID] (254)</option>
				</select>
				{$LANG.rcdns_dsalgorithmdesc}
			</td>
		</tr>
		<tr>
			<td><strong>Digest Type</strong></td>
			<td>
				<select name="digesttype" class="form-control">
					<option value="1" {if $smarty.post.digesttype eq 1}selected="selected"{/if}>SHA-1 (1)</option>
					<option value="2" {if $smarty.post.digesttype eq 2}selected="selected"{/if}>SHA-256 (2)</option>
					<option value="3" {if $smarty.post.digesttype eq 3}selected="selected"{/if}>GOST R 34.11-94 (3)</option>
					<option value="4" {if $smarty.post.digesttype eq 4}selected="selected"{/if}>SHA-384 (4)</option>
				</select>
				{$LANG.rcdns_dsdigesttypedesc}
			</td>
		</tr>
		<tr>
			<td><strong>Digest</strong></td>
			<td>
				<input name="digest" class="form-control" type="text" value="{if $smarty.post.digest}{$smarty.post.digest}{/if}" /> 
				{$LANG.rcdns_dsdigestdesc}
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<input type="hidden" name="dscreate" value="true"/>
				<input type="hidden" name="domainid" value="{$domainid}"/>
				<input name="freednshosting" value="{$freednshosting}" type="hidden"/>
				<input type="hidden" name="nsrecordtype" value="{$recordtype}"/>
				<input type="hidden" name="domain" value="{$domain}"/>
				<p align="center"><input type="submit" value="Add Record" class="btn btn-success"/></p>
			</td>
		</tr>
	</table>
	</form>
</div>