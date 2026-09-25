{if $loggedin}


	{* Restored Patch 161 menu; broadened to all logged-in /manage pages. *}

		{* Patch 75: load shared WHMCS table/sort-arrow layer plus scoped order-form visual consistency CSS. *}
		<link rel="stylesheet" href="{$WEB_ROOT}/templates/{$template}/css/dm-whmcs-tables-v72.css?v=149">
		<link rel="stylesheet" href="{$WEB_ROOT}/templates/{$template}/css/dm-whmcs-orderforms-v75.css?v=75">

		{* Patch 49: keep WHMCS submenu dropdowns hoverable on desktop while preserving click/tap behavior. *}
		<style>
			@media (min-width: 1200px) {
				.whmcssubmenu .navbar-nav > li.dropdown:hover > .dropdown-menu,
				.whmcssubmenu .navbar-nav > li.dropdown:focus-within > .dropdown-menu {
					display: block;
					margin-top: 0;
				}

				.whmcssubmenu .navbar-nav > li.dropdown:hover > a.dropdown-toggle,
				.whmcssubmenu .navbar-nav > li.dropdown:focus-within > a.dropdown-toggle {
					cursor: pointer;
				}
			}
		</style>

{* Patch 160: WHMCS portal-menu dropdown light style. *}
<style>
	.whmcssubmenu .navbar-nav > li.dropdown > .dropdown-menu,
	.whmcssubmenu .navbar-nav .collapsable-dropdown-menu.dropdown-menu {
		min-width: 205px !important;
		padding: 6px 0 !important;
		background: #ffffff !important;
		background-color: #ffffff !important;
		background-image: none !important;
		border: 1px solid #d7e0ea !important;
		border-radius: 5px !important;
		box-shadow: 0 8px 18px rgba(20, 52, 90, 0.14) !important;
		overflow: hidden !important;
	}

	.whmcssubmenu .navbar-nav > li.dropdown > .dropdown-menu > li.dropdown-item,
	.whmcssubmenu .navbar-nav > li.dropdown > .dropdown-menu > .dropdown-item,
	.whmcssubmenu .navbar-nav .collapsable-dropdown-menu.dropdown-menu > li,
	.whmcssubmenu .navbar-nav .collapsable-dropdown-menu.dropdown-menu > .dropdown-item {
		margin: 0 !important;
		padding: 0 !important;
		background: transparent !important;
		background-color: transparent !important;
		border: 0 !important;
	}

	.whmcssubmenu .navbar-nav > li.dropdown > .dropdown-menu > li.dropdown-item > a,
	.whmcssubmenu .navbar-nav > li.dropdown > .dropdown-menu > .dropdown-item > a,
	.whmcssubmenu .navbar-nav > li.dropdown > .dropdown-menu a.dropdown-item,
	.whmcssubmenu .navbar-nav .collapsable-dropdown-menu.dropdown-menu a {
		display: flex !important;
		align-items: center !important;
		gap: 8px !important;
		width: 100% !important;
		padding: 9px 13px !important;
		color: #14345a !important;
		background: transparent !important;
		background-color: transparent !important;
		font-size: 13px !important;
		font-weight: 600 !important;
		line-height: 1.35 !important;
		text-decoration: none !important;
		text-shadow: none !important;
		white-space: nowrap !important;
	}

	.whmcssubmenu .navbar-nav > li.dropdown > .dropdown-menu a i,
	.whmcssubmenu .navbar-nav .collapsable-dropdown-menu.dropdown-menu a i {
		width: 15px !important;
		color: #51667d !important;
		text-align: center !important;
	}

	.whmcssubmenu .navbar-nav > li.dropdown > .dropdown-menu > li.dropdown-item:hover,
	.whmcssubmenu .navbar-nav > li.dropdown > .dropdown-menu > li.dropdown-item:focus,
	.whmcssubmenu .navbar-nav > li.dropdown > .dropdown-menu > .dropdown-item:hover,
	.whmcssubmenu .navbar-nav > li.dropdown > .dropdown-menu > .dropdown-item:focus,
	.whmcssubmenu .navbar-nav .collapsable-dropdown-menu.dropdown-menu > li:hover,
	.whmcssubmenu .navbar-nav .collapsable-dropdown-menu.dropdown-menu > li:focus {
		background: #fff4ec !important;
		background-color: #fff4ec !important;
	}

	.whmcssubmenu .navbar-nav > li.dropdown > .dropdown-menu > li.dropdown-item:hover > a,
	.whmcssubmenu .navbar-nav > li.dropdown > .dropdown-menu > li.dropdown-item:focus > a,
	.whmcssubmenu .navbar-nav > li.dropdown > .dropdown-menu a.dropdown-item:hover,
	.whmcssubmenu .navbar-nav > li.dropdown > .dropdown-menu a.dropdown-item:focus,
	.whmcssubmenu .navbar-nav .collapsable-dropdown-menu.dropdown-menu a:hover,
	.whmcssubmenu .navbar-nav .collapsable-dropdown-menu.dropdown-menu a:focus {
		color: #14345a !important;
		background: #fff4ec !important;
		background-color: #fff4ec !important;
	}

	.whmcssubmenu .navbar-nav > li.dropdown > .dropdown-menu > li.dropdown-item:hover i,
	.whmcssubmenu .navbar-nav > li.dropdown > .dropdown-menu > li.dropdown-item:focus i,
	.whmcssubmenu .navbar-nav > li.dropdown > .dropdown-menu a.dropdown-item:hover i,
	.whmcssubmenu .navbar-nav > li.dropdown > .dropdown-menu a.dropdown-item:focus i,
	.whmcssubmenu .navbar-nav .collapsable-dropdown-menu.dropdown-menu a:hover i,
	.whmcssubmenu .navbar-nav .collapsable-dropdown-menu.dropdown-menu a:focus i {
		color: #f58220 !important;
	}

	.whmcssubmenu .navbar-nav > li.dropdown > .dropdown-menu .dropdown-divider,
	.whmcssubmenu .navbar-nav .collapsable-dropdown-menu.dropdown-menu .dropdown-divider {
		margin: 5px 0 !important;
		border-top: 1px solid #eef2f6 !important;
	}
</style>

{* Patch 161: Remove WHMCS submenu underline effects. *}
<style>
	.whmcssubmenu a,
	.whmcssubmenu a:hover,
	.whmcssubmenu a:focus,
	.whmcssubmenu a:active,
	.whmcssubmenu .navbar-nav > li > a,
	.whmcssubmenu .navbar-nav > li > a:hover,
	.whmcssubmenu .navbar-nav > li > a:focus,
	.whmcssubmenu .navbar-nav > li > a:active,
	.whmcssubmenu .dropdown-menu a,
	.whmcssubmenu .dropdown-menu a:hover,
	.whmcssubmenu .dropdown-menu a:focus,
	.whmcssubmenu .dropdown-menu a:active,
	.whmcssubmenu .dropdown-menu .dropdown-item,
	.whmcssubmenu .dropdown-menu .dropdown-item:hover,
	.whmcssubmenu .dropdown-menu .dropdown-item:focus,
	.whmcssubmenu .dropdown-menu .dropdown-item:active {
		text-decoration: none !important;
		border-bottom: 0 !important;
		box-shadow: none !important;
	}

	.whmcssubmenu .navbar-nav > li > a::before,
	.whmcssubmenu .navbar-nav > li > a::after,
	.whmcssubmenu .dropdown-menu a::before,
	.whmcssubmenu .dropdown-menu a::after,
	.whmcssubmenu .dropdown-menu .dropdown-item::before,
	.whmcssubmenu .dropdown-menu .dropdown-item::after {
		text-decoration: none !important;
		border-bottom: 0 !important;
		box-shadow: none !important;
	}
</style>

		<div class="whmcssubmenu">
			
			<div class="contentcontainer">
	
			    <header id="header" class="header">
			        <div class="navbar navbar-light">
			            <div class="container-fluid">
			                <ul class="navbar-nav toolbar">
			                    <li class="nav-item ml-3 d-xl-none">
			                        <button class="btn nav-link" type="button" data-toggle="collapse" data-target="#mainNavbar">
			                            <span class="fas fa-bars fa-fw"></span>
			                        </button>
			                    </li>
			                </ul>
			            </div>
			        </div>
			        <div class="navbar navbar-expand-xl main-navbar-wrapper">
			            <div class="container-fluid">
			                <div class="collapse navbar-collapse" id="mainNavbar">
			                    <ul id="nav" class="navbar-nav mr-auto">
			                        {include file="$template/includes/navbar.tpl" navbar=$primaryNavbar dmPortalSubmenu=true}
			                    </ul>
			                    <ul class="navbar-nav ml-auto">
			                        {include file="$template/includes/navbar.tpl" navbar=$secondaryNavbar rightDrop=true}
			                    </ul>
			                </div>
			            </div>
			        </div>
			    </header>
	    
				<div class="clear">&nbsp;</div>
	    
			</div><!-- .contentcontainer -->
	    
		</div><!-- .whmcssubmenu -->

{/if}