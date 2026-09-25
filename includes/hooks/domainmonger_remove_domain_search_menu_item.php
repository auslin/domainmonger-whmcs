<?php
/**
 * DomainMonger Patch 444
 * Remove the legacy "Domain Search" item from the WHMCS Menu > Domains dropdown.
 * Also removes the divider immediately before that item when present.
 *
 * Safe scope:
 * - Menu-only change.
 * - No templates, language file, or integration-folder changes.
 * - Skips the isolated WHMCS v9 support route flag dmv9support=1.
 */

use WHMCS\View\Menu\Item as MenuItem;

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

/**
 * Determine whether this request is the isolated v9 support route.
 */
function dm_patch444_is_v9_support_route(): bool
{
    return isset($_GET['dmv9support']) && (string) $_GET['dmv9support'] === '1';
}

/**
 * Normalize visible menu text for reliable matching.
 */
function dm_patch444_normalize_menu_text($text): string
{
    $text = html_entity_decode((string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = trim(strip_tags($text));
    $text = preg_replace('/\s+/', ' ', $text);

    return strtolower((string) $text);
}

/**
 * Return a child/menu item name if available.
 */
function dm_patch444_item_name($item): string
{
    if (is_object($item) && method_exists($item, 'getName')) {
        return (string) $item->getName();
    }

    return '';
}

/**
 * Return a child/menu item label if available.
 */
function dm_patch444_item_label($item): string
{
    if (is_object($item) && method_exists($item, 'getLabel')) {
        return (string) $item->getLabel();
    }

    return '';
}

/**
 * Return a child/menu item URI if available.
 */
function dm_patch444_item_uri($item): string
{
    if (is_object($item) && method_exists($item, 'getUri')) {
        return (string) $item->getUri();
    }

    return '';
}

/**
 * Return a child/menu item class if available.
 */
function dm_patch444_item_class($item): string
{
    if (is_object($item) && method_exists($item, 'getClass')) {
        return (string) $item->getClass();
    }

    return '';
}

/**
 * Match the legacy Domain Search menu item without affecting Register/Transfer links.
 */
function dm_patch444_is_domain_search_item($item): bool
{
    $name = dm_patch444_normalize_menu_text(dm_patch444_item_name($item));
    $label = dm_patch444_normalize_menu_text(dm_patch444_item_label($item));
    $uri = strtolower(dm_patch444_item_uri($item));

    if ($name === 'domain search' || $label === 'domain search') {
        return true;
    }

    // Older WHMCS menus can point the legacy Domain Search item to domainchecker.php.
    return strpos($uri, 'domainchecker.php') !== false;
}

/**
 * Detect divider-only menu children so the orphaned break line can be removed too.
 */
function dm_patch444_is_divider_item($item): bool
{
    $class = ' ' . dm_patch444_item_class($item) . ' ';

    return strpos($class, ' dropdown-divider ') !== false
        || strpos($class, ' nav-divider ') !== false;
}

/**
 * Find the Domains dropdown item even if the internal name varies slightly.
 */
function dm_patch444_find_domains_menu(MenuItem $primaryNavbar)
{
    $domains = null;

    if (method_exists($primaryNavbar, 'getChild')) {
        $domains = $primaryNavbar->getChild('Domains');
    }

    if ($domains || !method_exists($primaryNavbar, 'getChildren')) {
        return $domains;
    }

    foreach ($primaryNavbar->getChildren() as $topItem) {
        $name = dm_patch444_normalize_menu_text(dm_patch444_item_name($topItem));
        $label = dm_patch444_normalize_menu_text(dm_patch444_item_label($topItem));

        if ($name === 'domains' || $label === 'domains') {
            return $topItem;
        }
    }

    return null;
}

/**
 * Menu API cleanup: remove Domain Search and the divider directly above it.
 */
add_hook('ClientAreaPrimaryNavbar', 1, function (MenuItem $primaryNavbar) {
    if (dm_patch444_is_v9_support_route()) {
        return;
    }

    $domains = dm_patch444_find_domains_menu($primaryNavbar);

    if (!$domains || !method_exists($domains, 'getChildren') || !method_exists($domains, 'removeChild')) {
        return;
    }

    $namesToRemove = [];
    $pendingDividerName = '';

    foreach ($domains->getChildren() as $childItem) {
        $childName = dm_patch444_item_name($childItem);

        if ($childName === '') {
            continue;
        }

        if (dm_patch444_is_divider_item($childItem)) {
            $pendingDividerName = $childName;
            continue;
        }

        if (dm_patch444_is_domain_search_item($childItem)) {
            $namesToRemove[] = $childName;

            if ($pendingDividerName !== '') {
                $namesToRemove[] = $pendingDividerName;
            }
        }

        $pendingDividerName = '';
    }

    foreach (array_unique($namesToRemove) as $childName) {
        $domains->removeChild($childName);
    }
});

/**
 * Conservative DOM fallback for customized templates/cached menu output.
 * This only removes an exact "Domain Search"/domainchecker.php child from the Domains dropdown.
 */
add_hook('ClientAreaFooterOutput', 1, function (array $vars) {
    if (dm_patch444_is_v9_support_route()) {
        return '';
    }

    return <<<'HTML'
<script>
(function ($) {
    if (!$) {
        return;
    }

    function dmNormalizeMenuText(text) {
        return $.trim(String(text || '').replace(/\s+/g, ' ')).toLowerCase();
    }

    function dmFindDomainsDropdown() {
        var $domains = $('#Primary_Navbar-Domains').first();

        if (!$domains.length) {
            $domains = $('li').filter(function () {
                var menuName = $(this).attr('menuitemname') || $(this).attr('menuItemName') || '';
                return menuName === 'Domains';
            }).first();
        }

        if (!$domains.length) {
            $domains = $('li.dropdown').filter(function () {
                return dmNormalizeMenuText($(this).children('a').first().text()) === 'domains';
            }).first();
        }

        return $domains.children('ul.dropdown-menu').first();
    }

    function dmRemoveDomainSearchMenuItem() {
        var $menu = dmFindDomainsDropdown();

        if (!$menu.length) {
            return;
        }

        $menu.children('li, div').each(function () {
            var $item = $(this);
            var label = dmNormalizeMenuText($item.text());
            var href = String($item.find('a').attr('href') || '').toLowerCase();
            var isDomainSearch = label === 'domain search' || href.indexOf('domainchecker.php') !== -1;

            if (!isDomainSearch) {
                return;
            }

            var $previous = $item.prev();

            if ($previous.hasClass('dropdown-divider') || $previous.hasClass('nav-divider')) {
                $previous.remove();
            }

            $item.remove();
        });
    }

    $(dmRemoveDomainSearchMenuItem);
    $(document).on('shown.bs.dropdown', dmRemoveDomainSearchMenuItem);
    window.setTimeout(dmRemoveDomainSearchMenuItem, 250);
})(window.jQuery);
</script>
HTML;
});
