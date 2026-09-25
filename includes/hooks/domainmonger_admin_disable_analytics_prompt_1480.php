<?php
/**
 * DomainMonger WHMCS admin analytics tracking popup suppression.
 *
 * Patch 1481 emergency cleanup:
 * - Neutralizes Patch 1480 after its MutationObserver caused a self-triggering
 *   admin-page loop and persistent loading spinner.
 * - Removes the automatic /mixpanel/config/set request.
 * - Intentionally registers no hooks.
 *
 * Keep this file as a safe no-op so Install Only extraction cleanly replaces
 * the failed hook without requiring a separate file-deletion operation.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}
