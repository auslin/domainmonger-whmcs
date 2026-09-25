<?php
/**
 * DomainMonger WHMCS v9 styling cleanup: Add Funds page.
 *
 * Patch 440 cleans up the Patch 439 limits-table experiment, keeps the
 * confirmed Patch 438 normal-size Add Funds button fix, and renders the
 * account-credit limits as a real white/navy summary card.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

function domainmonger_is_add_funds_page(array $vars = []): bool
{
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';

    if (strpos($requestUri, 'dmv9support=1') !== false) {
        return false;
    }

    if (strpos($requestUri, 'action=addfunds') !== false) {
        return true;
    }

    if (isset($vars['templatefile']) && stripos((string) $vars['templatefile'], 'addfunds') !== false) {
        return true;
    }

    if (isset($vars['filename']) && stripos((string) $vars['filename'], 'clientarea') !== false) {
        $action = $_REQUEST['action'] ?? '';
        if ((string) $action === 'addfunds') {
            return true;
        }
    }

    return false;
}

add_hook('ClientAreaFooterOutput', 1, function ($vars) {
    if (!domainmonger_is_add_funds_page(is_array($vars) ? $vars : [])) {
        return '';
    }

    return <<<'HTML'
<style id="domainmonger-patch440-add-funds-limits-cards">
/* DomainMonger Patch 440: Add Funds limits summary-card cleanup. */

/* Hide the original limits table after JS creates the replacement card. */
body table.dm-addfunds-limits-source-table,
body .dm-addfunds-limits-card table.dm-addfunds-limits-source-table,
body table.dm-addfunds-limits-table.dm-addfunds-limits-source-table {
    display: none !important;
}

body .dm-addfunds-limits-summary {
    background: #ffffff !important;
    border: 1px solid #d7dee8 !important;
    border-radius: 4px !important;
    box-shadow: none !important;
    overflow: hidden !important;
    margin: 0 0 18px 0 !important;
}

body .dm-addfunds-limits-summary__header {
    background: #163a5f !important;
    color: #ffffff !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    line-height: 1.3 !important;
    padding: 10px 14px !important;
    margin: 0 !important;
}

body .dm-addfunds-limits-summary__body {
    display: grid !important;
    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    gap: 0 !important;
    background: #ffffff !important;
}

body .dm-addfunds-limit-tile {
    background: #ffffff !important;
    border-right: 1px solid #e5eaf0 !important;
    padding: 14px 16px !important;
    min-width: 0 !important;
}

body .dm-addfunds-limit-tile:last-child {
    border-right: 0 !important;
}

body .dm-addfunds-limit-tile:hover {
    background: #fff3e8 !important;
}

body .dm-addfunds-limit-tile__label {
    color: #5f6f82 !important;
    display: block !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    letter-spacing: 0 !important;
    line-height: 1.3 !important;
    margin: 0 0 6px 0 !important;
    text-transform: none !important;
}

body .dm-addfunds-limit-tile__value {
    color: #163a5f !important;
    display: block !important;
    font-size: 18px !important;
    font-weight: 700 !important;
    line-height: 1.25 !important;
    margin: 0 !important;
    white-space: nowrap !important;
}

/* Add Funds submit: keep the confirmed Patch 438 normal-size primary button, left aligned, not full-width. */
body .dm-addfunds-submit-wrap,
body .dm-addfunds-submit-wrap .text-center,
body .dm-addfunds-submit-wrap .text-right,
body form[action*="addfunds"] .form-group:last-of-type,
body form[action*="addfunds"] .text-center:last-child,
body form[action*="addfunds"] .text-right:last-child {
    text-align: left !important;
}

body .dm-addfunds-submit,
body input.dm-addfunds-submit,
body button.dm-addfunds-submit,
body form[action*="addfunds"] input[type="submit"][value="Add Funds"],
body form[action*="addfunds"] button[type="submit"],
body form[action*="addfunds"] .btn[type="submit"] {
    display: inline-block !important;
    width: auto !important;
    max-width: none !important;
    min-width: 130px !important;
    padding: 8px 18px !important;
    font-size: 13px !important;
    line-height: 1.35 !important;
    text-align: center !important;
    float: none !important;
    clear: none !important;
    background: #f58220 !important;
    background-color: #f58220 !important;
    border-color: #f58220 !important;
    color: #ffffff !important;
    box-shadow: none !important;
    text-shadow: none !important;
}

body .dm-addfunds-submit:hover,
body .dm-addfunds-submit:focus,
body form[action*="addfunds"] input[type="submit"][value="Add Funds"]:hover,
body form[action*="addfunds"] input[type="submit"][value="Add Funds"]:focus,
body form[action*="addfunds"] button[type="submit"]:hover,
body form[action*="addfunds"] button[type="submit"]:focus,
body form[action*="addfunds"] .btn[type="submit"]:hover,
body form[action*="addfunds"] .btn[type="submit"]:focus {
    background: #214e7a !important;
    background-color: #214e7a !important;
    border-color: #214e7a !important;
    color: #ffffff !important;
}

@media (max-width: 767.98px) {
    body .dm-addfunds-limits-summary__body {
        grid-template-columns: 1fr !important;
    }

    body .dm-addfunds-limit-tile {
        border-right: 0 !important;
        border-top: 1px solid #e5eaf0 !important;
    }

    body .dm-addfunds-limit-tile:first-child {
        border-top: 0 !important;
    }
}
</style>
<script>
(function () {
    function normalizeText(value) {
        return ((value || '') + '').replace(/\s+/g, ' ').trim();
    }

    function createLimitTile(label, value) {
        var tile = document.createElement('div');
        tile.className = 'dm-addfunds-limit-tile';

        var labelNode = document.createElement('span');
        labelNode.className = 'dm-addfunds-limit-tile__label';
        labelNode.textContent = label;

        var valueNode = document.createElement('span');
        valueNode.className = 'dm-addfunds-limit-tile__value';
        valueNode.textContent = value;

        tile.appendChild(labelNode);
        tile.appendChild(valueNode);
        return tile;
    }

    function buildLimitsSummary(table) {
        if (!table || table.dataset.dmAddfundsSummaryBuilt === '1') {
            return;
        }

        var rows = [];
        table.querySelectorAll('tr').forEach(function (row) {
            var cells = row.querySelectorAll('td, th');
            if (cells.length < 2) {
                return;
            }
            var label = normalizeText(cells[0].textContent);
            var value = normalizeText(cells[cells.length - 1].textContent);
            if (label && value) {
                rows.push({ label: label, value: value });
            }
        });

        if (!rows.length) {
            return;
        }

        var summary = document.createElement('div');
        summary.className = 'dm-addfunds-limits-summary';

        var header = document.createElement('div');
        header.className = 'dm-addfunds-limits-summary__header';
        header.textContent = 'Add Funds Limits';

        var body = document.createElement('div');
        body.className = 'dm-addfunds-limits-summary__body';

        rows.forEach(function (row) {
            body.appendChild(createLimitTile(row.label, row.value));
        });

        summary.appendChild(header);
        summary.appendChild(body);

        table.classList.add('dm-addfunds-limits-source-table');
        table.dataset.dmAddfundsSummaryBuilt = '1';

        var currentParent = table.parentElement;
        if (currentParent && currentParent.classList.contains('dm-addfunds-limits-card')) {
            currentParent.parentNode.insertBefore(summary, currentParent);
            currentParent.style.display = 'none';
        } else if (currentParent) {
            currentParent.insertBefore(summary, table);
        }
    }

    function applyDomainMongerAddFundsCleanup() {
        document.querySelectorAll('table').forEach(function (table) {
            var text = normalizeText(table.textContent);
            if (text.indexOf('Minimum Deposit') !== -1 && text.indexOf('Maximum Deposit') !== -1 && text.indexOf('Maximum Balance') !== -1) {
                buildLimitsSummary(table);
            }
        });

        document.querySelectorAll('input[type="submit"], button[type="submit"], .btn').forEach(function (button) {
            var label = normalizeText((button.value || button.textContent || ''));
            if (label === 'Add Funds') {
                button.classList.add('dm-addfunds-submit');
                if (button.parentElement) {
                    button.parentElement.classList.add('dm-addfunds-submit-wrap');
                }
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyDomainMongerAddFundsCleanup);
    } else {
        applyDomainMongerAddFundsCleanup();
    }

    window.setTimeout(applyDomainMongerAddFundsCleanup, 250);
})();
</script>
HTML;
});
