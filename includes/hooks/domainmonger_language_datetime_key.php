<?php
/**
 * DomainMonger Patch 568 cleanup stub.
 *
 * Patch 567 attempted to inject dateTime language values from a hook, but the
 * labels need to be available through the normal WHMCS language override flow.
 * This file intentionally does nothing and safely neutralizes the failed hook
 * if Patch 567 was installed.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}
