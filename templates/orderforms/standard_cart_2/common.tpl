<link rel="stylesheet" type="text/css" href="{assetPath file='all.min.css'}?v={$versionHash}" />
{assetExists file="custom.css"}
<link rel="stylesheet" type="text/css" href="{$__assetPath__}?v={$versionHash}" />
{/assetExists}
{* DomainMonger Patch 278: keep normal register asset chain isolated from dmv9support. *}
{if $smarty.get.a eq 'add' && $smarty.get.domain eq 'register' && !$smarty.get.dmv9support}
<link rel="stylesheet" type="text/css" href="{$WEB_ROOT}/templates/orderforms/standard_cart/css/dm-register-domain-v112.css?v=112" />
<link rel="stylesheet" type="text/css" href="{$WEB_ROOT}/templates/orderforms/standard_cart/css/dm-register-domain-v114.css?v=114" />
<link rel="stylesheet" type="text/css" href="{$WEB_ROOT}/templates/orderforms/standard_cart/css/dm-register-domain-v115.css?v=115" />
<link rel="stylesheet" type="text/css" href="{$WEB_ROOT}/templates/orderforms/standard_cart/css/dm-register-domain-v122.css?v=122" />
<link rel="stylesheet" type="text/css" href="{$WEB_ROOT}/templates/orderforms/standard_cart/css/dm-register-domain-v134.css?v=134" />
<link rel="stylesheet" type="text/css" href="{$WEB_ROOT}/templates/orderforms/standard_cart/css/dm-register-domain-v135.css?v=135" />
<link rel="stylesheet" type="text/css" href="{$WEB_ROOT}/templates/orderforms/standard_cart/css/dm-register-domain-v136.css?v=136" />
<link rel="stylesheet" type="text/css" href="{$WEB_ROOT}/templates/orderforms/standard_cart/css/dm-register-domain-v137.css?v=137" />
<link rel="stylesheet" type="text/css" href="{$WEB_ROOT}/templates/orderforms/standard_cart/css/dm-register-domain-v138.css?v=138" />
{/if}
<link rel="stylesheet" type="text/css" href="{$WEB_ROOT}/templates/orderforms/standard_cart/css/dm-cart-view-v139.css?v=139" />
<link rel="stylesheet" type="text/css" href="{$WEB_ROOT}/templates/orderforms/standard_cart/css/dm-cart-view-v141.css?v=141" />
<link rel="stylesheet" type="text/css" href="{$WEB_ROOT}/templates/orderforms/standard_cart/css/dm-cart-view-v143.css?v=143" />
<link rel="stylesheet" type="text/css" href="{$WEB_ROOT}/templates/orderforms/standard_cart/css/dm-cart-view-v145.css?v=145" />
<link rel="stylesheet" type="text/css" href="{$WEB_ROOT}/templates/orderforms/standard_cart/css/dm-cart-product-cards-v162.css?v=162" />
<link rel="stylesheet" type="text/css" href="{$WEB_ROOT}/templates/orderforms/standard_cart/css/dm-cart-alerts-v166.css?v=166" />
<link rel="stylesheet" type="text/css" href="{$WEB_ROOT}/templates/orderforms/standard_cart/css/dm-cart-buttons-v168.css?v=168" />
<link rel="stylesheet" type="text/css" href="{$WEB_ROOT}/templates/orderforms/standard_cart/css/dm-cart-checkout-v169.css?v=169" />
<script type="text/javascript" src="{assetPath file='scripts.min.js'}?v={$versionHash}"></script>
{if $smarty.get.a eq 'add' && $smarty.get.domain eq 'register' && !$smarty.get.dmv9support}
<script type="text/javascript" src="{$WEB_ROOT}/templates/orderforms/standard_cart/js/dm-register-domain-v122.js?v=122"></script>
<script type="text/javascript" src="{$WEB_ROOT}/templates/orderforms/standard_cart/js/dm-register-domain-v134.js?v=134"></script>
<script type="text/javascript" src="{$WEB_ROOT}/templates/orderforms/standard_cart/js/dm-register-domain-v135.js?v=135"></script>
<script type="text/javascript" src="{$WEB_ROOT}/templates/orderforms/standard_cart/js/dm-register-domain-v136.js?v=136"></script>
<script type="text/javascript" src="{$WEB_ROOT}/templates/orderforms/standard_cart/js/dm-register-domain-v138.js?v=138"></script>
{/if}
