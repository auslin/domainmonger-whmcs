{include file="orderforms/standard_cart/common.tpl"}


{literal}
<style>
#order-standard_cart.dm-v9-configure-product,
#order-standard_cart.dm-v9-configure-domain {
    color: #163a5f;
}
/* WHMCS v9 uses w-hidden for JavaScript-controlled domain result states.
   Keep this page-scoped and non-important so jQuery .show() can reveal a
   valid result while all inactive/fallback states remain hidden initially. */
#order-standard_cart.dm-v9-configure-domain .w-hidden {
    display: none;
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
/* Patch 1449: keep promotional badges beside the domain name and preserve
   Contact Support only for true manual-price results. */
#order-standard_cart.dm-v9-configure-domain .suggested-domains .domain-suggestion {
    align-items: center;
    display: flex;
    gap: 0;
}
#order-standard_cart.dm-v9-configure-domain .suggested-domains .domain-suggestion .promo {
    border-radius: 999px;
    font-size: 10px;
    font-weight: 700;
    line-height: 1.4;
    margin-left: 7px;
    padding: 2px 8px;
    text-transform: uppercase;
    vertical-align: middle;
}
#order-standard_cart.dm-v9-configure-domain .suggested-domains .domain-suggestion .promo.sale {
    background: #2e7d32 !important;
    color: #fff !important;
}
#order-standard_cart.dm-v9-configure-domain .suggested-domains .domain-suggestion .actions {
    align-items: center;
    display: flex;
    float: none;
    gap: 8px;
    margin-left: auto;
}
/* A priced, addable suggestion must never also display the manual-support
   fallback. If WHMCS removes Add to Cart for a ContactUs result, the fallback
   remains available as intended. */
#order-standard_cart.dm-v9-configure-domain .suggested-domains .btn-add-to-cart + .domain-contact-support {
    display: none !important;
}
@media (max-width: 767px) {
    #order-standard_cart.dm-v9-configure-domain .suggested-domains .domain-suggestion {
        flex-wrap: wrap;
        justify-content: center;
    }
    #order-standard_cart.dm-v9-configure-domain .suggested-domains .domain-suggestion .actions {
        justify-content: center;
        margin-left: 0;
        margin-top: 5px;
        width: 100%;
    }
}

#order-standard_cart.dm-v9-configure-domain .domain-input-group .input-group {
    width: 100%;
}
#order-standard_cart.dm-v9-configure-domain .domain-input-group .input-group-addon {
    background: transparent;
    border: 0;
    border-bottom: 1px solid #d9e1ea;
    border-left: 1px solid #d9e1ea;
    border-top: 1px solid #d9e1ea;
    border-radius: 4px 0 0 4px;
    color: #163a5f;
    font-weight: 700;
    line-height: 1.4;
    min-width: 56px;
    padding: 10px 12px;
    text-align: center;
    vertical-align: middle;
}
#order-standard_cart.dm-v9-configure-domain .domain-input-group .input-group .form-control {
    border-bottom-left-radius: 0;
    border-top-left-radius: 0;
    height: 42px;
}
#order-standard_cart.dm-v9-configure-domain .btn-add-to-cart {
    background: #f58220;
    border-color: #f58220;
    color: #fff;
    font-weight: 700;
    min-width: 120px;
}
#order-standard_cart.dm-v9-configure-domain .btn-add-to-cart:hover,
#order-standard_cart.dm-v9-configure-domain .btn-add-to-cart:focus {
    background: #214e7a;
    border-color: #214e7a;
    color: #fff;
}
#order-standard_cart.dm-v9-configure-domain .btn-add-to-cart span.added,
#order-standard_cart.dm-v9-configure-domain .btn-add-to-cart span.unavailable {
    display: none;
}
#order-standard_cart.dm-v9-configure-domain .btn-add-to-cart span.to-add {
    display: inline;
}
#order-standard_cart.dm-v9-configure-domain #primaryLookupResult,
#order-standard_cart.dm-v9-configure-domain .suggested-domains {
    margin-bottom: 18px;
}
#order-standard_cart.dm-v9-configure-domain #btnDomainContinue {
    margin-top: 18px;
}

#order-standard_cart.dm-v9-configure-domain #primaryLookupResult > .domain-contact-support.headline {
    display: none;
}
@media (min-width: 768px) {
    #order-standard_cart.dm-v9-configure-domain .domain-input-group > .row {
        align-items: center;
        display: flex;
        margin-left: 0;
        margin-right: 0;
    }
    #order-standard_cart.dm-v9-configure-domain .domain-input-group > .row > .col-sm-8,
    #order-standard_cart.dm-v9-configure-domain .domain-input-group > .row > .col-sm-8.col-sm-offset-1 {
        flex: 1 1 auto;
        float: none !important;
        margin-left: 0 !important;
        padding-left: 0;
        padding-right: 0;
        width: auto !important;
    }
    #order-standard_cart.dm-v9-configure-domain .domain-input-group > .row > .col-sm-2 {
        flex: 0 0 130px;
        float: none !important;
        margin-left: 18px;
        padding-left: 0;
        padding-right: 0;
        width: 130px !important;
    }
    #order-standard_cart.dm-v9-configure-domain .domain-input-group .domains-row {
        align-items: center;
        display: flex;
        margin-left: 0;
        margin-right: 0;
    }
    #order-standard_cart.dm-v9-configure-domain .domain-input-group .domains-row > .col-xs-9 {
        flex: 1 1 auto;
        float: none !important;
        padding-left: 0;
        padding-right: 10px;
        width: auto !important;
    }
    #order-standard_cart.dm-v9-configure-domain .domain-input-group .domains-row > .col-xs-3 {
        flex: 0 0 145px;
        float: none !important;
        padding-left: 0;
        padding-right: 0;
        width: 145px !important;
    }
    #order-standard_cart.dm-v9-configure-domain .domain-input-group .input-group-addon,
    #order-standard_cart.dm-v9-configure-domain .domain-input-group .form-control,
    #order-standard_cart.dm-v9-configure-domain .domain-input-group button[type="submit"] {
        height: 42px;
        line-height: 1.4;
    }
    #order-standard_cart.dm-v9-configure-domain .domain-input-group .input-group-addon {
        vertical-align: middle;
    }
    #order-standard_cart.dm-v9-configure-domain .domain-input-group button[type="submit"] {
        align-items: center;
        display: flex;
        justify-content: center;
        margin: 0;
        padding-bottom: 0;
        padding-top: 0;
        width: 100%;
    }
}

#order-standard_cart.dm-v9-configure-domain #domainowndomain > .row,
#order-standard_cart.dm-v9-configure-domain #domainsubdomain > .row {
    align-items: center;
    display: flex;
    margin-left: 0;
    margin-right: 0;
}
#order-standard_cart.dm-v9-configure-domain #domainowndomain > .row > .col-sm-9,
#order-standard_cart.dm-v9-configure-domain #domainsubdomain > .row > .col-sm-9 {
    flex: 1 1 auto;
    float: none !important;
    margin-left: 0 !important;
    padding-left: 0;
    padding-right: 0;
    width: auto !important;
}
#order-standard_cart.dm-v9-configure-domain #domainowndomain > .row > .col-sm-2,
#order-standard_cart.dm-v9-configure-domain #domainsubdomain > .row > .col-sm-2 {
    flex: 0 0 130px;
    float: none !important;
    margin-left: 18px;
    padding-left: 0;
    padding-right: 0;
    width: 130px !important;
}
#order-standard_cart.dm-v9-configure-domain #domainowndomain .domains-row,
#order-standard_cart.dm-v9-configure-domain #domainsubdomain .domains-row {
    align-items: stretch;
    display: flex;
    margin-left: 0;
    margin-right: 0;
    width: 100%;
}
#order-standard_cart.dm-v9-configure-domain #domainowndomain .domains-row > .col-xs-2,
#order-standard_cart.dm-v9-configure-domain #domainsubdomain .domains-row > .col-xs-2 {
    flex: 0 0 64px;
    float: none !important;
    padding-left: 0;
    padding-right: 0;
    width: 64px !important;
}
#order-standard_cart.dm-v9-configure-domain #domainowndomain .domains-row > .col-xs-7,
#order-standard_cart.dm-v9-configure-domain #domainsubdomain .domains-row > .col-xs-5:first-of-type {
    flex: 1 1 auto;
    float: none !important;
    padding-left: 0;
    padding-right: 10px;
    width: auto !important;
}
#order-standard_cart.dm-v9-configure-domain #domainowndomain .domains-row > .col-xs-3,
#order-standard_cart.dm-v9-configure-domain #domainsubdomain .domains-row > .col-xs-5:last-of-type {
    flex: 0 0 145px;
    float: none !important;
    padding-left: 0;
    padding-right: 0;
    width: 145px !important;
}
#order-standard_cart.dm-v9-configure-domain #domainowndomain .form-control-static,
#order-standard_cart.dm-v9-configure-domain #domainsubdomain .form-control-static {
    align-items: center;
    background: transparent;
    border: 1px solid #d9e1ea;
    border-radius: 4px 0 0 4px;
    color: #163a5f;
    display: flex;
    font-weight: 700;
    height: 42px;
    justify-content: center;
    line-height: 1.4;
    margin: 0;
    padding: 0 10px;
    text-align: center;
    width: 100%;
}
#order-standard_cart.dm-v9-configure-domain #domainowndomain .form-control,
#order-standard_cart.dm-v9-configure-domain #domainsubdomain .form-control,
#order-standard_cart.dm-v9-configure-domain #domainowndomain button[type="submit"],
#order-standard_cart.dm-v9-configure-domain #domainsubdomain button[type="submit"] {
    height: 42px;
    line-height: 1.4;
}
#order-standard_cart.dm-v9-configure-domain #domainowndomain #owndomainsld,
#order-standard_cart.dm-v9-configure-domain #domainsubdomain #subdomainsld {
    border-bottom-left-radius: 0;
    border-top-left-radius: 0;
}
#order-standard_cart.dm-v9-configure-domain #domainowndomain button[type="submit"],
#order-standard_cart.dm-v9-configure-domain #domainsubdomain button[type="submit"] {
    align-items: center;
    display: flex;
    justify-content: center;
    margin: 0;
    padding-bottom: 0;
    padding-top: 0;
    width: 100%;
}
#order-standard_cart.dm-v9-configure-domain button.btn:empty,
#order-standard_cart.dm-v9-configure-domain .btn.domain-contact-support.headline:empty {
    display: none !important;
}
@media (max-width: 767px) {
    #order-standard_cart.dm-v9-configure-domain .domain-input-group {
        padding-left: 18px;
    }
}

#order-standard_cart.dm-v9-configure-domain .header-lined,
#order-standard_cart.dm-v9-configure-domain .header-lined h1 {
    text-align: center;
}
</style>
{/literal}

<div id="order-standard_cart" class="dm-v9-configure-domain">

    <div class="row">

        <div class="col-md-3 sidebar hidden-xs hidden-sm">

            {include file="orderforms/standard_cart/sidebar-categories.tpl"}

        </div>

        <div class="col-md-9">

            {* Duplicate main-area Categories/Actions selector removed; left sidebar remains. *}

            <form id="frmProductDomain">
                <input type="hidden" id="frmProductDomainPid" value="{$pid}" />
                <div class="domain-selection-options">
                    {if $incartdomains}
                        <div class="option">
                            <label>
                                <input type="radio" name="domainoption" value="incart" id="selincart" />{$LANG.cartproductdomainuseincart}
                            </label>
                            <div class="domain-input-group clearfix" id="domainincart">
                                <div class="row">
                                    <div class="col-sm-8 col-sm-offset-1 col-md-6 col-md-offset-2">
                                        <div class="domains-row">
                                            <select id="incartsld" name="incartdomain" class="form-control">
                                                {foreach key=num item=incartdomain from=$incartdomains}
                                                    <option value="{$incartdomain}">{$incartdomain}</option>
                                                {/foreach}
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-2">
                                        <button type="submit" class="btn btn-primary btn-block">
                                            {$LANG.orderForm.use}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/if}
                    {if $registerdomainenabled}
                        <div class="option">
                            <label>
                                <input type="radio" name="domainoption" value="register" id="selregister"{if $domainoption eq "register"} checked{/if} />{$LANG.cartregisterdomainchoice|sprintf2:$companyname}
                            </label>
                            <div class="domain-input-group clearfix" id="domainregister">
                                <div class="row">
                                    <div class="col-sm-8 col-sm-offset-1">
                                        <div class="row domains-row">
                                            <div class="col-xs-9">
                                                <div class="input-group">
                                                    <span class="input-group-addon">{$LANG.orderForm.www}</span>
                                                    <input type="text" id="registersld" value="{$sld}" class="form-control" autocapitalize="none" data-toggle="tooltip" data-placement="top" data-trigger="manual" title="{lang key='orderForm.enterDomain'}" />
                                                </div>
                                            </div>
                                            <div class="col-xs-3">
                                                <select id="registertld" class="form-control">
                                                    {foreach from=$registertlds item=listtld}
                                                        <option value="{$listtld}"{if $listtld eq $tld} selected="selected"{/if}>{$listtld}</option>
                                                    {/foreach}
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-2">
                                        <button type="submit" class="btn btn-primary btn-block">
                                            {$LANG.orderForm.check}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/if}
                    {if $transferdomainenabled}
                        <div class="option">
                            <label>
                                <input type="radio" name="domainoption" value="transfer" id="seltransfer"{if $domainoption eq "transfer"} checked{/if} />{$LANG.carttransferdomainchoice|sprintf2:$companyname}
                            </label>
                            <div class="domain-input-group clearfix" id="domaintransfer">
                                <div class="row">
                                    <div class="col-sm-8 col-sm-offset-1">
                                        <div class="row domains-row">
                                            <div class="col-xs-9">
                                                <div class="input-group">
                                                    <span class="input-group-addon">www.</span>
                                                    <input type="text" id="transfersld" value="{$sld}" class="form-control" autocapitalize="none" data-toggle="tooltip" data-placement="top" data-trigger="manual" title="{lang key='orderForm.enterDomain'}"/>
                                                </div>
                                            </div>
                                            <div class="col-xs-3">
                                                <select id="transfertld" class="form-control">
                                                    {foreach from=$transfertlds item=listtld}
                                                        <option value="{$listtld}"{if $listtld eq $tld} selected="selected"{/if}>{$listtld}</option>
                                                    {/foreach}
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-2">
                                        <button type="submit" class="btn btn-primary btn-block">
                                            {$LANG.orderForm.transfer}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/if}
                    {if $owndomainenabled}
                        <div class="option">
                            <label>
                                <input type="radio" name="domainoption" value="owndomain" id="selowndomain"{if $domainoption eq "owndomain"} checked{/if} />{$LANG.cartexistingdomainchoice|sprintf2:$companyname}
                            </label>
                            <div class="domain-input-group clearfix" id="domainowndomain">
                                <div class="row">
                                    <div class="col-sm-9">
                                        <div class="row domains-row">
                                            <div class="col-xs-2 text-right">
                                                <p class="form-control-static">www.</p>
                                            </div>
                                            <div class="col-xs-7">
                                                <input type="text" id="owndomainsld" value="{$sld}" placeholder="{$LANG.yourdomainplaceholder}" class="form-control" autocapitalize="none" data-toggle="tooltip" data-placement="top" data-trigger="manual" title="{lang key='orderForm.enterDomain'}" />
                                            </div>
                                            <div class="col-xs-3">
                                                <input type="text" id="owndomaintld" value="{$tld|substr:1}" placeholder="{$LANG.yourtldplaceholder}" class="form-control" autocapitalize="none" data-toggle="tooltip" data-placement="top" data-trigger="manual" title="{lang key='orderForm.required'}" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-2">
                                        <button type="submit" class="btn btn-primary btn-block" id="useOwnDomain">
                                            {$LANG.orderForm.use}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/if}
                    {if $subdomains}
                        <div class="option">
                            <label>
                                <input type="radio" name="domainoption" value="subdomain" id="selsubdomain"{if $domainoption eq "subdomain"} checked{/if} />{$LANG.cartsubdomainchoice|sprintf2:$companyname}
                            </label>
                            <div class="domain-input-group clearfix" id="domainsubdomain">
                                <div class="row">
                                    <div class="col-sm-9">
                                        <div class="row domains-row">
                                            <div class="col-xs-2 text-right">
                                                <p class="form-control-static">http://</p>
                                            </div>
                                            <div class="col-xs-5">
                                                <input type="text" id="subdomainsld" value="{$sld}" placeholder="yourname" class="form-control" autocapitalize="none" data-toggle="tooltip" data-placement="top" data-trigger="manual" title="{lang key='orderForm.enterDomain'}" />
                                            </div>
                                            <div class="col-xs-5">
                                                <select id="subdomaintld" class="form-control">
                                                    {foreach $subdomains as $subid => $subdomain}
                                                        <option value="{$subid}">{$subdomain}</option>
                                                    {/foreach}
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-2">
                                        <button type="submit" class="btn btn-primary btn-block">
                                            {$LANG.orderForm.check}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/if}
                </div>

                {if $freedomaintlds}
                    <p>* <em>{$LANG.orderfreedomainregistration} {$LANG.orderfreedomainappliesto}: {$freedomaintlds}</em></p>
                {/if}

            </form>

            <div class="clearfix"></div>
            <form method="post" action="cart.php?a=add&pid={$pid}&domainselect=1" id="frmProductDomainSelections">

                <div id="DomainSearchResults" class="w-hidden">

                    <div id="searchDomainInfo">
                        <p id="primaryLookupSearching" class="domain-lookup-loader domain-lookup-primary-loader domain-searching domain-checker-result-headline">
                            <i class="fas fa-spinner fa-spin"></i>
                            <span class="domain-lookup-register-loader">{lang key='orderForm.checkingAvailability'}...</span>
                            <span class="domain-lookup-transfer-loader">{lang key='orderForm.verifyingTransferEligibility'}...</span>
                            <span class="domain-lookup-other-loader">{lang key='orderForm.verifyingDomain'}...</span>
                        </p>
                        <div id="primaryLookupResult" class="domain-lookup-result domain-lookup-primary-results w-hidden">
                            <div class="domain-unavailable domain-checker-unavailable headline">{lang key='orderForm.domainIsUnavailable'}</div>
                            <div class="domain-available domain-checker-available headline">{$LANG.domainavailable1} <strong></strong> {$LANG.domainavailable2}</div>
                            <div class="btn btn-primary domain-contact-support headline">{lang key='domainContactUs' defaultValue='Contact Support to Purchase'}</div>
                            <div class="transfer-eligible">
                                <p class="domain-checker-available headline">{lang key='orderForm.transferEligible'}</p>
                                <p>{lang key='orderForm.transferUnlockBeforeContinuing'}</p>
                            </div>
                            <div class="transfer-not-eligible">
                                <p class="domain-checker-unavailable headline">{lang key='orderForm.transferNotEligible'}</p>
                                <p>{lang key='orderForm.transferNotRegistered'}</p>
                                <p>{lang key='orderForm.trasnferRecentlyRegistered'}</p>
                                <p>{lang key='orderForm.transferAlternativelyRegister'}</p>
                            </div>
                            <div class="domain-invalid">
                                <p class="domain-checker-unavailable headline">{lang key='orderForm.domainInvalid'}</p>
                                <p>
                                    {lang key='orderForm.domainLetterOrNumber'}<span class="domain-length-restrictions">{lang key='orderForm.domainLengthRequirements'}</span><br />
                                    {lang key='orderForm.domainInvalidCheckEntry'}
                                </p>
                            </div>
                            <div class="domain-price">
                                <span class="register-price-label">{lang key='orderForm.domainPriceRegisterLabel'}</span>
                                <span class="transfer-price-label w-hidden">{lang key='orderForm.domainPriceTransferLabel'}</span>
                                <span class="price"></span>
                            </div>
                            <p class="domain-error domain-checker-unavailable headline"></p>
                            <input type="hidden" id="resultDomainOption" name="domainoption" />
                            <input type="hidden" id="resultDomain" name="domains[]" />
                            <input type="hidden" id="resultDomainPricingTerm" />
                        </div>
                    </div>

                    {if $registerdomainenabled}
                        {if $spotlightTlds}
                            <div id="spotlightTlds" class="spotlight-tlds clearfix w-hidden">
                                <div class="spotlight-tlds-container">
                                    {foreach $spotlightTlds as $key => $data}
                                        <div class="spotlight-tld-container spotlight-tld-container-{$spotlightTlds|count}">
                                            <div id="spotlight{$data.tldNoDots}" class="spotlight-tld">
                                                {if $data.group}
                                                    <div class="spotlight-tld-{$data.group}">{$data.groupDisplayName}</div>
                                                {/if}
                                                {$data.tld}
                                                <span class="domain-lookup-loader domain-lookup-spotlight-loader">
                                                    <i class="fas fa-spinner fa-spin"></i>
                                                </span>
                                                <div class="domain-lookup-result">
                                                    <button type="button" class="btn unavailable w-hidden" disabled="disabled">
                                                        {lang key='domainunavailable'}
                                                    </button>
                                                    <button type="button" class="btn invalid w-hidden" disabled="disabled">
                                                        {lang key='domainunavailable'}
                                                    </button>
                                                    <span class="available price w-hidden">{$data.register}</span>
                                                    <button type="button" class="btn w-hidden btn-add-to-cart product-domain" data-whois="0" data-domain="">
                                                        <span class="to-add">{lang key='orderForm.add'}</span>
                                                        <span class="added">{lang key='domaincheckeradded'}</span>
                                                        <span class="unavailable">{$LANG.domaincheckertaken}</span>
                                                    </button>
                                                    <button type="button" class="btn btn-primary domain-contact-support w-hidden">Contact Support to Purchase</button>
                                                </div>
                                            </div>
                                        </div>
                                    {/foreach}
                                </div>
                            </div>
                        {/if}

                        <div class="suggested-domains w-hidden">
                            <div class="panel-heading">
                                {lang key='orderForm.suggestedDomains'}
                            </div>
                            <div id="suggestionsLoader" class="panel-body domain-lookup-loader domain-lookup-suggestions-loader">
                                <i class="fas fa-spinner fa-spin"></i> {lang key='orderForm.generatingSuggestions'}
                            </div>
                            <div class="panel-body domain-lookup-message domain-lookup-suggestions-message w-hidden">
                                {lang key='domainSearch.errors.noSuggestions'}
                            </div>
                            {* Patch 1448: WHMCS 9 scripts clone div.domain-suggestion rows.
                               The legacy ul/li structure returned suggestion data but rendered no rows. *}
                            <div id="domainSuggestions" class="domain-lookup-result list-group w-hidden">
                                <div class="domain-suggestion list-group-item w-hidden">
                                    <span class="domain"></span><span class="extension"></span>
                                    <span class="promo w-hidden"></span>
                                    <div class="actions">
                                        <button type="button" class="btn btn-add-to-cart product-domain" data-whois="1" data-domain="">
                                            <span class="to-add">{$LANG.addtocart}</span>
                                            <span class="loading">
                                                <i class="fas fa-spinner fa-spin"></i> {lang key='loading'}
                                            </span>
                                            <span class="added">{lang key='domaincheckeradded'}</span>
                                            <span class="unavailable">{$LANG.domaincheckertaken}</span>
                                        </button>
                                        <button type="button" class="btn btn-primary domain-contact-support w-hidden">{lang key='domainChecker.contactSupport' defaultValue='Contact Support to Purchase'}</button>
                                        <span class="price"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="panel-footer more-suggestions w-hidden text-center">
                                <a id="moreSuggestions" href="#" onclick="loadMoreSuggestions();return false;">{lang key='domainsmoresuggestions'}</a>
                                <span id="noMoreSuggestions" class="no-more small w-hidden">{lang key='domaincheckernomoresuggestions'}</span>
                            </div>
                            <div class="text-center text-muted domain-suggestions-warning">
                                <p>{lang key='domainssuggestionswarnings'}</p>
                            </div>
                        </div>
                    {/if}
                </div>

                <div class="text-center">
                    <button id="btnDomainContinue" type="submit" class="btn btn-primary btn-lg w-hidden" disabled="disabled">
                        {$LANG.continue}
                        &nbsp;<i class="fas fa-arrow-circle-right"></i>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
