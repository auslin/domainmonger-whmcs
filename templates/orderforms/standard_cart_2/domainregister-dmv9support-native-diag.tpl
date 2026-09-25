{include file="orderforms/standard_cart_2/common.tpl"}

<div id="order-standard_cart">

    <div class="dm-v9-support-banner">
        <strong>DM v9 Support Diagnostic Route</strong>
        <span>Native/default register-page diagnostics. Isolated from the v8x/customer renderer.</span>
    </div>

    <div class="row">
        <div class="cart-body col-md-12 dm-v9-support-page">
            <div class="header-lined">
                <h1 class="font-size-36">
                    {$LANG.registerdomain}
                </h1>
            </div>
            <p>{if $showAdvancedSearchOptions}{$LANG.orderForm.findNewDomainAi}{else}{$LANG.orderForm.findNewDomain}{/if}</p>

            <div class="domain-checker-container{if $showAdvancedSearchOptions} domain-checker-advanced{/if}">
                <div class="domain-checker-bg clearfix">
                    <form method="post" action="{$WEB_ROOT}/cart.php" id="frmDomainChecker">
                        <input type="hidden" name="a" value="checkDomain">
                        <div class="row">
                            <div class="col-md-8 col-md-offset-2 offset-md-2 col-xs-10 col-xs-offset-1 col-10 offset-1">
                                <div class="input-group input-group-lg input-group-box">
                                    {if $showAdvancedSearchOptions}
                                        <div class="dm-register-search-stack">
                                            <textarea name="message"
                                                  id="message"
                                                  title="{lang key='domainSearch.domainOrAiPrompt'}"
                                                  data-placement="left"
                                                  data-trigger="manual"
                                                  placeholder="{lang key='domainSearch.domainOrAiInstruction'}">{$message}</textarea>
                                            <div class="dm-register-search-controls">
                                                <div class="dm-register-search-controls-left">
                                                    <div class="dm-register-control dm-register-control-tlds">
                                                        <select name="tlds[]" class="multiselect multiselect-filter" multiple="multiple" data-placeholder="{lang key='domainSearch.tlds'}" data-min-selection="1">
                                                            {foreach $tlds as $tld}
                                                                <option{if in_array($tld, $selectedTlds)} selected {if count($selectedTlds) <= 1}disabled="disabled"{/if}{/if} value="{$tld}">{$tld}</option>
                                                            {/foreach}
                                                        </select>
                                                    </div>
                                                    <div class="dm-register-control dm-register-control-maxlength">
                                                        <select name="maxLength" class="dm-maxlength-select" aria-hidden="true" tabindex="-1">
                                                            {foreach $searchLengths as $len}
                                                                <option value="{$len}" {if $maxLength === $len}selected{/if}>{$len}</option>
                                                            {/foreach}
                                                        </select>
                                                        <div class="dm-maxlength-picker" data-label="{lang key='domainSearch.maxLength'}">
                                                            <button type="button" class="dm-maxlength-toggle" aria-haspopup="listbox" aria-expanded="false">
                                                                <span class="dm-maxlength-toggle-text">{lang key='domainSearch.maxLength'}</span>
                                                                <span class="dm-maxlength-caret" aria-hidden="true"></span>
                                                            </button>
                                                            <div class="dm-maxlength-menu" role="listbox" aria-label="{lang key='domainSearch.maxLength'}">
                                                                {foreach $searchLengths as $len}
                                                                    <button type="button" class="dm-maxlength-option{if $maxLength === $len} active{/if}" data-value="{$len}" role="option" aria-selected="{if $maxLength === $len}true{else}false{/if}">{$len}</button>
                                                                {/foreach}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="dm-register-control dm-register-control-safe">
                                                        <label class="dm-safe-search-toggle" for="inputSafeSearch">
                                                            <input id="inputSafeSearch" type="checkbox" class="no-icheck" name="filter" {if $safeSearchSelected}checked{/if}>
                                                            <span>{lang key="domainSearch.safeSearch"}</span>
                                                        </label>
                                                    </div>
                                                    <div class="dm-register-control dm-register-control-search">
                                                        <button type="submit"
                                                                id="btnCheckAvailability"
                                                                class="btn btn-primary dm-register-search-submit domain-check-availability{$captcha->getButtonClass($captchaForm)}">
                                                            {lang key='search'} <i class="fa-regular fa-sparkles"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    {else}
                                        <input type="text" name="domain" class="form-control" placeholder="{$LANG.findyourdomain}" value="{$lookupTerm}" id="inputDomain" data-toggle="tooltip" data-placement="left" data-trigger="manual" title="{lang key='orderForm.domainOrKeyword'}" />
                                        <span class="input-group-btn input-group-append">
                                            <button type="submit" id="btnCheckAvailability" class="btn btn-primary domain-check-availability{$captcha->getButtonClass($captchaForm)}">{$LANG.search}</button>
                                        </span>
                                    {/if}
                                </div>
                            </div>

                            {if $captcha->isEnabled() && $captcha->isEnabledForForm($captchaForm) && !$captcha->recaptcha->isInvisible()}
                                <div class="col-md-8 col-md-offset-2 offset-md-2 col-xs-10 col-xs-offset-1 col-10 offset-1">
                                    <div class="captcha-container" id="captchaContainer">
                                        {if $captcha->recaptcha->isEnabled()}
                                            <br>
                                            <div class="text-center">
                                                <div class="form-group recaptcha-container"></div>
                                            </div>
                                        {else}
                                            <div class="default-captcha default-captcha-register-margin">
                                                <p>{lang key="cartSimpleCaptcha"}</p>
                                                <div>
                                                    <img id="inputCaptchaImage" src="{$systemurl}includes/verifyimage.php" align="middle" />
                                                    <input id="inputCaptcha" type="text" name="code" maxlength="6" class="form-control input-sm" data-toggle="tooltip" data-placement="right" data-trigger="manual" title="{lang key='orderForm.required'}" />
                                                </div>
                                            </div>
                                        {/if}
                                    </div>
                                </div>
                            {/if}
                        </div>
                    </form>
                </div>
            </div>

            <div class="dm-v9-support-raw-panel">
                <div class="dm-v9-support-raw-header">Raw <code>/domain/check</code> endpoint output</div>
                <div id="dmV9SupportRawMeta" class="dm-v9-support-raw-meta">Run a search to capture the native endpoint response.</div>
                <pre id="dmV9SupportRawOutput" class="dm-v9-support-raw-output">Waiting for search...</pre>
            </div>

            <div id="DomainSearchResults" class="w-hidden">
                <div id="primarySuggestionHeading" class="primary-domain-header"><i class="fa-regular fa-sparkles"></i> {$LANG.domainSearch.topSuggestion}</div>
                <div id="primaryExactHeading" class="primary-domain-header">{$LANG.domainSearch.exactMatch}</div>
                <div id="searchDomainInfo" class="domain-checker-result-headline">
                    <p id="primaryLookupSearching" class="domain-lookup-loader domain-lookup-primary-loader domain-searching"><i class="fas fa-spinner fa-spin"></i> {lang key='orderForm.searching'}...</p>
                    <div id="primaryLookupResult" class="domain-lookup-result w-hidden">
                        <p class="domain-invalid domain-checker-invalid">{lang key='orderForm.domainLetterOrNumber'}<span class="domain-length-restrictions">{lang key='orderForm.domainLengthRequirements'}</span></p>
                        <p class="domain-unavailable domain-checker-unavailable">{lang key='orderForm.domainIsUnavailable'}</p>
                        <p class="domain-tld-unavailable domain-checker-unavailable">{lang key='orderForm.domainHasUnavailableTld'}</p>
                        <p class="domain-available domain-checker-available">{$LANG.domainavailablemessage}</p>
                        <a class="domain-contact-support btn btn-primary">{$LANG.domainContactUs}</a>
                        <div id="idnLanguageSelector" class="form-group idn-language-selector w-hidden">
                            <div class="row">
                                <div class="col-sm-10 col-sm-offset-1 col-lg-8 col-lg-offset-2 offset-sm-1 offset-lg-2">
                                    <div class="margin-10 text-center">
                                        {lang key='cart.idnLanguageDescription'}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-8 col-lg-6 col-sm-offset-2 col-lg-offset-3 offset-sm-2 offset-lg-3">
                                    <select name="idnlanguage" class="form-control">
                                        <option value="">{lang key='cart.idnLanguage'}</option>
                                        {foreach $idnLanguages as $idnLanguageKey => $idnLanguage}
                                            <option value="{$idnLanguageKey}">{lang key='idnLanguage.'|cat:$idnLanguageKey}</option>
                                        {/foreach}
                                    </select>
                                    <div class="field-error-msg">
                                        {lang key='cart.selectIdnLanguageForRegister'}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="domain-price">
                            <span class="price"></span>
                            <button class="btn btn-primary btn-add-to-cart" data-whois="0" data-domain="">
                                <span class="to-add">{$LANG.addtocart}</span>
                                <span class="loading">
                                    <i class="fas fa-spinner fa-spin"></i> {lang key='loading'}
                                </span>
                                <span class="added"><i class="far fa-shopping-cart"></i> {lang key='checkout'}</span>
                                <span class="unavailable">{$LANG.domaincheckertaken}</span>
                            </button>
                        </p>
                        <p class="domain-error domain-checker-unavailable"></p>
                    </div>
                </div>

                {if $spotlightTlds}
                    <div id="spotlightTlds" class="spotlight-tlds clearfix">
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
                                            <button type="button" class="btn btn-add-to-cart w-hidden" data-whois="0" data-domain="">
                                                <span class="to-add">{lang key='orderForm.add'}</span>
                                                <span class="loading">
                                                    <i class="fas fa-spinner fa-spin"></i> {lang key='loading'}
                                                </span>
                                                <span class="added"><i class="far fa-shopping-cart"></i> {lang key='checkout'}</span>
                                                <span class="unavailable">{$LANG.domaincheckertaken}</span>
                                            </button>
                                            <button type="button" class="btn btn-primary domain-contact-support w-hidden">
                                                {lang key='domainChecker.contactSupport'}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            {/foreach}
                        </div>
                    </div>
                {/if}

                <div class="suggested-domains{if !$showSuggestionsContainer} w-hidden{/if}">
                    <div class="panel-heading card-header">
                        {lang key='orderForm.suggestedDomains'}
                    </div>
                    <div id="suggestionsLoader" class="panel-body card-body domain-lookup-loader domain-lookup-suggestions-loader">
                        <i class="fas fa-spinner fa-spin"></i> {lang key='orderForm.generatingSuggestions'}
                    </div>
                    <div class="panel-body card-body domain-lookup-message domain-lookup-suggestions-message">
                        {lang key='domainSearch.errors.noSuggestions'}
                    </div>
                    <div id="domainSuggestions" class="domain-lookup-result list-group w-hidden">
                        <div class="domain-suggestion list-group-item w-hidden">
                            <span class="dm-suggestion-name"><span class="domain"></span><span class="promo w-hidden">
                                <span class="sales-group-hot w-hidden">{lang key='domainCheckerSalesGroup.hot'}</span>
                                <span class="sales-group-new w-hidden">{lang key='domainCheckerSalesGroup.new'}</span>
                                <span class="sales-group-sale w-hidden">{lang key='domainCheckerSalesGroup.sale'}</span>
                            </span></span><span class="extension"></span>
                            <div class="actions">
                                <span class="price"></span>
                                <button type="button" class="btn btn-add-to-cart" data-whois="1" data-domain="">
                                    <span class="to-add">{$LANG.addtocart}</span>
                                    <span class="loading">
                                        <i class="fas fa-spinner fa-spin"></i> {lang key='loading'}
                                    </span>
                                    <span class="added"><i class="far fa-shopping-cart"></i> {lang key='checkout'}</span>
                                    <span class="unavailable">{$LANG.domaincheckertaken}</span>
                                </button>
                                <button type="button" class="btn btn-primary domain-contact-support w-hidden">
                                    {lang key='domainChecker.contactSupport'}
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer card-footer more-suggestions text-center w-hidden">
                        <a id="moreSuggestions" href="#" onclick="loadMoreSuggestions();return false;">{lang key='domainsmoresuggestions'}</a>
                        <span id="noMoreSuggestions" class="no-more small w-hidden">{lang key='domaincheckernomoresuggestions'}</span>
                    </div>
                    <div class="text-center domain-suggestions-warning">
                        <p>{lang key='domainssuggestionswarnings'}</p>
                    </div>
                </div>

            </div>

            <div class="domain-pricing">

                {if $featuredTlds}
                    <div class="featured-tlds-container">
                        <div class="row">
                            {foreach $featuredTlds as $num => $tldinfo}
                                {if $num % 3 == 0 && (count($featuredTlds) - $num < 3)}
                                    {if count($featuredTlds) - $num == 2}
                                        <div class="col-sm-2"></div>
                                    {else}
                                        <div class="col-sm-4"></div>
                                    {/if}
                                {/if}
                                <div class="col-lg-4 col-sm-6">
                                    <div class="featured-tld">
                                        <div class="img-container">
                                            <img src="{$BASE_PATH_IMG}/tld_logos/{$tldinfo.tldNoDots}.png" alt="{$tldinfo.tld}">
                                        </div>
                                        <div class="price {$tldinfo.tldNoDots}">
                                            {if is_object($tldinfo.register)}
                                                {$tldinfo.register->toPrefixed()}{if $tldinfo.period > 1}{lang key="orderForm.shortPerYears" years={$tldinfo.period}}{else}{lang key="orderForm.shortPerYear" years=''}{/if}
                                            {else}
                                                {lang key="domainregnotavailable"}
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                            {/foreach}
                        </div>
                    </div>
                {/if}

                <h4 class="font-size-18">{lang key='pricing.browseExtByCategory'}</h4>

                <div class="tld-filters">
                    {foreach $categoriesWithCounts as $category => $count}
                        <a href="#" data-category="{$category}" class="badge badge-secondary">{lang key="domainTldCategory.$category" defaultValue=$category} ({$count})</a>
                    {/foreach}
                </div>

                <div class="bg-white">
                    <div class="row no-gutters tld-pricing-header text-center">
                        <div class="col-md-4 tld-column">{lang key='orderdomain'}</div>
                        <div class="col-md-8">
                            <div class="row no-gutters">
                                <div class="col-xs-4 col-4">{lang key='pricing.register'}</div>
                                <div class="col-xs-4 col-4">{lang key='pricing.transfer'}</div>
                                <div class="col-xs-4 col-4">{lang key='pricing.renewal'}</div>
                            </div>
                        </div>
                    </div>
                    {foreach $pricing['pricing'] as $tld => $price}
                        <div class="row no-gutters tld-row" data-category="{foreach $price.categories as $category}|{$category}|{/foreach}">
                            <div class="col-md-4 two-row-center px-4">
                                <strong>.{$tld}</strong>
                                {if $price.group}
                                    <span class="tld-sale-group tld-sale-group-{$price.group}">
                                        {lang key='domainCheckerSalesGroup.'|cat:$price.group}
                                    </span>
                                {/if}
                            </div>
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-xs-4 col-4 text-center">
                                        {if isset($price.register) && current($price.register) > 0}
                                            {current($price.register)}<br>
                                            <small>{key($price.register)} {if key($price.register) > 1}{lang key="orderForm.years"}{else}{lang key="orderForm.year"}{/if}</small>
                                        {elseif isset($price.register) && current($price.register) == 0}
                                            <small>{lang key='orderfree'}</small>
                                        {else}
                                            <small>{lang key='na'}</small>
                                        {/if}
                                    </div>
                                    <div class="col-xs-4 col-4 text-center">
                                        {if isset($price.transfer) && current($price.transfer) > 0}
                                            {current($price.transfer)}<br>
                                            <small>{key($price.transfer)} {if key($price.register) > 1}{lang key="orderForm.years"}{else}{lang key="orderForm.year"}{/if}</small>
                                        {elseif isset($price.transfer) && current($price.transfer) == 0}
                                            <small>{lang key='orderfree'}</small>
                                        {else}
                                            <small>{lang key='na'}</small>
                                        {/if}
                                    </div>
                                    <div class="col-xs-4 col-4 text-center">
                                        {if isset($price.renew) && current($price.renew) > 0}
                                            {current($price.renew)}<br>
                                            <small>{key($price.renew)} {if key($price.register) > 1}{lang key="orderForm.years"}{else}{lang key="orderForm.year"}{/if}</small>
                                        {elseif isset($price.renew) && current($price.renew) == 0}
                                            <small>{lang key='orderfree'}</small>
                                        {else}
                                            <small>{lang key='na'}</small>
                                        {/if}
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/foreach}
                    <div class="row tld-row no-tlds">
                        <div class="col-xs-12 col-12 text-center">
                            <br>
                            {lang key='pricing.selectExtCategory'}
                            <br><br>
                        </div>
                    </div>
                </div>

            </div>

            <div class="row">
                <div class="{if $domainTransferEnabled}col-md-6{else}col-md-8 col-md-offset-2 offset-md-2{/if}">
                    <div class="domain-promo-box dm-hosting-promo-box">

                        <div class="clearfix">
                            <i class="fas fa-server fa-4x"></i>
                            <h3 class="font-size-24 no-wrap">{lang key='orderForm.addHosting'}</h3>
                            <p class="font-bold text-warning">{lang key='orderForm.chooseFromRange'}</p>
                        </div>

                        <p>{lang key='orderForm.packagesForBudget'}</p>

                        <a href="{$WEB_ROOT}/cart.php" class="btn btn-warning">
                            {lang key='orderForm.exploreNow'}
                        </a>
                    </div>
                </div>
                {if $domainTransferEnabled}
                    <div class="col-md-6">
                        <div class="domain-promo-box dm-transfer-promo-box">

                            <div class="clearfix">
                                <i class="fas fa-globe fa-4x"></i>
                                <h3 class="font-size-22">{lang key='orderForm.transferToUs'}</h3>
                                <p class="font-bold text-primary">{lang key='orderForm.transferExtend'}*</p>
                            </div>

                            <p class="small dm-transfer-exclusions">{lang key='orderForm.extendExclusions'}</p>

                            <a href="{$WEB_ROOT}/cart.php?a=add&domain=transfer" class="btn btn-primary dm-transfer-domain-btn">
                                {lang key='orderForm.transferDomain'}
                            </a>
                        </div>
                    </div>
                {/if}
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function() {
    jQuery('.tld-filters a:first-child').click();
{if $lookupTerm && !$captchaError && !$invalid}
    jQuery('#btnCheckAvailability').click();
{/if}
{if $invalid}
    jQuery('#primaryLookupSearching').toggle();
    jQuery('#primaryLookupResult').children().toggle();
    jQuery('#primaryLookupResult').toggle();
    jQuery('#DomainSearchResults').toggle();
    jQuery('.domain-invalid').toggle();
{/if}
});

{if $showAdvancedSearchOptions}
    $(document).ready(function() {
        jQuery('#frmDomainChecker .multiselect').each(function () {
            const enableFiltering = $(this).hasClass('multiselect-filter');
            const minSelection = jQuery(this).data('min-selection');
            $(this).multiselect({
                onChange: function (element) {
                    const closestSelect = element.closest('select');
                    const selectedOptions = closestSelect.find('option:selected');
                    if (minSelection === undefined) {
                        return;
                    }
                    const atMinOptions = selectedOptions.length <= minSelection;
                    const targetOptions = atMinOptions ? selectedOptions : closestSelect.find('option');
                    targetOptions.each(function () {
                        const inputElement = jQuery('input[value="' + jQuery(this).val() + '"]');
                        inputElement.prop('disabled', atMinOptions ? 'disabled' : false);
                    });
                },
                buttonText: function(options, select) {
                    return select.data('placeholder');
                },
                maxHeight: 200,
                includeFilterClearBtn: false,
                enableCaseInsensitiveFiltering: enableFiltering,
            });
        })
    });
{/if}

</script>



{literal}
<style>
#order-standard_cart .dm-v9-support-banner {
    margin: 0 0 18px;
    padding: 12px 16px;
    background: #163a5f;
    color: #fff;
    border-radius: 6px;
    box-shadow: 0 2px 6px rgba(0,0,0,.12);
}
#order-standard_cart .dm-v9-support-banner strong {
    display: block;
    font-size: 15px;
}
#order-standard_cart .dm-v9-support-banner span {
    display: block;
    margin-top: 2px;
    font-size: 12px;
    opacity: .92;
}
#order-standard_cart .dm-v9-support-page {
    width: 100%;
    max-width: none;
    flex: 0 0 100%;
}
#order-standard_cart .dm-v9-support-raw-panel {
    margin: 18px 0 22px;
    border: 1px solid #cfd7e2;
    border-radius: 6px;
    background: #fff;
    overflow: hidden;
}
#order-standard_cart .dm-v9-support-raw-header {
    padding: 10px 14px;
    background: #163a5f;
    color: #fff;
    font-weight: 700;
}
#order-standard_cart .dm-v9-support-raw-meta {
    padding: 9px 14px;
    background: #f7f9fc;
    color: #163a5f;
    font-size: 12px;
    border-bottom: 1px solid #e3e8ef;
}
#order-standard_cart .dm-v9-support-raw-output {
    margin: 0;
    padding: 14px;
    min-height: 150px;
    max-height: 360px;
    overflow: auto;
    white-space: pre-wrap;
    word-break: break-word;
    background: #fbfcfe;
    color: #17212b;
    font-size: 12px;
}
</style>
<script>
jQuery(function($) {
    var $meta = $('#dmV9SupportRawMeta');
    var $raw = $('#dmV9SupportRawOutput');
    function isDomainCheck(settings) {
        var url = settings && settings.url ? String(settings.url) : '';
        return url.indexOf('/domain/check') !== -1 || url.indexOf('rp=/domain/check') !== -1;
    }
    function countResult(json) {
        if (!json || typeof json !== 'object') {
            return 'n/a';
        }
        if ($.isArray(json.result)) {
            return json.result.length;
        }
        if (json.result && typeof json.result === 'object') {
            return 1;
        }
        return 0;
    }
    $(document).ajaxComplete(function(event, xhr, settings) {
        if (!isDomainCheck(settings)) {
            return;
        }
        var text = xhr && xhr.responseText ? xhr.responseText : '';
        var requestData = settings && settings.data ? String(settings.data) : '';
        var requestType = 'unknown';
        var match = requestData.match(/(?:^|&)type=([^&]+)/);
        if (match) {
            requestType = decodeURIComponent(match[1].replace(/\+/g, ' '));
        }
        try {
            var json = JSON.parse(text);
            $meta.text('Captured /domain/check response | HTTP ' + xhr.status + ' | type=' + requestType + ' | result count=' + countResult(json));
            $raw.text(JSON.stringify(json, null, 2));
        } catch (e) {
            $meta.text('Captured /domain/check response | HTTP ' + xhr.status + ' | type=' + requestType + ' | non-JSON response');
            $raw.text(text || '(empty response)');
        }
    });
});
</script>
{/literal}
