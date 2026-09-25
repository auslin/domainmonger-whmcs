<?php

declare(strict_types=1);

use InvoiceFix\Module\InvoiceFixService;
use InvoiceFix\Module\InvoiceFixText;
use InvoiceFix\Module\InvoiceFixUpgrade;
use WHMCS\Database\Capsule;

if (!defined('WHMCS')) {
    exit('This file cannot be accessed directly');
}

require_once __DIR__ . '/lib/InvoiceFixService.php';
require_once __DIR__ . '/lib/InvoiceFixUpgrade.php';

add_hook('AdminInvoicesControlsOutput', 1, function (array $vars): string {
    $invoiceId = (int) ($vars['invoiceid'] ?? $vars['id'] ?? $_GET['id'] ?? 0);
    if ($invoiceId <= 0 || empty($_SESSION['adminid'])) {
        return '';
    }

    try {
        InvoiceFixUpgrade::ensureCurrent();
        $admin = InvoiceFixService::adminContext();
        if (!InvoiceFixService::roleHasModuleAccess((int) $admin['role_id'])) {
            return '';
        }
        if (!InvoiceFixService::hasRequiredInvoicePermissions($admin['permissions'])) {
            return '';
        }

        $invoice = Capsule::table('tblinvoices')->where('id', $invoiceId)->first();
        if (!$invoice) {
            return '';
        }

        $status = trim((string) ($invoice->status ?? ''));
        $showLinkedNotice = InvoiceFixService::moduleSettingEnabled('show_linked_invoice_notice', true);
        $noticeStyle = strtolower(trim(InvoiceFixService::moduleSetting('linked_invoice_notice_style', 'Compact')));
        if (!in_array($noticeStyle, ['compact', 'standard'], true)) {
            $noticeStyle = 'compact';
        }

        $sourceLog = InvoiceFixService::sourceForDraft($invoiceId);
        if ($sourceLog) {
            if (!$showLinkedNotice) {
                return '';
            }

            $sourceId = (int) $sourceLog->source_invoice_id;
            $sourceStatus = (string) (Capsule::table('tblinvoices')->where('id', $sourceId)->value('status') ?? InvoiceFixText::get('admin_status_unknown'));

            return invoicefix_hook_assets($vars)
                . invoicefix_linked_notice(
                    InvoiceFixText::get('linked_original_label'),
                    $sourceId,
                    $sourceStatus,
                    $noticeStyle,
                    InvoiceFixText::get('linked_original_guidance')
                );
        }

        if (strcasecmp($status, 'Draft') === 0) {
            return '';
        }

        $replacement = InvoiceFixService::replacementForSource($invoiceId);
        if ($replacement !== null) {
            if (!$showLinkedNotice) {
                return invoicefix_hook_assets($vars)
                    . '<div class="alert alert-warning invoicefix-linked-notice">'
                    . '<strong>' . invoicefix_hook_escape(InvoiceFixText::get('linked_notice_prefix')) . '</strong> '
                    . invoicefix_hook_escape(InvoiceFixText::get('linked_replacement_exists_hidden'))
                    . '</div>';
            }

            return invoicefix_hook_assets($vars)
                . invoicefix_linked_notice(
                    InvoiceFixText::get('linked_replacement_label'),
                    (int) $replacement['invoice_id'],
                    (string) $replacement['status'],
                    $noticeStyle,
                    InvoiceFixText::get('linked_replacement_guidance')
                );
        }

        $token = function_exists('generate_token') ? (string) generate_token('plain') : '';
        $confirm = InvoiceFixText::get('confirm_create_draft', ['invoice_id' => $invoiceId]);

        return invoicefix_hook_assets($vars)
            . '<button type="button" class="btn btn-primary invoicefix-create-btn" id="invoicefixCreateDraftBtn"'
            . ' data-invoiceid="' . $invoiceId . '"'
            . ' data-token="' . invoicefix_hook_escape($token) . '"'
            . ' data-confirm="' . invoicefix_hook_escape($confirm) . '">'
            . '<i class="fas fa-copy" aria-hidden="true"></i> '
            . invoicefix_hook_escape(InvoiceFixText::get('button_create_draft'))
            . '</button>';
    } catch (Throwable $e) {
        return '';
    }
});

function invoicefix_linked_notice(
    string $label,
    int $linkedInvoiceId,
    string $linkedStatus,
    string $style,
    string $guidance
): string {
    $link = '<a href="invoices.php?action=edit&id=' . $linkedInvoiceId . '">#' . $linkedInvoiceId . '</a>';
    $status = trim($linkedStatus) !== '' ? ' <span class="text-muted">(' . invoicefix_hook_escape($linkedStatus) . ')</span>' : '';

    $html = '<div class="alert alert-info invoicefix-linked-notice">'
        . '<strong>' . invoicefix_hook_escape(InvoiceFixText::get('linked_notice_prefix')) . '</strong> '
        . invoicefix_hook_escape($label) . ' ' . $link . $status;

    if ($style === 'standard') {
        $html .= '<br><span class="invoicefix-guidance">' . invoicefix_hook_escape($guidance) . '</span>';
    }

    return $html . '</div>';
}

function invoicefix_hook_assets(array $vars): string
{
    static $loaded = false;
    if ($loaded) {
        return '';
    }
    $loaded = true;

    $webRoot = rtrim((string) ($vars['WEB_ROOT'] ?? ''), '/');
    $scriptUrl = $webRoot !== ''
        ? $webRoot . '/modules/addons/invoicefix/assets/invoicefix.js?v=' . InvoiceFixUpgrade::MODULE_VERSION
        : '../modules/addons/invoicefix/assets/invoicefix.js?v=' . InvoiceFixUpgrade::MODULE_VERSION;

    $javascriptText = InvoiceFixText::subset([
        'button_creating_draft',
        'js_request_failed',
        'js_invalid_response',
        'js_ajax_unavailable',
        'js_default_confirmation',
        'js_missing_invoice_id',
        'js_missing_token',
        'js_unknown_error',
        'js_error_prefix',
    ]);

    $json = json_encode(
        $javascriptText,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    );
    if ($json === false) {
        $json = '{}';
    }

    return '<style>
        .invoicefix-create-btn{font-weight:600;margin-top:10px;margin-bottom:12px}
        .invoicefix-linked-notice{margin:10px 0;line-height:1.45}
        .invoicefix-linked-notice a{font-weight:600}
        .invoicefix-guidance{display:inline-block;margin-top:4px}
    </style>'
        . '<script>window.InvoiceFixText=' . $json . ';</script>'
        . '<script src="' . invoicefix_hook_escape($scriptUrl) . '"></script>';
}

function invoicefix_hook_escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
