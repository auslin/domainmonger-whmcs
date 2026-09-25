<?php
/**
 * DomainMonger My Services management-status polish.
 *
 * Patch 501
 * - Keeps clickable/manage affordance from Patch 500.
 * - Sends DNSLite 10 service management clicks directly to the ClouDNS DNS Records action.
 * - Replaces native/browser/Bootstrap black tooltips with a DomainMonger-styled tooltip.
 * - Does not touch language files, register/order-form pages, or integration files.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 1005, function ($vars) {
    $templateFile = isset($vars['templatefile']) ? (string)$vars['templatefile'] : '';
    $requestUri = isset($_SERVER['REQUEST_URI']) ? (string)$_SERVER['REQUEST_URI'] : '';

    $isServicesList = ($templateFile === 'clientareaproducts')
        || (stripos($requestUri, 'clientarea.php') !== false && preg_match('/(?:\?|&)action=services(?:&|$)/i', $requestUri));

    if (!$isServicesList) {
        return '';
    }

    return <<<'HTML'
<style id="domainmonger-services-manage-status-links">
body #main-body table#tableServicesList tbody tr {
    cursor: pointer;
}

body #main-body table#tableServicesList .dm-service-manage-pill {
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

body #main-body table#tableServicesList tbody tr:hover .dm-service-manage-pill,
body #main-body table#tableServicesList .dm-service-manage-pill:hover,
body #main-body table#tableServicesList .dm-service-manage-pill:focus {
    filter: brightness(1.07) !important;
    box-shadow: 0 0 0 3px rgba(245, 130, 32, 0.24) !important;
    transform: translateY(-1px) !important;
    text-decoration: none !important;
}

body #main-body table#tableServicesList .dm-service-manage-pill:focus {
    outline: 2px solid rgba(245, 130, 32, 0.45) !important;
    outline-offset: 2px !important;
}

body #main-body table#tableServicesList .dm-service-manage-icon {
    font-size: 0.82em !important;
    line-height: 1 !important;
    opacity: 0.95 !important;
}

body .dm-service-manage-tooltip {
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

body .dm-service-manage-tooltip.is-visible {
    opacity: 1 !important;
    visibility: visible !important;
    transform: translateY(0) !important;
}

body .dm-service-manage-tooltip::after {
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

body .dm-service-manage-tooltip.is-below::after {
    top: -6px !important;
    bottom: auto !important;
    border-top: 0 !important;
    border-bottom: 6px solid #163a5f !important;
}
</style>
<script id="domainmonger-services-manage-status-links-js">
(function ($) {
    'use strict';

    var $dmTooltip = null;

    function dmServiceManageUrl($row) {
        var serviceId = $.trim(String($row.find('td[data-type="service"]').attr('data-element-id') || ''));
        var productName = $.trim(String($row.find('td:nth-child(2) strong').first().text() || ''));

        if (!serviceId) {
            return '';
        }

        var url = 'clientarea.php?action=productdetails&id=' + encodeURIComponent(serviceId);

        if (/DNSLite\s*10/i.test(productName)) {
            url += '&customAction=zone-settings';
        }

        return url;
    }

    function dmEnsureTooltip() {
        if ($dmTooltip && $dmTooltip.length) {
            return $dmTooltip;
        }

        $dmTooltip = $('<div class="dm-service-manage-tooltip" role="tooltip" aria-hidden="true"></div>').appendTo('body');
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

    function dmEnhanceServicesList() {
        var $table = $('#tableServicesList');
        if (!$table.length) {
            return;
        }

        $table.find('tbody tr').each(function () {
            var $row = $(this);
            var url = dmServiceManageUrl($row);
            var $status = $row.find('span.status').first();
            var productName = $.trim(String($row.find('td:nth-child(2) strong').first().text() || ''));
            var title = (/DNSLite\s*10/i.test(productName)) ? 'Manage DNS records' : 'Manage this service';

            if (!url || !$status.length) {
                return;
            }

            $row.attr('data-dm-manage-url', url);
            $row.attr('onclick', "clickableSafeRedirect(event, this.getAttribute('data-dm-manage-url'), false)");

            try {
                if ($.fn.tooltip) {
                    $status.tooltip('dispose');
                    $status.tooltip('destroy');
                }
            } catch (ignore) {}

            $status
                .addClass('dm-service-manage-pill')
                .attr('role', 'link')
                .attr('tabindex', '0')
                .attr('data-dm-tooltip', title)
                .attr('aria-label', title)
                .removeAttr('title')
                .removeAttr('data-original-title')
                .removeAttr('aria-describedby');

            if (!$status.find('.dm-service-manage-icon').length) {
                $status.append(' <i class="fas fa-arrow-right dm-service-manage-icon" aria-hidden="true"></i>');
            }
        });

        $table
            .off('keydown.domainmongerManageStatus')
            .on('keydown.domainmongerManageStatus', '.dm-service-manage-pill', function (event) {
                if (event.key !== 'Enter' && event.key !== ' ') {
                    return;
                }

                var $row = $(this).closest('tr');
                var url = $row.attr('data-dm-manage-url');

                if (!url) {
                    return;
                }

                event.preventDefault();
                clickableSafeRedirect(event, url, false);
            })
            .off('mouseenter.domainmongerManageStatus focusin.domainmongerManageStatus')
            .on('mouseenter.domainmongerManageStatus focusin.domainmongerManageStatus', '.dm-service-manage-pill', function () {
                dmShowTooltip(this);
            })
            .off('mouseleave.domainmongerManageStatus focusout.domainmongerManageStatus')
            .on('mouseleave.domainmongerManageStatus focusout.domainmongerManageStatus', '.dm-service-manage-pill', function () {
                dmHideTooltip();
            });
    }

    $(document).ready(function () {
        dmEnhanceServicesList();
        window.setTimeout(dmEnhanceServicesList, 250);
    });

    $(window).on('scroll.domainmongerManageStatus resize.domainmongerManageStatus', dmHideTooltip);
}(jQuery));
</script>
HTML;
});
