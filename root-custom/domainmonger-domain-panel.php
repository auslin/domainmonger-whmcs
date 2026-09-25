<?php
/**
 * DomainMonger patch 828: compatibility redirect for the old patch 827 preview URL.
 *
 * The original standalone preview page can be redirected by some WHMCS setups.
 * Send it to a normal WHMCS client-area route with dmdesign=1 instead.
 */

$domainId = 0;
if (isset($_GET['domainid'])) {
    $domainId = (int) $_GET['domainid'];
} elseif (isset($_GET['id'])) {
    $domainId = (int) $_GET['id'];
}

$query = 'action=domaindetails&dmdesign=1';
if ($domainId > 0) {
    $query .= '&id=' . $domainId;
}

header('Location: clientarea.php?' . $query, true, 302);
exit;
