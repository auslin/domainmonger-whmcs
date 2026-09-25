<?php
/**
 * DomainMonger WHMCS normal-URL menu diagnostic cleanup.
 *
 * Patch 516
 * - Keeps the failed Patch 514 diagnostic disabled while reverting Patch 515.
 * - Kept as a neutralized file so installing this patch safely cleans up the
 *   previous diagnostic hook without requiring file deletion support from ZIP install.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

// Patch 514 diagnostic intentionally disabled.
