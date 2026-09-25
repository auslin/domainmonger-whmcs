<style>
.dm-rc-dnssec-page > h3:first-of-type {
    background: #163a5f;
    color: #fff;
    font-size: 18px;
    font-weight: 700;
    line-height: 1.2;
    margin: 0 0 14px;
    padding: 14px 16px;
}
.dm-rc-dnssec-page .dm-rc-dnssec-table {
    width: 100%;
    max-width: 100%;
    margin-left: 0 !important;
    margin-right: 0 !important;
}
.dm-rc-dnssec-page .dm-rc-dnssec-table th,
.dm-rc-dnssec-page .dm-rc-dnssec-table td {
    vertical-align: top;
}
.dm-rc-dnssec-page .dm-rc-dnssec-label {
    width: 190px;
    min-width: 190px;
    color: #163a5f;
    font-weight: 700;
    white-space: nowrap;
}
.dm-rc-dnssec-page .dm-rc-dnssec-digest {
    max-width: 360px;
    overflow-wrap: anywhere;
    word-break: break-word;
}
.dm-rc-dnssec-page .dm-rc-dnssec-actions {
    margin: 14px 0 18px;
}
.dm-rc-dnssec-page .dm-rc-dnssec-help {
    display: block;
    margin-top: 6px;
    color: #666;
    font-size: 13px;
}
@media (max-width: 767px) {
    .dm-rc-dnssec-page .dm-rc-dnssec-responsive {
        width: 100%;
        overflow-x: auto;
    }
    .dm-rc-dnssec-page .dm-rc-dnssec-responsive > table {
        min-width: 720px;
    }
}
</style>

<div class="dm-rc-dnssec-page">

{include file="$template/includes/alert.tpl" type="info" msg="DNSSEC records help protect your domain by allowing DNS resolvers to verify that DNS responses have not been modified."}

<script language="javascript" type="text/javascript">
    function dmRcDnssecConfirmDelete(){literal}{{/literal}return confirm("Delete this DNSSEC record?");{literal}}{/literal}
    {literal}function dmRcShowDnssecForm(){jQuery("#dnssecform").slideToggle();}{/literal}
</script>

{if $deleterecorderror}
<br />
<div class="alert alert-danger">
    <p><strong>Error</strong></p>
    <ul>{$deleterecorderror}</ul>
</div>
{/if}

{if $deleterecordsuccess}
<br />
<div class="alert alert-success">
    <p><strong>Success</strong></p>
    <ul>{$deleterecordsuccess}</ul>
</div>
{/if}

{if $addrecorderror}
<br />
<div class="alert alert-danger">
    <p><strong>Error</strong></p>
    <ul>{$addrecorderror}</ul>
</div>
{/if}

{if $addrecordsuccess}
<br />
<div class="alert alert-success">
    <p><strong>Success</strong></p>
    <ul>{$addrecordsuccess}</ul>
</div>
{/if}

<h3>DNSSEC</h3>

<div class="dm-rc-dnssec-responsive">
<table class="table table-bordered table-hover dm-rc-dnssec-table">
    <thead>
        <tr>
            <th>Key Tag</th>
            <th>Algorithm</th>
            <th>Digest Type</th>
            <th>Digest</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    {foreach key=num item=service from=$dnssec_details}
        <tr>
            <td>{$service.keytag}</td>
            <td>{$service.algorithm}</td>
            <td>{$service.digesttype}</td>
            <td><div class="dm-rc-dnssec-digest">{$service.digest}</div></td>
            <td>
                <form method="POST" action="domainmanagement.php?action=managednssec" onsubmit="return dmRcDnssecConfirmDelete();">
                    <input type="hidden" name="dsdelete" value="true"/>
                    <input type="hidden" name="domainid" value="{$domainid}"/>
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
            <td colspan="5">No DNSSEC records found.</td>
        </tr>
    {/foreach}
    </tbody>
</table>
</div>

<div class="dm-rc-dnssec-actions">
    <a href="#" class="btn btn-primary btn-sm" onclick="dmRcShowDnssecForm();return false;">Add DNSSEC Record</a>
</div>

<div style="display:{if $toggleoff}block{else}none{/if};" id="dnssecform">
    <form method="POST" action="domainmanagement.php?action=managednssec">
        <table class="table table-bordered table-hover dm-rc-dnssec-table">
            <tr>
                <td colspan="2">
                    <h3>Add DNSSEC Record</h3>
                </td>
            </tr>
            <tr>
                <td class="dm-rc-dnssec-label">Key Tag</td>
                <td>
                    <input name="keytag" class="form-control" type="text" value="{if $smarty.post.keytag}{$smarty.post.keytag}{/if}" />
                    <span class="dm-rc-dnssec-help">Enter the DNSSEC DS key tag.</span>
                </td>
            </tr>
            <tr>
                <td class="dm-rc-dnssec-label">Algorithm</td>
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
                    <span class="dm-rc-dnssec-help">Choose the algorithm used by the DS record.</span>
                </td>
            </tr>
            <tr>
                <td class="dm-rc-dnssec-label">Digest Type</td>
                <td>
                    <select name="digesttype" class="form-control">
                        <option value="1" {if $smarty.post.digesttype eq 1}selected="selected"{/if}>SHA-1 (1)</option>
                        <option value="2" {if $smarty.post.digesttype eq 2}selected="selected"{/if}>SHA-256 (2)</option>
                        <option value="3" {if $smarty.post.digesttype eq 3}selected="selected"{/if}>GOST R 34.11-94 (3)</option>
                        <option value="4" {if $smarty.post.digesttype eq 4}selected="selected"{/if}>SHA-384 (4)</option>
                    </select>
                    <span class="dm-rc-dnssec-help">Choose the digest type used by the DS record.</span>
                </td>
            </tr>
            <tr>
                <td class="dm-rc-dnssec-label">Digest</td>
                <td>
                    <input name="digest" class="form-control" type="text" value="{if $smarty.post.digest}{$smarty.post.digest}{/if}" />
                    <span class="dm-rc-dnssec-help">Enter the DS record digest.</span>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="text-center">
                    <input type="hidden" name="dscreate" value="true"/>
                    <input type="hidden" name="domainid" value="{$domainid}"/>
                    <input type="hidden" name="domain" value="{$domain}"/>
                    <input type="submit" value="Save DNSSEC Record" class="btn btn-primary"/>
                </td>
            </tr>
        </table>
    </form>
</div>

</div>
