<?php
/**
 * DomainMonger WHMCS v9 global content spacing consistency.
 *
 * Patch 424:
 * - Component-level cleanup for card/panel/list/content spacing only.
 * - Keeps changes subtle to avoid page layout rewrites.
 * - Skips the custom v8x register/namespinner route and the isolated v9 support route.
 */

add_hook('ClientAreaHeadOutput', 1, function ($vars) {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $queryString = parse_url($requestUri, PHP_URL_QUERY) ?: '';
    parse_str($queryString, $query);

    if (isset($query['dmv9support']) && (string) $query['dmv9support'] === '1') {
        return '';
    }

    $path = parse_url($requestUri, PHP_URL_PATH) ?: '';
    $isRegisterRoute = strpos($path, '/manage/cart.php') !== false
        && isset($query['a'], $query['domain'])
        && (string) $query['a'] === 'add'
        && in_array((string) $query['domain'], ['register', 'r'], true);

    if ($isRegisterRoute) {
        return '';
    }

    return <<<'HTML'
<style id="domainmonger-global-content-spacing">
/* DomainMonger Patch 424: subtle content spacing / visual rhythm only. */
:root {
    --dm-border: #d8dee6;
    --dm-soft-border: #e7ebf0;
    --dm-card-bg: #ffffff;
    --dm-muted-bg: #f8fafc;
    --dm-radius: 8px;
    --dm-radius-sm: 6px;
}

/* Keep card and panel body spacing consistent without changing page width or grids. */
#main-body .card > .card-body,
#main-body .panel > .panel-body,
#main-body .card-block,
#main-body .panel-content {
    background: var(--dm-card-bg) !important;
    padding: 1rem 1.15rem !important;
}

/* Nested cards/panels should not look cramped or randomly separated. */
#main-body .card .card,
#main-body .panel .panel,
#main-body .card .panel,
#main-body .panel .card {
    margin-bottom: 1rem !important;
    border-color: var(--dm-soft-border) !important;
}

#main-body .card .card:last-child,
#main-body .panel .panel:last-child,
#main-body .card .panel:last-child,
#main-body .panel .card:last-child {
    margin-bottom: 0 !important;
}

/* Section dividers and horizontal rules should match the light border palette. */
#main-body hr,
#main-body .divider,
#main-body .section-divider,
#main-body .content-divider {
    border-top-color: var(--dm-soft-border) !important;
}

/* List groups inside client area cards should feel like one clean component. */
#main-body .list-group,
#main-body .list-group-flush {
    border-radius: var(--dm-radius) !important;
}

#main-body .list-group-item {
    border-color: var(--dm-soft-border) !important;
    padding: .72rem 1rem !important;
}

#main-body .list-group-item:first-child {
    border-top-left-radius: var(--dm-radius-sm) !important;
    border-top-right-radius: var(--dm-radius-sm) !important;
}

#main-body .list-group-item:last-child {
    border-bottom-left-radius: var(--dm-radius-sm) !important;
    border-bottom-right-radius: var(--dm-radius-sm) !important;
}

/* Description/detail rows often appear as bare Bootstrap gray blocks; make them cleaner. */
#main-body dl,
#main-body .dl-horizontal,
#main-body .details-list,
#main-body .item-list {
    margin-bottom: 1rem !important;
}

#main-body dl dt,
#main-body .details-list .title,
#main-body .item-list .title {
    font-weight: 600 !important;
}

#main-body dl dd,
#main-body .details-list .description,
#main-body .item-list .description {
    margin-bottom: .55rem !important;
}

/* Tighten stacked form/content blocks only inside existing components. */
#main-body .card-body > :last-child,
#main-body .panel-body > :last-child,
#main-body .well > :last-child,
#main-body .tab-content > :last-child {
    margin-bottom: 0 !important;
}

#main-body .card-body > .row:last-child,
#main-body .panel-body > .row:last-child,
#main-body .tab-content > .row:last-child {
    margin-bottom: 0 !important;
}

/* Keep small footers/notes visually connected to their card without looking disabled. */
#main-body .card-footer,
#main-body .panel-footer,
#main-body .box-footer {
    background: var(--dm-muted-bg) !important;
    border-color: var(--dm-soft-border) !important;
    padding: .8rem 1.15rem !important;
}

/* Avoid adding extra rhythm changes to table utilities/pagination handled by previous patches. */
#main-body .dataTables_wrapper .row,
#main-body .pagination,
#main-body .table {
    /* ownership intentionally left to Patch 417/418/420 and normal WHMCS layout */
}
</style>
HTML;
});
