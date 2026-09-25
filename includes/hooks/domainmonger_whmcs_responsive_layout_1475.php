<?php
/**
 * DomainMonger Patch 1476 cleanup
 *
 * Patch 1475's broad sitewide responsive rules did not address the actual
 * Renew Domains row overflow and are intentionally disabled. The replacement
 * is scoped to the active Renew Domains stylesheet/template.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}
