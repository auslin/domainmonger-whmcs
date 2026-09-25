<div class="card dm-submit-ticket-card dm-submit-ticket-kb-suggestions">
    <div class="card-header">
        <h3 class="card-title mb-0">{lang key='kbsuggestions'}</h3>
    </div>
    <div class="card-body">
        <p class="dm-submit-ticket-intro">{lang key='kbsuggestionsexplanation'}</p>

        <div class="kbarticles list-group dm-submit-ticket-kb-list">
            {foreach $kbarticles as $kbarticle}
                <div class="list-group-item kb-article-item dm-submit-ticket-kb-item">
                    <a href="knowledgebase.php?action=displayarticle&id={$kbarticle.id}" target="_blank">
                        <i class="fal fa-file-alt fa-fw text-black-50"></i>
                        <span class="dm-submit-ticket-kb-title">{$kbarticle.title}</span>
                        <small>{$kbarticle.article}...</small>
                    </a>
                </div>
            {/foreach}
        </div>
    </div>
</div>
