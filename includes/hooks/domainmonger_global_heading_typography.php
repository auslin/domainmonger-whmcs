<?php
/**
 * DomainMonger WHMCS v9 global heading/section-title consistency.
 *
 * Patch 423:
 * - Component-level typography cleanup for page headings and section titles only.
 * - Avoids broad body font or paragraph-size changes.
 * - Skips the custom v8x register/namespinner route and the isolated v9 support route.
 */

use WHMCS\View\Menu\Item as MenuItem;

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
<style id="domainmonger-global-heading-typography">
/* DomainMonger Patch 423: heading/section-title consistency only. */
:root {
    --dm-navy: #163a5f;
    --dm-navy-hover: #214e7a;
    --dm-orange: #f58220;
    --dm-border: #d8dee6;
    --dm-muted: #6c757d;
}

/* Page-level headings: clean, readable, and brand-consistent without forcing all caps. */
#main-body h1,
#main-body .main-content h1,
#main-body .client-area h1,
#main-body .page-title,
#main-body .page-header h1,
#main-body header h1 {
    color: var(--dm-navy) !important;
    font-weight: 600 !important;
    line-height: 1.25 !important;
    letter-spacing: 0 !important;
    text-transform: none !important;
}

/* Secondary section headings used inside cards, panels, forms, and WHMCS content blocks. */
#main-body h2,
#main-body h3,
#main-body .h2,
#main-body .h3,
#main-body .section-title,
#main-body .section-heading,
#main-body .content-block h2,
#main-body .content-block h3,
#main-body .card-body h2,
#main-body .card-body h3,
#main-body .panel-body h2,
#main-body .panel-body h3,
#main-body .tab-content h2,
#main-body .tab-content h3 {
    color: var(--dm-navy) !important;
    font-weight: 600 !important;
    line-height: 1.3 !important;
    letter-spacing: 0 !important;
    text-transform: none !important;
}

/* Smaller subsection labels should feel deliberate, not randomly bold/gray. */
#main-body h4,
#main-body h5,
#main-body h6,
#main-body .h4,
#main-body .h5,
#main-body .h6,
#main-body .sub-heading,
#main-body .sub-title,
#main-body .small-title,
#main-body .widget-title {
    color: var(--dm-navy) !important;
    font-weight: 600 !important;
    line-height: 1.35 !important;
    letter-spacing: 0 !important;
    text-transform: none !important;
}

/* Page-header separators should be subtle and aligned with the card/table border palette. */
#main-body .page-header,
#main-body .header-lined,
#main-body .section-header,
#main-body .content-header {
    border-color: var(--dm-border) !important;
}

#main-body .header-lined h1,
#main-body .header-lined h2,
#main-body .header-lined h3,
#main-body .content-header h1,
#main-body .content-header h2,
#main-body .content-header h3 {
    color: var(--dm-navy) !important;
    font-weight: 600 !important;
    text-transform: none !important;
}

/* Muted helper text under headings stays muted but readable. */
#main-body .page-header small,
#main-body .header-lined small,
#main-body .section-title small,
#main-body .section-heading small,
#main-body h1 small,
#main-body h2 small,
#main-body h3 small,
#main-body h4 small {
    color: var(--dm-muted) !important;
    font-weight: 400 !important;
    text-transform: none !important;
}

/* Do not override text color inside navy component headers already handled by Patch 411/419. */
#main-body .card-header h1,
#main-body .card-header h2,
#main-body .card-header h3,
#main-body .card-header h4,
#main-body .card-header h5,
#main-body .card-header h6,
#main-body .panel-heading h1,
#main-body .panel-heading h2,
#main-body .panel-heading h3,
#main-body .panel-heading h4,
#main-body .panel-heading h5,
#main-body .panel-heading h6,
#main-body .modal-header h1,
#main-body .modal-header h2,
#main-body .modal-header h3,
#main-body .modal-header h4,
#main-body .modal-header h5,
#main-body .modal-header h6 {
    color: inherit !important;
}

/* Keep heading links readable and aligned with global link behavior. */
#main-body h1 a,
#main-body h2 a,
#main-body h3 a,
#main-body h4 a,
#main-body h5 a,
#main-body h6 a {
    color: inherit !important;
    text-decoration: none !important;
}

#main-body h1 a:hover,
#main-body h2 a:hover,
#main-body h3 a:hover,
#main-body h4 a:hover,
#main-body h5 a:hover,
#main-body h6 a:hover,
#main-body h1 a:focus,
#main-body h2 a:focus,
#main-body h3 a:focus,
#main-body h4 a:focus,
#main-body h5 a:focus,
#main-body h6 a:focus {
    color: var(--dm-orange) !important;
    text-decoration: none !important;
}
</style>
HTML;
});
