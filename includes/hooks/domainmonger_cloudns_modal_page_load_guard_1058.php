<?php
/**
 * DomainMonger ClouDNS Modal Page-Load Guard 1058
 *
 * Neutralized in Patch 1059.
 * The 1058 guard/diagnostic helped confirm that the DNS Add modal hook was no
 * longer creating visible modal elements during page load, but it did not stop
 * the black page-load flash. Keep this file intentionally quiet so the failed
 * guard is cleaned up without deleting the file from servers that already have it.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}
