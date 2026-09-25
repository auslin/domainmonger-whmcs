<?php
/**
 * Patch 1578 cleanup: Patch 1577's external submit interceptor is disabled.
 * Direct existing-record updates are now initiated by the confirmed live-feed
 * form handler in domainmonger_resellerclub_dns_live_feed_1113.php.
 */
if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}
