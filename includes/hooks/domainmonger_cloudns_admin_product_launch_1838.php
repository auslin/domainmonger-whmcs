<?php
/**
 * DomainMonger Patch 1840
 * Product-specific DNSPlus / DNSLite admin launcher.
 *
 * Adds:
 *  - A launch control on the WHMCS Admin Product/Service page for services using
 *    the cloudns server module.
 *  - A compact product-row launch control on the Admin Client Summary page for
 *    each cloudns service belonging to that client. Patch 1840 renders the Summary launcher from AdminAreaFooterOutput so
 *    the Products/Services table exists before the launcher is attached, and
 *    reapplies it after WHMCS server-side DataTable redraws.
 *
 * Launches the exact WHMCS service through WHMCS CreateSsoToken using a
 * short-lived, single-use SSO token and a custom redirect to the existing
 * DNSPlus zone-settings Client Area route. No client password is required and
 * no DNSPlus controller/template behavior is duplicated here.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

if (!function_exists('dm1838_cloudns_service')) {
    function dm1838_cloudns_service(int $serviceId)
    {
        if ($serviceId <= 0) {
            return null;
        }

        try {
            return Capsule::table('tblhosting as h')
                ->join('tblproducts as p', 'p.id', '=', 'h.packageid')
                ->where('h.id', $serviceId)
                ->whereRaw('LOWER(p.servertype) = ?', ['cloudns'])
                ->first([
                    'h.id',
                    'h.userid',
                    'h.domain',
                    'h.domainstatus',
                    'p.name as productname',
                    'p.servertype',
                ]);
        } catch (Throwable $e) {
            return null;
        }
    }
}

if (!function_exists('dm1838_cloudns_services_for_client')) {
    function dm1838_cloudns_services_for_client(int $clientId): array
    {
        if ($clientId <= 0) {
            return [];
        }

        try {
            $rows = Capsule::table('tblhosting as h')
                ->join('tblproducts as p', 'p.id', '=', 'h.packageid')
                ->where('h.userid', $clientId)
                ->whereRaw('LOWER(p.servertype) = ?', ['cloudns'])
                ->orderBy('h.id', 'asc')
                ->get([
                    'h.id',
                    'h.userid',
                    'h.domain',
                    'h.domainstatus',
                    'p.name as productname',
                ]);
        } catch (Throwable $e) {
            return [];
        }

        $services = [];
        foreach ($rows as $row) {
            $services[] = $row;
        }
        return $services;
    }
}

if (!function_exists('dm1838_cloudns_label')) {
    function dm1838_cloudns_label($service): string
    {
        $productName = trim((string) ($service->productname ?? ''));
        if ($productName !== '' && preg_match('/dns\s*lite/i', $productName)) {
            return 'DNSLite';
        }
        return 'DNSPlus';
    }
}

if (!function_exists('dm1838_cloudns_escape')) {
    function dm1838_cloudns_escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('dm1838_cloudns_launch_url')) {
    function dm1838_cloudns_launch_url($service, string $csrf): string
    {
        return 'clientsservices.php?userid=' . (int) ($service->userid ?? 0)
            . '&id=' . (int) ($service->id ?? 0)
            . '&dm1838_cloudns_launch=1'
            . '&token=' . rawurlencode($csrf);
    }
}

if (!function_exists('dm1838_cloudns_set_notice')) {
    function dm1838_cloudns_set_notice(int $serviceId, string $type, string $message): void
    {
        if ($serviceId <= 0 || !isset($_SESSION) || !is_array($_SESSION)) {
            return;
        }

        if (!isset($_SESSION['dm1838_cloudns_launch_notices']) || !is_array($_SESSION['dm1838_cloudns_launch_notices'])) {
            $_SESSION['dm1838_cloudns_launch_notices'] = [];
        }

        $_SESSION['dm1838_cloudns_launch_notices'][$serviceId] = [
            'type' => in_array($type, ['success', 'info', 'warning', 'danger'], true) ? $type : 'info',
            'message' => $message,
        ];
    }
}

if (!function_exists('dm1838_cloudns_take_notice')) {
    function dm1838_cloudns_take_notice(int $serviceId): ?array
    {
        if ($serviceId <= 0 || !isset($_SESSION['dm1838_cloudns_launch_notices'][$serviceId])) {
            return null;
        }

        $notice = $_SESSION['dm1838_cloudns_launch_notices'][$serviceId];
        unset($_SESSION['dm1838_cloudns_launch_notices'][$serviceId]);
        if (empty($_SESSION['dm1838_cloudns_launch_notices'])) {
            unset($_SESSION['dm1838_cloudns_launch_notices']);
        }

        return is_array($notice) ? $notice : null;
    }
}

/*
 * Handle the launch only from an authenticated Admin Product/Service route.
 * WHMCS performs the admin-area authentication/permission checks before this
 * hook runs. We also validate the CSRF token and re-query the service/module.
 */
add_hook('AdminAreaPage', 1838, static function (array $vars): array {
    if ((string) ($_GET['dm1838_cloudns_launch'] ?? '') !== '1') {
        return [];
    }

    $filename = strtolower((string) ($vars['filename'] ?? ''));
    $script = strtolower((string) basename($_SERVER['SCRIPT_NAME'] ?? ''));
    if (!in_array($filename, ['clientsservices', 'clientsservices.php'], true) && $script !== 'clientsservices.php') {
        return [];
    }

    $serviceId = (int) ($_GET['id'] ?? 0);
    $requestedClientId = (int) ($_GET['userid'] ?? 0);
    $service = dm1838_cloudns_service($serviceId);

    if (!$service || (int) ($service->userid ?? 0) <= 0) {
        return [];
    }

    $clientId = (int) $service->userid;
    if ($requestedClientId > 0 && $requestedClientId !== $clientId) {
        dm1838_cloudns_set_notice($serviceId, 'danger', 'DNS launch was blocked because the selected service does not belong to that client.');
        header('Location: clientsservices.php?userid=' . $clientId . '&id=' . $serviceId);
        exit;
    }

    if (function_exists('check_token')) {
        check_token('WHMCS.admin.default');
    }

    if (!function_exists('localAPI')) {
        dm1838_cloudns_set_notice($serviceId, 'danger', 'WHMCS Local API is unavailable, so DNSPlus could not be opened.');
        header('Location: clientsservices.php?userid=' . $clientId . '&id=' . $serviceId);
        exit;
    }

    $redirectPath = 'clientarea.php?action=productdetails&id=' . $serviceId . '&customAction=zone-settings';

    try {
        $result = localAPI('CreateSsoToken', [
            'client_id' => $clientId,
            'destination' => 'sso:custom_redirect',
            'sso_redirect_path' => $redirectPath,
        ]);
    } catch (Throwable $e) {
        $result = [
            'result' => 'error',
            'message' => $e->getMessage(),
        ];
    }

    $redirectUrl = trim((string) ($result['redirect_url'] ?? ''));
    if (strcasecmp((string) ($result['result'] ?? ''), 'success') === 0 && $redirectUrl !== '') {
        header('Location: ' . $redirectUrl);
        exit;
    }

    $message = trim((string) ($result['message'] ?? $result['error'] ?? ''));
    if ($message === '') {
        $message = 'WHMCS could not create the one-time DNSPlus sign-on token.';
    }

    dm1838_cloudns_set_notice($serviceId, 'danger', $message);
    header('Location: clientsservices.php?userid=' . $clientId . '&id=' . $serviceId);
    exit;
});

/* Product/Service page: exact-service launcher. */
add_hook('AdminClientServicesTabFields', 1838, static function (array $vars): array {
    $serviceId = (int) ($vars['id'] ?? 0);
    $service = dm1838_cloudns_service($serviceId);
    if (!$service) {
        return [];
    }

    $csrf = function_exists('generate_token') ? (string) generate_token('plain') : '';
    $label = dm1838_cloudns_label($service);
    $launchUrl = dm1838_cloudns_launch_url($service, $csrf);

    $noticeHtml = '';
    $notice = dm1838_cloudns_take_notice($serviceId);
    if ($notice) {
        $type = in_array((string) ($notice['type'] ?? ''), ['success', 'info', 'warning', 'danger'], true)
            ? (string) $notice['type']
            : 'info';
        $noticeHtml = '<div class="alert alert-' . dm1838_cloudns_escape($type) . '" style="margin:0 0 10px 0;">'
            . dm1838_cloudns_escape((string) ($notice['message'] ?? ''))
            . '</div>';
    }

    $domain = trim((string) ($service->domain ?? ''));
    $detail = $domain !== ''
        ? 'Open DNS management for ' . dm1838_cloudns_escape($domain) . '.'
        : 'Open DNS management for this service.';

    $html = $noticeHtml
        . '<a class="btn btn-sm" href="' . dm1838_cloudns_escape($launchUrl) . '" target="_blank" rel="noopener" '
        . 'style="background:#163a5f;border-color:#163a5f;color:#fff;text-decoration:none;">'
        . 'Open ' . dm1838_cloudns_escape($label)
        . '</a>'
        . '<span style="display:inline-block;margin-left:10px;color:#666;vertical-align:middle;">'
        . $detail
        . '</span>';

    return [
        $label . ' Admin Launch' => $html,
    ];
});

/*
 * Client Summary: product-specific launcher inside the Products/Services table.
 *
 * IMPORTANT (Patch 1840): AdminAreaClientSummaryPage output is rendered near
 * the top of clientssummary.tpl, before #summaryServices exists. Patches
 * 1838/1839 therefore exited before they could attach anything. Render this
 * JavaScript from AdminAreaFooterOutput instead, after the Summary table has
 * been written to the page. WHMCS can still replace tbody rows on DataTable
 * redraws, so reinstall after draw events and tbody mutations.
 */
add_hook('AdminAreaFooterOutput', 1840, static function (array $vars): string {
    $filename = strtolower((string) ($vars['filename'] ?? ''));
    $script = strtolower((string) basename($_SERVER['SCRIPT_NAME'] ?? ''));
    if (!in_array($filename, ['clientssummary', 'clientssummary.php'], true) && $script !== 'clientssummary.php') {
        return '';
    }

    $clientId = (int) ($_GET['userid'] ?? $_POST['userid'] ?? 0);
    $services = dm1838_cloudns_services_for_client($clientId);
    if (empty($services)) {
        return '';
    }

    $csrf = function_exists('generate_token') ? (string) generate_token('plain') : '';
    $items = [];
    foreach ($services as $service) {
        $serviceId = (int) ($service->id ?? 0);
        if ($serviceId <= 0) {
            continue;
        }
        $items[(string) $serviceId] = [
            'label' => dm1838_cloudns_label($service),
            'url' => dm1838_cloudns_launch_url($service, $csrf),
        ];
    }

    if (empty($items)) {
        return '';
    }

    $json = json_encode($items, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    if ($json === false) {
        return '';
    }

    return '<style>'
        . '#summaryServices .dm1838-cloudns-summary-launch{display:inline-block;margin-left:8px;padding:2px 8px;border:1px solid #163a5f;border-radius:3px;background:#163a5f;color:#fff!important;font-size:11px;line-height:18px;text-decoration:none!important;vertical-align:middle;white-space:nowrap;}'
        . '#summaryServices .dm1838-cloudns-summary-launch:hover,#summaryServices .dm1838-cloudns-summary-launch:focus{background:#214e7a;border-color:#214e7a;color:#fff!important;text-decoration:none!important;}'
        . '</style>'
        . '<script>(function(){'
        . 'var items=' . $json . ';'
        . 'function getTable(){return document.getElementById("summaryServices");}'
        . 'function getServiceId(row){'
        . 'if(!row){return "";}'
        . 'var m=(row.id||"").match(/^service(\\d+)$/);if(m){return m[1];}'
        . 'var cb=row.querySelector("input[name=\\"selproducts[]\\"]");if(cb&&cb.value){return String(cb.value);}'
        . 'var links=row.querySelectorAll("a[href*=\\"clientsservices.php\\"]");'
        . 'for(var i=0;i<links.length;i++){try{var u=new URL(links[i].getAttribute("href"),window.location.href);var id=u.searchParams.get("id");if(id){return String(id);}}catch(e){}}'
        . 'return "";'
        . '}'
        . 'function install(){'
        . 'var table=getTable();if(!table){return;}'
        . 'var rows=table.querySelectorAll("tbody tr");'
        . 'for(var i=0;i<rows.length;i++){'
        . 'var row=rows[i],serviceId=getServiceId(row),item=items[serviceId];'
        . 'if(!item||row.querySelector(".dm1838-cloudns-summary-launch[data-service-id=\\""+serviceId+"\\"]")){continue;}'
        . 'var cells=row.querySelectorAll("td");if(cells.length<3){continue;}'
        . 'var productCell=cells[2];'
        . 'var b=document.createElement("a");'
        . 'b.className="dm1838-cloudns-summary-launch";'
        . 'b.setAttribute("data-service-id",serviceId);'
        . 'b.href=item.url;b.target="_blank";b.rel="noopener";'
        . 'b.textContent=item.label;'
        . 'b.title="Open "+item.label+" for this product";'
        . 'productCell.appendChild(b);'
        . '}'
        . '}'
        . 'var table=getTable();if(!table){return;}'
        . 'install();'
        . 'if(window.jQuery){window.jQuery(table).off("draw.dt.dm1840Cloudns").on("draw.dt.dm1840Cloudns",function(){window.setTimeout(install,0);});}'
        . 'var body=table.tBodies&&table.tBodies[0]?table.tBodies[0]:table;'
        . 'if(window.MutationObserver){new MutationObserver(function(){install();}).observe(body,{childList:true,subtree:true});}'
        . 'window.setTimeout(install,100);window.setTimeout(install,500);'
        . '})();</script>';
});
