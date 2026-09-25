<?php
/**
 * DomainMonger native DNS Development License placement — disabled.
 *
 * Patch 1355 emergency revert
 *
 * Patch 1354 caused the converted native DNS page to remain white with a
 * spinner. Keep this filename as a harmless no-op so an Install Only patch can
 * safely overwrite and disable the failed hook without requiring file deletion.
 *
 * The confirmed Patch 1353 placement and all other DNS functionality remain
 * unchanged.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

// Intentionally no hooks.
