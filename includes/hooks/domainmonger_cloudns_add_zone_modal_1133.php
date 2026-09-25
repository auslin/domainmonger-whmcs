<?php
/**
 * DomainMonger ClouDNS Add Zone — Full Page Workflow
 * Patch 1594
 *
 * The former hook opened the standard Add DNS Zone form in a modal while the
 * bulk-zone tools opened as full pages. That produced a mixed workflow and
 * caused the Action selector to behave differently depending on how the user
 * entered the Add Zone area.
 *
 * The modal is intentionally disabled. The existing + Add Zone link and every
 * zone-tool route now use the native ClouDNS full-page interface consistently.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

// Intentionally no ClientAreaFooterOutput hook.
// Keep this file in place so the prior modal implementation is cleanly removed
// by an Install Only patch without requiring a separate file-deletion step.
