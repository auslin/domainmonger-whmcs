<?php
/**
 * DomainMonger WHMCS global empty states/helper panels consistency.
 *
 * Patch 421
 * - Component-level cleanup for empty states, no-records messages, helper panels, and callout boxes.
 * - Keeps this separate from alerts (Patch 416), tables (Patch 411/420), and buttons (Patch 409).
 * - Avoids templates, language files, integration files, and order-form logic.
 * - Skips the custom v8x register route and the isolated WHMCS v9 support route.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 1015, function ($vars) {
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
<style id="domainmonger-global-empty-states-helper-panels">
:root {
    --dm-empty-navy: #163a5f;
    --dm-empty-navy-hover: #214e7a;
    --dm-empty-orange: #f58220;
    --dm-empty-orange-soft: #fff3e8;
    --dm-empty-border: #d7dee6;
    --dm-empty-bg: #ffffff;
    --dm-empty-muted-bg: #f7f9fb;
    --dm-empty-text: #1f2933;
    --dm-empty-muted: #6c757d;
    --dm-empty-radius: 8px;
}

/* Empty/no-record states: white card, navy emphasis, muted supporting text. */
body #main-body .empty-state,
body #main-body .emptyState,
body #main-body .empty-state-panel,
body #main-body .no-records,
body #main-body .no-records-found,
body #main-body .no-results,
body #main-body .no-results-found,
body #main-body .no-data,
body #main-body .nodata,
body #main-body .no-domains,
body #main-body .no-tickets,
body #main-body .no-invoices,
body #main-body .no-services,
body #main-body .no-payment-methods,
body #main-body .dataTables_empty,
body #main-body td.dataTables_empty {
    background: var(--dm-empty-bg) !important;
    border: 1px solid var(--dm-empty-border) !important;
    border-radius: var(--dm-empty-radius) !important;
    color: var(--dm-empty-text) !important;
    font-size: 14px !important;
    font-weight: 400 !important;
    line-height: 1.5 !important;
    padding: 18px 20px !important;
    text-align: center !important;
}

body #main-body td.dataTables_empty {
    border-left: 0 !important;
    border-right: 0 !important;
}

body #main-body .empty-state h1,
body #main-body .empty-state h2,
body #main-body .empty-state h3,
body #main-body .empty-state h4,
body #main-body .emptyState h1,
body #main-body .emptyState h2,
body #main-body .emptyState h3,
body #main-body .emptyState h4,
body #main-body .empty-state-title,
body #main-body .no-records-title,
body #main-body .no-results-title {
    color: var(--dm-empty-navy) !important;
    font-size: 16px !important;
    font-weight: 600 !important;
    line-height: 1.35 !important;
    margin: 0 0 6px !important;
    text-transform: none !important;
}

body #main-body .empty-state p,
body #main-body .emptyState p,
body #main-body .empty-state-description,
body #main-body .no-records-description,
body #main-body .no-results-description,
body #main-body .empty-state small,
body #main-body .emptyState small {
    color: var(--dm-empty-muted) !important;
    font-size: 13px !important;
    font-weight: 400 !important;
    line-height: 1.5 !important;
}

body #main-body .empty-state i,
body #main-body .emptyState i,
body #main-body .no-records i,
body #main-body .no-results i,
body #main-body .empty-state .fa,
body #main-body .emptyState .fa {
    color: var(--dm-empty-navy) !important;
    opacity: 0.85 !important;
}

/* Helper/callout panels that are not semantic alerts. */
body #main-body .well,
body #main-body .well-sm,
body #main-body .well-lg,
body #main-body .callout,
body #main-body .helper-box,
body #main-body .help-box,
body #main-body .info-box,
body #main-body .notice-box,
body #main-body .content-box,
body #main-body .summary-box,
body #main-body .instruction-box,
body #main-body .instructions,
body #main-body .form-instructions,
body #main-body .panel-body .well,
body #main-body .card-body .well {
    background: var(--dm-empty-muted-bg) !important;
    border: 1px solid var(--dm-empty-border) !important;
    border-radius: var(--dm-empty-radius) !important;
    box-shadow: none !important;
    color: var(--dm-empty-text) !important;
}

body #main-body .well,
body #main-body .helper-box,
body #main-body .help-box,
body #main-body .info-box,
body #main-body .notice-box,
body #main-body .content-box,
body #main-body .summary-box,
body #main-body .instruction-box,
body #main-body .instructions,
body #main-body .form-instructions {
    padding: 14px 16px !important;
}

body #main-body .well h1,
body #main-body .well h2,
body #main-body .well h3,
body #main-body .well h4,
body #main-body .helper-box h1,
body #main-body .helper-box h2,
body #main-body .helper-box h3,
body #main-body .helper-box h4,
body #main-body .help-box h1,
body #main-body .help-box h2,
body #main-body .help-box h3,
body #main-body .help-box h4,
body #main-body .info-box h1,
body #main-body .info-box h2,
body #main-body .info-box h3,
body #main-body .info-box h4,
body #main-body .notice-box h1,
body #main-body .notice-box h2,
body #main-body .notice-box h3,
body #main-body .notice-box h4 {
    color: var(--dm-empty-navy) !important;
    font-weight: 600 !important;
    text-transform: none !important;
}

body #main-body .well p,
body #main-body .helper-box p,
body #main-body .help-box p,
body #main-body .info-box p,
body #main-body .notice-box p,
body #main-body .content-box p,
body #main-body .summary-box p,
body #main-body .instruction-box p,
body #main-body .instructions p,
body #main-body .form-instructions p {
    color: var(--dm-empty-text) !important;
    font-size: 13px !important;
    line-height: 1.5 !important;
}

body #main-body .well small,
body #main-body .helper-box small,
body #main-body .help-box small,
body #main-body .info-box small,
body #main-body .notice-box small,
body #main-body .content-box small,
body #main-body .summary-box small,
body #main-body .instruction-box small,
body #main-body .instructions small,
body #main-body .form-instructions small {
    color: var(--dm-empty-muted) !important;
}

/* Keep links inside helper areas aligned with the global link palette. */
body #main-body .empty-state a:not(.btn),
body #main-body .emptyState a:not(.btn),
body #main-body .well a:not(.btn),
body #main-body .helper-box a:not(.btn),
body #main-body .help-box a:not(.btn),
body #main-body .info-box a:not(.btn),
body #main-body .notice-box a:not(.btn),
body #main-body .content-box a:not(.btn),
body #main-body .summary-box a:not(.btn),
body #main-body .instruction-box a:not(.btn) {
    color: var(--dm-empty-navy) !important;
    font-weight: 500 !important;
    text-decoration: none !important;
}

body #main-body .empty-state a:not(.btn):hover,
body #main-body .empty-state a:not(.btn):focus,
body #main-body .emptyState a:not(.btn):hover,
body #main-body .emptyState a:not(.btn):focus,
body #main-body .well a:not(.btn):hover,
body #main-body .well a:not(.btn):focus,
body #main-body .helper-box a:not(.btn):hover,
body #main-body .helper-box a:not(.btn):focus,
body #main-body .help-box a:not(.btn):hover,
body #main-body .help-box a:not(.btn):focus,
body #main-body .info-box a:not(.btn):hover,
body #main-body .info-box a:not(.btn):focus,
body #main-body .notice-box a:not(.btn):hover,
body #main-body .notice-box a:not(.btn):focus,
body #main-body .content-box a:not(.btn):hover,
body #main-body .content-box a:not(.btn):focus,
body #main-body .summary-box a:not(.btn):hover,
body #main-body .summary-box a:not(.btn):focus,
body #main-body .instruction-box a:not(.btn):hover,
body #main-body .instruction-box a:not(.btn):focus {
    color: var(--dm-empty-orange) !important;
}
</style>
HTML;
});
