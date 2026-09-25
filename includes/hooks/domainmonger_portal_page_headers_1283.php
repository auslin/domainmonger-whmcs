<?php
/**
 * DomainMonger shared portal page headers and My Domains toolbar cleanup.
 *
 * Patch 1318
 * - Adds the established navy page header to the requested non-module pages:
 *   My Domains, Register/Transfer/Renew Domains, Open Ticket, and the Billing,
 *   Support, and My Account page families.
 * - Uses route/template detection only; no database, language, or backend form changes.
 * - Preserves the custom Register Domains namespinner and all existing page logic.
 * - Restyles the My Domains selected-domain action row as a toolbar rather than
 *   a separate menu/frame.
 * - Removes duplicate inner Register/Transfer page titles and the extra Register
 *   Domains "Find your domain" blue heading without altering either form.
 * - Keeps the confirmed Register/Transfer cleanup from Patch 1288.
 * - Patch 1318 added the confirmed My Services header.
 * - Patch 1319 replaces the ineffective Hosting product-detail detection with
 *   the actual WHMCS Hosting store/product-group route.
 * - Patch 1320 replaces the ineffective generic title match on that route with
 *   an exact Hosting-only order-form header target.
 * - Patch 1292 detects pages with a left sidebar and places the shared navy
 *   page header above both the sidebar and main-content columns. Pages without
 *   a sidebar keep the established content-column header placement.
 * - Patch 1446 adds the standard shared header to the product-domain selection
 *   page used by Lite Hosting, Bronze Hosting, and other domain-required services.
 * - Patch 1450 adds the same shared header to the product-configuration page.
 * - Patch 1456 adds the shared Order Confirmation header to cart.php?a=complete.
 * - Patch 1457 removes the duplicate native Order Confirmation title block.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('domainmongerPortalRequestValue1283')) {
    function domainmongerPortalRequestValue1283(string $key): string
    {
        if (!isset($_REQUEST[$key]) || is_array($_REQUEST[$key])) {
            return '';
        }

        return strtolower(trim((string) $_REQUEST[$key]));
    }
}

if (!function_exists('domainmongerPortalHeaderConfig1283')) {
    /**
     * Return the page header configuration for the current requested page.
     * An empty array means the page is outside the approved scope.
     */
    function domainmongerPortalHeaderConfig1283(array $vars): array
    {
        $templateFile = strtolower(trim((string) ($vars['templatefile'] ?? '')));
        $filename = strtolower(trim((string) ($vars['filename'] ?? '')));
        $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '');
        $path = strtolower((string) parse_url($requestUri, PHP_URL_PATH));
        $action = domainmongerPortalRequestValue1283('action');
        $cartAction = domainmongerPortalRequestValue1283('a');
        $domainMode = domainmongerPortalRequestValue1283('domain');
        $gid = domainmongerPortalRequestValue1283('gid');
        $storeRoute = domainmongerPortalRequestValue1283('rp');
        $storeRoute = '/' . ltrim(rawurldecode($storeRoute), '/');
        $storeRoute = rtrim($storeRoute, '/');

        // Keep diagnostics, isolated testing routes, converted domain tools,
        // ClouDNS pages, and the already-converted bulk pages untouched.
        if (stripos($requestUri, 'dmv9support=1') !== false
            || stripos($requestUri, 'dmsupportdiag=') !== false
            || $action === 'bulkdomain'
            || $action === 'domaindetails'
            || $action === 'domaincontacts'
            || ($action === 'productdetails' && domainmongerPortalRequestValue1283('customaction') !== '')) {
            return [];
        }

        $configByTemplate = [
            // Domains.
            'clientareadomains' => ['My Domains', 'View and manage your registered domains.', 'domains'],
            'clientareadomainsx' => ['My Domains', 'View and manage your registered domains.', 'domains'],
            'domainregister' => ['Register Domains', 'Find and register your next domain.', 'standard'],
            'domaintransfer' => ['Transfer Domains', 'Transfer domains to DomainMonger.', 'standard'],
            'domain-renewals' => ['Renew Domains', 'Review and renew your domains.', 'standard'],
            'domainrenewals' => ['Renew Domains', 'Review and renew your domains.', 'standard'],

            // Services and hosting.
            'clientareaproducts' => ['My Services', 'View and manage your services and hosting accounts.', 'standard'],
            'configureproductdomain' => ['Choose a Domain', 'Choose how you would like to use a domain with your selected service.', 'standard'],
            'configureproduct' => ['Configure Product', 'Review your selected service options before continuing to checkout.', 'standard'],
            'complete' => ['Order Confirmation', 'Your order has been received and the confirmation details are shown below.', 'standard'],

            // Billing.
            'clientareainvoices' => ['My Invoices', 'View and manage your invoices.', 'standard'],
            'viewinvoice' => ['Invoice', 'Review invoice details and payment information.', 'standard'],
            'clientareaquotes' => ['My Quotes', 'Review your available quotes.', 'standard'],
            'viewquote' => ['Quote', 'Review quote details and available actions.', 'standard'],
            'masspay' => ['Mass Payment', 'Pay multiple outstanding invoices at once.', 'standard'],
            'clientareaaddfunds' => ['Add Funds', 'Add account credit for future purchases and renewals.', 'standard'],
            'account-paymentmethods' => ['Payment Methods', 'Manage your saved payment methods.', 'standard'],
            'account-paymentmethods-manage' => ['Payment Methods', 'Manage your saved payment methods.', 'standard'],

            // Support.
            'supportticketslist' => ['Support Tickets', 'View and manage your support requests.', 'standard'],
            'viewticket' => ['Support Ticket', 'Review and reply to your support request.', 'standard'],
            'supportticketsubmit-stepone' => ['Open Ticket', 'Tell us how we can help.', 'standard'],
            'supportticketsubmit-steptwo' => ['Open Ticket', 'Tell us how we can help.', 'standard'],
            'supportticketsubmit-customfields' => ['Open Ticket', 'Tell us how we can help.', 'standard'],
            'supportticketsubmit-kbsuggestions' => ['Open Ticket', 'Tell us how we can help.', 'standard'],
            'supportticketsubmit-confirm' => ['Ticket Submitted', 'Your support request has been received.', 'standard'],
            'announcements' => ['Announcements', 'News and updates from DomainMonger.', 'standard'],
            'viewannouncement' => ['Announcements', 'News and updates from DomainMonger.', 'standard'],
            'knowledgebase' => ['Knowledgebase', 'Find answers and helpful information.', 'standard'],
            'knowledgebasecat' => ['Knowledgebase', 'Find answers and helpful information.', 'standard'],
            'knowledgebasearticle' => ['Knowledgebase', 'Find answers and helpful information.', 'standard'],
            'downloads' => ['Downloads', 'Download available files and resources.', 'standard'],
            'downloadscat' => ['Downloads', 'Download available files and resources.', 'standard'],
            'downloaddenied' => ['Downloads', 'Download available files and resources.', 'standard'],
            'serverstatus' => ['Network Status', 'View current service status and network notices.', 'standard'],
            'networkissues' => ['Network Status', 'View current service status and network notices.', 'standard'],
            'viewnetworkissue' => ['Network Status', 'View current service status and network notices.', 'standard'],

            // My Account.
            'clientareadetails' => ['My Details', 'Review and update your account information.', 'standard'],
            'account-contacts-manage' => ['Contacts/Sub-Accounts', 'Manage account contacts and access.', 'standard'],
            'account-contacts-new' => ['Contacts/Sub-Accounts', 'Manage account contacts and access.', 'standard'],
            'clientareacontacts' => ['Contacts/Sub-Accounts', 'Manage account contacts and access.', 'standard'],
            'clientareaaddcontact' => ['Contacts/Sub-Accounts', 'Manage account contacts and access.', 'standard'],
            'user-profile' => ['Your Profile', 'Manage your personal profile information.', 'standard'],
            'user-password' => ['Change Password', 'Update your account password.', 'standard'],
            'clientareachangepw' => ['Change Password', 'Update your account password.', 'standard'],
            'user-security' => ['Security Settings', 'Manage account security and sign-in options.', 'standard'],
            'clientareasecurity' => ['Security Settings', 'Manage account security and sign-in options.', 'standard'],
            'account-user-management' => ['User Management', 'Manage account users and invitations.', 'standard'],
            'account-user-permissions' => ['Manage Permissions', 'Control account-user permissions.', 'standard'],
            'user-invite-accept' => ['User Invitation', 'Review and accept your account invitation.', 'standard'],
            'clientareaemails' => ['Email History', 'Review emails sent to your account.', 'standard'],
            'viewemail' => ['Email History', 'Review emails sent to your account.', 'standard'],
        ];

        if (isset($configByTemplate[$templateFile])) {
            return [
                'title' => $configByTemplate[$templateFile][0],
                'subtitle' => $configByTemplate[$templateFile][1],
                'mode' => $configByTemplate[$templateFile][2],
                'template' => $templateFile,
            ];
        }

        // Hosting store/product-group route. This is the public WHMCS Hosting
        // catalog page, not clientarea.php?action=productdetails.
        $productGroupLabel = '';
        if (isset($vars['productGroup'])) {
            $productGroup = $vars['productGroup'];
            if (is_array($productGroup)) {
                $productGroupLabel = trim((string) ($productGroup['headline'] ?? $productGroup['name'] ?? ''));
            } elseif (is_object($productGroup)) {
                $productGroupLabel = trim((string) ($productGroup->headline ?? $productGroup->name ?? ''));
            }
        }

        $normalisedGroupLabel = strtolower(preg_replace('/\s+/', ' ', $productGroupLabel) ?? '');
        $isHostingStoreRoute = ($storeRoute === '/store/hosting')
            || (preg_match('~/store/hosting/?$~i', $path) === 1)
            || ($templateFile === 'products' && $normalisedGroupLabel === 'hosting');

        if ($isHostingStoreRoute) {
            return [
                'title' => 'Hosting',
                'subtitle' => 'Choose the hosting plan that fits your needs.',
                'mode' => 'standard',
                'template' => $templateFile,
            ];
        }

        // Cart/order-form route fallbacks. These do not alter the order-form
        // templates or the custom namespinner; they only insert the shared header.
        if ($filename === 'cart' || str_contains($path, '/cart.php')) {
            if (($cartAction === 'add' && in_array($domainMode, ['register', 'r'], true))) {
                return ['title' => 'Register Domains', 'subtitle' => 'Find and register your next domain.', 'mode' => 'standard', 'template' => $templateFile];
            }
            if (($cartAction === 'add' && in_array($domainMode, ['transfer', 't'], true))) {
                return ['title' => 'Transfer Domains', 'subtitle' => 'Transfer domains to DomainMonger.', 'mode' => 'standard', 'template' => $templateFile];
            }
            if ($gid === 'renewals' || in_array($cartAction, ['renewals', 'domainrenewals'], true)) {
                return ['title' => 'Renew Domains', 'subtitle' => 'Review and renew your domains.', 'mode' => 'standard', 'template' => $templateFile];
            }
            if ($cartAction === 'complete') {
                return ['title' => 'Order Confirmation', 'subtitle' => 'Your order has been received and the confirmation details are shown below.', 'mode' => 'standard', 'template' => $templateFile];
            }
        }

        // Client-area route fallbacks for inherited parent-template pages.
        if ($filename === 'clientarea' || str_contains($path, '/clientarea.php')) {
            if ($action === 'services') {
                return ['title' => 'My Services', 'subtitle' => 'View and manage your services and hosting accounts.', 'mode' => 'standard', 'template' => $templateFile];
            }
        }

        // Friendly/basic route fallbacks for inherited parent-template pages.
        $pathMap = [
            '/supporttickets.php' => ['Support Tickets', 'View and manage your support requests.'],
            '/submitticket.php' => ['Open Ticket', 'Tell us how we can help.'],
            '/viewticket.php' => ['Support Ticket', 'Review and reply to your support request.'],
            '/announcements.php' => ['Announcements', 'News and updates from DomainMonger.'],
            '/knowledgebase.php' => ['Knowledgebase', 'Find answers and helpful information.'],
            '/downloads.php' => ['Downloads', 'Download available files and resources.'],
            '/serverstatus.php' => ['Network Status', 'View current service status and network notices.'],
            '/networkissues.php' => ['Network Status', 'View current service status and network notices.'],
            '/viewinvoice.php' => ['Invoice', 'Review invoice details and payment information.'],
            '/viewquote.php' => ['Quote', 'Review quote details and available actions.'],
            '/account/paymentmethods' => ['Payment Methods', 'Manage your saved payment methods.'],
            '/account/contacts' => ['Contacts/Sub-Accounts', 'Manage account contacts and access.'],
            '/account/users' => ['User Management', 'Manage account users and invitations.'],
            '/user/profile' => ['Your Profile', 'Manage your personal profile information.'],
            '/user/password' => ['Change Password', 'Update your account password.'],
            '/user/security' => ['Security Settings', 'Manage account security and sign-in options.'],
        ];

        foreach ($pathMap as $needle => $labels) {
            if (str_contains($path, $needle)) {
                return ['title' => $labels[0], 'subtitle' => $labels[1], 'mode' => 'standard', 'template' => $templateFile];
            }
        }

        // clientarea.php action fallbacks for inherited templates.
        if ($filename === 'clientarea' || str_contains($path, '/clientarea.php')) {
            $actionMap = [
                'domains' => ['My Domains', 'View and manage your registered domains.', 'domains'],
                'invoices' => ['My Invoices', 'View and manage your invoices.', 'standard'],
                'quotes' => ['My Quotes', 'Review your available quotes.', 'standard'],
                'masspay' => ['Mass Payment', 'Pay multiple outstanding invoices at once.', 'standard'],
                'addfunds' => ['Add Funds', 'Add account credit for future purchases and renewals.', 'standard'],
                'details' => ['My Details', 'Review and update your account information.', 'standard'],
                'contacts' => ['Contacts/Sub-Accounts', 'Manage account contacts and access.', 'standard'],
                'emails' => ['Email History', 'Review emails sent to your account.', 'standard'],
                'security' => ['Security Settings', 'Manage account security and sign-in options.', 'standard'],
            ];

            if (isset($actionMap[$action])) {
                return [
                    'title' => $actionMap[$action][0],
                    'subtitle' => $actionMap[$action][1],
                    'mode' => $actionMap[$action][2],
                    'template' => $templateFile,
                ];
            }
        }

        return [];
    }
}

add_hook('ClientAreaHeadOutput', 1283, static function ($vars) {
    $vars = is_array($vars) ? $vars : [];
    $config = domainmongerPortalHeaderConfig1283($vars);

    if ($config === []) {
        return '';
    }

    return <<<'HTML'
<style id="domainmonger-portal-page-headers-1283-css">
body.dm-portal-page-header-1283 .dm-portal-page-header-1283 {
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

body.dm-portal-page-header-1283 .dm-portal-page-header-1283__text {
    flex: 1 1 auto !important;
    min-width: 0 !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    text-align: left !important;
}

body.dm-portal-page-header-1283 .dm-portal-page-header-1283__title {
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

body.dm-portal-page-header-1283 .dm-portal-page-header-1283__subtitle {
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

/* Sidebar pages: the page header spans the complete row above both columns. */
body.dm-portal-page-header-1283.dm-portal-wide-header-1292 .dm-portal-page-header-wrap-1292 {
    box-sizing: border-box !important;
    flex: 0 0 100% !important;
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 0 12px !important;
}

body.dm-portal-page-header-1283.dm-portal-wide-header-1292
    .dm-portal-page-header-wrap-1292 > .dm-portal-page-header-1283 {
    width: 100% !important;
    margin: 0 !important;
}

/* My Domains previously connected the header directly to its workspace. When
   the header spans both columns, restore equal sidebar/content alignment and
   keep the action toolbar padding inside the white workspace instead. */
body.dm-portal-page-header-1283.dm-portal-wide-header-1292.dm-portal-domains-page-1283
    .dm-portal-domains-workspace-1283 {
    padding-top: 0 !important;
}

body.dm-portal-page-header-1283.dm-portal-wide-header-1292.dm-portal-domains-page-1283
    #domainForm > .btn-group.btn-group-sm,
body.dm-portal-page-header-1283.dm-portal-wide-header-1292.dm-portal-domains-page-1283
    #domainForm > .btn-group[role="group"] {
    margin-top: 12px !important;
}

/* Hide only a matching standalone native page title, not card/section titles. */
body.dm-portal-page-header-1283 .dm-native-page-title-hidden-1283 {
    display: none !important;
}

/* Hosting store: the shared navy header replaces the native order-form
   heading/tagline block. Scope this to the confirmed /store/hosting route so
   headings on every other product group remain untouched. */
body.dm-portal-page-header-1283.dm-portal-hosting-store-page-1283
    #order-standard_cart > .row > [class*="col-"] > .header-lined {
    display: none !important;
}

/* Order complete: remove only the original native title block now that the
   shared navy Order Confirmation header is present. */
body.dm-portal-page-header-1283.dm-portal-order-complete-page-1283
    #order-standard_cart > .row > .cart-body > .header-lined {
    display: none !important;
}

/* Register/Transfer: the shared navy header is the only page title.
   Keep the working forms and custom v8x namespinner intact. */
body.dm-portal-page-header-1283.dm-portal-register-page-1283
    #order-standard_cart .dm-v9-direct-page > .header-lined,
/* Remove the extra blue "Find your domain" strip, but retain the search card/body. */
body.dm-portal-page-header-1283.dm-portal-register-page-1283
    #order-standard_cart .dm-v9-direct-search-card > .dm-v9-direct-search-header {
    display: none !important;
}

body.dm-portal-page-header-1283.dm-portal-register-page-1283
    #order-standard_cart .dm-v9-direct-search-card > .dm-v9-direct-search-body {
    border-radius: 3px !important;
}

/* My Domains: connect the new header to the action/table workspace. */
body.dm-portal-page-header-1283.dm-portal-domains-page-1283 .dm-portal-page-header-1283 {
    margin-bottom: 0 !important;
}

body.dm-portal-page-header-1283.dm-portal-domains-page-1283 .dm-portal-domains-workspace-1283 {
    box-sizing: border-box !important;
    width: 100% !important;
    margin: 0 !important;
    padding-top: 12px !important;
    border: 0 !important;
    border-radius: 0 !important;
    background: #ffffff !important;
    box-shadow: none !important;
}

/* The selected-domain controls are an action toolbar, not a second menu. */
body.dm-portal-page-header-1283.dm-portal-domains-page-1283 #domainForm > .btn-group.btn-group-sm,
body.dm-portal-page-header-1283.dm-portal-domains-page-1283 #domainForm > .btn-group[role="group"] {
    box-sizing: border-box !important;
    display: flex !important;
    flex-wrap: wrap !important;
    align-items: center !important;
    gap: 6px !important;
    width: auto !important;
    min-height: 0 !important;
    margin: 0 12px 12px !important;
    padding: 0 !important;
    border: 0 !important;
    border-radius: 0 !important;
    background: transparent !important;
    background-color: transparent !important;
    box-shadow: none !important;
    overflow: visible !important;
}

body.dm-portal-page-header-1283.dm-portal-domains-page-1283 #domainForm > .btn-group.btn-group-sm > .btn,
body.dm-portal-page-header-1283.dm-portal-domains-page-1283 #domainForm > .btn-group[role="group"] > .btn,
body.dm-portal-page-header-1283.dm-portal-domains-page-1283 #domainForm > .btn-group > .btn-group > .btn {
    float: none !important;
    min-height: 34px !important;
    margin: 0 !important;
    border-radius: 4px !important;
    box-shadow: none !important;
}

body.dm-portal-page-header-1283.dm-portal-domains-page-1283 #domainForm > .btn-group > .btn-group {
    display: inline-flex !important;
    float: none !important;
    width: auto !important;
    margin: 0 !important;
    padding: 0 !important;
    border: 0 !important;
    border-radius: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
}

body.dm-portal-page-header-1283.dm-portal-domains-page-1283 #domainForm > .btn-group .btn + .btn,
body.dm-portal-page-header-1283.dm-portal-domains-page-1283 #domainForm > .btn-group .btn-group + .btn,
body.dm-portal-page-header-1283.dm-portal-domains-page-1283 #domainForm > .btn-group .btn + .btn-group {
    margin-left: 0 !important;
}

@media (max-width: 767px) {
    body.dm-portal-page-header-1283 .dm-portal-page-header-1283 {
        min-height: 0 !important;
        padding: 12px 12px !important;
    }

    body.dm-portal-page-header-1283 .dm-portal-page-header-1283__title {
        font-size: 17px !important;
    }

    body.dm-portal-page-header-1283.dm-portal-domains-page-1283 #domainForm > .btn-group.btn-group-sm,
    body.dm-portal-page-header-1283.dm-portal-domains-page-1283 #domainForm > .btn-group[role="group"] {
        margin-left: 10px !important;
        margin-right: 10px !important;
    }
}
</style>
HTML;
});

add_hook('ClientAreaFooterOutput', 1283, static function ($vars) {
    $vars = is_array($vars) ? $vars : [];
    $config = domainmongerPortalHeaderConfig1283($vars);

    if ($config === []) {
        return '';
    }

    $configJson = json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    if (!is_string($configJson)) {
        return '';
    }

    return '<script id="domainmonger-portal-page-headers-1283-js">'
        . '(function(){"use strict";'
        . 'var cfg=' . $configJson . ';'
        . <<<'JS'
if (!cfg || !cfg.title || window.domainmongerPortalHeader1283Loaded) {
    return;
}
window.domainmongerPortalHeader1283Loaded = true;

function normalize(value) {
    return String(value || '')
        .replace(/\s+/g, ' ')
        .trim()
        .toLowerCase();
}

function directChildByClass(parent, className) {
    var children = parent ? parent.children : [];
    var i;

    for (i = 0; i < children.length; i += 1) {
        if (children[i].classList && children[i].classList.contains(className)) {
            return children[i];
        }
    }

    return null;
}


function sidebarLayoutRow(primary) {
    var row;
    var children;
    var i;
    var child;

    if (!primary || !primary.parentElement) {
        return null;
    }

    row = primary.parentElement;
    if (!row.classList || !row.classList.contains('row')) {
        return null;
    }

    children = row.children || [];
    for (i = 0; i < children.length; i += 1) {
        child = children[i];
        if (child === primary || (child.classList && child.classList.contains('dm-portal-page-header-wrap-1292'))) {
            continue;
        }

        if ((child.classList && child.classList.contains('sidebar')) || child.querySelector('.sidebar')) {
            return row;
        }
    }

    return null;
}

function hideMatchingNativeTitle(primary) {
    var candidates = primary.querySelectorAll('h1, .header-lined h1, .page-header h1, .content-header h1');
    var wanted = normalize(cfg.title);
    var aliases = [wanted];

    if (wanted === 'my details') {
        aliases.push('account details');
    }
    if (wanted === 'security settings') {
        aliases.push('account security');
    }
    if (wanted === 'edit whois contact info') {
        aliases.push('edit contact information');
    }
    if (wanted === 'register domains') {
        aliases.push('register a new domain');
    }
    if (wanted === 'my services') {
        aliases.push('services', 'my products & services', 'my products and services');
    }
    if (wanted === 'hosting') {
        aliases.push('product details', 'service details');
    }
    if (wanted === 'configure product') {
        aliases.push('configure');
    }

    Array.prototype.some.call(candidates, function (heading) {
        var holder;
        var text;

        if (heading.closest('.card-header, .panel-heading, .modal-header, .dm-portal-page-header-1283')) {
            return false;
        }

        text = normalize(heading.textContent);
        if (aliases.indexOf(text) === -1) {
            return false;
        }

        holder = heading.closest('.header-lined, .page-header, .content-header');
        (holder || heading).classList.add('dm-native-page-title-hidden-1283');
        return true;
    });
}


function buildHeader() {
    var primary = document.querySelector('#main-body .primary-content');
    var header;
    var text;
    var title;
    var subtitle;
    var insertionTarget;
    var domainsWorkspace;
    var layoutRow;
    var wideHeaderWrap;

    if (!primary || primary.querySelector('.dm-portal-page-header-1283')) {
        return;
    }

    header = document.createElement('section');
    header.className = 'dm-portal-page-header-1283';
    header.setAttribute('aria-labelledby', 'dmPortalPageTitle1283');

    text = document.createElement('div');
    text.className = 'dm-portal-page-header-1283__text';

    title = document.createElement('h1');
    title.id = 'dmPortalPageTitle1283';
    title.className = 'dm-portal-page-header-1283__title';
    title.textContent = cfg.title;

    subtitle = document.createElement('span');
    subtitle.className = 'dm-portal-page-header-1283__subtitle';
    subtitle.textContent = cfg.subtitle || '';

    text.appendChild(title);
    if (cfg.subtitle) {
        text.appendChild(subtitle);
    }
    header.appendChild(text);

    document.body.classList.add('dm-portal-page-header-1283');
    document.body.classList.add('dm-portal-template-' + String(cfg.template || 'route').replace(/[^a-z0-9_-]/gi, '-'));

    if (normalize(cfg.title) === 'register domains') {
        document.body.classList.add('dm-portal-register-page-1283');
    } else if (normalize(cfg.title) === 'transfer domains') {
        document.body.classList.add('dm-portal-transfer-page-1283');
    } else if (normalize(cfg.title) === 'hosting') {
        document.body.classList.add('dm-portal-hosting-store-page-1283');
    } else if (normalize(cfg.title) === 'order confirmation') {
        document.body.classList.add('dm-portal-order-complete-page-1283');
    }

    if (cfg.mode === 'domains') {
        document.body.classList.add('dm-portal-domains-page-1283');
        domainsWorkspace = directChildByClass(primary, 'tab-content') || primary.querySelector('.tab-content');
        if (domainsWorkspace) {
            domainsWorkspace.classList.add('dm-portal-domains-workspace-1283');
        }
    }

    layoutRow = sidebarLayoutRow(primary);
    if (layoutRow) {
        document.body.classList.add('dm-portal-wide-header-1292');
        wideHeaderWrap = document.createElement('div');
        wideHeaderWrap.className = 'col-12 dm-portal-page-header-wrap-1292';
        wideHeaderWrap.appendChild(header);
        layoutRow.insertBefore(wideHeaderWrap, layoutRow.firstChild);
    } else if (cfg.mode === 'domains' && domainsWorkspace) {
        primary.insertBefore(header, domainsWorkspace);
    } else {
        insertionTarget = primary.firstChild;
        primary.insertBefore(header, insertionTarget);
    }

    hideMatchingNativeTitle(primary);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', buildHeader, { once: true });
} else {
    buildHeader();
}
JS
        . '})();</script>';
});
