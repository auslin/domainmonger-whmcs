<?php
/**
 * DomainMonger register-page checkbox color and two-way TLD sync.
 *
 * Patch 447:
 * - Preserves the Patch 404 quick/table checkbox sync behavior.
 * - Restores both directions: quick row -> table and table -> quick row.
 * - Keeps Patch 446's white-checkmark visual state refreshed when table checkboxes change.
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

if (!function_exists('domainmonger_register_quick_checkbox_orange_is_route')) {
    function domainmonger_register_quick_checkbox_orange_is_route(array $vars): bool
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

add_hook('ClientAreaHeadOutput', 1, function ($vars) {
    if (!domainmonger_register_quick_checkbox_orange_is_route($vars)) {
        return '';
    }

    return <<<'HTML'
<style id="domainmonger-register-quick-checkbox-orange">
/* Register page only: quick TLD/filter row checkbox color. */
#order-standard_cart .dm-v9-direct-options input[type="checkbox"],
#order-standard_cart .dm-v9-direct-search-card .dm-v9-direct-options input[type="checkbox"],
#order-standard_cart .dm-v9-direct-options input.dm-v9-direct-tld {
    accent-color: #d8741f !important;
    -webkit-accent-color: #d8741f !important;
}

/* WHMCS/iCheck fallback: replace the blue square skin in the quick row only. */
#order-standard_cart .dm-v9-direct-options .icheckbox_square-blue,
#order-standard_cart .dm-v9-direct-search-card .dm-v9-direct-options .icheckbox_square-blue {
    display: inline-block !important;
    width: 18px !important;
    height: 18px !important;
    min-width: 18px !important;
    min-height: 18px !important;
    margin: 0 4px 0 0 !important;
    padding: 0 !important;
    border: 1px solid #b7c0cc !important;
    border-radius: 2px !important;
    background: #fff !important;
    background-image: none !important;
    box-shadow: none !important;
    cursor: pointer !important;
    position: relative !important;
    vertical-align: -4px !important;
}

#order-standard_cart .dm-v9-direct-options .icheckbox_square-blue.hover,
#order-standard_cart .dm-v9-direct-search-card .dm-v9-direct-options .icheckbox_square-blue.hover {
    border-color: #d8741f !important;
    background: #fff7ef !important;
    background-image: none !important;
}

#order-standard_cart .dm-v9-direct-options .icheckbox_square-blue.checked,
#order-standard_cart .dm-v9-direct-search-card .dm-v9-direct-options .icheckbox_square-blue.checked,
#order-standard_cart .dm-v9-direct-options .icheckbox_square-blue.dm-register-patch446-checked,
#order-standard_cart .dm-v9-direct-search-card .dm-v9-direct-options .icheckbox_square-blue.dm-register-patch446-checked {
    border-color: #d8741f !important;
    background-color: #d8741f !important;
    background-image: none !important;
}

#order-standard_cart .dm-v9-direct-options .icheckbox_square-blue.checked::after,
#order-standard_cart .dm-v9-direct-search-card .dm-v9-direct-options .icheckbox_square-blue.checked::after,
#order-standard_cart .dm-v9-direct-options .icheckbox_square-blue.dm-register-patch446-checked::after,
#order-standard_cart .dm-v9-direct-search-card .dm-v9-direct-options .icheckbox_square-blue.dm-register-patch446-checked::after {
    content: "";
    position: absolute;
    left: 5px;
    top: 2px;
    width: 5px;
    height: 9px;
    border: solid #fff;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

#order-standard_cart .dm-v9-direct-options .icheckbox_square-blue.disabled,
#order-standard_cart .dm-v9-direct-search-card .dm-v9-direct-options .icheckbox_square-blue.disabled {
    opacity: .55 !important;
    cursor: default !important;
}
</style>
HTML;
});

add_hook('ClientAreaFooterOutput', 10001, function ($vars) {
    if (!domainmonger_register_quick_checkbox_orange_is_route($vars)) {
        return '';
    }

    return <<<'HTML'
<script id="domainmonger-register-tld-checkbox-two-way-sync-patch447">
(function($) {
    'use strict';

    var dmSyncingTldCheckboxes = false;

    function dmNormalizeTld(value) {
        value = String(value || '').toLowerCase().replace(/^\s+|\s+$/g, '');
        if (value && value.charAt(0) !== '.') {
            value = '.' + value;
        }
        return value;
    }

    function dmTldInputs(tld) {
        tld = dmNormalizeTld(tld);
        return $('#order-standard_cart .dm-v9-direct-tld').filter(function() {
            return dmNormalizeTld($(this).val()) === tld;
        });
    }

    function dmInputFromEventTarget(target) {
        var $target = $(target);
        var $input;

        if ($target.is('input[type="checkbox"]')) {
            return $target;
        }

        $input = $target.find('input[type="checkbox"]').first();
        if ($input.length) {
            return $input;
        }

        $input = $target.closest('[class*="icheckbox_"]').find('input[type="checkbox"]').first();
        if ($input.length) {
            return $input;
        }

        $input = $target.prev('input[type="checkbox"], [class*="icheckbox_"]').filter('input[type="checkbox"]').first();
        if ($input.length) {
            return $input;
        }

        $input = $target.next('input[type="checkbox"], [class*="icheckbox_"]').filter('input[type="checkbox"]').first();
        return $input;
    }

    function dmFindIcheckBox($input) {
        var $box;

        if (!$input || !$input.length) {
            return $();
        }

        $box = $input.closest('[class*="icheckbox_"]');
        if ($box.length) {
            return $box;
        }

        $box = $input.prev('[class*="icheckbox_"]');
        if ($box.length) {
            return $box;
        }

        $box = $input.next('[class*="icheckbox_"]');
        if ($box.length) {
            return $box;
        }

        return $();
    }

    function dmSetIcheckVisual($input, checked) {
        var $box = dmFindIcheckBox($input);

        if (!$box.length) {
            return;
        }

        $box.toggleClass('checked', !!checked)
            .toggleClass('dm-register-patch446-checked', !!checked)
            .attr('aria-checked', checked ? 'true' : 'false');
    }

    function dmSetCheckedAndVisual($inputs, checked) {
        $inputs.each(function() {
            var $input = $(this);
            $input.prop('checked', !!checked);

            if (checked) {
                $input.attr('checked', 'checked');
            } else {
                $input.removeAttr('checked');
            }

            dmSetIcheckVisual($input, checked);
        });
    }

    function dmIsInputChecked($input) {
        var $box = dmFindIcheckBox($input);

        if ($box.length) {
            if ($box.hasClass('checked') || $box.hasClass('dm-register-patch446-checked')) {
                return true;
            }
            if (!$box.hasClass('checked') && !$box.hasClass('dm-register-patch446-checked')) {
                return !!$input.prop('checked');
            }
        }

        return !!$input.prop('checked');
    }

    function dmRefreshTldVisuals() {
        $('#order-standard_cart .dm-v9-direct-tld').each(function() {
            var $input = $(this);
            dmSetIcheckVisual($input, $input.prop('checked'));
        });
    }

    function dmUpdateSelectedCount() {
        var seen = {};
        var count = 0;

        $('#order-standard_cart .dm-v9-direct-tld:checked').each(function() {
            var tld = dmNormalizeTld($(this).val());
            if (tld && !seen[tld]) {
                seen[tld] = true;
                count++;
            }
        });

        $('#dmV9DirectTldSelectedCount').text(count + ' extension' + (count === 1 ? '' : 's') + ' selected for search');
    }

    function dmSyncTldFromTarget(target) {
        var $source = dmInputFromEventTarget(target);
        var tld;
        var checked;

        if (!$source.length || !$source.hasClass('dm-v9-direct-tld')) {
            return;
        }

        tld = dmNormalizeTld($source.val());
        if (!tld || dmSyncingTldCheckboxes) {
            return;
        }

        checked = dmIsInputChecked($source);

        dmSyncingTldCheckboxes = true;
        dmSetCheckedAndVisual(dmTldInputs(tld), checked);
        dmSyncingTldCheckboxes = false;

        dmUpdateSelectedCount();
        dmRefreshTldVisuals();
    }

    function dmScheduleSync(target) {
        setTimeout(function() {
            dmSyncTldFromTarget(target);
        }, 0);
        setTimeout(function() {
            dmSyncTldFromTarget(target);
            dmRefreshTldVisuals();
        }, 75);
    }

    $(document).on('change ifChanged ifChecked ifUnchecked click', '#order-standard_cart .dm-v9-direct-tld, #order-standard_cart [class*="icheckbox_"]', function() {
        dmScheduleSync(this);
    });

    $(document).on('click', '#dmV9DirectSelectShown, #dmV9DirectClearShown, #dmV9DirectShowSelected, #dmV9DirectTldCategories [data-dm-tld-category]', function() {
        setTimeout(function() {
            dmUpdateSelectedCount();
            dmRefreshTldVisuals();
        }, 0);
        setTimeout(function() {
            dmUpdateSelectedCount();
            dmRefreshTldVisuals();
        }, 100);
    });

    $(function() {
        setTimeout(function() {
            dmUpdateSelectedCount();
            dmRefreshTldVisuals();
        }, 0);
        setTimeout(function() {
            dmUpdateSelectedCount();
            dmRefreshTldVisuals();
        }, 150);
        setTimeout(function() {
            dmUpdateSelectedCount();
            dmRefreshTldVisuals();
        }, 500);
    });
})(jQuery);
</script>
HTML;
});
