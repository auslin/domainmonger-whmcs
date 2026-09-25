<?php
/**
 * DomainMonger EPP/Auth Code endpoint.
 *
 * Patch 747: dedicated POST target for client EPP/Auth code updates so
 * WHMCS clientarea/domain-list forms cannot hijack the submit.
 */

require_once __DIR__ . '/init.php';

$hookFile = __DIR__ . '/includes/hooks/domainmonger_epp_authcode_manager.php';
if (is_file($hookFile)) {
    require_once $hookFile;
}

if (!function_exists('dm_epp_flash') || !function_exists('dm_epp_process_authcode_request') || !function_exists('dm_epp_redirect_to_page')) {
    header('Location: clientarea.php?action=domains', true, 303);
    exit;
}

$domainId = (int) ($_POST['domainid'] ?? $_REQUEST['domainid'] ?? 0);

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    dm_epp_redirect_to_page($domainId);
}

[$ok, $message] = dm_epp_process_authcode_request(
    $domainId,
    (string) ($_POST['dm_epp_action'] ?? ''),
    (string) ($_POST['dm_epp_token'] ?? ''),
    (string) ($_POST['dm_epp_custom_code'] ?? '')
);

dm_epp_flash($ok ? 'success' : 'error', $message);
dm_epp_redirect_to_page($domainId);
