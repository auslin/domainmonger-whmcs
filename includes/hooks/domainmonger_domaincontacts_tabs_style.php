<?php
/**
 * DomainMonger standard WHOIS contact tabs styling.
 *
 * Patch 792:
 * Replace the current tabs with a connected tab-bar style inspired by the
 * provided mockup, using DomainMonger colors.
 *
 * Scope:
 * manage/clientarea.php?action=domaincontacts&domainid=...
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('dm_domaincontacts_tabs_is_page')) {
    function dm_domaincontacts_tabs_is_page(): bool
    {
        $script = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        $uri = strtolower((string) ($_SERVER['REQUEST_URI'] ?? ''));
        $action = strtolower((string) ($_REQUEST['action'] ?? ''));

        return ($script === 'clientarea.php' || strpos($uri, '/manage/clientarea.php') !== false)
            && ($action === 'domaincontacts' || strpos($uri, 'action=domaincontacts') !== false);
    }
}

add_hook('ClientAreaHeadOutput', 1, function () {
    if (!dm_domaincontacts_tabs_is_page()) {
        return '';
    }

    return <<<'HTML'
<style id="dm-domaincontacts-tabs-style-v792">
/*
 * Connected tab bar like supplied mockup.
 * Active: DomainMonger orange with white text.
 * Inactive: pale orange/white with navy text and orange outline.
 */
#frmDomainContactModification ul.nav-tabs,
#frmDomainContactModification .nav.nav-tabs,
form[action*="domaincontacts"] ul.nav-tabs,
form[action*="domaincontacts"] .nav.nav-tabs,
.dm-domain-contact-tabs {
    background: transparent !important;
    border: 0 !important;
    box-shadow: none !important;
    display: flex !important;
    flex-wrap: nowrap !important;
    gap: 0 !important;
    list-style: none !important;
    margin: 0 0 18px 0 !important;
    padding: 0 !important;
    width: 100% !important;
}

/* Remove Bootstrap artifacts/extra line */
#frmDomainContactModification ul.nav-tabs:before,
#frmDomainContactModification ul.nav-tabs:after,
#frmDomainContactModification .nav.nav-tabs:before,
#frmDomainContactModification .nav.nav-tabs:after,
form[action*="domaincontacts"] ul.nav-tabs:before,
form[action*="domaincontacts"] ul.nav-tabs:after,
form[action*="domaincontacts"] .nav.nav-tabs:before,
form[action*="domaincontacts"] .nav.nav-tabs:after,
.dm-domain-contact-tabs:before,
.dm-domain-contact-tabs:after {
    content: none !important;
    display: none !important;
    border: 0 !important;
    box-shadow: none !important;
}

#frmDomainContactModification ul.nav-tabs > li,
#frmDomainContactModification .nav.nav-tabs > li,
#frmDomainContactModification .nav.nav-tabs > .nav-item,
form[action*="domaincontacts"] ul.nav-tabs > li,
form[action*="domaincontacts"] .nav.nav-tabs > li,
form[action*="domaincontacts"] .nav.nav-tabs > .nav-item,
.dm-domain-contact-tabs > li,
.dm-domain-contact-tabs > .nav-item {
    flex: 1 1 0 !important;
    float: none !important;
    margin: 0 !important;
    padding: 0 !important;
    min-width: 0 !important;
}

#frmDomainContactModification ul.nav-tabs > li > a,
#frmDomainContactModification .nav.nav-tabs > li > a,
#frmDomainContactModification .nav.nav-tabs .nav-link,
form[action*="domaincontacts"] ul.nav-tabs > li > a,
form[action*="domaincontacts"] .nav.nav-tabs > li > a,
form[action*="domaincontacts"] .nav.nav-tabs .nav-link,
.dm-domain-contact-tabs > li > a,
.dm-domain-contact-tabs .nav-link {
    align-items: center !important;
    background: #fff8f3 !important;
    border: 1px solid #f58220 !important;
    border-left-width: 0 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    color: #163a5f !important;
    -webkit-text-fill-color: #163a5f !important;
    display: flex !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    height: auto !important;
    justify-content: center !important;
    line-height: 1.2 !important;
    margin: 0 !important;
    min-height: 44px !important;
    min-width: 0 !important;
    opacity: 1 !important;
    overflow: visible !important;
    padding: 12px 18px !important;
    position: relative !important;
    text-align: center !important;
    text-decoration: none !important;
    text-indent: 0 !important;
    text-shadow: none !important;
    visibility: visible !important;
    white-space: nowrap !important;
}

#frmDomainContactModification ul.nav-tabs > li:first-child > a,
#frmDomainContactModification .nav.nav-tabs > li:first-child > a,
#frmDomainContactModification .nav.nav-tabs > .nav-item:first-child > .nav-link,
form[action*="domaincontacts"] ul.nav-tabs > li:first-child > a,
form[action*="domaincontacts"] .nav.nav-tabs > li:first-child > a,
form[action*="domaincontacts"] .nav.nav-tabs > .nav-item:first-child > .nav-link,
.dm-domain-contact-tabs > li:first-child > a,
.dm-domain-contact-tabs > .nav-item:first-child > .nav-link {
    border-left-width: 1px !important;
    border-radius: 8px 0 0 8px !important;
}

#frmDomainContactModification ul.nav-tabs > li:last-child > a,
#frmDomainContactModification .nav.nav-tabs > li:last-child > a,
#frmDomainContactModification .nav.nav-tabs > .nav-item:last-child > .nav-link,
form[action*="domaincontacts"] ul.nav-tabs > li:last-child > a,
form[action*="domaincontacts"] .nav.nav-tabs > li:last-child > a,
form[action*="domaincontacts"] .nav.nav-tabs > .nav-item:last-child > .nav-link,
.dm-domain-contact-tabs > li:last-child > a,
.dm-domain-contact-tabs > .nav-item:last-child > .nav-link {
    border-radius: 0 8px 8px 0 !important;
}

#frmDomainContactModification ul.nav-tabs > li > a:hover,
#frmDomainContactModification ul.nav-tabs > li > a:focus,
#frmDomainContactModification .nav.nav-tabs > li > a:hover,
#frmDomainContactModification .nav.nav-tabs > li > a:focus,
#frmDomainContactModification .nav.nav-tabs .nav-link:hover,
#frmDomainContactModification .nav.nav-tabs .nav-link:focus,
form[action*="domaincontacts"] ul.nav-tabs > li > a:hover,
form[action*="domaincontacts"] ul.nav-tabs > li > a:focus,
form[action*="domaincontacts"] .nav.nav-tabs > li > a:hover,
form[action*="domaincontacts"] .nav.nav-tabs > li > a:focus,
form[action*="domaincontacts"] .nav.nav-tabs .nav-link:hover,
form[action*="domaincontacts"] .nav.nav-tabs .nav-link:focus,
.dm-domain-contact-tabs > li > a:hover,
.dm-domain-contact-tabs > li > a:focus,
.dm-domain-contact-tabs .nav-link:hover,
.dm-domain-contact-tabs .nav-link:focus {
    background: #fff0e4 !important;
    color: #163a5f !important;
    -webkit-text-fill-color: #163a5f !important;
    outline: none !important;
    text-decoration: none !important;
}

/* Active tab */
#frmDomainContactModification ul.nav-tabs > li.active > a,
#frmDomainContactModification ul.nav-tabs > li.active > a:hover,
#frmDomainContactModification ul.nav-tabs > li.active > a:focus,
#frmDomainContactModification .nav.nav-tabs > li.active > a,
#frmDomainContactModification .nav.nav-tabs > li.active > a:hover,
#frmDomainContactModification .nav.nav-tabs > li.active > a:focus,
#frmDomainContactModification .nav.nav-tabs > li.dm-tab-active > a,
#frmDomainContactModification .nav.nav-tabs > li.dm-tab-active > a:hover,
#frmDomainContactModification .nav.nav-tabs > li.dm-tab-active > a:focus,
#frmDomainContactModification .nav.nav-tabs .nav-link.active,
#frmDomainContactModification .nav.nav-tabs .nav-item.show .nav-link,
form[action*="domaincontacts"] ul.nav-tabs > li.active > a,
form[action*="domaincontacts"] ul.nav-tabs > li.active > a:hover,
form[action*="domaincontacts"] ul.nav-tabs > li.active > a:focus,
form[action*="domaincontacts"] .nav.nav-tabs > li.active > a,
form[action*="domaincontacts"] .nav.nav-tabs > li.active > a:hover,
form[action*="domaincontacts"] .nav.nav-tabs > li.active > a:focus,
form[action*="domaincontacts"] .nav.nav-tabs > li.dm-tab-active > a,
form[action*="domaincontacts"] .nav.nav-tabs > li.dm-tab-active > a:hover,
form[action*="domaincontacts"] .nav.nav-tabs > li.dm-tab-active > a:focus,
form[action*="domaincontacts"] .nav.nav-tabs .nav-link.active,
form[action*="domaincontacts"] .nav.nav-tabs .nav-item.show .nav-link,
.dm-domain-contact-tabs > li.active > a,
.dm-domain-contact-tabs > li.active > a:hover,
.dm-domain-contact-tabs > li.active > a:focus,
.dm-domain-contact-tabs > li.dm-tab-active > a,
.dm-domain-contact-tabs > li.dm-tab-active > a:hover,
.dm-domain-contact-tabs > li.dm-tab-active > a:focus,
.dm-domain-contact-tabs .nav-link.active,
.dm-domain-contact-tabs .nav-item.show .nav-link {
    background: #f58220 !important;
    border-color: #f58220 !important;
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    opacity: 1 !important;
    text-shadow: none !important;
    z-index: 2 !important;
}

/* If active anchor wraps spans/icons, keep them white too. */
#frmDomainContactModification ul.nav-tabs > li.active > a *,
#frmDomainContactModification .nav.nav-tabs > li.active > a *,
#frmDomainContactModification .nav.nav-tabs > li.dm-tab-active > a *,
#frmDomainContactModification .nav.nav-tabs .nav-link.active *,
form[action*="domaincontacts"] ul.nav-tabs > li.active > a *,
form[action*="domaincontacts"] .nav.nav-tabs > li.active > a *,
form[action*="domaincontacts"] .nav.nav-tabs > li.dm-tab-active > a *,
form[action*="domaincontacts"] .nav.nav-tabs .nav-link.active *,
.dm-domain-contact-tabs > li.active > a *,
.dm-domain-contact-tabs > li.dm-tab-active > a *,
.dm-domain-contact-tabs .nav-link.active * {
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    opacity: 1 !important;
    visibility: visible !important;
}

#frmDomainContactModification ul.nav-tabs > li > a:before,
#frmDomainContactModification ul.nav-tabs > li > a:after,
#frmDomainContactModification .nav.nav-tabs > li > a:before,
#frmDomainContactModification .nav.nav-tabs > li > a:after,
#frmDomainContactModification .nav.nav-tabs .nav-link:before,
#frmDomainContactModification .nav.nav-tabs .nav-link:after,
form[action*="domaincontacts"] ul.nav-tabs > li > a:before,
form[action*="domaincontacts"] ul.nav-tabs > li > a:after,
form[action*="domaincontacts"] .nav.nav-tabs > li > a:before,
form[action*="domaincontacts"] .nav.nav-tabs > li > a:after,
form[action*="domaincontacts"] .nav.nav-tabs .nav-link:before,
form[action*="domaincontacts"] .nav.nav-tabs .nav-link:after,
.dm-domain-contact-tabs > li > a:before,
.dm-domain-contact-tabs > li > a:after,
.dm-domain-contact-tabs .nav-link:before,
.dm-domain-contact-tabs .nav-link:after {
    content: none !important;
    display: none !important;
}

/* Patch 1204: the converted WHOIS card needs its neutral gray top edge.
 * The previous rule removed that edge entirely, so added spacing could not
 * reveal it. Keep the old tab-strip cleanup, but restore the card border. */
#frmDomainContactModification .tab-content,
form[action*="domaincontacts"] .tab-content {
    border-top: 1px solid #d7dee7 !important;
    box-shadow: none !important;
    margin-top: 0 !important;
    padding-top: 16px !important;
}

#frmDomainContactModification .nav-tabs + hr,
#frmDomainContactModification .nav-tabs + .clearfix,
form[action*="domaincontacts"] .nav-tabs + hr,
form[action*="domaincontacts"] .nav-tabs + .clearfix,
.dm-domain-contact-tabs + hr,
.dm-domain-contact-tabs + .clearfix,
.dm-domain-contact-tabs + .nav-tabs {
    display: none !important;
}

@media (max-width: 767px) {
    #frmDomainContactModification ul.nav-tabs,
    #frmDomainContactModification .nav.nav-tabs,
    form[action*="domaincontacts"] ul.nav-tabs,
    form[action*="domaincontacts"] .nav.nav-tabs,
    .dm-domain-contact-tabs {
        flex-wrap: wrap !important;
    }

    #frmDomainContactModification ul.nav-tabs > li,
    #frmDomainContactModification .nav.nav-tabs > li,
    form[action*="domaincontacts"] ul.nav-tabs > li,
    form[action*="domaincontacts"] .nav.nav-tabs > li,
    .dm-domain-contact-tabs > li,
    .dm-domain-contact-tabs > .nav-item {
        flex: 1 1 50% !important;
    }

    #frmDomainContactModification ul.nav-tabs > li > a,
    #frmDomainContactModification .nav.nav-tabs > li > a,
    form[action*="domaincontacts"] ul.nav-tabs > li > a,
    form[action*="domaincontacts"] .nav.nav-tabs > li > a,
    .dm-domain-contact-tabs > li > a,
    .dm-domain-contact-tabs .nav-link {
        white-space: normal !important;
        width: 100% !important;
    }

    #frmDomainContactModification ul.nav-tabs > li:nth-child(odd) > a,
    #frmDomainContactModification .nav.nav-tabs > li:nth-child(odd) > a,
    form[action*="domaincontacts"] ul.nav-tabs > li:nth-child(odd) > a,
    form[action*="domaincontacts"] .nav.nav-tabs > li:nth-child(odd) > a,
    .dm-domain-contact-tabs > li:nth-child(odd) > a {
        border-left-width: 1px !important;
        border-radius: 8px 0 0 0 !important;
    }

    #frmDomainContactModification ul.nav-tabs > li:nth-child(even) > a,
    #frmDomainContactModification .nav.nav-tabs > li:nth-child(even) > a,
    form[action*="domaincontacts"] ul.nav-tabs > li:nth-child(even) > a,
    form[action*="domaincontacts"] .nav.nav-tabs > li:nth-child(even) > a,
    .dm-domain-contact-tabs > li:nth-child(even) > a {
        border-radius: 0 8px 0 0 !important;
    }
}
</style>
HTML;
});

add_hook('ClientAreaFooterOutput', 1, function () {
    if (!dm_domaincontacts_tabs_is_page()) {
        return '';
    }

    return <<<'HTML'
<script id="dm-domaincontacts-tabs-class-js-v792">
(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    function text(el) {
        return (el && el.textContent ? el.textContent : '').replace(/\s+/g, ' ').trim();
    }

    function looksLikeContactTabs(nav) {
        var t = text(nav);
        return /Registrant/i.test(t)
            && /Admin|Administrator/i.test(t)
            && /Technical/i.test(t)
            && /Billing/i.test(t);
    }

    function normalizeActive(nav) {
        var links = nav.querySelectorAll('a, .nav-link');
        var i;
        var link;
        var parent;

        for (i = 0; i < links.length; i += 1) {
            link = links[i];
            parent = link.parentElement;

            if (!parent) {
                continue;
            }

            if (parent.classList.contains('active') || link.classList.contains('active') || link.getAttribute('aria-expanded') === 'true') {
                parent.classList.add('dm-tab-active');
            } else {
                parent.classList.remove('dm-tab-active');
            }
        }
    }

    function applyTabs() {
        var navs = document.querySelectorAll('#frmDomainContactModification ul.nav-tabs, #frmDomainContactModification .nav.nav-tabs, form[action*="domaincontacts"] ul.nav-tabs, form[action*="domaincontacts"] .nav.nav-tabs, ul.responsive-tabs-sm');
        var i;
        var nav;

        for (i = 0; i < navs.length; i += 1) {
            nav = navs[i];

            if (!looksLikeContactTabs(nav)) {
                continue;
            }

            nav.classList.add('dm-domain-contact-tabs');
            normalizeActive(nav);
        }
    }

    ready(applyTabs);
    window.setTimeout(applyTabs, 150);
    window.setTimeout(applyTabs, 500);
    window.setTimeout(applyTabs, 1000);

    document.addEventListener('click', function (event) {
        var link = event.target.closest ? event.target.closest('.dm-domain-contact-tabs a, .dm-domain-contact-tabs .nav-link') : null;
        if (link) {
            window.setTimeout(applyTabs, 50);
            window.setTimeout(applyTabs, 200);
        }
    });
}());
</script>
HTML;
});
