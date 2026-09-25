<?php
/**
 * DomainMonger My Domains active-status management affordance.
 *
 * Patch 1191
 * - Forces the Active status link to the converted ResellerClub Overview page before the native row click can fire.
 * - Sends Manage Domains to the current/last managed domain Overview page instead of the My Domains list.
 * - Adds arrow, hover/focus state, keyboard access, and DomainMonger-styled tooltip.
 * - Page-scoped to clientareadomains / My Domains only.
 * - Does not touch language files, templates, register/order-form pages, or integration files.
 */

use WHMCS\View\Menu\Item as MenuItem;
use WHMCS\Database\Capsule;

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

/**
 * Resolve a useful domain Overview destination for the main menu.
 *
 * Preference order:
 * 1. Domain in the current request.
 * 2. Last domain managed during this client session.
 * 3. First active domain owned by the client.
 */
function domainmonger1191OverviewDomainId(): int
{
    $clientId = 0;
    if (!empty($_SESSION['uid'])) {
        $clientId = (int) $_SESSION['uid'];
    } elseif (!empty($_SESSION['clientareauserid'])) {
        $clientId = (int) $_SESSION['clientareauserid'];
    }

    if ($clientId <= 0) {
        return 0;
    }

    $requestedId = 0;
    if (!empty($_REQUEST['domainid'])) {
        $requestedId = (int) $_REQUEST['domainid'];
    } elseif (!empty($_REQUEST['id'])) {
        $requestedId = (int) $_REQUEST['id'];
    }

    try {
        if ($requestedId > 0) {
            $owned = Capsule::table('tbldomains')
                ->where('userid', $clientId)
                ->where('id', $requestedId)
                ->value('id');

            if ($owned) {
                $_SESSION['domainmonger_last_managed_domain_id'] = (int) $owned;
                return (int) $owned;
            }
        }

        $sessionId = (int) ($_SESSION['domainmonger_last_managed_domain_id'] ?? 0);
        if ($sessionId > 0) {
            $owned = Capsule::table('tbldomains')
                ->where('userid', $clientId)
                ->where('id', $sessionId)
                ->value('id');

            if ($owned) {
                return (int) $owned;
            }
        }

        $fallback = Capsule::table('tbldomains')
            ->where('userid', $clientId)
            ->where('status', 'Active')
            ->orderBy('domain', 'asc')
            ->value('id');

        if (!$fallback) {
            $fallback = Capsule::table('tbldomains')
                ->where('userid', $clientId)
                ->orderBy('domain', 'asc')
                ->value('id');
        }

        if ($fallback) {
            $_SESSION['domainmonger_last_managed_domain_id'] = (int) $fallback;
            return (int) $fallback;
        }
    } catch (\Throwable $e) {
        return 0;
    }

    return 0;
}

function domainmonger1191OverviewUrl(int $domainId): string
{
    if ($domainId <= 0) {
        return 'clientarea.php?action=domains';
    }

    return 'clientarea.php?action=domaindetails&id=' . $domainId
        . '&dmsection=overview&dmnav=overview&dmdesign=1&dmconverted=1';
}

/**
 * Add a dedicated Manage Domains entry directly after My Domains.
 */
add_hook('ClientAreaPrimaryNavbar', 45, function (MenuItem $primaryNavbar) {
    $domainsMenu = method_exists($primaryNavbar, 'getChild')
        ? $primaryNavbar->getChild('Domains')
        : null;

    if (!$domainsMenu && method_exists($primaryNavbar, 'getChildren')) {
        foreach ($primaryNavbar->getChildren() as $topItem) {
            if (!$topItem instanceof MenuItem) {
                continue;
            }

            $name = method_exists($topItem, 'getName') ? (string) $topItem->getName() : '';
            $label = method_exists($topItem, 'getLabel') ? (string) $topItem->getLabel() : '';
            $clean = strtolower(trim(strip_tags(html_entity_decode($name !== '' ? $name : $label, ENT_QUOTES | ENT_HTML5, 'UTF-8'))));

            if ($clean === 'domains') {
                $domainsMenu = $topItem;
                break;
            }
        }
    }

    if (!$domainsMenu instanceof MenuItem || !method_exists($domainsMenu, 'addChild')) {
        return;
    }

    if (method_exists($domainsMenu, 'getChild') && $domainsMenu->getChild('DomainMonger Manage Domains')) {
        return;
    }

    $myDomainsOrder = 10;
    if (method_exists($domainsMenu, 'getChildren')) {
        foreach ($domainsMenu->getChildren() as $child) {
            if (!$child instanceof MenuItem) {
                continue;
            }

            $name = method_exists($child, 'getName') ? (string) $child->getName() : '';
            $label = method_exists($child, 'getLabel') ? (string) $child->getLabel() : '';
            $uri = method_exists($child, 'getUri') ? strtolower((string) $child->getUri()) : '';
            $clean = strtolower(trim(strip_tags(html_entity_decode($name !== '' ? $name : $label, ENT_QUOTES | ENT_HTML5, 'UTF-8'))));

            $isMyDomains = $clean === 'my domains'
                || (strpos($uri, 'clientarea.php') !== false && strpos($uri, 'action=domains') !== false);

            if (!$isMyDomains) {
                continue;
            }

            if (method_exists($child, 'getOrder')) {
                $myDomainsOrder = (int) $child->getOrder();
            }
            break;
        }
    }

    $overviewDomainId = domainmonger1191OverviewDomainId();

    $domainsMenu->addChild('DomainMonger Manage Domains', [
        'label' => 'Manage Domains',
        'uri' => domainmonger1191OverviewUrl($overviewDomainId),
        'order' => $myDomainsOrder + 1,
    ]);
});

add_hook('ClientAreaFooterOutput', 1006, function ($vars) {
    $templateFile = isset($vars['templatefile']) ? (string) $vars['templatefile'] : '';
    $requestUri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';

    $isDomainsList = ($templateFile === 'clientareadomains')
        || (stripos($requestUri, 'clientarea.php') !== false && preg_match('/(?:\?|&)action=domains(?:&|$)/i', $requestUri));

    if (!$isDomainsList) {
        return '';
    }

    return <<<'HTML'
<style id="domainmonger-domains-manage-status-links">
body #main-body table#tableDomainsList .dm-domain-manage-pill {
    cursor: pointer !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 5px !important;
    min-height: 24px !important;
    padding: 5px 10px !important;
    border: 1px solid rgba(255, 255, 255, 0.32) !important;
    box-shadow: 0 0 0 0 rgba(245, 130, 32, 0) !important;
    transition: filter 0.15s ease, box-shadow 0.15s ease, transform 0.15s ease !important;
}

body #main-body table#tableDomainsList tbody tr:hover .dm-domain-manage-pill,
body #main-body table#tableDomainsList .dm-domain-manage-pill:hover,
body #main-body table#tableDomainsList .dm-domain-manage-pill:focus {
    filter: brightness(1.07) !important;
    box-shadow: 0 0 0 3px rgba(245, 130, 32, 0.24) !important;
    transform: translateY(-1px) !important;
    text-decoration: none !important;
}

body #main-body table#tableDomainsList .dm-domain-manage-pill:focus {
    outline: 2px solid rgba(245, 130, 32, 0.45) !important;
    outline-offset: 2px !important;
}

body #main-body table#tableDomainsList .dm-domain-manage-icon {
    font-size: 0.82em !important;
    line-height: 1 !important;
    opacity: 0.95 !important;
}

body .dm-domain-manage-tooltip {
    position: fixed !important;
    z-index: 999999 !important;
    max-width: 220px !important;
    padding: 7px 10px 7px 11px !important;
    border-left: 3px solid #f58220 !important;
    border-radius: 6px !important;
    background: #163a5f !important;
    color: #ffffff !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    line-height: 1.25 !important;
    text-align: left !important;
    white-space: nowrap !important;
    box-shadow: 0 8px 18px rgba(22, 58, 95, 0.24) !important;
    pointer-events: none !important;
    opacity: 0 !important;
    visibility: hidden !important;
    transform: translateY(4px) !important;
    transition: opacity 0.12s ease, transform 0.12s ease, visibility 0.12s ease !important;
}

body .dm-domain-manage-tooltip.is-visible {
    opacity: 1 !important;
    visibility: visible !important;
    transform: translateY(0) !important;
}

body .dm-domain-manage-tooltip::after {
    content: "" !important;
    position: absolute !important;
    left: 50% !important;
    bottom: -6px !important;
    width: 0 !important;
    height: 0 !important;
    margin-left: -6px !important;
    border-left: 6px solid transparent !important;
    border-right: 6px solid transparent !important;
    border-top: 6px solid #163a5f !important;
}

body .dm-domain-manage-tooltip.is-below::after {
    top: -6px !important;
    bottom: auto !important;
    border-top: 0 !important;
    border-bottom: 6px solid #163a5f !important;
}
</style>
<script id="domainmonger-domains-manage-status-links-js">
(function ($) {
    'use strict';

    var $dmTooltip = null;

    function dmDomainManageUrl($row) {
        var domainId = $.trim(String($row.find('input.domids').first().val() || ''));

        if (!domainId) {
            domainId = $.trim(String($row.find('td[data-type="domain"]').attr('data-element-id') || ''));
        }

        if (!domainId) {
            return '';
        }

        return 'clientarea.php?action=domaindetails&id=' + encodeURIComponent(domainId)
            + '&dmsection=overview&dmnav=overview&dmdesign=1&dmconverted=1';
    }

    function dmRedirect(event, url) {
        if (!url) {
            return;
        }

        if (event) {
            event.preventDefault();
            event.stopPropagation();
            if (typeof event.stopImmediatePropagation === 'function') {
                event.stopImmediatePropagation();
            }
        }

        window.location.assign(url);
    }

    function dmEnsureTooltip() {
        if ($dmTooltip && $dmTooltip.length) {
            return $dmTooltip;
        }

        $dmTooltip = $('<div class="dm-domain-manage-tooltip" role="tooltip" aria-hidden="true"></div>').appendTo('body');
        return $dmTooltip;
    }

    function dmHideTooltip() {
        if (!$dmTooltip || !$dmTooltip.length) {
            return;
        }

        $dmTooltip.removeClass('is-visible is-below').attr('aria-hidden', 'true');
    }

    function dmShowTooltip(element) {
        var $target = $(element);
        var text = $.trim(String($target.attr('data-dm-tooltip') || ''));

        if (!text) {
            dmHideTooltip();
            return;
        }

        var tooltip = dmEnsureTooltip();
        tooltip.text(text).removeClass('is-below').attr('aria-hidden', 'false').addClass('is-visible');

        var rect = element.getBoundingClientRect();
        var tooltipWidth = tooltip.outerWidth();
        var tooltipHeight = tooltip.outerHeight();
        var viewportWidth = $(window).width();
        var top = rect.top - tooltipHeight - 9;
        var left = rect.left + (rect.width / 2) - (tooltipWidth / 2);

        if (top < 8) {
            top = rect.bottom + 9;
            tooltip.addClass('is-below');
        }

        left = Math.max(8, Math.min(left, viewportWidth - tooltipWidth - 8));

        tooltip.css({
            top: Math.round(top) + 'px',
            left: Math.round(left) + 'px'
        });
    }

    function dmEnhanceDomainsList() {
        var $table = $('#tableDomainsList');
        if (!$table.length) {
            return;
        }

        $table.find('tbody tr').each(function () {
            var $row = $(this);
            var url = dmDomainManageUrl($row);
            var $status = $row.find('td:last-child > span.label.status, td:last-child > span.status').first();
            var statusText = $.trim(String($status.clone().children().remove().end().text() || $status.text() || ''));
            var isActive = $status.hasClass('status-active') || /^active$/i.test(statusText);

            if (!url || !$status.length || !isActive) {
                return;
            }

            try {
                if ($.fn.tooltip) {
                    $status.tooltip('dispose');
                    $status.tooltip('destroy');
                }
            } catch (ignore) {}

            $status
                .addClass('dm-domain-manage-pill')
                .attr('role', 'link')
                .attr('tabindex', '0')
                .attr('data-dm-domain-url', url)
                .attr('data-dm-tooltip', 'Manage this domain')
                .attr('aria-label', 'Manage this domain')
                .removeAttr('title')
                .removeAttr('data-original-title')
                .removeAttr('aria-describedby');

            if (!$status.find('.dm-domain-manage-icon').length) {
                $status.append(' <i class="fas fa-arrow-right dm-domain-manage-icon" aria-hidden="true"></i>');
            }
        });
    }

    /*
     * The native My Domains template puts an onclick handler on the whole row.
     * Capture the Active-pill click before that row handler can redirect to a
     * different tab, then send the browser to the exact Overview route.
     */
    if (!window.domainmonger1191StatusCaptureBound) {
        window.domainmonger1191StatusCaptureBound = true;
        document.addEventListener('click', function (event) {
            var target = event.target && event.target.closest
                ? event.target.closest('#tableDomainsList .dm-domain-manage-pill')
                : null;

            if (!target) {
                return;
            }

            dmRedirect(event, target.getAttribute('data-dm-domain-url'));
        }, true);
    }

    $(document)
        .off('click.domainmongerManageDomainStatus')
        .on('click.domainmongerManageDomainStatus', '#tableDomainsList .dm-domain-manage-pill', function (event) {
            dmRedirect(event, $(this).attr('data-dm-domain-url'));
        })
        .off('keydown.domainmongerManageDomainStatus')
        .on('keydown.domainmongerManageDomainStatus', '#tableDomainsList .dm-domain-manage-pill', function (event) {
            if (event.key !== 'Enter' && event.key !== ' ') {
                return;
            }

            dmRedirect(event, $(this).attr('data-dm-domain-url'));
        })
        .off('mouseenter.domainmongerManageDomainStatus focusin.domainmongerManageDomainStatus')
        .on('mouseenter.domainmongerManageDomainStatus focusin.domainmongerManageDomainStatus', '#tableDomainsList .dm-domain-manage-pill', function () {
            dmShowTooltip(this);
        })
        .off('mouseleave.domainmongerManageDomainStatus focusout.domainmongerManageDomainStatus')
        .on('mouseleave.domainmongerManageDomainStatus focusout.domainmongerManageDomainStatus', '#tableDomainsList .dm-domain-manage-pill', function () {
            dmHideTooltip();
        });

    $(document).ready(function () {
        dmEnhanceDomainsList();
        window.setTimeout(dmEnhanceDomainsList, 250);

        $('#tableDomainsList').on('draw.dt responsive-display.dt', function () {
            dmEnhanceDomainsList();
        });
    });

    $(window).on('scroll.domainmongerManageDomainStatus resize.domainmongerManageDomainStatus', dmHideTooltip);
}(jQuery));
</script>
HTML;
});
