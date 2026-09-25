<link rel="stylesheet" href="{$WEB_ROOT}/templates/{$template}/css/dm-whmcs-email-history-v41d.css?v=41d">

{include file="$template/includes/tablelist.tpl" tableName="EmailsList" noSortColumns="-1"}

<script>
    jQuery(document).ready(function () {
        var table = jQuery('#tableEmailsList').show().DataTable();

        {if $orderby == 'date'}
            table.order(0, '{$sort}');
        {elseif $orderby == 'subject'}
            table.order(1, '{$sort}');
        {/if}
        table.draw();
        jQuery('#tableLoading').hide();
    });
</script>

<div class="dm-email-history-page dm-clientarea-list-page">
    <div class="table-container clearfix dm-email-table-container">
        <table id="tableEmailsList" class="table table-list w-hidden dm-clientarea-table dm-email-history-table">
            <thead>
                <tr>
                    <th class="dm-email-date-col"><span class="dm-emails-th-label">{lang key='clientareaemailsdate'}</span><span class="dm-emails-sort-stack" aria-hidden="true"><span class="dm-emails-sort-up"></span><span class="dm-emails-sort-down"></span></span></th>
                    <th class="dm-email-subject-col"><span class="dm-emails-th-label">{lang key='clientareaemailssubject'}</span><span class="dm-emails-sort-stack" aria-hidden="true"><span class="dm-emails-sort-up"></span><span class="dm-emails-sort-down"></span></span></th>
                    <th class="dm-email-action-col">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                {foreach $emails as $email}
                    <tr class="dm-email-row" onclick="popupWindow('viewemail.php?id={$email.id}', 'emailWin', '800', '600', 'scrollbars=1,')">
                        <td class="text-center dm-email-date" data-order="{$email.normalisedDate}">
                            <span class="w-hidden">{$email.normalisedDate}</span>{$email.date}
                        </td>
                        <td class="dm-email-subject">
                            <span class="dm-email-subject-text">{$email.subject}</span>
                            {if $email.attachmentCount > 0}
                                <span class="dm-email-attachment" title="Attachment">
                                    <i class="fal fa-paperclip"></i>
                                </span>
                            {/if}
                        </td>
                        <td class="text-center dm-email-action">
                            <button type="button" class="btn btn-info btn-sm text-nowrap dm-email-view-btn" onclick="event.stopPropagation(); popupWindow('viewemail.php?id={$email.id}', 'emailWin', '800', '600', 'scrollbars=1,')">
                                {lang key='emailviewmessage'}
                            </button>
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
        <div class="text-center dm-table-loading" id="tableLoading">
            <p><i class="fas fa-spinner fa-spin"></i> {lang key='loading'}</p>
        </div>
    </div>
</div>
