<?php
/**
 * DomainMonger dashboard grey button cleanup hook.
 *
 * Patch 409 cleanup
 * - Patch 407/408 dashboard-only button CSS has been superseded by the
 *   component-level global button palette hook:
 *   domainmonger_global_button_palette.php
 * - This no-op file prevents duplicate dashboard-only button overrides when
 *   Patch 407 or 408 was previously installed.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}
