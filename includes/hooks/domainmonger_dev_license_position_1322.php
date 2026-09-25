<?php
/**
 * DomainMonger Development License notice placement.
 *
 * Patch 1402
 *
 * The production site no longer displays the WHMCS development-license
 * warning that Patch 1322 was created to reposition. The old footer-output
 * JavaScript was being rendered as visible page text after the production
 * migration, so this legacy hook is intentionally disabled.
 *
 * Keeping this inert file in place safely replaces the previous hook through
 * an Install Only ZIP without requiring file deletion during installation.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

// Intentionally no hooks or output on the production site.
