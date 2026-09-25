<?php
/**
 * Patch 1580 cleanup: Patch 1579 is disabled.
 * Patch 1580 retains the confirmed indexed changed-row transport, adds safe
 * timeout verification/retry handling, and delays the green browser success
 * message until the freshly reloaded live zone contains every requested value.
 */
if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}
