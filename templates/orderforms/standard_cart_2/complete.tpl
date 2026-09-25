{include file="orderforms/standard_cart_2/common.tpl"}

<div id="order-standard_cart">

    <div class="row">
        <div class="cart-sidebar">
            {include file="orderforms/standard_cart_2/sidebar-categories.tpl"}
        </div>
        <div class="cart-body">
            <div class="header-lined">
                <h1 class="font-size-36">{lang key="orderconfirmation"}</h1>
            </div>
            {include file="orderforms/standard_cart_2/sidebar-categories-collapsed.tpl"}

            <div class="row">
                <div class="col-sm-10 col-sm-offset-1 offset-sm-1 text-center">
                    <p>{lang key="orderreceived"}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-8 col-sm-offset-2 offset-sm-2">
                    <div class="alert alert-info order-confirmation">
                        {lang key="ordernumberis"} <span>{$ordernumber}</span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-10 col-sm-offset-1 offset-sm-1 text-center">
                    <p>{lang key="orderfinalinstructions"}</p>
                </div>
            </div>

            {if $expressCheckoutInfo}
                <div class="alert alert-info text-center">
                    {$expressCheckoutInfo}
                </div>
            {elseif $expressCheckoutError}
                <div class="alert alert-danger text-center">
                    {$expressCheckoutError}
                </div>
            {elseif $invoiceid && !$ispaid}
                <div class="alert alert-warning text-center">
                    {lang key="ordercompletebutnotpaid"}
                    <br /><br />
                    <a href="{$WEB_ROOT}/viewinvoice.php?id={$invoiceid}" target="_blank" class="alert-link">
                        {lang key="invoicenumber"}{$invoiceid}
                    </a>
                </div>
            {/if}

            {foreach $addons_html as $addon_html}
                <div class="order-confirmation-addon-output">
                    {$addon_html}
                </div>
            {/foreach}

            {if $ispaid}
                <!-- Enter any HTML code which should be displayed when a user has completed checkout here -->
                <!-- Common uses of this include conversion and affiliate tracking scripts -->
            {/if}

            <div class="text-center">
                <a href="{$WEB_ROOT}/clientarea.php" class="btn btn-default">
                    {lang key="orderForm.continueToClientArea"}
                    &nbsp;<i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>

            {if $hasRecommendations}
                {include file="orderforms/standard_cart_2/includes/product-recommendations.tpl"}
            {/if}
        </div>
    </div>
</div>
