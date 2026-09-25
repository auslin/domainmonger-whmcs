<style>
.dm-email-catchall-page .card {
    border: 1px solid #d9e0e7;
    border-radius: 5px;
    background: #fff;
}
.dm-email-catchall-page .card-header {
    padding: 11px 15px;
    border-radius: 4px 4px 0 0;
    background: #163a5f;
    color: #fff;
    font-size: 16px;
    font-weight: 700;
}
.dm-email-catchall-page .card-body {
    padding: 16px;
}
.dm-email-catchall-page .dm-catchall-help {
    margin: 0 0 16px;
    padding: 11px 13px;
    border: 1px solid #b8cbe0;
    border-radius: 4px;
    background: #eef5fb;
    color: #163a5f;
}
.dm-email-catchall-page .dm-catchall-current {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    margin: 0 0 14px;
}
.dm-email-catchall-page .dm-catchall-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 9px;
    border-radius: 999px;
    background: #2f7d4a;
    color: #fff;
    font-size: 12px;
    font-weight: 700;
}
.dm-email-catchall-page label {
    display: block;
    margin-bottom: 6px;
    color: #163a5f;
    font-weight: 700;
}
.dm-email-catchall-page select.form-control {
    width: 100%;
    max-width: 620px;
    color: #163a5f;
    background: #fff;
}
.dm-email-catchall-page .dm-catchall-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 15px;
}
.dm-email-catchall-page .dm-catchall-save {
    border-color: #f58220 !important;
    background: #f58220 !important;
    color: #fff !important;
}
.dm-email-catchall-page .dm-catchall-save:hover,
.dm-email-catchall-page .dm-catchall-save:focus {
    border-color: #d8741f !important;
    background: #d8741f !important;
    color: #fff !important;
}
.dm-email-catchall-page .dm-catchall-disable {
    border-color: #b94a48 !important;
    background: #b94a48 !important;
    color: #fff !important;
}
.dm-email-catchall-page .dm-catchall-disable:hover,
.dm-email-catchall-page .dm-catchall-disable:focus {
    border-color: #a94442 !important;
    background: #a94442 !important;
    color: #fff !important;
}
.dm-email-catchall-page .dm-catchall-back {
    border-color: #163a5f !important;
    background: #163a5f !important;
    color: #fff !important;
    text-decoration: none !important;
}
.dm-email-catchall-page .dm-catchall-back:hover,
.dm-email-catchall-page .dm-catchall-back:focus {
    border-color: #214e7a !important;
    background: #214e7a !important;
    color: #fff !important;
}
</style>

<div class="dm-email-catchall-page">
    <div class="card">
        <div class="card-header">Set or Change Catch-all</div>
        <div class="card-body">
            <div class="dm-catchall-help">
                Choose the existing email address that should receive messages sent to addresses that do not otherwise exist for <strong>{$domain}</strong>.
            </div>

            {if $catchallerror}
                <div class="alert alert-danger">
                    <strong>Catch-all was not updated.</strong>
                    <ul>{$catchallerror}</ul>
                </div>
            {/if}

            {if $catchallsuccess}
                <div class="alert alert-success">
                    <strong>Catch-all updated.</strong>
                    <ul>{$catchallsuccess}&nbsp;{$smarty.post.selectascatchall}</ul>
                </div>
            {/if}

            {if $iscatchallactive eq "true" && $catchallmailaccount}
                <div class="dm-catchall-current">
                    <strong>Current catch-all:</strong>
                    <span class="dm-catchall-badge">{$catchallmailaccount}</span>
                </div>
                <form method="post" action="emailmanagement.php?action=managemailhosting">
                    <input type="hidden" name="deactivatecatchall" value="true" />
                    <input type="hidden" name="domainid" value="{$domainid}" />
                    <input type="hidden" name="domain" value="{$domain}" />
                    <input type="hidden" name="freemailhosting" value="{$freemailhosting}" />
                    <div class="dm-catchall-actions">
                        <button type="submit" class="btn dm-catchall-disable" onclick="return window.confirm('Disable catch-all email forwarding? Messages sent to addresses that do not exist will no longer be forwarded.');">Disable Catch-all</button>
                    </div>
                </form>
            {/if}

            {if !$popaccounts}
                <div class="alert alert-warning">
                    Create an email forward or mailbox before setting a catch-all address.
                </div>
                <div class="dm-catchall-actions">
                    <a class="btn dm-catchall-back" href="clientarea.php?action=domainemailforwarding&amp;domainid={$domainid}&amp;id={$domainid}&amp;domain={$domain}">Back to Email Forwarding</a>
                </div>
            {else}
                <form method="post" action="emailmanagement.php?action=catchall">
                    <input type="hidden" name="domainid" value="{$domainid}" />
                    <input type="hidden" name="domain" value="{$domain}" />
                    <input type="hidden" name="freemailhosting" value="{$freemailhosting}" />
                    <input type="hidden" name="setcatchallaccount" value="true" />

                    <label for="dm-select-catchall">Catch-all destination</label>
                    <select id="dm-select-catchall" class="form-control" name="selectascatchall">
                        {foreach from=$popaccounts item=mailaddress}
                            <option value="{$mailaddress}"{if $catchallmailaccount eq $mailaddress} selected="selected"{/if}>{$mailaddress}</option>
                        {/foreach}
                    </select>

                    <div class="dm-catchall-actions">
                        <button type="submit" class="btn dm-catchall-save">Set Catch-all</button>
                        <a class="btn dm-catchall-back" href="clientarea.php?action=domainemailforwarding&amp;domainid={$domainid}&amp;id={$domainid}&amp;domain={$domain}">Back to Email Forwarding</a>
                    </div>
                </form>
            {/if}
        </div>
    </div>
</div>
