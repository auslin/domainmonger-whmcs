<div class="dm-kb-page dm-kb-category-page">
    <form role="form" method="post" action="{routePath('knowledgebase-search')}" class="dm-kb-search-form">
        <div class="input-group input-group-lg kb-search dm-kb-search">
            <input type="text" id="inputKnowledgebaseSearch" name="search" class="form-control font-weight-light" placeholder="{lang key='clientHomeSearchKb'}" value="{$searchterm|escape}" />
            <div class="input-group-append">
                <button type="submit" id="btnKnowledgebaseSearch" class="btn btn-primary btn-input-padded-responsive">
                    {lang key='search'}
                </button>
            </div>
        </div>
    </form>

    {if $kbcats}
        <div class="row dm-kb-category-grid">
            {foreach $kbcats as $category}
                <div class="col-xl-6">
                    <div class="card kb-category dm-kb-category-card">
                        <a href="{routePath('knowledgebase-category-view', {$category.id}, {$category.urlfriendlyname})}" class="card-body" data-id="{$category.id}">
                            <span class="dm-kb-category-title">
                                <span class="badge badge-info dm-kb-count">
                                    {lang key="knowledgebase.numArticle{if $category.numarticles != 1}s{/if}" num=$category.numarticles}
                                </span>
                                <span class="dm-kb-title-main">
                                    <i class="fal fa-folder fa-fw"></i>
                                    {$category.name}
                                </span>
                                {if $category.editLink}
                                    <button class="btn btn-sm btn-default show-on-card-hover" id="btnEditCategory-{$category.id}" data-url="{$category.editLink}" type="button">
                                        {lang key="edit"}
                                    </button>
                                {/if}
                            </span>
                            <p class="dm-kb-description"><small>{$category.description}</small></p>
                        </a>
                    </div>
                </div>
            {/foreach}
        </div>
    {/if}

    {if $kbarticles || !$kbcats}
        <div class="card dm-kb-list-card dm-kb-articles-card">
            <div class="card-header">
                <h3 class="card-title m-0">
                    <i class="fal fa-folder-open fa-fw"></i>
                    {if $tag}
                        {lang key='kbviewingarticlestagged'} '{$tag}'
                    {else}
                        {lang key='knowledgebasearticles'}
                    {/if}
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

        {if $kbcurrentcat.editLink}
            <a href="{$kbcurrentcat.editLink}" class="btn btn-default px-4">
                <i class="fas fa-pencil-alt fa-fw"></i>
                {lang key='edit'}
            </a>
        {/if}
    </div>
</div>
