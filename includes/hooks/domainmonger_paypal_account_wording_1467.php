<?php
/**
 * DomainMonger Patch 1468 cleanup
 *
 * Patch 1467 attempted to change PayPal checkout wording through client-area
 * language variables. PayPal Payments renders this panel after those hooks, so
 * the override did not reach the active markup. The failed override is
 * intentionally neutralized here. Patch 1468 handles only the rendered PayPal
 * checkout panel in a separate page-specific hook.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}
