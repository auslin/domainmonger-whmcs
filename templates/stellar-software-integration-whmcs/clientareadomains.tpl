{literal}
<style>
/* Patch 159: Domains list More dropdown light style.
   Replaces the dark dropdown with the cleaner WHMCS/card style. */
body.whmcsbody.whmcs-loggedin #tableDomainsList_wrapper ~ .btn-group .dropdown-menu,
body.whmcsbody.whmcs-loggedin .tab-pane#tabOverview .btn-group .dropdown-menu,
body.whmcsbody.whmcs-loggedin .tab-content #tabOverview .btn-group .dropdown-menu {
    min-width: 190px !important;
    padding: 6px 0 !important;
    background: #ffffff !important;
    background-color: #ffffff !important;
    background-image: none !important;
    border: 1px solid #d7e0ea !important;
    border-radius: 5px !important;
    box-shadow: 0 8px 18px rgba(20, 52, 90, 0.14) !important;
    overflow: hidden !important;
}

body.whmcsbody.whmcs-loggedin .tab-pane#tabOverview .btn-group .dropdown-menu .dropdown-item,
body.whmcsbody.whmcs-loggedin .tab-content #tabOverview .btn-group .dropdown-menu .dropdown-item,
body.whmcsbody.whmcs-loggedin .tab-pane#tabOverview .btn-group .dropdown-menu > a,
body.whmcsbody.whmcs-loggedin .tab-content #tabOverview .btn-group .dropdown-menu > a {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    padding: 9px 13px !important;
    color: #14345a !important;
    background: transparent !important;
    background-color: transparent !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    line-height: 1.35 !important;
    text-decoration: none !important;
    text-shadow: none !important;
    white-space: nowrap !important;
}

body.whmcsbody.whmcs-loggedin .tab-pane#tabOverview .btn-group .dropdown-menu .dropdown-item i,
body.whmcsbody.whmcs-loggedin .tab-content #tabOverview .btn-group .dropdown-menu .dropdown-item i,
body.whmcsbody.whmcs-loggedin .tab-pane#tabOverview .btn-group .dropdown-menu > a i,
body.whmcsbody.whmcs-loggedin .tab-content #tabOverview .btn-group .dropdown-menu > a i {
    width: 15px !important;
    color: #51667d !important;
    text-align: center !important;
}

body.whmcsbody.whmcs-loggedin .tab-pane#tabOverview .btn-group .dropdown-menu .dropdown-item:hover,
body.whmcsbody.whmcs-loggedin .tab-pane#tabOverview .btn-group .dropdown-menu .dropdown-item:focus,
body.whmcsbody.whmcs-loggedin .tab-content #tabOverview .btn-group .dropdown-menu .dropdown-item:hover,
body.whmcsbody.whmcs-loggedin .tab-content #tabOverview .btn-group .dropdown-menu .dropdown-item:focus,
body.whmcsbody.whmcs-loggedin .tab-pane#tabOverview .btn-group .dropdown-menu > a:hover,
body.whmcsbody.whmcs-loggedin .tab-pane#tabOverview .btn-group .dropdown-menu > a:focus,
body.whmcsbody.whmcs-loggedin .tab-content #tabOverview .btn-group .dropdown-menu > a:hover,
body.whmcsbody.whmcs-loggedin .tab-content #tabOverview .btn-group .dropdown-menu > a:focus {
    color: #14345a !important;
    background: #fff4ec !important;
    background-color: #fff4ec !important;
}

body.whmcsbody.whmcs-loggedin .tab-pane#tabOverview .btn-group .dropdown-menu .dropdown-item:hover i,
body.whmcsbody.whmcs-loggedin .tab-pane#tabOverview .btn-group .dropdown-menu .dropdown-item:focus i,
body.whmcsbody.whmcs-loggedin .tab-content #tabOverview .btn-group .dropdown-menu .dropdown-item:hover i,
body.whmcsbody.whmcs-loggedin .tab-content #tabOverview .btn-group .dropdown-menu .dropdown-item:focus i,
body.whmcsbody.whmcs-loggedin .tab-pane#tabOverview .btn-group .dropdown-menu > a:hover i,
body.whmcsbody.whmcs-loggedin .tab-pane#tabOverview .btn-group .dropdown-menu > a:focus i,
body.whmcsbody.whmcs-loggedin .tab-content #tabOverview .btn-group .dropdown-menu > a:hover i,
body.whmcsbody.whmcs-loggedin .tab-content #tabOverview .btn-group .dropdown-menu > a:focus i {
    color: #f58220 !important;
}
</style>
{/literal}

{literal}
<style>
/* Patch 158: Domains bulk-action warning uses the standard WHMCS warning style.
   Keeps the page consistent with existing WHMCS alert-warning / Dev License warnings. */
body.whmcsbody.whmcs-loggedin .dm-domains-bulk-warning {
    margin: 0 0 14px 0 !important;
}

body.whmcsbody.whmcs-loggedin .dm-domains-bulk-warning .alert,
body.whmcsbody.whmcs-loggedin .dm-domains-bulk-warning .alert-warning {
    margin: 0 !important;
    padding: 12px 18px !important;
    background: #fff3cd !important;
    background-color: #fff3cd !important;
    background-image: none !important;
    border: 1px solid #ffeeba !important;
    border-radius: 4px !important;
    box-shadow: none !important;
    color: #856404 !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    line-height: 1.4 !important;
    text-align: center !important;
    text-shadow: none !important;
}

body.whmcsbody.whmcs-loggedin .dm-domains-bulk-warning .alert *,
body.whmcsbody.whmcs-loggedin .dm-domains-bulk-warning .alert-warning * {
    color: #856404 !important;
    text-shadow: none !important;
}

body.whmcsbody.whmcs-loggedin .dm-domains-bulk-warning .close,
body.whmcsbody.whmcs-loggedin .dm-domains-bulk-warning button.close {
    color: #856404 !important;
    opacity: 0.65 !important;
}
</style>
{/literal}

{if $warnings}
    <div class="dm-domains-bulk-warning">
        {include file="$template/includes/alert.tpl" type="warning" msg=$warnings textcenter=true}
    </div>
{/if}
<div class="tab-content">
    <div class="tab-pane fade show active" id="tabOverview">
        {include file="$template/includes/tablelist.tpl" tableName="DomainsList" noSortColumns="0, 1" startOrderCol="2" filterColumn="5"}
        <script>
            jQuery(document).ready(function () {
                var table = jQuery('#tableDomainsList').show().DataTable();

                {if $orderby == 'domain'}
                    table.order(2, '{$sort}');
                {elseif $orderby == 'regdate' || $orderby == 'registrationdate'}
                    table.order(3, '{$sort}');
                {elseif $orderby == 'nextduedate'}
                    table.order(4, '{$sort}');
                {elseif $orderby == 'autorenew'}
                    table.order(5, '{$sort}');
                {elseif $orderby == 'status'}
                    table.order(6, '{$sort}');
                {/if}
                table.draw();
                jQuery('#tableLoading').hide();
            });
        </script>
        <form id="domainForm" method="post" action="clientarea.php?action=bulkdomain">
            <input id="bulkaction" name="update" type="hidden" />

            <div class="btn-group btn-group-sm mb-3" role="group">
                <button type="submit" class="btn btn-default" id="nameservers" onclick="document.getElementById('domainForm').action='clientarea.php?action=bulkdomain';document.getElementById('bulkaction').value='nameservers';">
                    <i class="fal fa-globe fa-fw"></i>
                    {lang key='domainmanagens'}
                </button>
                <button type="submit" class="btn btn-default" id="contactinfo" onclick="document.getElementById('domainForm').action='clientarea.php?action=bulkdomain';document.getElementById('bulkaction').value='contactinfo';">
                    <i class="fal fa-user"></i>
                    {lang key='domaincontactinfoedit'}
                </button>
                {if $allowrenew}
                    <button type="button" class="btn btn-default setBulkAction" id="renewDomains">
                        <i class="fal fa-sync"></i>
                        {lang key='domainmassrenew'}
                    </button>
                {/if}
                <div class="btn-group btn-group-sm" role="group">
                    <button id="btnGroupDrop1" type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      {lang key="more"}...
                    </button>
                    <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                      <button type="submit" class="dropdown-item" id="autorenew" onclick="document.getElementById('domainForm').action='clientarea.php?action=bulkdomain';document.getElementById('bulkaction').value='autorenew';"><i class="fal fa-sync"></i>
                    {lang key='domainautorenewstatus'}</button>
                      <button type="submit" class="dropdown-item" id="reglock" onclick="document.getElementById('domainForm').action='clientarea.php?action=bulkdomain';document.getElementById('bulkaction').value='reglock';"><i class="fal fa-lock"></i>
                    {lang key='domainreglockstatus'}</button>
                    </div>
                  </div>
            </div>

            <div class="table-container clearfix">
                <table id="tableDomainsList" class="table table-list w-hidden">
                    <thead>
                        <tr>
                            <th class="width-fixed-20 text-center dm-domain-select-header"><input type="checkbox" id="dmSelectAllDomains" class="stopEventBubble" aria-label="Select All Domains" title="Select All" /><span class="dm-checkbox-sort-arrows" aria-hidden="true"></span></th>
                            <th></th>
                            <th>{lang key='orderdomain'}</th>
                            <th>{lang key='clientareahostingregdate'}</th>
                            <th>{lang key='clientareahostingnextduedate'}</th>
                            <th>{lang key='domainstatus'}</th>
                        </tr>
                    </thead>
                    <tbody>
                    {foreach $domains as $domain}
                        <tr onclick="clickableSafeRedirect(event, 'clientarea.php?action=domaindetails&amp;id={$domain.id}', false)">
                            <td class="dm-domain-select-cell">
                                <input type="checkbox" name="domids[]" class="domids stopEventBubble" value="{$domain.id}" />
                            </td>
                            <td class="text-center ssl-info" data-element-id="{$domain.id}" data-type="domain" data-domain="{$domain.domain}">
                                {if $domain.sslStatus}
                                    {assign var=dmSslTooltip value=$domain.sslStatus->getTooltipContent()}
                                    {if $dmSslTooltip eq 'sslState.sslInactive' || $dmSslTooltip eq 'sslState.sslInactiveDomain'}
                                        {assign var=dmSslTooltip value='SSL Inactive'}
                                    {/if}
                                    <img src="{$domain.sslStatus->getImagePath()}" width="25" data-toggle="tooltip" title="{$dmSslTooltip}" class="{$domain.sslStatus->getClass()}" width="25">
                                {elseif !$domain.isActive}
                                    <img src="{$BASE_PATH_IMG}/ssl/ssl-inactive-domain.png" width="25" data-toggle="tooltip" title="SSL Inactive" width="25">
                                {/if}
                            </td>
                            <td>
                                <a href="http://{$domain.domain}" target="_blank">{$domain.domain}</a>
                                <br>
                                <small>
                                    {if $domain.autorenew}
                                        <i class="fas fa-fw fa-check text-success"></i>
                                        {lang key='domainsautorenew'}
                                    {else}
                                        <i class="fas fa-fw fa-times text-danger"></i>
                                        {lang key='domainsautorenew'}
                                    {/if}
                                </small>
                            </td>
                            <td><span class="w-hidden">{$domain.normalisedRegistrationDate}</span>{$domain.registrationdate}</td>
                            <td><span class="w-hidden">{$domain.normalisedNextDueDate}</span>{$domain.nextduedate}</td>
                            <td>
                                <span class="label status status-{$domain.statusClass}">{$domain.statustext}</span>
                                <span class="w-hidden">
                                    {if $domain.expiringSoon}<span>{lang key="domainsExpiringSoon"}</span>{/if}
                                </span>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
                <div class="text-center" id="tableLoading">
                    <p><i class="fas fa-spinner fa-spin"></i> {lang key='loading'}</p>
                </div>
            </div>
        </form>
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

/* Patch 153: date columns are fixed-format, so keep them compact to give the
   checkbox/sort column room without squeezing DOMAIN. */
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.dataTable.table-list thead tr th:nth-child(4),
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.dataTable.table-list tbody tr td:nth-child(4),
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.dataTable.table-list thead tr th:nth-child(5),
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.dataTable.table-list tbody tr td:nth-child(5),
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.table-list thead tr th:nth-child(4),
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.table-list tbody tr td:nth-child(4),
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.table-list thead tr th:nth-child(5),
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.table-list tbody tr td:nth-child(5) {
    width: 150px !important;
    min-width: 150px !important;
    max-width: 150px !important;
    white-space: nowrap !important;
}

</style>
{/literal}


{literal}
<script>
jQuery(function ($) {
    $(document).off('click.dmSelectAllDomains change.dmSelectAllDomains', '#dmSelectAllDomains');
    $(document).on('click.dmSelectAllDomains', '#dmSelectAllDomains', function (event) {
        event.stopPropagation();
    });
    $(document).on('change.dmSelectAllDomains', '#dmSelectAllDomains', function (event) {
        event.stopPropagation();
        $('#tableDomainsList tbody input.domids').prop('checked', this.checked).trigger('change');
    });
});
</script>
{/literal}

{literal}
<style>
/* Patch 152: align Check All over row checkboxes and restore checkbox-column arrow marker. */
/* Patch 153: enlarge checkbox-column sort arrows and widen that column slightly, taking space from date columns. */
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.dataTable.table-list thead tr th.dm-domain-select-header,
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.table-list thead tr th.dm-domain-select-header,
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.dataTable.table-list tbody tr td.dm-domain-select-cell,
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.table-list tbody tr td.dm-domain-select-cell {
    width: 68px !important;
    min-width: 68px !important;
    max-width: 68px !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
    text-align: center !important;
    vertical-align: middle !important;
}

body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.dataTable.table-list thead tr th.dm-domain-select-header,
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.table-list thead tr th.dm-domain-select-header {
    position: relative !important;
}

body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.dataTable.table-list thead tr th.dm-domain-select-header #dmSelectAllDomains,
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.table-list thead tr th.dm-domain-select-header #dmSelectAllDomains,
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.dataTable.table-list tbody tr td.dm-domain-select-cell input.domids,
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.table-list tbody tr td.dm-domain-select-cell input.domids {
    display: block !important;
    float: none !important;
    margin-top: 0 !important;
    margin-bottom: 0 !important;
    margin-left: auto !important;
    margin-right: auto !important;
    position: static !important;
}

body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.dataTable.table-list thead tr th.dm-domain-select-header .dm-checkbox-sort-arrows,
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.table-list thead tr th.dm-domain-select-header .dm-checkbox-sort-arrows {
    content: "" !important;
    position: absolute !important;
    right: 5px !important;
    top: 50% !important;
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    width: 14px !important;
    height: 30px !important;
    margin: 0 !important;
    padding: 0 !important;
    transform: translateY(-50%) !important;
    background-repeat: no-repeat !important;
    background-position: center center !important;
    background-size: 14px 30px !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='30' viewBox='0 0 14 30'%3E%3Cpath fill='rgba(255,255,255,.78)' d='M7 1L13 10H1z'/%3E%3Cpath fill='rgba(255,255,255,.78)' d='M7 29L1 20h12z'/%3E%3C/svg%3E") !important;
    border: 0 !important;
    pointer-events: none !important;
}

/* Patch 153: date columns are fixed-format, so keep them compact to give the
   checkbox/sort column room without squeezing DOMAIN. */
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.dataTable.table-list thead tr th:nth-child(4),
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.dataTable.table-list tbody tr td:nth-child(4),
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.dataTable.table-list thead tr th:nth-child(5),
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.dataTable.table-list tbody tr td:nth-child(5),
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.table-list thead tr th:nth-child(4),
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.table-list tbody tr td:nth-child(4),
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.table-list thead tr th:nth-child(5),
body.whmcsbody.whmcs-loggedin table#tableDomainsList#tableDomainsList.table-list tbody tr td:nth-child(5) {
    width: 150px !important;
    min-width: 150px !important;
    max-width: 150px !important;
    white-space: nowrap !important;
}

</style>
{/literal}
{literal}
<style>
/* DM Patch 354: Domains bulk action dropdown cleanup.
   Keep the More dropdown flat/light and remove the old header-colored shadow/backing. */
body.whmcsbody.whmcs-loggedin #domainForm .btn-group .dropdown-menu,
body.whmcsbody.whmcs-loggedin #domainForm .btn-group.open .dropdown-menu,
body.whmcsbody.whmcs-loggedin #domainForm .btn-group.show .dropdown-menu {
    background: #ffffff !important;
    background-color: #ffffff !important;
    background-image: none !important;
    border: 1px solid #d7e0ea !important;
    border-radius: 5px !important;
    box-shadow: none !important;
    filter: none !important;
    text-shadow: none !important;
    overflow: hidden !important;
}

body.whmcsbody.whmcs-loggedin #domainForm .btn-group .dropdown-menu:before,
body.whmcsbody.whmcs-loggedin #domainForm .btn-group .dropdown-menu:after {
    display: none !important;
    content: none !important;
    box-shadow: none !important;
}

body.whmcsbody.whmcs-loggedin #domainForm .dropdown-menu .dropdown-item,
body.whmcsbody.whmcs-loggedin #domainForm .dropdown-menu button.dropdown-item {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    width: 100% !important;
    padding: 9px 13px !important;
    border: 0 !important;
    background: #ffffff !important;
    background-color: #ffffff !important;
    color: #14345a !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    line-height: 1.35 !important;
    text-align: left !important;
    text-decoration: none !important;
    text-shadow: none !important;
    white-space: nowrap !important;
    cursor: pointer !important;
}

body.whmcsbody.whmcs-loggedin #domainForm .dropdown-menu .dropdown-item:hover,
body.whmcsbody.whmcs-loggedin #domainForm .dropdown-menu .dropdown-item:focus,
body.whmcsbody.whmcs-loggedin #domainForm .dropdown-menu button.dropdown-item:hover,
body.whmcsbody.whmcs-loggedin #domainForm .dropdown-menu button.dropdown-item:focus {
    background: #fff4ec !important;
    background-color: #fff4ec !important;
    color: #14345a !important;
    outline: none !important;
}
</style>
{/literal}


{literal}
<style>
/* DM Patch 356: Domains page visual-only cleanup.
   Built on confirmed Patch 354 so the working bulk-action buttons stay unchanged. */

/* Keep the More dropdown above the DataTables search input so the white search box
   does not show through/behind the opened menu. */
body.whmcsbody.whmcs-loggedin #domainForm > .btn-group,
body.whmcsbody.whmcs-loggedin #domainForm .btn-group {
    position: relative !important;
    z-index: 30 !important;
}

body.whmcsbody.whmcs-loggedin #tableDomainsList_wrapper .dataTables_filter,
body.whmcsbody.whmcs-loggedin #tableDomainsList_filter {
    position: relative !important;
    z-index: 1 !important;
}

body.whmcsbody.whmcs-loggedin #domainForm .btn-group .dropdown-menu,
body.whmcsbody.whmcs-loggedin #domainForm .btn-group.open .dropdown-menu,
body.whmcsbody.whmcs-loggedin #domainForm .btn-group.show .dropdown-menu {
    z-index: 2500 !important;
    background: #ffffff !important;
    background-color: #ffffff !important;
    background-image: none !important;
    border: 1px solid #d7e0ea !important;
    box-shadow: none !important;
    filter: none !important;
}

/* Remove the old darker active-sort header patch while keeping the table header navy. */
body.whmcsbody.whmcs-loggedin #domainForm table#tableDomainsList.table-list.dataTable thead tr th,
body.whmcsbody.whmcs-loggedin #domainForm table#tableDomainsList.table-list.dataTable thead tr th.sorting,
body.whmcsbody.whmcs-loggedin #domainForm table#tableDomainsList.table-list.dataTable thead tr th.sorting_asc,
body.whmcsbody.whmcs-loggedin #domainForm table#tableDomainsList.table-list.dataTable thead tr th.sorting_desc,
body.whmcsbody.whmcs-loggedin #domainForm table#tableDomainsList.table-list.dataTable thead tr th:nth-child(3),
body.whmcsbody.whmcs-loggedin #domainForm table#tableDomainsList.table-list.dataTable thead tr th:nth-child(3).sorting,
body.whmcsbody.whmcs-loggedin #domainForm table#tableDomainsList.table-list.dataTable thead tr th:nth-child(3).sorting_asc,
body.whmcsbody.whmcs-loggedin #domainForm table#tableDomainsList.table-list.dataTable thead tr th:nth-child(3).sorting_desc {
    background: #163a5f !important;
    background-color: #163a5f !important;
    background-image: none !important;
    color: #ffffff !important;
    box-shadow: none !important;
}

body.whmcsbody.whmcs-loggedin #domainForm table#tableDomainsList.table-list.dataTable thead tr th *,
body.whmcsbody.whmcs-loggedin #domainForm table#tableDomainsList.table-list.dataTable thead tr th a,
body.whmcsbody.whmcs-loggedin #domainForm table#tableDomainsList.table-list.dataTable thead tr th span,
body.whmcsbody.whmcs-loggedin #domainForm table#tableDomainsList.table-list.dataTable thead tr th div {
    background: transparent !important;
    background-color: transparent !important;
    color: #ffffff !important;
}

/* Keep the custom DOMAIN arrows readable without reintroducing a darker header block. */
body.whmcsbody.whmcs-loggedin #domainForm table#tableDomainsList.table-list.dataTable thead tr th:nth-child(3).sorting::after,
body.whmcsbody.whmcs-loggedin #domainForm table#tableDomainsList.table-list.dataTable thead tr th:nth-child(3).sorting_asc::after,
body.whmcsbody.whmcs-loggedin #domainForm table#tableDomainsList.table-list.dataTable thead tr th:nth-child(3).sorting_desc::after {
    width: 14px !important;
    height: 30px !important;
    margin-left: 18px !important;
    vertical-align: -10px !important;
    background-size: 14px 30px !important;
    background-color: transparent !important;
    box-shadow: none !important;
}
</style>
{/literal}


{literal}
<style id="dm-domains-wider-table-ssl-tooltip-360">
/* DM Patch 360: start from confirmed Patch 356.
   Widen the Domains content area on desktop and keep the SSL icon column compact.
   Also avoids Patch 357's table-wrapper widening that made the scroll worse. */

/* On the Domains page, the default lg layout gives the sidebar 4/12 columns.
   Narrow it slightly so the table has enough room without moving the side menus. */
@media (min-width: 992px) {
    body.whmcsbody.whmcs-templatefile-clientareadomains #main-body > .container > .row > .col-lg-4.col-xl-3,
    body.whmcsbody.whmcs-templatefile-clientareadomains #main-body > .container > .row > div.col-lg-4.col-xl-3 {
        flex: 0 0 230px !important;
        max-width: 230px !important;
        width: 230px !important;
    }

    body.whmcsbody.whmcs-templatefile-clientareadomains #main-body > .container > .row > .col-lg-8.col-xl-9.primary-content,
    body.whmcsbody.whmcs-templatefile-clientareadomains #main-body > .container > .row > div.col-lg-8.col-xl-9.primary-content {
        flex: 0 0 calc(100% - 230px) !important;
        max-width: calc(100% - 230px) !important;
        width: calc(100% - 230px) !important;
    }
}

/* Keep the checkbox and SSL icon columns compact.
   SSL is column 2; Status is the last column and should not be shrunk. */
body.whmcsbody #main-body #domainForm #tableDomainsList th:nth-child(1),
body.whmcsbody #main-body #domainForm #tableDomainsList td:nth-child(1) {
    width: 38px !important;
    max-width: 38px !important;
    min-width: 34px !important;
    text-align: center;
    white-space: nowrap;
}

body.whmcsbody #main-body #domainForm #tableDomainsList th:nth-child(2),
body.whmcsbody #main-body #domainForm #tableDomainsList td:nth-child(2),
body.whmcsbody #main-body #domainForm #tableDomainsList td.ssl-info {
    width: 48px !important;
    max-width: 48px !important;
    min-width: 44px !important;
    text-align: center;
    white-space: nowrap;
}

body.whmcsbody #main-body #domainForm #tableDomainsList th:nth-child(2)::before,
body.whmcsbody #main-body #domainForm #tableDomainsList th:nth-child(2)::after {
    margin-left: 0 !important;
}

/* Let the Domain column absorb the reclaimed width. */
body.whmcsbody #main-body #domainForm #tableDomainsList th:nth-child(3),
body.whmcsbody #main-body #domainForm #tableDomainsList td:nth-child(3) {
    min-width: 190px;
}

/* Keep status labels compact and readable. */
body.whmcsbody #main-body #domainForm #tableDomainsList th:last-child,
body.whmcsbody #main-body #domainForm #tableDomainsList td:last-child {
    width: 82px;
    max-width: 96px;
    white-space: nowrap;
}
</style>
{/literal}


{literal}
<style id="dm-domains-info-checkbox-361">
/* DM Patch 361: Domains list small consistency polish.
   Built on Patch 360. Keep buttons/dropdown/table width behavior unchanged. */

/* The "Showing 1 to 10..." line was too tight to the left edge. */
body.whmcsbody #main-body #tableDomainsList_info,
body.whmcsbody #main-body #tableDomainsList_wrapper .dataTables_info {
    padding-left: 12px !important;
    padding-top: 8px !important;
    padding-bottom: 10px !important;
    margin: 0 !important;
}

/* The checkbox/select-all header should not show sort arrows. */
body.whmcsbody #main-body #domainForm #tableDomainsList th:first-child::before,
body.whmcsbody #main-body #domainForm #tableDomainsList th:first-child::after,
body.whmcsbody #main-body #domainForm #tableDomainsList th.dm-domain-select-header::before,
body.whmcsbody #main-body #domainForm #tableDomainsList th.dm-domain-select-header::after,
body.whmcsbody #main-body #domainForm #tableDomainsList th:first-child .sorting-indicator,
body.whmcsbody #main-body #domainForm #tableDomainsList th:first-child .dt-column-order,
body.whmcsbody #main-body #domainForm #tableDomainsList th:first-child .dm-checkbox-sort-arrows,
body.whmcsbody #main-body #domainForm #tableDomainsList th.dm-domain-select-header .sorting-indicator,
body.whmcsbody #main-body #domainForm #tableDomainsList th.dm-domain-select-header .dt-column-order,
body.whmcsbody #main-body #domainForm #tableDomainsList th.dm-domain-select-header .dm-checkbox-sort-arrows {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
    width: 0 !important;
    height: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    content: "" !important;
    background: none !important;
    background-image: none !important;
}

/* Center the select-all checkbox cleanly now that the arrows are gone. */
body.whmcsbody #main-body #domainForm #tableDomainsList th:first-child,
body.whmcsbody #main-body #domainForm #tableDomainsList th.dm-domain-select-header,
body.whmcsbody #main-body #domainForm #tableDomainsList td.dm-domain-select-cell {
    text-align: center !important;
    vertical-align: middle !important;
}

body.whmcsbody #main-body #domainForm #tableDomainsList th:first-child input[type="checkbox"],
body.whmcsbody #main-body #domainForm #tableDomainsList th.dm-domain-select-header input[type="checkbox"],
body.whmcsbody #main-body #domainForm #tableDomainsList td.dm-domain-select-cell input[type="checkbox"] {
    margin: 0 auto !important;
}
</style>
{/literal}


{literal}
<script id="dm-domains-expiring-soon-template-safe-364">
jQuery(function ($) {
    function dmReplaceDomainsExpiringSoonText() {
        $('#main-body .sidebar, #main-body #domainForm, #main-body #tableDomainsList').find('*').contents().filter(function () {
            return this.nodeType === 3 && this.nodeValue.indexOf('domainsExpiringSoon') !== -1;
        }).each(function () {
            this.nodeValue = this.nodeValue.replace(/domainsExpiringSoon/g, 'Expiring Soon');
        });
    }

    dmReplaceDomainsExpiringSoonText();
    setTimeout(dmReplaceDomainsExpiringSoonText, 100);
    setTimeout(dmReplaceDomainsExpiringSoonText, 500);

    $('#tableDomainsList').on('draw.dt responsive-display.dt', function () {
        dmReplaceDomainsExpiringSoonText();
    });
});
</script>
{/literal}

{literal}
<style id="dm-domains-search-alignment-365">
/* DM Patch 365: Domains search box spacing/alignment polish.
   Built on Patch 364. Keep existing buttons, dropdown, width, and language-safe fixes. */
body.whmcsbody #main-body #tableDomainsList_wrapper .dataTables_filter,
body.whmcsbody #main-body #tableDomainsList_filter {
    padding-right: 12px !important;
    margin-top: 4px !important;
    margin-bottom: 8px !important;
}

body.whmcsbody #main-body #tableDomainsList_wrapper .dataTables_filter label,
body.whmcsbody #main-body #tableDomainsList_filter label {
    display: inline-flex !important;
    align-items: center !important;
    margin: 0 !important;
    line-height: 1.35 !important;
}

body.whmcsbody #main-body #tableDomainsList_wrapper .dataTables_filter input,
body.whmcsbody #main-body #tableDomainsList_filter input {
    margin-top: 0 !important;
    margin-bottom: 0 !important;
    margin-left: 8px !important;
    min-height: 34px !important;
    padding: 6px 10px !important;
}

@media (min-width: 768px) {
    body.whmcsbody #main-body #tableDomainsList_wrapper .dataTables_filter,
    body.whmcsbody #main-body #tableDomainsList_filter {
        display: flex !important;
        justify-content: flex-end !important;
        align-items: center !important;
    }
}
</style>
{/literal}


{literal}
<style id="dm-domains-menu-width-sort-arrows-368">
/* DM Patch 368: Domains page visual cleanup.
   Built from Patch 365. Keeps working Domains buttons/dropdown, search alignment,
   safe domainsExpiringSoon replacement, and prior header/dropdown fixes. */

/* Re-constrain the WHMCS submenu on the Domains page so it is not stretched
   across the whole browser width. This is page-scoped while we verify the width. */
@media (min-width: 992px) {
    body.whmcsbody.whmcs-templatefile-clientareadomains .whmcssubmenu .contentcontainer,
    body.whmcsbody.whmcs-templatefile-clientareadomains .whmcssubmenu #header.header,
    body.whmcsbody.whmcs-templatefile-clientareadomains .whmcssubmenu .navbar .container-fluid,
    body.whmcsbody.whmcs-templatefile-clientareadomains .whmcssubmenu .main-navbar-wrapper .container-fluid {
        max-width: 980px !important;
        width: 980px !important;
        margin-left: auto !important;
        margin-right: auto !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    body.whmcsbody.whmcs-templatefile-clientareadomains .whmcssubmenu .navbar,
    body.whmcsbody.whmcs-templatefile-clientareadomains .whmcssubmenu .main-navbar-wrapper {
        width: 100% !important;
    }
}

/* Make all sortable header arrows the same size and spacing. */
body.whmcsbody #main-body #domainForm #tableDomainsList thead th.sorting::before,
body.whmcsbody #main-body #domainForm #tableDomainsList thead th.sorting_asc::before,
body.whmcsbody #main-body #domainForm #tableDomainsList thead th.sorting_desc::before {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
    content: "" !important;
    background: none !important;
    background-image: none !important;
}

/* Hide the old extra checkbox-arrow span so the checkbox column uses the same
   ::after arrow style as the rest of the table. */
body.whmcsbody #main-body #domainForm #tableDomainsList thead th.dm-domain-select-header .dm-checkbox-sort-arrows {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
    width: 0 !important;
    height: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
}

/* Neutral/default state: same arrow size on checkbox, Domain, dates, and Status. */
body.whmcsbody #main-body #domainForm #tableDomainsList.table-list.dataTable thead tr th.sorting::after,
body.whmcsbody #main-body #domainForm #tableDomainsList.table-list.dataTable thead tr th.sorting_asc::after,
body.whmcsbody #main-body #domainForm #tableDomainsList.table-list.dataTable thead tr th.sorting_desc::after,
body.whmcsbody #main-body #domainForm table#tableDomainsList.table-list.dataTable thead tr th:nth-child(3).sorting::after,
body.whmcsbody #main-body #domainForm table#tableDomainsList.table-list.dataTable thead tr th:nth-child(3).sorting_asc::after,
body.whmcsbody #main-body #domainForm table#tableDomainsList.table-list.dataTable thead tr th:nth-child(3).sorting_desc::after {
    content: "" !important;
    position: static !important;
    display: inline-block !important;
    visibility: visible !important;
    opacity: 1 !important;
    width: 10px !important;
    height: 22px !important;
    margin-left: 10px !important;
    margin-right: 0 !important;
    padding: 0 !important;
    transform: none !important;
    vertical-align: -7px !important;
    background-repeat: no-repeat !important;
    background-position: center center !important;
    background-size: 10px 22px !important;
    border: 0 !important;
    box-shadow: none !important;
    font-size: 0 !important;
    line-height: 0 !important;
}

/* Checkbox column arrow: same size, slightly closer because there is no text label. */
body.whmcsbody #main-body #domainForm #tableDomainsList.table-list.dataTable thead tr th:first-child.sorting::after,
body.whmcsbody #main-body #domainForm #tableDomainsList.table-list.dataTable thead tr th:first-child.sorting_asc::after,
body.whmcsbody #main-body #domainForm #tableDomainsList.table-list.dataTable thead tr th:first-child.sorting_desc::after,
body.whmcsbody #main-body #domainForm #tableDomainsList.table-list.dataTable thead tr th.dm-domain-select-header.sorting::after,
body.whmcsbody #main-body #domainForm #tableDomainsList.table-list.dataTable thead tr th.dm-domain-select-header.sorting_asc::after,
body.whmcsbody #main-body #domainForm #tableDomainsList.table-list.dataTable thead tr th.dm-domain-select-header.sorting_desc::after {
    margin-left: 8px !important;
    vertical-align: -7px !important;
}

/* Give the checkbox column enough room for checkbox + arrow without pushing table width. */
body.whmcsbody #main-body #domainForm #tableDomainsList thead th:first-child,
body.whmcsbody #main-body #domainForm #tableDomainsList thead th.dm-domain-select-header,
body.whmcsbody #main-body #domainForm #tableDomainsList tbody td.dm-domain-select-cell {
    width: 54px !important;
    min-width: 54px !important;
    max-width: 54px !important;
    text-align: center !important;
    white-space: nowrap !important;
}

/* Shared arrow art. */
body.whmcsbody #main-body #domainForm #tableDomainsList.table-list.dataTable thead tr th.sorting::after,
body.whmcsbody #main-body #domainForm table#tableDomainsList.table-list.dataTable thead tr th:nth-child(3).sorting::after {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='22' viewBox='0 0 10 22'%3E%3Cpath fill='rgba(255,255,255,.75)' d='M5 1L9 7H1z'/%3E%3Cpath fill='rgba(255,255,255,.75)' d='M5 21L1 15h8z'/%3E%3C/svg%3E") !important;
}

body.whmcsbody #main-body #domainForm #tableDomainsList.table-list.dataTable thead tr th.sorting_asc::after,
body.whmcsbody #main-body #domainForm table#tableDomainsList.table-list.dataTable thead tr th:nth-child(3).sorting_asc::after {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='22' viewBox='0 0 10 22'%3E%3Cpath fill='white' d='M5 1L9 7H1z'/%3E%3Cpath fill='rgba(255,255,255,.35)' d='M5 21L1 15h8z'/%3E%3C/svg%3E") !important;
}

body.whmcsbody #main-body #domainForm #tableDomainsList.table-list.dataTable thead tr th.sorting_desc::after,
body.whmcsbody #main-body #domainForm table#tableDomainsList.table-list.dataTable thead tr th:nth-child(3).sorting_desc::after {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='22' viewBox='0 0 10 22'%3E%3Cpath fill='rgba(255,255,255,.35)' d='M5 1L9 7H1z'/%3E%3Cpath fill='white' d='M5 21L1 15h8z'/%3E%3C/svg%3E") !important;
}
</style>
{/literal}


{literal}
<style id="dm-domains-checkbox-arrows-369">
/* DM Patch 369: restore only the checkbox/select-all sort arrows.
   Built on Patch 368, keeping the WHMCS submenu width and normal column arrow fixes. */

body.whmcsbody #main-body #domainForm #tableDomainsList thead th:first-child,
body.whmcsbody #main-body #domainForm #tableDomainsList thead th.dm-domain-select-header,
body.whmcsbody #main-body #domainForm #tableDomainsList tbody td.dm-domain-select-cell {
    width: 58px !important;
    min-width: 58px !important;
    max-width: 58px !important;
    text-align: center !important;
    white-space: nowrap !important;
    vertical-align: middle !important;
}

body.whmcsbody #main-body #domainForm #tableDomainsList thead th.dm-domain-select-header input[type="checkbox"],
body.whmcsbody #main-body #domainForm #tableDomainsList thead th:first-child input[type="checkbox"] {
    margin: 0 6px 0 0 !important;
    vertical-align: middle !important;
}

/* Use the template's dedicated span for the checkbox arrows. */
body.whmcsbody #main-body #domainForm #tableDomainsList thead th.dm-domain-select-header .dm-checkbox-sort-arrows,
body.whmcsbody #main-body #domainForm #tableDomainsList thead th:first-child .dm-checkbox-sort-arrows {
    display: inline-block !important;
    visibility: visible !important;
    opacity: 1 !important;
    width: 10px !important;
    height: 22px !important;
    margin: 0 !important;
    padding: 0 !important;
    vertical-align: middle !important;
    background-repeat: no-repeat !important;
    background-position: center center !important;
    background-size: 10px 22px !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='22' viewBox='0 0 10 22'%3E%3Cpath fill='rgba(255,255,255,.75)' d='M5 1L9 7H1z'/%3E%3Cpath fill='rgba(255,255,255,.75)' d='M5 21L1 15h8z'/%3E%3C/svg%3E") !important;
}

/* Keep native DataTables pseudo-arrows off the checkbox column so only the custom span appears. */
body.whmcsbody #main-body #domainForm #tableDomainsList thead th:first-child::before,
body.whmcsbody #main-body #domainForm #tableDomainsList thead th:first-child::after,
body.whmcsbody #main-body #domainForm #tableDomainsList thead th.dm-domain-select-header::before,
body.whmcsbody #main-body #domainForm #tableDomainsList thead th.dm-domain-select-header::after {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
    content: "" !important;
    background: none !important;
    background-image: none !important;
}
</style>
{/literal}

