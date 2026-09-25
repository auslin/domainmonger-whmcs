<div class="card dm-announcements-card">
    <div class="card-header">
        <h3 class="card-title mb-0">{lang key="announcementstitle"}</h3>
    </div>
    <div class="card-body">
        <div class="announcements dm-announcements-list">
            {foreach $announcements as $announcement}
                <div class="announcement dm-announcement-item">
                    <div class="dm-announcement-heading">
                        <h2>
                            <a href="{routePath('announcement-view', $announcement.id, $announcement.urlfriendlytitle)}">
                                {$announcement.title}
                            </a>
                        </h2>
                        {if $announcement.editLink}
                            <a href="{$announcement.editLink}" class="btn btn-default btn-sm show-on-hover">
                                <i class="fas fa-pencil-alt fa-fw"></i>
                                {lang key='edit'}
                            </a>
                        {/if}
                    </div>

                    <ul class="list-inline dm-announcement-meta">
                        <li class="list-inline-item text-muted pr-3">
                            <i class="far fa-calendar-alt fa-fw"></i>
                            {$carbon->createFromTimestamp($announcement.timestamp)->format('jS F Y')}
                        </li>
                    </ul>

                    <article class="dm-announcement-summary">
                        {if $announcement.text|strip_tags|strlen < 350}
                            {$announcement.text}
                        {else}
                            {$announcement.summary}
                        {/if}
                    </article>

                    <a href="{routePath('announcement-view', $announcement.id, $announcement.urlfriendlytitle)}" class="btn btn-default btn-sm dm-announcement-read-more">
                        {lang key="announcementscontinue"}
                        <i class="far fa-arrow-right"></i>
                    </a>
                </div>
            {foreachelse}
                <div class="dm-announcements-empty-state mt-3 mb-3">
                    {include file="$template/includes/alert.tpl" type="info" msg="{lang key='noannouncements'}" textcenter=true}
                </div>
            {/foreach}
        </div>
    </div>
</div>

{if $prevpage || $nextpage}
    <nav aria-label="Announcements navigation" class="dm-announcements-pagination">
        <ul class="pagination">
            {foreach $pagination as $item}
                <li class="page-item{if $item.disabled} disabled{/if}{if $item.active} active{/if}">
                    <a class="page-link" href="{$item.link}">{$item.text}</a>
                </li>
            {/foreach}
        </ul>
    </nav>
{/if}

{if $announcementsFbRecommend}
    <script>
        (function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) {
                return;
            }
            js = d.createElement(s); js.id = id;
            js.src = "//connect.facebook.net/{lang key='locale'}/all.js#xfbml=1";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    </script>
{/if}
