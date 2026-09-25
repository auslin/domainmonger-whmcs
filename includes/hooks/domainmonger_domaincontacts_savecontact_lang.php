<?php
/**
 * DomainMonger cleanup for old domain contact save fallback hook.
 *
 * Patch 795:
 * The real message is now handled through:
 * public_html/manage/lang/overrides/english.php
 *
 * This file intentionally does not register hooks. It overwrites the failed
 * Patch 789/790 fallback hook if that file exists on the server, preventing
 * the old generic text from overriding the language file.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}
