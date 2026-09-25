<?php
/**
 * DomainMonger WHMCS v9 - ResellerClub DNS Live Feed 1113
 *
 * Purpose:
 * - Feeds the fast WHMCS native DNS form/table into the converted ResellerClub
 *   dnsmanagement.php page without redirecting away from the old URL.
 * - Patch 1113: moves the active live feed into a clearly named hook file.
 * - Keeps the confirmed 1109 live-route behavior and 1111 status-badge UI.
 * - Patch 1118: makes hidden fallback/debug link labels user-friendly.
 * - Patch 1122: clarifies toolbar items as non-clickable status badges.
 * - Patch 1124: keeps feed status messages from stacking after repeated saves.
 * - Patch 1125: adds a clearer save/loading state without changing backend behavior.
 * - Patch 1127: cleans up save-result messages so success text is clearer.
 * - Patch 1129: makes the record-count badge count saved records more accurately.
 * - Patch 1139: adds record-type coverage, TTL column support, row checkboxes,
 *   type filtering, sorting, and frontend bulk delete/TTL helpers.
 * - Patch 1140: fixes duplicate TTL header detection and keeps the bulk Apply
 *   button inside the bulk toolbar instead of the bottom form action row.
 * - Patch 1141: makes the bulk Apply button orange/muted-orange and aligns
 *   it cleanly with the toolbar controls.
 * - Patch 1145: fixes sortable checkbox-column toggle behavior and record-type
 *   sorting by using explicit field-aware sort values.
 * - Patch 1161: removes the obsolete DNS feed status-pill toolbar from the
 *   normal live editor while preserving explicit diagnostic fallback links.
 * - Patch 1207: forces the live Select All control to render white while
 *   unchecked by applying the state directly to the generated checkbox.
 * - Patch 1223: starts the existing native DNS request from the page head,
 *   releases the completed page session lock at that late render stage, and
 *   reuses the request when the converted DNS shell is ready. No routes or
 *   registrar/backend behavior are changed.
 * - Patch 1226: parses the prefetched response as soon as it arrives and mounts
 *   the converted DNS panel immediately when the target exists, removing the
 *   extra foreground parse and initial 100 ms polling delay.
 * - Patch 1228: adds an opt-in staging route that enhances the native WHMCS DNS
 *   form already on the current page.
 * - Patch 1231: promotes the confirmed native route to the default DNS Records
 * - Patch 1315: classifies the standard DNS guidance text as informational
 *   instead of success, preserving real save-success and error states.
 *   destination. The old dnsmanagement.php route remains available as a
 *   rollback/fallback and continues using the confirmed 1226 live-feed path.
 * - Patch 1234/1235: initial white table-body styling.
 * - Patch 1473: orders the bulk controls as Apply, Choose Action, TTL, Records.
 * - Patch 1474: makes Delete Selected run immediately from Apply after a
 *   confirmation prompt, instead of staging deletion for Save Changes.
 * - Patch 1488: keeps the Records badge synchronized with the active record-
 *   type filter, while All Records continues to show the complete saved total.
 * - Patch 1489: adds filter-aware client-side pagination with a default of
 *   25 records per page and selectable 25, 50, or All page sizes.
 * - Patch 1509: deletes selected records through the exact LogicBoxes
 *   type/host/value API endpoint. It no longer blanks table rows or uses
 *   the unreliable native SaveDNS delete path. Add/Edit stay unchanged.
 * - Patch 1513: restores sortable headers without physically moving form
 *   rows. Pagination follows the sorted view while Save/Delete retain the
 *   original registrar-ID and parallel-array order.
 * - Patch 1514: preserves each domain's current sort column/direction, record-
 *   type filter, and 25/50/All page-size selection across Add, Save, and Delete
 *   reloads by storing only those view preferences in the browser session.
 * - Patch 1515: reapplies a restored visual sort after the DNS table finishes
 *   its initial layout, so the saved order is visible immediately after reload
 *   instead of waiting for a row checkbox or field change to refresh it.
 * - Patch 1517: expands the Add Record popup into a row-based bulk editor
 *   with Add Another, Duplicate, and Remove controls. Each popup row is copied
 * - Patch 1524: places Add Another, Cancel, and Add Records on one shared footer row.
 * - Patch 1525: matches DNSPlus footer spacing by collapsing the empty validation row.
 * - Patch 1538: restores the stable pre-sort native row sequence for every
 *   Register DNS save, validates each existing row against its preserved
 *   registrar record ID before submission, removes the superseded value-only
 *   save verification, and strips only WHMCS's unrelated password-length
 *   sentence while preserving genuine DNS/registrar errors.
 * - Patch 1544: bypasses the registrar SaveDNS call when the complete DNS zone
 *   is unchanged. For changed saves that return WHMCS's generic DNS error, it
 *   performs a separate fresh full-zone comparison by exact normalized record
 *   multiset and count. Only a conclusive complete match is shown as success;
 *   stale-original-plus-new-duplicate and partial-save results remain errors.
 * - Patch 1545: adds a page-specific quick search beside Add Record. Search
 *   combines with the record-type filter, visual sorting, pagination, counts,
 *   and visible-row bulk controls without changing native row order or save data.
 * - Patch 1553: aligns Register DNS bulk actions with DNSPlus: Apply stays
 *   first, Choose Action follows, and the TTL field appears only when Change
 *   TTL is selected. Bulk save/delete behavior is unchanged.
 * - Patch 1559: standardizes the Register DNS record-table header typography
 *   with DNSPlus: identical family, size, weight, case, spacing, and alignment.
 * - Patch 1560: normalizes the live-rendered Register DNS header cells directly
 *   so uppercase labels and the shared Arial/Helvetica font cannot be bypassed
 *   by later template/table rules. Sorting and column behavior are unchanged.
 * - Patch 1576: temporarily disables Register DNS column sorting and clears
 *   any previously stored sort state. Records remain in their native registrar
 *   order so editing and saving cannot be affected by visual row ordering while
 *   the underlying sorted-save issue is investigated.
 * - Patch 1578: routes edits to existing records through a fresh-read,
 *   single-record registrar update path inside this form's confirmed submit
 *   handler. Add-only saves retain the existing path; mixed add/edit saves are
 *   blocked. Patch 1577's failed external interceptor is removed.
 * - Patch 1579: replaces the changed-row JSON form field that did not survive
 *   this installation's request handling with explicit indexed URL-encoded
 *   fields. The direct update and verification logic itself is unchanged.
 * - Patch 1580: restores visual-only column sorting now that existing-record
 *   edits are matched to fresh live record values rather than displayed row
 *   position. Timeout responses are verified before one guarded retry, and the
 * - Patch 1581: restores the missing in-memory row-list sort step. Headers and
 *   state were active in 1580, but pagination still received native row order.
 *   DOM/form row order remains unchanged for safe direct record updates.
 * - Patch 1626: keeps visually sorted existing rows in their last-loaded saved
 *   positions while fields contain unsaved edits. Edited rows are highlighted
 *   and counted, but DOM order, registrar IDs, and direct-update matching are
 *   unchanged. The active sort uses the new saved values only after reload.
 * - Patch 1627: makes the unsaved indicator reliable with capture-phase field
 *   listeners and places its badge inside the visible compact toolbar. This
 *   does not move rows, alter identities, or trigger sorting.
 * - Patch 1628: adds a visible Unsaved badge inside each changed record row and
 *   strengthens only that row's visual outline. The badge is not a form field
 * - Patch 1633: permit Host changes to reach the verified record-move endpoint.
 * - Patch 1629: contains each row badge inside its Host cell so it cannot
 *   increase row height or overlap records below.
 * - Patch 1630: dims and disables the native Save and Cancel actions when
 *   there are no unsaved existing-record edits, enables them immediately when
 *   an edit is detected, and disables them again when the edit is reverted.
 *   This is visual/action-state logic only and does not change sorting, record
 *   identities, row order, or DNS submission data.
 * - Patch 1631: refreshes the existing unsaved-row/count/action state after
 *   the native Cancel/reset behavior has restored field values. It does not
 *   reset fields itself, replace row snapshots, move rows, or alter record IDs.
 * - Patch 1685: sends add-only MX rows through a verified record-specific
 *   background request. The current Register DNS interface remains in place;
 *   existing-record edits, sorting, deletion, and all non-MX add paths are unchanged.
 * - Patch 1686: removes the temporary MX-only batch guard. When a new-record
 *   batch contains MX, supported companion records use the same verified
 *   background add path; existing records and sorting remain unchanged.
 * - Patch 1691: separates RegistrarDNS provider-managed root NS records from
 *   registrar delegation. The ns5-ns8 zone records remain visible but are
 *   labeled/protected, while actual domain nameserver management links to the
 *   confirmed Domain Settings -> Nameservers workflow.
 * - Patch 1849: routes every add-only A, AAAA, CNAME, MX, NS, and TXT batch
 *   through the verified record-specific add handler. Standalone CNAME and
 *   other standard additions no longer invoke WHMCS's full-zone SaveDNS path
 *   or resubmit existing records and their registrar identities.
 * - Patch 1850: removes the final native full-zone mutation fallback. The Add
 *   Record popup offers only verified direct-add types, unsupported additions
 *   fail closed, and every permitted add/edit/delete operation now uses a
 *   record-specific path with fresh live verification.
 * - Patch 1851: replaces the template's fabricated 14400 TTL fallback with
 *   each record's live NEO TTL, then makes bulk Change TTL save immediately
 *   through the verified record-specific update handler.
 * - Patch 1852: accepts the exact TTL read from DomainMonger's authoritative
 *   DNS servers when NEO's record-search response omits its TTL field.
 * - Patch 1857: rejects RegistrarDNS TTL values below 7200 seconds in the
 *   Add Record popup, bulk Change TTL, and existing-record Save Changes flow
 *   before anything is submitted. Record identities and save ordering are unchanged.
 * - Patch 1881: when the live-TTL safety pass repairs a legacy TXT row that was
 *   stored with the historical duplicate outer quote layer, reloads the DNS
 *   editor once so the client immediately sees the cleaned NEO value.
 * - Patch 1885: stores the exact live TXT value returned by the read-only TTL
 *   probe as identity-only state, so later edits use NEO's exact current-value
 *   while leaving the visible/editable TXT value unchanged.
 * - Patch 1634: after Cancel has actually restored all edited values, removes
 *   only the generated red Register DNS error message above this form. Genuine
 *   errors remain visible when any unsaved edit is still present.
 *   and is ignored by sorting, filtering, identity checks, save, and delete.
 *   green success notice is delayed until a freshly reloaded table contains
 *   every requested value.
 *   into its own native add row and submitted through the confirmed save path.
 * - Patch 1490: keeps Previous/Next controls outside the native action mover,
 *   adds matching pagers above and below the table, and replaces the visible
 *   native blank row with an Add Record modal using the native save path.
 * - Patch 1236: clears Bootstrap striped-table variables and inset cell shadows
 *   so the white row styling is actually visible.
 * - Fetches clientarea.php?action=domaindns&domainid=...&dmplainnative=1 so the
 *   native backend/form is used without rendering the full native page shell.
 *
 * Safety:
 * - The converted editor does not run when explicit legacy fallback flags are
 *   present, but Patch 1850 still blocks any full-zone mutation POST there.
 * - Does not touch language overrides.
 * - Does not touch the integration folder.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

/**
 * Patch 1850: fail closed before WHMCS can process a native full-zone save.
 *
 * Record-specific add/update/delete requests use their own POST fields and are
 * handled by the dedicated verified endpoints. A native SaveDNS request is
 * identifiable by its parallel DNS arrays (or sub=save). Those arrays are the
 * unsafe path because their displayed order can differ from NEO's live record
 * ID order. Block that payload on both the native and legacy DNS routes even
 * when the browser interceptor is missing, stale, disabled, or bypassed.
 */
$dmDns1850ScriptName = strtolower((string) basename($_SERVER['SCRIPT_NAME'] ?? ''));
$dmDns1850Action = strtolower((string) ($_GET['action'] ?? $_POST['action'] ?? ''));
$dmDns1850IsDnsRoute = (
    $dmDns1850ScriptName === 'clientarea.php'
    && $dmDns1850Action === 'domaindns'
) || (
    $dmDns1850ScriptName === 'dnsmanagement.php'
    && $dmDns1850Action === 'managednszone'
);
$dmDns1850IsFullZonePayload = strtolower(trim((string) ($_POST['sub'] ?? ''))) === 'save'
    || array_key_exists('dnsrecid', $_POST)
    || array_key_exists('dnsrecordhost', $_POST)
    || array_key_exists('dnsrecordtype', $_POST)
    || array_key_exists('dnsrecordaddress', $_POST)
    || array_key_exists('dnsrecordpriority', $_POST)
    || array_key_exists('dnsrecordttl', $_POST);

if (
    strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'POST'
    && $dmDns1850IsDnsRoute
    && $dmDns1850IsFullZonePayload
) {
    $dmDns1850DomainId = (int) ($_POST['domainid'] ?? $_POST['domain_id'] ?? $_GET['domainid'] ?? 0);
    $dmDns1850ReturnUrl = 'clientarea.php?action=domaindns';
    if ($dmDns1850DomainId > 0) {
        $dmDns1850ReturnUrl .= '&domainid=' . $dmDns1850DomainId;
    }

    http_response_code(409);
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=UTF-8');
        header('Cache-Control: no-store, max-age=0');
    }

    echo '<!doctype html><html lang="en"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>RegistrarDNS Full-Zone Save Blocked</title></head>'
        . '<body style="font-family:Arial,Helvetica,sans-serif;margin:40px;line-height:1.5">'
        . '<h1 style="font-size:22px">RegistrarDNS Full-Zone Save Blocked</h1>'
        . '<p>For safety, this installation does not permit WHMCS full-zone DNS submissions. '
        . 'No records were submitted.</p>'
        . '<p>Return to RegistrarDNS and use its record-specific Add, Save, or Delete controls.</p>'
        . '<p><a href="' . htmlspecialchars($dmDns1850ReturnUrl, ENT_QUOTES, 'UTF-8')
        . '">Return to RegistrarDNS</a></p></body></html>';
    exit;
}

/**
 * Begin the already-confirmed native DNS request as early as possible.
 *
 * This does not redirect, replace routes, or alter the native response. The
 * footer converter consumes this promise instead of starting the same request
 * after the old ResellerClub page has completely rendered.
 */
add_hook('ClientAreaHeadOutput', 1, function ($vars) {
    $scriptName = strtolower((string) basename($_SERVER['SCRIPT_NAME'] ?? ''));
    $action = strtolower((string) ($_GET['action'] ?? $_POST['action'] ?? ''));

    if ($scriptName !== 'dnsmanagement.php' || $action !== 'managednszone') {
        return '';
    }

    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'GET') {
        return '';
    }

    if (isset($_GET['dmlegacydns']) || isset($_GET['dmnoredirect']) || (isset($_GET['dmprefetch']) && (string) $_GET['dmprefetch'] === '0')) {
        return '';
    }

    $domainId = (int) ($_GET['domainid'] ?? $_POST['domainid'] ?? $_GET['id'] ?? $_POST['id'] ?? 0);
    if ($domainId <= 0) {
        return '';
    }

    // WHMCS has already authenticated and built the page by HeadOutput.
    // Release the PHP session lock so the authenticated native DNS request can
    // run concurrently instead of waiting for this page request to finish.
    if (session_status() === PHP_SESSION_ACTIVE) {
        @session_write_close();
    }

    $nativeUrl = 'clientarea.php?action=domaindns&domainid=' . $domainId . '&dmplainnative=1';
    $nativeUrlJson = json_encode($nativeUrl, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

    return <<<HTML
<script id="dm-resellerclub-dns-prefetch-1223">
(function () {
    'use strict';

    var nativeUrl = {$nativeUrlJson};

    if (window.dmDnsNativePrefetch1223 && window.dmDnsNativePrefetch1223.url === nativeUrl) {
        return;
    }

    window.dmDnsNativePrefetch1223 = {
        url: nativeUrl,
        startedAt: Date.now(),
        promise: fetch(nativeUrl, {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (response) {
            return response.text();
        }).then(function (html) {
            var doc = null;
            try {
                doc = new DOMParser().parseFromString(html, 'text/html');
            } catch (error) {
                doc = null;
            }
            return { ok: true, html: html, doc: doc };
        }).catch(function () {
            return { ok: false, html: '', doc: null };
        })
    };
}());
</script>
HTML;
});

add_hook('ClientAreaFooterOutput', 1113, function ($vars) {
    $scriptName = strtolower((string) basename($_SERVER['SCRIPT_NAME'] ?? ''));
    $action = strtolower((string) ($_GET['action'] ?? $_POST['action'] ?? ''));

    $legacyRoute = $scriptName === 'dnsmanagement.php' && $action === 'managednszone';
    $rawNativeRoute = $scriptName === 'clientarea.php'
        && $action === 'domaindns'
        && (isset($_REQUEST['dmplainnative']) || isset($_REQUEST['dmnativeold']) || isset($_REQUEST['dmfeednative']));
    $nativeDirectRoute = $scriptName === 'clientarea.php'
        && $action === 'domaindns'
        && !$rawNativeRoute;

    if (!$legacyRoute && !$nativeDirectRoute) {
        return '';
    }

    // Explicit fallback flags apply only to the confirmed legacy route.
    if ($legacyRoute && (isset($_GET['dmlegacydns']) || isset($_GET['dmnoredirect']))) {
        return '';
    }

    $domainId = (int) ($_GET['domainid'] ?? $_POST['domainid'] ?? $_GET['id'] ?? $_POST['id'] ?? 0);
    if ($domainId <= 0) {
        return '';
    }

    $domainName = trim((string) ($_GET['domain'] ?? $_POST['domain'] ?? ''));
    $nativeUrl = 'clientarea.php?action=domaindns&domainid=' . $domainId . '&dmplainnative=1';
    $nativeLiveUrl = 'clientarea.php?action=domaindns&domainid=' . $domainId;
    $domainNameserversUrl = 'clientarea.php?action=domaindetails&id=' . $domainId
        . '&dmsection=nameservers&dmdesign=1&dmconverted=1#tabNameservers';
    $legacyUrl = 'dnsmanagement.php?action=managednszone&domainid=' . $domainId . '&dmlegacydns=1&dmnoredirect=1';
    if ($domainName !== '') {
        $safeDomain = preg_replace('/[^a-z0-9._-]/i', '', $domainName);
        if ($safeDomain !== '') {
            $legacyUrl .= '&domain=' . rawurlencode($safeDomain);
        }
    }

    $domainJson = json_encode($domainName, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $nativeUrlJson = json_encode($nativeUrl, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $nativeLiveUrlJson = json_encode($nativeLiveUrl, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $domainNameserversUrlJson = json_encode($domainNameserversUrl, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $legacyUrlJson = json_encode($legacyUrl, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $showFallbackLinks = isset($_GET['dmshowdnsfallbacks']) || isset($_GET['dmdebugdnsfeed']);
    $showFallbackLinksJson = $showFallbackLinks ? 'true' : 'false';
    $nativeDirectModeJson = $nativeDirectRoute ? 'true' : 'false';

    return <<<HTML
<style id="dm-resellerclub-dns-live-feed-1113-css">
body.dm-resellerclub-dns-live-feed-1113 #fullpage-overlay {
    display: none !important;
    opacity: 0 !important;
    visibility: hidden !important;
    pointer-events: none !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-native-direct-target-1228,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-native-direct-target-1228.card,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-native-direct-target-1228.panel {
    background: transparent !important;
    border: 0 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    margin: 0 !important;
    padding: 0 !important;
}
/* Patch 1229: the opt-in native route already has the unified top menu,
 * so remove only the legacy WHMCS sidebar column and let the DNS workspace
 * use the full converted-page width. */
body.dm-resellerclub-dns-live-feed-1113.dm-dns-native-direct-1228 #main-body > .container > .row > .col-lg-4.col-xl-3,
body.dm-resellerclub-dns-live-feed-1113.dm-dns-native-direct-1228 #main-body > .container-fluid > .row > .col-lg-4.col-xl-3,
body.dm-resellerclub-dns-live-feed-1113.dm-dns-native-direct-1228 #main-body > .container > .row > .sidebar,
body.dm-resellerclub-dns-live-feed-1113.dm-dns-native-direct-1228 #main-body > .container-fluid > .row > .sidebar,
body.dm-resellerclub-dns-live-feed-1113.dm-dns-native-direct-1228 #main-body .secondary-sidebar,
body.dm-resellerclub-dns-live-feed-1113.dm-dns-native-direct-1228 #main-body .panel-sidebar {
    display: none !important;
}
body.dm-resellerclub-dns-live-feed-1113.dm-dns-native-direct-1228 #main-body .primary-content,
body.dm-resellerclub-dns-live-feed-1113.dm-dns-native-direct-1228 #main-body .dm-dns-native-main-column-1229 {
    flex: 0 0 100% !important;
    max-width: 100% !important;
    width: 100% !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-panel-1113 {
    background: transparent;
    border: 0;
    border-radius: 0;
    box-shadow: none;
    margin: 0;
    overflow: visible;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-head-1113 {
    align-items: center;
    background: #163a5f;
    border-radius: 7px;
    color: #ffffff;
    display: flex;
    gap: 14px;
    justify-content: space-between;
    margin: 0 0 10px;
    padding: 13px 16px;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-head-1113 strong {
    color: #ffffff;
    display: block;
    font-size: 16px;
    line-height: 1.25;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-head-1113 span {
    color: rgba(255,255,255,.8);
    display: block;
    font-size: 12px;
    margin-top: 2px;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-badge-1113 {
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.2);
    border-radius: 6px;
    color: #ffffff;
    font-size: 12px;
    font-weight: 800;
    padding: 7px 10px;
    white-space: nowrap;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-toolbar-1113 {
    align-items: center;
    background: #f8fafc;
    border-bottom: 1px solid rgba(17, 43, 77, .10);
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 12px;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-pill-1113 {
    align-items: center;
    background: #eef3f7;
    border: 1px solid rgba(17, 43, 77, .10);
    border-radius: 6px;
    color: #425466 !important;
    cursor: default;
    display: inline-flex;
    font-size: 12px;
    font-weight: 800;
    min-height: 28px;
    line-height: 26px;
    padding: 0 9px;
    pointer-events: none;
    text-decoration: none !important;
    user-select: none;
    white-space: nowrap;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-toolbar-1113 a {
    align-items: center;
    background: #ffffff;
    border: 1px solid rgba(17, 43, 77, .16);
    border-radius: 6px;
    color: #293f56 !important;
    display: inline-flex;
    font-size: 12px;
    font-weight: 800;
    height: 34px;
    line-height: 32px;
    padding: 0 10px;
    text-decoration: none !important;
    white-space: nowrap;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-toolbar-1113 a:hover,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-toolbar-1113 a:focus {
    border-color: rgba(245, 130, 32, .55);
    color: #f58220 !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-spacer-1113 {
    flex: 1 1 auto;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-body-1113 {
    background: transparent;
    border: 0;
    border-radius: 0;
    box-shadow: none;
    margin: 0;
    padding: 0;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-loading-1113,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-message-1113 {
    background: #fff8e1;
    border: 1px solid #f0dfad;
    border-radius: 6px;
    color: #5d4a1f;
    font-size: 13px;
    font-weight: 700;
    margin: 0 0 12px;
    padding: 10px 12px;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-message-1113.dm-success {
    background: #eef9f0;
    border-color: #b9dfc1;
    color: #275b31;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-message-1113.dm-info {
    background: #eef5fb;
    border-color: #cfe0ef;
    color: #163a5f;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-message-1113.dm-error {
    background: #fff1f1;
    border-color: #e5bbbb;
    color: #7a2d2d;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-scroll-1113 {
    border: 1px solid rgba(17, 43, 77, .12);
    border-radius: 7px;
    overflow-x: auto;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 .table,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 table {
    background: #ffffff !important;
    border-collapse: separate !important;
    border-spacing: 0 !important;
    margin: 0 !important;
    min-width: 760px;
    width: 100% !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 table thead th,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 table th {
    background: #163a5f !important;
    border-color: rgba(255,255,255,.16) !important;
    color: #ffffff !important;
    font-family: Arial, Helvetica, sans-serif !important;
    font-size: 12px !important;
    font-style: normal !important;
    font-weight: 700 !important;
    letter-spacing: .02em !important;
    line-height: 1.25 !important;
    padding: 10px 9px !important;
    text-transform: uppercase !important;
    vertical-align: middle !important;
    white-space: nowrap;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 table td {
    border-color: rgba(17, 43, 77, .08) !important;
    color: #293f56;
    font-size: 13px;
    padding: 8px !important;
    vertical-align: middle !important;
}
/* Patch 1236: Bootstrap 5 paints striped rows with CSS variables and an
 * inset cell box-shadow. Reset those paint layers as well as background-color. */
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-scroll-1113,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 table,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 table tbody,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 table tbody tr,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 table tbody td {
    --bs-table-bg: #ffffff !important;
    --bs-table-accent-bg: #ffffff !important;
    --bs-table-striped-bg: #ffffff !important;
    --bs-table-bg-type: #ffffff !important;
    --bs-table-bg-state: #ffffff !important;
    background: #ffffff !important;
    background-color: #ffffff !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 table.table > tbody > tr > *,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 table.table-striped > tbody > tr:nth-of-type(odd) > *,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 table.table-striped > tbody > tr:nth-of-type(even) > * {
    --bs-table-bg: #ffffff !important;
    --bs-table-accent-bg: #ffffff !important;
    --bs-table-striped-bg: #ffffff !important;
    --bs-table-bg-type: #ffffff !important;
    --bs-table-bg-state: #ffffff !important;
    background: #ffffff !important;
    background-color: #ffffff !important;
    box-shadow: none !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 table tbody tr:hover,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 table tbody tr:hover > * {
    --bs-table-bg: #fff8f1 !important;
    --bs-table-hover-bg: #fff8f1 !important;
    --bs-table-bg-state: #fff8f1 !important;
    background: #fff8f1 !important;
    background-color: #fff8f1 !important;
    box-shadow: none !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 input[type="text"],
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 input[type="number"],
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 select,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 textarea,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 .form-control {
    background: #ffffff !important;
    border: 1px solid rgba(17, 43, 77, .22) !important;
    border-radius: 6px !important;
    box-shadow: none !important;
    color: #293f56 !important;
    min-height: 34px;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 input:focus,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 select:focus,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 textarea:focus {
    border-color: #f58220 !important;
    box-shadow: 0 0 0 3px rgba(245, 130, 32, .14) !important;
    outline: none !important;
}
/* Patch 1238: bring the final DNS form actions slightly closer to the
 * table and inset them from the right edge. */
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-actions-1113 {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: flex-end;
    margin: 4px 12px 8px 0;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 .btn-primary,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 button[type="submit"],
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 input[type="submit"],
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-actions-1113 .btn-primary {
    background: #f58220 !important;
    border-color: #f58220 !important;
    border-radius: 6px !important;
    color: #ffffff !important;
    font-weight: 800 !important;
    min-height: 36px;
    padding: 8px 14px !important;
    text-decoration: none !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 .btn-primary:hover,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 button[type="submit"]:hover,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 input[type="submit"]:hover {
    background: #d8741f !important;
    border-color: #d8741f !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 .btn-default,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 .btn-secondary,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-actions-1113 .btn-secondary {
    background: #163a5f !important;
    border-color: #163a5f !important;
    border-radius: 6px !important;
    color: #ffffff !important;
    font-weight: 800 !important;
    min-height: 36px;
    padding: 8px 14px !important;
    text-decoration: none !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 .alert-info,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 .alert-warning {
    background: #fff8e1 !important;
    border-color: #f0dfad !important;
    color: #5d4a1f !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 button[disabled],
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 input[type="submit"][disabled] {
    cursor: not-allowed !important;
    opacity: .68 !important;
}

/* Patch 1630: Save and Cancel have no valid action until an existing record
 * differs from its preserved loaded state. Anchors need an explicit class
 * because HTML does not provide a native disabled state for links. */
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-actions-1113 .dm-dns-action-disabled-1630,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-actions-1113 button:disabled,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-actions-1113 input:disabled {
    cursor: not-allowed !important;
    opacity: .48 !important;
    pointer-events: none !important;
}

body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bulk-toolbar-1139 {
    align-items: center;
    background: #f8fafc;
    border: 1px solid rgba(17, 43, 77, .12);
    border-radius: 7px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin: 0 0 12px;
    padding: 10px;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-visually-hidden-1139 {
    border: 0 !important;
    clip: rect(0 0 0 0) !important;
    height: 1px !important;
    margin: -1px !important;
    overflow: hidden !important;
    padding: 0 !important;
    position: absolute !important;
    white-space: nowrap !important;
    width: 1px !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bulk-toolbar-1139 label {
    align-items: center !important;
    align-self: center !important;
    box-sizing: border-box !important;
    color: #293f56;
    display: inline-flex !important;
    font-size: 12px;
    font-weight: 800;
    gap: 6px;
    height: 36px !important;
    line-height: 36px !important;
    margin: 0 !important;
    vertical-align: middle !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bulk-toolbar-1139 select,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bulk-toolbar-1139 input[type="text"] {
    align-self: center !important;
    box-sizing: border-box !important;
    display: inline-block !important;
    height: 36px !important;
    line-height: 34px !important;
    margin: 0 !important;
    min-height: 36px !important;
    padding-bottom: 6px !important;
    padding-top: 6px !important;
    vertical-align: middle !important;
    width: auto;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bulk-toolbar-1139 input[data-dm-dns-bulk-ttl] {
    max-width: 92px;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bulk-toolbar-1139 label.dm-dns-bulk-ttl-label-1139 {
    display: none !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bulk-toolbar-1139 label.dm-dns-bulk-ttl-label-1139.dm-dns-bulk-ttl-visible-1553 {
    display: inline-flex !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bulk-toolbar-1139 button,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bulk-toolbar-1139 button[data-dm-dns-bulk-apply] {
    align-items: center !important;
    align-self: center !important;
    background: #f58220 !important;
    border: 1px solid #f58220 !important;
    border-radius: 6px !important;
    box-sizing: border-box !important;
    color: #ffffff !important;
    display: inline-flex !important;
    font-size: 12px;
    font-weight: 800;
    height: 36px !important;
    justify-content: center !important;
    line-height: 34px !important;
    margin: 0 !important;
    min-height: 36px !important;
    min-width: 76px !important;
    padding: 0 14px !important;
    position: relative !important;
    top: 0 !important;
    text-decoration: none !important;
    vertical-align: middle !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bulk-toolbar-1139 button:hover,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bulk-toolbar-1139 button:focus,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bulk-toolbar-1139 button[data-dm-dns-bulk-apply]:hover,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bulk-toolbar-1139 button[data-dm-dns-bulk-apply]:focus {
    background: #d8741f !important;
    border-color: #d8741f !important;
    color: #ffffff !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bulk-toolbar-1139 button:disabled,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bulk-toolbar-1139 button[data-dm-dns-bulk-apply]:disabled {
    background: #f5b171 !important;
    border-color: #f5b171 !important;
    color: #ffffff !important;
    cursor: not-allowed !important;
    opacity: .82 !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bulk-status-1139 {
    color: #5d4a1f;
    font-size: 12px;
    font-weight: 700;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-pagination-1490 {
    align-items: center;
    display: flex;
    flex: 0 0 auto;
    flex-wrap: nowrap;
    gap: 8px;
    justify-content: flex-end;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-pagination-top-1490 {
    margin-left: auto;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-pagination-bottom-1490 {
    margin: 10px 8px 2px;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-pagination-1490 button,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bulk-toolbar-1139 .dm-dns-pagination-1490 button {
    align-items: center !important;
    background: #163a5f !important;
    border: 1px solid #163a5f !important;
    border-radius: 6px !important;
    box-sizing: border-box !important;
    color: #ffffff !important;
    display: inline-flex !important;
    flex: 0 0 auto !important;
    font-size: 12px !important;
    font-weight: 800 !important;
    height: 36px !important;
    justify-content: center !important;
    line-height: 1 !important;
    margin: 0 !important;
    min-height: 36px !important;
    min-width: 94px !important;
    overflow: visible !important;
    padding: 0 14px !important;
    text-decoration: none !important;
    white-space: nowrap !important;
    width: auto !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-pagination-1490 button:hover,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-pagination-1490 button:focus,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bulk-toolbar-1139 .dm-dns-pagination-1490 button:hover,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bulk-toolbar-1139 .dm-dns-pagination-1490 button:focus {
    background: #214e7a !important;
    border-color: #214e7a !important;
    color: #ffffff !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-pagination-1490 button:disabled,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bulk-toolbar-1139 .dm-dns-pagination-1490 button:disabled {
    background: #8da0b4 !important;
    border-color: #8da0b4 !important;
    color: #ffffff !important;
    cursor: not-allowed !important;
    opacity: .72 !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-pagination-summary-1490 {
    color: #293f56;
    display: inline-block;
    font-size: 12px;
    font-weight: 800;
    min-width: 190px;
    text-align: center;
    white-space: nowrap;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-add-record-button-1490 {
    white-space: nowrap !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-quick-search-label-1545 {
    flex: 0 1 210px;
    min-width: 170px;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bulk-toolbar-1139 .dm-dns-quick-search-label-1545 input[type="search"] {
    background: #ffffff !important;
    border: 1px solid rgba(17, 43, 77, .22) !important;
    border-radius: 6px !important;
    box-shadow: none !important;
    color: #293f56 !important;
    height: 36px !important;
    line-height: 34px !important;
    margin: 0 !important;
    min-height: 36px !important;
    padding: 6px 10px !important;
    width: 100% !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bulk-toolbar-1139 .dm-dns-quick-search-label-1545 input[type="search"]:focus {
    border-color: #f58220 !important;
    box-shadow: 0 0 0 3px rgba(245, 130, 32, .14) !important;
    outline: none !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bulk-toolbar-1139 .dm-dns-quick-search-label-1545 input[type="search"]::-webkit-search-cancel-button {
    cursor: pointer;
}
body.dm-resellerclub-dns-live-feed-1113 tr.dm-dns-native-add-row-1490 {
    display: none !important;
}
body.dm-dns-add-record-open-1490 {
    overflow: hidden !important;
}
.dm-dns-add-record-modal-1490[hidden] {
    display: none !important;
}
.dm-dns-add-record-modal-1490 {
    align-items: center;
    background: rgba(14, 32, 52, .62);
    bottom: 0;
    display: flex;
    justify-content: center;
    left: 0;
    padding: 22px;
    position: fixed;
    right: 0;
    top: 0;
    z-index: 12050;
}
.dm-dns-add-record-dialog-1490 {
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 18px 55px rgba(10, 31, 52, .28);
    max-height: calc(100vh - 44px);
    max-width: 720px;
    overflow: auto;
    width: 100%;
}
.dm-dns-add-record-head-1490 {
    align-items: center;
    background: #163a5f;
    border-radius: 8px 8px 0 0;
    color: #ffffff;
    display: flex;
    justify-content: space-between;
    padding: 14px 16px;
}
.dm-dns-add-record-head-1490 strong {
    color: #ffffff;
    font-size: 16px;
}
.dm-dns-add-record-close-1490 {
    background: transparent !important;
    border: 0 !important;
    color: #ffffff !important;
    cursor: pointer;
    font-size: 25px !important;
    height: 32px !important;
    line-height: 28px !important;
    min-height: 32px !important;
    min-width: 32px !important;
    padding: 0 !important;
}
.dm-dns-add-record-body-1490 {
    padding: 18px;
}
.dm-dns-add-record-grid-1490 {
    display: grid;
    gap: 14px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}
.dm-dns-add-record-field-1490 {
    color: #293f56;
    display: flex;
    flex-direction: column;
    font-size: 12px;
    font-weight: 800;
    gap: 6px;
}
.dm-dns-add-record-field-1490.dm-wide-1490 {
    grid-column: 1 / -1;
}
.dm-dns-add-record-field-1490 input,
.dm-dns-add-record-field-1490 select {
    background: #ffffff !important;
    border: 1px solid rgba(17, 43, 77, .24) !important;
    border-radius: 6px !important;
    box-shadow: none !important;
    color: #293f56 !important;
    min-height: 38px !important;
    padding: 7px 10px !important;
    width: 100% !important;
}
.dm-dns-add-record-field-1490 input:focus,
.dm-dns-add-record-field-1490 select:focus {
    border-color: #f58220 !important;
    box-shadow: 0 0 0 3px rgba(245, 130, 32, .14) !important;
    outline: none !important;
}
.dm-dns-add-record-message-1490 {
    color: #b94a48;
    font-size: 12px;
    font-weight: 700;
    margin-top: 12px;
    min-height: 18px;
}
/* Patch 1525: do not reserve an empty validation row between the DNS rows and footer.
 * The normal error spacing returns automatically whenever a message is present. */
.dm-dns-add-record-message-1490:empty {
    display: none;
    margin: 0;
    min-height: 0;
}
.dm-dns-add-record-actions-1490 {
    align-items: center;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    padding: 0 18px 18px;
}
.dm-dns-add-record-actions-1490 button {
    border-radius: 6px !important;
    font-size: 13px !important;
    font-weight: 800 !important;
    min-height: 38px !important;
    min-width: 108px !important;
    padding: 8px 15px !important;
    white-space: nowrap !important;
}
.dm-dns-add-record-cancel-1490 {
    background: #163a5f !important;
    border: 1px solid #163a5f !important;
    color: #ffffff !important;
}
.dm-dns-add-record-save-1490 {
    background: #f58220 !important;
    border: 1px solid #f58220 !important;
    color: #ffffff !important;
}
.dm-dns-add-record-cancel-1490:hover,
.dm-dns-add-record-cancel-1490:focus {
    background: #214e7a !important;
    border-color: #214e7a !important;
}
.dm-dns-add-record-save-1490:hover,
.dm-dns-add-record-save-1490:focus {
    background: #d8741f !important;
    border-color: #d8741f !important;
}
/* Patch 1517: expandable row-based bulk Add DNS Records modal. */
.dm-dns-add-record-dialog-bulk-1517 {
    max-width: 1080px;
}
.dm-dns-add-record-table-wrap-1517 {
    border: 1px solid rgba(22, 58, 95, .18);
    border-radius: 7px;
    overflow-x: auto;
}
.dm-dns-add-record-header-1517,
.dm-dns-add-record-row-1517 {
    align-items: end;
    display: grid;
    gap: 10px;
    grid-template-columns: minmax(112px, .8fr) minmax(145px, 1fr) minmax(250px, 2fr) 92px 92px 164px;
    min-width: 930px;
}
.dm-dns-add-record-header-1517 {
    background: #163a5f;
    color: #ffffff;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .02em;
    padding: 9px 12px;
    text-transform: uppercase;
}
.dm-dns-add-record-row-1517 {
    background: #ffffff;
    border-top: 1px solid rgba(22, 58, 95, .12);
    padding: 11px 12px;
}
.dm-dns-add-record-row-1517:first-child {
    border-top: 0;
}
.dm-dns-add-record-row-1517:nth-child(even) {
    background: #f8fafc;
}
.dm-dns-add-record-row-error-1517 {
    background: #fff8e3 !important;
    box-shadow: inset 4px 0 0 #b94a48;
}
.dm-dns-add-record-row-1517 .dm-dns-add-record-field-1490 {
    gap: 4px;
    margin: 0;
    min-width: 0;
}
.dm-dns-add-record-row-1517 .dm-dns-add-record-field-1490 > span {
    display: none;
}
.dm-dns-add-record-row-1517 .dm-dns-add-record-field-1490 input,
.dm-dns-add-record-row-1517 .dm-dns-add-record-field-1490 select {
    min-height: 36px !important;
    padding: 6px 8px !important;
}
.dm-dns-add-record-row-actions-1517 {
    align-items: center;
    display: flex;
    gap: 7px;
    justify-content: flex-end;
    min-height: 36px;
}
.dm-dns-add-record-row-actions-1517 button,
.dm-dns-add-record-more-1517 {
    border-radius: 6px !important;
    cursor: pointer;
    font-size: 12px !important;
    font-weight: 800 !important;
    min-height: 36px !important;
    padding: 7px 10px !important;
    white-space: nowrap !important;
}
.dm-dns-add-record-duplicate-1517 {
    background: #163a5f !important;
    border: 1px solid #163a5f !important;
    color: #ffffff !important;
}
.dm-dns-add-record-duplicate-1517:hover,
.dm-dns-add-record-duplicate-1517:focus {
    background: #214e7a !important;
    border-color: #214e7a !important;
}
.dm-dns-add-record-remove-1517 {
    background: #b94a48 !important;
    border: 1px solid #b94a48 !important;
    color: #ffffff !important;
}
.dm-dns-add-record-remove-1517:hover,
.dm-dns-add-record-remove-1517:focus {
    filter: brightness(.92);
}
.dm-dns-add-record-remove-1517:disabled {
    cursor: not-allowed;
    opacity: .45;
}
.dm-dns-add-record-footer-1524 {
    align-items: center;
    display: flex;
    gap: 12px;
    justify-content: space-between;
    margin-top: 12px;
}
.dm-dns-add-record-more-wrap-1517 {
    align-items: center;
    display: flex;
    flex: 1 1 auto;
    justify-content: flex-start;
    margin: 0;
}
.dm-dns-add-record-footer-1524 .dm-dns-add-record-actions-1490 {
    flex: 0 0 auto;
    padding: 0;
}
.dm-dns-add-record-more-1517 {
    background: #163a5f !important;
    border: 1px solid #163a5f !important;
    color: #ffffff !important;
}
.dm-dns-add-record-more-1517:hover,
.dm-dns-add-record-more-1517:focus {
    background: #214e7a !important;
    border-color: #214e7a !important;
}
@media (max-width: 900px) {
    .dm-dns-add-record-dialog-bulk-1517 {
        max-width: 760px;
    }
    .dm-dns-add-record-header-1517 {
        display: none;
    }
    .dm-dns-add-record-table-wrap-1517 {
        border: 0;
        overflow: visible;
    }
    .dm-dns-add-record-row-1517 {
        border: 1px solid rgba(22, 58, 95, .18);
        border-radius: 7px;
        gap: 10px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        margin-bottom: 12px;
        min-width: 0;
        padding: 12px;
    }
    .dm-dns-add-record-row-1517 .dm-dns-add-record-field-1490 > span {
        display: inline;
    }
    .dm-dns-add-record-row-1517 .dm-dns-add-record-field-address-1517,
    .dm-dns-add-record-row-actions-1517 {
        grid-column: 1 / -1;
    }
    .dm-dns-add-record-row-actions-1517 {
        justify-content: flex-start;
    }
}
@media (max-width: 520px) {
    .dm-dns-add-record-row-1517 {
        grid-template-columns: 1fr;
    }
    .dm-dns-add-record-row-1517 .dm-dns-add-record-field-address-1517,
    .dm-dns-add-record-row-actions-1517 {
        grid-column: auto;
    }
    .dm-dns-add-record-footer-1524 {
        align-items: stretch;
        flex-direction: column;
    }
    .dm-dns-add-record-footer-1524 .dm-dns-add-record-more-wrap-1517,
    .dm-dns-add-record-footer-1524 .dm-dns-add-record-actions-1490 {
        width: 100%;
    }
    .dm-dns-add-record-footer-1524 .dm-dns-add-record-more-1517 {
        width: 100% !important;
    }
    .dm-dns-add-record-footer-1524 .dm-dns-add-record-actions-1490 {
        align-items: stretch;
        flex-direction: row;
    }
    .dm-dns-add-record-footer-1524 .dm-dns-add-record-actions-1490 button {
        flex: 1 1 0;
        min-width: 0 !important;
        width: auto;
    }
}
@media (max-width: 900px) {
    body.dm-resellerclub-dns-live-feed-1113 .dm-dns-quick-search-label-1545 {
        flex: 1 1 220px;
    }
    body.dm-resellerclub-dns-live-feed-1113 .dm-dns-pagination-top-1490 {
        margin-left: 0;
        width: 100%;
    }
    body.dm-resellerclub-dns-live-feed-1113 .dm-dns-pagination-1490 {
        justify-content: center;
    }
}
@media (max-width: 620px) {
    body.dm-resellerclub-dns-live-feed-1113 .dm-dns-quick-search-label-1545 {
        flex: 1 1 100%;
        min-width: 100%;
        width: 100%;
    }
    .dm-dns-add-record-grid-1490 {
        grid-template-columns: 1fr;
    }
    .dm-dns-add-record-field-1490.dm-wide-1490 {
        grid-column: auto;
    }
    body.dm-resellerclub-dns-live-feed-1113 .dm-dns-pagination-summary-1490 {
        min-width: 0;
        order: 3;
        width: 100%;
    }
    body.dm-resellerclub-dns-live-feed-1113 .dm-dns-pagination-1490 {
        flex-wrap: wrap;
    }
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-select-col {
    text-align: center !important;
    width: 52px !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-row-select,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-select-all {
    -webkit-appearance: none !important;
    appearance: none !important;
    background: #ffffff !important;
    background-image: none !important;
    border: 1px solid rgba(22, 58, 95, .55) !important;
    border-radius: 4px !important;
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .75) !important;
    box-sizing: border-box !important;
    cursor: pointer !important;
    display: inline-block !important;
    flex: 0 0 16px !important;
    height: 16px !important;
    margin: 0 !important;
    min-height: 16px !important;
    min-width: 16px !important;
    opacity: 1 !important;
    padding: 0 !important;
    position: relative !important;
    vertical-align: middle !important;
    visibility: visible !important;
    width: 16px !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-row-select:not(:checked)::after,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-select-all:not(:checked)::after {
    content: none !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-row-select:checked,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-select-all:checked,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-select-all:indeterminate {
    background: #f58220 !important;
    border-color: #f58220 !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-row-select:checked::after,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-select-all:checked::after {
    border: solid #ffffff !important;
    border-width: 0 2px 2px 0 !important;
    content: '' !important;
    display: block !important;
    height: 8px !important;
    left: 5px !important;
    position: absolute !important;
    top: 1px !important;
    transform: rotate(45deg) !important;
    width: 4px !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-select-all:indeterminate::after {
    background: #ffffff;
    content: '';
    height: 2px;
    left: 3px;
    position: absolute;
    top: 6px;
    width: 8px;
}
body.dm-resellerclub-dns-live-feed-1113 tr.dm-dns-row-delete-pending-1139 td {
    background: #fff1f1 !important;
}
body.dm-resellerclub-dns-live-feed-1113 tr.dm-dns-row-delete-pending-1139 input,
body.dm-resellerclub-dns-live-feed-1113 tr.dm-dns-row-delete-pending-1139 select {
    opacity: .62;
}
/* Patch 1626: show edited-but-unsaved existing records without changing
 * row height, DOM order, submitted arrays, or registrar identity fields. */
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 table tbody tr.dm-dns-unsaved-edit-1626 > td {
    --bs-table-bg: #fff8e1 !important;
    --bs-table-accent-bg: #fff8e1 !important;
    --bs-table-striped-bg: #fff8e1 !important;
    --bs-table-bg-type: #fff8e1 !important;
    --bs-table-bg-state: #fff8e1 !important;
    background: #fff8e1 !important;
    background-color: #fff8e1 !important;
    box-shadow: inset 0 2px 0 rgba(245,130,32,.72), inset 0 -2px 0 rgba(245,130,32,.72) !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 table tbody tr.dm-dns-unsaved-edit-1626 > td:first-child {
    box-shadow: inset 4px 0 0 #f58220, inset 0 2px 0 rgba(245,130,32,.72), inset 0 -2px 0 rgba(245,130,32,.72) !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 table tbody tr.dm-dns-unsaved-edit-1626 > td:last-child {
    box-shadow: inset -2px 0 0 rgba(245,130,32,.72), inset 0 2px 0 rgba(245,130,32,.72), inset 0 -2px 0 rgba(245,130,32,.72) !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 table tbody tr.dm-dns-unsaved-edit-1626 input[type="text"],
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 table tbody tr.dm-dns-unsaved-edit-1626 input[type="number"],
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 table tbody tr.dm-dns-unsaved-edit-1626 select,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 table tbody tr.dm-dns-unsaved-edit-1626 textarea {
    background: #fffdf5 !important;
    border-color: rgba(245,130,32,.72) !important;
}
/* Patch 1629: keep the row badge inside the existing Host-cell footprint.
 * It must not add row height or overlap the record below. */
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 table tbody td.dm-dns-unsaved-badge-cell-1629 {
    position: relative !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 table tbody td.dm-dns-unsaved-badge-cell-1629 input[name="dnsrecordhost[]"] {
    box-sizing: border-box !important;
    padding-right: 72px !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-unsaved-row-badge-1628 {
    align-items: center;
    background: #f58220;
    border: 1px solid #d8741f;
    border-radius: 999px;
    box-sizing: border-box;
    color: #ffffff;
    display: inline-flex;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .03em;
    line-height: 1;
    margin: 0;
    min-height: 16px;
    padding: 2px 6px;
    pointer-events: none;
    position: absolute;
    right: 12px;
    text-transform: uppercase;
    top: 50%;
    transform: translateY(-50%);
    white-space: nowrap;
    z-index: 2;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-unsaved-count-1626 {
    display: inline-flex;
    align-items: center;
    min-height: 30px;
    padding: 4px 10px;
    border: 1px solid rgba(245, 130, 32, .45);
    border-radius: 4px;
    background: #fff8e1;
    color: #163a5f;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.2;
    white-space: nowrap;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-unsaved-count-1626[hidden] {
    display: none !important;
}
/* Patch 1627: the compact-controls hook owns the visible top toolbar. Keep the
 * unsaved badge aligned beside the Records count after that hook rearranges it. */
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-top-left-1499 > .dm-dns-unsaved-count-1626 {
    box-sizing: border-box !important;
    height: 34px !important;
    min-height: 34px !important;
    padding: 0 10px !important;
}

/* Patch 1240: match the larger, clearer My Domains sort indicators. */
body.dm-resellerclub-dns-live-feed-1113 th.dm-dns-sortable-1139 {
    cursor: pointer;
    user-select: none;
}
body.dm-resellerclub-dns-live-feed-1113 th.dm-dns-sortable-1139::after {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='28' viewBox='0 0 12 28'%3E%3Cpath fill='rgba(255,255,255,.78)' d='M6 1L11 9H1z'/%3E%3Cpath fill='rgba(255,255,255,.78)' d='M6 27L1 19h10z'/%3E%3C/svg%3E");
    background-position: center center;
    background-repeat: no-repeat;
    background-size: 12px 28px;
    border: 0;
    content: '';
    display: inline-block;
    font-size: 0;
    height: 28px;
    line-height: 0;
    margin-left: 8px;
    opacity: 1;
    padding: 0;
    position: static;
    transform: none;
    vertical-align: -9px;
    visibility: visible;
    width: 12px;
}
body.dm-resellerclub-dns-live-feed-1113 th.dm-dns-sortable-1139.dm-sort-asc-1139::after {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='28' viewBox='0 0 12 28'%3E%3Cpath fill='white' d='M6 1L11 9H1z'/%3E%3Cpath fill='rgba(255,255,255,.42)' d='M6 27L1 19h10z'/%3E%3C/svg%3E");
}
body.dm-resellerclub-dns-live-feed-1113 th.dm-dns-sortable-1139.dm-sort-desc-1139::after {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='28' viewBox='0 0 12 28'%3E%3Cpath fill='rgba(255,255,255,.42)' d='M6 1L11 9H1z'/%3E%3Cpath fill='white' d='M6 27L1 19h10z'/%3E%3C/svg%3E");
}
/* Patch 1691: provider NS rows belong to the RegistrarDNS zone. They are not
 * registrar delegation controls, so make that distinction explicit without
 * removing the submitted native fields or changing record identity/order. */
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-zone-ns-notice-1691 {
    align-items: center;
    background: #fff8df;
    border: 1px solid #ecd58d;
    border-radius: 7px;
    display: flex;
    gap: 14px;
    justify-content: space-between;
    margin: 0 0 14px;
    padding: 12px 14px;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-zone-ns-notice-1691 strong {
    color: #163a5f;
    display: block;
    font-size: 14px;
    line-height: 1.3;
    margin: 0 0 3px;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-zone-ns-notice-1691 span {
    color: #536577;
    display: block;
    font-size: 12px;
    line-height: 1.45;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-zone-ns-manage-1691,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-zone-ns-manage-1691:visited {
    align-items: center;
    background: #163a5f;
    border: 1px solid #163a5f;
    border-radius: 5px;
    color: #fff !important;
    display: inline-flex;
    flex: 0 0 auto;
    font-size: 12px;
    font-weight: 700;
    justify-content: center;
    min-height: 34px;
    padding: 7px 12px;
    text-decoration: none !important;
    white-space: nowrap;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-zone-ns-manage-1691:hover,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-zone-ns-manage-1691:focus {
    background: #214e7a;
    border-color: #214e7a;
    color: #fff !important;
    text-decoration: none !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-zone-ns-actions-1695 {
    align-items: center;
    display: inline-flex;
    flex: 0 0 auto;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: flex-end;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-zone-ns-use-root-1695,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-zone-ns-use-root-1695:visited {
    align-items: center;
    background: #f58220;
    border: 1px solid #f58220;
    border-radius: 5px;
    color: #fff !important;
    display: inline-flex;
    flex: 0 0 auto;
    font-size: 12px;
    font-weight: 700;
    justify-content: center;
    min-height: 34px;
    padding: 7px 12px;
    text-decoration: none !important;
    white-space: nowrap;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-zone-ns-use-root-1695:hover,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-zone-ns-use-root-1695:focus {
    background: #214e7a;
    border-color: #214e7a;
    color: #fff !important;
    text-decoration: none !important;
}
body.dm-resellerclub-dns-live-feed-1113 tr.dm-provider-zone-ns-1691 td {
    background: #f8fafc !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-provider-zone-ns-badge-1691 {
    background: #163a5f;
    border-radius: 999px;
    color: #fff;
    display: inline-block;
    font-size: 10px;
    font-weight: 800;
    line-height: 1;
    margin: 5px 0 0;
    padding: 5px 7px;
    white-space: nowrap;
}
body.dm-resellerclub-dns-live-feed-1113 tr.dm-provider-zone-ns-1691 input[readonly],
body.dm-resellerclub-dns-live-feed-1113 tr.dm-provider-zone-ns-1691 select[aria-disabled="true"] {
    background: #eef2f6 !important;
    color: #536577 !important;
    cursor: default !important;
}
body.dm-resellerclub-dns-live-feed-1113 tr.dm-provider-zone-ns-1691 .dm-dns-row-select {
    cursor: not-allowed !important;
    opacity: .45 !important;
}
@media (max-width: 767px) {
    body.dm-resellerclub-dns-live-feed-1113 .dm-dns-zone-ns-notice-1691 {
        align-items: stretch;
        flex-direction: column;
    }
    body.dm-resellerclub-dns-live-feed-1113 .dm-dns-zone-ns-actions-1695 {
        align-items: stretch;
        flex-direction: column;
        width: 100%;
    }
    body.dm-resellerclub-dns-live-feed-1113 .dm-dns-zone-ns-manage-1691,
    body.dm-resellerclub-dns-live-feed-1113 .dm-dns-zone-ns-use-root-1695 {
        width: 100%;
    }
    body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-head-1113 {
        align-items: flex-start;
        flex-direction: column;
    }
    body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-spacer-1113 {
        display: none;
    }
}
</style>
<script id="dm-resellerclub-dns-live-feed-1113-js">
(function () {
    'use strict';

    var domainName = {$domainJson};
    var nativeUrl = {$nativeUrlJson};
    var nativeLiveUrl = {$nativeLiveUrlJson};
    var domainNameserversUrl = {$domainNameserversUrlJson};
    var legacyUrl = {$legacyUrlJson};
    var showFallbackLinks = {$showFallbackLinksJson};
    var nativeDirectMode = {$nativeDirectModeJson};

    function ready(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn, { once: true });
        } else {
            fn();
        }
    }

    function text(node) {
        return (node ? node.textContent : '').replace(/\s+/g, ' ').trim();
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function normalizeNsHost1695(value) {
        return String(value == null ? '' : value).trim().toLowerCase().replace(/\.+$/, '');
    }

    function collectUserRootNs1695(form, table) {
        if (!form || !table || !table.tBodies.length) { return []; }
        var rootHosts1695 = { '': true, '@': true };
        var zone1695 = normalizeNsHost1695(domainName);
        if (zone1695 !== '') { rootHosts1695[zone1695] = true; }

        // The URL can omit the domain name on the native route. The protected
        // ns5–ns8 rows are provider-managed root rows, so their displayed Host
        // gives us the page's root-host representation without guessing.
        Array.prototype.slice.call(table.tBodies[0].rows).forEach(function (row1695) {
            if (!isProviderZoneNs1691(row1695)) { return; }
            var providerHostField1695 = row1695.querySelector('input[name="dnsrecordhost[]"]');
            var providerHost1695 = normalizeNsHost1695(providerHostField1695 ? providerHostField1695.value : '');
            rootHosts1695[providerHost1695] = true;
        });

        var seen1695 = {};
        var values1695 = [];
        Array.prototype.slice.call(table.tBodies[0].rows).forEach(function (row1695) {
            if (isProviderZoneNs1691(row1695)) { return; }
            var typeField1695 = row1695.querySelector('select[name="dnsrecordtype[]"], input[name="dnsrecordtype[]"]');
            var hostField1695 = row1695.querySelector('input[name="dnsrecordhost[]"]');
            var addressField1695 = row1695.querySelector('input[name="dnsrecordaddress[]"], textarea[name="dnsrecordaddress[]"]');
            var type1695 = typeField1695 ? String(typeField1695.value || '').trim().toUpperCase() : '';
            var host1695 = normalizeNsHost1695(hostField1695 ? hostField1695.value : '');
            var value1695 = addressField1695 ? String(addressField1695.value || '').trim() : '';
            var key1695 = normalizeNsHost1695(value1695);
            if (type1695 !== 'NS' || !rootHosts1695[host1695] || key1695 === '' || seen1695[key1695]) { return; }
            seen1695[key1695] = true;
            values1695.push(value1695.replace(/\.+$/, ''));
        });

        // WHMCS's working Domain Settings form has five nameserver fields.
        return values1695.slice(0, 5);
    }

    function bindRootNsDelegationHandoff1696() {
        if (document.documentElement.getAttribute('data-dm-root-ns-handoff-1696') === '1') {
            return;
        }
        document.documentElement.setAttribute('data-dm-root-ns-handoff-1696', '1');

        document.addEventListener('click', function (event1696) {
            var link1696 = event1696.target && event1696.target.closest
                ? event1696.target.closest('.dm-dns-zone-ns-use-root-1695')
                : null;
            if (!link1696) {
                return;
            }

            try {
                var values1696 = JSON.parse(link1696.getAttribute('data-dm-root-ns-values-1696') || '[]');
                if (!Array.isArray(values1696) || !values1696.length) {
                    return;
                }
                var destination1696 = new URL(link1696.href, window.location.href);
                var domainId1696 = String(destination1696.searchParams.get('id') || '');
                window.sessionStorage.setItem('dmRootNsPrefill1696', JSON.stringify({
                    domainId: domainId1696,
                    values: values1696.slice(0, 5),
                    createdAt: Date.now()
                }));
            } catch (storageError1696) {
                // Keep the normal link navigation even if sessionStorage is unavailable.
            }
        }, true);
    }

    function normalizeProviderNs1691(value) {
        return String(value == null ? '' : value).trim().toLowerCase().replace(/\.+$/, '');
    }

    function isProviderZoneNs1691(row) {
        if (!row) { return false; }
        var typeField = row.querySelector('select[name="dnsrecordtype[]"], input[name="dnsrecordtype[]"]');
        var addressField = row.querySelector('input[name="dnsrecordaddress[]"], textarea[name="dnsrecordaddress[]"]');
        var type = typeField ? String(typeField.value || '').trim().toUpperCase() : '';
        var address = normalizeProviderNs1691(addressField ? addressField.value : '');
        if (type !== 'NS') { return false; }
        return [
            'ns5.domainmonger.com',
            'ns6.domainmonger.com',
            'ns7.domainmonger.com',
            'ns8.domainmonger.com'
        ].indexOf(address) !== -1;
    }

    function protectProviderZoneNs1691(form, table) {
        if (!form || !table || !table.tBodies.length) { return; }
        var providerRows = Array.prototype.slice.call(table.tBodies[0].rows).filter(isProviderZoneNs1691);
        if (!providerRows.length) { return; }

        providerRows.forEach(function (row) {
            if (row.dataset.dmProviderZoneNs1691 === '1') { return; }
            row.dataset.dmProviderZoneNs1691 = '1';
            row.classList.add('dm-provider-zone-ns-1691');

            Array.prototype.slice.call(row.querySelectorAll(
                'input[name="dnsrecordhost[]"], input[name="dnsrecordaddress[]"], ' +
                'textarea[name="dnsrecordaddress[]"], input[name="dnsrecordttl[]"], input[name="dnsrecordpriority[]"]'
            )).forEach(function (field) {
                field.readOnly = true;
                field.setAttribute('aria-readonly', 'true');
                field.setAttribute('title', 'Provider-managed RegistrarDNS zone nameserver record');
            });

            var typeField = row.querySelector('select[name="dnsrecordtype[]"]');
            if (typeField) {
                var originalType = String(typeField.value || 'NS');
                typeField.setAttribute('aria-disabled', 'true');
                typeField.setAttribute('title', 'Provider-managed RegistrarDNS zone nameserver record');
                typeField.addEventListener('mousedown', function (event) { event.preventDefault(); });
                typeField.addEventListener('keydown', function (event) {
                    if (event.key !== 'Tab') { event.preventDefault(); }
                });
                typeField.addEventListener('change', function () {
                    typeField.value = originalType;
                });
            }

            var checkbox = row.querySelector('.dm-dns-row-select');
            if (checkbox) {
                checkbox.checked = false;
                checkbox.disabled = true;
                checkbox.setAttribute('aria-label', 'Provider-managed RegistrarDNS zone nameserver record');
                checkbox.setAttribute('title', 'This provider-managed zone record cannot be bulk deleted here');
            }

            var addressField = row.querySelector('input[name="dnsrecordaddress[]"], textarea[name="dnsrecordaddress[]"]');
            var addressCell = addressField ? addressField.closest('td') : null;
            if (addressCell && !addressCell.querySelector('.dm-provider-zone-ns-badge-1691')) {
                var badge = document.createElement('span');
                badge.className = 'dm-provider-zone-ns-badge-1691';
                badge.textContent = 'RegistrarDNS zone NS';
                addressCell.appendChild(badge);
            }
        });

        if (!form.querySelector('.dm-dns-zone-ns-notice-1691')) {
            var rootNsValues1695 = collectUserRootNs1695(form, table);
            var rootAction1695 = rootNsValues1695.length
                ? '<a class="dm-dns-zone-ns-use-root-1695" data-dm-root-ns-values-1696="' + escapeHtml(JSON.stringify(rootNsValues1695)) + '" href="' + escapeHtml(domainNameserversUrl) + '">Use Root NS as Active Nameservers</a>'
                : '';
            var notice = document.createElement('div');
            notice.className = 'dm-dns-zone-ns-notice-1691';
            notice.innerHTML = '' +
                '<div><strong>Domain nameservers are managed separately</strong>' +
                '<span>The ns5–ns8 entries are provider-managed NS records inside the RegistrarDNS zone. Additional root or subdomain NS records are DNS-zone records and do not change active registrar delegation by themselves.</span></div>' +
                '<div class="dm-dns-zone-ns-actions-1695">' + rootAction1695 +
                '<a class="dm-dns-zone-ns-manage-1691" href="' + escapeHtml(domainNameserversUrl) + '">Manage Active Nameservers</a></div>';
            form.insertBefore(notice, form.firstChild);
        }
    }

    bindRootNsDelegationHandoff1696();

    function findModuleArea() {
        if (nativeDirectMode) {
            var nativeForm = findNativeDnsForm(document);
            if (!nativeForm) { return null; }
            return nativeForm.closest('.card, .panel, .dataTables_wrapper')
                || nativeForm.parentElement
                || null;
        }

        return document.getElementById('dm-dns-current-module-output')
            || document.querySelector('.dm-dns-module-area')
            || null;
    }

    function findNativeDnsForm(root) {
        var form = root.querySelector('form[action*="action=domaindns"]');
        if (form) { return form; }
        var sub = root.querySelector('input[name="sub"][value="save"]');
        if (sub && sub.form) { return sub.form; }
        var dnsInput = root.querySelector('input[name="dnsrecordhost[]"], input[name="dnsrecid[]"], select[name="dnsrecordtype[]"]');
        return dnsInput ? dnsInput.closest('form') : null;
    }

    function getRecordCount(form) {
        if (!form) { return 0; }

        var savedIds = Array.prototype.slice.call(form.querySelectorAll('input[name="dnsrecid[]"]'))
            .filter(function (input) {
                return String(input.value || '').trim() !== '';
            });

        if (savedIds.length) {
            return savedIds.length;
        }

        var hostInputs = Array.prototype.slice.call(form.querySelectorAll('input[name="dnsrecordhost[]"]'))
            .filter(function (input) {
                return String(input.value || '').trim() !== '';
            });

        return hostInputs.length;
    }


    function getDnsViewStateKey1514(form) {
        var domainId = '';
        var domainField = form ? form.querySelector('input[name="domainid"]') : null;

        if (domainField && String(domainField.value || '').trim()) {
            domainId = String(domainField.value || '').trim();
        }

        if (!domainId) {
            try {
                domainId = String(new URL(window.location.href).searchParams.get('domainid') || '').trim();
            } catch (error) {
                domainId = '';
            }
        }

        return 'dm-register-dns-view-state-1514:' + (domainId || 'current');
    }

    function readDnsViewState1514(form) {
        try {
            var raw = window.sessionStorage.getItem(getDnsViewStateKey1514(form));
            if (!raw) { return null; }

            var state = JSON.parse(raw);
            return state && typeof state === 'object' ? state : null;
        } catch (error) {
            return null;
        }
    }

    function persistDnsViewState1514(form, table, toolbar) {
        if (!form || !table || !toolbar) { return; }

        var filter = toolbar.querySelector('[data-dm-dns-type-filter]');
        var pageSize = toolbar.querySelector('[data-dm-dns-page-size]');
        var sortKey = String(table.dataset.dmDnsSortKey1513 || '');
        var sortDirection = String(table.dataset.dmDnsSortDir1513 || 'asc') === 'desc' ? 'desc' : 'asc';

        try {
            window.sessionStorage.setItem(getDnsViewStateKey1514(form), JSON.stringify({
                recordType: filter ? String(filter.value || '').toUpperCase() : '',
                pageSize: pageSize ? String(pageSize.value || '25').toLowerCase() : '25',
                sortKey: sortKey,
                sortDirection: sortDirection
            }));
        } catch (error) {
            // Session storage can be unavailable in restrictive browser modes.
            // DNS Add, Save, and Delete must continue normally if that happens.
        }
    }

    function restoreDnsViewState1514(form, table, toolbar) {
        var state = readDnsViewState1514(form);
        if (!state || !table || !toolbar) { return; }

        var filter = toolbar.querySelector('[data-dm-dns-type-filter]');
        var pageSize = toolbar.querySelector('[data-dm-dns-page-size]');
        var recordType = String(state.recordType || '').toUpperCase();
        var savedPageSize = String(state.pageSize || '25').toLowerCase();
        var allowedSortKeys = ['selected', 'type', 'host', 'address', 'ttl', 'priority'];
        var sortKey = String(state.sortKey || '');

        if (filter && Array.prototype.some.call(filter.options, function (option) {
            return String(option.value || '').toUpperCase() === recordType;
        })) {
            filter.value = recordType;
        }

        if (pageSize && ['25', '50', 'all'].indexOf(savedPageSize) !== -1) {
            pageSize.value = savedPageSize;
        }

        if (allowedSortKeys.indexOf(sortKey) !== -1) {
            table.dataset.dmDnsSortKey1513 = sortKey;
            table.dataset.dmDnsSortDir1513 = String(state.sortDirection || 'asc') === 'desc' ? 'desc' : 'asc';
        }
    }

    function compareDnsRows1513(a, b, table) {
        var sortKey = String(table && table.dataset.dmDnsSortKey1513 || '');
        var sortDir = String(table && table.dataset.dmDnsSortDir1513 || 'asc') === 'desc' ? -1 : 1;
        var headerRow = table && table.tHead && table.tHead.rows.length ? table.tHead.rows[0] : null;
        var header = null;
        var cellIndex = -1;

        if (headerRow) {
            Array.prototype.slice.call(headerRow.cells).some(function (candidate, index) {
                if (getHeaderKey(candidate) !== sortKey) { return false; }
                header = candidate;
                cellIndex = index;
                return true;
            });
        }

        if (!sortKey || !header) { return 0; }

        var aValue = naturalSortValue(getRowValue(a, cellIndex, header));
        var bValue = naturalSortValue(getRowValue(b, cellIndex, header));
        var result = 0;

        if (aValue.type === 'number' && bValue.type === 'number') {
            result = aValue.value - bValue.value;
        } else {
            result = String(aValue.value).localeCompare(String(bValue.value), undefined, {
                numeric: true,
                sensitivity: 'base'
            });
        }

        if (result !== 0) { return result * sortDir; }

        var aOrder = parseInt(a.getAttribute('data-dm-dns-original-order-1502'), 10);
        var bOrder = parseInt(b.getAttribute('data-dm-dns-original-order-1502'), 10);
        if (isNaN(aOrder)) { aOrder = Number.MAX_SAFE_INTEGER; }
        if (isNaN(bOrder)) { bOrder = Number.MAX_SAFE_INTEGER; }
        return aOrder - bOrder;
    }

    function getDnsQuickSearchText1545(form) {
        var input = form ? form.querySelector('[data-dm-dns-quick-search]') : null;
        return input
            ? String(input.value || '').trim().toLowerCase().replace(/\s+/g, ' ')
            : '';
    }

    function rowMatchesDnsQuickSearch1545(row, query) {
        if (!query) { return true; }

        var addressField = row ? row.querySelector('input[name="dnsrecordaddress[]"], textarea[name="dnsrecordaddress[]"]') : null;
        var typeField = row ? row.querySelector('select[name="dnsrecordtype[]"]') : null;
        var haystack = [
            fieldValue(row, 'input[name="dnsrecordhost[]"]'),
            typeField ? String(typeField.value || '') : '',
            addressField ? String(addressField.value || '') : '',
            fieldValue(row, 'input[name="dnsrecordttl[]"]'),
            fieldValue(row, 'input[name="dnsrecordpriority[]"]')
        ].join(' ').toLowerCase().replace(/\s+/g, ' ').trim();

        return query.split(' ').every(function (token) {
            return !token || haystack.indexOf(token) !== -1;
        });
    }

    function getFilteredRecordRows1489(form, table) {
        if (!table) { return []; }

        var filter = form ? form.querySelector('[data-dm-dns-type-filter]') : null;
        var value = filter ? String(filter.value || '').toUpperCase() : '';
        var searchValue = getDnsQuickSearchText1545(form);
        var rows = getRecordRows(table).filter(function (row) {
            if (isEmptyNewRow(row)) { return false; }
            if (value) {
                var select = row.querySelector('select[name="dnsrecordtype[]"]');
                if (!select || String(select.value || '').toUpperCase() !== value) { return false; }
            }
            return rowMatchesDnsQuickSearch1545(row, searchValue);
        });

        // Patch 1581: sort only this in-memory view list. The underlying DOM
        // rows and submitted DNS arrays remain in native registrar order, so
        // display sorting cannot change which live record an edit targets.
        if (String(table.dataset.dmDnsSortKey1513 || '')) {
            rows.sort(function (a, b) {
                return compareDnsRows1513(a, b, table);
            });
        }

        return rows;
    }

    function updateRecordCountBadge1488(form, table) {
        var badge = document.querySelector('#dm-dns-live-feed-page-header-1173 .dm-dns-live-feed-badge-1113');
        if (!badge) { return; }

        badge.textContent = 'Records: ' + getFilteredRecordRows1489(form, table).length;
    }

    function stripUnrelatedDnsPasswordNotice1538(message) {
        return String(message || '')
            .replace(/please enter a password length between 8 and 64 characters\.?/ig, '')
            .replace(/\s+([,.;:!?])/g, '$1')
            .replace(/([.!?]){2,}/g, '$1')
            .replace(/\s+/g, ' ')
            .replace(/^[\s.;,:-]+|[\s;,:-]+$/g, '')
            .trim();
    }

    function getMessages(root) {
        var nodes = Array.prototype.slice.call(root.querySelectorAll('.alert-success, .alert-danger, .alert-warning, .alert-error, .alert'));
        return nodes.map(text).map(stripUnrelatedDnsPasswordNotice1538).filter(function (message) {
            return !!message;
        }).slice(0, 3).join(' ');
    }

    function isDnsSaveError1538(message) {
        return /(?:\berror\b|issue was encountered|invalid|failed|required|could not|unable|denied|not saved)/i.test(String(message || ''));
    }

    function knownGenericDnsUpdateError1544() {
        return /an issue was encountered while updating the dns records\.?\s*please contact support\.?/ig;
    }

    function hasKnownGenericDnsUpdateError1544(message) {
        return knownGenericDnsUpdateError1544().test(String(message || '').replace(/\s+/g, ' ').trim());
    }

    function remainingDnsMessageAfterGenericError1544(message) {
        return String(message || '')
            .replace(knownGenericDnsUpdateError1544(), '')
            .replace(/^[\s.;,:-]+|[\s.;,:-]+$/g, '')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function normalizeDnsText1544(value, collapseWhitespace) {
        value = String(value == null ? '' : value).replace(/\\r\\n?/g, '\\n').trim();
        return collapseWhitespace === false ? value : value.replace(/\s+/g, ' ');
    }

    function normalizeDnsHost1544(value) {
        value = normalizeDnsText1544(value, true).toLowerCase();
        return value.length > 1 ? value.replace(/\.+$/, '') : value;
    }

    function normalizeDnsAddress1544(type, value) {
        type = String(type || '').toUpperCase();
        value = normalizeDnsText1544(value, type !== 'TXT');

        if (/^(?:A|AAAA)$/.test(type)) {
            return value.toLowerCase();
        }
        if (/^(?:CNAME|MX|MXE|NS)$/.test(type)) {
            value = value.toLowerCase();
            return value.length > 1 ? value.replace(/\.+$/, '') : value;
        }
        return value;
    }

    function captureDnsZoneState1544(root) {
        var table = root && root.tagName && String(root.tagName).toLowerCase() === 'table'
            ? root
            : (root ? root.querySelector('table') : null);
        var state = { count: 0, keys: [], signature: '[]' };

        if (!table || !table.tBodies.length) { return state; }

        Array.prototype.slice.call(table.tBodies[0].rows).forEach(function (row) {
            var recidField = row.querySelector('input[name="dnsrecid[]"]');
            var hostField = row.querySelector('input[name="dnsrecordhost[]"]');
            var typeField = row.querySelector('select[name="dnsrecordtype[]"]');
            var addressField = row.querySelector('input[name="dnsrecordaddress[]"], textarea[name="dnsrecordaddress[]"]');
            var ttlField = row.querySelector('input[name="dnsrecordttl[]"]');
            var priorityField = row.querySelector('input[name="dnsrecordpriority[]"]');

            if (!recidField && !hostField && !typeField && !addressField) { return; }

            var id = normalizeDnsText1544(recidField ? recidField.value : '', true);
            var type = normalizeDnsText1544(typeField ? typeField.value : '', true).toUpperCase();
            var host = normalizeDnsHost1544(hostField ? hostField.value : '');
            var address = normalizeDnsAddress1544(type, addressField ? addressField.value : '');
            var ttl = normalizeDnsText1544(ttlField ? ttlField.value : '', true);
            var priority = normalizeDnsText1544(priorityField ? priorityField.value : '', true);

            // Ignore only the native empty Add Record row. An existing root
            // record can legitimately use an empty hostname, so ID/address keep it.
            if (!id && !host && !address) { return; }

            if (!/^(?:MX|MXE|SRV)$/.test(type) || /^(?:n\/?a|not applicable)$/i.test(priority)) {
                priority = '';
            }

            state.keys.push([type, host, address, ttl, priority].join('\u001f'));
        });

        state.keys.sort();
        state.count = state.keys.length;
        state.signature = JSON.stringify(state.keys);
        return state;
    }

    function dnsZoneStatesMatch1544(expected, actual) {
        return !!expected && !!actual
            && expected.count === actual.count
            && expected.signature === actual.signature;
    }

    var dmDnsRecordTypeOptions1139 = [
        ['A', 'A'],
        ['AAAA', 'AAAA'],
        ['CNAME', 'CNAME'],
        ['MX', 'MX'],
        ['MXE', 'MXE'],
        ['NS', 'NS'],
        ['SRV', 'SRV'],
        ['TXT', 'TXT'],
        ['URL', 'URL Redirect'],
        ['FRAME', 'Frame Redirect']
    ];

    // Patch 1850: these are the only types whose add requests have complete,
    // record-specific LogicBoxes parameters and fresh live verification.
    // Keep the full list above for viewing/filtering existing records.
    var dmDnsVerifiedAddTypes1850 = {
        A: true,
        AAAA: true,
        CNAME: true,
        MX: true,
        NS: true,
        TXT: true
    };
    var dmDnsLiveTtlTypes1851 = {
        A: true,
        AAAA: true,
        CNAME: true,
        MX: true,
        NS: true,
        TXT: true,
        SRV: true
    };
    var dmDnsDirectAddTypeOptions1850 = dmDnsRecordTypeOptions1139.filter(function (pair1850) {
        return !!dmDnsVerifiedAddTypes1850[String(pair1850[0] || '').toUpperCase()];
    });

    function ensureRecordTypeOptions(select) {
        if (!select) { return; }
        var current = String(select.value || '').toUpperCase();
        var existing = {};
        Array.prototype.slice.call(select.options).forEach(function (option) {
            existing[String(option.value || option.text || '').toUpperCase()] = true;
        });
        dmDnsRecordTypeOptions1139.forEach(function (pair) {
            if (existing[pair[0]]) { return; }
            var option = document.createElement('option');
            option.value = pair[0];
            option.textContent = pair[1];
            select.appendChild(option);
        });
        if (current) { select.value = current; }
    }

    function getHeaderKey(th) {
        if (!th) { return ''; }
        if (th.querySelector('.dm-dns-select-all')) { return 'selected'; }
        var label = text(th).replace(/[\u2195\u2191\u2193]/g, '').replace(/[^a-z0-9]+/gi, ' ').trim().toLowerCase();
        if (label.indexOf('record type') !== -1 || label === 'type') { return 'type'; }
        if (label.indexOf('host') !== -1 || label.indexOf('name') !== -1) { return 'host'; }
        if (label.indexOf('value') !== -1 || label.indexOf('address') !== -1) { return 'address'; }
        if (label === 'ttl') { return 'ttl'; }
        if (label.indexOf('priority') !== -1) { return 'priority'; }
        return '';
    }

    function fieldValue(row, selector) {
        var field = row ? row.querySelector(selector) : null;
        return field ? String(field.value || '').trim() : '';
    }

    function naturalSortValue(value) {
        value = String(value || '').trim().toLowerCase();
        if (/^-?\d+(\.\d+)?$/.test(value)) {
            return { type: 'number', value: parseFloat(value) };
        }
        return { type: 'text', value: value };
    }

    function getRowValue(row, cellIndex, th) {
        var key = getHeaderKey(th);
        if (key === 'selected') {
            var rowCheckbox = row ? row.querySelector('.dm-dns-row-select') : null;
            return rowCheckbox && rowCheckbox.checked ? '1' : '0';
        }

        // Patch 1626: an existing row remains sorted by the values that were
        // loaded from the registrar. Editing a field never changes its visual
        // sort position before Save. The current field values remain in the
        // same DOM row and continue through the confirmed direct-update path.
        var savedState1626 = row && row.dmDnsOriginalState1578
            ? row.dmDnsOriginalState1578
            : null;
        if (savedState1626) {
            if (key === 'type') { return String(savedState1626.type || ''); }
            if (key === 'host') { return String(savedState1626.host || ''); }
            if (key === 'address') { return String(savedState1626.address || ''); }
            if (key === 'ttl') { return String(savedState1626.ttl || ''); }
            if (key === 'priority') { return String(savedState1626.priority || ''); }
        }

        if (key === 'type') { return fieldValue(row, 'select[name="dnsrecordtype[]"]'); }
        if (key === 'host') { return fieldValue(row, 'input[name="dnsrecordhost[]"]'); }
        if (key === 'address') { return fieldValue(row, 'input[name="dnsrecordaddress[]"]'); }
        if (key === 'ttl') { return fieldValue(row, 'input[name="dnsrecordttl[]"]'); }
        if (key === 'priority') { return fieldValue(row, 'input[name="dnsrecordpriority[]"]'); }

        var cell = row && row.cells ? row.cells[cellIndex] : null;
        if (!cell) { return ''; }
        var select = cell.querySelector('select');
        if (select) { return String(select.value || select.options[select.selectedIndex] && select.options[select.selectedIndex].text || '').trim(); }
        var field = cell.querySelector('input:not([type="checkbox"]):not([type="hidden"]), textarea');
        if (field) { return String(field.value || '').trim(); }
        return text(cell);
    }

    function isEmptyNewRow(row) {
        if (!row) { return true; }
        var recid = row.querySelector('input[name="dnsrecid[]"]');
        var host = row.querySelector('input[name="dnsrecordhost[]"]');
        var address = row.querySelector('input[name="dnsrecordaddress[]"]');
        return (!recid || String(recid.value || '').trim() === '')
            && (!host || String(host.value || '').trim() === '')
            && (!address || String(address.value || '').trim() === '');
    }

    function ensureTtlColumn(table) {
        if (!table || !table.tHead || !table.tBodies.length) { return; }
        var headerRow = table.tHead.rows[0];
        var hasTtl = Array.prototype.slice.call(headerRow.cells).some(function (th) {
            var label = text(th).replace(/[^a-z0-9]+/gi, ' ').trim().toLowerCase();
            return label === 'ttl';
        });
        if (!hasTtl) {
            var ttlTh = document.createElement('th');
            ttlTh.textContent = 'TTL';
            headerRow.insertBefore(ttlTh, headerRow.cells[headerRow.cells.length - 1] || null);
        }
        Array.prototype.slice.call(table.tBodies[0].rows).forEach(function (row) {
            if (row.querySelector('input[name="dnsrecordttl[]"]')) { return; }
            var ttlTd = document.createElement('td');
            ttlTd.innerHTML = '<input type="text" name="dnsrecordttl[]" value="14400" size="6" class="form-control dm-dns-ttl-input" />';
            row.insertBefore(ttlTd, row.cells[row.cells.length - 1] || null);
        });
    }

    function syncSelectAllAppearance1207(master) {
        if (!master) { return; }
        var active = !!master.checked || !!master.indeterminate;
        master.style.setProperty('-webkit-appearance', 'none', 'important');
        master.style.setProperty('appearance', 'none', 'important');
        master.style.setProperty('background', active ? '#f58220' : '#ffffff', 'important');
        master.style.setProperty('background-color', active ? '#f58220' : '#ffffff', 'important');
        master.style.setProperty('background-image', 'none', 'important');
        master.style.setProperty('border-color', active ? '#f58220' : '#ffffff', 'important');
        master.style.setProperty('box-shadow', active ? 'none' : 'inset 0 0 0 1px rgba(22, 58, 95, .35)', 'important');
        master.style.setProperty('opacity', '1', 'important');
        master.style.setProperty('visibility', 'visible', 'important');
        master.style.setProperty('forced-color-adjust', 'none', 'important');
    }

    function ensureCheckboxColumn(table) {
        if (!table || !table.tHead || !table.tBodies.length) { return; }
        var headerRow = table.tHead.rows[0];
        if (!headerRow.querySelector('.dm-dns-select-all')) {
            var th = document.createElement('th');
            th.className = 'dm-dns-select-col';
            th.innerHTML = '<input type="checkbox" class="dm-dns-select-all" aria-label="Select all DNS records" />';
            th.setAttribute('title', 'Sort selected records');
            headerRow.insertBefore(th, headerRow.firstChild);
        }
        Array.prototype.slice.call(table.tBodies[0].rows).forEach(function (row) {
            if (row.querySelector('.dm-dns-row-select')) { return; }
            var td = document.createElement('td');
            td.className = 'dm-dns-select-col';
            td.innerHTML = '<input type="checkbox" class="dm-dns-row-select" aria-label="Select DNS record" />';
            row.insertBefore(td, row.firstChild);
        });
        syncSelectAllAppearance1207(table.querySelector('.dm-dns-select-all'));
    }

    function getRecordRows(table) {
        if (!table || !table.tBodies.length) { return []; }
        return Array.prototype.slice.call(table.tBodies[0].rows);
    }

    function updateBulkButton(toolbar, table) {
        var btn = toolbar.querySelector('[data-dm-dns-bulk-apply]');
        var action = toolbar.querySelector('[data-dm-dns-bulk-action]');
        var rows = getRecordRows(table).filter(function (row) {
            var cb = row.querySelector('.dm-dns-row-select');
            return cb && !cb.disabled && row.style.display !== 'none' && !isEmptyNewRow(row);
        });
        var selected = rows.filter(function (row) {
            var cb = row.querySelector('.dm-dns-row-select');
            return cb && cb.checked;
        }).length;
        var hasAction = !!(action && action.value);
        var ttlSelected = !!(action && action.value === 'ttl');
        var ttlLabel = toolbar.querySelector('.dm-dns-bulk-ttl-label-1139');
        var ttlInput = toolbar.querySelector('[data-dm-dns-bulk-ttl]');

        if (action) {
            action.disabled = !selected;
        }
        if (ttlLabel) {
            ttlLabel.classList.toggle('dm-dns-bulk-ttl-visible-1553', ttlSelected);
        }
        if (ttlInput) {
            ttlInput.disabled = !selected || !ttlSelected;
        }
        if (btn) {
            btn.disabled = !selected || !hasAction;
            btn.classList.remove('dm-dns-bulk-needs-action-1142');
        }
        var master = table.querySelector('.dm-dns-select-all');
        if (master) {
            master.checked = !!rows.length && selected === rows.length;
            master.indeterminate = !!selected && selected < rows.length;
            syncSelectAllAppearance1207(master);
        }
    }

    function buildBulkToolbar(form, table) {
        if (!form || !table || form.querySelector('.dm-dns-bulk-toolbar-1139')) { return; }

        var toolbar = document.createElement('div');
        toolbar.className = 'dm-dns-bulk-toolbar-1139';
        // Patch 1490: retain the confirmed bulk-control order, add a native-path
        // Add Record modal, and keep a complete pager above the table.
        toolbar.innerHTML = '' +
            '<button type="button" data-dm-dns-bulk-apply disabled>Apply</button>' +
            '<label class="dm-dns-bulk-action-label-1139"><span class="dm-dns-visually-hidden-1139">Bulk Action</span><select data-dm-dns-bulk-action aria-label="Bulk action"><option value="">Choose Action</option><option value="delete">Delete Selected</option><option value="ttl">Change TTL</option></select></label>' +
            '<label class="dm-dns-bulk-ttl-label-1139">TTL <input type="text" data-dm-dns-bulk-ttl value="14400" /></label>' +
            '<label class="dm-dns-type-filter-label-1139"><span class="dm-dns-visually-hidden-1139">View Type</span><select data-dm-dns-type-filter aria-label="View record type"><option value="">All Records</option></select></label>' +
            '<label class="dm-dns-page-size-label-1489" title="Records per page"><select data-dm-dns-page-size aria-label="Records per page"><option value="25" selected>25</option><option value="50">50</option><option value="all">All</option></select></label>' +
            '<label class="dm-dns-quick-search-label-1545"><span class="dm-dns-visually-hidden-1139">Search Records</span><input type="search" data-dm-dns-quick-search aria-label="Search DNS records" placeholder="Search records" autocomplete="off" /></label>' +
            '<button type="button" class="dm-dns-add-record-button-1490" data-dm-dns-add-record data-dm-dns-ui-control="1">+ Add Record</button>' +
            '<span class="dm-dns-unsaved-count-1626" data-dm-dns-unsaved-count-1626 aria-live="polite" hidden></span>' +
            '<span class="dm-dns-bulk-status-1139" aria-live="polite"></span>' +
            '<div class="dm-dns-pagination-1490 dm-dns-pagination-top-1490" data-dm-dns-pager="top">' +
            '  <button type="button" data-dm-dns-page-prev data-dm-dns-ui-control="1">Previous</button>' +
            '  <span class="dm-dns-pagination-summary-1490" data-dm-dns-page-summary aria-live="polite">Page 1 of 1</span>' +
            '  <button type="button" data-dm-dns-page-next data-dm-dns-ui-control="1">Next</button>' +
            '</div>';

        var filter = toolbar.querySelector('[data-dm-dns-type-filter]');
        dmDnsRecordTypeOptions1139.forEach(function (pair) {
            var option = document.createElement('option');
            option.value = pair[0];
            option.textContent = pair[0];
            filter.appendChild(option);
        });
        form.insertBefore(toolbar, form.firstChild);
        restoreDnsViewState1514(form, table, toolbar);

        var emptyRow = getRecordRows(table).filter(isEmptyNewRow)[0] || null;
        if (emptyRow) {
            emptyRow.classList.add('dm-dns-native-add-row-1490');
            emptyRow.setAttribute('aria-hidden', 'true');
            var emptyCheckbox = emptyRow.querySelector('.dm-dns-row-select');
            if (emptyCheckbox) {
                emptyCheckbox.checked = false;
                emptyCheckbox.disabled = true;
            }
        }

        var bottomPagination = document.createElement('div');
        bottomPagination.className = 'dm-dns-pagination-1490 dm-dns-pagination-bottom-1490';
        bottomPagination.setAttribute('data-dm-dns-pager', 'bottom');
        bottomPagination.innerHTML = '' +
            '<button type="button" data-dm-dns-page-prev data-dm-dns-ui-control="1">Previous</button>' +
            '<span class="dm-dns-pagination-summary-1490" data-dm-dns-page-summary aria-live="polite">Page 1 of 1</span>' +
            '<button type="button" data-dm-dns-page-next data-dm-dns-ui-control="1">Next</button>';
        var tableContainer = table.closest('.dm-dns-live-feed-scroll-1113') || table;
        tableContainer.parentNode.insertBefore(bottomPagination, tableContainer.nextSibling);

        var pagers = Array.prototype.slice.call(form.querySelectorAll('[data-dm-dns-pager]'));

        function applyRecordPagination1490(requestedPage) {
            var matchingRows = getFilteredRecordRows1489(form, table);
            var pageSizeControl = toolbar.querySelector('[data-dm-dns-page-size]');
            var pageSizeValue = pageSizeControl ? String(pageSizeControl.value || '25').toLowerCase() : '25';
            var pageSize = pageSizeValue === 'all' ? Math.max(matchingRows.length, 1) : Math.max(parseInt(pageSizeValue, 10) || 25, 1);
            var totalPages = pageSizeValue === 'all' ? 1 : Math.max(Math.ceil(matchingRows.length / pageSize), 1);
            var storedPage = parseInt(toolbar.dataset.dmDnsCurrentPage1490 || '1', 10) || 1;
            var parsedRequestedPage = parseInt(requestedPage, 10);
            var currentPage = Number.isFinite(parsedRequestedPage) ? parsedRequestedPage : storedPage;
            currentPage = Math.min(Math.max(currentPage, 1), totalPages);
            toolbar.dataset.dmDnsCurrentPage1490 = String(currentPage);

            getRecordRows(table).forEach(function (row) {
                if (isEmptyNewRow(row)) {
                    row.classList.add('dm-dns-native-add-row-1490');
                    row.style.display = 'none';
                    return;
                }

                var matchingPosition = matchingRows.indexOf(row);
                if (matchingPosition === -1) {
                    row.style.display = 'none';
                    return;
                }

                var visible = pageSizeValue === 'all'
                    || (matchingPosition >= (currentPage - 1) * pageSize && matchingPosition < currentPage * pageSize);
                row.style.display = visible ? '' : 'none';
            });

            applyVisualRowOrder1513(table, matchingRows);

            var start = matchingRows.length ? ((currentPage - 1) * pageSize) + 1 : 0;
            var end = matchingRows.length ? Math.min(currentPage * pageSize, matchingRows.length) : 0;
            pagers.forEach(function (pager) {
                var summary = pager.querySelector('[data-dm-dns-page-summary]');
                if (summary) {
                    summary.textContent = pageSizeValue === 'all'
                        ? 'Showing all ' + matchingRows.length + ' records'
                        : 'Page ' + currentPage + ' of ' + totalPages + ' (' + start + '-' + end + ' of ' + matchingRows.length + ')';
                }

                var previous = pager.querySelector('[data-dm-dns-page-prev]');
                var next = pager.querySelector('[data-dm-dns-page-next]');
                if (previous) { previous.disabled = currentPage <= 1 || pageSizeValue === 'all'; }
                if (next) { next.disabled = currentPage >= totalPages || pageSizeValue === 'all'; }
                pager.style.display = pageSizeValue !== 'all' && totalPages > 1 ? 'flex' : 'none';
            });

            var filterValue = String(filter.value || '').toUpperCase();
            var searchValue = getDnsQuickSearchText1545(form);
            var status = toolbar.querySelector('.dm-dns-bulk-status-1139');
            if (status && !status.dataset.dmDnsManualMessage1490) {
                if (!matchingRows.length && (filterValue || searchValue)) {
                    status.textContent = 'No records match the current search and filter.';
                } else if (filterValue && searchValue) {
                    status.textContent = 'Showing ' + filterValue + ' records matching "' + searchValue + '".';
                } else if (searchValue) {
                    status.textContent = 'Showing records matching "' + searchValue + '".';
                } else if (filterValue) {
                    status.textContent = 'Showing ' + filterValue + ' records.';
                } else {
                    status.textContent = '';
                }
            }

            updateRecordCountBadge1488(form, table);
            updateBulkButton(toolbar, table);
        }

        pagers.forEach(function (pager) {
            var previousButton = pager.querySelector('[data-dm-dns-page-prev]');
            var nextButton = pager.querySelector('[data-dm-dns-page-next]');
            if (previousButton) {
                previousButton.addEventListener('click', function (event) {
                    event.preventDefault();
                    var current = parseInt(toolbar.dataset.dmDnsCurrentPage1490 || '1', 10) || 1;
                    applyRecordPagination1490(current - 1);
                });
            }
            if (nextButton) {
                nextButton.addEventListener('click', function (event) {
                    event.preventDefault();
                    var current = parseInt(toolbar.dataset.dmDnsCurrentPage1490 || '1', 10) || 1;
                    applyRecordPagination1490(current + 1);
                });
            }
        });

        toolbar.querySelector('[data-dm-dns-page-size]').addEventListener('change', function () {
            persistDnsViewState1514(form, table, toolbar);
            applyRecordPagination1490(1);
        });
        table.addEventListener('dmDnsSorted1489', function () {
            applyRecordPagination1490(parseInt(toolbar.dataset.dmDnsCurrentPage1490 || '1', 10) || 1);
        });

        var master = table.querySelector('.dm-dns-select-all');
        if (master) {
            master.addEventListener('change', function () {
                getRecordRows(table).forEach(function (row) {
                    if (row.style.display === 'none' || isEmptyNewRow(row)) { return; }
                    var cb = row.querySelector('.dm-dns-row-select');
                    if (cb && !cb.disabled) { cb.checked = master.checked; }
                });
                updateBulkButton(toolbar, table);
            });
        }
        table.addEventListener('change', function (event) {
            if (event.target && event.target.classList.contains('dm-dns-row-select')) {
                updateBulkButton(toolbar, table);
            }
            if (event.target && event.target.matches(
                'select[name="dnsrecordtype[]"], input[name="dnsrecordhost[]"], ' +
                'input[name="dnsrecordaddress[]"], textarea[name="dnsrecordaddress[]"], ' +
                'input[name="dnsrecordttl[]"], input[name="dnsrecordpriority[]"]'
            )) {
                updateDnsUnsavedIndicators1626(form, table);
                // Do not repaginate or re-sort an edited existing row. Its
                // saved identity and painted position remain fixed until Save.
            }
        });
        table.addEventListener('input', function (event) {
            if (event.target && event.target.matches(
                'input[name="dnsrecordhost[]"], input[name="dnsrecordaddress[]"], ' +
                'textarea[name="dnsrecordaddress[]"], input[name="dnsrecordttl[]"], ' +
                'input[name="dnsrecordpriority[]"]'
            )) {
                updateDnsUnsavedIndicators1626(form, table);
            }
        });
        toolbar.querySelector('[data-dm-dns-bulk-action]').addEventListener('change', function () {
            updateBulkButton(toolbar, table);
        });
        filter.addEventListener('change', function () {
            persistDnsViewState1514(form, table, toolbar);
            applyRecordPagination1490(1);
        });

        var quickSearch1545 = toolbar.querySelector('[data-dm-dns-quick-search]');
        if (quickSearch1545) {
            quickSearch1545.addEventListener('input', function () {
                applyRecordPagination1490(1);
            });
            quickSearch1545.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && quickSearch1545.value) {
                    event.preventDefault();
                    quickSearch1545.value = '';
                    applyRecordPagination1490(1);
                }
            });
        }

        form.addEventListener('submit', function () {
            persistDnsViewState1514(form, table, toolbar);
        }, true);

        function setNativeField1490(row, selector, value, eventName) {
            var field = row ? row.querySelector(selector) : null;
            if (!field) { return false; }
            field.value = value;
            field.dispatchEvent(new Event(eventName || 'input', { bubbles: true }));
            return true;
        }

        function submitNativeDnsForm1490() {
            persistDnsViewState1514(form, table, toolbar);
            var submit = form.querySelector('.dm-dns-live-feed-actions-1113 button[type="submit"], .dm-dns-live-feed-actions-1113 input[type="submit"], button[type="submit"], input[type="submit"]');
            if (typeof form.requestSubmit === 'function') {
                if (submit) {
                    form.requestSubmit(submit);
                } else {
                    form.requestSubmit();
                }
            } else if (submit && typeof submit.click === 'function') {
                submit.click();
            } else {
                form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
            }
        }

        Array.prototype.slice.call(document.querySelectorAll('.dm-dns-add-record-modal-1490')).forEach(function (existingModal) {
            existingModal.remove();
        });
        document.body.classList.remove('dm-dns-add-record-open-1490');

        var modal = document.createElement('div');
        modal.className = 'dm-dns-add-record-modal-1490';
        modal.hidden = true;
        modal.tabIndex = -1;
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-modal', 'true');
        modal.setAttribute('aria-label', 'Add DNS Records');
        modal.innerHTML = '' +
            '<div class="dm-dns-add-record-dialog-1490 dm-dns-add-record-dialog-bulk-1517" role="document">' +
            '  <div class="dm-dns-add-record-head-1490"><strong>Add DNS Records</strong><button type="button" class="dm-dns-add-record-close-1490" data-dm-dns-add-close data-dm-dns-ui-control="1" aria-label="Close">&times;</button></div>' +
            '  <div class="dm-dns-add-record-body-1490">' +
            '    <div class="dm-dns-add-record-table-wrap-1517">' +
            '      <div class="dm-dns-add-record-header-1517" aria-hidden="true"><span>Type</span><span>Host Name</span><span>Address / Value</span><span>Priority</span><span>TTL</span><span>Actions</span></div>' +
            '      <div class="dm-dns-add-record-rows-1517" data-dm-dns-add-rows></div>' +
            '    </div>' +
            '    <div class="dm-dns-add-record-message-1490" data-dm-dns-add-message aria-live="polite"></div>' +
            '    <div class="dm-dns-add-record-footer-1524">' +
            '      <div class="dm-dns-add-record-more-wrap-1517"><button type="button" class="dm-dns-add-record-more-1517" data-dm-dns-add-more data-dm-dns-ui-control="1">+ Add Another Record</button></div>' +
            '      <div class="dm-dns-add-record-actions-1490">' +
            '        <button type="button" class="dm-dns-add-record-cancel-1490" data-dm-dns-add-close data-dm-dns-ui-control="1">Cancel</button>' +
            '        <button type="button" class="dm-dns-add-record-save-1490" data-dm-dns-add-save data-dm-dns-ui-control="1">Add Record</button>' +
            '      </div>' +
            '    </div>' +
            '  </div>' +
            '</div>';
        document.body.appendChild(modal);

        var modalRows1517 = modal.querySelector('[data-dm-dns-add-rows]');
        var modalMessage1517 = modal.querySelector('[data-dm-dns-add-message]');
        var modalSave1517 = modal.querySelector('[data-dm-dns-add-save]');
        var maxBulkAddRows1517 = 50;
        var lastModalTrigger1490 = null;

        function getModalAddRows1517() {
            return Array.prototype.slice.call(modalRows1517.querySelectorAll('.dm-dns-add-record-row-1517'));
        }

        function updateModalRowPriority1517(row) {
            if (!row) { return; }
            var typeField = row.querySelector('[data-dm-dns-add-type]');
            var priority = row.querySelector('[data-dm-dns-add-priority]');
            var type = String(typeField ? typeField.value : '').toUpperCase();
            var usesPriority = type === 'MX' || type === 'MXE' || type === 'SRV';
            if (!priority) { return; }
            priority.disabled = !usesPriority;
            if (usesPriority && String(priority.value || '').trim() === '') {
                priority.value = '10';
            }
            if (!usesPriority) {
                priority.value = '';
            }
        }

        function updateModalRowControls1517() {
            var rows = getModalAddRows1517();
            rows.forEach(function (row, index) {
                row.setAttribute('data-dm-dns-add-row-number', String(index + 1));
                var remove = row.querySelector('[data-dm-dns-add-remove]');
                if (remove) {
                    remove.disabled = rows.length <= 1;
                    remove.setAttribute('aria-label', 'Remove DNS record row ' + (index + 1));
                }
                var duplicate = row.querySelector('[data-dm-dns-add-duplicate]');
                if (duplicate) {
                    duplicate.setAttribute('aria-label', 'Duplicate DNS record row ' + (index + 1));
                }
            });
            modalSave1517.textContent = rows.length === 1 ? 'Add Record' : 'Add ' + rows.length + ' Records';
            var addMore = modal.querySelector('[data-dm-dns-add-more]');
            if (addMore) {
                addMore.disabled = rows.length >= maxBulkAddRows1517;
            }
        }

        function createModalAddRow1517(values) {
            values = values || {};
            var row = document.createElement('div');
            row.className = 'dm-dns-add-record-row-1517';
            row.innerHTML = '' +
                '<label class="dm-dns-add-record-field-1490"><span>Record Type</span><select data-dm-dns-add-type aria-label="Record type"></select></label>' +
                '<label class="dm-dns-add-record-field-1490"><span>Host Name</span><input type="text" data-dm-dns-add-host autocomplete="off" placeholder="@ or blank for root" aria-label="Host name" /></label>' +
                '<label class="dm-dns-add-record-field-1490 dm-dns-add-record-field-address-1517"><span>Address / Value</span><input type="text" data-dm-dns-add-address autocomplete="off" aria-label="Address or value" /></label>' +
                '<label class="dm-dns-add-record-field-1490"><span>Priority</span><input type="text" inputmode="numeric" data-dm-dns-add-priority aria-label="Priority" /></label>' +
                '<label class="dm-dns-add-record-field-1490"><span>TTL</span><input type="text" inputmode="numeric" data-dm-dns-add-ttl aria-label="TTL" /></label>' +
                '<div class="dm-dns-add-record-row-actions-1517">' +
                '  <button type="button" class="dm-dns-add-record-duplicate-1517" data-dm-dns-add-duplicate data-dm-dns-ui-control="1">Duplicate</button>' +
                '  <button type="button" class="dm-dns-add-record-remove-1517" data-dm-dns-add-remove data-dm-dns-ui-control="1">Remove</button>' +
                '</div>';

            var typeSelect = row.querySelector('[data-dm-dns-add-type]');
            dmDnsDirectAddTypeOptions1850.forEach(function (pair) {
                var option = document.createElement('option');
                option.value = pair[0];
                option.textContent = pair[1];
                typeSelect.appendChild(option);
            });

            typeSelect.value = String(values.type || 'A').toUpperCase();
            if (!typeSelect.value && typeSelect.options.length) {
                typeSelect.selectedIndex = 0;
            }
            row.querySelector('[data-dm-dns-add-host]').value = values.host == null ? '' : String(values.host);
            row.querySelector('[data-dm-dns-add-address]').value = values.address == null ? '' : String(values.address);
            row.querySelector('[data-dm-dns-add-ttl]').value = String(values.ttl || '14400');
            row.querySelector('[data-dm-dns-add-priority]').value = values.priority == null ? '' : String(values.priority);
            updateModalRowPriority1517(row);
            return row;
        }

        function modalRowValues1517(row) {
            var priority = row.querySelector('[data-dm-dns-add-priority]');
            return {
                type: String(row.querySelector('[data-dm-dns-add-type]').value || '').toUpperCase(),
                host: String(row.querySelector('[data-dm-dns-add-host]').value || '').trim(),
                address: String(row.querySelector('[data-dm-dns-add-address]').value || '').trim(),
                ttl: String(row.querySelector('[data-dm-dns-add-ttl]').value || '').trim(),
                priority: priority && !priority.disabled ? String(priority.value || '').trim() : ''
            };
        }

        function appendModalAddRow1517(values, focusNewRow) {
            if (getModalAddRows1517().length >= maxBulkAddRows1517) {
                modalMessage1517.textContent = 'A maximum of ' + maxBulkAddRows1517 + ' records can be added at one time.';
                return null;
            }
            var row = createModalAddRow1517(values);
            modalRows1517.appendChild(row);
            updateModalRowControls1517();
            if (focusNewRow) {
                window.setTimeout(function () {
                    var host = row.querySelector('[data-dm-dns-add-host]');
                    if (host) { host.focus(); }
                }, 0);
            }
            return row;
        }

        function resetModalAddRows1517() {
            emptyRow = getRecordRows(table).filter(isEmptyNewRow)[0] || emptyRow;
            var nativeType = emptyRow ? emptyRow.querySelector('select[name="dnsrecordtype[]"]') : null;
            var nativeTtl = emptyRow ? emptyRow.querySelector('input[name="dnsrecordttl[]"]') : null;
            var selectedFilter = String(filter.value || '').toUpperCase();
            var requestedDefaultType1850 = selectedFilter
                || (nativeType ? String(nativeType.value || '').toUpperCase() : '')
                || 'A';
            var defaultType = dmDnsVerifiedAddTypes1850[requestedDefaultType1850]
                ? requestedDefaultType1850
                : 'A';
            var defaultTtl = nativeTtl && String(nativeTtl.value || '').trim() ? String(nativeTtl.value) : '14400';
            modalRows1517.innerHTML = '';
            appendModalAddRow1517({ type: defaultType, ttl: defaultTtl }, false);
            modalMessage1517.textContent = '';
        }

        function openAddRecordModal1490(trigger) {
            resetModalAddRows1517();
            lastModalTrigger1490 = trigger || null;
            modal.hidden = false;
            document.body.classList.add('dm-dns-add-record-open-1490');
            window.setTimeout(function () {
                var host = modal.querySelector('[data-dm-dns-add-host]');
                if (host) { host.focus(); }
            }, 20);
        }

        function closeAddRecordModal1490() {
            modal.hidden = true;
            document.body.classList.remove('dm-dns-add-record-open-1490');
            if (lastModalTrigger1490 && typeof lastModalTrigger1490.focus === 'function') {
                lastModalTrigger1490.focus();
            }
        }

        toolbar.querySelector('[data-dm-dns-add-record]').addEventListener('click', function () {
            openAddRecordModal1490(this);
        });
        Array.prototype.slice.call(modal.querySelectorAll('[data-dm-dns-add-close]')).forEach(function (button) {
            button.addEventListener('click', closeAddRecordModal1490);
        });
        modal.addEventListener('click', function (event) {
            if (event.target === modal) { closeAddRecordModal1490(); }
        });
        modal.addEventListener('keydown', function (event) {
            if (!modal.hidden && event.key === 'Escape') {
                event.preventDefault();
                closeAddRecordModal1490();
            }
        });

        modal.addEventListener('change', function (event) {
            if (event.target && event.target.matches('[data-dm-dns-add-type]')) {
                updateModalRowPriority1517(event.target.closest('.dm-dns-add-record-row-1517'));
            }
        });

        modal.querySelector('[data-dm-dns-add-more]').addEventListener('click', function () {
            var rows = getModalAddRows1517();
            var previous = rows.length ? modalRowValues1517(rows[rows.length - 1]) : { type: 'A', ttl: '14400' };
            modalMessage1517.textContent = '';
            appendModalAddRow1517({ type: previous.type, ttl: previous.ttl }, true);
        });

        modalRows1517.addEventListener('click', function (event) {
            var duplicate = event.target.closest('[data-dm-dns-add-duplicate]');
            var remove = event.target.closest('[data-dm-dns-add-remove]');
            var row = event.target.closest('.dm-dns-add-record-row-1517');
            if (!row) { return; }

            if (duplicate) {
                event.preventDefault();
                if (getModalAddRows1517().length >= maxBulkAddRows1517) {
                    modalMessage1517.textContent = 'A maximum of ' + maxBulkAddRows1517 + ' records can be added at one time.';
                    return;
                }
                modalMessage1517.textContent = '';
                var clone = createModalAddRow1517(modalRowValues1517(row));
                row.parentNode.insertBefore(clone, row.nextSibling);
                updateModalRowControls1517();
                var cloneHost = clone.querySelector('[data-dm-dns-add-host]');
                if (cloneHost) { cloneHost.focus(); }
                return;
            }

            if (remove) {
                event.preventDefault();
                if (getModalAddRows1517().length <= 1) { return; }
                row.remove();
                modalMessage1517.textContent = '';
                updateModalRowControls1517();
            }
        });

        function validateModalRecords1517() {
            var records = [];
            var firstInvalid = null;
            var errorMessage = '';

            getModalAddRows1517().forEach(function (row, index) {
                row.classList.remove('dm-dns-add-record-row-error-1517');
                if (errorMessage) { return; }

                var record = modalRowValues1517(row);
                if (!record.type || !record.address) {
                    errorMessage = 'Row ' + (index + 1) + ': choose a record type and enter an Address / Value.';
                    firstInvalid = !record.type ? row.querySelector('[data-dm-dns-add-type]') : row.querySelector('[data-dm-dns-add-address]');
                } else if (!/^\d+$/.test(record.ttl) || parseInt(record.ttl, 10) < 7200) {
                    errorMessage = 'Row ' + (index + 1) + ': TTL must be at least 7200 seconds.';
                    firstInvalid = row.querySelector('[data-dm-dns-add-ttl]');
                } else if (record.priority !== '' && !/^\d+$/.test(record.priority)) {
                    errorMessage = 'Row ' + (index + 1) + ': Priority must be a whole number.';
                    firstInvalid = row.querySelector('[data-dm-dns-add-priority]');
                }

                if (errorMessage) {
                    row.classList.add('dm-dns-add-record-row-error-1517');
                    return;
                }
                records.push(record);
            });

            if (errorMessage) {
                modalMessage1517.textContent = errorMessage;
                if (firstInvalid && typeof firstInvalid.focus === 'function') {
                    firstInvalid.focus();
                }
                return null;
            }

            modalMessage1517.textContent = '';
            return records;
        }

        function prepareNativeAddRow1517(row) {
            if (!row) { return; }
            row.classList.add('dm-dns-native-add-row-1490');
            row.setAttribute('aria-hidden', 'true');
            row.setAttribute('data-dm-dns-new-row-1502', '1');
            row.removeAttribute('data-dm-dns-original-order-1502');
            row.removeAttribute('data-dm-dns-visual-order-1513');
            row.style.removeProperty('transform');
            row.style.removeProperty('position');
            row.style.removeProperty('z-index');
            row.style.display = 'none';
            Array.prototype.slice.call(row.querySelectorAll('[id]')).forEach(function (node) {
                node.removeAttribute('id');
            });
            Array.prototype.slice.call(row.querySelectorAll('input[name="dnsrecid[]"]')).forEach(function (field) {
                field.value = '';
            });
            var checkbox = row.querySelector('.dm-dns-row-select');
            if (checkbox) {
                checkbox.checked = false;
                checkbox.disabled = true;
            }
        }

        function populateNativeAddRows1517(records) {
            emptyRow = getRecordRows(table).filter(isEmptyNewRow)[0] || emptyRow;
            if (!emptyRow || !table.tBodies.length) { return false; }

            var template = emptyRow.cloneNode(true);
            records.forEach(function (record, index) {
                var nativeRow = index === 0 ? emptyRow : template.cloneNode(true);
                if (index > 0) {
                    table.tBodies[0].appendChild(nativeRow);
                }
                prepareNativeAddRow1517(nativeRow);
                var nativeType = nativeRow.querySelector('select[name="dnsrecordtype[]"]');
                ensureRecordTypeOptions(nativeType);
                setNativeField1490(nativeRow, 'select[name="dnsrecordtype[]"]', record.type, 'change');
                setNativeField1490(nativeRow, 'input[name="dnsrecordhost[]"]', record.host, 'input');
                setNativeField1490(nativeRow, 'input[name="dnsrecordaddress[]"]', record.address, 'input');
                setNativeField1490(nativeRow, 'input[name="dnsrecordttl[]"]', record.ttl, 'input');
                setNativeField1490(nativeRow, 'input[name="dnsrecordpriority[]"]', record.priority, 'input');
            });
            return true;
        }

        modalSave1517.addEventListener('click', function () {
            var saveButton = this;
            var records = validateModalRecords1517();
            if (!records || !records.length) { return; }

            if (!populateNativeAddRows1517(records)) {
                modalMessage1517.textContent = 'The native Add Record row could not be found. Reload the page and try again.';
                return;
            }

            var status = toolbar.querySelector('.dm-dns-bulk-status-1139');
            if (status) {
                status.dataset.dmDnsManualMessage1490 = '1';
                status.textContent = records.length === 1 ? 'Adding DNS record…' : 'Adding ' + records.length + ' DNS records…';
            }
            saveButton.disabled = true;
            closeAddRecordModal1490();
            submitNativeDnsForm1490();
            window.setTimeout(function () { saveButton.disabled = false; }, 5000);
        });

        toolbar.querySelector('[data-dm-dns-bulk-apply]').addEventListener('click', function () {
            var action = toolbar.querySelector('[data-dm-dns-bulk-action]').value;
            var ttl = toolbar.querySelector('[data-dm-dns-bulk-ttl]').value;
            var rows = getRecordRows(table).filter(function (row) {
                var cb = row.querySelector('.dm-dns-row-select');
                return cb && !cb.disabled && cb.checked && row.style.display !== 'none';
            });
            var status = toolbar.querySelector('.dm-dns-bulk-status-1139');
            if (!rows.length) { return; }
            if (status) { delete status.dataset.dmDnsManualMessage1490; }
            if (!action) {
                if (status) { status.textContent = 'Choose a bulk action first.'; }
                updateBulkButton(toolbar, table);
                return;
            }
            if (action === 'ttl') {
                var ttl1851 = String(ttl == null ? '' : ttl).trim();
                var ttlNumber1851 = /^\d+$/.test(ttl1851) ? parseInt(ttl1851, 10) : 0;

                if (form.dataset.dmDnsLiveTtlReady1851 !== '1') {
                    if (status) {
                        status.dataset.dmDnsManualMessage1490 = '1';
                        status.textContent = form.dataset.dmDnsLiveTtlReady1851 === 'error'
                            ? 'Live NEO TTL values are unavailable. Reload the page before changing TTL.'
                            : 'Wait for the live NEO TTL values to finish loading.';
                    }
                    return;
                }

                if (ttlNumber1851 < 7200) {
                    if (status) {
                        status.dataset.dmDnsManualMessage1490 = '1';
                        status.textContent = 'Enter a TTL of at least 7200 seconds. No records were changed.';
                    }
                    updateBulkButton(toolbar, table);
                    return;
                }

                var unavailableRow1851 = rows.find(function (row1851) {
                    var input1851 = row1851.querySelector('input[name="dnsrecordttl[]"]');
                    return !input1851 || input1851.getAttribute('data-dm-dns-live-ttl-loaded-1851') !== '1';
                });
                if (unavailableRow1851) {
                    if (status) {
                        status.dataset.dmDnsManualMessage1490 = '1';
                        status.textContent = 'A selected record does not provide a verified live TTL. No records were changed.';
                    }
                    return;
                }

                // Keep this bulk request separate from any earlier manual row
                // edits so its verified reload cannot discard unrelated work.
                if (updateDnsUnsavedIndicators1626(form, table) > 0) {
                    if (status) {
                        status.dataset.dmDnsManualMessage1490 = '1';
                        status.textContent = 'Save or cancel the existing unsaved DNS changes before applying a bulk TTL.';
                    }
                    updateBulkButton(toolbar, table);
                    return;
                }

                var ttlChanges1851 = [];
                rows.forEach(function (row) {
                    var input = row.querySelector('input[name="dnsrecordttl[]"]');
                    if (!input) { return; }

                    input.value = String(ttlNumber1851);
                    var original1851 = row.dmDnsOriginalState1578 || null;
                    var current1851 = captureDnsRowState1578(row);
                    if (original1851 && !dnsRowStatesEqual1578(original1851, current1851)) {
                        ttlChanges1851.push({ original: original1851, current: current1851 });
                    }
                });
                updateDnsUnsavedIndicators1626(form, table);

                if (!ttlChanges1851.length) {
                    if (status) {
                        status.dataset.dmDnsManualMessage1490 = '1';
                        status.textContent = rows.length === 1
                            ? 'The selected record already uses that TTL.'
                            : 'The selected records already use that TTL.';
                    }
                    updateBulkButton(toolbar, table);
                    return;
                }

                var identityCheck1851 = validateStableDnsIdentity1538(form);
                if (!identityCheck1851.valid) {
                    if (status) {
                        status.dataset.dmDnsManualMessage1490 = '1';
                        status.textContent = identityCheck1851.message;
                    }
                    return;
                }

                var panel1851 = form.closest('.dm-dns-live-feed-panel-1113');
                var applyButton1851 = toolbar.querySelector('[data-dm-dns-bulk-apply]');
                if (!panel1851 || !applyButton1851) {
                    if (status) {
                        status.dataset.dmDnsManualMessage1490 = '1';
                        status.textContent = 'The safe TTL update handler is unavailable. Reload the page and try again.';
                    }
                    return;
                }

                persistDnsViewState1514(form, table, toolbar);
                if (status) {
                    status.dataset.dmDnsManualMessage1490 = '1';
                    status.textContent = '';
                }
                showMessage(
                    panel1851,
                    ttlChanges1851.length === 1 ? 'Saving selected TTL…' : 'Saving selected TTLs…',
                    ''
                );
                setButtonsBusy([applyButton1851], true);
                submitDirectDnsChanges1578(panel1851, form, ttlChanges1851, [applyButton1851]);
                return;
            }
            if (action === 'delete') {
                var recordLabel = rows.length === 1 ? 'DNS record' : 'DNS records';
                if (!window.confirm('Are you sure you want to delete ' + rows.length + ' selected ' + recordLabel + '?')) {
                    if (status) { status.textContent = 'Deletion canceled.'; }
                    updateBulkButton(toolbar, table);
                    return;
                }

                var deleteRecords1509 = rows.map(function (row) {
                    var type = row.querySelector('select[name="dnsrecordtype[]"]');
                    var host = row.querySelector('input[name="dnsrecordhost[]"]');
                    var address = row.querySelector('input[name="dnsrecordaddress[]"]');

                    return {
                        type: type ? String(type.value || '').trim() : '',
                        host: host ? String(host.value || '').trim() : '@',
                        value: address ? String(address.value || '').trim() : ''
                    };
                });

                persistDnsViewState1514(form, table, toolbar);

                var deleteData1509 = new FormData();
                var token1509 = form.querySelector('input[name="token"]');
                var domainId1509 = form.querySelector('input[name="domainid"]');
                var url1509 = new URL(window.location.href);

                deleteData1509.append('dm_register_dns_direct_delete_1509', '1');
                deleteData1509.append(
                    'domainid',
                    domainId1509 && domainId1509.value
                        ? domainId1509.value
                        : (url1509.searchParams.get('domainid') || '')
                );
                deleteData1509.append('records', JSON.stringify(deleteRecords1509));
                if (token1509 && token1509.value) {
                    deleteData1509.append('token', token1509.value);
                }

                var applyButton1509 = toolbar.querySelector('[data-dm-dns-bulk-apply]');
                var applyText1509 = applyButton1509
                    ? (applyButton1509.textContent || 'Apply')
                    : 'Apply';

                if (applyButton1509) {
                    applyButton1509.disabled = true;
                    applyButton1509.textContent = 'Deleting…';
                }
                if (status) {
                    status.dataset.dmDnsManualMessage1490 = '1';
                    status.textContent = 'Deleting ' + rows.length + ' selected ' + recordLabel + '…';
                }

                fetch(url1509.toString(), {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: deleteData1509
                })
                    .then(function (response) {
                        return response.text().then(function (text) {
                            var payload;
                            try {
                                payload = JSON.parse(text);
                            } catch (error) {
                                throw new Error('The server returned an invalid delete response.');
                            }

                            if (!response.ok || !payload || payload.ok !== true) {
                                var details = payload && Array.isArray(payload.errors)
                                    ? ' ' + payload.errors.join(' ')
                                    : '';
                                throw new Error(
                                    (payload && payload.message
                                        ? payload.message
                                        : 'The selected record was not deleted.')
                                    + details
                                );
                            }

                            return payload;
                        });
                    })
                    .then(function (payload) {
                        if (status) {
                            status.textContent = payload.message || 'DNS record deleted. Reloading…';
                        }
                        window.setTimeout(function () {
                            window.location.reload();
                        }, 250);
                    })
                    .catch(function (error) {
                        if (status) {
                            status.textContent = error && error.message
                                ? error.message
                                : 'The selected record was not deleted.';
                        }
                        if (applyButton1509) {
                            applyButton1509.disabled = false;
                            applyButton1509.textContent = applyText1509;
                        }
                        updateBulkButton(toolbar, table);
                    });

                return;
            }
            updateBulkButton(toolbar, table);
        });

        updateDnsUnsavedIndicators1626(form, table);
        applyRecordPagination1490(1);
    }

    function captureDnsRowState1578(row) {
        function value(selector) {
            var field = row ? row.querySelector(selector) : null;
            return field ? String(field.value == null ? '' : field.value).trim() : '';
        }

        return {
            recordId: value('input[name="dnsrecid[]"]'),
            type: value('select[name="dnsrecordtype[]"], input[name="dnsrecordtype[]"]').toUpperCase(),
            host: value('input[name="dnsrecordhost[]"]'),
            address: value('input[name="dnsrecordaddress[]"], textarea[name="dnsrecordaddress[]"]'),
            ttl: value('input[name="dnsrecordttl[]"]'),
            priority: value('input[name="dnsrecordpriority[]"]')
        };
    }

    function dnsRowStatesEqual1578(a, b) {
        if (!a || !b) { return false; }
        var typeA = String(a.type || '').toUpperCase();
        var typeB = String(b.type || '').toUpperCase();
        return typeA === typeB
            && normalizeDnsHost1544(a.host) === normalizeDnsHost1544(b.host)
            && normalizeDnsAddress1544(typeA, a.address) === normalizeDnsAddress1544(typeB, b.address)
            && normalizeDnsText1544(a.ttl, true) === normalizeDnsText1544(b.ttl, true)
            && normalizeDnsText1544(a.priority, true) === normalizeDnsText1544(b.priority, true);
    }

    function isDnsEditableField1627(target) {
        return !!(target && target.matches && target.matches(
            'select[name="dnsrecordtype[]"], input[name="dnsrecordhost[]"], ' +
            'input[name="dnsrecordaddress[]"], textarea[name="dnsrecordaddress[]"], ' +
            'input[name="dnsrecordttl[]"], input[name="dnsrecordpriority[]"]'
        ));
    }

    function bindDnsUnsavedCapture1627(form, table) {
        if (!form || !table || form.dataset.dmDnsUnsavedCapture1627 === '1') { return; }

        var refresh1627 = function (event) {
            if (!isDnsEditableField1627(event && event.target)) { return; }
            if (!form.contains(event.target)) { return; }

            // Run after the field's own handler has applied its final value.
            if (typeof window.requestAnimationFrame === 'function') {
                window.requestAnimationFrame(function () {
                    updateDnsUnsavedIndicators1626(form, table);
                });
            } else {
                window.setTimeout(function () {
                    updateDnsUnsavedIndicators1626(form, table);
                }, 0);
            }
        };

        // Capture phase is intentional. Some legacy Register DNS controls stop
        // bubbling events, which prevented Patch 1626's table listener from
        // seeing otherwise valid edits.
        form.addEventListener('input', refresh1627, true);
        form.addEventListener('change', refresh1627, true);
        form.addEventListener('keyup', refresh1627, true);
        form.dataset.dmDnsUnsavedCapture1627 = '1';

        window.setTimeout(function () {
            updateDnsUnsavedIndicators1626(form, table);
        }, 0);
    }

    function clearDnsErrorAfterConfirmedCancel1634(form) {
        if (!form || form.dataset.dmDnsHasUnsaved1630 === '1') { return; }

        var panel1634 = form.closest
            ? form.closest('.dm-dns-live-feed-panel-1113')
            : null;
        if (!panel1634) { return; }

        Array.prototype.slice.call(
            panel1634.querySelectorAll('.dm-dns-live-feed-message-1113.dm-error')
        ).forEach(function (message1634) {
            if (message1634.parentNode) {
                message1634.parentNode.removeChild(message1634);
            }
        });
    }

    function refreshDnsUnsavedAfterCancel1631(form, table) {
        if (!form || !table) { return; }

        var refresh1631 = function () {
            if (!document.documentElement.contains(form) || !document.documentElement.contains(table)) {
                return;
            }
            updateDnsUnsavedIndicators1626(form, table);
            clearDnsErrorAfterConfirmedCancel1634(form);
        };

        // Native reset values are applied after the click/reset event's current
        // call stack. A short set of visual-only passes also covers legacy
        // Cancel handlers that restore values in a zero-delay callback.
        window.setTimeout(refresh1631, 0);
        if (typeof window.requestAnimationFrame === 'function') {
            window.requestAnimationFrame(function () {
                window.requestAnimationFrame(refresh1631);
            });
        }
        window.setTimeout(refresh1631, 80);
    }

    function bindDnsCancelReset1631(form, table) {
        if (!form || !table || form.dataset.dmDnsCancelReset1631 === '1') { return; }

        form.addEventListener('reset', function () {
            refreshDnsUnsavedAfterCancel1631(form, table);
        }, true);

        form.addEventListener('click', function (event1631) {
            var control1631 = event1631.target && event1631.target.closest
                ? event1631.target.closest('button, input, a')
                : null;
            if (!control1631 || !form.contains(control1631)) { return; }

            var label1631 = dnsActionLabel1630(control1631);
            var isCancel1631 = /^Cancel(?: Changes)?$/i.test(label1631)
                || (control1631.classList && control1631.classList.contains('btn-secondary'));
            if (!isCancel1631) { return; }

            refreshDnsUnsavedAfterCancel1631(form, table);
        }, true);

        form.dataset.dmDnsCancelReset1631 = '1';
    }

    function dnsActionLabel1630(control) {
        if (!control) { return ''; }
        return String(
            control.tagName && control.tagName.toLowerCase() === 'input'
                ? (control.value || '')
                : (control.textContent || '')
        ).replace(/\s+/g, ' ').trim();
    }

    function setDnsActionEnabled1630(control, enabled1630) {
        if (!control) { return; }

        var tag1630 = control.tagName ? control.tagName.toLowerCase() : '';
        var isNativeControl1630 = tag1630 === 'button' || tag1630 === 'input';

        if (isNativeControl1630) {
            // A busy submit remains disabled until its request handler releases
            // it. The release path re-applies the unsaved-state decision.
            if (control.dataset.dmDnsBusy1630 === '1') {
                control.disabled = true;
            } else {
                control.disabled = !enabled1630;
            }
        } else {
            if (!control.hasAttribute('data-dm-dns-original-tabindex-1630')) {
                control.setAttribute(
                    'data-dm-dns-original-tabindex-1630',
                    control.hasAttribute('tabindex') ? String(control.getAttribute('tabindex')) : ''
                );
            }
            if (enabled1630) {
                control.classList.remove('dm-dns-action-disabled-1630');
                control.removeAttribute('aria-disabled');
                var originalTabindex1630 = control.getAttribute('data-dm-dns-original-tabindex-1630');
                if (originalTabindex1630 === '') {
                    control.removeAttribute('tabindex');
                } else {
                    control.setAttribute('tabindex', originalTabindex1630);
                }
            } else {
                control.classList.add('dm-dns-action-disabled-1630');
                control.setAttribute('aria-disabled', 'true');
                control.setAttribute('tabindex', '-1');
            }
        }
    }

    function syncDnsFormActions1630(form, hasUnsaved1630) {
        if (!form) { return; }
        form.dataset.dmDnsHasUnsaved1630 = hasUnsaved1630 ? '1' : '0';

        var actions1630 = form.querySelector('.dm-dns-live-feed-actions-1113');
        if (!actions1630) { return; }

        if (actions1630.dataset.dmDnsDisabledGuard1630 !== '1') {
            actions1630.addEventListener('click', function (event1630) {
                var disabledAction1630 = event1630.target && event1630.target.closest
                    ? event1630.target.closest('.dm-dns-action-disabled-1630')
                    : null;
                if (!disabledAction1630) { return; }
                event1630.preventDefault();
                event1630.stopImmediatePropagation();
            }, true);
            actions1630.dataset.dmDnsDisabledGuard1630 = '1';
        }

        Array.prototype.slice.call(actions1630.querySelectorAll('button, input, a')).forEach(function (control1630) {
            var label1630 = dnsActionLabel1630(control1630);
            var isSave1630 = control1630.matches && control1630.matches('button[type="submit"], input[type="submit"], .btn-primary')
                || /^Save(?: Changes)?$/i.test(label1630);
            var isCancel1630 = /^Cancel(?: Changes)?$/i.test(label1630)
                || (control1630.classList && control1630.classList.contains('btn-secondary'));

            if (isSave1630 || isCancel1630) {
                setDnsActionEnabled1630(control1630, !!hasUnsaved1630);
            }
        });
    }

    function updateDnsUnsavedIndicators1626(form, table) {
        if (!table || !table.tBodies.length) { return 0; }

        var changedCount1626 = 0;
        Array.prototype.slice.call(table.tBodies[0].rows).forEach(function (row) {
            var isExisting1626 = row.hasAttribute('data-dm-dns-original-order-1502');
            var original1626 = row.dmDnsOriginalState1578 || null;
            var changed1626 = !!isExisting1626
                && !!original1626
                && !dnsRowStatesEqual1578(original1626, captureDnsRowState1578(row));

            row.classList.toggle('dm-dns-unsaved-edit-1626', changed1626);

            // Patch 1628: place a purely visual badge in the changed row. It has
            // no name/value and therefore cannot enter the DNS form payload or
            // affect the preserved registrar identity / view-only sort logic.
            var rowBadge1628 = row.querySelector('[data-dm-dns-unsaved-row-badge-1628]');
            if (changed1626) {
                row.setAttribute('data-dm-dns-unsaved-edit-1626', '1');
                if (!rowBadge1628) {
                    var hostField1628 = row.querySelector('input[name="dnsrecordhost[]"]');
                    var typeField1628 = row.querySelector('select[name="dnsrecordtype[]"]');
                    var addressField1628 = row.querySelector('input[name="dnsrecordaddress[]"], textarea[name="dnsrecordaddress[]"]');
                    var anchorField1628 = hostField1628 || typeField1628 || addressField1628;
                    var anchorCell1628 = anchorField1628 && anchorField1628.closest
                        ? anchorField1628.closest('td')
                        : null;
                    if (!anchorCell1628 && row.cells && row.cells.length) {
                        anchorCell1628 = row.cells[Math.min(1, row.cells.length - 1)];
                    }
                    if (anchorCell1628) {
                        anchorCell1628.classList.add('dm-dns-unsaved-badge-cell-1629');
                        rowBadge1628 = document.createElement('span');
                        rowBadge1628.className = 'dm-dns-unsaved-row-badge-1628';
                        rowBadge1628.setAttribute('data-dm-dns-unsaved-row-badge-1628', '1');
                        rowBadge1628.textContent = 'Unsaved';
                        anchorCell1628.appendChild(rowBadge1628);
                    }
                } else if (rowBadge1628.parentElement) {
                    rowBadge1628.parentElement.classList.add('dm-dns-unsaved-badge-cell-1629');
                }
                changedCount1626 += 1;
            } else {
                row.removeAttribute('data-dm-dns-unsaved-edit-1626');
                Array.prototype.slice.call(row.querySelectorAll('.dm-dns-unsaved-badge-cell-1629')).forEach(function (cell1629) {
                    cell1629.classList.remove('dm-dns-unsaved-badge-cell-1629');
                });
                if (rowBadge1628 && rowBadge1628.parentNode) {
                    rowBadge1628.parentNode.removeChild(rowBadge1628);
                }
            }
        });

        var indicator1626 = form
            ? form.querySelector('[data-dm-dns-unsaved-count-1626]')
            : null;
        if (!indicator1626) {
            indicator1626 = document.querySelector('[data-dm-dns-unsaved-count-1626]');
        }
        if (indicator1626) {
            // Patch 1627: the later compact-controls hook rearranges the toolbar.
            // Move this badge into the visible left group once that group exists.
            var visibleLeft1627 = form ? form.querySelector('.dm-dns-top-left-1499') : null;
            if (visibleLeft1627 && indicator1626.parentNode !== visibleLeft1627) {
                var recordCount1627 = visibleLeft1627.querySelector('.dm-dns-record-count-1499');
                if (recordCount1627 && recordCount1627.nextSibling) {
                    visibleLeft1627.insertBefore(indicator1626, recordCount1627.nextSibling);
                } else if (recordCount1627) {
                    visibleLeft1627.appendChild(indicator1626);
                } else {
                    visibleLeft1627.insertBefore(indicator1626, visibleLeft1627.firstChild);
                }
            }

            indicator1626.hidden = changedCount1626 < 1;
            indicator1626.textContent = changedCount1626 === 1
                ? '1 unsaved change'
                : changedCount1626 + ' unsaved changes';
        }

        syncDnsFormActions1630(form, changedCount1626 > 0);
        return changedCount1626;
    }

    function analyzeDnsChanges1578(form) {
        var result = { changed: [], newRows: [], error: '' };
        var table = form ? form.querySelector('table') : null;
        if (!table || !table.tBodies.length) {
            result.error = 'The DNS record table is unavailable. Reload the page and try again.';
            return result;
        }

        Array.prototype.slice.call(table.tBodies[0].rows).forEach(function (row) {
            if (result.error) { return; }

            var current = captureDnsRowState1578(row);
            var isExisting = row.hasAttribute('data-dm-dns-original-order-1502');

            if (!isExisting) {
                if (current.host || current.address) {
                    result.newRows.push(current);
                }
                return;
            }

            var original = row.dmDnsOriginalState1578;
            if (!original) {
                result.error = 'The original DNS row values could not be verified. Reload the page and try again.';
                return;
            }

            if (!dnsRowStatesEqual1578(original, current)) {
                result.changed.push({ original: original, current: current });
            }
        });

        return result;
    }

    function getDnsDirectUpdateReceiptKey1580(form) {
        var config = window.dmRegisterDnsDirectUpdate1580Config || {};
        var domainId = Number(config.domainId || 0);
        if (!domainId && form) {
            var domainField = form.querySelector('input[name="domainid"], input[name="id"]');
            domainId = domainField ? Number(domainField.value || 0) : 0;
        }
        return 'dm-register-dns-direct-update-receipt-1580:' + String(domainId || 'current');
    }

    function storeDnsDirectUpdateReceipt1580(form, changes, message, source1888) {
        try {
            window.sessionStorage.setItem(getDnsDirectUpdateReceiptKey1580(form), JSON.stringify({
                createdAt: Date.now(),
                source1888: String(source1888 || 'update'),
                message: String(message || (changes.length === 1 ? 'DNS record updated.' : changes.length + ' DNS records updated.')),
                expected: changes.map(function (change) {
                    return {
                        type: String(change.type || '').toUpperCase(),
                        host: String(change.newHost || ''),
                        address: String(change.newValue || ''),
                        priority: String(change.newPriority || ''),
                        ttl: String(change.newTtl || '')
                    };
                })
            }));
            return true;
        } catch (error) {
            return false;
        }
    }

    function decodeDnsHtmlEntities1887(value1887) {
        var decoded1887 = String(value1887 == null ? '' : value1887);
        if (decoded1887.indexOf('&') === -1) { return decoded1887; }
        try {
            var textarea1887 = document.createElement('textarea');
            textarea1887.innerHTML = decoded1887;
            return textarea1887.value;
        } catch (error1887) {
            return decoded1887;
        }
    }

    function normalizeDnsTxtReceipt1887(value1887) {
        var candidate1887 = decodeDnsHtmlEntities1887(value1887).trim();
        // Receipt verification is display-only. Strip complete DNS
        // presentation-quote layers so a freshly rendered NEO TXT record can
        // match the plain value that the verified add handler submitted.
        for (var layer1887 = 0; layer1887 < 4; layer1887 += 1) {
            if (candidate1887.length < 2
                || candidate1887.charAt(0) !== '"'
                || candidate1887.charAt(candidate1887.length - 1) !== '"') {
                break;
            }
            candidate1887 = candidate1887.slice(1, -1).trim();
        }
        // Join normal multi-string TXT presentation chunks for comparison only.
        candidate1887 = candidate1887.replace(/"\s+"/g, '');
        return candidate1887;
    }

    function dnsRowMatchesReceipt1580(rowState, expected) {
        if (!rowState || !expected) { return false; }
        var receiptType1887 = String(rowState.type || '').toUpperCase();
        if (receiptType1887 !== String(expected.type || '').toUpperCase()) { return false; }
        if (normalizeDnsHost1544(rowState.host) !== normalizeDnsHost1544(expected.host)) { return false; }
        if (receiptType1887 === 'TXT') {
            if (normalizeDnsTxtReceipt1887(rowState.address) !== normalizeDnsTxtReceipt1887(expected.address)) { return false; }
        } else if (normalizeDnsAddress1544(rowState.type, rowState.address) !== normalizeDnsAddress1544(expected.type, expected.address)) {
            return false;
        }

        var expectedPriority = normalizeDnsText1544(expected.priority, true);
        if (expectedPriority && normalizeDnsText1544(rowState.priority, true) !== expectedPriority) { return false; }

        var expectedTtl = normalizeDnsText1544(expected.ttl, true);
        if (expectedTtl && normalizeDnsText1544(rowState.ttl, true) !== expectedTtl) { return false; }
        return true;
    }

    function showPendingDnsUpdateReceipt1580(panel, form) {
        if (form && form.dataset.dmDnsLiveTtlReady1851 !== '1') {
            form.dataset.dmDnsPendingReceiptAfterTtl1851 = '1';
            return;
        }

        var raw = '';
        try {
            raw = window.sessionStorage.getItem(getDnsDirectUpdateReceiptKey1580(form)) || '';
        } catch (error) {
            return;
        }
        if (!raw) { return; }

        var receipt;
        try {
            receipt = JSON.parse(raw);
        } catch (error) {
            try { window.sessionStorage.removeItem(getDnsDirectUpdateReceiptKey1580(form)); } catch (ignore) {}
            return;
        }

        if (!receipt || !Array.isArray(receipt.expected) || !receipt.expected.length
            || Date.now() - Number(receipt.createdAt || 0) > 120000) {
            try { window.sessionStorage.removeItem(getDnsDirectUpdateReceiptKey1580(form)); } catch (ignore) {}
            return;
        }

        // Patch 1889: this receipt is created only after the record-specific
        // server handler has already re-read NEO and verified the requested
        // mutation. The page reload is presentation refresh, not a second DNS
        // verification. Comparing the WHMCS-rendered row to the verified NEO
        // value caused false failures for TXT presentation/normalization.
        //
        // IMPORTANT: this does not select or identify a record and is not used
        // for any write/delete request. RecordID-first write matching and all
        // ambiguous-record fail-closed protections remain server-side and
        // unchanged.
        try { window.sessionStorage.removeItem(getDnsDirectUpdateReceiptKey1580(form)); } catch (ignore1889) {}
        showMessage(panel, String(receipt.message || 'DNS update verified.'), 'success');
    }

    function directUpdateErrorDetails1578(payload) {
        if (!payload || !Array.isArray(payload.results)) { return ''; }
        var failed = payload.results.filter(function (item) { return !item.updated; });
        if (!failed.length) { return ''; }
        return failed.map(function (item) {
            var label = [item.type, item.host, item.value].filter(function (part) { return !!part; }).join(' | ');
            return (label ? label + ': ' : '') + String(item.message || 'Update failed.');
        }).join(' ');
    }

    function submitDirectDnsChanges1578(panel, form, changes, buttons) {
        var config = window.dmRegisterDnsDirectUpdate1580Config || null;
        if (!config || !config.token || !Number(config.domainId || 0)) {
            setButtonsBusy(buttons, false);
            showMessage(panel, 'The safe DNS update handler is unavailable. Reload the page before trying again. No changes were submitted.', 'error');
            return;
        }

        var supportedTypes = { A: true, AAAA: true, CNAME: true, MX: true, NS: true, TXT: true, SRV: true };
        var payloadChanges = [];

        for (var index = 0; index < changes.length; index += 1) {
            var original = changes[index].original;
            var current = changes[index].current;

            if (!supportedTypes[current.type] || current.type !== original.type) {
                setButtonsBusy(buttons, false);
                showMessage(panel, 'Changing a DNS record Type is temporarily disabled for safety. Delete the old record and add the replacement instead.', 'error');
                return;
            }
            if (!String(current.address || '').trim()) {
                setButtonsBusy(buttons, false);
                showMessage(panel, 'A DNS record value cannot be blank. Use Delete for records you want to remove.', 'error');
                return;
            }
            var ttlText1857 = String(current.ttl == null ? '' : current.ttl).trim();
            var ttlNumber1857 = /^\d+$/.test(ttlText1857) ? parseInt(ttlText1857, 10) : 0;
            if (ttlNumber1857 < 7200) {
                setButtonsBusy(buttons, false);
                showMessage(panel, 'TTL must be at least 7200 seconds. No changes were submitted.', 'error');
                return;
            }

            var exactLiveOriginal1886 = String(original.liveOriginalAddress || '');
            var originalDisplay1886 = String(original.address || '');
            var currentDisplay1886 = String(current.address || '');
            var clientChangedTxtValue1886 = String(current.type || '').toUpperCase() === 'TXT'
                && currentDisplay1886 !== originalDisplay1886;
            var submittedNewValue1886 = current.address;

            // Patch 1886: if the live-TTL identity probe learned a more exact
            // registrar TXT representation, preserve it on Host/TTL/priority-only
            // edits. Only send the visible TXT value as a new value when the
            // client actually changed the TXT field itself. This prevents a
            // harmless display normalization from becoming an unintended DNS
            // content change.
            if (String(current.type || '').toUpperCase() === 'TXT'
                && exactLiveOriginal1886 !== ''
                && !clientChangedTxtValue1886) {
                submittedNewValue1886 = exactLiveOriginal1886;
            }

            payloadChanges.push({
                recordId: original.recordId,
                type: current.type,
                originalType: original.type,
                originalHost: original.host,
                newHost: current.host,
                // Patch 1885/1886: the exact live value is identity-only and
                // remains the strict pre-write match key.
                originalValue: String(original.liveOriginalAddress || original.address || ''),
                newValue: submittedNewValue1886,
                originalPriority: original.priority,
                newPriority: current.priority,
                originalTtl: original.ttl,
                newTtl: current.ttl
            });
        }

        var endpoint = new URL(window.location.href);
        endpoint.searchParams.set('dmdnsupdate1580', '1');
        endpoint.hash = '';

        var body = new URLSearchParams();
        body.append('dm_register_dns_direct_update_1580', '1');
        body.append('domain_id', String(config.domainId));
        body.append('dm_update_token_1580', String(config.token));
        body.append('dm_change_count_1580', String(payloadChanges.length));
        payloadChanges.forEach(function (change, changeIndex) {
            Object.keys(change).forEach(function (fieldName) {
                body.append(
                    'dm_change_' + String(changeIndex) + '_' + fieldName + '_1580',
                    String(change[fieldName] == null ? '' : change[fieldName])
                );
            });
        });

        fetch(endpoint.toString(), {
            method: 'POST',
            credentials: 'same-origin',
            cache: 'no-store',
            redirect: 'follow',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body
        }).then(function (response) {
            return response.text().then(function (responseText) {
                var payload;
                try {
                    payload = JSON.parse(responseText);
                } catch (error) {
                    throw new Error('The server returned an invalid DNS update response (HTTP ' + response.status + ').');
                }
                return { response: response, payload: payload || {} };
            });
        }).then(function (result) {
            var payload = result.payload;
            if (result.response.ok && payload.success === true) {
                storeDnsDirectUpdateReceipt1580(form, payloadChanges, payload.message || 'DNS record updated.');
                showMessage(panel, 'DNS update verified. Reloading live records…', '');
                window.setTimeout(function () { window.location.reload(); }, 80);
                return;
            }

            var details = directUpdateErrorDetails1578(payload);
            var message = String(payload.message || 'No DNS records were updated.');
            if (details) { message += ' ' + details; }

            if (Number(payload.updatedCount || 0) > 0) {
                showMessage(panel, message + ' Reloading the live zone…', 'error');
                window.setTimeout(function () { window.location.reload(); }, 900);
                return;
            }

            setButtonsBusy(buttons, false);
            showMessage(panel, message, 'error');
        }).catch(function (error) {
            setButtonsBusy(buttons, false);
            showMessage(panel, error && error.message ? error.message : 'No DNS records were updated.', 'error');
        });
    }


    function directVerifiedAddErrorDetails1686(payload1686) {
        if (!payload1686 || !Array.isArray(payload1686.results)) { return ''; }
        var failed1686 = payload1686.results.filter(function (item1686) { return !item1686.added; });
        if (!failed1686.length) { return ''; }
        return failed1686.map(function (item1686) {
            var label1686 = [item1686.type, item1686.host, item1686.value].filter(function (part1686) { return !!part1686; }).join(' | ');
            return (label1686 ? label1686 + ': ' : '') + String(item1686.message || 'Add failed.');
        }).join(' ');
    }

    function submitDirectVerifiedAdds1686(panel, form, newRows1686, buttons1686) {
        var config1686 = window.dmRegisterDnsDirectUpdate1580Config || null;
        if (!config1686 || !config1686.token || !Number(config1686.domainId || 0)) {
            setButtonsBusy(buttons1686, false);
            showMessage(panel, 'The verified DNS add handler is unavailable. Reload the page before trying again. No records were submitted.', 'error');
            return;
        }

        var records1686 = newRows1686.map(function (row1686) {
            return {
                type: String(row1686.type || '').toUpperCase(),
                host: String(row1686.host || ''),
                value: String(row1686.address || ''),
                priority: String(row1686.priority || ''),
                ttl: String(row1686.ttl || '')
            };
        });

        var endpoint1686 = new URL(window.location.href);
        endpoint1686.searchParams.set('dmdnsaddmx1685', '1');
        endpoint1686.hash = '';

        var body1686 = new URLSearchParams();
        body1686.append('dm_register_dns_direct_mx_add_1685', '1');
        body1686.append('domain_id', String(config1686.domainId));
        body1686.append('dm_update_token_1580', String(config1686.token));
        body1686.append('dm_add_count_1685', String(records1686.length));
        records1686.forEach(function (record1686, recordIndex1686) {
            body1686.append('dm_add_' + String(recordIndex1686) + '_type_1685', record1686.type);
            body1686.append('dm_add_' + String(recordIndex1686) + '_host_1685', record1686.host);
            body1686.append('dm_add_' + String(recordIndex1686) + '_value_1685', record1686.value);
            body1686.append('dm_add_' + String(recordIndex1686) + '_priority_1685', record1686.priority);
            body1686.append('dm_add_' + String(recordIndex1686) + '_ttl_1685', record1686.ttl);
        });

        fetch(endpoint1686.toString(), {
            method: 'POST',
            credentials: 'same-origin',
            cache: 'no-store',
            redirect: 'follow',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body1686
        }).then(function (response1686) {
            return response1686.text().then(function (responseText1686) {
                var payload1686;
                try {
                    payload1686 = JSON.parse(responseText1686);
                } catch (error1686) {
                    throw new Error('The server returned an invalid DNS add response (HTTP ' + response1686.status + ').');
                }
                return { response: response1686, payload: payload1686 || {} };
            });
        }).then(function (result1686) {
            var payload1686 = result1686.payload;
            if (result1686.response.ok && payload1686.success === true) {
                // Patch 1888: build the post-add receipt from the values the
                // server actually normalized and verified live, not from the
                // pre-normalized browser inputs. This is especially important
                // for TXT values, where submission normalization can remove DNS
                // presentation quotes/entity layers before the registrar write.
                var verifiedResults1888 = Array.isArray(payload1686.results)
                    ? payload1686.results.filter(function (item1888) { return item1888 && item1888.added === true; })
                    : [];
                var receiptChanges1686 = (verifiedResults1888.length === records1686.length
                    ? verifiedResults1888.map(function (item1888) {
                        return {
                            type: String(item1888.type || '').toUpperCase(),
                            newHost: String(item1888.host || ''),
                            newValue: String(item1888.value || ''),
                            newPriority: String(item1888.priority || ''),
                            newTtl: ''
                        };
                    })
                    : records1686.map(function (record1686) {
                        return {
                            type: record1686.type,
                            newHost: record1686.host,
                            newValue: record1686.value,
                            newPriority: record1686.type === 'MX' ? record1686.priority : '',
                            newTtl: ''
                        };
                    }));
                storeDnsDirectUpdateReceipt1580(form, receiptChanges1686, payload1686.message || 'DNS records added.', 'add');
                showMessage(panel, records1686.length === 1
                    ? 'DNS record added and verified. Reloading live records…'
                    : 'DNS records added and verified. Reloading live records…', '');
                window.setTimeout(function () { window.location.reload(); }, 80);
                return;
            }

            var details1686 = directVerifiedAddErrorDetails1686(payload1686);
            var message1686 = String(payload1686.message || 'No DNS records were added.');
            if (details1686) { message1686 += ' ' + details1686; }

            if (Number(payload1686.addedCount || 0) > 0) {
                showMessage(panel, message1686 + ' Reloading the live zone…', 'error');
                window.setTimeout(function () { window.location.reload(); }, 1000);
                return;
            }

            setButtonsBusy(buttons1686, false);
            showMessage(panel, message1686, 'error');
        }).catch(function (error1686) {
            setButtonsBusy(buttons1686, false);
            showMessage(panel, error1686 && error1686.message ? error1686.message : 'No DNS records were added.', 'error');
        });
    }

    function markOriginalRecordOrder1502(table) {
        if (!table || !table.tBodies.length) { return; }

        var savedIndex = 0;
        Array.prototype.slice.call(table.tBodies[0].rows).forEach(function (row) {
            var recid = row.querySelector('input[name="dnsrecid[]"]');
            var nativeRecid = recid ? String(recid.value || '').trim() : '';
            if (nativeRecid !== '') {
                if (!row.hasAttribute('data-dm-dns-original-order-1502')) {
                    row.setAttribute('data-dm-dns-original-order-1502', String(savedIndex));
                }
                row.setAttribute('data-dm-dns-native-recid-1538', nativeRecid);
                row.dmDnsOriginalState1578 = captureDnsRowState1578(row);
                row.removeAttribute('data-dm-dns-new-row-1502');
                savedIndex += 1;
            } else {
                row.setAttribute('data-dm-dns-new-row-1502', '1');
            }
        });
        table.setAttribute('data-dm-dns-original-record-count-1538', String(savedIndex));
        // This baseline is page-local only. The dm1487 values are synthetic
        // row identities and are not written back to the registrar or database.
        table.dmDnsBaseline1544 = captureDnsZoneState1544(table).signature;
    }

    function validateStableDnsIdentity1538(form) {
        if (!form) {
            return { valid: false, message: 'The DNS form is unavailable. Reload the page and try again.' };
        }

        var table = form.querySelector('table');
        if (!table || !table.tBodies.length) {
            return { valid: false, message: 'The DNS record table is unavailable. Reload the page and try again.' };
        }

        var expectedCount = parseInt(table.getAttribute('data-dm-dns-original-record-count-1538'), 10);
        if (isNaN(expectedCount) || expectedCount < 0) {
            return { valid: false, message: 'The original DNS record order could not be verified. Reload the page and try again.' };
        }

        var existingRows = [];
        var seenOrders = {};
        var seenIds = {};
        var identityError = '';

        Array.prototype.slice.call(table.tBodies[0].rows).forEach(function (row) {
            if (identityError) { return; }

            var originalOrderValue = row.getAttribute('data-dm-dns-original-order-1502');
            var hasOriginalOrder = originalOrderValue !== null && originalOrderValue !== '';
            var recidField = row.querySelector('input[name="dnsrecid[]"]');
            var currentRecid = recidField ? String(recidField.value || '').trim() : '';

            if (hasOriginalOrder) {
                var originalOrder = parseInt(originalOrderValue, 10);
                var expectedRecid = String(row.getAttribute('data-dm-dns-native-recid-1538') || '').trim();

                if (isNaN(originalOrder) || originalOrder < 0 || originalOrder >= expectedCount || seenOrders[originalOrder]) {
                    identityError = 'The original DNS record order changed before saving. Reload the page and try again.';
                    return;
                }
                if (!recidField || !currentRecid || !expectedRecid || currentRecid !== expectedRecid || seenIds[currentRecid]) {
                    identityError = 'A DNS record identifier changed before saving. No changes were submitted; reload the page and try again.';
                    return;
                }

                seenOrders[originalOrder] = true;
                seenIds[currentRecid] = true;
                existingRows.push(row);
                return;
            }

            // A native add row must never carry an existing registrar ID.
            if (currentRecid) {
                identityError = 'A new DNS row contained an existing record identifier. No changes were submitted; reload the page and try again.';
            }
        });

        if (identityError) {
            return { valid: false, message: identityError };
        }
        if (existingRows.length !== expectedCount) {
            return { valid: false, message: 'One or more original DNS record identifiers are missing. No changes were submitted; reload the page and try again.' };
        }
        for (var order = 0; order < expectedCount; order += 1) {
            if (!seenOrders[order]) {
                return { valid: false, message: 'The complete pre-sort DNS record order could not be restored. No changes were submitted; reload the page and try again.' };
            }
        }

        return { valid: true, message: '' };
    }

    function buildStableDnsFormData1503(form) {
        var data = new FormData(form);
        if (!form) { return data; }

        var table = form.querySelector('table');
        if (!table || !table.tBodies.length) { return data; }

        var dnsFieldNames = {
            'dnsrecid[]': true,
            'dnsrecordhost[]': true,
            'dnsrecordtype[]': true,
            'dnsrecordaddress[]': true,
            'dnsrecordpriority[]': true,
            'dnsrecordttl[]': true
        };

        Object.keys(dnsFieldNames).forEach(function (name) {
            data.delete(name);
        });

        var rows = Array.prototype.slice.call(table.tBodies[0].rows);
        rows.sort(function (a, b) {
            var aNew = a.getAttribute('data-dm-dns-new-row-1502') === '1';
            var bNew = b.getAttribute('data-dm-dns-new-row-1502') === '1';

            if (aNew && !bNew) { return 1; }
            if (!aNew && bNew) { return -1; }

            var aOrder = parseInt(a.getAttribute('data-dm-dns-original-order-1502'), 10);
            var bOrder = parseInt(b.getAttribute('data-dm-dns-original-order-1502'), 10);

            if (isNaN(aOrder)) { aOrder = Number.MAX_SAFE_INTEGER; }
            if (isNaN(bOrder)) { bOrder = Number.MAX_SAFE_INTEGER; }

            return aOrder - bOrder;
        });

        rows.forEach(function (row) {
            Array.prototype.slice.call(row.querySelectorAll('[name]')).forEach(function (control) {
                var name = control.getAttribute('name') || '';
                if (!dnsFieldNames[name] || control.disabled) { return; }

                var tag = control.tagName.toLowerCase();
                var type = String(control.type || '').toLowerCase();

                if ((type === 'checkbox' || type === 'radio') && !control.checked) {
                    return;
                }

                if (tag === 'select' && control.multiple) {
                    Array.prototype.slice.call(control.options).forEach(function (option) {
                        if (option.selected) {
                            data.append(name, option.value);
                        }
                    });
                    return;
                }

                data.append(name, control.value == null ? '' : control.value);
            });
        });

        return data;
    }

    function clearVisualRowOrder1513(table) {
        if (!table) { return; }
        getRecordRows(table).forEach(function (row) {
            row.style.removeProperty('transform');
            row.style.removeProperty('position');
            row.style.removeProperty('z-index');
            row.removeAttribute('data-dm-dns-visual-order-1513');
        });
    }

    function applyVisualRowOrder1513(table, orderedRows) {
        if (!table) { return; }

        clearVisualRowOrder1513(table);
        if (!String(table.dataset.dmDnsSortKey1513 || '')) { return; }

        var visibleRows = getRecordRows(table).filter(function (row) {
            return row.style.display !== 'none' && !isEmptyNewRow(row);
        });
        if (visibleRows.length < 2) { return; }

        var desiredRows = (orderedRows || []).filter(function (row) {
            return row.style.display !== 'none' && !isEmptyNewRow(row);
        });
        if (desiredRows.length !== visibleRows.length) { return; }

        // The rows keep their original DOM slots and submitted array order.
        // A transform moves only their painted, interactive position.
        void table.offsetHeight;
        var slotTops = visibleRows.map(function (row) {
            return row.getBoundingClientRect().top;
        });
        var currentTops = new Map();
        visibleRows.forEach(function (row) {
            currentTops.set(row, row.getBoundingClientRect().top);
        });

        desiredRows.forEach(function (row, desiredIndex) {
            var currentTop = currentTops.get(row);
            var targetTop = slotTops[desiredIndex];
            if (typeof currentTop !== 'number' || typeof targetTop !== 'number') { return; }

            var delta = targetTop - currentTop;
            row.setAttribute('data-dm-dns-visual-order-1513', String(desiredIndex));
            if (Math.abs(delta) < 0.5) { return; }

            row.style.setProperty('position', 'relative');
            row.style.setProperty('z-index', String(visibleRows.length - desiredIndex));
            row.style.setProperty('transform', 'translateY(' + delta + 'px)');
        });
    }


    function enableSorting(table) {
        if (!table || table.dataset.dmDnsSortingBound1513 === '1') { return; }
        table.dataset.dmDnsSortingBound1513 = '1';
        table.removeAttribute('data-dm-dns-sorting-disabled1504');

        var headers = Array.prototype.slice.call(table.querySelectorAll('thead th'));

        function updateHeaders(activeHeader, direction) {
            headers.forEach(function (th) {
                var active = th === activeHeader;
                th.classList.toggle('dm-sort-asc-1139', active && direction === 'asc');
                th.classList.toggle('dm-sort-desc-1139', active && direction === 'desc');
                th.setAttribute('aria-sort', active ? (direction === 'asc' ? 'ascending' : 'descending') : 'none');
                if (active) {
                    th.dataset.dmSortDir = direction;
                } else {
                    delete th.dataset.dmSortDir;
                }
            });
        }

        function requestSortedRefresh1513() {
            table.dispatchEvent(new CustomEvent('dmDnsSorted1489', {
                bubbles: false,
                detail: {
                    key: String(table.dataset.dmDnsSortKey1513 || ''),
                    direction: String(table.dataset.dmDnsSortDir1513 || 'asc'),
                    visualOnly: true
                }
            }));
        }

        headers.forEach(function (th) {
            var key = getHeaderKey(th);
            if (!key) {
                th.classList.remove('dm-dns-sortable-1139', 'dm-sort-asc-1139', 'dm-sort-desc-1139');
                th.style.cursor = 'default';
                return;
            }

            th.classList.add('dm-dns-sortable-1139');
            th.style.removeProperty('cursor');
            th.setAttribute('tabindex', '0');
            th.setAttribute('role', 'button');
            th.setAttribute('aria-sort', 'none');
            if (key === 'selected') {
                th.setAttribute('title', 'Sort selected records');
            } else {
                th.setAttribute('title', 'Sort by ' + text(th).replace(/[↕↑↓]/g, '').trim());
            }

            function activateSort1513(event) {
                if (event.type === 'keydown' && event.key !== 'Enter' && event.key !== ' ') { return; }
                if (event.target && event.target.closest && event.target.closest('input, select, button, a, label')) { return; }
                event.preventDefault();

                var currentKey = String(table.dataset.dmDnsSortKey1513 || '');
                var currentDir = String(table.dataset.dmDnsSortDir1513 || 'asc');
                var nextDir = currentKey === key && currentDir === 'asc' ? 'desc' : 'asc';

                table.dataset.dmDnsSortKey1513 = key;
                table.dataset.dmDnsSortDir1513 = nextDir;
                updateHeaders(th, nextDir);
                persistDnsViewState1514(table.closest('form'), table, table.closest('form') ? table.closest('form').querySelector('.dm-dns-bulk-toolbar-1139') : null);
                requestSortedRefresh1513();
            }

            th.addEventListener('click', activateSort1513);
            th.addEventListener('keydown', activateSort1513);
        });

        var restoredSortKey1514 = String(table.dataset.dmDnsSortKey1513 || '');
        var restoredSortHeader1514 = null;
        headers.some(function (th) {
            if (getHeaderKey(th) !== restoredSortKey1514) { return false; }
            restoredSortHeader1514 = th;
            return true;
        });
        if (restoredSortHeader1514) {
            updateHeaders(
                restoredSortHeader1514,
                String(table.dataset.dmDnsSortDir1513 || 'asc') === 'desc' ? 'desc' : 'asc'
            );
        }

        table.addEventListener('change', function (event) {
            // Patch 1626: record-field edits never trigger a visual re-sort.
            // Only the optional Selected column remains live because checking a
            // box does not change any DNS record identity or submitted value.
            if (String(table.dataset.dmDnsSortKey1513 || '') !== 'selected') { return; }
            var target = event.target;
            if (!target || !target.matches || !target.matches('.dm-dns-row-select')) { return; }
            requestSortedRefresh1513();
        });

        var resizeTimer1513 = null;
        window.addEventListener('resize', function () {
            if (!String(table.dataset.dmDnsSortKey1513 || '')) { return; }
            window.clearTimeout(resizeTimer1513);
            resizeTimer1513 = window.setTimeout(requestSortedRefresh1513, 80);
        });

        // Patch 1515: the toolbar restores the saved sort before this sorting
        // layer is bound. At that point the table can still be settling into
        // its final width/row heights, so the first transform calculation may
        // be cleared or measure the default row slots. Re-run the same visual-
        // only refresh after layout stabilizes. This does not move DOM rows or
        // alter any submitted registrar IDs/parallel DNS arrays.
        if (restoredSortHeader1514) {
            var refreshRestoredSort1515 = function () {
                if (!document.documentElement.contains(table)) { return; }
                if (!String(table.dataset.dmDnsSortKey1513 || '')) { return; }
                requestSortedRefresh1513();
            };

            if (typeof window.requestAnimationFrame === 'function') {
                window.requestAnimationFrame(function () {
                    window.requestAnimationFrame(refreshRestoredSort1515);
                });
            } else {
                window.setTimeout(refreshRestoredSort1515, 50);
            }

            // A short final pass covers late font/width changes in the WHMCS
            // panel without waiting for the user to interact with a row.
            window.setTimeout(refreshRestoredSort1515, 180);
        }
    }

    function normalizeRecordHeaderTypography1560(table) {
        if (!table || !table.tHead) { return; }

        Array.prototype.slice.call(table.tHead.querySelectorAll('th')).forEach(function (th) {
            th.style.setProperty('font-family', 'Arial, Helvetica, sans-serif', 'important');
            th.style.setProperty('font-size', '12px', 'important');
            th.style.setProperty('font-style', 'normal', 'important');
            th.style.setProperty('font-weight', '700', 'important');
            th.style.setProperty('letter-spacing', '.02em', 'important');
            th.style.setProperty('line-height', '1.25', 'important');
            th.style.setProperty('text-transform', 'uppercase', 'important');
            th.style.setProperty('vertical-align', 'middle', 'important');

            // Keep the select-all checkbox cell intact. The remaining native
            // header cells contain plain labels, so normalizing their text here
            // guarantees the same visible case even if a later stylesheet
            // overrides text-transform.
            if (th.querySelector('input, select, button, a')) { return; }
            var label = String(th.textContent || '').replace(/\s+/g, ' ').trim();
            if (label) {
                th.textContent = label.toUpperCase();
            }
        });
    }

    function prepareDnsLiveTtl1851(form, table) {
        if (!form || !table || !table.tBodies.length) { return 0; }

        var pendingCount1851 = 0;
        form.dataset.dmDnsLiveTtlReady1851 = '0';

        Array.prototype.slice.call(table.tBodies[0].rows).forEach(function (row1851) {
            var recid1851 = row1851.querySelector('input[name="dnsrecid[]"]');
            var ttlInput1851 = row1851.querySelector('input[name="dnsrecordttl[]"]');
            var typeInput1851 = row1851.querySelector('select[name="dnsrecordtype[]"], input[name="dnsrecordtype[]"]');
            var recordId1851 = recid1851 ? String(recid1851.value || '').trim() : '';
            var type1851 = typeInput1851 ? String(typeInput1851.value || '').trim().toUpperCase() : '';

            // Keep the native blank Add Record row's value as the default for
            // a future new record. Only saved rows need authoritative loading.
            if (!recordId1851 || !ttlInput1851) { return; }

            ttlInput1851.value = '';
            ttlInput1851.readOnly = true;

            if (dmDnsLiveTtlTypes1851[type1851]) {
                ttlInput1851.placeholder = 'Loading…';
                ttlInput1851.setAttribute('data-dm-dns-live-ttl-pending-1851', '1');
                row1851.setAttribute('data-dm-dns-live-ttl-row-1851', '1');
                pendingCount1851 += 1;
            } else {
                ttlInput1851.placeholder = 'Unavailable';
                ttlInput1851.title = 'Live TTL editing is unavailable for this record type.';
                row1851.setAttribute('data-dm-dns-live-ttl-unsupported-1851', '1');
            }
        });

        if (!pendingCount1851) {
            form.dataset.dmDnsLiveTtlReady1851 = '1';
        }

        return pendingCount1851;
    }

    function originalDnsZoneSignature1851(table) {
        var keys1851 = [];
        if (!table || !table.tBodies.length) { return '[]'; }

        Array.prototype.slice.call(table.tBodies[0].rows).forEach(function (row1851) {
            var original1851 = row1851.dmDnsOriginalState1578 || null;
            if (!original1851 || !row1851.hasAttribute('data-dm-dns-original-order-1502')) { return; }

            var type1851 = String(original1851.type || '').toUpperCase();
            var priority1851 = normalizeDnsText1544(original1851.priority, true);
            if (!/^(?:MX|MXE|SRV)$/.test(type1851) || /^(?:n\/?a|not applicable)$/i.test(priority1851)) {
                priority1851 = '';
            }

            keys1851.push([
                type1851,
                normalizeDnsHost1544(original1851.host),
                normalizeDnsAddress1544(type1851, original1851.address),
                normalizeDnsText1544(original1851.ttl, true),
                priority1851
            ].join('\u001f'));
        });

        keys1851.sort();
        return JSON.stringify(keys1851);
    }

    function failDnsLiveTtl1851(form, table, message1851) {
        if (!form || !table) { return; }

        form.dataset.dmDnsLiveTtlReady1851 = 'error';
        Array.prototype.slice.call(table.querySelectorAll('[data-dm-dns-live-ttl-pending-1851]')).forEach(function (input1851) {
            input1851.value = '';
            input1851.readOnly = true;
            input1851.placeholder = 'Unavailable';
            input1851.removeAttribute('data-dm-dns-live-ttl-pending-1851');
        });

        var status1851 = form.querySelector('.dm-dns-bulk-status-1139');
        if (status1851) {
            status1851.dataset.dmDnsManualMessage1490 = '1';
            status1851.textContent = 'Live TTL values unavailable.';
        }

        var panel1851 = form.closest ? form.closest('.dm-dns-live-feed-panel-1113') : null;
        if (panel1851) {
            showMessage(
                panel1851,
                String(message1851 || 'The live NEO TTL values could not be loaded. Reload the page before making DNS changes.'),
                'error'
            );
        }
    }

    function scheduleDnsLiveTtl1851(form, table, pendingCount1851) {
        if (!form || !table || pendingCount1851 < 1) { return; }

        var begin1851 = function (attempt1851) {
            if (!document.documentElement.contains(form)) {
                if (attempt1851 < 20) {
                    window.setTimeout(function () { begin1851(attempt1851 + 1); }, 50);
                }
                return;
            }

            var config1851 = window.dmRegisterDnsDirectUpdate1580Config || null;
            if (!config1851 || !config1851.token || !Number(config1851.domainId || 0)) {
                if (attempt1851 < 20) {
                    window.setTimeout(function () { begin1851(attempt1851 + 1); }, 50);
                    return;
                }
                failDnsLiveTtl1851(form, table, 'The safe live-TTL handler is unavailable. Reload the page before making DNS changes.');
                return;
            }

            var candidates1851 = Array.prototype.slice.call(
                table.querySelectorAll('tbody tr[data-dm-dns-live-ttl-row-1851="1"]')
            );
            if (!candidates1851.length) {
                form.dataset.dmDnsLiveTtlReady1851 = '1';
                return;
            }

            var status1851 = form.querySelector('.dm-dns-bulk-status-1139');
            if (status1851) {
                status1851.dataset.dmDnsManualMessage1490 = '1';
                status1851.textContent = 'Loading live TTL values…';
            }

            var endpoint1851 = new URL(window.location.href);
            endpoint1851.searchParams.set('dmdnsttl1851', '1');
            endpoint1851.hash = '';

            var body1851 = new URLSearchParams();
            body1851.append('dm_register_dns_live_ttl_1851', '1');
            body1851.append('domain_id', String(config1851.domainId));
            body1851.append('dm_update_token_1580', String(config1851.token));
            body1851.append('dm_ttl_probe_count_1851', String(candidates1851.length));

            candidates1851.forEach(function (row1851, index1851) {
                var original1851 = row1851.dmDnsOriginalState1578 || captureDnsRowState1578(row1851);
                var prefix1851 = 'dm_ttl_probe_' + String(index1851) + '_';
                body1851.append(prefix1851 + 'recordId_1851', String(original1851.recordId || ''));
                body1851.append(prefix1851 + 'type_1851', String(original1851.type || ''));
                body1851.append(prefix1851 + 'host_1851', String(original1851.host || ''));
                body1851.append(prefix1851 + 'value_1851', String(original1851.address || ''));
                body1851.append(prefix1851 + 'priority_1851', String(original1851.priority || ''));
            });

            fetch(endpoint1851.toString(), {
                method: 'POST',
                credentials: 'same-origin',
                cache: 'no-store',
                redirect: 'follow',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: body1851
            }).then(function (response1851) {
                return response1851.text().then(function (text1851) {
                    var payload1851;
                    try {
                        payload1851 = JSON.parse(text1851);
                    } catch (error1851) {
                        throw new Error('The server returned an invalid live-TTL response (HTTP ' + response1851.status + ').');
                    }
                    return { response: response1851, payload: payload1851 || {} };
                });
            }).then(function (result1851) {
                var payload1851 = result1851.payload;

                // Patch 1882: a confirmed legacy TXT repair must win over the
                // stale pre-repair error state returned by the same background
                // pass. Reload immediately so the page is rebuilt from the
                // cleaned NEO record before normal TTL/error handling continues.
                if (Number(payload1851.legacyTxtRepairedCount || 0) > 0) {
                    if (status1851) {
                        status1851.dataset.dmDnsManualMessage1490 = '1';
                        status1851.textContent = 'Legacy TXT formatting cleaned. Reloading…';
                    }
                    window.setTimeout(function () {
                        window.location.reload();
                    }, 100);
                    return;
                }

                if (!result1851.response.ok || payload1851.success !== true || !Array.isArray(payload1851.results)) {
                    var failed1851 = Array.isArray(payload1851.results)
                        ? payload1851.results.find(function (item1851) {
                            return !item1851.resolved
                                || (item1851.legacyTxtRepairAttempted && !item1851.legacyTxtRepaired
                                    && String(item1851.legacyTxtRepairMessage || '').trim() !== '');
                        })
                        : null;
                    throw new Error(String(
                        (failed1851 && (failed1851.legacyTxtRepairMessage || failed1851.message))
                        || payload1851.message
                        || 'The live NEO TTL values could not be loaded.'
                    ));
                }

                var resultsByIndex1851 = {};
                payload1851.results.forEach(function (item1851) {
                    resultsByIndex1851[Number(item1851.index)] = item1851;
                });

                var loadedTtls1851 = [];
                candidates1851.forEach(function (row1851, index1851) {
                    var item1851 = resultsByIndex1851[index1851];
                    var ttl1851 = item1851 && item1851.resolved
                        ? String(item1851.ttl || '').trim()
                        : '';
                    if (!/^\d+$/.test(ttl1851) || parseInt(ttl1851, 10) <= 0) {
                        throw new Error('NEO did not return a usable TTL for every DNS record.');
                    }

                    var input1851 = row1851.querySelector('input[name="dnsrecordttl[]"]');
                    if (!input1851) {
                        throw new Error('A RegistrarDNS TTL field disappeared while live values were loading.');
                    }

                    input1851.value = ttl1851;
                    input1851.readOnly = false;
                    input1851.placeholder = '';
                    input1851.removeAttribute('data-dm-dns-live-ttl-pending-1851');
                    input1851.setAttribute('data-dm-dns-live-ttl-loaded-1851', '1');
                    row1851.removeAttribute('data-dm-dns-live-ttl-row-1851');

                    if (row1851.dmDnsOriginalState1578) {
                        row1851.dmDnsOriginalState1578.ttl = ttl1851;

                        // Patch 1885: retain the exact live registrar value as
                        // identity-only state. This fixes synthetic-ID TXT rows
                        // whose WHMCS/display representation differs from the
                        // current search-records.json representation.
                        var liveOriginalValue1885 = item1851
                            ? String(item1851.liveOriginalValue == null ? '' : item1851.liveOriginalValue)
                            : '';
                        var identityMode1885 = item1851
                            ? String(item1851.identityMode == null ? '' : item1851.identityMode)
                            : '';
                        if (liveOriginalValue1885 !== '') {
                            // Patch 1886: keep the registrar's exact TXT value as
                            // identity-only state. Do NOT rewrite the visible value
                            // or the display baseline. The UI may intentionally
                            // normalize harmless TXT presentation differences (for
                            // example "k=rsa; p=..." versus "k=rsa;p=..."). A
                            // read-only identity recovery must never make an
                            // unchanged row appear dirty/unsaved.
                            row1851.dmDnsOriginalState1578.liveOriginalAddress = liveOriginalValue1885;
                        }
                    }
                    loadedTtls1851.push(ttl1851);
                });

                form.dataset.dmDnsLiveTtlReady1851 = '1';
                table.dmDnsBaseline1544 = originalDnsZoneSignature1851(table);
                updateDnsUnsavedIndicators1626(form, table);

                var uniqueTtls1851 = loadedTtls1851.filter(function (ttl1851, index1851, all1851) {
                    return all1851.indexOf(ttl1851) === index1851;
                });
                var bulkTtl1851 = form.querySelector('[data-dm-dns-bulk-ttl]');
                if (bulkTtl1851 && uniqueTtls1851.length === 1) {
                    bulkTtl1851.value = uniqueTtls1851[0];

                    // Use the zone's current live TTL as the starting value in
                    // the hidden native Add Record row as well. The modal copies
                    // this field when it opens, so it no longer starts from the
                    // template's unrelated 14400 fallback on a 7200 zone.
                    getRecordRows(table).filter(isEmptyNewRow).forEach(function (row1851) {
                        var addTtl1851 = row1851.querySelector('input[name="dnsrecordttl[]"]');
                        if (addTtl1851) {
                            addTtl1851.value = uniqueTtls1851[0];
                        }
                    });
                }

                if (status1851) {
                    delete status1851.dataset.dmDnsManualMessage1490;
                    status1851.textContent = '';
                }

                if (form.dataset.dmDnsPendingReceiptAfterTtl1851 === '1') {
                    delete form.dataset.dmDnsPendingReceiptAfterTtl1851;
                    var panel1851 = form.closest ? form.closest('.dm-dns-live-feed-panel-1113') : null;
                    if (panel1851) {
                        showPendingDnsUpdateReceipt1580(panel1851, form);
                    }
                }
            }).catch(function (error1851) {
                failDnsLiveTtl1851(
                    form,
                    table,
                    error1851 && error1851.message
                        ? error1851.message + ' Reload the page before making DNS changes.'
                        : 'The live NEO TTL values could not be loaded. Reload the page before making DNS changes.'
                );
            });
        };

        window.setTimeout(function () { begin1851(0); }, 0);
    }

    function enhanceDnsEditor(form) {
        if (!form || form.dataset.dmDnsEnhanced1139 === '1') { return; }
        form.dataset.dmDnsEnhanced1139 = '1';
        Array.prototype.slice.call(form.querySelectorAll('select[name="dnsrecordtype[]"]')).forEach(ensureRecordTypeOptions);
        var table = form.querySelector('table');
        if (!table) { return; }
        ensureCheckboxColumn(table);
        ensureTtlColumn(table);
        normalizeRecordHeaderTypography1560(table);
        var pendingTtlCount1851 = prepareDnsLiveTtl1851(form, table);
        markOriginalRecordOrder1502(table);
        buildBulkToolbar(form, table);
        bindDnsUnsavedCapture1627(form, table);
        bindDnsCancelReset1631(form, table);
        enableSorting(table);
        scheduleDnsLiveTtl1851(form, table, pendingTtlCount1851);
    }

    function normalizeForm(form) {
        form.classList.add('dm-dns-live-feed-form-1113');
        form.setAttribute('data-dm-dns-live-feed-form', '1');
        form.setAttribute('action', 'clientarea.php?action=domaindns');
        form.setAttribute('method', 'post');

        var table = form.querySelector('table');
        if (table && (!table.parentElement || !table.parentElement.classList.contains('dm-dns-live-feed-scroll-1113'))) {
            var scroll = document.createElement('div');
            scroll.className = 'dm-dns-live-feed-scroll-1113';
            table.parentNode.insertBefore(scroll, table);
            scroll.appendChild(table);
        }

        enhanceDnsEditor(form);
        protectProviderZoneNs1691(form, table);

        var buttons = Array.prototype.slice.call(form.querySelectorAll('button, input[type="submit"], a.btn'));
        if (buttons.length && !form.querySelector('.dm-dns-live-feed-actions-1113')) {
            var actions = document.createElement('div');
            actions.className = 'dm-dns-live-feed-actions-1113';
            buttons.forEach(function (btn) {
                if (btn.hasAttribute('data-dm-dns-ui-control') || btn.closest('td, th, .dm-dns-bulk-toolbar-1139, .dm-dns-pagination-1490, .dm-dns-add-record-modal-1490')) { return; }
                actions.appendChild(btn);
            });
            if (actions.children.length) {
                form.appendChild(actions);
            }
        }

        var submit = form.querySelector('button[type="submit"], input[type="submit"], .dm-dns-live-feed-actions-1113 .btn-primary');
        if (submit) {
            if (submit.tagName.toLowerCase() === 'input') {
                submit.value = submit.value || 'Save Changes';
            } else if (!text(submit)) {
                submit.textContent = 'Save Changes';
            }
        }

        return form;
    }

    function buildPageHeader() {
        var header = document.createElement('div');
        header.id = 'dm-dns-live-feed-page-header-1173';
        header.className = 'dm-dns-live-feed-head-1113';
        header.innerHTML = '' +
            '<div><strong>DNS Records</strong><span>Manage DNS records' + (domainName ? ' for ' + escapeHtml(domainName) : '') + '</span></div>' +
            '<div class="dm-dns-live-feed-badge-1113">Live DNS Management</div>';
        return header;
    }

    function buildPanel(statusHtml) {
        var diagnosticLinks = showFallbackLinks ? (
            '<div class="dm-dns-live-feed-toolbar-1113" data-dm-dns-diagnostic-links="1">' +
            '  <a href="' + escapeHtml(nativeLiveUrl) + '">Open Direct Native Editor</a>' +
            '  <a href="' + escapeHtml(legacyUrl) + '">Open Old DNS Editor</a>' +
            '</div>'
        ) : '';
        var panel = document.createElement('div');
        panel.className = 'dm-dns-live-feed-panel-1113';
        panel.innerHTML = diagnosticLinks +
            '<div class="dm-dns-live-feed-body-1113" aria-live="polite">' + (statusHtml || '<div class="dm-dns-live-feed-loading-1113" role="status">Loading DNS records…</div>') + '</div>';
        return panel;
    }

    function setPanelBody(panel, content) {
        var body = panel.querySelector('.dm-dns-live-feed-body-1113');
        if (body) {
            body.innerHTML = '';
            if (typeof content === 'string') {
                body.innerHTML = content;
            } else if (content) {
                body.appendChild(content);
            }
        }
    }

    function isDnsGuidanceMessage(message) {
        var normalized = String(message || '').replace(/\s+/g, ' ').trim().toLowerCase();
        return normalized.indexOf('point your domain to a web site by pointing to an ip address') === 0
            && normalized.indexOf('these records are also known as sub-domains') !== -1
            && !/(saved|updated|successfully|error|failed|unable)/i.test(normalized);
    }

    function showMessage(panel, message, kind) {
        var body = panel.querySelector('.dm-dns-live-feed-body-1113');
        if (!body || !message) { return; }

        if (isDnsGuidanceMessage(message)) {
            kind = 'info';
        }

        Array.prototype.slice.call(body.querySelectorAll('.dm-dns-live-feed-message-1113')).forEach(function (oldMessage) {
            oldMessage.parentNode.removeChild(oldMessage);
        });

        var div = document.createElement('div');
        div.className = 'dm-dns-live-feed-message-1113' + (kind ? ' dm-' + kind : '');
        div.setAttribute('role', kind === 'error' ? 'alert' : (kind === 'info' ? 'note' : 'status'));
        div.textContent = message;
        body.insertBefore(div, body.firstChild);
    }

    function renderFormNode(panel, form, message, kind) {
        if (!form) {
            setPanelBody(panel, '<div class="dm-dns-live-feed-message-1113 dm-error">The DNS editor could not be found. Refresh this page or use a fallback editor if needed.</div>');
            return false;
        }

        var clonedForm = form.cloneNode(true);
        normalizeForm(clonedForm);
        setPanelBody(panel, clonedForm);

        updateRecordCountBadge1488(clonedForm, clonedForm.querySelector('table'));

        if (message) {
            showMessage(panel, message, kind || 'success');
        }

        bindSubmit(panel, clonedForm);
        showPendingDnsUpdateReceipt1580(panel, clonedForm);
        return true;
    }

    function renderForm(panel, sourceDoc, message, kind) {
        return renderFormNode(panel, findNativeDnsForm(sourceDoc), message, kind);
    }

    function parseHtml(html) {
        return new DOMParser().parseFromString(html, 'text/html');
    }

    function requestNativePayload() {
        var prefetched = window.dmDnsNativePrefetch1223;

        if (prefetched && prefetched.url === nativeUrl && prefetched.promise && typeof prefetched.promise.then === 'function') {
            window.dmDnsNativePrefetch1223 = null;
            return prefetched.promise;
        }

        return fetch(nativeUrl, {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (response) {
            return response.text();
        }).then(function (html) {
            return { ok: true, html: html, doc: null };
        }).catch(function () {
            return { ok: false, html: '', doc: null };
        });
    }

    function loadNative(panel) {
        requestNativePayload().then(function (payload) {
            if (!payload || !payload.ok || !payload.html) {
                throw new Error('Native DNS response unavailable');
            }

            var doc = payload.doc || parseHtml(payload.html);
            var messages = getMessages(doc);
            renderForm(panel, doc, messages || '', messages ? 'success' : '');
        }).catch(function () {
            setPanelBody(panel, '<div class="dm-dns-live-feed-message-1113 dm-error">The DNS editor could not load inside this page. Refresh this page or use a fallback editor if needed.</div>');
        });
    }

    function setButtonsBusy(buttons, busy) {
        buttons.forEach(function (btn) {
            btn.dataset.dmDnsBusy1630 = busy ? '1' : '0';
            if (busy) {
                if (btn.dataset.dmDnsFeedOriginalText == null) {
                    btn.dataset.dmDnsFeedOriginalText = btn.tagName.toLowerCase() === 'input' ? (btn.value || '') : (btn.textContent || '');
                }
                if (btn.tagName.toLowerCase() === 'input') {
                    btn.value = 'Saving…';
                } else {
                    btn.textContent = 'Saving…';
                }
                btn.disabled = true;
            } else {
                if (btn.dataset.dmDnsFeedOriginalText != null) {
                    if (btn.tagName.toLowerCase() === 'input') {
                        btn.value = btn.dataset.dmDnsFeedOriginalText;
                    } else {
                        btn.textContent = btn.dataset.dmDnsFeedOriginalText;
                    }
                }
                var form1630 = btn.closest ? btn.closest('form') : null;
                btn.disabled = !(form1630 && form1630.dataset.dmDnsHasUnsaved1630 === '1');
            }
        });
    }

    function bindSubmit(panel, form) {
        if (!form || form.dataset.dmNativeFeedSubmitBound === '1') { return; }
        form.dataset.dmNativeFeedSubmitBound = '1';
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            if (form.dataset.dmDnsLiveTtlReady1851 !== '1') {
                showMessage(
                    panel,
                    form.dataset.dmDnsLiveTtlReady1851 === 'error'
                        ? 'Live NEO TTL values are unavailable. Reload the page before making DNS changes.'
                        : 'Live NEO TTL values are still loading. Wait for loading to finish before saving DNS changes.',
                    'error'
                );
                return;
            }

            var identityCheck1538 = validateStableDnsIdentity1538(form);
            if (!identityCheck1538.valid) {
                showMessage(panel, identityCheck1538.message, 'error');
                return;
            }

            // Build a complete normalized zone snapshot before submission. The
            // snapshot is value/count based because dm1487 IDs are synthetic and
            // are regenerated from the current native response on every load.
            var submittedState1544 = captureDnsZoneState1544(form);
            var table1544 = form.querySelector('table');
            var baselineSignature1544 = table1544 ? String(table1544.dmDnsBaseline1544 || '') : '';

            // A no-change SaveDNS call is unsafe for zones that were touched
            // during the former sorted-row mismatch period: the registrar module
            // can attempt to reconcile a synthetic identity and return
            // "Record does not exist." Nothing needs to be sent in this case.
            if (baselineSignature1544 && baselineSignature1544 === submittedState1544.signature) {
                showMessage(panel, 'DNS records are already up to date.', 'success');
                return;
            }

            var submitButton = event.submitter || form.querySelector(
                '.dm-dns-live-feed-actions-1113 button[type="submit"], ' +
                '.dm-dns-live-feed-actions-1113 input[type="submit"], ' +
                'button[type="submit"], input[type="submit"]'
            );
            var buttons = submitButton ? [submitButton] : [];
            var changeAnalysis1578 = analyzeDnsChanges1578(form);

            if (changeAnalysis1578.error) {
                showMessage(panel, changeAnalysis1578.error, 'error');
                return;
            }

            // Existing-record edits must never be submitted through WHMCS's
            // full-zone array reconciliation. On affected zones that process
            // can apply the requested edit and also rewrite unrelated rows.
            if (changeAnalysis1578.changed.length) {
                if (changeAnalysis1578.newRows.length) {
                    showMessage(panel, 'For safety, save existing record edits separately from new records. Cancel the new rows, save the edits, then add the new records.', 'error');
                    return;
                }

                showMessage(
                    panel,
                    changeAnalysis1578.changed.length === 1 ? 'Saving DNS change…' : 'Saving DNS changes…',
                    ''
                );
                setButtonsBusy(buttons, true);
                submitDirectDnsChanges1578(panel, form, changeAnalysis1578.changed, buttons);
                return;
            }

            // Patches 1849/1850: standard additions bypass WHMCS's full-zone
            // reconciliation. Unsupported additions fail closed; no existing
            // row, displayed position, dnsrecid, or dm1487 identity is ever
            // resubmitted as part of an add operation.
            var unsupportedAddRow1850 = changeAnalysis1578.newRows.find(function (row1850) {
                return !dmDnsVerifiedAddTypes1850[String(row1850.type || '').toUpperCase()];
            });

            if (unsupportedAddRow1850) {
                showMessage(
                    panel,
                    String(unsupportedAddRow1850.type || 'This record type').toUpperCase()
                        + ' additions are disabled until a verified record-specific add path is available. No records were submitted.',
                    'error'
                );
                return;
            }

            if (changeAnalysis1578.newRows.length) {
                showMessage(
                    panel,
                    changeAnalysis1578.newRows.length === 1
                        ? 'Adding and verifying DNS record…'
                        : 'Adding and verifying DNS records…',
                    ''
                );
                setButtonsBusy(buttons, true);
                submitDirectVerifiedAdds1686(panel, form, changeAnalysis1578.newRows, buttons);
                return;
            }

            // Patch 1850: every valid mutation must have returned through a
            // verified record-specific handler above. Never fall through to a
            // native full-zone SaveDNS request.
            showMessage(
                panel,
                'RegistrarDNS could not classify this change safely. Reload the page and try again. No records were submitted.',
                'error'
            );
        });
    }

    function cleanNativeDirectLayout(target) {
        if (!nativeDirectMode || !target) { return; }

        var mainColumn = target.closest(
            '.primary-content, .main-content, .col-lg-8.col-xl-9, .col-md-9, .col-md-8'
        );
        if (mainColumn) {
            mainColumn.classList.add('dm-dns-native-main-column-1229');
            mainColumn.style.setProperty('flex', '0 0 100%', 'important');
            mainColumn.style.setProperty('max-width', '100%', 'important');
            mainColumn.style.setProperty('width', '100%', 'important');
        }

        var outerRow = mainColumn && mainColumn.parentElement
            ? mainColumn.parentElement
            : target.closest('.row');
        if (outerRow) {
            Array.prototype.slice.call(outerRow.children).forEach(function (column) {
                if (column === mainColumn || (mainColumn && column.contains(mainColumn))) {
                    return;
                }
                var cls = String(column.className || '');
                var legacySidebar = /(?:^|\s)(?:sidebar|secondary-sidebar|panel-sidebar)(?:\s|$)/i.test(cls)
                    || (/col-lg-4/i.test(cls) && /col-xl-3/i.test(cls));
                if (legacySidebar) {
                    column.style.setProperty('display', 'none', 'important');
                    column.setAttribute('aria-hidden', 'true');
                }
            });
        }

        var legacySidebars = document.querySelectorAll(
            '#main-body > .container > .row > .col-lg-4.col-xl-3, '
            + '#main-body > .container-fluid > .row > .col-lg-4.col-xl-3, '
            + '#main-body .secondary-sidebar, #main-body .panel-sidebar'
        );
        for (var i = 0; i < legacySidebars.length; i++) {
            if (mainColumn && legacySidebars[i].contains(mainColumn)) { continue; }
            legacySidebars[i].style.setProperty('display', 'none', 'important');
            legacySidebars[i].setAttribute('aria-hidden', 'true');
        }
    }

    function mountDnsPanel() {
        var directForm = nativeDirectMode ? findNativeDnsForm(document) : null;
        var directMessages = nativeDirectMode ? getMessages(document) : '';
        var target = findModuleArea();
        if (!target) {
            return false;
        }

        if (nativeDirectMode) {
            target.classList.add('dm-dns-native-direct-target-1228');
            cleanNativeDirectLayout(target);
        }
        target.innerHTML = '';

        var oldHeader = document.getElementById('dm-dns-live-feed-page-header-1173');
        if (oldHeader && oldHeader.parentNode) {
            oldHeader.parentNode.removeChild(oldHeader);
        }

        var pageHeader = buildPageHeader();
        if (target.parentNode && target !== document.body) {
            target.parentNode.insertBefore(pageHeader, target);
        } else {
            target.insertBefore(pageHeader, target.firstChild);
        }

        var panel = buildPanel('');
        target.appendChild(panel);
        if (nativeDirectMode) {
            renderFormNode(panel, directForm, directMessages || '', directMessages ? 'success' : '');
        } else {
            loadNative(panel);
        }
        return true;
    }

    function boot() {
        document.body.classList.add('dm-resellerclub-dns-live-feed-1113');
        document.body.classList.add('dm-dns-unified');
        if (nativeDirectMode) {
            document.body.classList.add('dm-dns-native-direct-1228');
        }

        // In the normal WHMCS render the module area already exists when
        // DOMContentLoaded fires, so mount immediately instead of waiting for
        // the first polling interval.
        if (mountDnsPanel()) {
            return;
        }

        var tries = 0;
        var timer = window.setInterval(function () {
            tries += 1;
            if (mountDnsPanel()) {
                window.clearInterval(timer);
                return;
            }
            if (tries >= 20) {
                window.clearInterval(timer);
                var fallback = document.querySelector('#main-body .primary-content, #main-body .main-content, #main-body .col-md-9, .primary-content, .main-content') || document.body;
                fallback.innerHTML = '';
                var pageHeader = buildPageHeader();
                if (fallback.parentNode && fallback !== document.body) {
                    fallback.parentNode.insertBefore(pageHeader, fallback);
                } else {
                    fallback.insertBefore(pageHeader, fallback.firstChild);
                }
                var panel = buildPanel('');
                fallback.appendChild(panel);
                loadNative(panel);
            }
        }, 50);
    }

    ready(boot);
}());
</script>
HTML;
});
