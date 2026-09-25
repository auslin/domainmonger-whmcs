<?php
/**
 * Draftify - hooks
 * Adds the button in admin invoice controls (AdminInvoicesControlsOutput).
 *
 * NOTE:
 * Some WHMCS admin setups enforce CSP that blocks inline onclick handlers.
 * We therefore load an external JS file via <script src="..."></script>.
 * To guarantee it loads whenever the button is rendered, we output the script tag
 * from the same hook (no dependency on AdminAreaHeadOutput detection).
 */

if (!defined('WHMCS')) {
    exit('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

add_hook('AdminInvoicesControlsOutput', 1, function ($vars) {
    if (empty($_SESSION['adminid'])) {
        return '';
    }

    $invoiceId = 0;
    if (isset($vars['invoiceid'])) {
        $invoiceId = (int) $vars['invoiceid'];
    } elseif (isset($vars['id'])) {
        $invoiceId = (int) $vars['id'];
    } elseif (isset($_GET['id'])) {
        $invoiceId = (int) $_GET['id'];
    }

    if ($invoiceId <= 0) {
        return '';
    }

    $confirmText = 'Mark this invoice as Draft? This is a workaround to unlock editing. Use with care.';
    try {
        $row = Capsule::table('tbladdonmodules')
            ->where('module', 'draftify')
            ->where('setting', 'confirmText')
            ->first();
        if ($row && isset($row->value) && trim((string)$row->value) !== '') {
            $confirmText = (string)$row->value;
        }
    } catch (\Throwable $e) {
        // ignore
    }

    $confirmAttr = htmlspecialchars($confirmText, ENT_QUOTES, 'UTF-8');

    // Pathing:
    // In WHMCS, admin pages are under /admin/ and modules are under root /modules/.
    // Therefore we use ../modules/... to reliably resolve from admin context.
    $v = '1.1.1';
    $scriptSrc = '../modules/addons/draftify/assets/draftify.js?v=' . rawurlencode($v);

    // Only output loader once per page.
    $loader = '';
    if (!defined('DRAFTIFY_JS_LOADED')) {
        define('DRAFTIFY_JS_LOADED', true);
        $loader = '<script src="' . htmlspecialchars($scriptSrc, ENT_QUOTES, 'UTF-8') . '"></script>';
    }

    return $loader . '
        <button
            type="button"
            class="btn btn-warning"
            id="draftifyMarkDraftBtn"
            data-invoiceid="' . (int)$invoiceId . '"
            data-confirm="' . $confirmAttr . '"
        >
            <i class="fas fa-pencil-alt"></i> Mark as Draft
        </button>
    ';
});
