<?php
/**
 * DomainMonger WHMCS global status badge/label palette.
 *
 * Patch 413
 * - Component-level global cleanup for WHMCS client-area badges, labels, and status pills.
 * - Keeps status colors meaningful: closed/inactive/default muted gray, info/primary navy,
 *   danger red, warning pale WHMCS yellow, success green.
 * - Avoids broad typography or layout changes.
 * - Skips the custom v8x register route and the isolated WHMCS v9 support route.
 * - Does not touch templates, language files, integration files, or order-form logic.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 1004, function ($vars) {
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
<style id="domainmonger-global-status-badges">
:root {
    --dm-badge-navy: #163a5f;
    --dm-badge-orange: #f58220;
    --dm-badge-gray: #6c757d;
    --dm-badge-gray-border: #5f6871;
    --dm-badge-muted-bg: #f1f3f5;
    --dm-badge-muted-text: #5f6871;
    --dm-badge-red: #b94a48;
    --dm-badge-red-border: #a64240;
    --dm-badge-warning-bg: #fff3cd;
    --dm-badge-warning-text: #604200;
    --dm-badge-warning-border: #f0d98c;
    --dm-badge-success: #2f7d4f;
    --dm-badge-success-border: #286b43;
    --dm-badge-radius: 999px;
}

/*
 * Shared badge/label shape and text weight.
 * This is intentionally component-scoped and does not change page typography.
 */
body #main-body .badge,
body #main-body .label,
body #main-body span.status,
body #main-body .status-label,
body #main-body .ticket-status,
body #main-body .domain-status,
body #main-body .invoice-status {
    border-radius: var(--dm-badge-radius) !important;
    font-weight: 650 !important;
    line-height: 1.15 !important;
    text-transform: none !important;
    letter-spacing: 0 !important;
    text-decoration: none !important;
    white-space: nowrap !important;
}

/* Default/secondary/closed/inactive states should be muted gray, not random Bootstrap gray/blue. */
body #main-body .badge-default,
body #main-body .badge-secondary,
body #main-body .label-default,
body #main-body .label-secondary,
body #main-body .status-closed,
body #main-body .ticket-status-closed,
body #main-body .ticket-status.status-closed,
body #main-body .domain-status-expired,
body #main-body .domain-status-cancelled,
body #main-body .domain-status-terminated,
body #main-body .status-inactive,
body #main-body .status-cancelled,
body #main-body .status-terminated,
body #main-body .status-expired,
body #main-body .label-closed,
body #main-body .badge-closed,
body #main-body .label-inactive,
body #main-body .badge-inactive {
    background: var(--dm-badge-gray) !important;
    background-color: var(--dm-badge-gray) !important;
    border-color: var(--dm-badge-gray-border) !important;
    color: #ffffff !important;
}

/* Primary/info status pills use the DomainMonger navy palette. */
body #main-body .badge-primary,
body #main-body .badge-info,
body #main-body .label-primary,
body #main-body .label-info,
body #main-body .status-pending,
body #main-body .status-open,
body #main-body .ticket-status-open,
body #main-body .label-open,
body #main-body .badge-open {
    background: var(--dm-badge-navy) !important;
    background-color: var(--dm-badge-navy) !important;
    border-color: var(--dm-badge-navy) !important;
    color: #ffffff !important;
}

/* Success/active remains green because it communicates a positive status. */
body #main-body .badge-success,
body #main-body .label-success,
body #main-body .status-active,
body #main-body .domain-status-active,
body #main-body .invoice-status-paid,
body #main-body .label-active,
body #main-body .badge-active,
body #main-body .label-paid,
body #main-body .badge-paid {
    background: var(--dm-badge-success) !important;
    background-color: var(--dm-badge-success) !important;
    border-color: var(--dm-badge-success-border) !important;
    color: #ffffff !important;
}

/* Danger/error/fraud/unpaid states use the approved DomainMonger danger red. */
body #main-body .badge-danger,
body #main-body .label-danger,
body #main-body .status-fraud,
body #main-body .status-unpaid,
body #main-body .status-overdue,
body #main-body .status-suspended,
body #main-body .domain-status-suspended,
body #main-body .invoice-status-unpaid,
body #main-body .label-unpaid,
body #main-body .badge-unpaid,
body #main-body .label-overdue,
body #main-body .badge-overdue {
    background: var(--dm-badge-red) !important;
    background-color: var(--dm-badge-red) !important;
    border-color: var(--dm-badge-red-border) !important;
    color: #ffffff !important;
}

/* Warning remains pale WHMCS yellow with dark readable text. */
body #main-body .badge-warning,
body #main-body .label-warning,
body #main-body .status-pending-transfer,
body #main-body .status-pending-registration,
body #main-body .status-grace,
body #main-body .status-redemption,
body #main-body .label-pending,
body #main-body .badge-pending {
    background: var(--dm-badge-warning-bg) !important;
    background-color: var(--dm-badge-warning-bg) !important;
    border-color: var(--dm-badge-warning-border) !important;
    color: var(--dm-badge-warning-text) !important;
}

/* Keep links/icons inside badges readable and remove accidental underlines. */
body #main-body .badge *,
body #main-body .label *,
body #main-body span.status *,
body #main-body .status-label *,
body #main-body .ticket-status *,
body #main-body .domain-status *,
body #main-body .invoice-status * {
    color: inherit !important;
    text-decoration: none !important;
}

/* Mild hover polish for clickable status pills without changing meaning. */
body #main-body a.badge:hover,
body #main-body a.badge:focus,
body #main-body a.label:hover,
body #main-body a.label:focus {
    filter: brightness(0.96);
    text-decoration: none !important;
}
</style>
HTML;
});
