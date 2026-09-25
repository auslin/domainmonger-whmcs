<div class="card">
    <div class="card-body">
        <h3 class="card-title">{lang key='domaindnsmanagement'}</h3>

        {if $error}
            {include file="$template/includes/alert.tpl" type="error" msg=$error}
        {/if}

        {if $external}
            <div class="text-center px-4">
                {$code}
            </div>
        {else}

            <form method="post" action="{$smarty.server.PHP_SELF}?action=domaindns">
                <input type="hidden" name="sub" value="save" />
                <input type="hidden" name="domainid" value="{$domainid}" />

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th class="dm-dns-select-col"><input type="checkbox" class="dm-dns-select-all" aria-label="Select all DNS records" /></th>
                            <th>Host</th>
                            <th>Type</th>
                            <th>Value</th>
                            <th>TTL</th>
                            <th>{lang key='domaindnspriority'}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach $dnsrecords as $dnsrecord}
                            <tr>
                                <td class="dm-dns-select-col"><input type="checkbox" class="dm-dns-row-select" aria-label="Select DNS record" /></td>
                                <td><input type="hidden" name="dnsrecid[]" value="{$dnsrecord.recid}" /><input type="text" name="dnsrecordhost[]" value="{$dnsrecord.hostname}" size="10" class="form-control" /></td>
                                <td>
                                    <select name="dnsrecordtype[]" class="form-control">
                                        <option value="A"{if $dnsrecord.type eq "A"} selected="selected"{/if}>{lang key="domainDns.a"}</option>
                                        <option value="AAAA"{if $dnsrecord.type eq "AAAA"} selected="selected"{/if}>{lang key="domainDns.aaaa"}</option>
                                        <option value="MXE"{if $dnsrecord.type eq "MXE"} selected="selected"{/if}>{lang key="domainDns.mxe"}</option>
                                        <option value="MX"{if $dnsrecord.type eq "MX"} selected="selected"{/if}>{lang key="domainDns.mx"}</option>
                                        <option value="NS"{if $dnsrecord.type eq "NS"} selected="selected"{/if}>NS</option>
                                        <option value="SRV"{if $dnsrecord.type eq "SRV"} selected="selected"{/if}>SRV</option>
                                        <option value="CNAME"{if $dnsrecord.type eq "CNAME"} selected="selected"{/if}>{lang key="domainDns.cname"}</option>
                                        <option value="TXT"{if $dnsrecord.type eq "TXT"} selected="selected"{/if}>{lang key="domainDns.txt"}</option>
                                        <option value="URL"{if $dnsrecord.type eq "URL"} selected="selected"{/if}>{lang key="domainDns.url"}</option>
                                        <option value="FRAME"{if $dnsrecord.type eq "FRAME"} selected="selected"{/if}>{lang key="domainDns.frame"}</option>
                                    </select>
                                </td>
                                <td><input type="text" name="dnsrecordaddress[]" value="{$dnsrecord.address|escape:'html'}" size="40" class="form-control" /></td>
                                <td><input type="text" name="dnsrecordttl[]" value="{$dnsrecord.ttl|default:'14400'}" size="6" class="form-control dm-dns-ttl-input" /></td>
                                <td><input type="text" name="dnsrecordpriority[]" value="{$dnsrecord.priority|default:'N/A'}" size="4" class="form-control dm-dns-priority-input" /></td>
                            </tr>
                        {/foreach}
                        <tr class="dm-dns-new-record-row">
                            <td class="dm-dns-select-col"><input type="checkbox" class="dm-dns-row-select" aria-label="Select new DNS record" /></td>
                            <td><input type="text" name="dnsrecordhost[]" size="10" class="form-control" /></td>
                            <td>
                                <select name="dnsrecordtype[]" class="form-control">
                                    <option value="A">{lang key="domainDns.a"}</option>
                                    <option value="AAAA">{lang key="domainDns.aaaa"}</option>
                                    <option value="MXE">{lang key="domainDns.mxe"}</option>
                                    <option value="MX">{lang key="domainDns.mx"}</option>
                                    <option value="NS">NS</option>
                                    <option value="SRV">SRV</option>
                                    <option value="CNAME">{lang key="domainDns.cname"}</option>
                                    <option value="TXT">{lang key="domainDns.txt"}</option>
                                    <option value="URL">{lang key="domainDns.url"}</option>
                                    <option value="FRAME">{lang key="domainDns.frame"}</option>
                                </select>
                            </td>
                            <td><input type="text" name="dnsrecordaddress[]" size="40" class="form-control" /></td>
                            <td><input type="text" name="dnsrecordttl[]" value="14400" size="6" class="form-control dm-dns-ttl-input" /></td>
                            <td><input type="text" name="dnsrecordpriority[]" size="4" class="form-control dm-dns-priority-input" /></td>
                        </tr>
                    </tbody>
                </table>

                <p class="text-right text-muted">
                    <small>* Priority is normally used for MX and SRV records. TTL support depends on registrar/backend support.</small>
                </p>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary">
                        {lang key='clientareasavechanges'}
                    </button>
                    <button type="reset" class="btn btn-default">
                        {lang key='clientareacancel'}
                    </button>
                </div>

            </form>

        {/if}

    </div>
</div>
