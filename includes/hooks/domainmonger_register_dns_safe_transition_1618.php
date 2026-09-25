<?php
/**
 * Patch 1618 disabled by Patch 1619.
 *
 * The transition/prefetch pilot was safe but did not produce a visibly smooth
 * Register DNS navigation. It is intentionally left inert so Patch 1619 can
 * use native browser prerendering without overlapping navigation experiments.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}
