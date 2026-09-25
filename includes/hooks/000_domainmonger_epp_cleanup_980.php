<?php
/**
 * DomainMonger EPP failed-patch cleanup — Patch 980.
 * Removes the failed Get EPP child template override from 973/974 if it still exists.
 * Leaves confirmed routing and loader-flash fixes to their restored hook files.
 */
if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaPage', 1, function () {
    $root = dirname(__DIR__); // /manage/includes -> /manage
    $paths = [
        $root . '/templates/stellar-software-integration-whmcs/clientareadomaingetepp.tpl',
    ];

    foreach ($paths as $path) {
        if (is_file($path) && is_writable($path)) {
            @unlink($path);
        }
    }

    return [];
});
