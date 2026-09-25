<?php

declare(strict_types=1);

http_response_code(410);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');
header('X-Content-Type-Options: nosniff');

echo json_encode([
    'success' => false,
    'error' => 'The legacy Default Nameservers addon has been retired. Use the native WHMCS registrar and nameserver controls.',
], JSON_UNESCAPED_SLASHES);
exit;
