<?php
/**
 * DomainMonger Patch 1650 cleanup stub.
 *
 * Patch 1649's complete-list snapshot comparison has been replaced by Patch
 * 1650's explicit destination-delta handler. This file intentionally registers
 * no hooks so cached or previously installed 1649 logic cannot compete.
 */
if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}
