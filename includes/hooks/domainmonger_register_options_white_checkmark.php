<?php
/**
 * DomainMonger Patch 446
 * Register page quick-extension checkbox white checkmark correction.
 *
 * Replaces/supersedes failed Patch 445 with a footer-loaded override so it wins
 * after the order form CSS and any iCheck/native checkbox rendering.
 *
 * Scope:
 * - /manage/cart.php?a=add&domain=register
 * - /manage/cart.php?a=add&domain=r
 *
 * Protected route:
 * - Does not run when dmv9support=1 is present.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('domainmonger_register_white_checkmark_is_route')) {
    function domainmonger_register_white_checkmark_is_route(array $vars): bool
    {
        $filename = isset($vars['filename']) ? strtolower((string) $vars['filename']) : '';
        $action = isset($_GET['a']) ? strtolower((string) $_GET['a']) : '';
        $domain = isset($_GET['domain']) ? strtolower((string) $_GET['domain']) : '';

        if ($filename !== 'cart' || $action !== 'add' || !in_array($domain, ['register', 'r'], true)) {
            return false;
        }

        return (($_GET['dmv9support'] ?? '') !== '1');
    }
}

add_hook('ClientAreaFooterOutput', 10000, function ($vars) {
    if (!domainmonger_register_white_checkmark_is_route($vars)) {
        return '';
    }

    return <<<'HTML'
<style id="domainmonger-register-options-white-checkmark-patch446">
/* Patch 446: selected quick-extension checkboxes use a pure white checkmark. */
#order-standard_cart .dm-v9-direct-search-card .dm-v9-direct-options input[type="checkbox"],
#order-standard_cart .dm-v9-direct-options input.dm-v9-direct-tld[type="checkbox"],
#order-standard_cart .dm-v9-direct-options #dmV9DirectSafeSearch[type="checkbox"],
#order-standard_cart .dm-v9-direct-options #dmV9DirectAvailableOnly[type="checkbox"] {
    accent-color: #d8741f !important;
    -webkit-accent-color: #d8741f !important;
}

/* Native checkbox rendering path. */
#order-standard_cart .dm-v9-direct-search-card .dm-v9-direct-options input[type="checkbox"].dm-register-patch446-native,
#order-standard_cart .dm-v9-direct-options input.dm-v9-direct-tld[type="checkbox"].dm-register-patch446-native,
#order-standard_cart .dm-v9-direct-options #dmV9DirectSafeSearch[type="checkbox"].dm-register-patch446-native,
#order-standard_cart .dm-v9-direct-options #dmV9DirectAvailableOnly[type="checkbox"].dm-register-patch446-native {
    -webkit-appearance: none !important;
    appearance: none !important;
    background: #fff !important;
    border: 1px solid #b7c0cc !important;
    border-radius: 2px !important;
    box-shadow: none !important;
    box-sizing: border-box !important;
    cursor: pointer !important;
    display: inline-block !important;
    height: 14px !important;
    margin: 0 2px 0 0 !important;
    min-height: 14px !important;
    min-width: 14px !important;
    padding: 0 !important;
    position: relative !important;
    vertical-align: middle !important;
    width: 14px !important;
}

#order-standard_cart .dm-v9-direct-search-card .dm-v9-direct-options input[type="checkbox"].dm-register-patch446-native:checked,
#order-standard_cart .dm-v9-direct-options input.dm-v9-direct-tld[type="checkbox"].dm-register-patch446-native:checked,
#order-standard_cart .dm-v9-direct-options #dmV9DirectSafeSearch[type="checkbox"].dm-register-patch446-native:checked,
#order-standard_cart .dm-v9-direct-options #dmV9DirectAvailableOnly[type="checkbox"].dm-register-patch446-native:checked {
    background-color: #d8741f !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 14 14'%3E%3Cpath d='M3.05 7.15 5.65 9.75 10.95 4.25' fill='none' stroke='%23fff' stroke-width='2.65' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E") !important;
    background-position: center center !important;
    background-repeat: no-repeat !important;
    background-size: 13px 13px !important;
    border-color: #d8741f !important;
}

/* iCheck rendering path. */
#order-standard_cart .dm-v9-direct-search-card .dm-v9-direct-options .icheckbox_square-blue,
#order-standard_cart .dm-v9-direct-options .icheckbox_square-blue.dm-register-patch446-icheck {
    background-color: #fff !important;
    background-image: none !important;
    border-color: #b7c0cc !important;
    box-shadow: none !important;
    opacity: 1 !important;
    overflow: visible !important;
    position: relative !important;
}

#order-standard_cart .dm-v9-direct-search-card .dm-v9-direct-options .icheckbox_square-blue.checked,
#order-standard_cart .dm-v9-direct-search-card .dm-v9-direct-options .icheckbox_square-blue.dm-register-patch446-checked,
#order-standard_cart .dm-v9-direct-options .icheckbox_square-blue.dm-register-patch446-icheck.checked,
#order-standard_cart .dm-v9-direct-options .icheckbox_square-blue.dm-register-patch446-icheck.dm-register-patch446-checked {
    background-color: #d8741f !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 18 18'%3E%3Cpath d='M4.1 9.15 7.4 12.45 14 5.55' fill='none' stroke='%23fff' stroke-width='3.15' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E") !important;
    background-position: center center !important;
    background-repeat: no-repeat !important;
    background-size: 16px 16px !important;
    border-color: #d8741f !important;
}

#order-standard_cart .dm-v9-direct-search-card .dm-v9-direct-options .icheckbox_square-blue.checked::before,
#order-standard_cart .dm-v9-direct-search-card .dm-v9-direct-options .icheckbox_square-blue.checked::after,
#order-standard_cart .dm-v9-direct-search-card .dm-v9-direct-options .icheckbox_square-blue.dm-register-patch446-checked::before,
#order-standard_cart .dm-v9-direct-search-card .dm-v9-direct-options .icheckbox_square-blue.dm-register-patch446-checked::after,
#order-standard_cart .dm-v9-direct-options .icheckbox_square-blue.dm-register-patch446-icheck.checked::before,
#order-standard_cart .dm-v9-direct-options .icheckbox_square-blue.dm-register-patch446-icheck.checked::after,
#order-standard_cart .dm-v9-direct-options .icheckbox_square-blue.dm-register-patch446-icheck.dm-register-patch446-checked::before,
#order-standard_cart .dm-v9-direct-options .icheckbox_square-blue.dm-register-patch446-icheck.dm-register-patch446-checked::after {
    content: none !important;
    display: none !important;
}
</style>
<script id="domainmonger-register-options-white-checkmark-patch446-js">
(function($) {
    'use strict';

    function refreshQuickCheckboxCheckmarks() {
        var $scope = $('#order-standard_cart .dm-v9-direct-search-card .dm-v9-direct-options, #order-standard_cart .dm-v9-direct-options').first();

        if (!$scope.length) {
            return;
        }

        $scope.find('input[type="checkbox"]').addClass('dm-register-patch446-native');

        $scope.find('.icheckbox_square-blue').each(function() {
            var $box = $(this);
            var $input = $box.find('input[type="checkbox"]');
            var isChecked = $box.hasClass('checked') || ($input.length && $input.prop('checked'));

            $box.addClass('dm-register-patch446-icheck')
                .toggleClass('dm-register-patch446-checked', !!isChecked)
                .attr('aria-checked', isChecked ? 'true' : 'false');
        });
    }

    $(refreshQuickCheckboxCheckmarks);
    $(window).on('load', refreshQuickCheckboxCheckmarks);

    $(document).on('ifChanged ifChecked ifUnchecked change click', '#order-standard_cart .dm-v9-direct-options input[type="checkbox"], #order-standard_cart .dm-v9-direct-options .icheckbox_square-blue', function() {
        setTimeout(refreshQuickCheckboxCheckmarks, 0);
        setTimeout(refreshQuickCheckboxCheckmarks, 75);
    });

    setTimeout(refreshQuickCheckboxCheckmarks, 150);
    setTimeout(refreshQuickCheckboxCheckmarks, 500);
})(jQuery);
</script>
HTML;
});
