<?php
/**
 * DomainMonger Patch 340
 * Submit Ticket page-only spacing polish.
 *
 * This hook intentionally avoids global template edits. It only outputs CSS
 * when WHMCS is rendering submitticket.php.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaHeadOutput', 1, function (array $vars): string {
    $filename = (string)($vars['filename'] ?? '');
    $script = (string)($_SERVER['SCRIPT_NAME'] ?? '');
    $phpSelf = (string)($_SERVER['PHP_SELF'] ?? '');

    $isSubmitTicket = $filename === 'submitticket'
        || substr($script, -16) === '/submitticket.php'
        || substr($phpSelf, -16) === '/submitticket.php';

    if (!$isSubmitTicket) {
        return '';
    }

    return <<<'HTML'
<style id="dm-submit-ticket-spacing-340">
/* DomainMonger Patch 340: Submit Ticket page-only attachment/action spacing. */
body.whmcsbody #main-body .attachment-group {
    align-items: stretch;
}

body.whmcsbody #main-body .attachment-group .custom-file {
    min-width: 0;
}

body.whmcsbody #main-body .attachment-group .input-group-append {
    margin-left: 8px;
}

body.whmcsbody #main-body .attachment-group .input-group-append .btn,
body.whmcsbody #main-body .attachment-group .btn {
    min-height: 36px;
    border-radius: 3px !important;
    white-space: nowrap;
}

body.whmcsbody #main-body .attachment-group .custom-file-label {
    min-height: 36px;
    line-height: 1.5;
    border-radius: 3px !important;
}

body.whmcsbody #main-body .attachment-group .custom-file-label::after {
    min-height: 34px;
    line-height: 1.5;
}

body.whmcsbody #main-body #openTicketSubmit + .btn,
body.whmcsbody #main-body p.text-center .btn + .btn {
    margin-left: 8px;
}

@media (max-width: 575.98px) {
    body.whmcsbody #main-body .attachment-group {
        display: block;
    }

    body.whmcsbody #main-body .attachment-group .custom-file,
    body.whmcsbody #main-body .attachment-group .input-group-append,
    body.whmcsbody #main-body .attachment-group .input-group-append .btn {
        display: block;
        width: 100%;
    }

    body.whmcsbody #main-body .attachment-group .input-group-append {
        margin-left: 0;
        margin-top: 8px;
    }
}
</style>
HTML;
});
