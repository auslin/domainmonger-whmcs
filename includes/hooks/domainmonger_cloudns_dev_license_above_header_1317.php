<?php
/**
 * DomainMonger ClouDNS development-license placement cleanup.
 *
 * Patch 1318 disables the ineffective Patch 1317 relocation while the native
 * WHMCS notice position is reviewed separately. No DOM movement is performed.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}
