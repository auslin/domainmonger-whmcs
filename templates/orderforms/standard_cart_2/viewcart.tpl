{if $checkout}

    {include file="orderforms/$carttpl/checkout.tpl"}

{else}

    <script>
        // Define state tab index value
        var statesTab = 10;
        var stateNotRequired = true;
    </script>
    {include file="orderforms/standard_cart/common.tpl"}

{literal}
<style>
#order-standard_cart.dm-v9-cart-polish .sidebar {
    margin-top: 0;
}
#order-standard_cart.dm-v9-cart-polish .sidebar .panel,
#order-standard_cart.dm-v9-cart-polish .sidebar .card {
    margin-bottom: 18px;
    border: 1px solid #d9e1ea;
    border-radius: 4px;
    overflow: hidden;
    box-shadow: none;
    background: #fff;
}
#order-standard_cart.dm-v9-cart-polish .sidebar .panel-heading,
#order-standard_cart.dm-v9-cart-polish .sidebar .card-header {
    background: #163a5f !important;
    border: 0;
    color: #fff !important;
    padding: 11px 14px;
}
#order-standard_cart.dm-v9-cart-polish .sidebar .panel-title,
#order-standard_cart.dm-v9-cart-polish .sidebar .panel-title a,
#order-standard_cart.dm-v9-cart-polish .sidebar .card-title,
#order-standard_cart.dm-v9-cart-polish .sidebar .card-title a {
    color: #fff !important;
    font-weight: 700;
}
#order-standard_cart.dm-v9-cart-polish .sidebar .panel-body,
#order-standard_cart.dm-v9-cart-polish .sidebar .list-group,
#order-standard_cart.dm-v9-cart-polish .sidebar .list-group-flush,
#order-standard_cart.dm-v9-cart-polish .sidebar .nav,
#order-standard_cart.dm-v9-cart-polish .sidebar .panel-collapse {
    background: #fff;
}
#order-standard_cart.dm-v9-cart-polish .sidebar .list-group-item,
#order-standard_cart.dm-v9-cart-polish .sidebar .list-group-item a,
#order-standard_cart.dm-v9-cart-polish .sidebar .nav > li > a,
#order-standard_cart.dm-v9-cart-polish .sidebar .panel-body a,
#order-standard_cart.dm-v9-cart-polish .sidebar .card-body a {
    color: #4a5a6a;
    font-size: 14px;
    line-height: 1.35;
}
#order-standard_cart.dm-v9-cart-polish .sidebar .list-group-item,
#order-standard_cart.dm-v9-cart-polish .sidebar .nav > li > a,
#order-standard_cart.dm-v9-cart-polish .sidebar .panel-body a,
#order-standard_cart.dm-v9-cart-polish .sidebar .card-body a {
    background: #fff;
    border-top: 1px solid #e7edf3;
    padding: 11px 14px;
}
#order-standard_cart.dm-v9-cart-polish .sidebar .list-group-item:first-child,
#order-standard_cart.dm-v9-cart-polish .sidebar .nav > li:first-child > a {
    border-top: 0;
}
#order-standard_cart.dm-v9-cart-polish .sidebar .list-group-item,
#order-standard_cart.dm-v9-cart-polish .sidebar .list-group-item:first-child,
#order-standard_cart.dm-v9-cart-polish .sidebar .list-group-item:last-child,
#order-standard_cart.dm-v9-cart-polish .sidebar .nav > li > a,
#order-standard_cart.dm-v9-cart-polish .sidebar .nav > li:first-child > a,
#order-standard_cart.dm-v9-cart-polish .sidebar .nav > li:last-child > a,
#order-standard_cart.dm-v9-cart-polish .sidebar .panel-body a,
#order-standard_cart.dm-v9-cart-polish .sidebar .card-body a {
    border-radius: 0 !important;
}
#order-standard_cart.dm-v9-cart-polish .sidebar .list-group-item:hover,
#order-standard_cart.dm-v9-cart-polish .sidebar .list-group-item:hover a,
#order-standard_cart.dm-v9-cart-polish .sidebar .nav > li > a:hover,
#order-standard_cart.dm-v9-cart-polish .sidebar .panel-body a:hover,
#order-standard_cart.dm-v9-cart-polish .sidebar .card-body a:hover {
    background: #fff7ef;
    color: #f58220 !important;
    text-decoration: none;
}
#order-standard_cart.dm-v9-cart-polish .sidebar .list-group-item.active,
#order-standard_cart.dm-v9-cart-polish .sidebar .list-group-item.active a,
#order-standard_cart.dm-v9-cart-polish .sidebar .nav > li.active > a,
#order-standard_cart.dm-v9-cart-polish .sidebar .nav > li.current > a,
#order-standard_cart.dm-v9-cart-polish .sidebar .panel-body .active a,
#order-standard_cart.dm-v9-cart-polish .sidebar .card-body .active a {
    background: #f58220 !important;
    border-color: #f58220 !important;
    color: #fff !important;
}
#order-standard_cart.dm-v9-cart-polish .header-lined h1 {
    color: #163a5f;
    font-weight: 700;
}
#order-standard_cart.dm-v9-cart-polish .panel,
#order-standard_cart.dm-v9-cart-polish .view-cart-items,
#order-standard_cart.dm-v9-cart-polish .order-summary,
#order-standard_cart.dm-v9-cart-polish .modal-content {
    border: 1px solid #d9e1ea;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(22,58,95,.08);
    overflow: hidden;
}
#order-standard_cart.dm-v9-cart-polish .panel-heading,
#order-standard_cart.dm-v9-cart-polish .view-cart-items-header,
#order-standard_cart.dm-v9-cart-polish .order-summary h2,
#order-standard_cart.dm-v9-cart-polish .modal-header {
    background: #163a5f !important;
    border-color: #163a5f !important;
    color: #fff !important;
}
#order-standard_cart.dm-v9-cart-polish .panel-heading .panel-title,
#order-standard_cart.dm-v9-cart-polish .panel-heading h3,
#order-standard_cart.dm-v9-cart-polish .view-cart-items-header,
#order-standard_cart.dm-v9-cart-polish .order-summary h2,
#order-standard_cart.dm-v9-cart-polish .modal-header .modal-title {
    color: #fff !important;
    font-weight: 700;
}
#order-standard_cart.dm-v9-cart-polish .panel-body,
#order-standard_cart.dm-v9-cart-polish .panel-footer,
#order-standard_cart.dm-v9-cart-polish .summary-container,
#order-standard_cart.dm-v9-cart-polish .modal-body,
#order-standard_cart.dm-v9-cart-polish .modal-footer {
    background: #fff;
}
#order-standard_cart.dm-v9-cart-polish .form-control {
    border-color: #cfd9e3;
    border-radius: 4px;
    box-shadow: none;
    color: #163a5f;
}
#order-standard_cart.dm-v9-cart-polish .form-control:focus {
    border-color: #f58220;
    box-shadow: 0 0 0 2px rgba(245,130,32,.12);
}
#order-standard_cart.dm-v9-cart-polish .btn {
    border-radius: 4px;
    font-weight: 700;
    text-decoration: none;
}
#order-standard_cart.dm-v9-cart-polish .btn-primary,
#order-standard_cart.dm-v9-cart-polish .btn-success,
#order-standard_cart.dm-v9-cart-polish .btn-checkout,
#order-standard_cart.dm-v9-cart-polish .btn-transfer {
    background: #f58220 !important;
    border-color: #f58220 !important;
    color: #fff !important;
}
#order-standard_cart.dm-v9-cart-polish .btn-primary:hover,
#order-standard_cart.dm-v9-cart-polish .btn-primary:focus,
#order-standard_cart.dm-v9-cart-polish .btn-success:hover,
#order-standard_cart.dm-v9-cart-polish .btn-success:focus,
#order-standard_cart.dm-v9-cart-polish .btn-checkout:hover,
#order-standard_cart.dm-v9-cart-polish .btn-checkout:focus,
#order-standard_cart.dm-v9-cart-polish .btn-transfer:hover,
#order-standard_cart.dm-v9-cart-polish .btn-transfer:focus {
    background: #214e7a !important;
    border-color: #214e7a !important;
    color: #fff !important;
}
#order-standard_cart.dm-v9-cart-polish .btn-default {
    background: #163a5f !important;
    border-color: #102d4b !important;
    color: #fff !important;
}
#order-standard_cart.dm-v9-cart-polish .btn-default:hover,
#order-standard_cart.dm-v9-cart-polish .btn-default:focus {
    background: #214e7a !important;
    border-color: #214e7a !important;
    color: #fff !important;
}
#order-standard_cart.dm-v9-cart-polish .btn-link {
    color: #163a5f;
    text-decoration: none;
}
#order-standard_cart.dm-v9-cart-polish .btn-link:hover,
#order-standard_cart.dm-v9-cart-polish .btn-link:focus,
#order-standard_cart.dm-v9-cart-polish a:hover,
#order-standard_cart.dm-v9-cart-polish a:focus {
    color: #f58220;
    text-decoration: none;
}
#order-standard_cart.dm-v9-cart-polish .dropdown-menu > li > a:hover,
#order-standard_cart.dm-v9-cart-polish .dropdown-menu > li > a:focus {
    background: #fff7ef;
    color: #f58220;
}
#order-standard_cart.dm-v9-cart-polish .btn-remove-from-cart,
#order-standard_cart.dm-v9-cart-polish .empty-cart .btn {
    color: #b94a48;
}
#order-standard_cart.dm-v9-cart-polish .btn-remove-from-cart:hover,
#order-standard_cart.dm-v9-cart-polish .empty-cart .btn:hover {
    color: #9d3533;
}
#order-standard_cart.dm-v9-cart-polish .view-cart-items .item {
    border-color: #e7edf3;
}
#order-standard_cart.dm-v9-cart-polish .view-cart-items .item-title,
#order-standard_cart.dm-v9-cart-polish .view-cart-items .item-domain,
#order-standard_cart.dm-v9-cart-polish .item-price > span:first-child {
    color: #163a5f;
    font-weight: 700;
}

/* Patch 1418: make the domain name easier to read without enlarging the item title. */
#order-standard_cart.dm-v9-viewcart-polish .view-cart-items .item-domain {
    display: block;
    margin-top: 2px;
    font-size: 15px;
    line-height: 1.35;
}
#order-standard_cart.dm-v9-cart-polish .cycle,
#order-standard_cart.dm-v9-cart-polish .renewal,
#order-standard_cart.dm-v9-cart-polish .item-group,
#order-standard_cart.dm-v9-cart-polish .product-info {
    color: #5f6f7f;
}
#order-standard_cart.dm-v9-cart-polish .order-summary .total-due-today {
    background: #fff7ef;
    border-top: 1px solid #f4d2b0;
    color: #163a5f;
}
#order-standard_cart.dm-v9-cart-polish .order-summary .total-due-today .amt,
#order-standard_cart.dm-v9-cart-polish .order-summary .cost {
    color: #f58220;
    font-weight: 700;
}
#order-standard_cart.dm-v9-cart-polish .view-cart-empty,
#order-standard_cart.dm-v9-cart-polish .empty-cart {
    background: #fff;
    border-color: #e7edf3;
}
#order-standard_cart.dm-v9-cart-polish .view-cart-gateway-checkout,
#order-standard_cart.dm-v9-cart-polish .view-cart-tabs {
    margin-top: 18px;
}

#order-standard_cart.dm-v9-cart-polish .header-lined,
#order-standard_cart.dm-v9-cart-polish .header-lined h1 {
    text-align: center;
}
#order-standard_cart.dm-v9-cart-polish .order-summary .summary-container {
    text-align: center;
}
#order-standard_cart.dm-v9-cart-polish .order-summary .summary-container .subtotal,
#order-standard_cart.dm-v9-cart-polish .order-summary .summary-container .bordered-totals,
#order-standard_cart.dm-v9-cart-polish .order-summary .summary-container .recurring-totals,
#order-standard_cart.dm-v9-cart-polish .order-summary .summary-container .total-due-today,
#order-standard_cart.dm-v9-cart-polish .order-summary .summary-container .total-due-today .amt {
    border-radius: 0 !important;
}
#order-standard_cart.dm-v9-cart-polish .order-summary .summary-container .subtotal {
    background: #163a5f !important;
    color: #fff !important;
    display: block;
    margin: 0 0 8px;
    padding: 10px 14px;
}
#order-standard_cart.dm-v9-cart-polish .order-summary .summary-container .subtotal span,
#order-standard_cart.dm-v9-cart-polish .order-summary .summary-container .subtotal * {
    color: #fff !important;
}
#order-standard_cart.dm-v9-cart-polish .order-summary .summary-container .subtotal .pull-left,
#order-standard_cart.dm-v9-cart-polish .order-summary .summary-container .subtotal .pull-right {
    line-height: 1.35;
}
#order-standard_cart.dm-v9-cart-polish .order-summary .summary-container .recurring-totals {
    margin: 8px 0 14px;
    text-align: center;
}
#order-standard_cart.dm-v9-cart-polish .order-summary .summary-container .recurring-totals .pull-left,
#order-standard_cart.dm-v9-cart-polish .order-summary .summary-container .recurring-totals .pull-right {
    float: none !important;
}
#order-standard_cart.dm-v9-cart-polish .order-summary .summary-container .recurring-totals .pull-left {
    display: inline-block;
    margin-right: 4px;
}
#order-standard_cart.dm-v9-cart-polish .order-summary .summary-container .recurring-totals .pull-right {
    display: inline-block;
}
#order-standard_cart.dm-v9-cart-polish .order-summary .total-due-today {
    text-align: center;
}
#order-standard_cart.dm-v9-cart-polish .order-summary .total-due-today .amt {
    display: block;
    margin: 0 auto 8px;
    max-width: 100%;
    padding: 10px 14px;
}
#order-standard_cart.dm-v9-cart-polish .order-summary .summary-container .text-right {
    text-align: center !important;
}
#order-standard_cart.dm-v9-cart-polish .order-summary .btn-checkout {
    display: inline-block;
    margin: 0 auto 8px;
}
#order-standard_cart.dm-v9-cart-polish .order-summary .btn-continue-shopping {
    display: inline-block;
    margin: 0 auto;
}

#order-standard_cart.dm-v9-cart-polish .sidebar .panel,
#order-standard_cart.dm-v9-cart-polish .sidebar .card,
#order-standard_cart.dm-v9-cart-polish .view-cart-items,
#order-standard_cart.dm-v9-cart-polish .order-summary {
    border-radius: 4px !important;
}
#order-standard_cart.dm-v9-cart-polish .sidebar .panel-heading,
#order-standard_cart.dm-v9-cart-polish .sidebar .card-header,
#order-standard_cart.dm-v9-cart-polish .view-cart-items-header,
#order-standard_cart.dm-v9-cart-polish .order-summary h2 {
    border-radius: 4px 4px 0 0 !important;
}
#order-standard_cart.dm-v9-viewcart-polish > .dm-v9-viewcart-page-header {
    box-sizing: border-box !important;
    display: flex !important;
    align-items: center !important;
    width: 100% !important;
    min-height: 68px !important;
    margin: 0 0 12px !important;
    padding: 14px 32px !important;
    border: 0 !important;
    border-radius: 0 !important;
    background: #163a5f !important;
    color: #fff !important;
    box-shadow: none !important;
    text-align: left !important;
}
#order-standard_cart.dm-v9-viewcart-polish > .dm-v9-viewcart-page-header .dm-v9-viewcart-page-header-text {
    width: 100%;
    margin: 0;
    padding: 0;
    text-align: left !important;
}
#order-standard_cart.dm-v9-viewcart-polish > .dm-v9-viewcart-page-header h1 {
    margin: 0 !important;
    padding: 0 !important;
    color: #fff !important;
    -webkit-text-fill-color: #fff !important;
    font-size: 18px !important;
    font-weight: 700 !important;
    line-height: 1.15 !important;
    letter-spacing: 0 !important;
    text-transform: none !important;
    text-shadow: none !important;
    text-align: left !important;
}
#order-standard_cart.dm-v9-viewcart-polish > .dm-v9-viewcart-page-header p {
    margin: 4px 0 0 !important;
    padding: 0 !important;
    color: rgba(255,255,255,.91) !important;
    -webkit-text-fill-color: rgba(255,255,255,.91) !important;
    font-size: 13px !important;
    font-weight: 500 !important;
    line-height: 1.2 !important;
    letter-spacing: 0 !important;
    text-transform: none !important;
    text-shadow: none !important;
    text-align: left !important;
}
#order-standard_cart.dm-v9-cart-polish .empty-cart {
    background: transparent !important;
    border: 0 !important;
    box-shadow: none !important;
    padding: 14px 0 0 !important;
    text-align: center;
}
#order-standard_cart.dm-v9-cart-polish .empty-cart .btn {
    margin: 0 auto;
}
@media (max-width: 767px) {
    #order-standard_cart.dm-v9-viewcart-polish > .dm-v9-viewcart-page-header {
        min-height: 0 !important;
        padding: 14px 22px !important;
    }
}

#order-standard_cart.dm-v9-cart-polish .order-summary .summary-container .subtotal,
#order-standard_cart.dm-v9-cart-polish .order-summary .summary-container .bordered-totals,
#order-standard_cart.dm-v9-cart-polish .order-summary .summary-container .recurring-totals,
#order-standard_cart.dm-v9-cart-polish .order-summary .summary-container .total-due-today,
#order-standard_cart.dm-v9-cart-polish .order-summary .summary-container .total-due-today .amt {
    border-radius: 4px !important;
}
#order-standard_cart.dm-v9-cart-polish .order-summary .summary-container .subtotal {
    overflow: hidden;
}
#order-standard_cart.dm-v9-cart-polish .order-summary .summary-container .subtotal:before,
#order-standard_cart.dm-v9-cart-polish .order-summary .summary-container .subtotal:after {
    background: transparent !important;
}
#order-standard_cart.dm-v9-cart-polish .view-cart-items-header {
    overflow: hidden;
}
#order-standard_cart.dm-v9-cart-polish .alert-warning {
    background: #fff8cf;
    border-color: #f3e69a;
    color: #5c4a00;
}

/* Patch 1417: compact the cart item heading and fully expose Remove actions. */
#order-standard_cart.dm-v9-cart-polish .view-cart-items-header {
    min-height: 0 !important;
    padding: 7px 12px !important;
    font-size: .86em !important;
    line-height: 1.25 !important;
}
#order-standard_cart.dm-v9-cart-polish .dm-cart-remove-column {
    text-align: right;
}
#order-standard_cart.dm-v9-cart-polish .dm-cart-remove-column .btn-remove-from-cart {
    display: inline-flex;
    align-items: center;
    justify-content: flex-end;
    gap: 4px;
    max-width: 100%;
    padding: 4px 6px;
    white-space: nowrap;
}
#order-standard_cart.dm-v9-cart-polish .dm-cart-remove-column .dm-remove-label {
    display: inline !important;
    font-size: 12px;
    line-height: 1.2;
}
/* Patch 1682: use one domain Remove action at every viewport size. */
@media (max-width: 767px) {
    #order-standard_cart.dm-v9-cart-polish .dm-domain-remove-column {
        clear: both;
        text-align: right;
        padding-top: 4px;
    }
}

/* Patch 1417: use the approved pale-orange total panel and center its context. */
#order-standard_cart.dm-v9-cart-polish .order-summary .summary-container .total-due-today {
    background: #fff8f1 !important;
    border: 1px solid #f4d2b0 !important;
    border-radius: 4px !important;
    color: #163a5f !important;
    padding: 12px 14px !important;
    text-align: center !important;
}
#order-standard_cart.dm-v9-cart-polish .order-summary .summary-container .total-due-today > span {
    text-align: center !important;
}
#order-standard_cart.dm-v9-cart-polish .order-summary .summary-container .total-due-today .amt {
    background: transparent !important;
    border: 0 !important;
    box-shadow: none !important;
    color: #f58220 !important;
    margin: 0 auto 4px !important;
    padding: 0 !important;
}
/* Patch 1419: keep cart confirmation dialogs above the fixed site toolbar and center their content. */
body.modal-open .modal-backdrop {
    z-index: 11990 !important;
}
#modalRemoveItem.modal,
#modalEmptyCart.modal {
    z-index: 12000 !important;
}
#modalRemoveItem .modal-header,
#modalEmptyCart .modal-header {
    position: relative;
    padding: 16px 48px 12px !important;
    text-align: center !important;
}
#modalRemoveItem .modal-header .close,
#modalEmptyCart .modal-header .close {
    position: absolute;
    top: 10px;
    right: 14px;
    float: none;
    margin: 0;
    z-index: 1;
}
#modalRemoveItem .modal-title,
#modalEmptyCart .modal-title {
    width: 100%;
    margin: 0;
    text-align: center !important;
}
#modalRemoveItem .modal-title i,
#modalEmptyCart .modal-title i,
#modalRemoveItem .modal-title span,
#modalEmptyCart .modal-title span {
    display: block;
    text-align: center !important;
}
#modalRemoveItem .modal-title i,
#modalEmptyCart .modal-title i {
    margin: 0 auto 6px;
}
#modalRemoveItem .modal-body,
#modalEmptyCart .modal-body {
    padding: 18px 24px !important;
    text-align: center !important;
    line-height: 1.5;
}

@media (max-width: 991px) {
    #order-standard_cart.dm-v9-cart-polish .sidebar {
        display: none !important;
    }
}
</style>
{/literal}

    <script type="text/javascript" src="{$BASE_PATH_JS}/StatesDropdown.js"></script>

    <div id="order-standard_cart" class="dm-v9-cart-polish dm-v9-viewcart-polish">

        <section class="header-lined dm-v9-viewcart-page-header" aria-labelledby="dmV9ViewCartPageTitle1441">
            <div class="dm-v9-viewcart-page-header-text">
                <h1 id="dmV9ViewCartPageTitle1441">{$LANG.cartreviewcheckout}</h1>
                <p>Review your cart and complete your order.</p>
            </div>
        </section>

        <div class="row">

            <div class="col-md-3 sidebar hidden-xs hidden-sm">

                {include file="orderforms/standard_cart/sidebar-categories.tpl"}

            </div>

            <div class="col-md-9">

                {include file="orderforms/standard_cart/sidebar-categories-collapsed.tpl"}

                <div class="row">
                    <div class="col-md-8">

                        {if $promoerrormessage}
                            <div class="alert alert-warning text-center" role="alert">
                                {$promoerrormessage}
                            </div>
                        {elseif $errormessage}
                            <div class="alert alert-danger" role="alert">
                                <p>{$LANG.orderForm.correctErrors}:</p>
                                <ul>
                                    {$errormessage}
                                </ul>
                            </div>
                        {elseif $promotioncode && $rawdiscount eq "0.00"}
                            <div class="alert alert-info text-center" role="alert">
                                {$LANG.promoappliedbutnodiscount}
                            </div>
                        {elseif $promoaddedsuccess}
                            <div class="alert alert-success text-center" role="alert">
                                {$LANG.orderForm.promotionAccepted}
                            </div>
                        {/if}

                        {if $bundlewarnings}
                            <div class="alert alert-warning" role="alert">
                                <strong>{$LANG.bundlereqsnotmet}</strong><br />
                                <ul>
                                    {foreach from=$bundlewarnings item=warning}
                                        <li>{$warning}</li>
                                    {/foreach}
                                </ul>
                            </div>
                        {/if}

                        <form method="post" action="{$smarty.server.PHP_SELF}?a=view">

                            <div class="view-cart-items-header">
                                <div class="row">
                                    <div class="{if $showqtyoptions}col-sm-4{else}col-sm-6{/if} col-xs-7">
                                        {$LANG.orderForm.productOptions}
                                    </div>
                                    {if $showqtyoptions}
                                        <div class="col-sm-2 hidden-xs text-center">
                                            {$LANG.orderForm.qty}
                                        </div>
                                    {/if}
                                    <div class="col-sm-4 col-xs-5 text-right">
                                        {$LANG.orderForm.priceCycle}
                                    </div>
                                    <div class="col-sm-2 hidden-xs" aria-hidden="true"></div>
                                </div>
                            </div>
                            <div class="view-cart-items">

                                {foreach $products as $num => $product}
                                    <div class="item">
                                        <div class="row">
                                            <div class="{if $showqtyoptions}col-sm-4{else}col-sm-6{/if}">
                                                <span class="item-title">
                                                    {$product.productinfo.name}
                                                    <a href="{$smarty.server.PHP_SELF}?a=confproduct&i={$num}" class="btn btn-link btn-xs">
                                                        <i class="fas fa-pencil-alt"></i>
                                                        {$LANG.orderForm.edit}
                                                    </a>
                                                    <span class="visible-xs-inline">
                                                        <button type="button" class="btn btn-link btn-xs btn-remove-from-cart" onclick="removeItem('p','{$num}')">
                                                            <i class="fas fa-times"></i>
                                                            {$LANG.orderForm.remove}
                                                        </button>
                                                    </span>
                                                </span>
                                                <span class="item-group">
                                                    {$product.productinfo.groupname}
                                                </span>
                                                {if $product.domain}
                                                    <span class="item-domain">
                                                        {$product.domain}
                                                    </span>
                                                {/if}
                                                {if $product.configoptions}
                                                    <small>
                                                    {foreach key=confnum item=configoption from=$product.configoptions}
                                                        &nbsp;&raquo; {$configoption.name}: {if $configoption.type eq 1 || $configoption.type eq 2}{$configoption.option}{elseif $configoption.type eq 3}{if $configoption.qty}{$configoption.option}{else}{$LANG.no}{/if}{elseif $configoption.type eq 4}{$configoption.qty} x {$configoption.option}{/if}<br />
                                                    {/foreach}
                                                    </small>
                                                {/if}
                                            </div>
                                            {if $showqtyoptions}
                                                <div class="col-sm-2 item-qty">
                                                    {if $product.allowqty}
                                                        <input type="number" name="qty[{$num}]" value="{$product.qty}" class="form-control text-center" />
                                                        <button type="submit" class="btn btn-xs">
                                                            {$LANG.orderForm.update}
                                                        </button>
                                                    {/if}
                                                </div>
                                            {/if}
                                            <div class="col-sm-4 item-price">
                                                <span>{$product.pricing.totalTodayExcludingTaxSetup}</span>
                                                <span class="cycle">{$product.billingcyclefriendly}</span>
                                                {if $product.pricing.productonlysetup}
                                                    {$product.pricing.productonlysetup->toPrefixed()} {$LANG.ordersetupfee}
                                                {/if}
                                                {if $product.proratadate}<br />({$LANG.orderprorata} {$product.proratadate}){/if}
                                            </div>
                                            <div class="col-sm-2 hidden-xs dm-cart-remove-column">
                                                <button type="button" class="btn btn-link btn-xs btn-remove-from-cart" onclick="removeItem('p','{$num}')">
                                                    <i class="fas fa-times"></i>
                                                    <span class="dm-remove-label">{$LANG.orderForm.remove}</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    {foreach key=addonnum item=addon from=$product.addons}
                                        <div class="item">
                                            <div class="row">
                                                <div class="col-sm-7">
                                                    <span class="item-title">
                                                        {$addon.name}
                                                    </span>
                                                    <span class="item-group">
                                                        {$LANG.orderaddon}
                                                    </span>
                                                    {if $addon.setup}
                                                        <span class="item-setup">
                                                            {$addon.setup} {$LANG.ordersetupfee}
                                                        </span>
                                                    {/if}
                                                </div>
                                                <div class="col-sm-4 item-price">
                                                    <span>{$addon.totaltoday}</span>
                                                    <span class="cycle">{$addon.billingcyclefriendly}</span>
                                                </div>
                                            </div>
                                        </div>
                                    {/foreach}
                                {/foreach}

                                {foreach $addons as $num => $addon}
                                    <div class="item">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <span class="item-title">
                                                    {$addon.name}
                                                    <span class="visible-xs-inline">
                                                        <button type="button" class="btn btn-link btn-xs btn-remove-from-cart" onclick="removeItem('a','{$num}')">
                                                            <i class="fas fa-times"></i>
                                                            {$LANG.orderForm.remove}
                                                        </button>
                                                    </span>
                                                </span>
                                                <span class="item-group">
                                                    {$addon.productname}
                                                </span>
                                                {if $addon.domainname}
                                                    <span class="item-domain">
                                                        {$addon.domainname}
                                                    </span>
                                                {/if}
                                                {if $addon.setup}
                                                    <span class="item-setup">
                                                        {$addon.setup} {$LANG.ordersetupfee}
                                                    </span>
                                                {/if}
                                            </div>
                                            <div class="col-sm-4 item-price">
                                                <span>{$addon.pricingtext}</span>
                                                <span class="cycle">{$addon.billingcyclefriendly}</span>
                                            </div>
                                            <div class="col-sm-2 hidden-xs dm-cart-remove-column">
                                                <button type="button" class="btn btn-link btn-xs btn-remove-from-cart" onclick="removeItem('a','{$num}')">
                                                    <i class="fas fa-times"></i>
                                                    <span class="dm-remove-label">{$LANG.orderForm.remove}</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                {/foreach}

                                {foreach $domains as $num => $domain}
                                    <div class="item">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <span class="item-title">
                                                    {if $domain.type eq "register"}{$LANG.orderdomainregistration}{else}{$LANG.orderdomaintransfer}{/if}
                                                    <a href="{$smarty.server.PHP_SELF}?a=confdomains" class="btn btn-link btn-xs">
                                                        <i class="fas fa-pencil-alt"></i>
                                                        {$LANG.orderForm.edit}
                                                    </a>
                                                </span>
                                                {if $domain.domain}
                                                    <span class="item-domain">
                                                        {$domain.domain}
                                                    </span>
                                                {/if}
                                                {if $domain.dnsmanagement}&nbsp;&raquo; {$LANG.domaindnsmanagement}<br />{/if}
                                                {if $domain.emailforwarding}&nbsp;&raquo; {$LANG.domainemailforwarding}<br />{/if}
                                                {if $domain.idprotection}&nbsp;&raquo; {$LANG.domainidprotection}<br />{/if}
                                            </div>
                                            <div class="col-sm-4 item-price">
                                                {if count($domain.pricing) == 1 || $domain.type == 'transfer'}
                                                    <span name="{$domain.domain}Price">{$domain.price}</span>
                                                    <span class="cycle">{$domain.regperiod} {$domain.yearsLanguage}</span>
                                                    <span class="renewal cycle">
                                                        {if isset($domain.renewprice)}{lang key='domainrenewalprice'} <span class="renewal-price cycle">{$domain.renewprice->toPrefixed()}{$domain.shortRenewalYearsLanguage}{/if}</span>
                                                    </span>
                                                {else}
                                                    <span name="{$domain.domain}Price">{$domain.price}</span>
                                                    <div class="dropdown">
                                                        <button class="btn btn-default btn-xs dropdown-toggle" type="button" id="{$domain.domain}Pricing" name="{$domain.domain}Pricing" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                            {$domain.regperiod} {$domain.yearsLanguage}
                                                            <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu" aria-labelledby="{$domain.domain}Pricing">
                                                            {foreach $domain.pricing as $years => $price}
                                                                <li>
                                                                    <a href="#" onclick="selectDomainPeriodInCart('{$domain.domain}', '{$price.register}', {$years}, '{if $years == 1}{lang key='orderForm.year'}{else}{lang key='orderForm.years'}{/if}');return false;">
                                                                        {$years} {if $years == 1}{lang key='orderForm.year'}{else}{lang key='orderForm.years'}{/if} @ {$price.register}
                                                                    </a>
                                                                </li>
                                                            {/foreach}
                                                        </ul>
                                                    </div>
                                                    <span class="renewal cycle">
                                                        {lang key='domainrenewalprice'} <span class="renewal-price cycle">{if isset($domain.renewprice)}{$domain.renewprice->toPrefixed()}{$domain.shortRenewalYearsLanguage}{/if}</span>
                                                    </span>
                                                {/if}
                                            </div>
                                            <div class="col-sm-2 col-xs-12 dm-cart-remove-column dm-domain-remove-column">
                                                <button type="button" class="btn btn-link btn-xs btn-remove-from-cart" onclick="removeItem('d','{$num}')">
                                                    <i class="fas fa-times"></i>
                                                    <span class="dm-remove-label">{$LANG.orderForm.remove}</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                {/foreach}

                                {foreach key=num item=domain from=$renewals}
                                    <div class="item">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <span class="item-title">
                                                    {$LANG.domainrenewal}
                                                </span>
                                                <span class="item-domain">
                                                    {$domain.domain}
                                                </span>
                                                {if $domain.dnsmanagement}&nbsp;&raquo; {$LANG.domaindnsmanagement}<br />{/if}
                                                {if $domain.emailforwarding}&nbsp;&raquo; {$LANG.domainemailforwarding}<br />{/if}
                                                {if $domain.idprotection}&nbsp;&raquo; {$LANG.domainidprotection}<br />{/if}
                                            </div>
                                            <div class="col-sm-4 item-price">
                                                <span>{$domain.price}</span>
                                                <span class="cycle">{$domain.regperiod} {$LANG.orderyears}</span>
                                            </div>
                                            <div class="col-sm-2 dm-cart-remove-column">
                                                <button type="button" class="btn btn-link btn-xs btn-remove-from-cart" onclick="removeItem('r','{$num}')">
                                                    <i class="fas fa-times"></i>
                                                    <span class="dm-remove-label">{$LANG.orderForm.remove}</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                {/foreach}

                                {foreach $upgrades as $num => $upgrade}
                                    <div class="item">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <span class="item-title">
                                                    {$LANG.upgrade}
                                                </span>
                                                <span class="item-group">
                                                    {if $upgrade->type == 'service'}
                                                        {$upgrade->originalProduct->productGroup->name}<br>{$upgrade->originalProduct->name} => {$upgrade->newProduct->name}
                                                    {elseif $upgrade->type == 'addon'}
                                                        {$upgrade->originalAddon->name} => {$upgrade->newAddon->name}
                                                    {/if}
                                                </span>
                                                <span class="item-domain">
                                                    {if $upgrade->type == 'service'}
                                                        {$upgrade->service->domain}
                                                    {/if}
                                                </span>
                                            </div>
                                            <div class="col-sm-4 item-price">
                                                <span>{$upgrade->newRecurringAmount}</span>
                                                <span class="cycle">{$upgrade->localisedNewCycle}</span>
                                            </div>
                                            <div class="col-sm-2 dm-cart-remove-column">
                                                <button type="button" class="btn btn-link btn-xs btn-remove-from-cart" onclick="removeItem('u','{$num}')">
                                                    <i class="fas fa-times"></i>
                                                    <span class="dm-remove-label">{$LANG.orderForm.remove}</span>
                                                </button>
                                            </div>
                                        </div>
                                        {if $upgrade->totalDaysInCycle > 0}
                                            <div class="row row-upgrade-credit">
                                                <div class="col-sm-7">
                                                    <span class="item-group">
                                                        {$LANG.upgradeCredit}
                                                    </span>
                                                    <div class="upgrade-calc-msg">
                                                        {lang key="upgradeCreditDescription" daysRemaining=$upgrade->daysRemaining totalDays=$upgrade->totalDaysInCycle}
                                                    </div>
                                                </div>
                                                <div class="col-sm-4 item-price">
                                                    <span>-{$upgrade->creditAmount}</span>
                                                </div>
                                            </div>
                                        {/if}
                                    </div>
                                {/foreach}

                                {if $cartitems == 0}
                                    <div class="view-cart-empty">
                                        {$LANG.cartempty}
                                    </div>
                                {/if}

                            </div>

                            {if $cartitems > 0}
                                <div class="empty-cart">
                                    <button type="button" class="btn btn-link btn-xs" id="btnEmptyCart">
                                        <i class="fas fa-trash-alt"></i>
                                        <span>{$LANG.emptycart}</span>
                                    </button>
                                </div>
                            {/if}

                        </form>

                        {foreach $hookOutput as $output}
                            <div>
                                {$output}
                            </div>
                        {/foreach}

                        {foreach $gatewaysoutput as $gatewayoutput}
                            <div class="view-cart-gateway-checkout">
                                {$gatewayoutput}
                            </div>
                        {/foreach}

                        <div class="view-cart-tabs">
                            <ul class="nav nav-tabs" role="tablist">
                                {if $taxenabled && !$loggedin}
                                    <li role="presentation"><a href="#calcTaxes" aria-controls="calcTaxes" role="tab" data-toggle="tab">{$LANG.orderForm.estimateTaxes}</a></li>
                                {/if}
                            </ul>

                        </div>

                    </div>
                    <div class="col-md-4" id="scrollingPanelContainer">

                        <div class="order-summary" id="orderSummary">
                            <div class="loader" id="orderSummaryLoader" style="display: none;">
                                <i class="fas fa-fw fa-sync fa-spin"></i>
                            </div>
                            <h2>{$LANG.ordersummary}</h2>
                            <div class="summary-container">

                                <div class="subtotal clearfix">
                                    <span class="pull-left">{$LANG.ordersubtotal}</span>
                                    <span id="subtotal" class="pull-right">{$subtotal}</span>
                                </div>
                                {if $promotioncode || $taxrate || $taxrate2}
                                    <div class="bordered-totals">
                                        {if $promotioncode}
                                            <div class="clearfix">
                                                <span class="pull-left">{$promotiondescription}</span>
                                                <span id="discount" class="pull-right">{$discount}</span>
                                            </div>
                                        {/if}
                                        {if $taxrate}
                                            <div class="clearfix">
                                                <span class="pull-left">{$taxname} @ {$taxrate}%</span>
                                                <span id="taxTotal1" class="pull-right">{$taxtotal}</span>
                                            </div>
                                        {/if}
                                        {if $taxrate2}
                                            <div class="clearfix">
                                                <span class="pull-left">{$taxname2} @ {$taxrate2}%</span>
                                                <span id="taxTotal2" class="pull-right">{$taxtotal2}</span>
                                            </div>
                                        {/if}
                                    </div>
                                {/if}
                                <div class="recurring-totals clearfix">
                                    <span class="pull-left">{$LANG.orderForm.totals}</span>
                                    <span id="recurring" class="pull-right recurring-charges">
                                        <span id="recurringMonthly" {if !$totalrecurringmonthly}style="display:none;"{/if}>
                                            <span class="cost">{$totalrecurringmonthly}</span> {$LANG.orderpaymenttermmonthly}<br />
                                        </span>
                                        <span id="recurringQuarterly" {if !$totalrecurringquarterly}style="display:none;"{/if}>
                                            <span class="cost">{$totalrecurringquarterly}</span> {$LANG.orderpaymenttermquarterly}<br />
                                        </span>
                                        <span id="recurringSemiAnnually" {if !$totalrecurringsemiannually}style="display:none;"{/if}>
                                            <span class="cost">{$totalrecurringsemiannually}</span> {$LANG.orderpaymenttermsemiannually}<br />
                                        </span>
                                        <span id="recurringAnnually" {if !$totalrecurringannually}style="display:none;"{/if}>
                                            <span class="cost">{$totalrecurringannually}</span> {$LANG.orderpaymenttermannually}<br />
                                        </span>
                                        <span id="recurringBiennially" {if !$totalrecurringbiennially}style="display:none;"{/if}>
                                            <span class="cost">{$totalrecurringbiennially}</span> {$LANG.orderpaymenttermbiennially}<br />
                                        </span>
                                        <span id="recurringTriennially" {if !$totalrecurringtriennially}style="display:none;"{/if}>
                                            <span class="cost">{$totalrecurringtriennially}</span> {$LANG.orderpaymenttermtriennially}<br />
                                        </span>
                                    </span>
                                </div>

                                <div class="total-due-today total-due-today-padded">
                                    <span id="totalDueToday" class="amt">{$total}</span>
                                    <span>{$LANG.ordertotalduetoday}</span>
                                </div>

                                <div class="express-checkout-buttons">
                                    {foreach $expressCheckoutButtons as $checkoutButton}
                                        {$checkoutButton}
                                        <div class="separator">
                                            - {$LANG.or|strtoupper} -
                                        </div>
                                    {/foreach}
                                </div>

                                <div class="text-right">
                                    <a href="cart.php?a=checkout&e=false" class="btn btn-success btn-lg btn-checkout{if $cartitems == 0} disabled{/if}" id="checkout">
                                        {$LANG.orderForm.checkout}
                                        <i class="fas fa-arrow-right"></i>
                                    </a><br />
                                    <a href="cart.php" class="btn btn-link btn-continue-shopping" id="continueShopping">
                                        {$LANG.orderForm.continueShopping}
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form method="post" action="cart.php">
            <input type="hidden" name="a" value="remove" />
            <input type="hidden" name="r" value="" id="inputRemoveItemType" />
            <input type="hidden" name="i" value="" id="inputRemoveItemRef" />
            <div class="modal fade modal-remove-item" id="modalRemoveItem" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="{$LANG.orderForm.close}">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h4 class="modal-title">
                                <i class="fas fa-times fa-3x"></i>
                                <span>{$LANG.orderForm.removeItem}</span>
                            </h4>
                        </div>
                        <div class="modal-body">
                            {$LANG.cartremoveitemconfirm}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">{$LANG.no}</button>
                            <button type="submit" class="btn btn-primary">{$LANG.yes}</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <form method="post" action="cart.php">
            <input type="hidden" name="a" value="empty" />
            <div class="modal fade modal-remove-item" id="modalEmptyCart" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="{$LANG.orderForm.close}">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h4 class="modal-title">
                                <i class="fas fa-trash-alt fa-3x"></i>
                                <span>{$LANG.emptycart}</span>
                            </h4>
                        </div>
                        <div class="modal-body">
                            {$LANG.cartemptyconfirm}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">{$LANG.no}</button>
                            <button type="submit" class="btn btn-primary">{$LANG.yes}</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
{/if}
