<style>
.dm-rc-domain-forwarding .dm-rc-card {
    background: #fff;
    border: 1px solid #d9e2ec;
    border-radius: 4px;
    margin-bottom: 18px;
}
.dm-rc-domain-forwarding .dm-rc-card-heading {
    background: #163a5f;
    color: #fff;
    font-weight: 600;
    padding: 10px 14px;
    border-radius: 4px 4px 0 0;
}
.dm-rc-domain-forwarding .dm-rc-card-body {
    padding: 16px;
}
.dm-rc-domain-forwarding .dm-rc-actions {
    margin-top: 14px;
}
.dm-rc-domain-forwarding .dm-rc-table-wrap {
    width: 100%;
    overflow-x: auto;
}
.dm-rc-domain-forwarding .table {
    width: 100%;
    max-width: 100%;
    margin-left: 0 !important;
    margin-right: 0 !important;
}
.dm-rc-domain-forwarding .table th,
.dm-rc-domain-forwarding .table td {
    vertical-align: top;
}
.dm-rc-domain-forwarding .dm-rc-label-cell {
    width: 240px;
    min-width: 190px;
    font-weight: 600;
    color: #163a5f;
}
.dm-rc-domain-forwarding textarea.form-control {
    min-height: 110px;
}
.dm-rc-domain-forwarding .radio-inline + .radio-inline {
    margin-left: 18px;
}
@media (max-width: 767px) {
    .dm-rc-domain-forwarding .dm-rc-label-cell {
        width: 160px;
        min-width: 150px;
    }
    .dm-rc-domain-forwarding .dm-rc-table-wrap > .table {
        min-width: 640px;
    }
}
</style>

<div class="dm-rc-domain-forwarding">

{if $fwderror}
    <div class="alert alert-danger">
        <strong>Error</strong>
        <div>{$fwderror}</div>
    </div>
{/if}

{if $fwdsuccess}
    <div class="alert alert-success">
        <strong>Success</strong>
        <div>{$fwdsuccess}</div>
    </div>
{/if}

{if $nschangeerror}
    <div class="alert alert-danger">
        <strong>Nameserver Error</strong>
        <div>{$nschangeerror}</div>
    </div>
{/if}

{if $nschangesuccess}
    <div class="alert alert-success">
        <strong>Nameservers Updated</strong>
        <div>{$nschangesuccess}</div>
    </div>
{/if}

{if $isactivated}

    <div class="dm-rc-card">
        <div class="dm-rc-card-heading">Domain Forwarding</div>
        <div class="dm-rc-card-body">
            <p>Domain Forwarding is not currently active for <strong>{$domain}</strong>. Activate Domain Forwarding to forward visitors from this domain to another URL.</p>
            <form method="post" action="domainforwarding.php?action=managedomfwd">
                <input type="hidden" name="id" value="{if $domainid}{$domainid}{else}{$id}{/if}" />
                <input type="hidden" name="domain" value="{$domain}" />
                <input type="hidden" name="activatefwd" value="true" />
                <div class="dm-rc-actions">
                    <button type="submit" {if $disabled}{$disabled}{/if} class="btn btn-primary">Activate Domain Forwarding</button>
                </div>
            </form>
        </div>
    </div>

{else}

    <script type="text/javascript">
        function dmRcConfirmDisableForwarding() {
            return confirm('Disable Domain Forwarding for this domain?');
        }
        function dmRcShowDnsCheckForm() {
            jQuery('#dm-rc-dnscheck').slideToggle();
        }
    </script>

    <div class="dm-rc-card">
        <div class="dm-rc-card-heading">Domain Forwarding</div>
        <div class="dm-rc-card-body">
            <form method="post" action="domainforwarding.php?action=managedomfwd" onsubmit="return dmRcConfirmDisableForwarding();">
                <input type="hidden" name="id" value="{if $domainid}{$domainid}{else}{$id}{/if}" />
                <input type="hidden" name="domain" value="{$domain}" />
                <input type="hidden" name="disablefwd" value="true" />
                <button type="submit" class="btn btn-danger">Disable Domain Forwarding</button>
            </form>
        </div>
    </div>

    {if $nsserversok != 1}
        <div class="alert alert-warning">
            This domain may not be using the recommended nameservers for Domain Forwarding.
            <a href="#" onclick="dmRcShowDnsCheckForm();return false;">View DNS requirements</a>
        </div>

        <div id="dm-rc-dnscheck" style="display:none;">
            <div class="dm-rc-card">
                <div class="dm-rc-card-heading">Nameserver Requirements</div>
                <div class="dm-rc-card-body">
                    <p>You can either switch the domain to the recommended nameservers or add the required DNS records manually.</p>

                    <div class="dm-rc-table-wrap">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Current Nameservers</th>
                                    <th>Recommended Nameservers</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        {foreach key=num item=actualdns from=$configurednsservers.0}
                                            {$actualdns}<br />
                                        {foreachelse}
                                            No current nameservers returned.
                                        {/foreach}
                                    </td>
                                    <td>
                                        {foreach key=num item=defaultdns from=$requirednsservers.0}
                                            {$defaultdns}<br />
                                        {foreachelse}
                                            No recommended nameservers returned.
                                        {/foreach}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <form method="post" action="domainforwarding.php?action=managedomfwd">
                        <input type="hidden" name="id" value="{if $domainid}{$domainid}{else}{$id}{/if}" />
                        <input type="hidden" name="domain" value="{$domain}" />
                        {foreach key=num item=defaultdns from=$requirednsservers.0}
                            <input type="hidden" name="ns[]" value="{$defaultdns}" />
                        {/foreach}
                        <input type="hidden" name="changens" value="true" />
                        <button type="submit" {if $nsserversok == 1}disabled="disabled"{/if} class="btn btn-primary">Use Recommended Nameservers</button>
                    </form>
                </div>
            </div>

            <div class="dm-rc-card">
                <div class="dm-rc-card-heading">Required DNS Records</div>
                <div class="dm-rc-card-body">
                    <div class="dm-rc-table-wrap">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Record Type</th>
                                    <th>Hostname</th>
                                    <th>Value / IP Address</th>
                                    <th>TTL</th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach key=num item=service from=$requiredarecords}
                                    <tr>
                                        <td>{$service.type}</td>
                                        <td>{$service.host}</td>
                                        <td>{$service.value}</td>
                                        <td>{$service.timetolive}</td>
                                    </tr>
                                {foreachelse}
                                    <tr>
                                        <td colspan="4">No required DNS records were returned.</td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    {/if}

    <form method="post" action="domainforwarding.php?action=managedomfwd">
        <div class="dm-rc-card">
            <div class="dm-rc-card-heading">Forwarding Settings</div>
            <div class="dm-rc-card-body">
                <div class="dm-rc-table-wrap">
                    <table class="table table-bordered table-hover">
                        <tbody>
                            <tr>
                                <td class="dm-rc-label-cell">Destination URL</td>
                                <td>
                                    <p>Enter the full URL where this domain should forward.</p>
                                    <input name="forward" class="form-control" type="text" value="{if $forward}{$forward}{else}http://{/if}" />
                                </td>
                            </tr>
                            <tr>
                                <td class="dm-rc-label-cell">URL Masking</td>
                                <td>
                                    <p>Choose whether the destination URL should be hidden in the browser address bar.</p>
                                    <label class="radio-inline"><input name="urlmask" type="radio" value="true" {if $urlmasking=="true"}checked="checked"{/if} /> Yes</label>
                                    <label class="radio-inline"><input name="urlmask" type="radio" value="false" {if $urlmasking=="false"}checked="checked"{/if} /> No</label>
                                </td>
                            </tr>
                            <tr>
                                <td class="dm-rc-label-cell">Header Tags</td>
                                <td>
                                    <p>Optional HTML header tags used when URL masking is enabled.</p>
                                    <textarea class="form-control" name="headertags" id="headertags">{$headertags}</textarea>
                                </td>
                            </tr>
                            <tr>
                                <td class="dm-rc-label-cell">No Frames Content</td>
                                <td>
                                    <p>Optional content shown when a visitor's browser does not support frames.</p>
                                    <textarea class="form-control" name="noframes" id="noframes">{$noframes}</textarea>
                                </td>
                            </tr>
                            <tr>
                                <td class="dm-rc-label-cell">Subdomain Forwarding</td>
                                <td>
                                    <p>Forward subdomains along with this domain.</p>
                                    <label class="radio-inline"><input name="subforwarding" type="radio" value="true" {if $subforwarding=="true"}checked="checked"{/if} /> Yes</label>
                                    <label class="radio-inline"><input name="subforwarding" type="radio" value="false" {if $subforwarding=="false"}checked="checked"{/if} /> No</label>
                                </td>
                            </tr>
                            <tr>
                                <td class="dm-rc-label-cell">Path Forwarding</td>
                                <td>
                                    <p>Preserve and forward the path after the domain name.</p>
                                    <label class="radio-inline"><input name="pathforwarding" type="radio" value="true" {if $pathforwarding=="true"}checked="checked"{/if} /> Yes</label>
                                    <label class="radio-inline"><input name="pathforwarding" type="radio" value="false" {if $pathforwarding=="false"}checked="checked"{/if} /> No</label>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <input type="hidden" name="cfgdomfwd" value="true" />
                <input type="hidden" name="domain" value="{$domain}" />
                <input type="hidden" name="id" value="{if $domainid}{$domainid}{else}{$id}{/if}" />
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </form>

{/if}

</div>
