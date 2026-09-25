{include file="$template/includes/tablelist.tpl" tableName="ServicesList" filterColumn="4" noSortColumns="0"}

<script>
    jQuery(document).ready(function() {
        var table = jQuery('#tableServicesList').show().DataTable();

        {if $orderby == 'product'}
            table.order([1, '{$sort}'], [4, 'asc']);
        {elseif $orderby == 'amount' || $orderby == 'billingcycle'}
            table.order(2, '{$sort}');
        {elseif $orderby == 'nextduedate'}
            table.order(3, '{$sort}');
        {elseif $orderby == 'domainstatus'}
            table.order(4, '{$sort}');
        {/if}
        table.draw();
        jQuery('#tableLoading').hide();
    });
</script>

<div class="table-container clearfix">
    <table id="tableServicesList" class="table table-list w-hidden">
        <thead>
            <tr>
                <th></th>
                <th>{lang key='orderproduct'}</th>
                <th>{lang key='clientareaaddonpricing'}</th>
                <th>{lang key='clientareahostingnextduedate'}</th>
                <th>{lang key='clientareastatus'}</th>
            </tr>
        </thead>
        <tbody>
            {foreach $services as $service}
                <tr onclick="clickableSafeRedirect(event, 'clientarea.php?action=productdetails&amp;id={$service.id}', false)">
                    <td class="py-0 text-center{if $service.sslStatus} ssl-info{/if}" data-element-id="{$service.id}" data-type="service"{if $service.domain} data-domain="{$service.domain}"{/if}>
                        {if $service.sslStatus}
                            <img src="{$service.sslStatus->getImagePath()}" data-toggle="tooltip" title="{$service.sslStatus->getTooltipContent()}" class="{$service.sslStatus->getClass()}" width="25">
                        {elseif !$service.isActive}
                            <img src="{$BASE_PATH_IMG}/ssl/ssl-inactive-domain.png" data-toggle="tooltip" title="{lang key='sslState.sslInactiveService'}" width="25">
                        {/if}
                    </td>
                    <td><strong>{$service.product}</strong>{if $service.domain}<br /><a href="http://{$service.domain}" target="_blank">{$service.domain}</a>{else}<br />-{/if}</td>
                    <td class="text-center" data-order="{$service.amountnum}">{$service.amount} <small class="text-muted">{$service.billingcycle}</small></td>
                    <td class="text-center"><span class="w-hidden">{$service.normalisedNextDueDate}</span>{$service.nextduedate}</td>
                    <td class="text-center"><span class="label status status-{$service.status|strtolower}">{$service.statustext}</span></td>
                </tr>
            {/foreach}
        </tbody>
    </table>
    <div class="text-center" id="tableLoading">
        <p><i class="fas fa-spinner fa-spin"></i> {lang key='loading'}</p>
    </div>
</div>


{literal}
<style>
/* Patch 151: targeted Domains/Services sort arrows only.
   Restores the checkbox header/select-all behavior from the template level and avoids
   styling the checkbox/SSL columns. */

body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.dataTable.table-list thead tr th:nth-child(3).sorting::before,
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.dataTable.table-list thead tr th:nth-child(3).sorting_asc::before,
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.dataTable.table-list thead tr th:nth-child(3).sorting_desc::before,
body.whmcsbody.whmcs-loggedin table#tableServicesList#tableServicesList.dataTable.table-list thead tr th:nth-child(2).sorting::before,
body.whmcsbody.whmcs-loggedin table#tableServicesList#tableServicesList.dataTable.table-list thead tr th:nth-child(2).sorting_asc::before,
body.whmcsbody.whmcs-loggedin table#tableServicesList#tableServicesList.dataTable.table-list thead tr th:nth-child(2).sorting_desc::before {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
    content: "" !important;
}

body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.dataTable.table-list thead tr th:nth-child(3) .sorting-indicator,
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.dataTable.table-list thead tr th:nth-child(3) .dt-column-order,
body.whmcsbody.whmcs-loggedin table#tableServicesList#tableServicesList.dataTable.table-list thead tr th:nth-child(2) .sorting-indicator,
body.whmcsbody.whmcs-loggedin table#tableServicesList#tableServicesList.dataTable.table-list thead tr th:nth-child(2) .dt-column-order {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
    width: 0 !important;
    height: 0 !important;
}

/* DOMAIN: larger arrows, more vertical gap, farther from the word. */
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.dataTable.table-list thead tr th:nth-child(3).sorting::after,
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.dataTable.table-list thead tr th:nth-child(3).sorting_asc::after,
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.dataTable.table-list thead tr th:nth-child(3).sorting_desc::after,
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.dataTable.table-list thead tr th[aria-label^="Domain"].sorting::after,
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.dataTable.table-list thead tr th[aria-label^="Domain"].sorting_asc::after,
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.dataTable.table-list thead tr th[aria-label^="Domain"].sorting_desc::after {
    content: "" !important;
    position: static !important;
    display: inline-block !important;
    visibility: visible !important;
    opacity: 1 !important;
    width: 12px !important;
    height: 28px !important;
    margin-left: 24px !important;
    margin-right: 0 !important;
    padding: 0 !important;
    transform: none !important;
    vertical-align: -9px !important;
    background-repeat: no-repeat !important;
    background-position: center center !important;
    background-size: 12px 28px !important;
    border: 0 !important;
    font-size: 0 !important;
    line-height: 0 !important;
}

/* PRODUCT/SERVICE: keep consistent, but do not touch checkbox/SSL columns. */
body.whmcsbody.whmcs-loggedin table#tableServicesList#tableServicesList.dataTable.table-list thead tr th:nth-child(2).sorting::after,
body.whmcsbody.whmcs-loggedin table#tableServicesList#tableServicesList.dataTable.table-list thead tr th:nth-child(2).sorting_asc::after,
body.whmcsbody.whmcs-loggedin table#tableServicesList#tableServicesList.dataTable.table-list thead tr th:nth-child(2).sorting_desc::after {
    content: "" !important;
    position: static !important;
    display: inline-block !important;
    visibility: visible !important;
    opacity: 1 !important;
    width: 10px !important;
    height: 24px !important;
    margin-left: 12px !important;
    margin-right: 0 !important;
    padding: 0 !important;
    transform: none !important;
    vertical-align: -7px !important;
    background-repeat: no-repeat !important;
    background-position: center center !important;
    background-size: 10px 24px !important;
    border: 0 !important;
    font-size: 0 !important;
    line-height: 0 !important;
}

body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.dataTable.table-list thead tr th:nth-child(3).sorting::after,
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.dataTable.table-list thead tr th[aria-label^="Domain"].sorting::after {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='28' viewBox='0 0 12 28'%3E%3Cpath fill='rgba(255,255,255,.78)' d='M6 1L11 9H1z'/%3E%3Cpath fill='rgba(255,255,255,.78)' d='M6 27L1 19h10z'/%3E%3C/svg%3E") !important;
}
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.dataTable.table-list thead tr th:nth-child(3).sorting_asc::after,
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.dataTable.table-list thead tr th[aria-label^="Domain"].sorting_asc::after {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='28' viewBox='0 0 12 28'%3E%3Cpath fill='white' d='M6 1L11 9H1z'/%3E%3Cpath fill='rgba(255,255,255,.42)' d='M6 27L1 19h10z'/%3E%3C/svg%3E") !important;
}
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.dataTable.table-list thead tr th:nth-child(3).sorting_desc::after,
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.dataTable.table-list thead tr th[aria-label^="Domain"].sorting_desc::after {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='28' viewBox='0 0 12 28'%3E%3Cpath fill='rgba(255,255,255,.42)' d='M6 1L11 9H1z'/%3E%3Cpath fill='white' d='M6 27L1 19h10z'/%3E%3C/svg%3E") !important;
}

body.whmcsbody.whmcs-loggedin table#tableServicesList#tableServicesList.dataTable.table-list thead tr th:nth-child(2).sorting::after {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='24' viewBox='0 0 10 24'%3E%3Cpath fill='rgba(255,255,255,.70)' d='M5 1L9 7H1z'/%3E%3Cpath fill='rgba(255,255,255,.70)' d='M5 23L1 17h8z'/%3E%3C/svg%3E") !important;
}
body.whmcsbody.whmcs-loggedin table#tableServicesList#tableServicesList.dataTable.table-list thead tr th:nth-child(2).sorting_asc::after {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='24' viewBox='0 0 10 24'%3E%3Cpath fill='white' d='M5 1L9 7H1z'/%3E%3Cpath fill='rgba(255,255,255,.42)' d='M5 23L1 17h8z'/%3E%3C/svg%3E") !important;
}
body.whmcsbody.whmcs-loggedin table#tableServicesList#tableServicesList.dataTable.table-list thead tr th:nth-child(2).sorting_desc::after {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='24' viewBox='0 0 10 24'%3E%3Cpath fill='rgba(255,255,255,.42)' d='M5 1L9 7H1z'/%3E%3Cpath fill='white' d='M5 23L1 17h8z'/%3E%3C/svg%3E") !important;
}

body.whmcsbody.whmcs-loggedin #dmSelectAllDomains {
    margin: 0 !important;
    vertical-align: middle !important;
}
</style>
{/literal}

