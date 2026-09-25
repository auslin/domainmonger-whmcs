<?php
/**
 * DomainMonger WHMCS v9 styling
 * Page-specific: Add Funds and My Details duplicate content-box title cleanup.
 *
 * Patch 634 removes duplicate page-name titles from blue card/content headers
 * while preserving the main page header/subbanner title.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

function dm_addfunds_details_duplicate_titles_is_target_page($vars)
{
    $templateFile = isset($vars['templatefile']) ? (string) $vars['templatefile'] : '';
    $requestUri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';

    return $templateFile === 'clientareaaddfunds'
        || $templateFile === 'clientareadetails'
        || strpos($requestUri, 'clientarea.php?action=addfunds') !== false
        || strpos($requestUri, 'clientarea.php?action=details') !== false;
}

add_hook('ClientAreaFooterOutput', 1, function ($vars) {
    if (!dm_addfunds_details_duplicate_titles_is_target_page($vars)) {
        return '';
    }

    return <<<'HTML'
<script id="dm-addfunds-details-duplicate-card-titles-v1">
(function () {
    'use strict';

    function normalizeText(value) {
        return String(value || '')
            .replace(/\s+/g, ' ')
            .trim()
            .toLowerCase();
    }

    function targetTitlesForCurrentPage() {
        var bodyClass = document.body ? document.body.className : '';
        var href = window.location.href || '';

        if (bodyClass.indexOf('whmcs-templatefile-clientareaaddfunds') !== -1 || href.indexOf('action=addfunds') !== -1) {
            return ['add funds'];
        }

        if (bodyClass.indexOf('whmcs-templatefile-clientareadetails') !== -1 || href.indexOf('action=details') !== -1) {
            return ['my details'];
        }

        return [];
    }

    function hideDuplicateTitleElement(element) {
        var hideTarget = element;
        var cardHeader = element.classList && element.classList.contains('card-header')
            ? element
            : (element.closest ? element.closest('.card-header') : null);

        if (cardHeader) {
            hideTarget = cardHeader;
        } else if (element.parentElement && element.parentElement.className && /section-header|card-heading|card-title-wrap/i.test(element.parentElement.className)) {
            if (normalizeText(element.parentElement.textContent) === normalizeText(element.textContent)) {
                hideTarget = element.parentElement;
            }
        }

        hideTarget.style.display = 'none';
        hideTarget.setAttribute('data-dm-hidden-duplicate-page-title', 'true');
    }

    function removeDuplicateCardPageTitles() {
        var titles = targetTitlesForCurrentPage();

        if (!titles.length) {
            return;
        }

        var candidates = document.querySelectorAll([
            '.card .card-title',
            '.card .card-header',
            '.card h1',
            '.card h2',
            '.card h3',
            '.card h4'
        ].join(','));

        Array.prototype.forEach.call(candidates, function (element) {
            var text = normalizeText(element.textContent);

            if (titles.indexOf(text) !== -1) {
                hideDuplicateTitleElement(element);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', removeDuplicateCardPageTitles);
    } else {
        removeDuplicateCardPageTitles();
    }

    window.setTimeout(removeDuplicateCardPageTitles, 100);
    window.setTimeout(removeDuplicateCardPageTitles, 500);
}());
</script>
HTML;
});
