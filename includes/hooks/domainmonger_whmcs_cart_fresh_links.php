<?php
/**
 * DomainMonger WHMCS public-cart fresh link guard cleanup.
 *
 * Patch 516
 * - Reverts/neutralizes Patch 515 after it produced no change.
 * - Intentionally disables the cart fresh-link redirect/link rewriting behavior.
 * - Kept as a neutralized file so installing this patch safely cleans up the
 *   previously-added hook without requiring file deletion support from ZIP install.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

// Patch 515 fresh-link behavior intentionally disabled.
