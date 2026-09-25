<?php
/**
 * DomainMonger WHMCS global pagination, tabs, and small nav control palette.
 *
 * Patch 418 update to Patch 417
 * - Component-level global cleanup for WHMCS client-area pagination, tabs, nav pills, and breadcrumbs.
 * - Fixes active pagination text so the current page remains white on navy, even on hover/focus.
 * - Keeps active states navy, hover states pale orange, and avoids Bootstrap default blue/gray drift.
 * - Avoids broad typography, templates, language files, integration files, and order-form logic.
 * - Skips the custom v8x register route and the isolated WHMCS v9 support route.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 1012, function ($vars) {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

    // Keep the isolated WHMCS v9 support/testing route untouched.
    if (stripos($requestUri, 'dmv9support=1') !== false) {
        return '';
    }

    // Protect the custom v8x register/namespinner route.
    $isCart = stripos($scriptName, '/cart.php') !== false || stripos($requestUri, '/cart.php') !== false;
    $isDomainRegister = preg_match('/(?:\?|&)a=add(?:&|$)/i', $requestUri) === 1
        && preg_match('/(?:\?|&)domain=(register|r)(?:&|$)/i', $requestUri) === 1;

    if ($isCart && $isDomainRegister) {
        return '';
    }

    return <<<'HTML'
<style id="domainmonger-global-pagination-tabs-nav">
:root {
    --dm-nav-navy: #163a5f;
    --dm-nav-navy-hover: #214e7a;
    --dm-nav-orange: #f58220;
    --dm-nav-orange-soft: #fff3e8;
    --dm-nav-border: #d7dee6;
    --dm-nav-muted: #6c757d;
    --dm-nav-text: #1f2933;
    --dm-nav-radius: 6px;
}

/* Pagination: neutral default, pale-orange hover, navy active. */
body #main-body .pagination,
body #main-body .dataTables_paginate .pagination {
    gap: 3px;
}

body #main-body .pagination > li > a,
body #main-body .pagination > li > span,
body #main-body .page-link,
body #main-body .dataTables_paginate .paginate_button a,
body #main-body .dataTables_paginate .paginate_button span {
    color: var(--dm-nav-navy) !important;
    background-color: #ffffff !important;
    border-color: var(--dm-nav-border) !important;
    border-radius: var(--dm-nav-radius) !important;
    box-shadow: none !important;
    text-decoration: none !important;
    font-weight: 500 !important;
}

body #main-body .pagination > li > a:hover,
body #main-body .pagination > li > a:focus,
body #main-body .page-link:hover,
body #main-body .page-link:focus,
body #main-body .dataTables_paginate .paginate_button a:hover,
body #main-body .dataTables_paginate .paginate_button a:focus {
    color: var(--dm-nav-navy) !important;
    background-color: var(--dm-nav-orange-soft) !important;
    border-color: #f1c49b !important;
    box-shadow: none !important;
    outline: none !important;
}

/* Active/current pagination must stay white-on-navy, including hover/focus. */
body #main-body .pagination > .active > a,
body #main-body .pagination > .active > a:hover,
body #main-body .pagination > .active > a:focus,
body #main-body .pagination > .active > span,
body #main-body .pagination > .active > span:hover,
body #main-body .pagination > .active > span:focus,
body #main-body .pagination .page-item.active > .page-link,
body #main-body .pagination .page-item.active > .page-link:hover,
body #main-body .pagination .page-item.active > .page-link:focus,
body #main-body .page-item.active > .page-link,
body #main-body .page-item.active > .page-link:hover,
body #main-body .page-item.active > .page-link:focus,
body #main-body .dataTables_paginate .paginate_button.active > a,
body #main-body .dataTables_paginate .paginate_button.active > a:hover,
body #main-body .dataTables_paginate .paginate_button.active > a:focus,
body #main-body .dataTables_paginate .paginate_button.active > span,
body #main-body .dataTables_paginate .paginate_button.active > span:hover,
body #main-body .dataTables_paginate .paginate_button.active > span:focus,
body #main-body .dataTables_paginate .paginate_button.current,
body #main-body .dataTables_paginate .paginate_button.current:hover,
body #main-body .dataTables_paginate .paginate_button.current:focus,
body #main-body .dataTables_paginate .paginate_button.current a,
body #main-body .dataTables_paginate .paginate_button.current a:hover,
body #main-body .dataTables_paginate .paginate_button.current a:focus {
    color: #ffffff !important;
    background-color: var(--dm-nav-navy) !important;
    border-color: var(--dm-nav-navy) !important;
    text-decoration: none !important;
    box-shadow: none !important;
}

body #main-body .pagination > .disabled > span,
body #main-body .pagination > .disabled > a,
body #main-body .pagination .page-item.disabled .page-link,
body #main-body .page-item.disabled .page-link {
    color: var(--dm-nav-muted) !important;
    background-color: #f4f6f8 !important;
    border-color: var(--dm-nav-border) !important;
    cursor: not-allowed !important;
}

/* Tabs: keep a clean card-like tab bar without default Bootstrap blue. */
body #main-body .nav-tabs {
    border-bottom-color: var(--dm-nav-border) !important;
}

body #main-body .nav-tabs > li > a,
body #main-body .nav-tabs .nav-link,
body #main-body ul.nav-tabs li a {
    color: var(--dm-nav-navy) !important;
    background-color: #ffffff !important;
    border-color: transparent !important;
    border-radius: var(--dm-nav-radius) var(--dm-nav-radius) 0 0 !important;
    text-decoration: none !important;
    font-weight: 500 !important;
    box-shadow: none !important;
}

body #main-body .nav-tabs > li > a:hover,
body #main-body .nav-tabs > li > a:focus,
body #main-body .nav-tabs .nav-link:hover,
body #main-body .nav-tabs .nav-link:focus,
body #main-body ul.nav-tabs li a:hover,
body #main-body ul.nav-tabs li a:focus {
    color: var(--dm-nav-navy) !important;
    background-color: var(--dm-nav-orange-soft) !important;
    border-color: var(--dm-nav-border) var(--dm-nav-border) transparent !important;
    outline: none !important;
}

body #main-body .nav-tabs > li.active > a,
body #main-body .nav-tabs > li.active > a:hover,
body #main-body .nav-tabs > li.active > a:focus,
body #main-body .nav-tabs .nav-link.active,
body #main-body .nav-tabs .nav-item.show .nav-link,
body #main-body ul.nav-tabs li.active a {
    color: #ffffff !important;
    background-color: var(--dm-nav-navy) !important;
    border-color: var(--dm-nav-navy) !important;
}

/* Nav pills / small segmented controls: navy active, pale-orange hover. Avoid site navbar/sidebar selectors. */
body #main-body .nav-pills:not(.navbar-nav):not(.nav-sidebar) > li > a,
body #main-body .nav-pills:not(.navbar-nav):not(.nav-sidebar) .nav-link,
body #main-body .btn-group .btn.btn-default:not(.dropdown-toggle),
body #main-body .btn-group .btn.btn-light:not(.dropdown-toggle) {
    border-radius: var(--dm-nav-radius) !important;
    text-decoration: none !important;
}

body #main-body .nav-pills:not(.navbar-nav):not(.nav-sidebar) > li > a,
body #main-body .nav-pills:not(.navbar-nav):not(.nav-sidebar) .nav-link {
    color: var(--dm-nav-navy) !important;
    background-color: #ffffff !important;
    font-weight: 500 !important;
}

body #main-body .nav-pills:not(.navbar-nav):not(.nav-sidebar) > li > a:hover,
body #main-body .nav-pills:not(.navbar-nav):not(.nav-sidebar) > li > a:focus,
body #main-body .nav-pills:not(.navbar-nav):not(.nav-sidebar) .nav-link:hover,
body #main-body .nav-pills:not(.navbar-nav):not(.nav-sidebar) .nav-link:focus {
    color: var(--dm-nav-navy) !important;
    background-color: var(--dm-nav-orange-soft) !important;
    outline: none !important;
}

body #main-body .nav-pills:not(.navbar-nav):not(.nav-sidebar) > li.active > a,
body #main-body .nav-pills:not(.navbar-nav):not(.nav-sidebar) > li.active > a:hover,
body #main-body .nav-pills:not(.navbar-nav):not(.nav-sidebar) > li.active > a:focus,
body #main-body .nav-pills:not(.navbar-nav):not(.nav-sidebar) .nav-link.active {
    color: #ffffff !important;
    background-color: var(--dm-nav-navy) !important;
}

/* Breadcrumbs: keep understated and consistent with the link palette. */
body #main-body .breadcrumb,
body #main-body ol.breadcrumb,
body #main-body ul.breadcrumb {
    background-color: #ffffff !important;
    border: 1px solid var(--dm-nav-border) !important;
    border-radius: var(--dm-nav-radius) !important;
    color: var(--dm-nav-muted) !important;
    box-shadow: none !important;
}

body #main-body .breadcrumb a,
body #main-body ol.breadcrumb a,
body #main-body ul.breadcrumb a {
    color: var(--dm-nav-text) !important;
    text-decoration: none !important;
}

body #main-body .breadcrumb a:hover,
body #main-body .breadcrumb a:focus,
body #main-body ol.breadcrumb a:hover,
body #main-body ol.breadcrumb a:focus,
body #main-body ul.breadcrumb a:hover,
body #main-body ul.breadcrumb a:focus {
    color: var(--dm-nav-orange) !important;
    text-decoration: none !important;
}

body #main-body .breadcrumb > .active,
body #main-body .breadcrumb .active {
    color: var(--dm-nav-muted) !important;
}
</style>
HTML;
});
