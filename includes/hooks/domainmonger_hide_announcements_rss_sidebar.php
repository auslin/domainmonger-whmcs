<?php
/**
 * DomainMonger Patch 571
 * Fix Patch 570: remove the Announcements "View RSS Feed" side-menu item.
 *
 * Scope: WHMCS client-area sidebars only.
 * Notes:
 * - Replaces/cleans up the failed Patch 570 hook in this same file.
 * - Does not touch language files.
 * - Does not touch templates, order forms, products, addons, or integration files.
 * - Hooks both PrimarySidebar and SecondarySidebar because WHMCS/template placement can vary.
 */

use WHMCS\View\Menu\Item as MenuItem;

if (!function_exists('domainmongerCleanMenuText571')) {
    /**
     * Normalize menu text for reliable comparisons.
     *
     * @param mixed $text
     * @return string
     */
    function domainmongerCleanMenuText571($text)
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string) $text), ENT_QUOTES, 'UTF-8'))));
    }
}

if (!function_exists('domainmongerIsAnnouncementsRssMenuItem571')) {
    /**
     * Detect the WHMCS Announcements RSS side-menu child.
     *
     * @param MenuItem $menuItem
     * @param string $childName
     * @return bool
     */
    function domainmongerIsAnnouncementsRssMenuItem571(MenuItem $menuItem, $childName = '')
    {
        $cleanName = domainmongerCleanMenuText571($childName);

        if ($cleanName === '' && method_exists($menuItem, 'getName')) {
            $cleanName = domainmongerCleanMenuText571($menuItem->getName());
        }

        $cleanLabel = method_exists($menuItem, 'getLabel')
            ? domainmongerCleanMenuText571($menuItem->getLabel())
            : '';

        $uri = method_exists($menuItem, 'getUri') ? (string) $menuItem->getUri() : '';
        $cleanUri = strtolower($uri);

        $knownNames = [
            'rss',
            'rss feed',
            'view rss',
            'view rss feed',
            'view rss feeds',
            'announcements rss',
            'announcements rss feed',
            'announcements rss feeds',
        ];

        $knownLabels = [
            'rss',
            'rss feed',
            'view rss',
            'view rss feed',
            'view rss feeds',
            'view rss fees',
        ];

        $matchesKnownText = in_array($cleanName, $knownNames, true)
            || in_array($cleanLabel, $knownLabels, true);

        $matchesAnnouncementsRssUri = stripos($cleanUri, 'announcementsrss') !== false
            || (stripos($cleanUri, 'announcements') !== false && stripos($cleanUri, 'rss') !== false);

        return $matchesKnownText || $matchesAnnouncementsRssUri;
    }
}

if (!function_exists('domainmongerRemoveAnnouncementsRssMenuItems571')) {
    /**
     * Remove matching child menu items recursively from the supplied menu tree.
     *
     * @param MenuItem $menuItem
     * @return void
     */
    function domainmongerRemoveAnnouncementsRssMenuItems571(MenuItem $menuItem)
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

            if (domainmongerIsAnnouncementsRssMenuItem571($child, $resolvedChildName)) {
                if ($resolvedChildName !== '') {
                    $childrenToRemove[] = $resolvedChildName;
                }
                continue;
            }

            domainmongerRemoveAnnouncementsRssMenuItems571($child);
        }

        foreach (array_unique($childrenToRemove) as $childName) {
            if (!is_null($menuItem->getChild($childName))) {
                $menuItem->removeChild($childName);
            }
        }
    }
}

add_hook('ClientAreaPrimarySidebar', 100, function (MenuItem $primarySidebar) {
    domainmongerRemoveAnnouncementsRssMenuItems571($primarySidebar);
});

add_hook('ClientAreaSecondarySidebar', 100, function (MenuItem $secondarySidebar) {
    domainmongerRemoveAnnouncementsRssMenuItems571($secondarySidebar);
});
