<?php
/**
 * DomainMonger WHMCS v9 - DNS Native Route Redirect Disabled 1216
 *
 * Cleanup after failed Patch 1209 native-route conversion.
 *
 * The normal ResellerClub DNS Records page must remain on:
 *   dnsmanagement.php?action=managednszone
 *
 * The working DNS live-feed hook loads the native WHMCS DNS form into that
 * existing page. This file intentionally registers no redirect and emits no
 * JavaScript so the old DNS URL cannot be sent to clientarea.php?action=domaindns.
 *
 * Scope:
 * - No language changes.
 * - No integration-folder changes.
 * - No registrar/backend changes.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}
