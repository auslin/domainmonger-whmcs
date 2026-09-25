<?php
/**
 * DomainMonger WHMCS v9 - retired native DNS UI 1117.
 *
 * Patch 1231 promotes the confirmed 1113 native-route interface to the default
 * DNS Records page. This older converter is intentionally disabled so it cannot
 * compete with, duplicate, or restyle the confirmed interface.
 *
 * Raw native troubleshooting remains available with either:
 *   &dmplainnative=1
 *   &dmnativeold=1
 */
if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

return;
