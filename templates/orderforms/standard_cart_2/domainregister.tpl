{* DomainMonger Patch 278
   Normal register page: confirmed Patch 221 wide-results renderer restored after Patch 277 cap regression.
   Support route: restored from Patch 232 support/native diagnostic files.
   Protected route: manage/cart.php?a=add&domain=register&dmv9support=1
   Do not load the customer v8x renderer when dmv9support is present.
*}
{if $smarty.get.dmv9support|default:'' eq '1'}
    {include file="orderforms/standard_cart_2/domainregisterv9.tpl"}
{else}
    {include file="orderforms/standard_cart_2/domainregister-patch275-direct.tpl"}
{/if}
