<?php

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

/**
 * Advance the Expiring Domain Tracker nightly NEO refresh queue after each
 * WHMCS cron invocation. The addon itself enforces the configured start hour,
 * one-run-per-day guard, and 50-domain batch size.
 */
add_hook('AfterCronJob', 1, function ($vars) {
    try {
        require_once __DIR__ . '/expiringdomaintracker.php';

        if (!function_exists('edt_run_nightly_neo_refresh')) {
            if (function_exists('logActivity')) {
                logActivity('Expiring Domain Tracker nightly NEO refresh hook could not find edt_run_nightly_neo_refresh().');
            }
            return;
        }

        edt_run_nightly_neo_refresh();
    } catch (Throwable $e) {
        if (function_exists('logActivity')) {
            logActivity('Expiring Domain Tracker nightly NEO refresh hook failed: ' . $e->getMessage());
        }
    }
});
