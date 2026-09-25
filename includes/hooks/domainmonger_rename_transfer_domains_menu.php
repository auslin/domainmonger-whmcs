<?php
/**
 * DomainMonger Patch 563
 * Fix Patch 562 by renaming the visible WHMCS menu label
 * "Transfer Domains to Us" to "Transfer Domains" wherever WHMCS builds it.
 *
 * Scope: WHMCS menu/sidebar label only.
 * Notes:
 * - Does not touch language files.
 * - Does not touch templates, order forms, or integration files.
 * - Uses a recursive label/URI fallback because WHMCS internal child names can vary by template/version.
 */

use WHMCS\View\Menu\Item as MenuItem;

if (!function_exists('domainmongerRenameTransferDomainsMenuItems')) {
    /**
     * Rename matching transfer-domain menu items recursively.
     *
     * @param MenuItem $menuItem
     * @return void
     */
    function domainmongerRenameTransferDomainsMenuItems(MenuItem $menuItem)
    {
        $label = method_exists($menuItem, 'getLabel') ? (string) $menuItem->getLabel() : '';
        $cleanLabel = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($label), ENT_QUOTES, 'UTF-8')));

        $uri = '';
        if (method_exists($menuItem, 'getUri')) {
            $uri = (string) $menuItem->getUri();
        }

        $isTransferDomainsLabel = strcasecmp($cleanLabel, 'Transfer Domains to Us') === 0;
        $isTransferDomainsUri = stripos($uri, 'cart.php') !== false
            && stripos($uri, 'domain=transfer') !== false;

        if ($isTransferDomainsLabel || $isTransferDomainsUri) {
            $menuItem->setLabel('Transfer Domains');
        }

        if (method_exists($menuItem, 'getChildren')) {
            foreach ($menuItem->getChildren() as $child) {
                if ($child instanceof MenuItem) {
                    domainmongerRenameTransferDomainsMenuItems($child);
                }
            }
        }
    }
}

add_hook('ClientAreaPrimaryNavbar', 1, function (MenuItem $primaryNavbar) {
    domainmongerRenameTransferDomainsMenuItems($primaryNavbar);
});

add_hook('ClientAreaSecondarySidebar', 1, function (MenuItem $secondarySidebar) {
    domainmongerRenameTransferDomainsMenuItems($secondarySidebar);
});
