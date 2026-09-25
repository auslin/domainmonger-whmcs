<div class="dm-downloads-page dm-downloads-category-page">

    <form role="form" method="post" action="{routePath('download-search')}" class="dm-downloads-search-form">
        <div class="input-group input-group-lg kb-search dm-downloads-search">
            <input type="text" name="search" id="inputDownloadsSearch" class="form-control font-weight-light" placeholder="{lang key='downloadssearch'}" value="{$search}" />
            <div class="input-group-append">
                <button type="submit" id="btnDownloadsSearch" class="btn btn-primary btn-input-padded-responsive">
                    {lang key='search'}
                </button>
            </div>
        </div>
    </form>

    {if $dlcats}
        <div class="row dm-downloads-category-grid">
            {foreach $dlcats as $category}
                <div class="col-xl-6">
                    <div class="card dm-downloads-category-card">
                        <a href="{routePath('download-by-cat', {$category.id}, {$category.urlfriendlyname})}" class="card-body">
                            <span class="dm-downloads-category-title">
                                <span>
                                    <i class="fal fa-folder fa-fw"></i>
                                    {$category.name}
                                </span>
                                <span class="badge badge-info">
                                    {lang key="downloads.numDownload{if $category.numarticles != 1}s{/if}" num=$category.numarticles}
                                </span>
                            </span>
                            {if $category.description}
                                <p class="dm-downloads-category-description">{$category.description}</p>
                            {/if}
                        </a>
                    </div>
                </div>
            {/foreach}
        </div>
    {/if}

    <div class="card dm-downloads-list-card">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="fal fa-download fa-fw"></i>
                {lang key='downloadsfiles'}
            </h3>
        </div>
        <div class="list-group list-group-flush dm-downloads-list">
            {foreach $downloads as $download}
                <a href="{$download.link}" class="list-group-item dm-download-item">
                    <div class="dm-download-item-main">
                        <span class="dm-download-item-title">
                            {$download.type|replace:'alt':' class="pr-1" alt'}
                            {$download.title}
                        </span>
                        {if $download.clientsonly}
                            <span class="label label-danger dm-download-restricted">
                                <i class="fas fa-lock fa-fw"></i>
                                {lang key='restricted'}
                            </span>
                        {/if}
                    </div>
                    <small class="dm-download-item-description">
                        {$download.description}
                        <br>
                        <strong>{lang key='downloadsfilesize'}: {$download.filesize}</strong>
                    </small>
                </a>
            {foreachelse}
                <div class="list-group-item dm-download-item dm-downloads-none">
                    {lang key='downloadsnone'}
                </div>
            {/foreach}
        </div>
    </div>

    <a href="javascript:history.go(-1)" class="btn btn-default px-4 dm-downloads-back">
        {lang key='clientareabacklink'}
    </a>

</div>
