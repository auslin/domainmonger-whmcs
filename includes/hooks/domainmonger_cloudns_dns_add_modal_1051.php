<?php
/**
 * DomainMonger ClouDNS DNS Add Modal 1051 - Neutralized in Patch 1057
 *
 * Patch 1051 made the ClouDNS DNS Records +Add modal work, but introduced a
 * black page-load flash with a white spinner. Patches 1052-1056 did not remove
 * the flash, so this cleanup patch intentionally disables only the DNS Records
 * +Add modal hook and restores the original full-page +Add behavior.
 *
 * Confirmed modals that remain active in separate hook files:
 * - ResellerClub DNS Modify modal / close-on-save
 * - ClouDNS DNS Records pencil/Edit modal
 * - ClouDNS Mail Forwards pencil/Edit modal
 * - ClouDNS Mail Forwards +Add modal
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

// Intentionally no hooks in this file.
// Leaving this neutralized file in place prevents older 1051-1056 DNS Add
// modal code from continuing to run if the file already exists on the server.
