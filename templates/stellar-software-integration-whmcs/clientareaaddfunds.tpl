{if $addfundsdisabled}
    {include file="$template/includes/alert.tpl" type="error" msg="{lang key='clientareaaddfundsdisabled'}" textcenter=true}
{elseif $notallowed}
    {include file="$template/includes/alert.tpl" type="error" msg="{lang key='clientareaaddfundsnotallowed'}" textcenter=true}
{elseif $errormessage}
    {include file="$template/includes/alert.tpl" type="error" errorshtml=$errormessage textcenter=true}
{/if}

{if !$addfundsdisabled}

    <div class="dm-add-funds-page">

        <div class="card dm-add-funds-card dm-add-funds-limits">
            <div class="card-header">
                <h3 class="card-title mb-0">{lang key='addfunds'}</h3>
            </div>
            <div class="card-body">
                <table class="table table-list table-striped mb-0">
                    <tbody>
                        {if isset($dmAccountCreditFormatted)}
                        <tr class="dm-account-credit-row-1688">
                            <th scope="row">Current Account Credit</th>
                            <td><strong>{$dmAccountCreditFormatted|escape}</strong></td>
                        </tr>
                        {/if}
                        <tr>
                            <th scope="row">{lang key='addfundsminimum'}</th>
                            <td>{$minimumamount}</td>
                        </tr>
                        <tr>
                            <th scope="row">{lang key='addfundsmaximum'}</th>
                            <td>{$maximumamount}</td>
                        </tr>
                        <tr>
                            <th scope="row">{lang key='addfundsmaximumbalance'}</th>
                            <td>{$maximumbalance}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card dm-add-funds-card dm-add-funds-form">
            <div class="card-header">
                <h3 class="card-title mb-0">{lang key='addfunds'}</h3>
            </div>
            <div class="card-body">
                <form method="post" action="{$smarty.server.PHP_SELF}?action=addfunds">
                    <fieldset>
                        <div class="form-group">
                            <label for="amount" class="col-form-label">{lang key='addfundsamount'}:</label>
                            <input type="text" name="amount" id="amount"
                                   value="{$amount}" class="form-control" required />
                        </div>
                        <div class="form-group">
                            <label for="paymentmethod" class="col-form-label">{lang key='orderpaymentmethod'}:</label>
                            <select name="paymentmethod" id="paymentmethod" class="form-control custom-select">
                                {foreach $gateways as $gateway}
                                    <option value="{$gateway.sysname}">{$gateway.name}</option>
                                {/foreach}
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">
                            {lang key='addfunds'}
                        </button>
                    </fieldset>
                </form>
            </div>
            <div class="card-footer">
                <small>{lang key='addfundsnonrefundable'}</small>
            </div>
        </div>

    </div>

{/if}
