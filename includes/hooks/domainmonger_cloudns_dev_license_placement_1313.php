<?php
/**
 * DomainMonger ClouDNS development-license placement cleanup.
 *
 * Patch 1317 keeps the former Patch 1313 filename as a no-op so the failed
 * below-module-menu relocation remains disabled. The corrected placement is
 * handled by the separate 1317 hook.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}
