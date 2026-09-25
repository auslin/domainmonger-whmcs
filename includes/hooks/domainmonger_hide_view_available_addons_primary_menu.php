<?php
/**
 * DomainMonger Patch 565
 * Hide "View Available Addons" from the WHMCS primary navigation menu.
 *
 * Scope: WHMCS primary navbar only.
 * Notes:
 * - Does not touch language files.
 * - Does not touch templates, order forms, products, addons, or integration files.
 * - Uses label/URI matching because WHMCS internal menu item names can vary.
 */

use WHMCS\View\Menu\Item as MenuItem;

if (!function_exists('domainmongerCleanMenuText565')) {
    /**
     * Normalize menu labels for reliable comparisons.
     *
     * @param string $label
     * @return string
     */
    function domainmongerCleanMenuText565($label)
    {
        return trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string) $label), ENT_QUOTES, 'UTF-8')));
    }
}

if (!function_exists('domainmongerIsViewAvailableAddonsMenuItem565')) {
    /**
     * Detect the WHMCS "View Available Addons" menu item.
     *
     * @param MenuItem $menuItem
     * @return bool
     */
    function domainmongerIsViewAvailableAddonsMenuItem565(MenuItem $menuItem)
    {
        $label = method_exists($menuItem, 'getLabel')
            ? domainmongerCleanMenuText565($menuItem->getLabel())
            : '';

        if (strcasecmp($label, 'View Available Addons') === 0) {
            return true;
        }

        $uri = method_exists($menuItem, 'getUri') ? (string) $menuItem->getUri() : '';

        return stripos($uri, 'cart.php') !== false
            && stripos($uri, 'gid=addons') !== false;
    }
}

if (!function_exists('domainmongerRemoveViewAvailableAddonsMenuItems565')) {
    /**
     * Remove matching child menu items recursively from the supplied menu tree.
     *
     * @param MenuItem $menuItem
     * @return void
     */
    function domainmongerRemoveViewAvailableAddonsMenuItems565(MenuItem $menuItem)
    {
        if (!method_exists($menuItem, 'getChildren')) {
            return;
        }

        $childrenToRemove = [];

        foreach ($menuItem->getChildren() as $childName => $child) {
            if (!$child instanceof MenuItem) {
                continue;
            }

            $resolvedChildName = is_string($childName) ? $childName : '';
            if ($resolvedChildName === '' && method_exists($child, 'getName')) {
                $resolvedChildName = (string) $child->getName();
            }

            if (domainmongerIsViewAvailableAddonsMenuItem565($child)) {
                if ($resolvedChildName !== '') {
                    $childrenToRemove[] = $resolvedChildName;
                }
                continue;
            }

            domainmongerRemoveViewAvailableAddonsMenuItems565($child);
        }

        foreach (array_unique($childrenToRemove) as $childName) {
            if (!is_null($menuItem->getChild($childName))) {
                $menuItem->removeChild($childName);
            }
        }
    }
}

add_hook('ClientAreaPrimaryNavbar', 100, function (MenuItem $primaryNavbar) {
    domainmongerRemoveViewAvailableAddonsMenuItems565($primaryNavbar);
});
