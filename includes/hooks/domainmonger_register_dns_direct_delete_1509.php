<?php
/**
 * DomainMonger Register DNS Direct Delete 1509 — disabled by Patch 1510.
 *
 * Patch 1509 did not complete its request after confirmation. Its endpoint and
 * browser behavior are intentionally disabled while Patch 1510 traces the
 * actual request path. This file remains as a harmless replacement so the
 * failed implementation is cleaned up rather than left active.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

// Intentionally no hooks, output, form handling, registrar calls, or database changes.
