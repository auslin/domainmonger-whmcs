<?php
/**
 * DomainMonger native DNS Development License diagnostic cleanup.
 *
 * Patch 1357 retires the opt-in Patch 1356 diagnostic after it confirmed the
 * stable DOM order. No diagnostic output or DOM monitoring remains active.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}
