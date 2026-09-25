<?php
/**
 * DomainMonger selected-page initial overlay guard — Patch 1571.
 *
 * Scope:
 * - The main WHMCS client-area Dashboard: /manage/clientarea.php
 * - The Hosting Store landing page: /manage/index.php?rp=/store/hosting
 * - Add Funds: /manage/clientarea.php?action=addfunds
 * - Payment Methods: /manage/index.php?rp=/account/paymentmethods
 * - Support Tickets: /manage/supporttickets.php
 * - Knowledgebase: /manage/index.php?rp=/knowledgebase
 * - Server Status: /manage/serverstatus.php
 * - Ticket submission, department 2: /manage/submitticket.php?step=2&deptid=2
 * - Account Users: /manage/index.php?rp=/account/users
 * - Account Contacts: /manage/index.php?rp=/account/contacts
 * - User Profile: /manage/index.php?rp=/user/profile
 * - User Security: /manage/index.php?rp=/user/security
 * - No other store, cart, client-area action, or domain-management page.
 *
 * Purpose:
 * - Prevent WHMCS's normally hidden black #fullpage-overlay from painting for
 *   a moment during the selected pages' initial render.
 * - Release the temporary guard after page load so later intentional overlay
 *   behavior remains available.
 *
 * Presentation only. No template, integration, session, form, database, or
 * business-logic changes.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaHeadOutput', 1, static function (array $vars): string {
    $scriptName = strtolower(basename((string) ($_SERVER['SCRIPT_NAME'] ?? '')));
    $action = trim(strtolower((string) ($_GET['action'] ?? '')));
    $routePath = trim((string) ($_GET['rp'] ?? ''));
    $templateFile = strtolower((string) ($vars['templatefile'] ?? ''));
    $normalisedRoutePath = '/' . ltrim(rawurldecode($routePath), '/');
    $normalisedRoutePath = rtrim($normalisedRoutePath, '/');

    $isDashboard = $scriptName === 'clientarea.php'
        && $action === ''
        && $routePath === ''
        && ($templateFile === '' || $templateFile === 'clientareahome');

    $isHostingStore = $scriptName === 'index.php'
        && $normalisedRoutePath === '/store/hosting';

    $isAddFunds = $scriptName === 'clientarea.php'
        && $action === 'addfunds';

    $isPaymentMethods = $scriptName === 'index.php'
        && $normalisedRoutePath === '/account/paymentmethods';

    $isSupportTickets = $scriptName === 'supporttickets.php';

    $isKnowledgebase = $scriptName === 'index.php'
        && $normalisedRoutePath === '/knowledgebase';

    $isServerStatus = $scriptName === 'serverstatus.php';

    $isAccountUsers = $scriptName === 'index.php'
        && $normalisedRoutePath === '/account/users';

    $isAccountContacts = $scriptName === 'index.php'
        && $normalisedRoutePath === '/account/contacts';

    $isUserProfile = $scriptName === 'index.php'
        && $normalisedRoutePath === '/user/profile';

    $isUserSecurity = $scriptName === 'index.php'
        && $normalisedRoutePath === '/user/security';

    $step = trim((string) ($_GET['step'] ?? ''));
    $departmentId = trim((string) ($_GET['deptid'] ?? ''));
    $isTicketSubmissionDepartmentTwo = $scriptName === 'submitticket.php'
        && $step === '2'
        && $departmentId === '2';

    if (
        !$isDashboard
        && !$isHostingStore
        && !$isAddFunds
        && !$isPaymentMethods
        && !$isSupportTickets
        && !$isKnowledgebase
        && !$isServerStatus
        && !$isAccountUsers
        && !$isAccountContacts
        && !$isUserProfile
        && !$isUserSecurity
        && !$isTicketSubmissionDepartmentTwo
    ) {
        return '';
    }

    return <<<'HTML'
<style id="domainmonger-selected-pages-overlay-guard-1571">
html.dm-selected-pages-initial-guard-1571 #fullpage-overlay,
body.dm-selected-pages-initial-guard-1571 #fullpage-overlay {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
    pointer-events: none !important;
    background: transparent !important;
}
html.dm-selected-pages-initial-guard-1571 #fullpage-overlay img,
body.dm-selected-pages-initial-guard-1571 #fullpage-overlay img,
html.dm-selected-pages-initial-guard-1571 #fullpage-overlay .overlay-spinner,
body.dm-selected-pages-initial-guard-1571 #fullpage-overlay .overlay-spinner {
    display: none !important;
}
</style>
<script id="domainmonger-selected-pages-overlay-guard-script-1571">
(function () {
    'use strict';

    var guardClass = 'dm-selected-pages-initial-guard-1571';
    var root = document.documentElement;
    var released = false;

    root.classList.add(guardClass);

    function addBodyGuard() {
        if (document.body) {
            document.body.classList.add(guardClass);
        }
    }

    function keepInitialOverlayHidden() {
        var overlay = document.getElementById('fullpage-overlay');
        if (!overlay) {
            return;
        }
        overlay.classList.add('w-hidden');
        overlay.setAttribute('aria-hidden', 'true');
    }

    function releaseGuard() {
        if (released) {
            return;
        }
        released = true;
        keepInitialOverlayHidden();

        window.requestAnimationFrame(function () {
            window.requestAnimationFrame(function () {
                root.classList.remove(guardClass);
                if (document.body) {
                    document.body.classList.remove(guardClass);
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            addBodyGuard();
            keepInitialOverlayHidden();
        }, true);
    } else {
        addBodyGuard();
        keepInitialOverlayHidden();
    }

    window.addEventListener('load', releaseGuard, true);
    window.setTimeout(releaseGuard, 2500);
})();
</script>
HTML;
});
