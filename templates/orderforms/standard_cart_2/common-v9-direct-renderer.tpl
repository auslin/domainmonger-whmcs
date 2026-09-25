{* DomainMonger Patch 220: native v9 namespinner endpoint with clean safe custom renderer. *}
{* Purpose: avoid native WHMCS renderer/template mismatch while using real v9 namespinner output. *}
{* Does not touch english.php. *}
<link rel="stylesheet" type="text/css" href="{$WEB_ROOT}/templates/orderforms/standard_cart/css/all.min.css?v={$versionHash}" />
<link rel="stylesheet" type="text/css" href="{$WEB_ROOT}/templates/orderforms/standard_cart_2/css/dm-v9-direct-renderer.css?v={$versionHash}" />
<script type="text/javascript" src="{$WEB_ROOT}/templates/orderforms/standard_cart/js/scripts.min.js?v={$versionHash}"></script>
