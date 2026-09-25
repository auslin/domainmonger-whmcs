<?php
/**
 * DomainMonger shopping-cart product-group headers.
 *
 * Patch 1321
 * - Applies the confirmed Hosting product-group presentation to the Email,
 *   DNS, and SSL Certificates store landing pages.
 * - Adds one full-width navy page header above the shopping-cart sidebar and
 *   product area.
 * - Removes only the native order-form .header-lined page title/tagline.
 * - Leaves product cards, pricing, ordering links, sidebar, cart, checkout,
 *   Register Domains, Transfer Domains, and unrelated store groups untouched.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('domainmongerStoreGroupHeaderConfig1321')) {
    /**
     * Return header labels only for the approved store-group landing routes.
     * Exact matching keeps product-detail routes and other cart pages out.
     */
    function domainmongerStoreGroupHeaderConfig1321(): array
    {
        $rp = $_GET['rp'] ?? '';
        if (is_array($rp)) {
            return [];
        }

        $route = '/' . trim(strtolower(trim((string) $rp)), '/');

        $routes = [
            '/store/email' => [
                'title' => 'Email',
                'subtitle' => 'Choose the email plan that fits your needs.',
            ],
            '/store/email-hosting' => [
                'title' => 'Email',
                'subtitle' => 'Choose the email plan that fits your needs.',
            ],
            '/store/dns' => [
                'title' => 'DNS',
                'subtitle' => 'Choose the DNS plan that fits your needs.',
            ],
            '/store/dns-hosting' => [
                'title' => 'DNS',
                'subtitle' => 'Choose the DNS plan that fits your needs.',
            ],
            '/store/ssl-certificates' => [
                'title' => 'SSL Certificates',
                'subtitle' => 'Choose the SSL certificate that fits your needs.',
            ],
            '/store/ssl' => [
                'title' => 'SSL Certificates',
                'subtitle' => 'Choose the SSL certificate that fits your needs.',
            ],
        ];

        if (!isset($routes[$route])) {
            return [];
        }

        return $routes[$route] + ['route' => $route];
    }
}

add_hook('ClientAreaHeadOutput', 1321, static function ($vars) {
    if (domainmongerStoreGroupHeaderConfig1321() === []) {
        return '';
    }

    return <<<'HTML'
<style id="domainmonger-store-group-headers-1321-css">
body.dm-store-group-page-1321 .dm-store-group-page-header-1321 {
    box-sizing: border-box !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    width: 100% !important;
    min-height: 62px !important;
    margin: 0 0 12px !important;
    padding: 13px 14px !important;
    text-align: left !important;
    border: 0 !important;
    border-radius: 0 !important;
    background: #163a5f !important;
    background-color: #163a5f !important;
    color: #ffffff !important;
    box-shadow: none !important;
}

body.dm-store-group-page-1321 .dm-store-group-page-header-1321__text {
    flex: 1 1 auto !important;
    min-width: 0 !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    text-align: left !important;
}

body.dm-store-group-page-1321 .dm-store-group-page-header-1321__title {
    display: block !important;
    margin: 0 !important;
    padding: 0 !important;
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    font-size: 18px !important;
    font-weight: 700 !important;
    line-height: 1.15 !important;
    letter-spacing: 0 !important;
    text-transform: none !important;
    text-shadow: none !important;
    text-align: left !important;
}

body.dm-store-group-page-1321 .dm-store-group-page-header-1321__subtitle {
    display: block !important;
    margin: 2px 0 0 !important;
    padding: 0 !important;
    color: rgba(255, 255, 255, 0.91) !important;
    -webkit-text-fill-color: rgba(255, 255, 255, 0.91) !important;
    font-size: 13px !important;
    font-weight: 500 !important;
    line-height: 1.2 !important;
    letter-spacing: 0 !important;
    text-transform: none !important;
    text-shadow: none !important;
    text-align: left !important;
}

/* Remove only the native group title/tagline block from products.tpl. */
body.dm-store-group-page-1321 #order-standard_cart .dm-store-native-header-hidden-1321 {
    display: none !important;
}

@media (max-width: 767px) {
    body.dm-store-group-page-1321 .dm-store-group-page-header-1321 {
        min-height: 0 !important;
        padding: 12px !important;
    }

    body.dm-store-group-page-1321 .dm-store-group-page-header-1321__title {
        font-size: 17px !important;
    }
}
</style>
HTML;
});

add_hook('ClientAreaFooterOutput', 1321, static function ($vars) {
    $config = domainmongerStoreGroupHeaderConfig1321();
    if ($config === []) {
        return '';
    }

    $configJson = json_encode(
        $config,
        JSON_UNESCAPED_SLASHES
        | JSON_UNESCAPED_UNICODE
        | JSON_HEX_TAG
        | JSON_HEX_AMP
        | JSON_HEX_APOS
        | JSON_HEX_QUOT
    );

    if (!is_string($configJson)) {
        return '';
    }

    return '<script id="domainmonger-store-group-headers-1321-js">'
        . '(function(){"use strict";var cfg=' . $configJson . ';'
        . <<<'JS'
if (!cfg || !cfg.title || window.domainmongerStoreGroupHeader1321Loaded) {
    return;
}
window.domainmongerStoreGroupHeader1321Loaded = true;

function buildStoreHeader1321() {
    var primary = document.querySelector('#main-body .primary-content');
    var orderForm = primary ? primary.querySelector('#order-standard_cart') : null;
    var existingHeader;
    var nativeHeader;
    var header;
    var text;
    var title;
    var subtitle;

    if (!primary || !orderForm) {
        return false;
    }

    document.body.classList.add('dm-store-group-page-1321');

    /* Patch 1320 already owns Hosting. On these routes this guard also makes
       the patch safe if the shared header system gains native route support. */
    existingHeader = primary.querySelector(
        '.dm-store-group-page-header-1321, .dm-portal-page-header-1283'
    );

    if (!existingHeader) {
        header = document.createElement('section');
        header.className = 'dm-store-group-page-header-1321';
        header.setAttribute('aria-labelledby', 'dmStoreGroupTitle1321');

        text = document.createElement('div');
        text.className = 'dm-store-group-page-header-1321__text';

        title = document.createElement('h1');
        title.id = 'dmStoreGroupTitle1321';
        title.className = 'dm-store-group-page-header-1321__title';
        title.textContent = cfg.title;

        subtitle = document.createElement('span');
        subtitle.className = 'dm-store-group-page-header-1321__subtitle';
        subtitle.textContent = cfg.subtitle || '';

        text.appendChild(title);
        if (cfg.subtitle) {
            text.appendChild(subtitle);
        }
        header.appendChild(text);

        /* Insert above #order-standard_cart so the header spans both the
           shopping-cart sidebar and the product column. */
        primary.insertBefore(header, orderForm);
    }

    nativeHeader = orderForm.querySelector('.header-lined');
    if (nativeHeader && !nativeHeader.closest('.dm-store-group-page-header-1321, .dm-portal-page-header-1283')) {
        nativeHeader.classList.add('dm-store-native-header-hidden-1321');
    }

    return true;
}

function startStoreHeader1321() {
    var attempts = 0;
    var timer;

    if (buildStoreHeader1321()) {
        return;
    }

    timer = window.setInterval(function () {
        attempts += 1;
        if (buildStoreHeader1321() || attempts >= 20) {
            window.clearInterval(timer);
        }
    }, 100);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', startStoreHeader1321, { once: true });
} else {
    startStoreHeader1321();
}
JS
        . '})();</script>';
});
