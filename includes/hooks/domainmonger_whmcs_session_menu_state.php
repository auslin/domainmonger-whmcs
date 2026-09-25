<?php
/**
 * DomainMonger Patch 464
 * Cleanup for failed/no-change Patch 463 menu cache-buster work.
 *
 * This file intentionally neutralizes the previously installed
 * domainmonger_whmcs_session_menu_state.php hook used by failed Patches
 * 457, 458, 459, 461, 462, and 463.
 *
 * Leave this file in place when installing via ZIP because ZIP extraction
 * will not delete an existing hook file. This no-op replacement prevents
 * the failed menu/session/cache-buster logic from continuing to run.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

// No hooks registered. Cleanup-only neutralizer.
