<?php
/**
 * DomainMonger WHMCS v9
 * Patch 683: Use DNS product names in the Services dropdown.
 *
 * Scope:
 * - Logged-in client area only.
 * - Finds active services whose product group name is DNS.
 * - Adds product-name links under the existing Services dropdown.
 * - Does not touch templates, language files, order forms, or integration files.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;
use WHMCS\View\Menu\Item as MenuItem;

if (!function_exists('domainmongerDnsMenuClientId683')) {
    /**
     * Resolve the logged-in client id from the common WHMCS session keys.
     */
    function domainmongerDnsMenuClientId683(): int
    {
        foreach (['uid', 'clientid'] as $key) {
            if (!empty($_SESSION[$key]) && !is_array($_SESSION[$key])) {
                return (int) $_SESSION[$key];
            }
        }

        return 0;
    }
}

if (!function_exists('domainmongerDnsMenuCleanText683')) {
    /**
     * Normalize labels/names so this works across template/menu variations.
     */
    function domainmongerDnsMenuCleanText683($value): string
    {
        $text = html_entity_decode(strip_tags((string) $value), ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        return strtolower(trim((string) $text));
    }
}

if (!function_exists('domainmongerDnsMenuDisplayText683')) {
    /**
     * Clean display labels without forcing lowercase.
     */
    function domainmongerDnsMenuDisplayText683($value): string
    {
        $text = html_entity_decode(strip_tags((string) $value), ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        return trim((string) $text);
    }
}

if (!function_exists('domainmongerDnsMenuFindServices683')) {
    /**
     * Find the primary Services dropdown without assuming only one internal name.
     */
    function domainmongerDnsMenuFindServices683(MenuItem $primaryNavbar): ?MenuItem
    {
        if (method_exists($primaryNavbar, 'getChild')) {
            $services = $primaryNavbar->getChild('Services');
            if ($services instanceof MenuItem) {
                return $services;
            }
        }

        if (!method_exists($primaryNavbar, 'getChildren')) {
            return null;
        }

        foreach ($primaryNavbar->getChildren() as $item) {
            if (!$item instanceof MenuItem) {
                continue;
            }

            $name = method_exists($item, 'getName') ? domainmongerDnsMenuCleanText683($item->getName()) : '';
            $label = method_exists($item, 'getLabel') ? domainmongerDnsMenuCleanText683($item->getLabel()) : '';

            if ($name === 'services' || $label === 'services') {
                return $item;
            }
        }

        return null;
    }
}

if (!function_exists('domainmongerDnsMenuActiveServices683')) {
    /**
     * Return active services for products in the DNS product group.
     *
     * @return array<int, array{id:int, product_name:string, domain:string}>
     */
    function domainmongerDnsMenuActiveServices683(int $clientId): array
    {
        if ($clientId <= 0 || !class_exists(Capsule::class)) {
            return [];
        }

        try {
            $rows = Capsule::table('tblhosting')
                ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
                ->join('tblproductgroups', 'tblproductgroups.id', '=', 'tblproducts.gid')
                ->where('tblhosting.userid', '=', $clientId)
                ->where('tblhosting.domainstatus', '=', 'Active')
                ->where('tblproductgroups.name', '=', 'DNS')
                ->orderBy('tblhosting.id', 'asc')
                ->select(
                    'tblhosting.id',
                    'tblhosting.domain',
                    'tblproducts.name as product_name'
                )
                ->get();
        } catch (\Throwable $e) {
            return [];
        }

        $services = [];
        foreach ($rows as $row) {
            if (!isset($row->id)) {
                continue;
            }

            $serviceId = (int) $row->id;
            if ($serviceId <= 0) {
                continue;
            }

            $productName = domainmongerDnsMenuDisplayText683($row->product_name ?? '');
            if ($productName === '') {
                $productName = 'DNS';
            }

            $services[$serviceId] = [
                'id' => $serviceId,
                'product_name' => $productName,
                'domain' => domainmongerDnsMenuDisplayText683($row->domain ?? ''),
            ];
        }

        return array_values($services);
    }
}

add_hook('ClientAreaPrimaryNavbar', 40, function (MenuItem $primaryNavbar) {
    $clientId = domainmongerDnsMenuClientId683();
    if ($clientId <= 0) {
        return;
    }

    $activeDnsServices = domainmongerDnsMenuActiveServices683($clientId);
    if (empty($activeDnsServices)) {
        return;
    }

    $servicesMenu = domainmongerDnsMenuFindServices683($primaryNavbar);
    if (!$servicesMenu instanceof MenuItem || !method_exists($servicesMenu, 'addChild')) {
        return;
    }

    $nameCounts = [];
    foreach ($activeDnsServices as $service) {
        $key = domainmongerDnsMenuCleanText683($service['product_name']);
        $nameCounts[$key] = ($nameCounts[$key] ?? 0) + 1;
    }

    $order = 15;
    foreach ($activeDnsServices as $service) {
        $serviceId = (int) $service['id'];
        if ($serviceId <= 0) {
            continue;
        }

        $childName = 'DomainMonger DNS Service ' . $serviceId;
        if (method_exists($servicesMenu, 'getChild') && $servicesMenu->getChild($childName)) {
            continue;
        }

        $label = $service['product_name'];
        $labelKey = domainmongerDnsMenuCleanText683($label);

        // If the client has multiple active services with the same DNS product
        // name, append the service domain to avoid duplicate-looking menu links.
        if (($nameCounts[$labelKey] ?? 0) > 1 && $service['domain'] !== '') {
            $label .= ' - ' . $service['domain'];
        }

        $servicesMenu->addChild($childName, [
            'label' => $label,
            'uri' => 'clientarea.php?action=productdetails&id=' . $serviceId,
            'order' => $order,
        ]);

        $order++;
    }
});
