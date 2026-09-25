{if $createSuccess}
    {include file="$template/includes/alert.tpl" type="success" msg="<i class='fas fa-check fa-fw'></i> {lang key='paymentMethods.addedSuccess'}"}
{elseif $createFailed}
    {include file="$template/includes/alert.tpl" type="warning" msg="<i class='fas fa-times fa-fw'></i> {lang key='paymentMethods.addFailed'}"}
{elseif $saveSuccess}
    {include file="$template/includes/alert.tpl" type="success" msg="<i class='fas fa-check fa-fw'></i> {lang key='paymentMethods.updateSuccess'}"}
{elseif $saveFailed}
    {include file="$template/includes/alert.tpl" type="warning" msg="<i class='fas fa-check fa-fw'></i> {lang key='paymentMethods.saveFailed'}"}
{elseif $setDefaultResult === true}
    {include file="$template/includes/alert.tpl" type="success" msg="<i class='fas fa-check fa-fw'></i> {lang key='paymentMethods.defaultUpdateSuccess'}"}
{elseif $setDefaultResult === false}
    {include file="$template/includes/alert.tpl" type="warning" msg="<i class='fas fa-times fa-fw'></i> {lang key='paymentMethods.defaultUpdateFailed'}"}
{elseif $deleteResult === true}
    {include file="$template/includes/alert.tpl" type="success" msg="<i class='fas fa-check fa-fw'></i> {lang key='paymentMethods.deleteSuccess'}"}
{elseif $deleteResult === false}
    {include file="$template/includes/alert.tpl" type="warning" msg="<i class='fas fa-times fa-fw'></i> {lang key='paymentMethods.deleteFailed'}"}
{/if}


<style>
    /* DomainMonger: keep the compact Payment Method action icons optically centered. */
    #payMethodList .dm-paymethod-icon-btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        vertical-align: middle !important;
    }
    #payMethodList .dm-paymethod-icon-btn > i {
        display: block !important;
        margin: 0 !important;
        padding: 0 !important;
        line-height: 1 !important;
        width: auto !important;
    }
</style>

<div class="card">
    <div class="card-body">
        <p>{lang key='paymentMethods.intro'}</p>

        <p>
            {if $allowCreditCard}
                <a href="{routePath('account-paymentmethods-add')}" class="btn btn-primary" data-role="add-new-credit-card">
                    {lang key='paymentMethods.addNewCC'}
                </a>
            {/if}
            {if $allowBankDetails}
                <a href="{routePathWithQuery('account-paymentmethods-add', null, 'type=bankacct')}" class="btn btn-default">
                    {lang key='paymentMethods.addNewBank'}
                </a>
            {/if}
        </p>

        <table class="table table-striped" id="payMethodList">
            <tr>
                <th></th>
                <th>{lang key='paymentMethods.name'}</th>
                <th>{lang key='paymentMethods.description'}</th>
                <th>Expiration</th>
                <th>{lang key='paymentMethods.status'}</th>
                <th>{lang key='paymentMethods.actions'}</th>
            </tr>
            {foreach $client->payMethods->validateGateways() as $payMethod}
                <tr>
                    <td>
                        <i class="{$payMethod->getFontAwesomeIcon()}"></i>
                    </td>
                    <td>{$payMethod->payment->getDisplayName()}</td>
                    <td>
                        {if $payMethod->description}
                            {$payMethod->description}
                        {else}
                            -
                        {/if}
                    </td>
                    <td>
                        {assign "dmExpiryDate" ""}
                        {if $payMethod->isCreditCard()
                            && $payMethod->gateway_name != 'paypal_ppcpv'
                            && $payMethod->gateway_name != 'paypal_acdc'
                            && $payMethod->payment->getExpiryDate()}
                            {assign "dmExpiryDate" $payMethod->payment->getExpiryDate()->format('m/Y')}
                        {/if}
                        {if $dmExpiryDate}
                            {$dmExpiryDate}
                        {else}
                            —
                        {/if}
                    </td>
                    <td>{$payMethod->getStatus()}{if $payMethod->isDefaultPayMethod()} - {lang key='paymentMethods.default'}{/if}</td>
                    <td>
                        <a href="{routePath('account-paymentmethods-setdefault', $payMethod->id)}"
                           class="btn btn-sm btn-default dm-paymethod-icon-btn btn-set-default{if $payMethod->isDefaultPayMethod() || $payMethod->isExpired()} disabled{/if}"
                           title="{if $payMethod->isDefaultPayMethod()}Current Default{else}{lang key='paymentMethods.setAsDefault'}{/if}"
                           aria-label="{if $payMethod->isDefaultPayMethod()}Current Default{else}{lang key='paymentMethods.setAsDefault'}{/if}">
                            <i class="{if $payMethod->isDefaultPayMethod()}fas{else}far{/if} fa-star"></i>
                        </a>
                        <a href="{routePath('account-paymentmethods-view', $payMethod->id)}"
                           class="btn btn-sm btn-default dm-paymethod-icon-btn{if $payMethod->getType() == 'RemoteBankAccount'} disabled{/if}"
                           data-role="edit-payment-method"
                           title="{lang key='paymentMethods.edit'}"
                           aria-label="{lang key='paymentMethods.edit'}">
                            <i class="fas fa-pencil"></i>
                        </a>
                        {if $allowDelete}
                            <a href="{routePath('account-paymentmethods-delete', $payMethod->id)}"
                               class="btn btn-sm btn-default dm-paymethod-icon-btn btn-delete"
                               title="{lang key='paymentMethods.delete'}"
                               aria-label="{lang key='paymentMethods.delete'}">
                                <i class="fas fa-trash"></i>
                            </a>
                        {/if}
                    </td>
                </tr>
            {foreachelse}
                <tr>
                    <td colspan="6" align="center">
                        {lang key='paymentMethods.noPaymentMethodsCreated'}
                    </td>
                </tr>
            {/foreach}
        </table>

    </div>
</div>

<form method="post" action="" id="frmDeletePaymentMethod">
<div class="modal fade" id="modalPaymentMethodDeleteConfirmation" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel">{lang key='paymentMethods.areYouSure'}</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <p>{lang key='paymentMethods.deletePaymentMethodConfirm'}</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">{lang key='no'}</button>
        <button type="submit" class="btn btn-primary">{lang key='yes'}</button>
      </div>
    </div>
  </div>
</div>
</form>

<form method="post" action="" id="frmSetDefaultPaymentMethod"></form>

<script>
    jQuery(document).ready(function() {
        jQuery('.btn-set-default').click(function(e) {
            e.preventDefault();
            jQuery('#frmSetDefaultPaymentMethod')
                .attr('action', jQuery(this).attr('href'))
                .submit();
        });
        jQuery('.btn-delete').click(function(e) {
            e.preventDefault();
            jQuery('#frmDeletePaymentMethod')
                .attr('action', jQuery(this).attr('href'));
            jQuery('#modalPaymentMethodDeleteConfirmation').modal('show');
        });
    });
</script>
