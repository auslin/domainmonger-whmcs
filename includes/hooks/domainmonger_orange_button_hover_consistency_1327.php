<?php
/**
 * DomainMonger Patch 1327
 * Standardize active primary-orange button hover/focus to #d8741f.
 *
 * - Loads in the document head to avoid a late repaint.
 * - Does not alter disabled, danger, secondary/nav, or outline controls.
 * - Protects the custom v8x Register Domains namespinner route.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaHeadOutput', 200, function ($vars) {
    $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '');
    $scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? '');

    // Preserve the isolated WHMCS v9 support/testing route.
    if (stripos($requestUri, 'dmv9support=1') !== false) {
        return '';
    }

    // Preserve the confirmed custom v8x Register Domains namespinner.
    $isCart = stripos($scriptName, '/cart.php') !== false
        || stripos($requestUri, '/cart.php') !== false;
    $isRegisterRoute = preg_match('/(?:\?|&)domain=(?:register|r)(?:&|$)/i', $requestUri) === 1;

    if ($isCart && $isRegisterRoute) {
        return '';
    }

    return <<<'HTML'
<style id="dm-orange-button-hover-consistency-1327">
:root,
body.whmcsbody {
    --dm-orange-hover: #d8741f;
    --dm-brand-orange-soft: #d8741f;
    --dm-rc-orange-hover: #d8741f;
}

/*
 * Primary orange controls only.
 * Danger, secondary/nav, outline, disabled, and aria-disabled controls are
 * deliberately excluded so their existing visual hierarchy is preserved.
 */
body.whmcsbody #main-body .btn-primary:not(.btn-danger):not(.btn-secondary):not(.btn-info):not(.btn-default):not(.btn-outline-primary):not(.btn-outline-secondary):not(.disabled):not([disabled]):not([aria-disabled="true"]):hover,
body.whmcsbody #main-body .btn-primary:not(.btn-danger):not(.btn-secondary):not(.btn-info):not(.btn-default):not(.btn-outline-primary):not(.btn-outline-secondary):not(.disabled):not([disabled]):not([aria-disabled="true"]):focus,
body.whmcsbody #main-body .btn-primary:not(.btn-danger):not(.btn-secondary):not(.btn-info):not(.btn-default):not(.btn-outline-primary):not(.btn-outline-secondary):not(.disabled):not([disabled]):not([aria-disabled="true"]):focus-visible,
body.whmcsbody #main-body .btn-success:not(.btn-danger):not(.disabled):not([disabled]):not([aria-disabled="true"]):hover,
body.whmcsbody #main-body .btn-success:not(.btn-danger):not(.disabled):not([disabled]):not([aria-disabled="true"]):focus,
body.whmcsbody #main-body .btn-success:not(.btn-danger):not(.disabled):not([disabled]):not([aria-disabled="true"]):focus-visible,
body.whmcsbody #main-body .btn-warning:not(.btn-danger):not(.disabled):not([disabled]):not([aria-disabled="true"]):hover,
body.whmcsbody #main-body .btn-warning:not(.btn-danger):not(.disabled):not([disabled]):not([aria-disabled="true"]):focus,
body.whmcsbody #main-body .btn-warning:not(.btn-danger):not(.disabled):not([disabled]):not([aria-disabled="true"]):focus-visible,
body.whmcsbody #main-body .btn-orange:not(.disabled):not([disabled]):not([aria-disabled="true"]):hover,
body.whmcsbody #main-body .btn-orange:not(.disabled):not([disabled]):not([aria-disabled="true"]):focus,
body.whmcsbody #main-body .btn-orange:not(.disabled):not([disabled]):not([aria-disabled="true"]):focus-visible,
body.whmcsbody #main-body .btn-order-now:not(.disabled):not([disabled]):not([aria-disabled="true"]):hover,
body.whmcsbody #main-body .btn-order-now:not(.disabled):not([disabled]):not([aria-disabled="true"]):focus,
body.whmcsbody #main-body .btn-order-now:not(.disabled):not([disabled]):not([aria-disabled="true"]):focus-visible,
body.whmcsbody #main-body .checkout-btn:not(.disabled):not([disabled]):not([aria-disabled="true"]):hover,
body.whmcsbody #main-body .checkout-btn:not(.disabled):not([disabled]):not([aria-disabled="true"]):focus,
body.whmcsbody #main-body .checkout-btn:not(.disabled):not([disabled]):not([aria-disabled="true"]):focus-visible {
    background: #d8741f !important;
    background-color: #d8741f !important;
    border-color: #d8741f !important;
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
}
</style>
HTML;
});
