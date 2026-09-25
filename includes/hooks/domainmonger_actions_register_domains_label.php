<?php
/**
 * DomainMonger - Actions menu wording cleanup
 *
 * Changes the WHMCS Actions menu item label from
 * "Register a New Domains" / "Register a New Domain" to "Register Domains".
 *
 * Install path:
 * public_html/manage/includes/hooks/domainmonger_actions_register_domains_label.php
 */

use WHMCS\View\Menu\Item as MenuItem;

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('domainmonger_actions_register_domains_label_walk_692')) {
    function domainmonger_actions_register_domains_label_walk_692(MenuItem $item)
    {
        foreach ($item->getChildren() as $child) {
            if (!$child instanceof MenuItem) {
                continue;
            }

            $label = trim((string) $child->getLabel());
            $name = trim((string) $child->getName());

            $labelNormalized = preg_replace('/\s+/', ' ', strtolower($label));
            $nameNormalized = preg_replace('/\s+/', ' ', strtolower($name));

            $isRegisterDomainAction = in_array($labelNormalized, array(
                'register a new domains',
                'register a new domain',
                'register new domains',
                'register new domain',
            ), true);

            if (!$isRegisterDomainAction && strpos($nameNormalized, 'register') !== false && strpos($nameNormalized, 'domain') !== false) {
                $isRegisterDomainAction = true;
            }

            if ($isRegisterDomainAction) {
                $child->setLabel('Register Domains');
            }

            domainmonger_actions_register_domains_label_walk_692($child);
        }
    }
}

add_hook('ClientAreaPrimarySidebar', 90, function (MenuItem $primarySidebar) {
    domainmonger_actions_register_domains_label_walk_692($primarySidebar);
});

add_hook('ClientAreaSecondarySidebar', 90, function (MenuItem $secondarySidebar) {
    domainmonger_actions_register_domains_label_walk_692($secondarySidebar);
});
