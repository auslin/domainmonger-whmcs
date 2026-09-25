<?php
/**
 * DomainMonger Register DNS Patch 1496 disabled by emergency rollback Patch 1497.
 *
 * Patch 1496 caused a repeating DOM mutation cycle that left the WHMCS content
 * area blank and continuously spinning. This no-output stub restores the last
 * uploaded working control layer while preserving all underlying DNS records,
 * pagination, filtering, Add Record, TTL, and delete functionality.
 *
 * No database changes.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}
