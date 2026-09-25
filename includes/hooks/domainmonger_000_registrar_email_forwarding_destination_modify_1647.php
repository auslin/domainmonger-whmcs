<?php
/**
 * DomainMonger Patch 1648 cleanup stub.
 *
 * Patch 1647 merged the individual account response with users/search.json.
 * The broader search index can lag after a forward deletion and falsely show
 * the removed destination. The active replacement is Patch 1648 in the new
 * hook filename. This stub intentionally registers no hooks or request logic.
 */
if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}
