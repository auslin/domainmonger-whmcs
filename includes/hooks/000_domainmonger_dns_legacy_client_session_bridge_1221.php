<?php
/**
 * DomainMonger WHMCS v9 - legacy DNS client-session compatibility bridge.
 *
 * The encoded ResellerClub DNS endpoint predates WHMCS's current
 * clientareauserid session key and may still require uid/clientid.
 * Scope is intentionally limited to dnsmanagement.php.
 */
if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

$dm1221Script = strtolower((string) basename($_SERVER['SCRIPT_NAME'] ?? ''));
$dm1221Uri = strtolower((string) ($_SERVER['REQUEST_URI'] ?? ''));

if ($dm1221Script !== 'dnsmanagement.php' && strpos($dm1221Uri, '/dnsmanagement.php') === false) {
    return;
}

if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
    @session_start();
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    return;
}

$dm1221ActiveClientId = (int) (
    $_SESSION['clientareauserid']
    ?? $_SESSION['clientid']
    ?? $_SESSION['uid']
    ?? 0
);

if ($dm1221ActiveClientId <= 0) {
    return;
}

// Populate only missing legacy aliases. Never replace an existing identity.
if (empty($_SESSION['uid'])) {
    $_SESSION['uid'] = $dm1221ActiveClientId;
}
if (empty($_SESSION['clientid'])) {
    $_SESSION['clientid'] = $dm1221ActiveClientId;
}
