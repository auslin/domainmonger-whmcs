<?php
/**
 * DomainMonger WHMCS client-area tooltip styling.
 *
 * Patch 502
 * - WHMCS-only tooltip skin for /manage/ client-area pages.
 * - Styles Bootstrap 3/4/5 tooltip markup and DomainMonger custom tooltips.
 * - Does not convert every native browser title tooltip; existing site behavior remains intact.
 * - Does not touch WordPress, language overrides, register/order-form pages, or integration files.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 1010, function ($vars) {
    return <<<'HTML'
<style id="domainmonger-whmcs-sitewide-tooltip-style">
/* DomainMonger WHMCS tooltip skin: navy, white text, orange accent. */
body .tooltip {
    opacity: 1 !important;
    z-index: 999999 !important;
}

body .tooltip.show,
body .tooltip.in {
    opacity: 1 !important;
}

body .tooltip .tooltip-inner,
body .tooltip-inner,
body .dm-whmcs-tooltip,
body .dm-service-manage-tooltip {
    max-width: 260px !important;
    padding: 7px 10px 7px 11px !important;
    border-left: 3px solid #f58220 !important;
    border-radius: 6px !important;
    background: #163a5f !important;
    color: #ffffff !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    line-height: 1.25 !important;
    text-align: left !important;
    text-decoration: none !important;
    white-space: normal !important;
    box-shadow: 0 8px 18px rgba(22, 58, 95, 0.24) !important;
}

body .dm-service-manage-tooltip {
    white-space: nowrap !important;
}

/* Bootstrap 4/5 tooltip arrows */
body .bs-tooltip-auto[data-popper-placement^="top"] .tooltip-arrow::before,
body .bs-tooltip-top .tooltip-arrow::before,
body .bs-tooltip-auto[x-placement^="top"] .arrow::before,
body .bs-tooltip-top .arrow::before {
    border-top-color: #163a5f !important;
}

body .bs-tooltip-auto[data-popper-placement^="bottom"] .tooltip-arrow::before,
body .bs-tooltip-bottom .tooltip-arrow::before,
body .bs-tooltip-auto[x-placement^="bottom"] .arrow::before,
body .bs-tooltip-bottom .arrow::before {
    border-bottom-color: #163a5f !important;
}

body .bs-tooltip-auto[data-popper-placement^="left"] .tooltip-arrow::before,
body .bs-tooltip-left .tooltip-arrow::before,
body .bs-tooltip-auto[x-placement^="left"] .arrow::before,
body .bs-tooltip-left .arrow::before {
    border-left-color: #163a5f !important;
}

body .bs-tooltip-auto[data-popper-placement^="right"] .tooltip-arrow::before,
body .bs-tooltip-right .tooltip-arrow::before,
body .bs-tooltip-auto[x-placement^="right"] .arrow::before,
body .bs-tooltip-right .arrow::before {
    border-right-color: #163a5f !important;
}

/* Bootstrap 3 tooltip arrows */
body .tooltip.top .tooltip-arrow,
body .tooltip.bs-tether-element-attached-bottom .tooltip-arrow {
    border-top-color: #163a5f !important;
}

body .tooltip.bottom .tooltip-arrow,
body .tooltip.bs-tether-element-attached-top .tooltip-arrow {
    border-bottom-color: #163a5f !important;
}

body .tooltip.left .tooltip-arrow,
body .tooltip.bs-tether-element-attached-right .tooltip-arrow {
    border-left-color: #163a5f !important;
}

body .tooltip.right .tooltip-arrow,
body .tooltip.bs-tether-element-attached-left .tooltip-arrow {
    border-right-color: #163a5f !important;
}

body .tooltip a,
body .tooltip .tooltip-inner a,
body .dm-whmcs-tooltip a,
body .dm-service-manage-tooltip a {
    color: #ffffff !important;
    text-decoration: underline !important;
}
</style>
<script id="domainmonger-whmcs-sitewide-tooltip-init">
(function ($) {
    'use strict';

    function dmInitWhmcsTooltips() {
        if (!$ || !$.fn || !$.fn.tooltip) {
            return;
        }

        $('[data-toggle="tooltip"], [data-bs-toggle="tooltip"]').each(function () {
            var $el = $(this);

            try {
                if (!$el.data('dmTooltipStyled')) {
                    $el.tooltip({
                        container: 'body',
                        trigger: $el.attr('data-trigger') || $el.attr('data-bs-trigger') || 'hover focus'
                    });
                    $el.data('dmTooltipStyled', true);
                }
            } catch (ignore) {}
        });
    }

    $(document).ready(function () {
        dmInitWhmcsTooltips();
        window.setTimeout(dmInitWhmcsTooltips, 300);
    });
}(window.jQuery));
</script>
HTML;
});
