<div class="dm-kb-page dm-kb-article-page">
    {if $kbarticle.voted}
        <div class="dm-kb-vote-alert">
            {include file="$template/includes/alert.tpl" type="success alert-bordered-left" msg="{lang key="knowledgebaseArticleRatingThanks"}" textcenter=true}
        </div>
    {/if}

    <div class="card dm-kb-article-card">
        <div class="card-header">
            <div class="dm-kb-article-heading">
                <h1 class="card-title mb-0">{$kbarticle.title}</h1>
                <a href="#" class="btn btn-default btn-sm" onclick="window.print();return false">
                    <i class="fas fa-print"></i>
                    {lang key='print'}
                </a>
            </div>
        </div>
        <div class="card-body">
            <ul class="list-inline dm-kb-meta">
                {if $kbarticle.tags}
                    <li class="list-inline-item pr-3">
                        <span class="badge badge-pill badge-info">
                            <i class="fas fa-code mr-1"></i>
                            {$kbarticle.tags}
                        </span>
                    </li>
                {/if}
                <li class="list-inline-item text-sm pr-3 text-muted"><i class="fas fa-thumbs-up mr-2"></i>{$kbarticle.useful}</li>
            </ul>

            <article class="dm-kb-article-content">
                {$kbarticle.text}
            </article>

            {if !$kbarticle.voted}
                <div class="dm-kb-helpful-box">
                    <h4>{lang key='knowledgebasehelpful'}</h4>
                    <form action="{routePath('knowledgebase-article-view', {$kbarticle.id}, {$kbarticle.urlfriendlytitle})}" method="post" class="dm-kb-helpful-actions">
                        <input type="hidden" name="useful" value="vote">
                        <button class="btn btn-sm btn-secondary px-4" type="submit" name="vote" value="yes">
                            <i class="fas fa-thumbs-up"></i>
                            {lang key='knowledgebaseyes'}
                        </button>
                        <button class="btn btn-sm btn-secondary px-4" type="submit" name="vote" value="no">
                            <i class="fas fa-thumbs-down"></i>
                            {lang key='knowledgebaseno'}
                        </button>
                    </form>
                </div>
            {/if}
        </div>
    </div>

    {if $kbarticles}
        <div class="card dm-kb-list-card dm-kb-related-card">
            <div class="card-header">
                <h3 class="card-title m-0">
                    <i class="fal fa-folder-open fa-fw"></i>
                    {lang key='knowledgebaserelated'}
                </h3>
            </div>
            <div class="list-group list-group-flush">
                {foreach $kbarticles as $kbarticle}
                    <a href="{routePath('knowledgebase-article-view', {$kbarticle.id}, {$kbarticle.urlfriendlytitle})}" class="list-group-item kb-article-item dm-kb-article-item" data-id="{$kbarticle.id}">
                        <span class="dm-kb-article-title">
                            <i class="fal fa-file-alt fa-fw text-black-50"></i>
                            {$kbarticle.title}
                        </span>
                        {if $kbarticle.editLink}
                            <button class="btn btn-sm btn-default show-on-card-hover" id="btnEditArticle-{$kbarticle.id}" data-url="{$kbarticle.editLink}" type="button">
                                {lang key="edit"}
                            </button>
                        {/if}
                        <small>{$kbarticle.article|truncate:100:"..."}</small>
                    </a>
                {foreachelse}
                    <div class="list-group-item dm-kb-empty-row">
                        {lang key='knowledgebasenoarticles'}
                    </div>
                {/foreach}
            </div>
        </div>
    {/if}

    <div class="dm-kb-page-actions">
        <a href="javascript:history.go(-1)" class="btn btn-default px-4">
            {lang key='clientareabacklink'}
        </a>

        {if $kbarticle.editLink}
            <a href="{$kbarticle.editLink}" class="btn btn-default px-4">
                <i class="fas fa-pencil-alt fa-fw"></i>
                {lang key='edit'}
            </a>
        {/if}
    </div>
</div>
