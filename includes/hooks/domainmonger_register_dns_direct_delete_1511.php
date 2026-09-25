<?php
/**
 * Patch 1512 cleanup stub.
 *
 * Patch 1511 used a JSON POST field for selected DNS rows. On the active
 * WHMCS request path that field arrived empty or undecodable, so the handler
 * rejected the request before contacting the registrar. Patch 1512 replaces
 * it with ordinary indexed form fields. This file intentionally registers no
 * hooks or endpoints and prevents the failed 1511 JavaScript from loading.
 */
if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}
