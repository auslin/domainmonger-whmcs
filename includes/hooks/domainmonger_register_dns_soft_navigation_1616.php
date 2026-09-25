<?php
/**
 * Patch 1617 rollback placeholder.
 *
 * Patch 1616's Register DNS soft-navigation hook was disabled because it could
 * leave Register DNS pages hanging. This intentionally registers no hooks and
 * preserves normal WHMCS full-page navigation.
 */

defined('WHMCS') || exit;
