<?php
/**
 * DomainMonger WHMCS v9
 * Patch 595 cleanup: 2FA state/action controls are now handled in
 * templates/stellar-software-integration-whmcs/user-security.tpl and
 * css/dm-whmcs-security-settings-v46.css.
 *
 * This file intentionally outputs nothing. It replaces the earlier 2FA hook
 * that used broad CSS/JS and could reveal both enabled/disabled indicators.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}
