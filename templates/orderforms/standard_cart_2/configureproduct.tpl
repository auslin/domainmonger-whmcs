{include file="orderforms/standard_cart/common.tpl"}

<script>
var _localLang = {
    'addToCart': '{$LANG.orderForm.addToCart|escape}',
    'addedToCartRemove': '{$LANG.orderForm.addedToCartRemove|escape}'
}
</script>


{literal}
<style>
#order-standard_cart.dm-v9-configure-product,
#order-standard_cart.dm-v9-configure-domain {
    color: #163a5f;
}
#order-standard_cart.dm-v9-configure-product .sidebar .panel,
#order-standard_cart.dm-v9-configure-product .sidebar .card,
#order-standard_cart.dm-v9-configure-domain .sidebar .panel,
#order-standard_cart.dm-v9-configure-domain .sidebar .card {
    margin-bottom: 18px;
    border: 1px solid #d9e1ea;
    border-radius: 4px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(22,58,95,.06);
    background: #fff;
}
#order-standard_cart.dm-v9-configure-product .sidebar .panel-heading,
#order-standard_cart.dm-v9-configure-product .sidebar .card-header,
#order-standard_cart.dm-v9-configure-domain .sidebar .panel-heading,
#order-standard_cart.dm-v9-configure-domain .sidebar .card-header {
    background: #163a5f !important;
    color: #fff !important;
    border: 0;
    padding: 11px 14px;
}
#order-standard_cart.dm-v9-configure-product .sidebar .panel-title,
#order-standard_cart.dm-v9-configure-product .sidebar .panel-title a,
#order-standard_cart.dm-v9-configure-product .sidebar .card-title,
#order-standard_cart.dm-v9-configure-product .sidebar .card-title a,
#order-standard_cart.dm-v9-configure-domain .sidebar .panel-title,
#order-standard_cart.dm-v9-configure-domain .sidebar .panel-title a,
#order-standard_cart.dm-v9-configure-domain .sidebar .card-title,
#order-standard_cart.dm-v9-configure-domain .sidebar .card-title a {
    color: #fff !important;
    font-weight: 700;
}
#order-standard_cart.dm-v9-configure-product .sidebar .list-group-item,
#order-standard_cart.dm-v9-configure-product .sidebar .list-group-item a,
#order-standard_cart.dm-v9-configure-product .sidebar .nav > li > a,
#order-standard_cart.dm-v9-configure-product .sidebar .panel-body a,
#order-standard_cart.dm-v9-configure-product .sidebar .card-body a,
#order-standard_cart.dm-v9-configure-domain .sidebar .list-group-item,
#order-standard_cart.dm-v9-configure-domain .sidebar .list-group-item a,
#order-standard_cart.dm-v9-configure-domain .sidebar .nav > li > a,
#order-standard_cart.dm-v9-configure-domain .sidebar .panel-body a,
#order-standard_cart.dm-v9-configure-domain .sidebar .card-body a {
    color: #4a5a6a;
    font-size: 14px;
    line-height: 1.35;
}
#order-standard_cart.dm-v9-configure-product .sidebar .list-group-item,
#order-standard_cart.dm-v9-configure-product .sidebar .nav > li > a,
#order-standard_cart.dm-v9-configure-product .sidebar .panel-body a,
#order-standard_cart.dm-v9-configure-product .sidebar .card-body a,
#order-standard_cart.dm-v9-configure-domain .sidebar .list-group-item,
#order-standard_cart.dm-v9-configure-domain .sidebar .nav > li > a,
#order-standard_cart.dm-v9-configure-domain .sidebar .panel-body a,
#order-standard_cart.dm-v9-configure-domain .sidebar .card-body a {
    border-top: 1px solid #e7edf3;
    padding: 11px 14px;
    background: #fff;
    border-radius: 0 !important;
}
#order-standard_cart.dm-v9-configure-product .sidebar .list-group-item:hover,
#order-standard_cart.dm-v9-configure-product .sidebar .list-group-item:hover a,
#order-standard_cart.dm-v9-configure-product .sidebar .nav > li > a:hover,
#order-standard_cart.dm-v9-configure-domain .sidebar .list-group-item:hover,
#order-standard_cart.dm-v9-configure-domain .sidebar .list-group-item:hover a,
#order-standard_cart.dm-v9-configure-domain .sidebar .nav > li > a:hover {
    background: #fff7ef;
    color: #f58220 !important;
    text-decoration: none;
}
#order-standard_cart.dm-v9-configure-product .sidebar .list-group-item.active,
#order-standard_cart.dm-v9-configure-product .sidebar .list-group-item.active a,
#order-standard_cart.dm-v9-configure-product .sidebar .nav > li.active > a,
#order-standard_cart.dm-v9-configure-domain .sidebar .list-group-item.active,
#order-standard_cart.dm-v9-configure-domain .sidebar .list-group-item.active a,
#order-standard_cart.dm-v9-configure-domain .sidebar .nav > li.active > a {
    background: #f58220 !important;
    border-color: #f58220 !important;
    color: #fff !important;
}
#order-standard_cart.dm-v9-configure-product .header-lined h1,
#order-standard_cart.dm-v9-configure-domain .header-lined h1 {
    color: #163a5f;
    font-weight: 700;
}
#order-standard_cart.dm-v9-configure-product .btn-primary,
#order-standard_cart.dm-v9-configure-product .btn-success,
#order-standard_cart.dm-v9-configure-product #btnCompleteProductConfig,
#order-standard_cart.dm-v9-configure-domain .btn-primary,
#order-standard_cart.dm-v9-configure-domain .btn-success,
#order-standard_cart.dm-v9-configure-domain #btnDomainContinue {
    background: #f58220 !important;
    border-color: #f58220 !important;
    color: #fff !important;
    font-weight: 700;
    text-decoration: none;
}
#order-standard_cart.dm-v9-configure-product .btn-primary:hover,
#order-standard_cart.dm-v9-configure-product .btn-primary:focus,
#order-standard_cart.dm-v9-configure-product .btn-success:hover,
#order-standard_cart.dm-v9-configure-product .btn-success:focus,
#order-standard_cart.dm-v9-configure-domain .btn-primary:hover,
#order-standard_cart.dm-v9-configure-domain .btn-primary:focus,
#order-standard_cart.dm-v9-configure-domain .btn-success:hover,
#order-standard_cart.dm-v9-configure-domain .btn-success:focus {
    background: #214e7a !important;
    border-color: #214e7a !important;
    color: #fff !important;
    text-decoration: none;
}
#order-standard_cart.dm-v9-configure-product .btn-default,
#order-standard_cart.dm-v9-configure-domain .btn-default {
    background: #163a5f;
    border-color: #102d4b;
    color: #fff;
    font-weight: 700;
}
#order-standard_cart.dm-v9-configure-product .btn-default:hover,
#order-standard_cart.dm-v9-configure-product .btn-default:focus,
#order-standard_cart.dm-v9-configure-domain .btn-default:hover,
#order-standard_cart.dm-v9-configure-domain .btn-default:focus {
    background: #214e7a;
    border-color: #214e7a;
    color: #fff;
}
#order-standard_cart.dm-v9-configure-product .form-control,
#order-standard_cart.dm-v9-configure-domain .form-control {
    border-color: #d9e1ea;
    box-shadow: none;
    color: #163a5f;
}
#order-standard_cart.dm-v9-configure-product .form-control:focus,
#order-standard_cart.dm-v9-configure-domain .form-control:focus {
    border-color: #f58220;
    box-shadow: 0 0 0 2px rgba(245,130,32,.12);
}
#order-standard_cart.dm-v9-configure-product a,
#order-standard_cart.dm-v9-configure-domain a {
    color: #163a5f;
}
#order-standard_cart.dm-v9-configure-product a:hover,
#order-standard_cart.dm-v9-configure-product a:focus,
#order-standard_cart.dm-v9-configure-domain a:hover,
#order-standard_cart.dm-v9-configure-domain a:focus {
    color: #f58220;
    text-decoration: none;
}
#order-standard_cart.dm-v9-configure-product .product-info,
#order-standard_cart.dm-v9-configure-product .field-container,
#order-standard_cart.dm-v9-configure-product .product-configurable-options,
#order-standard_cart.dm-v9-configure-product .addon-products .panel-addon,
#order-standard_cart.dm-v9-configure-domain .domain-selection-options .option,
#order-standard_cart.dm-v9-configure-domain .suggested-domains {
    background: #fff;
    border: 1px solid #d9e1ea;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(22,58,95,.06);
    overflow: hidden;
}
#order-standard_cart.dm-v9-configure-product .product-info {
    padding: 0 18px 16px;
    margin-bottom: 18px;
}

#order-standard_cart.dm-v9-configure-product .product-info .product-title,
#order-standard_cart.dm-v9-configure-product .product-info .product-title *,
#order-standard_cart.dm-v9-configure-product p.product-title,
#order-standard_cart.dm-v9-configure-product p.product-title * {
    color: #fff !important;
}
#order-standard_cart.dm-v9-configure-product .product-info .product-title a,
#order-standard_cart.dm-v9-configure-product p.product-title a {
    color: #fff !important;
    text-decoration: none;
}


#order-standard_cart.dm-v9-configure-product #producttotal {
    padding: 0;
}
#order-standard_cart.dm-v9-configure-product #producttotal .clearfix {
    border-radius: 0 !important;
    clear: both;
    display: block;
    margin-left: -16px;
    margin-right: -16px;
    padding: 8px 16px;
}
#order-standard_cart.dm-v9-configure-product #producttotal .clearfix:before,
#order-standard_cart.dm-v9-configure-product #producttotal .clearfix:after {
    content: " ";
    display: table;
}
#order-standard_cart.dm-v9-configure-product #producttotal .clearfix:after {
    clear: both;
}
#order-standard_cart.dm-v9-configure-product #producttotal .clearfix span,
#order-standard_cart.dm-v9-configure-product #producttotal .clearfix div {
    line-height: 1.35;
}
#order-standard_cart.dm-v9-configure-product #producttotal .clearfix:first-child,
#order-standard_cart.dm-v9-configure-product #producttotal .clearfix:nth-child(2) {
    background: #163a5f !important;
    color: #fff !important;
}
#order-standard_cart.dm-v9-configure-product #producttotal .clearfix:first-child *,
#order-standard_cart.dm-v9-configure-product #producttotal .clearfix:nth-child(2) * {
    color: #fff !important;
}
#order-standard_cart.dm-v9-configure-product #producttotal .total-due-today,
#order-standard_cart.dm-v9-configure-product #producttotal .total-due,
#order-standard_cart.dm-v9-configure-product #producttotal .amt {
    border-radius: 0 !important;
}
#order-standard_cart.dm-v9-configure-product #orderSummary .summary-container {
    padding: 16px;
}
#order-standard_cart.dm-v9-configure-product #containerProductValidationErrors.hidden,
#order-standard_cart.dm-v9-configure-product #containerProductValidationErrors.dm-v9-empty-validation {
    display: none !important;
}
#order-standard_cart.dm-v9-configure-product .product-info .product-title {
    background: #163a5f;
    color: #fff;
    font-weight: 700;
    margin: 0 -18px 14px;
    padding: 12px 18px;
}
#order-standard_cart.dm-v9-configure-product .field-container,
#order-standard_cart.dm-v9-configure-product .product-configurable-options {
    padding: 18px;
    margin-bottom: 18px;
}
#order-standard_cart.dm-v9-configure-product .sub-heading {
    border: 0;
    margin: 18px 0 0;
}
#order-standard_cart.dm-v9-configure-product .sub-heading span {
    background: #163a5f;
    border-radius: 4px 4px 0 0;
    color: #fff;
    display: block;
    font-weight: 700;
    padding: 11px 14px;
}
#order-standard_cart.dm-v9-configure-product #orderSummary .order-summary {
    background: #fff;
    border: 1px solid #d9e1ea;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(22,58,95,.08);
    overflow: hidden;
}
#order-standard_cart.dm-v9-configure-product #orderSummary .order-summary h2 {
    background: #163a5f;
    color: #fff;
    font-size: 16px;
    font-weight: 700;
    margin: 0;
    padding: 12px 16px;
}
#order-standard_cart.dm-v9-configure-product #orderSummary .summary-container {
    padding: 16px;
}
#order-standard_cart.dm-v9-configure-domain .domain-selection-options .option {
    margin-bottom: 14px;
}
#order-standard_cart.dm-v9-configure-domain .domain-selection-options .option > label {
    color: #163a5f;
    cursor: pointer;
    display: block;
    font-weight: 700;
    margin: 0;
    padding: 16px 18px;
}
#order-standard_cart.dm-v9-configure-domain .domain-selection-options .option > label input[type="radio"] {
    margin-right: 10px;
    position: relative;
    top: 2px;
}
#order-standard_cart.dm-v9-configure-domain .domain-selection-options .option:hover {
    border-color: #f58220;
}
#order-standard_cart.dm-v9-configure-domain .domain-input-group {
    background: #fbfcfd;
    border-top: 1px solid #e7edf3;
    padding: 16px 18px 18px 42px;
}
#order-standard_cart.dm-v9-configure-domain .input-group-addon {
    background: #f4f7fa;
    border-color: #d9e1ea;
    color: #163a5f;
    font-weight: 700;
}
#order-standard_cart.dm-v9-configure-domain #searchDomainInfo,
#order-standard_cart.dm-v9-configure-domain #primaryLookupResult {
    background: #fff;
    border: 1px solid #d9e1ea;
    border-radius: 4px;
    margin: 16px 0;
    padding: 16px;
}
#order-standard_cart.dm-v9-configure-domain .domain-checker-result-headline,
#order-standard_cart.dm-v9-configure-domain .domain-checker-available,
#order-standard_cart.dm-v9-configure-domain .domain-checker-unavailable {
    font-weight: 700;
}
#order-standard_cart.dm-v9-configure-domain .domain-checker-available {
    color: #2e7d32;
}
#order-standard_cart.dm-v9-configure-domain .domain-checker-unavailable {
    color: #b94a48;
}
#order-standard_cart.dm-v9-configure-domain .suggested-domains .panel-heading {
    background: #163a5f;
    border-color: #163a5f;
    color: #fff;
    font-weight: 700;
    padding: 12px 16px;
}
#order-standard_cart.dm-v9-configure-domain .suggested-domains .panel-body,
#order-standard_cart.dm-v9-configure-domain .suggested-domains .list-group-item,
#order-standard_cart.dm-v9-configure-domain .suggested-domains .panel-footer {
    border-color: #e7edf3;
}
#order-standard_cart.dm-v9-configure-domain .suggested-domains .list-group-item:hover {
    background: #fff7ef;
}
@media (max-width: 767px) {
    #order-standard_cart.dm-v9-configure-domain .domain-input-group {
        padding-left: 18px;
    }
}

#order-standard_cart.dm-v9-configure-product .header-lined,
#order-standard_cart.dm-v9-configure-product .header-lined h1 {
    text-align: center;
}

#order-standard_cart.dm-v9-configure-product > .row > .col-md-9 > .header-lined {
    width: 66.666667%;
    text-align: center;
}
#order-standard_cart.dm-v9-configure-product > .row > .col-md-9 > .header-lined h1 {
    text-align: center;
}
@media (max-width: 991px) {
    #order-standard_cart.dm-v9-configure-product > .row > .col-md-9 > .header-lined {
        width: 100%;
    }
}

#order-standard_cart.dm-v9-configure-product #producttotal .clearfix,
#order-standard_cart.dm-v9-configure-product #producttotal .total-due-today,
#order-standard_cart.dm-v9-configure-product #producttotal .total-due,
#order-standard_cart.dm-v9-configure-product #producttotal .amt {
    border-radius: 4px !important;
}
#order-standard_cart.dm-v9-configure-product #producttotal .clearfix:first-child,
#order-standard_cart.dm-v9-configure-product #producttotal .clearfix:nth-child(2) {
    overflow: hidden;
}
</style>
{/literal}

<div id="order-standard_cart" class="dm-v9-configure-product">

    <div class="row">

        <div class="col-md-3 sidebar hidden-xs hidden-sm">

            {include file="orderforms/standard_cart/sidebar-categories.tpl"}

        </div>

        <div class="col-md-9">

            <div class="header-lined">
                <h1>{$LANG.orderconfigure}</h1>
            </div>

            {* Duplicate main-area Categories/Actions selector removed; left sidebar remains. *}

            <form id="frmConfigureProduct">
                <input type="hidden" name="configure" value="true" />
                <input type="hidden" name="i" value="{$i}" />

                <div class="row">
                    <div class="col-md-8">

                        <p>{$LANG.orderForm.configureDesiredOptions}</p>

                        <div class="product-info">
                            <p class="product-title">{$productinfo.name}</p>
                            <p>{$productinfo.description}</p>
                        </div>

                        <div class="alert alert-danger hidden" role="alert" id="containerProductValidationErrors">
                            <p>{$LANG.orderForm.correctErrors}:</p>
                            <ul id="containerProductValidationErrorsList"></ul>
                        </div>

                        {if $pricing.type eq "recurring"}
                            <div class="field-container">
                                <div class="form-group">
                                    <label for="inputBillingcycle">{$LANG.cartchoosecycle}</label>
                                    <select name="billingcycle" id="inputBillingcycle" class="form-control select-inline" onchange="{if $configurableoptions}updateConfigurableOptions({$i}, this.value);{else}recalctotals();{/if}">
                                        {if $pricing.monthly}
                                            <option value="monthly"{if $billingcycle eq "monthly"} selected{/if}>
                                                {$pricing.monthly}
                                            </option>
                                        {/if}
                                        {if $pricing.quarterly}
                                            <option value="quarterly"{if $billingcycle eq "quarterly"} selected{/if}>
                                                {$pricing.quarterly}
                                            </option>
                                        {/if}
                                        {if $pricing.semiannually}
                                            <option value="semiannually"{if $billingcycle eq "semiannually"} selected{/if}>
                                                {$pricing.semiannually}
                                            </option>
                                        {/if}
                                        {if $pricing.annually}
                                            <option value="annually"{if $billingcycle eq "annually"} selected{/if}>
                                                {$pricing.annually}
                                            </option>
                                        {/if}
                                        {if $pricing.biennially}
                                            <option value="biennially"{if $billingcycle eq "biennially"} selected{/if}>
                                                {$pricing.biennially}
                                            </option>
                                        {/if}
                                        {if $pricing.triennially}
                                            <option value="triennially"{if $billingcycle eq "triennially"} selected{/if}>
                                                {$pricing.triennially}
                                            </option>
                                        {/if}
                                    </select>
                                </div>
                            </div>
                        {/if}

                        {if count($metrics) > 0}
                            <div class="sub-heading">
                                <span>{$LANG.metrics.title}</span>
                            </div>

                            <p>{$LANG.metrics.explanation}</p>

                            <ul>
                                {foreach $metrics as $metric}
                                    <li>
                                        {$metric.displayName}
                                        -
                                        {if count($metric.pricing) > 1}
                                            {$LANG.metrics.startingFrom} {$metric.lowestPrice} / {if $metric.unitName}{$metric.unitName}{else}{$LANG.metrics.unit}{/if}
                                            <button type="button" class="btn btn-default btn-xs" data-toggle="modal" data-target="#modalMetricPricing-{$metric.systemName}">
                                                {$LANG.metrics.viewPricing}
                                            </button>
                                        {elseif count($metric.pricing) == 1}
                                            {$metric.lowestPrice} / {if $metric.unitName}{$metric.unitName}{else}{$LANG.metrics.unit}{/if}
                                            {if $metric.includedQuantity > 0} ({$metric.includedQuantity} {$LANG.metrics.includedNotCounted}){/if}
                                        {/if}
                                        {include file="$template/usagebillingpricing.tpl"}
                                    </li>
                                {/foreach}
                            </ul>

                            <br>
                        {/if}

                        {if $productinfo.type eq "server"}
                            <div class="sub-heading">
                                <span>{$LANG.cartconfigserver}</span>
                            </div>

                            <div class="field-container">

                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="inputHostname">{$LANG.serverhostname}</label>
                                            <input type="text" name="hostname" class="form-control" id="inputHostname" value="{$server.hostname}" placeholder="servername.yourdomain.com">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="inputRootpw">{$LANG.serverrootpw}</label>
                                            <input type="password" name="rootpw" class="form-control" id="inputRootpw" value="{$server.rootpw}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="inputNs1prefix">{$LANG.serverns1prefix}</label>
                                            <input type="text" name="ns1prefix" class="form-control" id="inputNs1prefix" value="{$server.ns1prefix}" placeholder="ns1">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="inputNs2prefix">{$LANG.serverns2prefix}</label>
                                            <input type="text" name="ns2prefix" class="form-control" id="inputNs2prefix" value="{$server.ns2prefix}" placeholder="ns2">
                                        </div>
                                    </div>
                                </div>

                            </div>
                        {/if}

                        {if $configurableoptions}
                            <div class="sub-heading">
                                <span>{$LANG.orderconfigpackage}</span>
                            </div>
                            <div class="product-configurable-options" id="productConfigurableOptions">
                                <div class="row">
                                    {foreach $configurableoptions as $num => $configoption}
                                        {if $configoption.optiontype eq 1}
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="inputConfigOption{$configoption.id}">{$configoption.optionname}</label>
                                                    <select name="configoption[{$configoption.id}]" id="inputConfigOption{$configoption.id}" class="form-control">
                                                        {foreach key=num2 item=options from=$configoption.options}
                                                            <option value="{$options.id}"{if $configoption.selectedvalue eq $options.id} selected="selected"{/if}>
                                                                {$options.name}
                                                            </option>
                                                        {/foreach}
                                                    </select>
                                                </div>
                                            </div>
                                        {elseif $configoption.optiontype eq 2}
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="inputConfigOption{$configoption.id}">{$configoption.optionname}</label>
                                                    {foreach key=num2 item=options from=$configoption.options}
                                                        <br />
                                                        <label>
                                                            <input type="radio" name="configoption[{$configoption.id}]" value="{$options.id}"{if $configoption.selectedvalue eq $options.id} checked="checked"{/if} />
                                                            {if $options.name}
                                                                {$options.name}
                                                            {else}
                                                                {$LANG.enable}
                                                            {/if}
                                                        </label>
                                                    {/foreach}
                                                </div>
                                            </div>
                                        {elseif $configoption.optiontype eq 3}
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="inputConfigOption{$configoption.id}">{$configoption.optionname}</label>
                                                    <br />
                                                    <label>
                                                        <input type="checkbox" name="configoption[{$configoption.id}]" id="inputConfigOption{$configoption.id}" value="1"{if $configoption.selectedqty} checked{/if} />
                                                        {if $configoption.options.0.name}
                                                            {$configoption.options.0.name}
                                                        {else}
                                                            {$LANG.enable}
                                                        {/if}
                                                    </label>
                                                </div>
                                            </div>
                                        {elseif $configoption.optiontype eq 4}
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label for="inputConfigOption{$configoption.id}">{$configoption.optionname}</label>
                                                    {if $configoption.qtymaximum}
                                                        {if !$rangesliderincluded}
                                                            <script type="text/javascript" src="{$BASE_PATH_JS}/ion.rangeSlider.min.js"></script>
                                                            <link href="{$BASE_PATH_CSS}/ion.rangeSlider.css" rel="stylesheet">
                                                            <link href="{$BASE_PATH_CSS}/ion.rangeSlider.skinModern.css" rel="stylesheet">
                                                            {assign var='rangesliderincluded' value=true}
                                                        {/if}
                                                        <input type="text" name="configoption[{$configoption.id}]" value="{if $configoption.selectedqty}{$configoption.selectedqty}{else}{$configoption.qtyminimum}{/if}" id="inputConfigOption{$configoption.id}" class="form-control" />
                                                        <script>
                                                            var sliderTimeoutId = null;
                                                            var sliderRangeDifference = {$configoption.qtymaximum} - {$configoption.qtyminimum};
                                                            // The largest size that looks nice on most screens.
                                                            var sliderStepThreshold = 25;
                                                            // Check if there are too many to display individually.
                                                            var setLargerMarkers = sliderRangeDifference > sliderStepThreshold;

                                                            jQuery("#inputConfigOption{$configoption.id}").ionRangeSlider({
                                                                min: {$configoption.qtyminimum},
                                                                max: {$configoption.qtymaximum},
                                                                grid: true,
                                                                grid_snap: setLargerMarkers ? false : true,
                                                                onChange: function() {
                                                                    if (sliderTimeoutId) {
                                                                        clearTimeout(sliderTimeoutId);
                                                                    }

                                                                    sliderTimeoutId = setTimeout(function() {
                                                                        sliderTimeoutId = null;
                                                                        recalctotals();
                                                                    }, 250);
                                                                }
                                                            });
                                                        </script>
                                                    {else}
                                                        <div>
                                                            <input type="number" name="configoption[{$configoption.id}]" value="{if $configoption.selectedqty}{$configoption.selectedqty}{else}{$configoption.qtyminimum}{/if}" id="inputConfigOption{$configoption.id}" min="{$configoption.qtyminimum}" onchange="recalctotals()" onkeyup="recalctotals()" class="form-control form-control-qty" />
                                                            <span class="form-control-static form-control-static-inline">
                                                                x {$configoption.options.0.name}
                                                            </span>
                                                        </div>
                                                    {/if}
                                                </div>
                                            </div>
                                        {/if}
                                        {if $num % 2 != 0}
                                            </div>
                                            <div class="row">
                                        {/if}
                                    {/foreach}
                                </div>
                            </div>

                        {/if}

                        {if $customfields}

                            <div class="sub-heading">
                                <span>{$LANG.orderadditionalrequiredinfo}</span>
                            </div>

                            <div class="field-container">
                                {foreach $customfields as $customfield}
                                    <div class="form-group">
                                        <label for="customfield{$customfield.id}">{$customfield.name}</label>
                                        {$customfield.input}
                                        {if $customfield.description}
                                            <span class="field-help-text">
                                                {$customfield.description}
                                            </span>
                                        {/if}
                                    </div>
                                {/foreach}
                            </div>

                        {/if}

                        {if $addons || count($addonsPromoOutput) > 0}

                            <div class="sub-heading">
                                <span>{$LANG.cartavailableaddons}</span>
                            </div>

                            {foreach $addonsPromoOutput as $output}
                                <div>
                                    {$output}
                                </div>
                            {/foreach}

                            <div class="row addon-products">
                                {foreach $addons as $addon}
                                    <div class="col-sm-{if count($addons) > 1}6{else}12{/if}">
                                        <div class="panel panel-default panel-addon{if $addon.status} panel-addon-selected{/if}">
                                            <div class="panel-body">
                                                <label>
                                                    <input type="checkbox" name="addons[{$addon.id}]"{if $addon.status} checked{/if} />
                                                    {$addon.name}
                                                </label><br />
                                                {$addon.description}
                                            </div>
                                            <div class="panel-price">
                                                {$addon.pricing}
                                            </div>
                                            <div class="panel-add">
                                                <i class="fas fa-plus"></i>
                                                {$LANG.addtocart}
                                            </div>
                                        </div>
                                    </div>
                                {/foreach}
                            </div>

                        {/if}

                        <div class="alert alert-warning info-text-sm">
                            <i class="fas fa-question-circle"></i>
                            {$LANG.orderForm.haveQuestionsContact} <a href="contact.php" target="_blank" class="alert-link">{$LANG.orderForm.haveQuestionsClickHere}</a>
                        </div>

                    </div>
                    <div class="col-md-4" id="scrollingPanelContainer">

                        <div id="orderSummary">
                            <div class="order-summary">
                                <div class="loader" id="orderSummaryLoader">
                                    <i class="fas fa-fw fa-sync fa-spin"></i>
                                </div>
                                <h2>{$LANG.ordersummary}</h2>
                                <div class="summary-container" id="producttotal"></div>
                            </div>
                            <div class="text-center">
                                <button type="submit" id="btnCompleteProductConfig" class="btn btn-primary btn-lg">
                                    {$LANG.continue}
                                    <i class="fas fa-arrow-circle-right"></i>
                                </button>
                            </div>
                        </div>

                    </div>

                </div>

            </form>
        </div>
    </div>
</div>


<script>
(function($) {
    'use strict';

    function dmV9ToggleEmptyProductValidationBox() {
        var $box = $('#containerProductValidationErrors');
        var $list = $('#containerProductValidationErrorsList');

        if (!$box.length || !$list.length) {
            return;
        }

        if ($.trim($list.text()).length || $list.children().length) {
            $box.removeClass('dm-v9-empty-validation');
        } else {
            $box.addClass('dm-v9-empty-validation').hide();
        }
    }

    $(function() {
        dmV9ToggleEmptyProductValidationBox();

        if (window.MutationObserver && $('#containerProductValidationErrorsList').length) {
            var observer = new MutationObserver(dmV9ToggleEmptyProductValidationBox);
            observer.observe($('#containerProductValidationErrorsList')[0], {
                childList: true,
                subtree: true,
                characterData: true
            });
        }

        $('#frmConfigureProduct').on('change input submit', function() {
            window.setTimeout(dmV9ToggleEmptyProductValidationBox, 0);
        });
    });
})(jQuery);
</script>

<script>recalctotals();</script>
