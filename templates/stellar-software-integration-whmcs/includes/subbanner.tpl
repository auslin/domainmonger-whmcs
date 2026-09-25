{if $templatefile eq "store/ssl/index" || $templatefile eq "store/ssl/dv" || $templatefile eq "store/ssl/ov" || $templatefile eq "store/ssl/ev" || $templatefile eq "store/ssl/wildcard" || $templatefile eq "store/spamexperts/index" || $templatefile eq "store/weebly/index" || $templatefile eq "store/sitelock/index" || $templatefile eq "store/codeguard/index" || $templatefile eq "store/marketgoo/index" || $templatefile eq "store/ox/index" || $templatefile eq "store/sitelockvpn/index" || $templatefile eq "store/sitebuilder/index"}{* Display no subbanner *}

	{elseif $templatefile eq "domain-pricing"}
	
		<div class="banner banner-registerdomain banner-design1column" style="background-image: url({$WEB_ROOT}/templates/{$template}/images/background5.jpg);">
			
			<div class="background" style="background: rgba(0, 0, 0, 0.5);">
				
				<div class="contentcontainer">
						
					<div class="banner-heading animate__animated animate__fadeInUp wow">Register Domain</div>
					
					<div class="banner-text animate__animated animate__fadeInRight wow" data-wow-delay="0.8s">
						Find the perfect domain for your business
					</div>
	
					<div class="banner-domainsearch animate__animated animate__jackInTheBox wow" data-wow-delay="1.6s">					
						<form action="{$WEB_ROOT}/cart.php?a=add&domain=register" method="post" id="frmDomainHomepage">
							<input type="text" placeholder="yourbusinessname.com" name="query" autocapitalize="none" data-toggle="tooltip" data-placement="left" /><input type="submit" value="Search" class="button1 color1" />		
						</form>				
					</div><!-- .banner-domainsearch -->
		
				</div><!-- .contentcontainer -->
		
			</div><!-- .background -->
		
		</div><!-- .banner -->

	{else}

		<div class="subbanner">
			
			<div class="background">
				
				<div class="contentcontainer">
		
					<h1 class="subbanner-heading">
						{if $templatefile eq "viewannouncement"}	
							{$title}
						{elseif $templatefile eq "clientareaproducts"}
							My Services
						{elseif $templatefile eq "clientareadomains" || $templatefile eq "clientareadomainsx"}
							My Domains
						{elseif $templatefile eq "clientareainvoices"}
							My Invoices
						{elseif $templatefile eq "clientareaquotes"}
							My Quotes
						{elseif $templatefile eq "clientareaaddfunds"}
							Add Funds
						{elseif $templatefile eq "domainregister"}
							Register Domains
						{elseif $templatefile eq "domaintransfer"}
							Transfer Domains
						{elseif $templatefile eq "domainrenewals" || $templatefile eq "domainrenew" || $templatefile eq "domain-renewals" || $pagetitle eq "Renew" || $pagetitle eq "Domain Renewals" || ($filename eq "cart" && $smarty.get.a eq "add" && $smarty.get.domain eq "renew")}
							Renew Domains
						{elseif $templatefile eq "user-profile" || $templatefile eq "clientareadetails"}
							My Details
						{elseif $templatefile eq "account-contacts-manage" || $templatefile eq "clientareacontacts" || $templatefile eq "clientareaaddcontact"}
							Contacts
						{elseif $templatefile eq "user-security" || $templatefile eq "clientareasecurity"}
							Account Security
						{elseif $templatefile eq "clientareaemails"}
							Email History
						{elseif $filename eq "clientarea" && $smarty.get.action eq "productdetails" && ($smarty.get.customAction eq "zone-settings" || $smarty.get.customAction eq "add-new-record" || $smarty.get.customAction eq "add-record" || $smarty.get.customAction eq "edit-record" || $smarty.get.customAction eq "delete-record")}
							DNS Records
						{elseif $filename eq "clientarea" && $smarty.get.action eq "productdetails" && ($smarty.get.customAction eq "mail-forwarding" || $smarty.get.customAction eq "add-forward" || $smarty.get.customAction eq "add-new-forwarding" || $smarty.get.customAction eq "edit-forward" || $smarty.get.customAction eq "do-edit-forward" || $smarty.get.customAction eq "delete-forward" || $smarty.get.customAction eq "mail-forwarding-add-mx")}
							Mail Forwards
						{elseif $filename eq "clientarea" && $smarty.get.action eq "productdetails" && $smarty.get.customAction eq "statistics"}
							Statistics
						{elseif $filename eq "clientarea" && $smarty.get.action eq "productdetails" && ($smarty.get.customAction eq "update-status" || $smarty.get.customAction eq "update")}
							Status
						{elseif $filename eq "clientarea" && $smarty.get.action eq "productdetails" && ($smarty.get.customAction eq "soa-settings" || $smarty.get.customAction eq "edit-soa-settings")}
							SOA
						{elseif $filename eq "clientarea" && $smarty.get.action eq "productdetails" && ($smarty.get.customAction eq "dnssec" || $smarty.get.customAction eq "dnssec-show" || $smarty.get.customAction eq "dnssec-settings" || $smarty.get.customAction eq "dnssec-activate" || $smarty.get.customAction eq "dnssec-deactivate" || $smarty.get.customAction eq "dnssec-waiting")}
							DNSSEC
						{elseif $filename eq "clientarea" && $smarty.get.action eq "productdetails" && ($smarty.get.customAction eq "free-ssl" || $smarty.get.customAction eq "freessl-activate" || $smarty.get.customAction eq "freessl-deactivate" || $smarty.get.customAction eq "freessl-change-issuer")}
							SSL
						{elseif $filename eq "clientarea" && $smarty.get.action eq "productdetails" && ($smarty.get.customAction eq "zone-transfers" || $smarty.get.customAction eq "zone-transfers-add" || $smarty.get.customAction eq "zone-transfers-delete")}
							Zone Transfers
						{elseif $filename eq "clientarea" && $smarty.get.action eq "productdetails" && ($smarty.get.customAction eq "import" || $smarty.get.customAction eq "import-records")}
							Import Zone File
						{elseif $filename eq "clientarea" && $smarty.get.action eq "productdetails" && $smarty.get.customAction eq "export-zone-file"}
							Export Zone File
						{else}
							{$pagetitle}
						{/if}
					</h1>
					
					{* Breadcrumb navigation intentionally hidden for WHMCS client-area consistency. *}
		
				</div><!-- .contentcontainer -->
		
			</div><!-- .background -->
		
		</div><!-- .subbanner -->

{/if}