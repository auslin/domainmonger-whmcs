<?php
/**
 * Patch 1579 cleanup: Patch 1578 is disabled.
 * Its JSON changed-row transport reached the handler without the row payload
 * on this installation. Patch 1579 uses explicit indexed form fields instead.
 */
if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}
