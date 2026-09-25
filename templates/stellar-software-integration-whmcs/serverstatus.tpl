<div class="dm-server-status-page">

    {if $opencount == 0}
        <div class="dm-server-status-alert">
            {include file="$template/includes/alert.tpl" type="success" msg="<i class='fas fa-check fa-fw'></i> {"{lang key='networkstatusnone'}"|sprintf:"{lang key='networkissuesstatusopen'}"}"}
        </div>
    {/if}

    {if $scheduledcount > 0}
        <div class="dm-server-status-alert">
            <div class="alert alert-info">
                <i class="fas fa-exclamation-triangle fa-fw"></i>
                {lang key='networkIssues.scheduled' count=$scheduledcount}
                <a href="serverstatus.php?view=scheduled" class="alert-link">{lang key='learnmore'}...</a>
            </div>
        </div>
    {/if}

    {if $servers}
        <div class="card dm-server-status-card dm-server-status-table-card">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="fal fa-server fa-fw"></i>
                    {lang key='serverstatustitle'}
                </h3>
            </div>
            <div class="card-body">
                <p class="dm-server-status-intro">{lang key='serverstatusheadingtext'}</p>

                <div class="table-responsive dm-server-status-table-wrap">
                    <table class="table table-list table-striped dm-server-status-table">
                        <thead>
                            <tr>
                                <th>{lang key='servername'}</th>
                                <th class="text-center">{lang key='networkIssues.http'}</th>
                                <th class="text-center">{lang key='networkIssues.ftp'}</th>
                                <th class="text-center">{lang key='networkIssues.pop3'}</th>
                                <th class="text-center">{lang key='serverstatusphpinfo'}</th>
                                <th class="text-center">{lang key='serverstatusserverload'}</th>
                                <th class="text-center">{lang key='serverstatusuptime'}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $servers as $num => $server}
                                <tr>
                                    <td class="dm-server-name">{$server.name}</td>
                                    <td class="text-center dm-server-status-check" id="port80_{$num}">
                                        <span class="fas fa-spinner fa-spin"></span>
                                    </td>
                                    <td class="text-center dm-server-status-check" id="port21_{$num}">
                                        <span class="fas fa-spinner fa-spin"></span>
                                    </td>
                                    <td class="text-center dm-server-status-check" id="port110_{$num}">
                                        <span class="fas fa-spinner fa-spin"></span>
                                    </td>
                                    <td class="text-center"><a href="{$server.phpinfourl}" target="_blank" class="dm-server-status-link">{lang key='serverstatusphpinfo'}</a></td>
                                    <td class="text-center dm-server-status-check" id="load{$num}">
                                        <span class="fas fa-spinner fa-spin"></span>
                                    </td>
                                    <td class="text-center dm-server-status-check" id="uptime{$num}">
                                        <span class="fas fa-spinner fa-spin"></span>
                                        <script>
                                        jQuery(document).ready(function() {
                                            checkPort({$num}, 80);
                                            checkPort({$num}, 21);
                                            checkPort({$num}, 110);
                                            getStats({$num});
                                        });
                                        </script>
                                    </td>
                                </tr>
                            {foreachelse}
                                <tr>
                                    <td colspan="7" class="text-center dm-server-status-empty">{lang key='serverstatusnoservers'}</td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    {/if}

    <div class="dm-network-issues-section">
        {foreach $issues as $issue}
            <div class="card dm-server-status-card dm-network-issue-card">
                <div class="card-header">
                    <div class="dm-network-issue-heading">
                        <span class="dm-network-issue-title">{$issue.title}</span>
                        <div class="dm-network-issue-meta">
                            <span class="dm-network-issue-status">{$issue.status}</span>
                            <span id="issuePriorityLabel" class="badge badge-{if $issue.rawPriority == 'Critical'}danger{elseif $issue.rawPriority == 'High'}warning{elseif $issue.rawPriority == 'Low'}success{else}info{/if}">{$issue.priority}</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    {if $issue.server || $issue.affecting}
                        <p class="dm-network-issue-affecting">
                            <strong>{lang key='networkissuesaffecting'} {$issue.type}</strong>
                            -
                            {if $issue.type eq "{lang key='networkissuestypeserver'}"}
                                {$issue.server}
                            {else}
                                {$issue.affecting}
                            {/if}
                        </p>
                    {/if}
                    <ul class="list-inline dm-network-issue-dates">
                        <li class="list-inline-item pr-3">
                            <i class="far fa-calendar-alt fa-fw"></i>
                            {$issue.startdate}
                            {if $issue.enddate} - {$issue.enddate}{/if}
                        </li>
                        <li class="list-inline-item pr-3">
                            <i class="far fa-clock fa-fw"></i>
                            {lang key='networkissueslastupdated'} {$issue.lastupdate}
                        </li>
                    </ul>
                    {if $issue.clientaffected}
                        <div class="alert alert-warning p-1 text-center dm-network-issue-client-alert">
                            {lang key='networkIssues.affectingYou'}
                        </div>
                    {/if}
                    <div class="dm-network-issue-description">
                        {$issue.description}
                    </div>
                </div>
            </div>
        {foreachelse}
            <div class="card dm-server-status-card dm-network-no-issues-card">
                <div class="card-body">
                    {$noissuesmsg}
                </div>
            </div>
        {/foreach}
    </div>

    <nav aria-label="Network issues navigation" class="dm-network-issues-pagination">
        <ul class="pagination">
            <li class="page-item{if !$prevpage} disabled{/if}"><a class="page-link" href="?{if $view}view={$view}&amp;{/if}page={$prevpage}">{lang key='previouspage'}</a></li>
            <li class="page-item{if !$nextpage} disabled{/if}"><a class="page-link" href="?{if $view}view={$view}&amp;{/if}page={$nextpage}">{lang key='nextpage'}</a></li>
        </ul>
    </nav>

</div>
