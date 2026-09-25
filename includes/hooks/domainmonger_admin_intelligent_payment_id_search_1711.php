<?php
/**
 * Patch 1711 - Extend WHMCS Admin Intelligent Search with payment identifiers.
 *
 * Searches the normal WHMCS Admin general search box for:
 * - Transaction IDs in tblaccounts (normal applied invoice/client transactions)
 * - Transaction IDs in tbltransaction_history (newer gateway transaction history)
 * - Transaction IDs embedded in tblcredit descriptions (payments converted to credit)
 * - Subscription IDs on Domains (tbldomains.subscriptionid)
 * - Subscription IDs on Products/Services (tblhosting.subscriptionid)
 * - Historical Subscription ID references in the Gateway Log (tblgatewaylog.data)
 *
 * Historical Gateway Log matches attempt to correlate the log back to an invoice,
 * transaction, or client credit when possible. This helps after a Subscription ID
 * has been removed from the current Domain or Product/Service record.
 *
 * Scope/safety:
 * - Admin IntelligentSearch hook only.
 * - No WHMCS core/admin template changes.
 * - Read-only: performs no inserts, updates, or deletes.
 * - Long identifier-shaped searches only, to avoid slowing ordinary admin searches.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

if (!function_exists('dm1711_client_name')) {
    function dm1711_client_name($row): string
    {
        $first = trim((string) ($row->firstname ?? ''));
        $last = trim((string) ($row->lastname ?? ''));
        $company = trim((string) ($row->companyname ?? ''));
        $name = trim($first . ' ' . $last);

        if ($name !== '' && $company !== '') {
            return $name . ' (' . $company . ')';
        }
        if ($name !== '') {
            return $name;
        }
        if ($company !== '') {
            return $company;
        }

        $id = (int) ($row->userid ?? $row->clientid ?? 0);
        return $id > 0 ? 'Client #' . $id : 'Client';
    }
}

if (!function_exists('dm1711_invoice_label')) {
    function dm1711_invoice_label($row): string
    {
        $invoiceId = (int) ($row->invoiceid ?? $row->invoice_id ?? 0);
        $invoiceNum = trim((string) ($row->invoicenum ?? ''));
        if ($invoiceNum !== '') {
            return 'Invoice ' . $invoiceNum;
        }
        return $invoiceId > 0 ? 'Invoice #' . $invoiceId : 'Invoice';
    }
}

if (!function_exists('dm1711_add_result')) {
    function dm1711_add_result(array &$results, array &$seen, int $limit, string $key, array $result): void
    {
        if (count($results) >= $limit || isset($seen[$key])) {
            return;
        }
        $seen[$key] = true;
        $results[] = $result;
    }
}

if (!function_exists('dm1711_identifier_search_allowed')) {
    function dm1711_identifier_search_allowed(string $term): bool
    {
        // Transaction/subscription IDs are long. Requiring 8 characters prevents
        // these extra queries from running during normal short-name searches.
        if (strlen($term) < 8 || strlen($term) > 255) {
            return false;
        }
        if (preg_match('/\s/', $term)) {
            return false;
        }
        return (bool) preg_match('/^[A-Za-z0-9._:\-]+$/', $term);
    }
}

if (!function_exists('dm1711_gateway_history_search_allowed')) {
    function dm1711_gateway_history_search_allowed(string $term): bool
    {
        // The Gateway Log has a TEXT payload and no index on its contents.
        // Restrict the historical fallback to longer subscription-like IDs.
        return strlen($term) >= 10
            && strpos($term, '-') !== false
            && (bool) preg_match('/^[A-Za-z0-9._:\-]+$/', $term);
    }
}

if (!function_exists('dm1711_extract_gateway_transaction_id')) {
    function dm1711_extract_gateway_transaction_id(string $data): string
    {
        $trimmed = trim($data);
        if ($trimmed === '') {
            return '';
        }

        // Some gateways log JSON payloads.
        $decoded = json_decode($trimmed, true);
        if (is_array($decoded)) {
            if (isset($decoded['resource']) && is_array($decoded['resource'])) {
                $resourceId = trim((string) ($decoded['resource']['id'] ?? ''));
                if ($resourceId !== '') {
                    return $resourceId;
                }
            }
            foreach (['transaction_id', 'transactionId', 'txn_id', 'sale_id'] as $key) {
                if (!empty($decoded[$key])) {
                    return trim((string) $decoded[$key]);
                }
            }
        }

        // WHMCS Gateway Log debug output commonly uses "key => value" formatting.
        $patterns = [
            '/\bresource\s*=>\s*(?:\r?\n\s*)?id\s*=>\s*([A-Za-z0-9._:-]+)/i',
            '/["\']resource["\']\s*:\s*\{\s*["\']id["\']\s*:\s*["\']([^"\']+)["\']/i',
            '/\btransaction_id\s*(?:=>|:)\s*["\']?([A-Za-z0-9._:-]+)/i',
            '/\btxn_id\s*(?:=>|:)\s*["\']?([A-Za-z0-9._:-]+)/i',
            '/\bsale_id\s*(?:=>|:)\s*["\']?([A-Za-z0-9._:-]+)/i',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $data, $matches)) {
                return trim((string) ($matches[1] ?? ''));
            }
        }

        return '';
    }
}

if (!function_exists('dm1711_find_transaction_owner')) {
    /**
     * Resolve a transaction ID to an invoice/client when possible.
     * Returns null when no normal transaction or client-credit match exists.
     */
    function dm1711_find_transaction_owner(string $transactionId): ?array
    {
        try {
            $account = Capsule::table('tblaccounts as a')
                ->leftJoin('tblinvoices as i', 'i.id', '=', 'a.invoiceid')
                ->leftJoin('tblclients as c', 'c.id', '=', 'a.userid')
                ->where('a.transid', $transactionId)
                ->orderBy('a.id', 'desc')
                ->first([
                    'a.id as accountid', 'a.userid', 'a.invoiceid', 'a.gateway', 'a.date',
                    'a.amountin', 'a.amountout', 'a.transid',
                    'i.invoicenum', 'c.firstname', 'c.lastname', 'c.companyname',
                ]);
            if ($account) {
                return ['type' => 'account', 'row' => $account];
            }

            $history = Capsule::table('tbltransaction_history as th')
                ->leftJoin('tblinvoices as i', 'i.id', '=', 'th.invoice_id')
                ->leftJoin('tblclients as c', 'c.id', '=', 'i.userid')
                ->where('th.transaction_id', $transactionId)
                ->orderBy('th.id', 'desc')
                ->first([
                    'th.id as historyid', 'th.invoice_id as invoiceid', 'th.gateway',
                    'th.transaction_id as transid', 'th.amount', 'th.created_at as date',
                    'i.userid', 'i.invoicenum', 'c.firstname', 'c.lastname', 'c.companyname',
                ]);
            if ($history) {
                return ['type' => 'history', 'row' => $history];
            }

            $credit = Capsule::table('tblcredit as cr')
                ->leftJoin('tblclients as c', 'c.id', '=', 'cr.clientid')
                ->where('cr.description', 'like', '%' . $transactionId . '%')
                ->orderBy('cr.id', 'desc')
                ->first([
                    'cr.id as creditid', 'cr.clientid as userid', 'cr.date', 'cr.description', 'cr.amount',
                    'c.firstname', 'c.lastname', 'c.companyname',
                ]);
            if ($credit) {
                return ['type' => 'credit', 'row' => $credit];
            }
        } catch (Throwable $e) {
            return null;
        }

        return null;
    }
}

add_hook('IntelligentSearch', 1, function (array $vars) {
    $term = trim((string) ($vars['searchTerm'] ?? ''));
    if (!dm1711_identifier_search_allowed($term)) {
        return [];
    }

    $limit = max(1, min(25, (int) ($vars['numResults'] ?? 10)));
    $results = [];
    $seen = [];

    try {
        // 1. Normal WHMCS accounting transactions (invoice payments and other transactions).
        $rows = Capsule::table('tblaccounts as a')
            ->leftJoin('tblinvoices as i', 'i.id', '=', 'a.invoiceid')
            ->leftJoin('tblclients as c', 'c.id', '=', 'a.userid')
            ->where('a.transid', $term)
            ->orderBy('a.id', 'desc')
            ->limit($limit)
            ->get([
                'a.id as accountid', 'a.userid', 'a.invoiceid', 'a.gateway', 'a.date',
                'a.amountin', 'a.amountout', 'a.transid',
                'i.invoicenum', 'c.firstname', 'c.lastname', 'c.companyname',
            ]);

        foreach ($rows as $row) {
            $invoiceId = (int) $row->invoiceid;
            $clientId = (int) $row->userid;
            $title = 'Transaction ID — ' . ($invoiceId > 0 ? dm1711_invoice_label($row) : 'Client Transaction');
            $href = $invoiceId > 0
                ? 'index.php?rp=/admin/billing/invoice/' . $invoiceId
                : 'clientstransactions.php?userid=' . $clientId;
            $amount = (float) $row->amountin > 0 ? (string) $row->amountin : (string) $row->amountout;
            $subtitle = dm1711_client_name($row)
                . ' • Transaction ' . $term
                . ($amount !== '0.00' && $amount !== '0' ? ' • ' . $amount : '')
                . (trim((string) $row->gateway) !== '' ? ' • ' . trim((string) $row->gateway) : '');

            dm1711_add_result($results, $seen, $limit, 'account:' . (int) $row->accountid, [
                'title' => $title,
                'href' => $href,
                'subTitle' => $subtitle,
                'icon' => 'fal fa-receipt',
            ]);
        }

        // 2. Newer gateway transaction-history records. Deduplicate invoice matches already found above.
        if (count($results) < $limit) {
            $rows = Capsule::table('tbltransaction_history as th')
                ->leftJoin('tblinvoices as i', 'i.id', '=', 'th.invoice_id')
                ->leftJoin('tblclients as c', 'c.id', '=', 'i.userid')
                ->where('th.transaction_id', $term)
                ->orderBy('th.id', 'desc')
                ->limit($limit - count($results))
                ->get([
                    'th.id as historyid', 'th.invoice_id as invoiceid', 'th.gateway',
                    'th.transaction_id as transid', 'th.amount', 'th.created_at as date',
                    'i.userid', 'i.invoicenum', 'c.firstname', 'c.lastname', 'c.companyname',
                ]);

            foreach ($rows as $row) {
                $invoiceId = (int) $row->invoiceid;
                $clientId = (int) $row->userid;
                $dedupeInvoiceKey = 'invoice-trans:' . $invoiceId . ':' . strtolower($term);
                if ($invoiceId > 0 && isset($seen[$dedupeInvoiceKey])) {
                    continue;
                }
                $title = 'Transaction ID — ' . ($invoiceId > 0 ? dm1711_invoice_label($row) : 'Gateway Transaction');
                $href = $invoiceId > 0
                    ? 'index.php?rp=/admin/billing/invoice/' . $invoiceId
                    : ($clientId > 0 ? 'clientstransactions.php?userid=' . $clientId : 'transactions.php');
                $subtitle = dm1711_client_name($row)
                    . ' • Transaction ' . $term
                    . ((string) $row->amount !== '' ? ' • ' . (string) $row->amount : '')
                    . (trim((string) $row->gateway) !== '' ? ' • ' . trim((string) $row->gateway) : '');

                dm1711_add_result($results, $seen, $limit, 'history:' . (int) $row->historyid, [
                    'title' => $title,
                    'href' => $href,
                    'subTitle' => $subtitle,
                    'icon' => 'fal fa-receipt',
                ]);
                if ($invoiceId > 0) {
                    $seen[$dedupeInvoiceKey] = true;
                }
            }
        }

        // 3. Payments that were added to the client's credit balance instead of an invoice.
        if (count($results) < $limit) {
            $rows = Capsule::table('tblcredit as cr')
                ->leftJoin('tblclients as c', 'c.id', '=', 'cr.clientid')
                ->where('cr.description', 'like', '%' . $term . '%')
                ->orderBy('cr.id', 'desc')
                ->limit($limit - count($results))
                ->get([
                    'cr.id as creditid', 'cr.clientid as userid', 'cr.date', 'cr.description', 'cr.amount',
                    'c.firstname', 'c.lastname', 'c.companyname',
                ]);

            foreach ($rows as $row) {
                $clientId = (int) $row->userid;
                dm1711_add_result($results, $seen, $limit, 'credit:' . (int) $row->creditid, [
                    'title' => 'Transaction ID — Client Credit',
                    'href' => 'clientscredits.php?userid=' . $clientId,
                    'subTitle' => dm1711_client_name($row)
                        . ' • Transaction ' . $term
                        . ' • Credit ' . (string) $row->amount
                        . ' • ' . (string) $row->date,
                    'icon' => 'fal fa-coins',
                ]);
            }
        }

        // 4. Current Domain Subscription IDs.
        if (count($results) < $limit) {
            $rows = Capsule::table('tbldomains as d')
                ->leftJoin('tblclients as c', 'c.id', '=', 'd.userid')
                ->where('d.subscriptionid', $term)
                ->orderBy('d.id', 'desc')
                ->limit($limit - count($results))
                ->get([
                    'd.id as domainid', 'd.userid', 'd.domain', 'd.status', 'd.subscriptionid',
                    'c.firstname', 'c.lastname', 'c.companyname',
                ]);

            foreach ($rows as $row) {
                dm1711_add_result($results, $seen, $limit, 'domain-sub:' . (int) $row->domainid, [
                    'title' => 'Subscription ID — Domain ' . (string) $row->domain,
                    'href' => 'clientsdomains.php?userid=' . (int) $row->userid . '&id=' . (int) $row->domainid,
                    'subTitle' => dm1711_client_name($row)
                        . ' • Domain ID ' . (int) $row->domainid
                        . ' • ' . (string) $row->subscriptionid
                        . (trim((string) $row->status) !== '' ? ' • ' . trim((string) $row->status) : ''),
                    'icon' => 'fal fa-globe',
                ]);
            }
        }

        // 5. Current Product/Service Subscription IDs.
        if (count($results) < $limit) {
            $rows = Capsule::table('tblhosting as h')
                ->leftJoin('tblproducts as p', 'p.id', '=', 'h.packageid')
                ->leftJoin('tblclients as c', 'c.id', '=', 'h.userid')
                ->where('h.subscriptionid', $term)
                ->orderBy('h.id', 'desc')
                ->limit($limit - count($results))
                ->get([
                    'h.id as serviceid', 'h.userid', 'h.domain', 'h.domainstatus', 'h.subscriptionid',
                    'p.name as productname', 'c.firstname', 'c.lastname', 'c.companyname',
                ]);

            foreach ($rows as $row) {
                $productName = trim((string) $row->productname);
                if ($productName === '') {
                    $productName = 'Service #' . (int) $row->serviceid;
                }
                $serviceDomain = trim((string) $row->domain);
                dm1711_add_result($results, $seen, $limit, 'service-sub:' . (int) $row->serviceid, [
                    'title' => 'Subscription ID — Product/Service ' . $productName,
                    'href' => 'clientsservices.php?userid=' . (int) $row->userid . '&id=' . (int) $row->serviceid,
                    'subTitle' => dm1711_client_name($row)
                        . ' • Service ID ' . (int) $row->serviceid
                        . ($serviceDomain !== '' ? ' • ' . $serviceDomain : '')
                        . ' • ' . (string) $row->subscriptionid
                        . (trim((string) $row->domainstatus) !== '' ? ' • ' . trim((string) $row->domainstatus) : ''),
                    'icon' => 'fal fa-server',
                ]);
            }
        }

        // 6. Historical Subscription ID fallback in Gateway Log data.
        // This remains useful after a Subscription ID is cleared from a Domain/Service.
        if (count($results) < $limit && dm1711_gateway_history_search_allowed($term)) {
            $logLimit = min(5, $limit - count($results));
            $logs = Capsule::table('tblgatewaylog')
                ->where('data', 'like', '%' . $term . '%')
                ->orderBy('id', 'desc')
                ->limit($logLimit)
                ->get(['id', 'date', 'gateway', 'data', 'transaction_history_id', 'result']);

            foreach ($logs as $log) {
                $logId = (int) $log->id;
                $transactionId = dm1711_extract_gateway_transaction_id((string) $log->data);
                $owner = $transactionId !== '' ? dm1711_find_transaction_owner($transactionId) : null;

                // If the log itself references WHMCS transaction history, use that relationship too.
                if (!$owner && (int) $log->transaction_history_id > 0) {
                    $history = Capsule::table('tbltransaction_history as th')
                        ->leftJoin('tblinvoices as i', 'i.id', '=', 'th.invoice_id')
                        ->leftJoin('tblclients as c', 'c.id', '=', 'i.userid')
                        ->where('th.id', (int) $log->transaction_history_id)
                        ->first([
                            'th.id as historyid', 'th.invoice_id as invoiceid', 'th.gateway',
                            'th.transaction_id as transid', 'th.amount', 'th.created_at as date',
                            'i.userid', 'i.invoicenum', 'c.firstname', 'c.lastname', 'c.companyname',
                        ]);
                    if ($history) {
                        $owner = ['type' => 'history', 'row' => $history];
                        if ($transactionId === '') {
                            $transactionId = trim((string) $history->transid);
                        }
                    }
                }

                $title = 'Historical Subscription ID — Gateway Log #' . $logId;
                $href = 'gatewaylog.php';
                $subtitleParts = [];

                if ($owner) {
                    $row = $owner['row'];
                    $ownerType = (string) $owner['type'];
                    if ($ownerType === 'credit') {
                        $clientId = (int) $row->userid;
                        $title = 'Historical Subscription ID — Client Credit';
                        $href = 'clientscredits.php?userid=' . $clientId;
                        $subtitleParts[] = dm1711_client_name($row);
                    } else {
                        $invoiceId = (int) ($row->invoiceid ?? 0);
                        $clientId = (int) ($row->userid ?? 0);
                        if ($invoiceId > 0) {
                            $title = 'Historical Subscription ID — ' . dm1711_invoice_label($row);
                            $href = 'index.php?rp=/admin/billing/invoice/' . $invoiceId;
                        } elseif ($clientId > 0) {
                            $href = 'clientstransactions.php?userid=' . $clientId;
                        }
                        $subtitleParts[] = dm1711_client_name($row);
                    }
                }

                $subtitleParts[] = 'Subscription ' . $term;
                if ($transactionId !== '') {
                    $subtitleParts[] = 'Transaction ' . $transactionId;
                }
                $subtitleParts[] = 'Gateway Log #' . $logId;
                if (trim((string) $log->gateway) !== '') {
                    $subtitleParts[] = trim((string) $log->gateway);
                }
                if (trim((string) $log->date) !== '') {
                    $subtitleParts[] = trim((string) $log->date);
                }
                if (trim((string) $log->result) !== '') {
                    $subtitleParts[] = trim((string) $log->result);
                }

                dm1711_add_result($results, $seen, $limit, 'gateway-sub:' . $logId, [
                    'title' => $title,
                    'href' => $href,
                    'subTitle' => implode(' • ', $subtitleParts),
                    'icon' => 'fal fa-history',
                ]);
            }
        }
    } catch (Throwable $e) {
        // Intelligent Search must never be allowed to break the Admin search UI.
        // Return any results already collected and let WHMCS's native search continue.
    }

    return $results;
});
