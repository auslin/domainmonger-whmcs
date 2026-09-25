{* DomainMonger Patch 1687: page-specific My Invoices sidebar action alignment. *}
<style id="dm-invoices-sidebar-actions-1687">
body.whmcsbody.whmcs-templatefile-clientareainvoices #main-body .sidebar .card-footer {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    flex-wrap: nowrap !important;
    gap: 4px !important;
    padding-left: 8px !important;
    padding-right: 8px !important;
}

body.whmcsbody.whmcs-templatefile-clientareainvoices #main-body .sidebar .card-footer > *,
body.whmcsbody.whmcs-templatefile-clientareainvoices #main-body .sidebar .card-footer .btn {
    float: none !important;
    margin-top: 0 !important;
    margin-bottom: 0 !important;
}
</style>



{* DomainMonger Patch 1688: show current account credit in the invoices sidebar card. *}
{if isset($dmAccountCreditFormatted)}
<style id="dm-invoices-account-credit-1688">
#dm-account-credit-invoices-source-1688 {
    display: none;
}

body.whmcsbody.whmcs-templatefile-clientareainvoices #main-body .sidebar .dm-account-credit-summary-1688 {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 12px;
    background: #fff;
    border-top: 1px solid rgba(0, 0, 0, 0.08);
}

body.whmcsbody.whmcs-templatefile-clientareainvoices #main-body .sidebar .dm-account-credit-label-1688 {
    color: #163a5f;
    font-weight: 600;
}

body.whmcsbody.whmcs-templatefile-clientareainvoices #main-body .sidebar .dm-account-credit-value-1688 {
    font-weight: 700;
    white-space: nowrap;
}
</style>

<div id="dm-account-credit-invoices-source-1688">
    <div class="dm-account-credit-summary-1688">
        <span class="dm-account-credit-label-1688">Account Credit</span>
        <strong class="dm-account-credit-value-1688">{$dmAccountCreditFormatted|escape}</strong>
    </div>
</div>

<script>
jQuery(function ($) {
    var $source = $('#dm-account-credit-invoices-source-1688');
    var $summary = $source.children('.dm-account-credit-summary-1688').first();
    var $addFunds = $('#main-body .sidebar a[href*="action=addfunds"]').first();
    var $footer = $addFunds.closest('.card-footer');

    if ($summary.length && $footer.length && !$footer.parent().children('.dm-account-credit-summary-1688').length) {
        $summary.insertBefore($footer);
    }

    $source.remove();
});
</script>
{/if}

{include file="$template/includes/tablelist.tpl" tableName="InvoicesList" filterColumn="4"}

<script>
    jQuery(document).ready(function() {
        var table = jQuery('#tableInvoicesList').show().DataTable();

        {if $orderby == 'default'}
            table.order([4, 'desc'], [2, 'asc']);
        {elseif $orderby == 'invoicenum'}
            table.order(0, '{$sort}');
        {elseif $orderby == 'date'}
            table.order(1, '{$sort}');
        {elseif $orderby == 'duedate'}
            table.order(2, '{$sort}');
        {elseif $orderby == 'total'}
            table.order(3, '{$sort}');
        {elseif $orderby == 'status'}
            table.order(4, '{$sort}');
        {/if}
        table.draw();
        jQuery('#tableLoading').hide();
    });
</script>

<div class="table-container clearfix">
    <table id="tableInvoicesList" class="table table-list w-hidden">
        <thead>
            <tr>
                <th><span class="dm-invoice-th-label">{lang key='invoicestitle'}</span><span class="dm-invoice-sort-stack" aria-hidden="true"><span class="dm-invoice-sort-up"></span><span class="dm-invoice-sort-down"></span></span></th>
                <th><span class="dm-invoice-th-label">{lang key='invoicesdatecreated'}</span><span class="dm-invoice-sort-stack" aria-hidden="true"><span class="dm-invoice-sort-up"></span><span class="dm-invoice-sort-down"></span></span></th>
                <th><span class="dm-invoice-th-label">{lang key='invoicesdatedue'}</span><span class="dm-invoice-sort-stack" aria-hidden="true"><span class="dm-invoice-sort-up"></span><span class="dm-invoice-sort-down"></span></span></th>
                <th><span class="dm-invoice-th-label">{lang key='invoicestotal'}</span><span class="dm-invoice-sort-stack" aria-hidden="true"><span class="dm-invoice-sort-up"></span><span class="dm-invoice-sort-down"></span></span></th>
                <th><span class="dm-invoice-th-label">{lang key='invoicesstatus'}</span><span class="dm-invoice-sort-stack" aria-hidden="true"><span class="dm-invoice-sort-up"></span><span class="dm-invoice-sort-down"></span></span></th>
            </tr>
        </thead>
        <tbody>
            {foreach $invoices as $invoice}
                <tr onclick="clickableSafeRedirect(event, 'viewinvoice.php?id={$invoice.id}', false)">
                    <td>{$invoice.invoicenum}</td>
                    <td><span class="w-hidden">{$invoice.normalisedDateCreated}</span>{$invoice.datecreated}</td>
                    <td><span class="w-hidden">{$invoice.normalisedDateDue}</span>{$invoice.datedue}</td>
                    <td data-order="{$invoice.totalnum}">{$invoice.total}</td>
                    <td><span class="label status status-{$invoice.statusClass}">{$invoice.status}</span></td>
                </tr>
            {/foreach}
        </tbody>
    </table>
    <div class="text-center" id="tableLoading">
        <p><i class="fas fa-spinner fa-spin"></i> {lang key='loading'}</p>
    </div>
</div>
