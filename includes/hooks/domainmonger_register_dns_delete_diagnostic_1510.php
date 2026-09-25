<?php
/**
 * DomainMonger Register DNS Delete Diagnostic 1510 — disabled by Patch 1511.
 *
 * Patch 1510 confirmed that the browser request, session, domain lookup, and
 * page-specific JSON endpoint all worked. Patch 1511 replaces the diagnostic
 * with the verified direct-delete workflow. This cleanup stub intentionally
 * registers no hooks and performs no request handling.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

// Intentionally inactive after Patch 1511.
