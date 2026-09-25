<?php
/**
 * DomainMonger Patch 1652 cleanup stub.
 *
 * Patch 1651's direct registrar API and rebuild fallback are intentionally
 * disabled. Patch 1652 delegates exact destination additions/removals to the
 * installed ResellerClub Email Management module instead.
 */
if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}
