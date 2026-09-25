<?php
/**
 * DomainMonger Patch 1877 cleanup.
 *
 * Patch 1876 attempted to enable native sorting on the Services column of
 * admin/clients.php. WHMCS does not expose service count as a supported
 * server-side client-list sort field, so the attempted client-side toggle is
 * intentionally disabled here rather than leaving a misleading/incomplete
 * sort control in place.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}
