<?php

use WHMCS\View\Menu\Item as MenuItem;

/**
 * DomainMonger: Hide the Product Addons category from the client-area secondary sidebar.
 *
 * This removes the visible Categories > Product Addons menu entry without changing
 * products, product addons, language files, templates, or order form files.
 */
add_hook('ClientAreaSecondarySidebar', 1, function (MenuItem $secondarySidebar) {
    $categories = $secondarySidebar->getChild('Categories');

    if (!is_null($categories) && !is_null($categories->getChild('Addons'))) {
        $categories->removeChild('Addons');
    }
});
