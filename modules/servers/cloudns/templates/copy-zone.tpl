{assign var="path" value="../modules/servers/cloudns/templates"}
{if $version gte '6'}
    {assign var="path" value="./"}
{/if}

{include file="$path/header-settings.tpl"}

<style type="text/css">
{literal}
.cloudns-copy-zone-panel{border:1px solid #d8dee5;border-radius:7px;background:#fff;padding:18px;margin-bottom:15px;text-align:left}
.cloudns-copy-zone-panel h4{margin:0 0 14px;color:#163a5f;font-weight:700}
.cloudns-copy-zone-response{padding:10px 12px;margin-bottom:12px;border:1px solid #bce8f1;border-radius:4px;background:#d9edf7;color:#31708f}
.cloudns-copy-zone-response-error{border-color:#ebccd1;background:#f2dede;color:#a94442}
.cloudns-copy-zone-response-success{border-color:#c8e5cc;background:#edf8ef;color:#2f6f3e}
.cloudns-copy-zone-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.cloudns-copy-zone-field label{display:block;margin:0 0 6px;color:#24384d;font-weight:600}
.cloudns-copy-zone-field input[type=text],.cloudns-copy-zone-field select{display:block;width:100%;height:38px;border:1px solid #b8c2cc;border-radius:5px;background:#fff;padding:8px 10px;box-sizing:border-box;color:#1f2933}
.cloudns-copy-zone-options{margin-top:16px;padding:14px;background:#f7f9fb;border:1px solid #e2e7ec;border-radius:6px}
.cloudns-copy-zone-check{display:flex!important;align-items:flex-start;gap:8px;margin:0 0 10px!important;color:#24384d;font-weight:600!important}
.cloudns-copy-zone-check input{margin:3px 0 0}
.cloudns-copy-zone-help{display:block;color:#68737e;font-size:12px;font-weight:400;margin-top:2px}
.cloudns-copy-zone-mode{margin:14px 0}
.cloudns-copy-zone-mode label{display:flex!important;align-items:flex-start;gap:8px;margin:0 0 10px!important;font-weight:600!important;color:#24384d}
.cloudns-copy-zone-mode input{margin:3px 0 0}
.cloudns-copy-zone-danger{padding:10px 12px;border-left:4px solid #b94a48;background:#f8e9e8;color:#8b3735;margin-top:8px}
.cloudns-copy-zone-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:18px}
.cloudns-copy-zone-actions .btn-primary{background:#f58220;border-color:#f58220;color:#fff}
.cloudns-copy-zone-actions .btn-primary:hover,.cloudns-copy-zone-actions .btn-primary:focus{background:#d8741f;border-color:#d8741f;color:#fff}
@media(max-width:760px){.cloudns-copy-zone-grid{grid-template-columns:1fr}.cloudns-copy-zone-actions{flex-direction:column}.cloudns-copy-zone-actions .btn{width:100%}}
{/literal}
</style>

{if isset($copyResponse) && is_array($copyResponse) && isset($copyResponse.description) && $copyResponse.description != ''}
    <div class="cloudns-copy-zone-response {if isset($copyResponse.status) && $copyResponse.status == 'error'}cloudns-copy-zone-response-error{else}cloudns-copy-zone-response-success{/if}">
        {$copyResponse.description|@htmlspecialchars}
        {if isset($copyResponse.destination_created) && $copyResponse.destination_created} The destination zone was created and attached to this DNSPlus service.{/if}
        {if isset($copyResponse.status) && $copyResponse.status == 'success'}
            {if isset($copyResponse.records_deleted) && $copyResponse.records_deleted gt 0} Matching records removed: {$copyResponse.records_deleted}.{/if}
            {if isset($copyResponse.wr_added) && $copyResponse.wr_added gt 0} Web Redirects added separately: {$copyResponse.wr_added}.{/if}
            {if isset($copyResponse.forwards_added) && $copyResponse.forwards_added gt 0} Mail Forwards added: {$copyResponse.forwards_added}.{/if}
            {if isset($copyResponse.hsts) && $copyResponse.hsts == 'active'} HSTS copied as Active.{elseif isset($copyResponse.hsts) && $copyResponse.hsts == 'inactive'} HSTS copied as Inactive.{elseif isset($copyResponse.hsts) && $copyResponse.hsts == 'destination-freessl-inactive'} HSTS was not copied because FreeSSL is not active on the destination.{elseif isset($copyResponse.hsts) && $copyResponse.hsts == 'source-unavailable'} HSTS state was not available from the source zone.{/if}
        {/if}
    </div>
{/if}

<div class="cloudns-copy-zone-panel">
    <h4>Copy DNS Zone</h4>
    <p>Copy DNS records from <strong>{$zone|@htmlspecialchars}</strong> to another DNSPlus zone.</p>

    <form method="post" action="clientarea.php?action=productdetails&id={$serviceid}&customAction=copy-zone-execute&zone={$zone|@urlencode}" id="cloudns-copy-zone-form">
        <div class="cloudns-copy-zone-grid">
            <div class="cloudns-copy-zone-field">
                <label>Source Zone</label>
                <input type="text" value="{$zone|@htmlspecialchars}" disabled="disabled">
            </div>
            <div class="cloudns-copy-zone-field">
                <label for="cloudns-copy-destination">Destination Zone</label>
                <input id="cloudns-copy-destination" type="text" name="destination_zone" value="{if isset($copyForm.destination)}{$copyForm.destination|@htmlspecialchars}{/if}" placeholder="example.net" autocomplete="off" required>
            </div>
        </div>

        <div class="cloudns-copy-zone-options">
            <label class="cloudns-copy-zone-check"><input type="checkbox" name="create_if_missing" value="1"{if !isset($copyForm.create_if_missing) || $copyForm.create_if_missing == '1'} checked="checked"{/if}><span>Create destination zone if it does not exist<span class="cloudns-copy-zone-help">The new master zone is created under this same DNSPlus service before records are copied.</span></span></label>

            <div class="cloudns-copy-zone-mode">
                <strong>Copy Mode</strong>
                <label><input type="radio" name="copy_mode" value="replace_matching"{if !isset($copyForm.mode) || $copyForm.mode == 'replace_matching'} checked="checked"{/if}><span>Replace Matching Records<span class="cloudns-copy-zone-help">Replace destination records with the same record type and host, while leaving unrelated records in place.</span></span></label>
                <label><input type="radio" name="copy_mode" value="add_alongside"{if isset($copyForm.mode) && $copyForm.mode == 'add_alongside'} checked="checked"{/if}><span>Add Alongside Existing Records<span class="cloudns-copy-zone-help">Keep existing destination records and add the source records beside them.</span></span></label>
                <label><input type="radio" name="copy_mode" value="replace_entire"{if isset($copyForm.mode) && $copyForm.mode == 'replace_entire'} checked="checked"{/if}><span>Replace Entire Zone<span class="cloudns-copy-zone-help">Clear the destination zone contents and replace them with the source zone.</span></span></label>
                <div class="cloudns-copy-zone-danger" id="cloudns-copy-zone-danger" style="display:none"><strong>Replace Entire Zone is destructive.</strong> Existing destination DNS records and Mail Forwards will be removed.</div>
            </div>

            <label class="cloudns-copy-zone-check"><input type="checkbox" name="follow_domain" value="1"{if !isset($copyForm.follow_domain) || $copyForm.follow_domain == '1'} checked="checked"{/if}><span>Update records to follow the new domain name<span class="cloudns-copy-zone-help">Uses DNSPlus follow-domain handling for records such as CNAME/MX and also follows the destination domain for copied WR/Mail Forward references where applicable.</span></span></label>
            <label class="cloudns-copy-zone-check"><input type="checkbox" name="copy_wr" value="1"{if !isset($copyForm.copy_wr) || $copyForm.copy_wr == '1'} checked="checked"{/if}><span>Copy Web Redirect (WR) records<span class="cloudns-copy-zone-help">DNSPlus verifies WR records after the DNSPlus copy and adds any that DNSPlus skipped.</span></span></label>
            <label class="cloudns-copy-zone-check"><input type="checkbox" name="copy_forwards" value="1"{if !isset($copyForm.copy_forwards) || $copyForm.copy_forwards == '1'} checked="checked"{/if}><span>Copy Mail Forwards<span class="cloudns-copy-zone-help">Mail Forwards are copied separately by DNSPlus.</span></span></label>
            <label class="cloudns-copy-zone-check"><input type="checkbox" name="copy_hsts" value="1"{if isset($copyForm.copy_hsts) && $copyForm.copy_hsts == '1'} checked="checked"{/if}><span>Copy HSTS setting when available<span class="cloudns-copy-zone-help">Does not activate FreeSSL. HSTS is copied only when FreeSSL is already active on the destination.</span></span></label>
        </div>

        <div class="cloudns-copy-zone-actions">
            <a class="btn cloudns-switch-style-button cloudns-btn-secondary" href="clientarea.php?action=productdetails&id={$serviceid}&customAction=zone-settings&zone={$zone|@urlencode}">Cancel</a>
            <button type="submit" class="btn btn-primary cloudns-switch-style-button cloudns-btn-primary" onclick="return cloudnsConfirmCopyZone();">Copy Zone</button>
        </div>
    </form>
</div>

<script type="text/javascript">
{literal}
(function(){
    var radios=document.querySelectorAll('input[name="copy_mode"]');
    var warning=document.getElementById('cloudns-copy-zone-danger');
    function update(){
        var checked=document.querySelector('input[name="copy_mode"]:checked');
        if(warning){warning.style.display=checked&&checked.value==='replace_entire'?'block':'none';}
    }
    for(var i=0;i<radios.length;i++){radios[i].addEventListener('change',update);} update();
}());
function cloudnsConfirmCopyZone(){
    var checked=document.querySelector('input[name="copy_mode"]:checked');
    if(checked&&checked.value==='replace_entire'){
        return window.confirm('Replace the entire destination zone? Existing destination DNS records and Mail Forwards will be removed.');
    }
    return true;
}
{/literal}
</script>
