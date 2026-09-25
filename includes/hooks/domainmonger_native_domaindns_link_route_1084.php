<?php
/**
 * DomainMonger cleanup stub.
 *
 * Former client-side Native Domain DNS Link Route 1084.
 * Normal DNS Management is now served by the live ResellerClub DNS feed hook:
 * domainmonger_resellerclub_dns_live_feed_1113.php
 *
 * The direct native-route escape hatch is handled by:
 * domainmonger_resellerclub_dns_native_route_escape_1116.php
 *
 * This file is intentionally inert to prevent duplicate link-rewrite JavaScript
 * while still overwriting any older active 1084 version during install.
 */
if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

// Intentionally no hooks registered.
