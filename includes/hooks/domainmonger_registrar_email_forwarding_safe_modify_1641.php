<?php
/**
 * Retired by DomainMonger Patch 1647.
 *
 * Patches 1641-1646 used this same pathname. Keeping a small inert file here
 * removes those failed handlers on servers that reload hook files normally.
 * Patch 1647 also suppresses any stale OPcache copy from a new hook filename.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}
