<?php
/**
 * DomainMonger DNSSEC-only sidebar route normalizer.
 *
 * Patch 1002:
 * - Neutralizes the broad 999/1000 sidebar normalizer that affected main domain links.
 * - Keeps the confirmed DNSSEC Management route fix only.
 * - Does not touch Get EPP, forms, tokens, registrar/module behavior, templates, or language files.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

function dm1002_norm_text($value)
{
    $value = strtolower((string) $value);
    $value = preg_replace('/\s+/', ' ', $value);
    return trim($value);
}

function dm1002_parse_query_from_uri($uri)
{
    $query = parse_url((string) $uri, PHP_URL_QUERY);
    $params = [];
    if ($query) {
        parse_str($query, $params);
    }
    return is_array($params) ? $params : [];
}

function dm1002_id_from_uri_or_request($uri)
{
    $params = dm1002_parse_query_from_uri($uri);
    foreach (['domainid', 'id'] as $key) {
        if (!empty($params[$key]) && preg_match('/^\d+$/', (string) $params[$key])) {
            return (string) $params[$key];
        }
    }
    foreach (['domainid', 'id'] as $key) {
        if (!empty($_GET[$key]) && preg_match('/^\d+$/', (string) $_GET[$key])) {
            return (string) $_GET[$key];
        }
    }
    return '';
}

function dm1002_domain_from_uri_or_request($uri)
{
    $params = dm1002_parse_query_from_uri($uri);
    if (!empty($params['domain'])) {
        return (string) $params['domain'];
    }
    if (!empty($_GET['domain'])) {
        return (string) $_GET['domain'];
    }
    return '';
}

function dm1002_is_dnssec_item($label, $uri)
{
    $text = dm1002_norm_text($label . ' ' . $uri);
    return strpos($text, 'dnssec management') !== false || strpos($text, 'dnssec') !== false || strpos($text, 'nsrecordtype=dnssec') !== false;
}

function dm1002_build_dnssec_uri($id, $domain = '')
{
    if (!$id) {
        return '';
    }
    $uri = 'dnsmanagement.php?action=managednszone&dmsection=dnssec&nsrecordtype=DNSSEC&dmdesign=1&dmconverted=1&domainid=' . rawurlencode($id);
    if ($domain !== '') {
        $uri .= '&domain=' . rawurlencode($domain);
    }
    return $uri . '#dm-dns-dnssec';
}

function dm1002_normalize_dnssec_menu_item($item)
{
    if (!is_object($item)) {
        return;
    }

    $label = '';
    $name = '';
    $uri = '';

    if (method_exists($item, 'getLabel')) {
        $label = (string) $item->getLabel();
    }
    if (method_exists($item, 'getName')) {
        $name = (string) $item->getName();
    }
    if (method_exists($item, 'getUri')) {
        $uri = (string) $item->getUri();
    }

    if (dm1002_is_dnssec_item($label . ' ' . $name, $uri) && method_exists($item, 'setUri')) {
        $id = dm1002_id_from_uri_or_request($uri);
        $domain = dm1002_domain_from_uri_or_request($uri);
        $newUri = dm1002_build_dnssec_uri($id, $domain);
        if ($newUri) {
            $item->setUri($newUri);
        }
    }

    if (method_exists($item, 'getChildren')) {
        $children = $item->getChildren();
        if (is_iterable($children)) {
            foreach ($children as $child) {
                dm1002_normalize_dnssec_menu_item($child);
            }
        }
    }
}

add_hook('ClientAreaPrimarySidebar', 1, function ($primarySidebar) {
    dm1002_normalize_dnssec_menu_item($primarySidebar);
});

add_hook('ClientAreaSecondarySidebar', 1, function ($secondarySidebar) {
    dm1002_normalize_dnssec_menu_item($secondarySidebar);
});
