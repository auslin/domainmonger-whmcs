<?php
/**
 * Cleanup for failed DomainMonger EPP client-side tool attempts.
 *
 * Patches 717-722 were not confirmed and produced registrar order-id errors.
 * This file intentionally registers no hooks so the failed custom EPP actions
 * stop running while the page falls back to normal WHMCS registrar behavior.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

return;
