<?php
/**
 * DomainMonger - Domain Transfer page title centering
 * Patch 674
 *
 * Cleans up/replaces failed Patch 671/672/673 selector attempts.
 * The earlier hooks changed styles on the existing title but did not visibly
 * move the live title. This version keys off the actual transfer form and
 * rebuilds the in-page title as a full-width centered row above the transfer
 * layout, then hides the original in-column title.
 *
 * Page-specific by requiring the Domain Transfer form to exist.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 1, function ($vars) {
    return <<<'HTML'
<style id="dm-domain-transfer-title-center-674">
#order-standard_cart .dm-transfer-title-center-row-674 {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    clear: both !important;
    text-align: center !important;
    margin: 0 0 8px 0 !important;
}

#order-standard_cart .dm-transfer-title-center-row-674 h1,
#order-standard_cart .dm-transfer-title-center-row-674 h2,
#order-standard_cart .dm-transfer-title-center-row-674 h3 {
    display: block !important;
    width: 100% !important;
    margin: 0 auto !important;
    text-align: center !important;
    color: #163a5f !important;
}

#order-standard_cart .dm-transfer-title-original-hidden-674 {
    display: none !important;
}

#order-standard_cart .dm-transfer-title-empty-column-674 {
    display: none !important;
}
</style>
<script id="dm-domain-transfer-title-center-js-674">
(function () {
    function textOf(node) {
        return (node && node.textContent ? node.textContent : '').replace(/\s+/g, ' ').trim();
    }

    function isVisible(node) {
        if (!node || !node.getBoundingClientRect) {
            return false;
        }
        var rect = node.getBoundingClientRect();
        return rect.width > 0 && rect.height > 0;
    }

    function closest(node, selector) {
        return node && node.closest ? node.closest(selector) : null;
    }

    function findTransferForm(root) {
        return document.getElementById('frmDomainTransfer') ||
            (root ? root.querySelector('form input[name="a"][value="addDomainTransfer"]') : null);
    }

    function findTitle(root, form) {
        var nodes = root.querySelectorAll('h1, h2, h3, .header-lined');
        var best = null;
        var bestDistance = Infinity;

        Array.prototype.forEach.call(nodes, function (node) {
            var text = textOf(node);
            if (text !== 'Transfer Domains' || !isVisible(node)) {
                return;
            }

            if (closest(node, '.subbanner, .banner, .headermain, .navmain, .toolbarmain, .sidebar, .list-group, .panel-heading')) {
                return;
            }

            var titleNode = node.classList.contains('header-lined') ? (node.querySelector('h1, h2, h3') || node) : node;
            var distance = 0;
            if (form && titleNode.compareDocumentPosition) {
                var pos = titleNode.compareDocumentPosition(form);
                distance = (pos & Node.DOCUMENT_POSITION_FOLLOWING) ? 0 : 100000;
            }

            var rect = titleNode.getBoundingClientRect();
            distance += Math.abs(rect.top - (form ? form.getBoundingClientRect().top : rect.top));

            if (distance < bestDistance) {
                bestDistance = distance;
                best = titleNode;
            }
        });

        return best;
    }

    function centerTransferTitle() {
        var root = document.getElementById('order-standard_cart');
        if (!root) {
            return;
        }

        var formProbe = findTransferForm(root);
        var form = formProbe && formProbe.tagName && formProbe.tagName.toLowerCase() === 'form'
            ? formProbe
            : closest(formProbe, 'form');

        if (!form) {
            return;
        }

        var title = findTitle(root, form);
        if (!title) {
            return;
        }

        var existing = document.getElementById('dm-transfer-title-center-row-674');
        if (!existing) {
            existing = document.createElement('div');
            existing.id = 'dm-transfer-title-center-row-674';
            existing.className = 'dm-transfer-title-center-row-674';
            existing.innerHTML = '<h2>Transfer Domains</h2>';

            var row = closest(form, '#order-standard_cart > .row') || closest(form, '.row');
            if (row && row.parentNode) {
                row.parentNode.insertBefore(existing, row);
            } else {
                root.insertBefore(existing, root.firstChild);
            }
        }

        title.classList.add('dm-transfer-title-original-hidden-674');

        var titleWrap = closest(title, '.header-lined');
        if (titleWrap) {
            titleWrap.classList.add('dm-transfer-title-original-hidden-674');
        }

        var col = closest(title, '.col-md-9, .col-sm-9, .col-lg-9, .pull-md-right');
        if (col) {
            var remaining = textOf(col);
            if (remaining === '' || remaining === 'Transfer Domains') {
                col.classList.add('dm-transfer-title-empty-column-674');
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', centerTransferTitle);
    } else {
        centerTransferTitle();
    }

    window.setTimeout(centerTransferTitle, 250);
    window.setTimeout(centerTransferTitle, 1000);
})();
</script>
HTML;
});
