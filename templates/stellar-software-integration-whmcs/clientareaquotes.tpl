{include file="$template/includes/tablelist.tpl" tableName="QuotesList"  noSortColumns="5" filterColumn="4"}

<script>
    jQuery(document).ready(function() {
        var table = jQuery('#tableQuotesList').show().DataTable();

        {if $orderby == 'id'}
            table.order(0, '{$sort}');
        {elseif $orderby == 'date'}
            table.order(2, '{$sort}');
        {elseif $orderby == 'validuntil'}
            table.order(3, '{$sort}');
        {elseif $orderby == 'stage'}
            table.order(4, '{$sort}');
        {/if}
        table.draw();
        jQuery('#tableLoading').hide();
    });
</script>

<div class="table-container clearfix">
    <table id="tableQuotesList" class="table table-list w-hidden">
        <thead>
            <tr>
                <th><span class="dm-quotes-th-label">{lang key='quotenumber'}</span><span class="dm-quotes-sort-stack" aria-hidden="true"><span class="dm-quotes-sort-up"></span><span class="dm-quotes-sort-down"></span></span></th>
                <th><span class="dm-quotes-th-label">{lang key='quotesubject'}</span><span class="dm-quotes-sort-stack" aria-hidden="true"><span class="dm-quotes-sort-up"></span><span class="dm-quotes-sort-down"></span></span></th>
                <th><span class="dm-quotes-th-label">{lang key='quotedatecreated'}</span><span class="dm-quotes-sort-stack" aria-hidden="true"><span class="dm-quotes-sort-up"></span><span class="dm-quotes-sort-down"></span></span></th>
                <th><span class="dm-quotes-th-label">{lang key='quotevaliduntil'}</span><span class="dm-quotes-sort-stack" aria-hidden="true"><span class="dm-quotes-sort-up"></span><span class="dm-quotes-sort-down"></span></span></th>
                <th><span class="dm-quotes-th-label">{lang key='quotestage'}</span><span class="dm-quotes-sort-stack" aria-hidden="true"><span class="dm-quotes-sort-up"></span><span class="dm-quotes-sort-down"></span></span></th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            {foreach $quotes as $quote}
                <tr onclick="clickableSafeRedirect(event, 'viewquote.php?id={$quote.id}', true)">
                    <td>{$quote.id}</td>
                    <td>{$quote.subject}</td>
                    <td><span class="w-hidden">{$quote.normalisedDateCreated}</span>{$quote.datecreated}</td>
                    <td><span class="w-hidden">{$quote.normalisedValidUntil}</span>{$quote.validuntil}</td>
                    <td><span class="label status status-{$quote.stageClass}">{$quote.stage}</span></td>
                    <td class="text-center">
                        <form method="post" action="dl.php">
                            <input type="hidden" name="type" value="q" />
                            <input type="hidden" name="id" value="{$quote.id}" />
                            <button type="submit" class="btn btn-default btn-sm"><i class="fas fa-download"></i> {lang key='quotedownload'}</button>
                        </form>
                    </td>
                </tr>
            {/foreach}
        </tbody>
    </table>
    <div class="text-center" id="tableLoading">
        <p><i class="fas fa-spinner fa-spin"></i> {lang key='loading'}</p>
    </div>
</div>
