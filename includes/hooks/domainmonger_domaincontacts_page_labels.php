<?php
/**
 * DomainMonger cleanup for old WHOIS-only page label hook.
 *
 * Patch 810:
 * The one-page Patch 809 label behavior has been replaced by the
 * domain-management-wide hook:
 * domainmonger_domainmanagement_page_labels.php
 *
 * This file intentionally registers no hooks.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}
