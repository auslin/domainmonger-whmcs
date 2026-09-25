{* DomainMonger Patch 200 temporary namespinner-only diagnostic loader. *}
<link rel="stylesheet" type="text/css" href="{assetPath file='all.min.css'}?v={$versionHash}" />
{assetExists file="custom.css"}
<link rel="stylesheet" type="text/css" href="{$__assetPath__}?v={$versionHash}" />
{/assetExists}
<script type="text/javascript" src="{assetPath file='scripts.min.js'}?v={$versionHash}"></script>
<style>
    #order-standard_cart.dm-namespinner-test .dm-test-note {
        margin: 0 0 15px;
        padding: 10px 12px;
        border: 1px solid #ddd;
        background: #f8f8f8;
        color: #333;
        font-size: 14px;
    }
    #order-standard_cart.dm-namespinner-test .suggested-domains {
        margin-top: 18px;
    }
    #order-standard_cart.dm-namespinner-test #domainSuggestions .domain-suggestion {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        font-size: 18px;
    }
    #order-standard_cart.dm-namespinner-test #domainSuggestions .domain-suggestion.hidden {
        display: none !important;
    }
    #order-standard_cart.dm-namespinner-test #domainSuggestions .price {
        font-size: 14px;
        color: #555;
        white-space: nowrap;
    }
</style>
