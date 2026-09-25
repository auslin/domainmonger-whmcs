{include file="orderforms/standard_cart_2/common-v9-direct-renderer.tpl"}

{* DomainMonger Patch 1574: relevance-prefix filtering plus stable requested-domain availability results. *}
{literal}
<style>
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar {
    margin-top: 0;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .panel,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .card {
    margin-bottom: 18px;
    border: 1px solid #d9e1ea;
    border-radius: 4px;
    overflow: hidden;
    box-shadow: none;
    background: #fff;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .panel-heading,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .card-header {
    background: #2d4667;
    color: #fff;
    border: 0;
    padding: 11px 14px;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .panel-title,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .panel-title a,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .card-title,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .card-title a {
    color: #fff !important;
    font-weight: 700;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .panel-body,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .list-group,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .list-group-flush,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .nav,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .panel-collapse {
    background: #fff;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .list-group-item,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .list-group-item a,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .nav > li > a,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .panel-body a,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .card-body a {
    color: #4a5a6a;
    font-size: 14px;
    line-height: 1.35;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .list-group-item,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .nav > li > a,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .panel-body a,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .card-body a {
    border-top: 1px solid #e7edf3;
    padding: 11px 14px;
    background: #fff;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .list-group-item:first-child,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .nav > li:first-child > a {
    border-top: 0;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .list-group-item:hover,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .list-group-item:hover a,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .nav > li > a:hover,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .panel-body a:hover,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .card-body a:hover {
    background: #fff7ef;
    color: #f58220;
    text-decoration: none;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .list-group-item.active,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .list-group-item.active a,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .nav > li.active > a,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .nav > li.current > a,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .panel-body .active a,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .card-body .active a {
    background: #f58220 !important;
    border-color: #f58220 !important;
    color: #fff !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .panel-heading .badge,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .card-header .badge {
    background: rgba(255,255,255,.18);
    color: #fff;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .fa,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .fas,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar .glyphicon {
    color: inherit;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-main-with-left-sidebar > .sidebar-collapsed,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-main-with-left-sidebar > .panel.hidden-lg.hidden-md,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-main-with-left-sidebar > .panel.d-md-none {
    margin-bottom: 18px;
}
@media (max-width: 991px) {
    #order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-left-sidebar {
        display: none !important;
    }
}


/* DomainMonger Patch 381: Register page search input/button seam alignment.
   Visual-only: keep the v8x namespinner intact while making the search input/button render as one attached control. */
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-search-card {
    border-color: #cfd9e3;
    border-radius: 4px;
    box-shadow: none;
    margin-bottom: 14px;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-search-header {
    padding: 10px 16px;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-search-header h2 {
    font-size: 16px;
    line-height: 1.25;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-search-body {
    background: #fff;
    padding: 12px 11px 13px;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-search-box.input-group {
    margin: 0 0 10px;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-search-box .form-control {
    border-color: #c9d3dd;
    border-right: 0;
    border-top-right-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
    box-shadow: none;
    color: #163a5f;
    font-size: 13px;
    height: 36px !important;
    min-height: 36px;
    line-height: 20px;
    padding: 7px 12px;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-search-box .input-group-btn,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-search-box .input-group-append {
    font-size: 0;
    vertical-align: top;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-search-box .btn-primary {
    align-items: center;
    border-color: #f58220;
    border-radius: 0 3px 3px 0 !important;
    box-sizing: border-box;
    display: inline-flex;
    font-size: 12px;
    height: 36px !important;
    justify-content: center;
    line-height: 1;
    margin-left: -1px !important;
    min-height: 36px;
    min-width: 92px;
    padding: 0 12px;
    vertical-align: top;
}
/* DomainMonger Patch 1575: immediate search feedback without changing NameSpinner ranking. */
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-search-spinner {
    animation: dmV9DirectSearchSpin .7s linear infinite;
    animation-play-state: paused;
    border: 2px solid rgba(255, 255, 255, .45);
    border-radius: 50%;
    border-top-color: #fff;
    box-sizing: border-box;
    display: inline-block;
    height: 14px;
    margin-right: 7px;
    opacity: 0;
    transition: opacity .12s ease;
    width: 14px;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-search-box .btn-primary.dm-v9-direct-searching .dm-v9-direct-search-spinner {
    animation-play-state: running;
    opacity: 1;
}
@keyframes dmV9DirectSearchSpin {
    to { transform: rotate(360deg); }
}
@media (prefers-reduced-motion: reduce) {
    #order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-search-spinner {
        animation: none;
    }
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-options {
    align-items: center;
    color: #163a5f;
    display: flex;
    flex-wrap: nowrap;
    font-size: 12px;
    gap: 6px;
    line-height: 1.2;
    margin-top: 0;
    white-space: nowrap;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-options label {
    align-items: center;
    color: #163a5f;
    display: inline-flex;
    font-size: 12px;
    font-weight: 400;
    gap: 3px;
    margin: 0;
    white-space: nowrap;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-options input[type="checkbox"] {
    height: 14px;
    margin: 0 2px 0 0;
    width: 14px;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-options .dm-v9-direct-safe,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-options .dm-v9-direct-available-only,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-options .dm-v9-direct-sort,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-options .dm-v9-direct-max-length {
    margin-left: 2px;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page #dmV9DirectSort,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page #dmV9DirectMaxLength {
    border-color: #c9d3dd;
    color: #163a5f;
    font-size: 12px;
    height: 30px !important;
    line-height: 1.2;
    margin-left: 5px !important;
    padding: 3px 24px 3px 8px !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page #dmV9DirectSort {
    width: 128px !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page #dmV9DirectMaxLength {
    width: 84px !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page #dmV9DirectStatus {
    clear: both;
    font-size: 12px;
    line-height: 1.35;
    margin: 10px 0 0;
    padding: 9px 11px;
}
@media (max-width: 767px) {
    #order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-options {
        flex-wrap: wrap;
        gap: 8px 10px;
    }
}


/* DomainMonger Patch 382: Register page expanded TLD browser.
   Visual + selection only: adds the extra extension picker without changing the v8x namespinner search flow. */
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-browser {
    background: #fff;
    border: 1px solid #cfd9e3;
    border-radius: 4px;
    box-shadow: none;
    margin: 0 0 16px;
    overflow: hidden;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-browser-header {
    align-items: center;
    background: #163a5f;
    color: #fff;
    display: flex;
    justify-content: center;
    min-height: 38px;
    padding: 10px 14px;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-browser-header h3 {
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    line-height: 1.2;
    margin: 0;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-browser-body {
    background: #fff;
    padding: 12px 12px 0;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-browser-intro {
    align-items: center;
    color: #4d5f72;
    display: flex;
    flex-wrap: wrap;
    font-size: 12px;
    gap: 8px;
    justify-content: space-between;
    line-height: 1.35;
    margin-bottom: 10px;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-count {
    color: #163a5f;
    font-weight: 700;
    white-space: nowrap;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-actions {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: space-between;
    margin-bottom: 10px;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-action-buttons .btn {
    border-radius: 3px;
    font-size: 11px;
    font-weight: 700;
    line-height: 1.2;
    padding: 6px 12px;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-action-buttons .btn-primary {
    background: #f58220;
    border-color: #f58220;
    color: #fff;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-action-buttons .btn-default,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-action-buttons .btn-secondary {
    background: #163a5f;
    border-color: #163a5f;
    color: #fff;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-action-buttons .btn:hover,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-action-buttons .btn:focus {
    background: #214e7a;
    border-color: #214e7a;
    color: #fff;
    text-decoration: none;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-category-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 12px;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-category-buttons .btn {
    background: #163a5f;
    border: 1px solid #163a5f;
    border-radius: 3px;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    line-height: 1.15;
    padding: 7px 10px;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-category-buttons .btn:hover,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-category-buttons .btn:focus {
    background: #214e7a;
    border-color: #214e7a;
    color: #fff;
    text-decoration: none;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-category-buttons .active {
    background: #f58220;
    border-color: #f58220;
    color: #fff;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-table-wrap {
    border-top: 1px solid #d8e0e8;
    margin: 0 -12px;
    overflow-x: auto;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-table {
    color: #163a5f;
    margin: 0;
    min-width: 620px;
    width: 100%;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-table thead th {
    background: #163a5f;
    border: 0;
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    padding: 9px 12px;
    text-align: center;
    vertical-align: middle;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-table thead th:first-child {
    text-align: left;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-table tbody td {
    background: #fff;
    border-top: 1px solid #dfe6ed;
    font-size: 12px;
    padding: 9px 12px;
    text-align: center;
    vertical-align: middle;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-table tbody td:first-child {
    text-align: left;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-name {
    align-items: center;
    display: inline-flex;
    gap: 8px;
    min-width: 90px;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-table input[type="checkbox"] {
    height: 15px;
    margin: 0;
    width: 15px;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-price {
    display: block;
    font-weight: 600;
    line-height: 1.2;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-term {
    color: #6a7d90;
    display: block;
    font-size: 10px;
    line-height: 1.2;
    margin-top: 2px;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-sale-badge {
    background: #00a651;
    border-radius: 7px;
    color: #fff;
    display: inline-block;
    font-size: 9px;
    font-weight: 700;
    line-height: 1;
    margin-left: 6px;
    padding: 3px 6px;
    text-transform: uppercase;
}
@media (max-width: 767px) {
    #order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-actions,
    #order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-browser-intro {
        align-items: flex-start;
        flex-direction: column;
    }
}
/* DomainMonger Patch 383: Results replace the expanded TLD browser after search.
   Behavior-only: keep selected TLDs/namespinner intact, but do not stack results below the browser. */
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-browser.dm-v9-direct-tld-browser-replaced {
    display: none !important;
}

/* DomainMonger Patch 385: TLD browser visual polish.
   Visual-only: style the pre-search TLD browser area to match the confirmed WHMCS register/results styling. */
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-browser {
    border-color: #cfd9e3 !important;
    border-radius: 4px !important;
    box-shadow: 0 1px 2px rgba(22, 58, 95, .08) !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-browser-header {
    background: #163a5f !important;
    border: 0 !important;
    min-height: 36px !important;
    padding: 9px 14px !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-browser-header h3 {
    color: #fff !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    letter-spacing: 0 !important;
    line-height: 1.2 !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-browser-body {
    background: #fff !important;
    padding: 13px 12px 0 !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-browser-intro {
    color: #314e6f !important;
    font-size: 12px !important;
    line-height: 1.4 !important;
    margin-bottom: 9px !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-count {
    color: #163a5f !important;
    font-size: 12px !important;
    font-weight: 700 !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-actions {
    margin-bottom: 10px !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-action-buttons,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-category-buttons {
    gap: 7px !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-action-buttons .btn,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-category-buttons .btn {
    background-image: none !important;
    border-radius: 4px !important;
    box-shadow: none !important;
    color: #fff !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    line-height: 1.15 !important;
    min-height: 28px !important;
    text-decoration: none !important;
    text-shadow: none !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-action-buttons .btn {
    padding: 6px 12px !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-category-buttons {
    margin-bottom: 12px !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-category-buttons .btn {
    padding: 7px 11px !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-action-buttons .btn-primary,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-category-buttons .btn.active {
    background-color: #f58220 !important;
    border-color: #f58220 !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-action-buttons .btn-default,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-action-buttons .btn-secondary,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-category-buttons .btn:not(.active) {
    background-color: #163a5f !important;
    border-color: #163a5f !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-action-buttons .btn:hover,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-action-buttons .btn:focus,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-category-buttons .btn:hover,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-category-buttons .btn:focus {
    background-color: #214e7a !important;
    border-color: #214e7a !important;
    color: #fff !important;
    outline: none !important;
    text-decoration: none !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-action-buttons .btn-primary:hover,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-action-buttons .btn-primary:focus,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-category-buttons .btn.active:hover,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-category-buttons .btn.active:focus {
    background-color: #e67313 !important;
    border-color: #e67313 !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-table-wrap {
    border-top-color: #cfd9e3 !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-table {
    border-collapse: collapse !important;
    color: #163a5f !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-table thead th {
    background: #163a5f !important;
    border: 0 !important;
    color: #fff !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    padding: 9px 12px !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-table tbody td {
    border-top: 1px solid #dfe6ed !important;
    color: #163a5f !important;
    font-size: 12px !important;
    padding: 8px 12px !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-table tbody tr:hover td {
    background: #fff7ef !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-name strong {
    color: #163a5f !important;
    font-size: 12px !important;
    font-weight: 700 !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-price {
    color: #163a5f !important;
    font-size: 12px !important;
    font-weight: 700 !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-term {
    color: #6a7d90 !important;
    font-size: 10px !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-sale-badge {
    align-items: center !important;
    background: #00a651 !important;
    border-radius: 8px !important;
    color: #fff !important;
    display: inline-flex !important;
    font-size: 9px !important;
    font-weight: 700 !important;
    justify-content: center !important;
    line-height: 1 !important;
    min-height: 14px !important;
    min-width: 29px !important;
    padding: 3px 6px !important;
    text-align: center !important;
    vertical-align: middle !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-domain-name .dm-v9-direct-sale-badge,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-name .dm-v9-direct-sale-badge {
    margin-left: 7px !important;
}


/* DomainMonger Patch 386: TLD browser table typography match to search results.
   Visual-only: keep the extra TLD browser behavior intact, but make the row text read like the existing results table. */
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-table tbody td {
    color: #1f2933 !important;
    font-size: 13px !important;
    font-weight: 400 !important;
    line-height: 1.35 !important;
    padding: 10px 12px !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-table tbody td:first-child {
    color: #1f2933 !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-name {
    align-items: center !important;
    color: #1f2933 !important;
    display: inline-flex !important;
    gap: 8px !important;
    line-height: 1.35 !important;
    min-width: 100px !important;
    word-break: break-word !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-name strong {
    color: #1f2933 !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    line-height: 1.35 !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-price {
    color: #1f2933 !important;
    display: block !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    line-height: 1.2 !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-term {
    color: #7b8794 !important;
    display: block !important;
    font-size: 11px !important;
    font-weight: 400 !important;
    line-height: 1.2 !important;
    margin-top: 2px !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-table input[type="checkbox"] {
    vertical-align: middle !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-table tbody tr:hover td {
    background: #fff7ef !important;
}
/* Patch 394: soften register-page checkbox checked color to match DomainMonger style.
   Visual-only: does not change checkbox behavior or sync logic. */
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-options input[type="checkbox"],
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-table input[type="checkbox"] {
    accent-color: #d8741f;
    cursor: pointer;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-options label,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-tld-name {
    cursor: pointer;
}

/* DomainMonger Patch 472/473: keep register-page filter controls inside the search card.
   Visual-only: lets the row wrap while keeping Sort + Max Length together and readable. */
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-options {
    flex-wrap: wrap !important;
    gap: 7px 9px !important;
    max-width: 100% !important;
    overflow: visible !important;
    white-space: normal !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-options label {
    flex: 0 0 auto !important;
    white-space: nowrap !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-options .dm-v9-direct-filter-pair {
    align-items: center !important;
    display: inline-flex !important;
    flex: 0 0 auto !important;
    gap: 9px !important;
    margin-left: auto !important;
    max-width: 100% !important;
    white-space: nowrap !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-options .dm-v9-direct-filter-pair .dm-v9-direct-sort,
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-options .dm-v9-direct-filter-pair .dm-v9-direct-max-length {
    margin-left: 0 !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page #dmV9DirectSort {
    min-width: 152px !important;
    width: 152px !important;
}
#order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page #dmV9DirectMaxLength {
    min-width: 96px !important;
    width: 96px !important;
}
@media (max-width: 767px) {
    #order-standard_cart.dm-v9-direct-with-left-sidebar .dm-v9-direct-page .dm-v9-direct-options .dm-v9-direct-filter-pair {
        margin-left: 0 !important;
    }
}

</style>
{/literal}

<div id="order-standard_cart" class="dm-v9-direct-with-left-sidebar">
    <div class="row dm-v9-direct-layout-row dm-v9-direct-left-menu-layout">
        <div class="col-md-3 sidebar hidden-xs hidden-sm dm-v9-direct-sidebar dm-v9-direct-left-sidebar">
            {include file="orderforms/standard_cart_2/sidebar-categories.tpl"}
        </div>

        <div class="col-md-9 dm-v9-direct-page dm-v9-direct-main-with-left-sidebar">
            <div class="header-lined">
                <h1>Register a New Domain</h1>
            </div>

            {include file="orderforms/standard_cart_2/sidebar-categories-collapsed.tpl"}

            <div class="dm-v9-direct-search-card">
                <div class="dm-v9-direct-search-header">
                    <h2>Find your domain</h2>
                </div>
                <div class="dm-v9-direct-search-body">
                    <form method="post" action="#" id="dmV9DirectForm">
                        <input type="hidden" name="token" value="{$token}">
                        <div class="input-group input-group-lg input-group-box dm-v9-direct-search-box">
                            <input type="text"
                                   id="dmV9DirectInput"
                                   class="form-control"
                                   value="{$message|default:$lookupTerm|escape}"
                                   placeholder="Example: volleyball for women">
                            <span class="input-group-btn input-group-append">
                                <button type="submit" id="dmV9DirectButton" class="btn btn-primary" aria-busy="false">
                                    <span class="dm-v9-direct-search-spinner" aria-hidden="true"></span>
                                    <span class="dm-v9-direct-search-label">Search</span>
                                </button>
                            </span>
                        </div>
                        <div class="dm-v9-direct-options">
                            <label><input type="checkbox" class="dm-v9-direct-tld" value=".com" checked> .com</label>
                            <label><input type="checkbox" class="dm-v9-direct-tld" value=".net" checked> .net</label>
                            <label><input type="checkbox" class="dm-v9-direct-tld" value=".org" checked> .org</label>
                            <label><input type="checkbox" class="dm-v9-direct-tld" value=".us" checked> .us</label>
                            <label><input type="checkbox" class="dm-v9-direct-tld" value=".biz" checked> .biz</label>
                            <label><input type="checkbox" class="dm-v9-direct-tld" value=".ca" checked> .ca</label>
                            <label class="dm-v9-direct-safe"><input type="checkbox" id="dmV9DirectSafeSearch" checked> Safe Search</label>
                            <label class="dm-v9-direct-available-only"><input type="checkbox" id="dmV9DirectAvailableOnly"> Available Only</label>
                            <span class="dm-v9-direct-filter-pair">
                                <label class="dm-v9-direct-sort">Sort
                                    <select id="dmV9DirectSort" class="form-control input-sm" style="display:inline-block;width:auto;margin-left:6px;height:30px;padding:3px 24px 3px 8px;">
                                        <option value="recommended">Recommended</option>
                                        <option value="available">Available First</option>
                                        <option value="az">A-Z</option>
                                        <option value="shortest">Shortest</option>
                                    </select>
                                </label>
                                <label class="dm-v9-direct-max-length">Max Length
                                    <select id="dmV9DirectMaxLength" class="form-control input-sm" style="display:inline-block;width:auto;margin-left:6px;height:30px;padding:3px 24px 3px 8px;">
                                        <option value="0" selected>No Max</option>
                                        <option value="12">12</option>
                                        <option value="15">15</option>
                                        <option value="20">20</option>
                                        <option value="25">25</option>
                                        <option value="30">30</option>
                                        <option value="40">40</option>
                                    </select>
                                </label>
                            </span>
                        </div>
                    </form>
                    <div id="dmV9DirectStatus" class="alert dm-v9-direct-status" style="display:none;"></div>
                </div>
            </div>



            <div id="dmV9DirectTldBrowser" class="dm-v9-direct-tld-browser">
                <div class="dm-v9-direct-tld-browser-header">
                    <h3>Browse extensions by category</h3>
                </div>
                <div class="dm-v9-direct-tld-browser-body">
                    <div class="dm-v9-direct-tld-browser-intro">
                        <span>Choose extra extensions here, then run the search above. The current quick extensions stay selected by default.</span>
                        <span id="dmV9DirectTldSelectedCount" class="dm-v9-direct-tld-count">6 extensions selected for search</span>
                    </div>
                    <div class="dm-v9-direct-tld-actions">
                        <div class="dm-v9-direct-tld-action-buttons">
                            <button type="button" id="dmV9DirectSelectShown" class="btn btn-primary btn-sm">Select Shown</button>
                            <button type="button" id="dmV9DirectClearShown" class="btn btn-default btn-sm">Clear Shown</button>
                            <button type="button" id="dmV9DirectShowSelected" class="btn btn-default btn-sm">Show Selected</button>
                        </div>
                    </div>
                    <div id="dmV9DirectTldCategories" class="dm-v9-direct-tld-category-buttons"></div>
                    <div class="dm-v9-direct-tld-table-wrap">
                        <table class="table dm-v9-direct-tld-table">
                            <thead>
                                <tr>
                                    <th>Domain</th>
                                    <th>Register</th>
                                    <th>Transfer</th>
                                    <th>Renewal</th>
                                </tr>
                            </thead>
                            <tbody id="dmV9DirectTldTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            {* Patch 1668: use the live WHMCS Domain Pricing collection that powers configdomains.php. *}
            <div id="dmV9DirectWhmcsTldData" style="display:none;" aria-hidden="true">
                {foreach $pricing['pricing'] as $tld => $price}
                    {assign var=dmRegisterPrice value=''}
                    {assign var=dmRegisterYears value=''}
                    {assign var=dmTransferPrice value=''}
                    {assign var=dmTransferYears value=''}
                    {assign var=dmRenewalPrice value=''}
                    {assign var=dmRenewalYears value=''}
                    {if isset($price.register)}
                        {assign var=dmRegisterPrice value=current($price.register)}
                        {assign var=dmRegisterYears value=key($price.register)}
                    {/if}
                    {if isset($price.transfer)}
                        {assign var=dmTransferPrice value=current($price.transfer)}
                        {assign var=dmTransferYears value=key($price.transfer)}
                    {/if}
                    {if isset($price.renew)}
                        {assign var=dmRenewalPrice value=current($price.renew)}
                        {assign var=dmRenewalYears value=key($price.renew)}
                    {/if}
                    <div class="dm-v9-direct-whmcs-tld"
                         data-tld=".{$tld|escape:'html'}"
                         data-register="{$dmRegisterPrice|escape:'html'}"
                         data-register-years="{$dmRegisterYears|escape:'html'}"
                         data-transfer="{$dmTransferPrice|escape:'html'}"
                         data-transfer-years="{$dmTransferYears|escape:'html'}"
                         data-renewal="{$dmRenewalPrice|escape:'html'}"
                         data-renewal-years="{$dmRenewalYears|escape:'html'}"
                         data-group="{$price.group|default:''|escape:'html'}">
                        {foreach $price.categories as $category}
                            <span class="dm-v9-direct-whmcs-tld-category" data-category="{$category|escape:'html'}"></span>
                        {/foreach}
                    </div>
                {/foreach}
            </div>

            <div id="dmV9DirectResultsCard" class="dm-v9-direct-results-card" style="display:none;">
                <div class="dm-v9-direct-results-header">
                    <h3>Suggested Domains</h3>
                    <span id="dmV9DirectResultsCount" class="dm-v9-direct-results-count"></span>
                    <div id="dmV9DirectSummary" class="dm-v9-direct-results-summary" style="font-size:12px;font-weight:600;margin-top:4px;opacity:.9;"></div>
                </div>
                <div class="dm-v9-direct-results-body">
                    <div class="dm-v9-direct-table-wrap">
                        <table class="table table-striped dm-v9-direct-results-table">
                            <thead>
                                <tr>
                                    <th>Domain</th>
                                    <th>Status</th>
                                    <th>Price</th>
                                    <th class="dm-v9-direct-action-cell">Action</th>
                                </tr>
                            </thead>
                            <tbody id="dmV9DirectResults"></tbody>
                        </table>
                    </div>
                    <div id="dmV9DirectEmpty" class="dm-v9-direct-empty" style="display:none;">
                        No v9 suggestion names or context lookups were returned.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{literal}
<script>
(function($) {
    'use strict';

    var activeRun = 0;
    var maxConcurrentChecks = 5;
    /* Patch 1575: one shared availability queue keeps the existing concurrency limit. */
    var availabilityState = null;
    var catalogTldMapCache = null;

    function getEndpoint() {
        if (window.WHMCS && WHMCS.utils && typeof WHMCS.utils.getRouteUrl === 'function') {
            return WHMCS.utils.getRouteUrl('/domain/check');
        }
        return (window.whmcsBaseUrl || '') + '/index.php?rp=/domain/check';
    }

    function getToken() {
        if (typeof window.csrfToken !== 'undefined' && window.csrfToken) {
            return window.csrfToken;
        }
        return $('input[name="token"]').val() || '';
    }

    function setSearchButtonLoading(loading) {
        var $button = $('#dmV9DirectButton');
        $button.toggleClass('dm-v9-direct-searching', !!loading)
            .prop('disabled', !!loading)
            .attr('aria-busy', loading ? 'true' : 'false');
    }

    function cleanDomain(fullName) {
        return String(fullName || '')
            .toLowerCase()
            .replace(/^\s+|\s+$/g, '')
            .replace(/^https?:\/\//, '')
            .replace(/^www\./, '')
            .split('/')[0]
            .replace(/[^a-z0-9.-]/g, '');
    }

    function splitDomain(fullName) {
        fullName = cleanDomain(fullName);
        if (fullName.indexOf('.') === -1) {
            return null;
        }
        var firstDot = fullName.indexOf('.');
        var sld = fullName.substring(0, firstDot);
        var tld = fullName.substring(firstDot);
        if (!sld || !tld || sld.length < 2) {
            return null;
        }
        return { domain: sld + tld, sld: sld, tld: tld };
    }

    function normalizeTld(value) {
        value = String(value || '').toLowerCase().replace(/^\s+|\s+$/g, '');
        if (value && value.charAt(0) !== '.') {
            value = '.' + value;
        }
        return value;
    }

    function getCatalogTldMap() {
        var map = {};
        if (catalogTldMapCache) {
            return catalogTldMapCache;
        }
        if (typeof dmV9TldCatalog === 'undefined' || !$.isArray(dmV9TldCatalog)) {
            return map;
        }
        $.each(dmV9TldCatalog, function(index, item) {
            var tld = normalizeTld(item && item.tld);
            if (tld) {
                map[tld] = true;
            }
        });
        catalogTldMapCache = map;
        return catalogTldMapCache;
    }

    /* Patch 1668: a complete typed domain must be the first exact lookup. */
    function parseCompleteDomainInput(input) {
        var raw = String(input || '').toLowerCase().replace(/^\s+|\s+$/g, '');
        var parts;
        var tld;

        if (!raw || /\s/.test(raw)) {
            return null;
        }
        raw = raw.replace(/^https?:\/\//, '').replace(/^www\./, '').split('/')[0];
        parts = splitDomain(raw);
        if (!parts || !/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/.test(parts.sld)) {
            return null;
        }
        tld = normalizeTld(parts.tld);
        if (!getCatalogTldMap()[tld]) {
            return null;
        }
        parts.tld = tld;
        parts.domain = parts.sld + tld;
        parts.source = 'typed domain exact match';
        return parts;
    }

    function uniqueTldList(values) {
        var out = [];
        var seen = {};
        $.each(values || [], function(index, value) {
            var tld = normalizeTld(value);
            if (tld && !seen[tld]) {
                seen[tld] = true;
                out.push(tld);
            }
        });
        return out;
    }

    function parseInlineTlds(input) {
        var catalogMap = getCatalogTldMap();
        var found = [];
        var seen = {};
        var message = String(input || '').replace(/(^|\s)(\.[a-z0-9][a-z0-9.-]*)(?=\s|$)/gi, function(match, prefix, candidate) {
            var tld = normalizeTld(candidate);
            if (!catalogMap[tld]) {
                return match;
            }
            if (!seen[tld]) {
                seen[tld] = true;
                found.push(tld);
            }
            return prefix;
        });

        message = message.replace(/\s+/g, ' ').replace(/^\s+|\s+$/g, '');
        return { message: message, tlds: found };
    }

    function findBareWordTlds(input) {
        var catalogMap = getCatalogTldMap();
        var found = [];
        var seen = {};
        var tokens = String(input || '').split(/\s+/);

        $.each(tokens, function(index, token) {
            var word = String(token || '')
                .toLowerCase()
                .replace(/^[,;:!?()\[\]{}"']+|[,;:!?()\[\]{}"']+$/g, '');
            var tld;
            if (!word || !/^[a-z0-9]+$/.test(word)) {
                return;
            }
            tld = normalizeTld(word);
            if (catalogMap[tld] && !seen[tld]) {
                seen[tld] = true;
                found.push(tld);
            }
        });
        return found;
    }

    function ensureTldsSelected(tlds) {
        $.each(uniqueTldList(tlds), function(index, tld) {
            var $marker = $('.dm-v9-direct-auto-tld').filter(function() {
                return normalizeTld($(this).val()) === tld;
            }).first();

            if (!$marker.length) {
                $('<input/>')
                    .attr({
                        type: 'checkbox',
                        tabindex: '-1',
                        'aria-hidden': 'true'
                    })
                    .addClass('dm-v9-direct-tld dm-v9-direct-auto-tld')
                    .css('display', 'none')
                    .val(tld)
                    .prop('checked', true)
                    .appendTo('#dmV9DirectForm');
            }
            setTldChecked(tld, true);
        });
        updateTldSelectedCount();
    }

    function getSelectedTlds() {
        var tlds = [];
        var seen = {};
        $('.dm-v9-direct-tld:checked').each(function() {
            var tld = normalizeTld($(this).val());
            if (tld && !seen[tld]) {
                seen[tld] = true;
                tlds.push(tld);
            }
        });
        return tlds.length ? tlds : ['.com'];
    }

    function selectedTldMap(tlds) {
        var map = {};
        $.each(tlds || [], function(index, tld) {
            tld = normalizeTld(tld);
            if (tld) {
                map[tld] = true;
            }
        });
        return map;
    }

    function tldIsSelected(tld, tlds) {
        var map = selectedTldMap(tlds);
        tld = normalizeTld(tld);
        return !!map[tld];
    }

    function extractSuggestionDomains(data) {
        var out = [];
        var seen = {};
        if (!data || !data.result || !$.isArray(data.result)) {
            return out;
        }
        $.each(data.result, function(index, item) {
            var name = '';
            var parts;
            if (!item) {
                return;
            }
            if (typeof item === 'string') {
                name = item;
            } else {
                name = item.domainName || item.idnDomainName || item.domain || '';
                if (!name && item.sld && item.tld) {
                    name = item.sld + '.' + String(item.tld).replace(/^\./, '');
                }
            }
            parts = splitDomain(name);
            if (!parts || seen[parts.domain]) {
                return;
            }
            seen[parts.domain] = true;
            parts.source = 'v9 namespinner';
            out.push(parts);
        });
        return out;
    }

    function mergeRankedSuggestions(exactDomains, v9Domains, contextDomains, input, maxTotal) {
        var merged = [];
        var candidates = [];
        var seen = {};
        var sequence = 0;
        var words = getRelevanceWords(input);
        maxTotal = maxTotal || 40;

        function addExact(item) {
            if (!item || !item.domain || seen[item.domain] || merged.length >= maxTotal) {
                return;
            }
            seen[item.domain] = true;
            merged.push(item);
        }

        function addCandidate(item, sourceRank) {
            var relevance;
            var candidateSld;
            var startsWithSearchWord = false;
            if (!item || !item.domain || seen[item.domain]) {
                return;
            }
            seen[item.domain] = true;
            relevance = scoreSuggestionRelevance(item, input);
            if (words.length >= 2 && relevance.matchedWords === 0) {
                return;
            }

            /*
             * Native NameSpinner results sometimes prepend an unrelated place/name
             * while preserving only part of a longer search. Keep useful shortened
             * combinations that begin with a search word, but omit weak native
             * prefixes generically rather than blacklisting any specific word.
             */
            if (sourceRank === 0 && words.length >= 3 && relevance.matchedWords <= Math.floor(words.length / 2)) {
                candidateSld = cleanSld(item.sld || '').replace(/-/g, '');
                $.each(words, function(wordIndex, word) {
                    if (candidateSld.indexOf(word) === 0) {
                        startsWithSearchWord = true;
                        return false;
                    }
                });
                if (!startsWithSearchWord) {
                    return;
                }
            }

            candidates.push({
                item: item,
                score: relevance.score,
                matchedWords: relevance.matchedWords,
                sourceRank: sourceRank,
                sequence: sequence++
            });
        }

        $.each(exactDomains || [], function(index, item) {
            addExact(item);
        });

        $.each(v9Domains || [], function(index, item) {
            if (item) {
                item.source = item.source || 'v9 namespinner';
            }
            addCandidate(item, 0);
        });

        $.each(contextDomains || [], function(index, item) {
            if (item) {
                item.source = item.source || 'context lookup';
            }
            addCandidate(item, 1);
        });

        candidates.sort(function(a, b) {
            if (a.score !== b.score) {
                return b.score - a.score;
            }
            if (a.matchedWords !== b.matchedWords) {
                return b.matchedWords - a.matchedWords;
            }
            if (a.sourceRank !== b.sourceRank) {
                return a.sourceRank - b.sourceRank;
            }
            return a.sequence - b.sequence;
        });

        $.each(candidates, function(index, candidate) {
            if (merged.length < maxTotal) {
                merged.push(candidate.item);
            }
        });

        return merged;
    }


    function cleanSld(value) {
        return String(value || '')
            .toLowerCase()
            .replace(/[^a-z0-9-]/g, '')
            .replace(/^-+|-+$/g, '')
            .replace(/-{2,}/g, '-');
    }

    function splitCamelAndBoundaries(value) {
        return String(value || '')
            .replace(/([a-z])([A-Z])/g, '$1 $2')
            .replace(/([a-zA-Z])([0-9])/g, '$1 $2')
            .replace(/([0-9])([a-zA-Z])/g, '$1 $2');
    }

    function wordsFromInput(input) {
        var cleaned = splitCamelAndBoundaries(input)
            .toLowerCase()
            .replace(/^https?:\/\//, '')
            .replace(/^www\./, '')
            .replace(/\.[a-z0-9.-]+$/i, '')
            .replace(/[^a-z0-9]+/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
        var allWords = cleaned ? cleaned.split(' ') : [];
        var stopWords = {
            'a': true,
            'an': true,
            'and': true,
            'of': true,
            'the': true,
            'to': true,
            'with': true
        };
        var meaningful = [];
        var seenWords = {};
        $.each(allWords, function(index, word) {
            if (word && !stopWords[word] && !seenWords[word]) {
                seenWords[word] = true;
                meaningful.push(word);
            }
        });
        return {
            all: allWords,
            meaningful: meaningful.length ? meaningful : allWords
        };
    }

    function getRelevanceWords(input) {
        var parts = wordsFromInput(input);
        var out = [];
        var seen = {};
        $.each(parts.meaningful || parts.all || [], function(index, word) {
            word = String(word || '').toLowerCase();
            if (word && !seen[word]) {
                seen[word] = true;
                out.push(word);
            }
        });
        return out;
    }

    function scoreSuggestionRelevance(item, input) {
        var parts = wordsFromInput(input);
        var words = getRelevanceWords(input);
        var phraseWords = parts.all && parts.all.length ? parts.all : words;
        var phrase = phraseWords.join('');
        var sld = cleanSld(item && item.sld ? item.sld : '').replace(/-/g, '');
        var matched = 0;
        var ordered = true;
        var cursor = 0;
        var score = 0;
        var phrasePosition;
        var extraLength;

        $.each(words, function(index, word) {
            var position = sld.indexOf(word);
            var orderedPosition = sld.indexOf(word, cursor);
            if (position !== -1) {
                matched++;
            }
            if (orderedPosition === -1) {
                ordered = false;
            } else {
                cursor = orderedPosition + word.length;
            }
        });

        if (phrase && sld === phrase) {
            score += 10000;
        } else if (phrase) {
            phrasePosition = sld.indexOf(phrase);
            if (phrasePosition !== -1) {
                score += 4200;
                if (phrasePosition === 0) {
                    score += 300;
                }
            }
        }

        if (words.length) {
            score += matched * 260;
            score += Math.round((matched / words.length) * 900);
            if (matched === words.length) {
                score += 1600;
            } else if (words.length >= 3 && matched === 1) {
                score -= 650;
            }
            if (ordered && matched === words.length) {
                score += 350;
            }
        }

        if (phrase && sld) {
            extraLength = Math.max(0, sld.length - phrase.length);
            score -= Math.min(extraLength, 30) * 8;
        }

        return {
            score: score,
            matchedWords: matched
        };
    }

    function generateExactPhraseDomains(input, explicitTlds, bareWordTlds, selectedTlds) {
        var inputWords = wordsFromInput(input);
        var phraseWords = inputWords.all.length ? inputWords.all : inputWords.meaningful;
        var base = cleanSld(phraseWords.join(''));
        var orderedTlds = uniqueTldList((explicitTlds || []).concat(bareWordTlds || [], selectedTlds || []));
        var explicitMap = selectedTldMap(explicitTlds);
        var bareMap = selectedTldMap(bareWordTlds);
        var list = [];
        var seen = {};

        if (!base) {
            return list;
        }

        $.each(orderedTlds, function(index, tld) {
            var source = 'exact phrase selected TLD';
            if (explicitMap[tld]) {
                source = 'typed extension exact match';
            } else if (bareMap[tld]) {
                source = 'matching TLD word exact phrase';
            }
            addContextDomain(list, seen, base, tld, source, orderedTlds);
        });
        return list;
    }

    function addContextDomain(list, seen, sld, tld, source, selectedTlds) {
        var parts;
        sld = cleanSld(sld);
        tld = String(tld || '.com').toLowerCase();
        if (!sld || sld.length < 3 || sld.length > 63 || !/^[a-z0-9][a-z0-9-]*[a-z0-9]$/.test(sld)) {
            return;
        }
        if (!/^\.[a-z0-9.-]+$/.test(tld)) {
            return;
        }
        if (selectedTlds && selectedTlds.length && !tldIsSelected(tld, selectedTlds)) {
            return;
        }
        parts = splitDomain(sld + tld);
        if (!parts || seen[parts.domain]) {
            return;
        }
        seen[parts.domain] = true;
        parts.source = source || 'context lookup';
        list.push(parts);
    }

    function replaceWord(words, from, to) {
        var out = [];
        var changed = false;
        $.each(words, function(index, word) {
            if (word === from) {
                out.push(to);
                changed = true;
            } else {
                out.push(word);
            }
        });
        return changed ? out : null;
    }

    function singularizeLast(words) {
        var out;
        if (!words.length) {
            return null;
        }
        out = words.slice(0);
        if (out[out.length - 1] === 'women') {
            out[out.length - 1] = 'woman';
            return out;
        }
        if (out[out.length - 1] === 'men') {
            out[out.length - 1] = 'man';
            return out;
        }
        if (out[out.length - 1].length > 4 && /s$/.test(out[out.length - 1])) {
            out[out.length - 1] = out[out.length - 1].replace(/s$/, '');
            return out;
        }
        return null;
    }

    function generateContextLookupDomains(input, existingDomains, selectedTlds) {
        var parts = wordsFromInput(input);
        var phraseWords = parts.all.length ? parts.all : parts.meaningful;
        var meaningful = parts.meaningful;
        var base = phraseWords.join('');
        var hyphenBase = phraseWords.join('-');
        var singularWords = singularizeLast(phraseWords);
        var numberWords = replaceWord(phraseWords, 'for', '4');
        var list = [];
        var seen = {};
        var relatedFirst = [];
        var sportRelated = {
            volleyball: ['basketball', 'soccer'],
            basketball: ['volleyball', 'soccer'],
            soccer: ['volleyball', 'basketball'],
            football: ['basketball', 'soccer'],
            baseball: ['softball', 'basketball'],
            softball: ['baseball', 'volleyball']
        };
        var i;

        selectedTlds = selectedTlds && selectedTlds.length ? selectedTlds : ['.com'];

        $.each(existingDomains || [], function(index, item) {
            if (item && item.domain) {
                seen[item.domain] = true;
            }
        });

        if (!phraseWords.length) {
            return list;
        }

        if (sportRelated[phraseWords[0]]) {
            relatedFirst = sportRelated[phraseWords[0]];
            addContextDomain(list, seen, [relatedFirst[0]].concat(phraseWords.slice(1)).join(''), '.com', 'context related word', selectedTlds);
        }

        if (singularWords) {
            addContextDomain(list, seen, singularWords.join(''), '.com', 'context singular/plural', selectedTlds);
        }

        if (numberWords) {
            addContextDomain(list, seen, numberWords.join(''), '.com', 'context number substitution', selectedTlds);
        }

        addContextDomain(list, seen, 'little' + base, '.com', 'context prefix', selectedTlds);
        if (relatedFirst.length > 1) {
            addContextDomain(list, seen, [relatedFirst[1]].concat(phraseWords.slice(1)).join(''), '.net', 'context related word', selectedTlds);
        }
        addContextDomain(list, seen, base + 'today', '.com', 'context suffix', selectedTlds);
        addContextDomain(list, seen, base, '.ca', 'context alternate TLD', selectedTlds);
        addContextDomain(list, seen, base, '.us', 'context alternate TLD', selectedTlds);
        if (singularWords) {
            addContextDomain(list, seen, singularWords.join(''), '.net', 'context singular/plural alternate TLD', selectedTlds);
        }
        addContextDomain(list, seen, base + 'today', '.net', 'context suffix alternate TLD', selectedTlds);
        if (phraseWords.length > 1) {
            addContextDomain(list, seen, hyphenBase, '.ca', 'context hyphen alternate TLD', selectedTlds);
            addContextDomain(list, seen, hyphenBase, '.biz', 'context hyphen alternate TLD', selectedTlds);
        }
        if (relatedFirst.length > 1) {
            addContextDomain(list, seen, [relatedFirst[1]].concat(phraseWords.slice(1)).join(''), '.biz', 'context related word', selectedTlds);
        }
        addContextDomain(list, seen, base, '.info', 'context alternate TLD', selectedTlds);
        addContextDomain(list, seen, base, '.co', 'context alternate TLD', selectedTlds);
        if (numberWords) {
            addContextDomain(list, seen, numberWords.join(''), '.net', 'context number substitution alternate TLD', selectedTlds);
            addContextDomain(list, seen, numberWords.join(''), '.org', 'context number substitution alternate TLD', selectedTlds);
        }
        addContextDomain(list, seen, 'little' + base, '.net', 'context prefix alternate TLD', selectedTlds);
        addContextDomain(list, seen, base + 'today', '.org', 'context suffix alternate TLD', selectedTlds);
        addContextDomain(list, seen, base + 'online', '.com', 'context suffix', selectedTlds);
        addContextDomain(list, seen, 'my' + base, '.com', 'context prefix', selectedTlds);
        addContextDomain(list, seen, 'get' + base, '.com', 'context prefix', selectedTlds);

        if (relatedFirst.length) {
            $.each(relatedFirst, function(index, relatedWord) {
                addContextDomain(list, seen, [relatedWord].concat(phraseWords.slice(1)).join(''), '.org', 'context related word alternate TLD', selectedTlds);
                addContextDomain(list, seen, [relatedWord].concat(phraseWords.slice(1)).join(''), '.ca', 'context related word alternate TLD', selectedTlds);
                addContextDomain(list, seen, [relatedWord].concat(phraseWords.slice(1)).join(''), '.us', 'context related word alternate TLD', selectedTlds);
            });
        }

        if (meaningful.length >= 2) {
            addContextDomain(list, seen, meaningful.join(''), '.com', 'context meaningful words', selectedTlds);
            addContextDomain(list, seen, meaningful.join('-'), '.com', 'context meaningful words', selectedTlds);
            for (i = 0; i < meaningful.length - 1; i++) {
                addContextDomain(list, seen, meaningful[i] + meaningful[i + 1], '.com', 'context adjacent word pair', selectedTlds);
            }
            addContextDomain(list, seen, meaningful[meaningful.length - 1] + meaningful[0], '.com', 'context last + first', selectedTlds);
        }

        return list.slice(0, 35);
    }
    function parseAvailabilityResponse(request, data) {
        var result = {
            requestedDomain: request.domain,
            returnedDomain: request.domain,
            status: 'Unknown',
            price: '',
            raw: data
        };
        var item = null;
        var pricing;
        var firstTerm;

        if (data && data.result && $.isArray(data.result) && data.result.length) {
            item = data.result[0];
        } else if (data && data.result && typeof data.result === 'object') {
            item = data.result;
        }

        if (!item) {
            result.status = 'No response item';
            return result;
        }

        if (typeof item === 'string') {
            result.status = item;
            return result;
        }

        /*
         * Availability checks are for one requested domain. Some native responses
         * can include a different domain name; never let that relabel the row or
         * create a visible duplicate of another requested exact match.
         */
        result.returnedDomain = request.domain;

        if (item.error) {
            result.status = item.error;
            return result;
        }

        if (item.isValidDomain === false) {
            result.status = item.domainErrorMessage || 'Invalid';
            return result;
        }

        pricing = item.pricing;
        if (item.isAvailable && typeof pricing !== 'string') {
            result.status = 'Available';
            if (pricing) {
                firstTerm = Object.keys(pricing)[0];
                if (firstTerm && pricing[firstTerm] && pricing[firstTerm].register) {
                    result.price = pricing[firstTerm].register;
                }
            }
            return result;
        }

        if (typeof pricing === 'string' && pricing) {
            result.status = pricing === 'ContactUs' ? 'Contact Support' : pricing;
            return result;
        }

        result.status = 'Unavailable';
        return result;
    }

    function statusClass(status) {
        if (status === 'Available') {
            return 'text-success';
        }
        if (status === 'Unavailable') {
            return 'text-danger';
        }
        if (status === 'Checking...') {
            return 'text-muted';
        }
        return 'text-warning';
    }

    function getSaleTldForDomain(domain) {
        var clean = cleanDomain(domain);
        var match = '';
        if (typeof dmV9TldCatalog === 'undefined' || !$.isArray(dmV9TldCatalog)) {
            return '';
        }
        $.each(dmV9TldCatalog, function(index, item) {
            var tld = normalizeTld(item && item.tld);
            if (!item || !item.sale || !tld || clean.length <= tld.length) {
                return;
            }
            if (clean.substr(clean.length - tld.length) === tld && tld.length > match.length) {
                match = tld;
            }
        });
        return match;
    }

    function buildDomainNameCell(domain) {
        var $domainName = $('<span/>').addClass('dm-v9-direct-domain-name').text(domain);
        if (getSaleTldForDomain(domain)) {
            $domainName.append($('<span/>').addClass('dm-v9-direct-sale-badge').text('Sale'));
        }
        return $domainName;
    }

    function addPlaceholderRow(request, index) {
        $('<tr/>', {
                id: 'dmV9DirectRow' + index,
                'data-dm-index': index,
                'data-dm-status': 'Checking...',
                'data-dm-domain': request.domain || '',
                'data-dm-length': String(request.domain || '').length
            })
            .append($('<td/>').append(buildDomainNameCell(request.domain)))
            .append($('<td/>').append($('<strong/>').addClass('text-muted').text('Checking...')))
            .append($('<td/>').addClass('dm-v9-direct-price').text(''))
            .append($('<td/>').addClass('dm-v9-direct-action-cell'))
            .appendTo('#dmV9DirectResults');
    }

    function buildActionCell(result, domain) {
        var $cell = $('<td/>').addClass('dm-v9-direct-action-cell');
        if (result.status === 'Available') {
            $('<button/>', {
                type: 'button',
                class: 'btn btn-primary btn-sm dm-v9-direct-add-btn',
                'data-domain': domain
            }).append($('<span/>').addClass('dm-add-label').text('Add'))
              .append($('<span/>').addClass('dm-add-loading').hide().text('Adding...'))
              .append($('<span/>').addClass('dm-add-added').hide().text('Checkout'))
              .appendTo($cell);
        }
        return $cell;
    }

    function getOriginalIndex($row) {
        var index = parseInt($row.attr('data-dm-index'), 10);
        return isNaN(index) ? 999999 : index;
    }

    function compareOriginalOrder($a, $b) {
        return getOriginalIndex($a) - getOriginalIndex($b);
    }

    function statusSortRank(status) {
        if (status === 'Available') {
            return 0;
        }
        if (!status || status === 'Checking...') {
            return 1;
        }
        if (status === 'Unavailable') {
            return 3;
        }
        return 2;
    }

    function sortResultRows() {
        var mode = $('#dmV9DirectSort').val() || 'recommended';
        var $body = $('#dmV9DirectResults');
        var rows;
        if (mode === 'recommended') {
            return;
        }
        rows = $body.children('tr').get();
        rows.sort(function(a, b) {
            var $a = $(a);
            var $b = $(b);
            var domainA = String($a.attr('data-dm-domain') || '').toLowerCase();
            var domainB = String($b.attr('data-dm-domain') || '').toLowerCase();
            var rankA;
            var rankB;
            var lenA;
            var lenB;

            if (mode === 'available') {
                rankA = statusSortRank($a.attr('data-dm-status') || '');
                rankB = statusSortRank($b.attr('data-dm-status') || '');
                if (rankA !== rankB) {
                    return rankA - rankB;
                }
                return compareOriginalOrder($a, $b);
            }

            if (mode === 'az') {
                if (domainA < domainB) {
                    return -1;
                }
                if (domainA > domainB) {
                    return 1;
                }
                return compareOriginalOrder($a, $b);
            }

            if (mode === 'shortest') {
                lenA = parseInt($a.attr('data-dm-length'), 10);
                lenB = parseInt($b.attr('data-dm-length'), 10);
                lenA = isNaN(lenA) ? 999999 : lenA;
                lenB = isNaN(lenB) ? 999999 : lenB;
                if (lenA !== lenB) {
                    return lenA - lenB;
                }
                if (domainA < domainB) {
                    return -1;
                }
                if (domainA > domainB) {
                    return 1;
                }
                return compareOriginalOrder($a, $b);
            }

            return compareOriginalOrder($a, $b);
        });
        $.each(rows, function(index, row) {
            $body.append(row);
        });
    }

    function updateResultSummary(total, visible, available, unavailable, checking, other) {
        var parts = [];
        if (!total) {
            $('#dmV9DirectSummary').text('');
            return;
        }
        parts.push(available + ' available');
        parts.push(unavailable + ' unavailable');
        if (checking) {
            parts.push(checking + ' checking');
        }
        if (other) {
            parts.push(other + ' other');
        }
        parts.push(visible + ' visible');
        $('#dmV9DirectSummary').text(parts.join(' • '));
    }

    function getSelectedMaxLength() {
        var maxLength = parseInt($('#dmV9DirectMaxLength').val(), 10);
        return isNaN(maxLength) ? 0 : maxLength;
    }

    function applyAvailableOnlyFilter() {
        sortResultRows();
        var availableOnly = $('#dmV9DirectAvailableOnly').is(':checked');
        var maxLength = getSelectedMaxLength();
        var total = 0;
        var available = 0;
        var unavailable = 0;
        var checking = 0;
        var other = 0;
        var visible = 0;
        var lengthHidden = 0;
        var selectedTlds = getSelectedTlds();

        $('#dmV9DirectResults tr').each(function() {
            var $row = $(this);
            var status = $row.attr('data-dm-status') || '';
            var rowLength = parseInt($row.attr('data-dm-length'), 10);
            var rowDomain = String($row.attr('data-dm-domain') || '').toLowerCase();
            var rowTld = rowDomain.indexOf('.') >= 0 ? rowDomain.substring(rowDomain.indexOf('.')) : '';
            var hideForAvailability = false;
            var hideForLength = false;
            var hideForTld = false;
            rowLength = isNaN(rowLength) ? 0 : rowLength;
            total++;
            if (status === 'Available') {
                available++;
            } else if (status === 'Unavailable') {
                unavailable++;
            } else if (!status || status === 'Checking...') {
                checking++;
            } else {
                other++;
            }
            hideForAvailability = availableOnly && status && status !== 'Available';
            hideForLength = maxLength > 0 && rowLength > maxLength;
            hideForTld = rowTld && !tldIsSelected(rowTld, selectedTlds);
            if (hideForLength) {
                lengthHidden++;
            }
            if (hideForAvailability || hideForLength || hideForTld) {
                $row.hide();
            } else {
                $row.show();
                visible++;
            }
        });

        if (!total) {
            updateResultSummary(0, 0, 0, 0, 0, 0);
            return;
        }
        if (availableOnly || maxLength > 0) {
            $('#dmV9DirectResultsCount').text(visible + ' visible / ' + total + ' checked');
        } else if (available || total) {
            $('#dmV9DirectResultsCount').text(total + ' suggestion(s)');
        }
        updateResultSummary(total, visible, available, unavailable, checking, other);
        if (maxLength > 0 && lengthHidden) {
            $('#dmV9DirectSummary').append(' • ' + lengthHidden + ' over max length hidden');
        }
    }

    function updateRow(index, result) {
        var domain = result.returnedDomain || result.requestedDomain;
        var $row = $('#dmV9DirectRow' + index);
        var $domainName = buildDomainNameCell(domain);
        if (result.status === 'Unavailable') {
            $domainName.addClass('dm-v9-direct-domain-unavailable');
        }
        $row.attr('data-dm-status', result.status || '')
            .attr('data-dm-domain', domain || '')
            .attr('data-dm-length', String(domain || '').length)
            .empty()
            .append($('<td/>').append($domainName))
            .append($('<td/>').append($('<strong/>').addClass(statusClass(result.status)).text(result.status)))
            .append($('<td/>').addClass('dm-v9-direct-price').text(result.price || ''))
            .append(buildActionCell(result, domain));
        applyAvailableOnlyFilter();
    }

    function checkDomain(request) {
        return $.ajax({
            url: getEndpoint(),
            method: 'POST',
            dataType: 'json',
            data: {
                token: getToken(),
                type: 'domain',
                source: 'cartAddDomain',
                domain: request.domain,
                sld: request.sld,
                tld: request.tld
            }
        }).then(function(data) {
            return parseAvailabilityResponse(request, data);
        }, function(xhr) {
            return {
                requestedDomain: request.domain,
                returnedDomain: request.domain,
                status: 'Request failed: HTTP ' + xhr.status,
                price: '',
                raw: xhr.responseText || ''
            };
        });
    }

    function beginAvailabilityRun(runId) {
        availabilityState = {
            runId: runId,
            queue: [],
            running: 0,
            completed: 0,
            total: 0,
            nativePending: true,
            nativeFailed: false
        };
    }

    function updateAvailabilityStatus(runId) {
        var state = availabilityState;
        var $status = $('#dmV9DirectStatus');
        if (!state || state.runId !== runId || runId !== activeRun) {
            return;
        }

        if (state.nativeFailed) {
            $status.removeClass('alert-info alert-success').addClass('alert-warning');
            if (state.completed < state.total) {
                $status.text('Native suggestions failed; checking exact matches (' + state.completed + ' of ' + state.total + ')...').show();
            } else {
                $status.text('Exact matches checked. The native namespinner request failed.').show();
            }
            return;
        }

        if (state.nativePending) {
            $status.removeClass('alert-warning alert-success').addClass('alert-info');
            if (state.completed && state.total) {
                $status.text('Generating suggestions • checked ' + state.completed + ' of ' + state.total + ' exact match(es)...').show();
            } else {
                $status.text('Generating suggestions and checking exact matches...').show();
            }
            return;
        }

        if (state.completed < state.total) {
            $status.removeClass('alert-warning alert-success').addClass('alert-info')
                .text('Checked ' + state.completed + ' of ' + state.total + ' suggestion(s)...')
                .show();
        } else {
            $status.removeClass('alert-info alert-warning').addClass('alert-success')
                .text('Finished checking suggestions.')
                .show();
        }
    }

    function pumpAvailabilityQueue(runId) {
        var state = availabilityState;
        var task;
        if (!state || state.runId !== runId || runId !== activeRun) {
            return;
        }

        while (state.running < maxConcurrentChecks && state.queue.length) {
            task = state.queue.shift();
            state.running++;
            (function(currentTask, currentState) {
                checkDomain(currentTask.request).then(function(result) {
                    if (availabilityState === currentState && runId === activeRun) {
                        updateRow(currentTask.rowIndex, result);
                    }
                }).always(function() {
                    if (availabilityState !== currentState || runId !== activeRun) {
                        return;
                    }
                    currentState.running--;
                    currentState.completed++;
                    updateAvailabilityStatus(runId);
                    pumpAvailabilityQueue(runId);
                });
            }(task, state));
        }
    }

    function enqueueAvailabilityChecks(domains, runId, startIndex) {
        var state = availabilityState;
        startIndex = startIndex || 0;
        if (!state || state.runId !== runId || runId !== activeRun) {
            return;
        }
        $.each(domains || [], function(index, request) {
            state.queue.push({
                request: request,
                rowIndex: startIndex + index
            });
            state.total++;
        });
        updateAvailabilityStatus(runId);
        pumpAvailabilityQueue(runId);
    }

    function finishNativeSuggestionRequest(runId, failed) {
        var state = availabilityState;
        if (!state || state.runId !== runId || runId !== activeRun) {
            return;
        }
        state.nativePending = false;
        state.nativeFailed = !!failed;
        updateAvailabilityStatus(runId);
    }

    function addToCart($button) {
        var domain = $button.attr('data-domain');
        if (!domain) {
            return;
        }
        if ($button.hasClass('checkout')) {
            window.location = (window.whmcsBaseUrl || '') + '/cart.php?a=confdomains';
            return;
        }
        $button.prop('disabled', true).addClass('disabled')
            .find('.dm-add-label').hide().end()
            .find('.dm-add-loading').show().end()
            .find('.dm-add-added').hide();

        $.ajax({
            url: (window.whmcsBaseUrl || '') + '/cart.php',
            method: 'POST',
            dataType: 'json',
            data: {
                a: 'addToCart',
                domain: domain,
                token: getToken(),
                sideorder: 1
            }
        }).done(function(data) {
            $button.find('.dm-add-loading').hide();
            if (data && data.result === 'added') {
                $button.removeClass('disabled').prop('disabled', false).addClass('checkout')
                    .find('.dm-add-added').show();
                if (data.cartCount) {
                    $('#cartItemCount').html(data.cartCount);
                }
            } else {
                $button.removeClass('disabled btn-primary').prop('disabled', false).addClass('btn-danger')
                    .find('.dm-add-label').show().text('Try Again');
            }
        }).fail(function() {
            $button.find('.dm-add-loading').hide();
            $button.removeClass('disabled btn-primary').prop('disabled', false).addClass('btn-danger')
                .find('.dm-add-label').show().text('Try Again');
        });
    }

    function hideTldBrowserForResults() {
        $('#dmV9DirectTldBrowser').addClass('dm-v9-direct-tld-browser-replaced').hide();
    }

    function showTldBrowserBeforeSearch() {
        $('#dmV9DirectTldBrowser').removeClass('dm-v9-direct-tld-browser-replaced').show();
    }

    function runV9DirectSearch(input) {
        var runId = ++activeRun;
        var completeDomain = parseCompleteDomainInput(input);
        var parsedInput = completeDomain
            ? {message: completeDomain.sld, tlds: [completeDomain.tld]}
            : parseInlineTlds(input);
        var searchInput = parsedInput.message;
        var explicitTlds = parsedInput.tlds;
        var bareWordTlds;
        var priorityTlds;
        var tlds;
        var payload;
        var exactDomains;
        var suggestionRequest;

        if (!searchInput) {
            setSearchButtonLoading(false);
            $('#dmV9DirectStatus').removeClass('alert-info alert-success').addClass('alert-warning')
                .text('Enter a domain idea before the extension choices.')
                .show();
            $('#dmV9DirectResultsCard').hide();
            showTldBrowserBeforeSearch();
            return;
        }

        setSearchButtonLoading(true);
        bareWordTlds = findBareWordTlds(searchInput);
        priorityTlds = uniqueTldList(explicitTlds.concat(bareWordTlds));
        if (priorityTlds.length) {
            ensureTldsSelected(priorityTlds);
        }
        tlds = uniqueTldList(priorityTlds.concat(getSelectedTlds()));

        payload = {
            token: getToken(),
            type: 'suggestions',
            source: 'cartAddDomain',
            maxLength: 30,
            message: searchInput
        };
        if ($('#dmV9DirectSafeSearch').is(':checked')) {
            payload.filter = 'on';
        }
        $.each(tlds, function(index, tld) {
            payload['tlds[' + index + ']'] = tld;
        });

        $('#dmV9DirectResults').empty();
        $('#dmV9DirectEmpty').hide();
        hideTldBrowserForResults();
        $('#dmV9DirectResultsCard').show();
        $('#dmV9DirectResultsCount').text('');
        $('#dmV9DirectSummary').text('');

        beginAvailabilityRun(runId);
        exactDomains = generateExactPhraseDomains(searchInput, explicitTlds, bareWordTlds, tlds);
        if (completeDomain) {
            exactDomains = [completeDomain].concat($.grep(exactDomains, function(item) {
                return item && item.domain !== completeDomain.domain;
            }));
        }
        exactDomains = exactDomains.slice(0, 40);

        /*
         * Start the native request immediately, but do not make the customer wait
         * for it before seeing useful results. Exact phrase rows are rendered and
         * checked in parallel while the native NameSpinner generates suggestions.
         */
        suggestionRequest = $.ajax({
            url: getEndpoint(),
            method: 'POST',
            dataType: 'json',
            data: payload
        });

        $.each(exactDomains, function(index, request) {
            addPlaceholderRow(request, index);
        });
        if (exactDomains.length) {
            $('#dmV9DirectResultsCount').text(exactDomains.length + ' exact match(es) loading...');
            $('#dmV9DirectSummary').text('Exact matches are checking while native suggestions load.');
            applyAvailableOnlyFilter();
            enqueueAvailabilityChecks(exactDomains, runId, 0);
        } else {
            updateAvailabilityStatus(runId);
        }

        suggestionRequest.done(function(data) {
            var v9Domains;
            var contextDomains;
            var domains;
            var remainingDomains;
            var exactCount;
            var nativeCount = 0;
            var contextCount = 0;
            if (runId !== activeRun) {
                return;
            }
            v9Domains = extractSuggestionDomains(data);
            v9Domains = $.grep(v9Domains, function(item) {
                return item && item.tld && tldIsSelected(item.tld, tlds);
            });
            contextDomains = generateContextLookupDomains(searchInput, exactDomains.concat(v9Domains), tlds);
            domains = mergeRankedSuggestions(exactDomains, v9Domains, contextDomains, searchInput, 40);
            exactCount = exactDomains.length;
            remainingDomains = domains.slice(exactCount);
            $.each(domains, function(index, item) {
                if (!item || index < exactCount) {
                    return;
                }
                if (item.source === 'v9 namespinner') {
                    nativeCount++;
                } else {
                    contextCount++;
                }
            });
            $('#dmV9DirectResultsCount').text(domains.length + ' suggestion(s)');
            $('#dmV9DirectSummary').text(exactCount + ' exact phrase • ' + nativeCount + ' ranked native • ' + contextCount + ' ranked context');
            if (!domains.length) {
                $('#dmV9DirectStatus').removeClass('alert-info alert-success').addClass('alert-warning')
                    .text('The v9 endpoint and context lookup did not return suggestion names for that input.')
                    .show();
                $('#dmV9DirectEmpty').show();
                finishNativeSuggestionRequest(runId, false);
                return;
            }
            $.each(remainingDomains, function(index, request) {
                addPlaceholderRow(request, exactCount + index);
            });
            applyAvailableOnlyFilter();
            enqueueAvailabilityChecks(remainingDomains, runId, exactCount);
            finishNativeSuggestionRequest(runId, false);
        }).fail(function(xhr) {
            if (runId !== activeRun) {
                return;
            }
            finishNativeSuggestionRequest(runId, true);
        }).always(function() {
            if (runId === activeRun) {
                setSearchButtonLoading(false);
            }
        });
    }



    var dmV9TldCurrentCategory = 'All';
    var dmV9TldShowSelectedOnly = false;

    /*
     * Patch 1668: build the browser from WHMCS's live Domain Pricing data.
     * configdomains.php and the stock order form use this same pricing collection.
     */
    function isNegativeWhmcsTldPrice(value) {
        value = String(value == null ? '' : value).replace(/^\s+|\s+$/g, '');
        return /^-\d+(?:\.\d+)?$/.test(value);
    }

    function displayWhmcsTldPrice(value) {
        value = String(value == null ? '' : value).replace(/^\s+|\s+$/g, '');
        return isNegativeWhmcsTldPrice(value) ? 'N/A' : value;
    }

    function isUsableWhmcsRegistrationTld(tld, registerPrice) {
        tld = normalizeTld(tld);

        /*
         * Patch 1669: WHMCS can expose legacy/invalid pricing rows such as
         * .??? with -1 prices.  Keep the browser tied to live WHMCS data,
         * but do not expose placeholders that cannot be registered.
         */
        if (!tld || tld === '.' || /\?/.test(tld)) {
            return false;
        }
        if (!/^\.(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)(?:\.[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)*$/i.test(tld)) {
            return false;
        }
        return !isNegativeWhmcsTldPrice(registerPrice);
    }

    function buildWhmcsTldCatalog() {
        var catalog = [];
        $('#dmV9DirectWhmcsTldData .dm-v9-direct-whmcs-tld').each(function() {
            var $item = $(this);
            var categories = [];
            var group = String($item.attr('data-group') || '').toLowerCase();
            var tld = normalizeTld($item.attr('data-tld'));
            var registerPrice = String($item.attr('data-register') || '');
            var transferPrice = String($item.attr('data-transfer') || '');
            var renewalPrice = String($item.attr('data-renewal') || '');

            if (!isUsableWhmcsRegistrationTld(tld, registerPrice)) {
                return;
            }
            $item.find('.dm-v9-direct-whmcs-tld-category').each(function() {
                var category = String($(this).attr('data-category') || '').replace(/^\s+|\s+$/g, '');
                if (category && $.inArray(category, categories) === -1) {
                    categories.push(category);
                }
            });
            if (!categories.length) {
                categories.push('Other');
            }
            catalog.push({
                tld: tld,
                categories: categories,
                register: displayWhmcsTldPrice(registerPrice),
                registerTerm: formatTldTerm($item.attr('data-register-years')),
                transfer: displayWhmcsTldPrice(transferPrice),
                transferTerm: formatTldTerm($item.attr('data-transfer-years')),
                renewal: displayWhmcsTldPrice(renewalPrice),
                renewalTerm: formatTldTerm($item.attr('data-renewal-years')),
                sale: group === 'sale'
            });
        });

        /* Keep the quick search usable if WHMCS ever omits pricing data unexpectedly. */
        if (!catalog.length) {
            $.each(['.com', '.net', '.org', '.us', '.biz', '.ca'], function(index, tld) {
                catalog.push({tld: tld, categories: ['Popular'], register: '', transfer: '', renewal: '', sale: false});
            });
        }
        return catalog;
    }

    function formatTldTerm(years) {
        years = parseInt(years, 10);
        if (!years || years < 1) {
            return '';
        }
        return years + ' Year' + (years === 1 ? '' : 's');
    }

    function buildTldCategoryList(catalog) {
        var preferred = [
            'Popular',
            'Arts and Entertainment',
            'Business',
            'Geographic',
            'Sports',
            'Technology',
            'Services',
            'Money and Finance',
            'Education',
            'Food and Drink',
            'Leisure and Recreation',
            'Shopping',
            'Real Estate',
            'Novelty',
            'Other'
        ];
        var available = {};
        var added = {'All': true};
        var categories = ['All'];

        $.each(catalog || [], function(index, item) {
            $.each(item.categories || [], function(categoryIndex, category) {
                if (category) {
                    available[category] = true;
                }
            });
        });
        $.each(preferred, function(index, category) {
            if (available[category] && !added[category]) {
                added[category] = true;
                categories.push(category);
            }
        });
        $.each(catalog || [], function(index, item) {
            $.each(item.categories || [], function(categoryIndex, category) {
                if (category && !added[category]) {
                    added[category] = true;
                    categories.push(category);
                }
            });
        });
        return categories;
    }

    var dmV9TldCatalog = buildWhmcsTldCatalog();
    var dmV9TldCategories = buildTldCategoryList(dmV9TldCatalog);

    function tldHasCategory(item, category) {
        if (category === 'All') {
            return true;
        }
        return $.inArray(category, item.categories || []) !== -1;
    }

    function getTldCategoryCount(category) {
        if (category === 'All') {
            return dmV9TldCatalog.length;
        }
        return $.grep(dmV9TldCatalog, function(item) {
            return tldHasCategory(item, category);
        }).length;
    }

    function getFilteredTldCatalog() {
        var selectedMap = selectedTldMap(getSelectedTlds());
        return $.grep(dmV9TldCatalog, function(item) {
            var tld = normalizeTld(item.tld);
            if (dmV9TldShowSelectedOnly && !selectedMap[tld]) {
                return false;
            }
            return tldHasCategory(item, dmV9TldCurrentCategory);
        });
    }

    function updateTldSelectedCount() {
        var count = getSelectedTlds().length;
        $('#dmV9DirectTldSelectedCount').text(count + ' extension' + (count === 1 ? '' : 's') + ' selected for search');
    }

    function renderTldCategoryButtons() {
        var $wrap = $('#dmV9DirectTldCategories');
        $wrap.empty();
        $.each(dmV9TldCategories, function(index, category) {
            var label = category === 'All' ? 'All' : category + ' (' + getTldCategoryCount(category) + ')';
            $('<button/>')
                .attr('type', 'button')
                .attr('data-dm-tld-category', category)
                .addClass('btn btn-default btn-sm')
                .toggleClass('active', category === dmV9TldCurrentCategory && !dmV9TldShowSelectedOnly)
                .text(label)
                .appendTo($wrap);
        });
    }

    function renderPriceCell(price, term) {
        return $('<td/>')
            .append($('<span/>').addClass('dm-v9-direct-tld-price').text(price || ''))
            .append($('<span/>').addClass('dm-v9-direct-tld-term').text(term || ''));
    }

    function renderTldTable() {
        var $body = $('#dmV9DirectTldTableBody');
        var selectedMap = selectedTldMap(getSelectedTlds());
        $body.empty();
        $.each(getFilteredTldCatalog(), function(index, item) {
            var tld = normalizeTld(item.tld);
            var $checkbox = $('<input/>')
                .attr('type', 'checkbox')
                .addClass('dm-v9-direct-tld dm-v9-direct-tld-extra')
                .val(tld)
                .prop('checked', !!selectedMap[tld]);
            var $name = $('<span/>').addClass('dm-v9-direct-tld-name')
                .append($checkbox)
                .append($('<strong/>').text(tld));
            if (item.sale) {
                $name.append($('<span/>').addClass('dm-v9-direct-sale-badge').text('Sale'));
            }
            $('<tr/>')
                .attr('data-dm-tld-value', tld)
                .append($('<td/>').append($name))
                .append(renderPriceCell(item.register, item.registerTerm))
                .append(renderPriceCell(item.transfer, item.transferTerm))
                .append(renderPriceCell(item.renewal, item.renewalTerm))
                .appendTo($body);
        });
        updateTldSelectedCount();
    }

    function setTldChecked(tld, checked) {
        tld = normalizeTld(tld);
        $('.dm-v9-direct-tld').each(function() {
            if (normalizeTld($(this).val()) === tld) {
                $(this).prop('checked', checked);
            }
        });
    }

    function setVisibleTldsChecked(checked) {
        $('#dmV9DirectTldTableBody tr:visible').each(function() {
            setTldChecked($(this).attr('data-dm-tld-value'), checked);
        });
        renderTldTable();
        applyAvailableOnlyFilter();
    }

    function renderTldBrowser() {
        if (!$('#dmV9DirectTldBrowser').length) {
            return;
        }
        renderTldCategoryButtons();
        renderTldTable();
    }

    $(function() {
        renderTldBrowser();

        $('#dmV9DirectForm').on('submit', function(e) {
            e.preventDefault();
            if ($('#dmV9DirectButton').prop('disabled')) {
                return;
            }
            var input = $.trim($('#dmV9DirectInput').val());
            if (!input) {
                activeRun++;
                availabilityState = null;
                setSearchButtonLoading(false);
                $('#dmV9DirectStatus').removeClass('alert-info alert-success').addClass('alert-warning')
                    .text('Enter a domain idea first.')
                    .show();
                $('#dmV9DirectResultsCard').hide();
                showTldBrowserBeforeSearch();
                return;
            }
            runV9DirectSearch(input);
        });

        $('#dmV9DirectResults').on('click', '.dm-v9-direct-add-btn', function() {
            addToCart($(this));
        });

        $('#dmV9DirectAvailableOnly').on('change', function() {
            applyAvailableOnlyFilter();
        });

        $('#dmV9DirectSort').on('change', function() {
            applyAvailableOnlyFilter();
        });

        $('#dmV9DirectMaxLength').on('change', function() {
            applyAvailableOnlyFilter();
        });

        $(document).on('change', '.dm-v9-direct-tld', function() {
            var tld = normalizeTld($(this).val());
            setTldChecked(tld, $(this).is(':checked'));
            updateTldSelectedCount();
            if (dmV9TldShowSelectedOnly) {
                renderTldTable();
            }
            applyAvailableOnlyFilter();
        });

        $('#dmV9DirectTldCategories').on('click', '[data-dm-tld-category]', function() {
            dmV9TldCurrentCategory = $(this).attr('data-dm-tld-category') || 'All';
            dmV9TldShowSelectedOnly = false;
            $('#dmV9DirectShowSelected').removeClass('active').text('Show Selected');
            renderTldBrowser();
        });

        $('#dmV9DirectSelectShown').on('click', function() {
            setVisibleTldsChecked(true);
        });

        $('#dmV9DirectClearShown').on('click', function() {
            setVisibleTldsChecked(false);
        });

        $('#dmV9DirectShowSelected').on('click', function() {
            dmV9TldShowSelectedOnly = !dmV9TldShowSelectedOnly;
            $(this).toggleClass('active', dmV9TldShowSelectedOnly)
                .text(dmV9TldShowSelectedOnly ? 'Show All' : 'Show Selected');
            renderTldCategoryButtons();
            renderTldTable();
        });

        if ($.trim($('#dmV9DirectInput').val())) {
            $('#dmV9DirectForm').trigger('submit');
        }
    });
})(jQuery);
</script>
{/literal}
