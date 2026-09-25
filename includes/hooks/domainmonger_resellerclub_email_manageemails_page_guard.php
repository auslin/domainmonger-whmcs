<?php
/**
 * DomainMonger - Patch 730 rollback cleanup.
 *
 * Earlier email-management pagination guard disabled. Left as a safe no-op so
 * installing this patch overwrites the previous hook file without deleting
 * files manually.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

// No-op by design.
