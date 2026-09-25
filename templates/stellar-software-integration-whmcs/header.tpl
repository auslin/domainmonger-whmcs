<!DOCTYPE HTML>

<html lang="en">
	
<head>
    <meta charset="{$charset}" />
    
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <title>{if $kbarticle.title}{$kbarticle.title} - {/if}{$pagetitle} - {$companyname}</title>
    
    {include file="$template/includes/head.tpl"}
    
    {include file="$template/integration/integration-head.html"}
    
    {$headoutput}

    <!-- DM Patch 350: rebuilt WHMCS global visual consistency layer + sortable header cleanup -->
    {literal}
    <style id="dm-whmcs-global-style-rebuild-350">
    /*
     * DomainMonger WHMCS v9 global visual consistency rebuild.
     * Patch 350 keeps the confirmed sidebar polish and fixes sortable table header backgrounds.
     * Header-only recovery layer rebuilt from confirmed screenshots after the
     * global header.tpl was reverted by bad submit-ticket experiments.
     */
    :root {
        --dm-orange: #f58220;
        --dm-orange-hover: #dd711b;
        --dm-navy: #163a5f;
        --dm-navy-hover: #214e7a;
        --dm-red: #b94a48;
        --dm-soft-orange: #fff3e7;
        --dm-soft-orange-hover: #fff7ef;
        --dm-border: #d9e1ea;
        --dm-border-soft: #e7edf3;
        --dm-body: #f5f6f8;
        --dm-text: #13283b;
        --dm-muted: #687789;
    }

    body.whmcsbody {
        background: var(--dm-body);
        color: var(--dm-text);
    }

    body.whmcsbody #main-body {
        background: var(--dm-body);
        padding: 42px 0 58px;
    }

    body.whmcsbody #main-body .container {
        max-width: 1120px;
    }

    body.whmcsbody #main-body .primary-content,
    body.whmcsbody #main-body .sidebar {
        color: var(--dm-text);
    }

    /* Links: dark normally, orange on hover/focus, no extra underlines. */
    body.whmcsbody #main-body a:not(.btn):not(.page-link):not(.list-group-item),
    body.whmcsbody .primary-content a:not(.btn):not(.page-link):not(.list-group-item) {
        color: var(--dm-navy);
        text-decoration: none;
    }

    body.whmcsbody #main-body a:not(.btn):not(.page-link):not(.list-group-item):hover,
    body.whmcsbody #main-body a:not(.btn):not(.page-link):not(.list-group-item):focus,
    body.whmcsbody .primary-content a:not(.btn):not(.page-link):not(.list-group-item):hover,
    body.whmcsbody .primary-content a:not(.btn):not(.page-link):not(.list-group-item):focus {
        color: var(--dm-orange);
        text-decoration: none;
    }

    /* Cards, panels, and WHMCS content boxes. */
    body.whmcsbody #main-body .card,
    body.whmcsbody #main-body .panel,
    body.whmcsbody #main-body .domain-promo-box,
    body.whmcsbody #main-body .ticket-reply,
    body.whmcsbody #main-body .ticket-details,
    body.whmcsbody #main-body .client-home-panels .panel,
    body.whmcsbody #main-body .client-home-panels .card {
        background: #fff;
        border: 1px solid var(--dm-border);
        border-radius: 4px;
        box-shadow: 0 2px 6px rgba(22, 58, 95, 0.08);
        overflow: hidden;
    }

    body.whmcsbody #main-body .card-header,
    body.whmcsbody #main-body .panel-heading,
    body.whmcsbody #main-body .panel-title,
    body.whmcsbody #main-body .section-title,
    body.whmcsbody #main-body .dataTables_wrapper table.table thead th,
    body.whmcsbody #main-body table.table-list thead th,
    body.whmcsbody #main-body table.datatable thead th,
    body.whmcsbody #main-body table.table thead th {
        background: var(--dm-navy) !important;
        border-color: var(--dm-navy) !important;
        color: #fff !important;
    }

    body.whmcsbody #main-body .card-header,
    body.whmcsbody #main-body .panel-heading {
        padding: 11px 16px;
        font-weight: 700;
    }

    body.whmcsbody #main-body .card-header h1,
    body.whmcsbody #main-body .card-header h2,
    body.whmcsbody #main-body .card-header h3,
    body.whmcsbody #main-body .card-header h4,
    body.whmcsbody #main-body .card-header .card-title,
    body.whmcsbody #main-body .panel-heading h1,
    body.whmcsbody #main-body .panel-heading h2,
    body.whmcsbody #main-body .panel-heading h3,
    body.whmcsbody #main-body .panel-heading h4,
    body.whmcsbody #main-body .panel-heading .panel-title,
    body.whmcsbody #main-body .panel-heading a,
    body.whmcsbody #main-body .card-header a {
        color: #fff !important;
        font-weight: 700;
        text-decoration: none;
    }

    body.whmcsbody #main-body .card-body,
    body.whmcsbody #main-body .panel-body,
    body.whmcsbody #main-body .list-group,
    body.whmcsbody #main-body .list-group-flush,
    body.whmcsbody #main-body .table-container,
    body.whmcsbody #main-body .dataTables_wrapper {
        background: #fff;
    }

    /* Sidebars: left-side panels, navy headers, white rows, pale-orange hover/active state. */
    body.whmcsbody #main-body .sidebar .panel,
    body.whmcsbody #main-body .sidebar .card,
    body.whmcsbody #main-body .sidebar .panel-sidebar {
        margin-bottom: 24px;
        background: #fff;
        border: 1px solid var(--dm-border);
        border-radius: 4px;
        box-shadow: 0 2px 6px rgba(22, 58, 95, 0.08);
        overflow: hidden;
    }

    body.whmcsbody #main-body .sidebar .panel-heading,
    body.whmcsbody #main-body .sidebar .card-header,
    body.whmcsbody #main-body .sidebar .panel-sidebar .panel-heading {
        background: var(--dm-navy) !important;
        border-color: var(--dm-navy) !important;
        color: #fff !important;
        padding: 10px 14px;
    }

    body.whmcsbody #main-body .sidebar .panel-heading .panel-title,
    body.whmcsbody #main-body .sidebar .panel-heading .panel-title a,
    body.whmcsbody #main-body .sidebar .card-header,
    body.whmcsbody #main-body .sidebar .card-header a,
    body.whmcsbody #main-body .sidebar .card-title,
    body.whmcsbody #main-body .sidebar .card-title a {
        color: #fff !important;
        font-weight: 700;
        text-decoration: none !important;
    }

    body.whmcsbody #main-body .sidebar .panel-sidebar > .panel-heading .panel-title {
        position: relative;
        margin: 0;
        text-align: center;
        line-height: 1.25;
    }

    body.whmcsbody #main-body .sidebar .panel-sidebar > .panel-heading .panel-title .panel-minimise,
    body.whmcsbody #main-body .sidebar .panel-sidebar > .panel-heading .panel-title .pull-right {
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        color: #fff !important;
    }

    body.whmcsbody #main-body .sidebar .list-group,
    body.whmcsbody #main-body .sidebar .panel-body,
    body.whmcsbody #main-body .sidebar .card-body {
        background: #fff;
    }

    body.whmcsbody #main-body .sidebar .list-group-item,
    body.whmcsbody #main-body .sidebar .panel-body a,
    body.whmcsbody #main-body .sidebar .card-body a,
    body.whmcsbody #main-body .sidebar .nav > li > a {
        background: #fff;
        border-color: var(--dm-border-soft);
        color: var(--dm-text) !important;
        text-decoration: none !important;
    }

    body.whmcsbody #main-body .sidebar .list-group-item:hover,
    body.whmcsbody #main-body .sidebar .list-group-item:focus,
    body.whmcsbody #main-body .sidebar .panel-body a:hover,
    body.whmcsbody #main-body .sidebar .panel-body a:focus,
    body.whmcsbody #main-body .sidebar .card-body a:hover,
    body.whmcsbody #main-body .sidebar .card-body a:focus,
    body.whmcsbody #main-body .sidebar .nav > li > a:hover,
    body.whmcsbody #main-body .sidebar .nav > li > a:focus,
    body.whmcsbody #main-body .sidebar .list-group-item.active,
    body.whmcsbody #main-body .sidebar .list-group-item.active:hover,
    body.whmcsbody #main-body .sidebar .list-group-item.active:focus,
    body.whmcsbody #main-body .sidebar .nav > li.active > a,
    body.whmcsbody #main-body .sidebar .nav > li.current > a,
    body.whmcsbody #main-body .sidebar a.active,
    body.whmcsbody #main-body .sidebar .active > a {
        background: var(--dm-soft-orange) !important;
        border-color: #f4dfca !important;
        color: var(--dm-text) !important;
        text-decoration: none !important;
    }

    body.whmcsbody #main-body .sidebar .list-group-item:hover i,
    body.whmcsbody #main-body .sidebar .list-group-item:focus i,
    body.whmcsbody #main-body .sidebar .list-group-item.active i,
    body.whmcsbody #main-body .sidebar .nav > li > a:hover i,
    body.whmcsbody #main-body .sidebar .nav > li > a:focus i,
    body.whmcsbody #main-body .sidebar .nav > li.active > a i {
        color: var(--dm-text) !important;
    }

    body.whmcsbody #main-body .sidebar .list-group-item.active a,
    body.whmcsbody #main-body .sidebar .active > a {
        color: var(--dm-text) !important;
        text-decoration: none !important;
    }

    body.whmcsbody #main-body .sidebar .badge,
    body.whmcsbody #main-body .sidebar .label {
        font-weight: 700;
    }

    /* Forms. */
    body.whmcsbody #main-body .form-control,
    body.whmcsbody #main-body input[type="text"],
    body.whmcsbody #main-body input[type="email"],
    body.whmcsbody #main-body input[type="password"],
    body.whmcsbody #main-body input[type="tel"],
    body.whmcsbody #main-body input[type="number"],
    body.whmcsbody #main-body select,
    body.whmcsbody #main-body textarea {
        border-color: #cfd8e3;
        border-radius: 3px;
        color: var(--dm-text);
        box-shadow: none;
    }

    body.whmcsbody #main-body .form-control:focus,
    body.whmcsbody #main-body input[type="text"]:focus,
    body.whmcsbody #main-body input[type="email"]:focus,
    body.whmcsbody #main-body input[type="password"]:focus,
    body.whmcsbody #main-body input[type="tel"]:focus,
    body.whmcsbody #main-body input[type="number"]:focus,
    body.whmcsbody #main-body select:focus,
    body.whmcsbody #main-body textarea:focus {
        border-color: var(--dm-orange);
        box-shadow: 0 0 0 0.15rem rgba(245, 130, 32, 0.18);
        outline: 0;
    }

    /* Buttons. Keep submit/primary orange; defaults and secondary actions navy; danger red. */
    body.whmcsbody #main-body .btn {
        border-radius: 4px;
        font-weight: 700;
        text-decoration: none !important;
        transition: background-color .12s ease, border-color .12s ease, color .12s ease;
    }

    body.whmcsbody #main-body .btn-primary,
    body.whmcsbody #main-body button[type="submit"].btn-primary,
    body.whmcsbody #main-body input[type="submit"].btn-primary,
    body.whmcsbody #main-body .btn-warning,
    body.whmcsbody #main-body .btn-orange,
    body.whmcsbody #main-body .btn-order-now,
    body.whmcsbody #main-body .checkout-btn {
        background: var(--dm-orange) !important;
        border-color: var(--dm-orange) !important;
        color: #fff !important;
    }

    body.whmcsbody #main-body .btn-primary:hover,
    body.whmcsbody #main-body .btn-primary:focus,
    body.whmcsbody #main-body button[type="submit"].btn-primary:hover,
    body.whmcsbody #main-body button[type="submit"].btn-primary:focus,
    body.whmcsbody #main-body input[type="submit"].btn-primary:hover,
    body.whmcsbody #main-body input[type="submit"].btn-primary:focus,
    body.whmcsbody #main-body .btn-warning:hover,
    body.whmcsbody #main-body .btn-warning:focus,
    body.whmcsbody #main-body .btn-orange:hover,
    body.whmcsbody #main-body .btn-orange:focus,
    body.whmcsbody #main-body .btn-order-now:hover,
    body.whmcsbody #main-body .btn-order-now:focus,
    body.whmcsbody #main-body .checkout-btn:hover,
    body.whmcsbody #main-body .checkout-btn:focus {
        background: var(--dm-orange-hover) !important;
        border-color: var(--dm-orange-hover) !important;
        color: #fff !important;
    }

    body.whmcsbody #main-body .btn-default,
    body.whmcsbody #main-body .btn-secondary,
    body.whmcsbody #main-body .btn-info,
    body.whmcsbody #main-body .btn-outline-primary,
    body.whmcsbody #main-body a.btn-default,
    body.whmcsbody #main-body a.btn-secondary,
    body.whmcsbody #main-body a.btn-info {
        background: var(--dm-navy) !important;
        border-color: var(--dm-navy) !important;
        color: #fff !important;
    }

    body.whmcsbody #main-body .btn-default:hover,
    body.whmcsbody #main-body .btn-default:focus,
    body.whmcsbody #main-body .btn-secondary:hover,
    body.whmcsbody #main-body .btn-secondary:focus,
    body.whmcsbody #main-body .btn-info:hover,
    body.whmcsbody #main-body .btn-info:focus,
    body.whmcsbody #main-body .btn-outline-primary:hover,
    body.whmcsbody #main-body .btn-outline-primary:focus {
        background: var(--dm-navy-hover) !important;
        border-color: var(--dm-navy-hover) !important;
        color: #fff !important;
    }

    body.whmcsbody #main-body .btn-danger,
    body.whmcsbody #main-body .btn-danger:active {
        background: var(--dm-red) !important;
        border-color: var(--dm-red) !important;
        color: #fff !important;
    }

    body.whmcsbody #main-body .btn-danger:hover,
    body.whmcsbody #main-body .btn-danger:focus {
        background: #a64240 !important;
        border-color: #a64240 !important;
        color: #fff !important;
    }

    /* Tables and sortable headers. */
    body.whmcsbody #main-body table.table,
    body.whmcsbody #main-body table.table-list,
    body.whmcsbody #main-body table.datatable {
        background: #fff;
        border-color: var(--dm-border);
    }

    body.whmcsbody #main-body table.table thead th,
    body.whmcsbody #main-body table.table-list thead th,
    body.whmcsbody #main-body table.datatable thead th {
        font-weight: 700;
        border-bottom: 0;
    }

    body.whmcsbody #main-body table.table tbody td,
    body.whmcsbody #main-body table.table-list tbody td,
    body.whmcsbody #main-body table.datatable tbody td {
        border-color: var(--dm-border-soft);
        color: var(--dm-text);
        vertical-align: middle;
    }

    /* Pagination. */
    body.whmcsbody #main-body .pagination > li > a,
    body.whmcsbody #main-body .pagination > li > span,
    body.whmcsbody #main-body .page-link {
        border-color: var(--dm-border);
        color: var(--dm-navy);
        text-decoration: none;
    }

    body.whmcsbody #main-body .pagination > li > a:hover,
    body.whmcsbody #main-body .pagination > li > a:focus,
    body.whmcsbody #main-body .page-link:hover,
    body.whmcsbody #main-body .page-link:focus {
        background: var(--dm-soft-orange-hover);
        border-color: #f7d5b3;
        color: var(--dm-orange);
    }

    body.whmcsbody #main-body .pagination > .active > a,
    body.whmcsbody #main-body .pagination > .active > span,
    body.whmcsbody #main-body .page-item.active .page-link {
        background: var(--dm-navy) !important;
        border-color: var(--dm-navy) !important;
        color: #fff !important;
    }

    /* Alerts: leave warning as the pale WHMCS style. */
    body.whmcsbody #main-body .alert-warning {
        background: #fffecb !important;
        border-color: #fff3a6 !important;
        color: #4b4b24 !important;
    }

    body.whmcsbody #main-body .alert-info {
        background: #eef5fb;
        border-color: #cfe0ef;
        color: var(--dm-navy);
    }

    /* Badges and labels. Closed tickets should be muted, not bright. */
    body.whmcsbody #main-body .label-default,
    body.whmcsbody #main-body .badge-default,
    body.whmcsbody #main-body .badge-secondary,
    body.whmcsbody #main-body .label-closed,
    body.whmcsbody #main-body .status-closed,
    body.whmcsbody #main-body .ticket-status-closed {
        background: #8b96a3 !important;
        color: #fff !important;
    }

    /* Keep WHMCS markdown/editor chrome consistent without changing layout. */
    body.whmcsbody #main-body .md-editor > .md-header,
    body.whmcsbody #main-body .markdown-editor-status {
        background: #555 !important;
        border-color: #555 !important;
    }

    body.whmcsbody #main-body .md-editor > textarea,
    body.whmcsbody #main-body .md-editor .md-input {
        background: #fff;
        border-color: #555 !important;
    }

    body.whmcsbody #main-body .md-editor .btn-default {
        background: var(--dm-navy) !important;
        border-color: var(--dm-navy) !important;
        color: #fff !important;
    }

    body.whmcsbody #main-body .md-editor .btn-primary,
    body.whmcsbody #main-body .md-editor .btn-warning {
        background: var(--dm-orange) !important;
        border-color: var(--dm-orange) !important;
        color: #fff !important;
    }

    /* DataTables filter/search alignment and appearance. */
    body.whmcsbody #main-body .dataTables_filter input,
    body.whmcsbody #main-body .dataTables_length select {
        border: 1px solid #cfd8e3;
        border-radius: 4px;
        box-shadow: none;
        height: 34px;
        padding: 6px 10px;
    }

    /* Keep order summary boxes navy/white/orange when this global header is the only active layer. */
    body.whmcsbody #main-body .order-summary .summary-container,
    body.whmcsbody #main-body .order-summary .total-due-today,
    body.whmcsbody #main-body .summary-container {
        border-color: var(--dm-border);
    }

    body.whmcsbody #main-body .order-summary .summary-header,
    body.whmcsbody #main-body .order-summary h2,
    body.whmcsbody #main-body .summary-container .clearfix,
    body.whmcsbody #main-body .summary-container .subtotal {
        background-color: var(--dm-navy);
        color: #fff;
    }


    /* DM Patch 350: force sortable table headers to remain one navy color.
       This is direct in header.tpl because hook-based versions were not winning
       against the active template/header style order on the Domains list. */
    body.whmcsbody #main-body table.table-list thead th,
    body.whmcsbody #main-body table.table-list thead th.sorting,
    body.whmcsbody #main-body table.table-list thead th.sorting_asc,
    body.whmcsbody #main-body table.table-list thead th.sorting_desc,
    body.whmcsbody #main-body table.dataTable thead th,
    body.whmcsbody #main-body table.dataTable thead th.sorting,
    body.whmcsbody #main-body table.dataTable thead th.sorting_asc,
    body.whmcsbody #main-body table.dataTable thead th.sorting_desc,
    body.whmcsbody #main-body table.datatable thead th,
    body.whmcsbody #main-body table.datatable thead th.sorting,
    body.whmcsbody #main-body table.datatable thead th.sorting_asc,
    body.whmcsbody #main-body table.datatable thead th.sorting_desc,
    body.whmcsbody #main-body .dataTables_wrapper table thead th,
    body.whmcsbody #main-body .dataTables_wrapper table thead th.sorting,
    body.whmcsbody #main-body .dataTables_wrapper table thead th.sorting_asc,
    body.whmcsbody #main-body .dataTables_wrapper table thead th.sorting_desc {
        background: var(--dm-navy) !important;
        background-color: var(--dm-navy) !important;
        background-image: none !important;
        color: #fff !important;
        box-shadow: none !important;
    }

    body.whmcsbody #main-body table.table-list thead th *,
    body.whmcsbody #main-body table.dataTable thead th *,
    body.whmcsbody #main-body table.datatable thead th *,
    body.whmcsbody #main-body .dataTables_wrapper table thead th * {
        background: transparent !important;
        background-color: transparent !important;
        color: #fff !important;
    }

    body.whmcsbody #main-body table.table-list thead th.sorting:before,
    body.whmcsbody #main-body table.table-list thead th.sorting:after,
    body.whmcsbody #main-body table.table-list thead th.sorting_asc:before,
    body.whmcsbody #main-body table.table-list thead th.sorting_asc:after,
    body.whmcsbody #main-body table.table-list thead th.sorting_desc:before,
    body.whmcsbody #main-body table.table-list thead th.sorting_desc:after,
    body.whmcsbody #main-body table.dataTable thead th.sorting:before,
    body.whmcsbody #main-body table.dataTable thead th.sorting:after,
    body.whmcsbody #main-body table.dataTable thead th.sorting_asc:before,
    body.whmcsbody #main-body table.dataTable thead th.sorting_asc:after,
    body.whmcsbody #main-body table.dataTable thead th.sorting_desc:before,
    body.whmcsbody #main-body table.dataTable thead th.sorting_desc:after {
        color: #dbe7f2 !important;
        opacity: .85 !important;
    }

    @media (max-width: 991px) {
        body.whmcsbody #main-body {
            padding-top: 28px;
        }
        body.whmcsbody #main-body .sidebar {
            margin-bottom: 24px;
        }
    }
    </style>
    {/literal}
    
</head>

<body class="primary-bg-color wordpressbody whmcsbody whmcs-filename-{$filename} whmcs-templatefile-{$templatefile}{if $loggedin} whmcs-loggedin{else} whmcs-loggedout{/if}{if $templatefile eq "login1"} feature-slimmedlogin{/if} templatebody template-stellar" data-phone-cc-input="{$phoneNumberInputStyle}">

{if $captcha}{$captcha->getMarkup()}{/if}
{$headeroutput}




	{include file="$template/integration/integration-header.html"}
	
	{include file="$template/includes/subbanner.tpl"}
	
	{include file="$template/includes/submenu-whmcs.tpl"}
		
		{if $loggedin && !$inShoppingCart}

			{include file="$template/includes/network-issues-notifications.tpl"}
    
		{/if}

		{include file="$template/includes/validateuser.tpl"}
		{include file="$template/includes/verifyemail.tpl"}
		
		{include file="$template/integration/integration-content-top.html"}

	    <section id="main-body">
	        <div class="{if !$skipMainBodyContainer}container{/if}">
	            <div class="row">
	
	            {if !$inShoppingCart && ($primarySidebar->hasChildren() || $secondarySidebar->hasChildren())}
	                <div class="col-lg-4 col-xl-3">
	                    <div class="sidebar">
	                        {include file="$template/includes/sidebar.tpl" sidebar=$primarySidebar}
	                    </div>
	                    {if !$inShoppingCart && $secondarySidebar->hasChildren()}
	                        <div class="d-none d-lg-block sidebar">
	                            {include file="$template/includes/sidebar.tpl" sidebar=$secondarySidebar}
	                        </div>
	                    {/if}
	                </div>
	            {/if}
	            <div class="{if !$inShoppingCart && ($primarySidebar->hasChildren() || $secondarySidebar->hasChildren())}col-lg-8 col-xl-9{else}col-12{/if} primary-content">