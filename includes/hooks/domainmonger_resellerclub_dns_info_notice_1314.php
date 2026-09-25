<?php
/**
 * Patch 1315 cleanup.
 *
 * Patch 1314 targeted markup that the live DNS feed had already replaced.
 * Its output is intentionally disabled; the corrected classification now
 * lives narrowly in domainmonger_resellerclub_dns_live_feed_1113.php.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}
