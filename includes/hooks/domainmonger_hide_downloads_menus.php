<?php

use WHMCS\View\Menu\Item as MenuItem;

/**
 * DomainMonger: Hide Downloads from WHMCS client-area menus.
 *
 * Removes the Downloads entry from:
 * - Primary WHMCS navigation menu, usually under Support
 * - Secondary Support sidebar menu
 *
 * This does not change downloads, language files, templates, order forms,
 * product data, or integration files.
 */
function domainmongerRemoveDownloadsMenuChildren(MenuItem $menuItem)
{
    foreach (['Downloads', 'Download'] as $childName) {
        if (!is_null($menuItem->getChild($childName))) {
            $menuItem->removeChild($childName);
        }
    }
}

add_hook('ClientAreaPrimaryNavbar', 100, function (MenuItem $primaryNavbar) {
    $supportMenu = $primaryNavbar->getChild('Support');

    if (!is_null($supportMenu)) {
        domainmongerRemoveDownloadsMenuChildren($supportMenu);
    }

    // Defensive fallback in case a template/customization exposes Downloads as a top-level item.
    domainmongerRemoveDownloadsMenuChildren($primaryNavbar);
});

add_hook('ClientAreaSecondarySidebar', 100, function (MenuItem $secondarySidebar) {
    $supportMenu = $secondarySidebar->getChild('Support');

    if (!is_null($supportMenu)) {
        domainmongerRemoveDownloadsMenuChildren($supportMenu);
    }

    // Defensive fallback in case the secondary sidebar exposes Downloads directly.
    domainmongerRemoveDownloadsMenuChildren($secondarySidebar);
});
