<?php
/**
 * DomainMonger Register Domain category button text restore.
 *
 * Route-scoped to the custom customer register page only:
 * /manage/cart.php?a=add&domain=register
 *
 * Keeps the WHMCS v9 support/testing route isolated:
 * /manage/cart.php?a=add&domain=register&dmv9support=1
 */

use WHMCS\View\Menu\Item as MenuItem;

add_hook('ClientAreaFooterOutput', 1, function (array $vars) {
    $filename = (string)($vars['filename'] ?? '');
    $templateFile = (string)($vars['templatefile'] ?? '');

    if ($filename !== 'cart' || $templateFile !== 'domainregister') {
        return '';
    }

    if (isset($_GET['dmv9support']) && $_GET['dmv9support'] !== '') {
        return '';
    }

    return <<<'HTML'
<script id="dm-register-category-button-text-371">
(function () {
    function normalize(text) {
        return (text || '').replace(/\s+/g, ' ').trim();
    }

    function readableFromValue(value) {
        value = normalize(value).replace(/^\.+/, '').replace(/\.+$/, '');
        if (!value) return '';

        var known = {
            popular: 'Popular',
            sale: 'Sale',
            new: 'New',
            geographic: 'Geographic',
            technology: 'Technology',
            business: 'Business',
            services: 'Services',
            personal: 'Personal',
            shopping: 'Shopping',
            international: 'International',
            adult: 'Adult',
            all: 'All'
        };

        var key = value.toLowerCase();
        if (known[key]) return known[key];

        return value
            .replace(/[._-]+/g, ' ')
            .replace(/\b\w/g, function (m) { return m.toUpperCase(); });
    }

    function getLabel(btn) {
        var candidates = [
            btn.getAttribute('aria-label'),
            btn.getAttribute('title'),
            btn.getAttribute('data-original-title'),
            btn.getAttribute('data-title'),
            btn.getAttribute('data-category'),
            btn.getAttribute('data-value'),
            btn.getAttribute('value')
        ];

        for (var i = 0; i < candidates.length; i++) {
            var label = readableFromValue(candidates[i]);
            if (label) return label;
        }

        var input = btn.querySelector('input[value]');
        if (input) {
            var inputLabel = readableFromValue(input.getAttribute('value'));
            if (inputLabel) return inputLabel;
        }

        return '';
    }

    function restoreLabels() {
        var selectors = [
            '.tld-filters button',
            '.tld-categories button',
            '.spotlight-tlds button',
            '.domain-pricing .btn',
            '#domainSuggestionsCategories button',
            '[data-category]'
        ];

        var buttons = document.querySelectorAll(selectors.join(','));
        buttons.forEach(function (btn) {
            var visibleText = normalize(btn.textContent);
            if (visibleText) return;

            var label = getLabel(btn);
            if (!label) return;

            var span = document.createElement('span');
            span.className = 'dm-register-category-label';
            span.textContent = label;
            btn.appendChild(span);
            btn.setAttribute('aria-label', label);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', restoreLabels);
    } else {
        restoreLabels();
    }

    window.setTimeout(restoreLabels, 250);
    window.setTimeout(restoreLabels, 1000);
})();
</script>
HTML;
});
