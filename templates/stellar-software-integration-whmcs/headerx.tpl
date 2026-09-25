<!DOCTYPE HTML>

<html lang="en">
	
<head>
    <meta charset="{$charset}" />
    
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <title>{if $kbarticle.title}{$kbarticle.title} - {/if}{$pagetitle} - {$companyname}</title>
    
    {include file="$template/includes/head.tpl"}
    
    {include file="$template/integration/integration-head.html"}
    
    {$headoutput}
    
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