<?php
/**
 * DomainMonger WHMCS v9
 * Patch 591 cleanup stub.
 *
 * The failed hook-based Change Password Generate Password alignment attempts
 * from patches 585-590 are intentionally disabled here. The confirmed target
 * has been moved to the page-specific user-password.tpl + v45 CSS patch.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

// Intentionally no hooks.
