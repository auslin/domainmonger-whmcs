<?php
/**
 * DomainMonger WHMCS logged-in menu guard cleanup.
 *
 * Patch 511
 * - Neutralizes the failed Patch 510 menu guard attempt.
 * - Kept as a safe no-op so installing this patch overwrites the failed hook file.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}
