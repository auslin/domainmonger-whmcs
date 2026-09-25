<style>
/* DomainMonger patch 704: ResellerClub Contact Information table alignment */
.dm-rc-contact-info-page .dm-rc-contact-table {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    table-layout: fixed;
}
.dm-rc-contact-info-page .dm-rc-contact-table td,
.dm-rc-contact-info-page .dm-rc-contact-table th {
    position: static !important;
    left: auto !important;
    right: auto !important;
    transform: none !important;
    text-indent: 0 !important;
    clip: auto !important;
    clip-path: none !important;
    opacity: 1 !important;
    overflow: visible !important;
    white-space: normal !important;
    vertical-align: top;
}
.dm-rc-contact-info-page .dm-rc-contact-table td:first-child {
    width: 34% !important;
    min-width: 190px;
    color: #163a5f !important;
    background: #f8fafc;
    font-weight: 700;
}
.dm-rc-contact-info-page .dm-rc-contact-table td:first-child strong {
    position: static !important;
    left: auto !important;
    transform: none !important;
    text-indent: 0 !important;
    clip: auto !important;
    clip-path: none !important;
    opacity: 1 !important;
    color: #163a5f !important;
    display: inline !important;
    visibility: visible !important;
}
.dm-rc-contact-info-page .tab-content,
.dm-rc-contact-info-page .tab-pane {
    width: 100%;
    max-width: 100%;
    overflow: visible !important;
}
@media (max-width: 767px) {
    .dm-rc-contact-info-page .dm-rc-contact-table {
        table-layout: auto;
    }
    .dm-rc-contact-info-page .dm-rc-contact-table td:first-child {
        min-width: 160px;
    }
}
</style>
<div class="dm-rc-contact-info-page">
<script>
jQuery(document).ready(function($){
    function dmRcActivateContactTab(hash) {
        if (!hash || hash.indexOf('#tab') !== 0) {
            return;
        }
        var $target = $(hash);
        if (!$target.length) {
            return;
        }
        var $tabContent = $target.closest('.tab-content');
        $tabContent.find('.tab-pane').removeClass('active show in').hide();
        $target.addClass('active show in').show();
    }

    $('a[href^="#tab"]').on('click', function(e) {
        var hash = this.hash;
        if (!hash) {
            return;
        }
        e.preventDefault();
        dmRcActivateContactTab(hash);
        $('a[href^="#tab"]').removeClass('active');
        $(this).addClass('active');
    });

    if (window.location.hash && $(window.location.hash).length) {
        dmRcActivateContactTab(window.location.hash);
    } else {
        $('.tab-content .tab-pane.active').addClass('show in').show();
    }
});
</script>

{if $raasuccess}
	<br />
	<div class="alert alert-success">
		<p>{$LANG.moduleactionsuccess}</p>
		<ul>
			<p>{$LANG.rcdom_raasuccess} <strong>{$regcEmailaddr}</strong></p>
		</ul>
	</div><br />
{/if}

{if $irtpsuccess}
	<br />
	<div class="alert alert-success">
		<p>{$LANG.moduleactionsuccess}</p>
		<ul>
			<p>{$LANG.rcdom_irtpsuccess} {$LANG.rcdom_toword} <strong>{$current_regc_email}</strong> {$LANG.rcdom_andword} <strong>{$new_regc_email}</strong></p>
		</ul>
	</div><br />
{/if}

{if $raaverifystatus eq "Pending"}
	<div class="alert alert-warning">
		<p>{$LANG.rcdom_raapendingtitle}</p>
		<ul>
			<p>{$LANG.rcdom_raaverifybefore1} <strong>{$regcEmailaddr}</strong>. {$LANG.rcdom_raaverifybefore2} <strong>{$verifyenddate}</strong>{$LANG.rcdom_raaverifybefore3}</p>
			<form method="post" action="domainmanagement.php?action=domaincontacts">
				<input type="hidden" name="domain" value="{$domain}"/>
				<input type="hidden" name="domainid" value="{$domainid}"/>
				<input type="hidden" name="irtprule" value="{$irtprule}"/>
				<input type="hidden" name="raa" value="resend"/>
				<p><input class="btn btn-success" type="submit" value="{$LANG.rcdom_raasendbutton}"/></p>
			</form>
		</ul>
	</div>
{/if}

{if $irtp_status eq "PENDING"}
	<div class="alert alert-warning">
		<p><strong>{$LANG.rcdom_irtppendingtitle}</strong></p><br />
		<ul>
			<table class="table">
				<tr>
					<th>{$LANG.rcdom_irtpregistranttitle}</th>
					<th>{$LANG.rcdom_irtpstatustitle}</th>
				</tr>
				<tr>
					<td>{$LANG.rcdom_currentregistranttitle} ({$current_regc_email})</td>
					<td>{$current_regc_status}</td>
				</tr>
				<tr>
					<td>{$LANG.rcdom_newregistranttitle} ({$new_regc_email})</td>
					<td>{$new_regc_status}</td>
				</tr>
			</table>
			<form method="post" action="domainmanagement.php?action=domaincontacts">
				<input type="hidden" name="domain" value="{$domain}"/>
				<input type="hidden" name="domainid" value="{$domainid}"/>
				<input type="hidden" name="irtprule" value="{$irtprule}"/>
				<input type="hidden" name="irtp" value="resend"/>
				<p><input class="btn btn-success" type="submit" value="{$LANG.rcdom_irtpsendbutton}"/></p>
			</form>
		</ul>
	</div>
{/if}

<div class="tab-content margin-bottom">
    <div class="tab-pane fade show in active" id="tabRegc">
		{include file="$template/includes/alert.tpl" type="info" msg=$LANG.rcdom_contactdesc}
		<table class="table table-bordered table-hover dm-rc-contact-table">
			<tr>
				<td colspan="2">
					<div class="pull-left">
						<h3>{$LANG.rcdom_regcdetails}</h3>
						<p>{$LANG.rcdom_currentcontactddetailsdesc}</p>
					</div>
					<div class="pull-right" style="padding:0px 3px 2px 0px;">
						<form method="post" action="domainmanagement.php?action=changedomaincontacts">
							<input type="hidden" name="domainid" value="{$domainid}"/>
							<input type="hidden" name="domain" value="{$domain}"/>
							<input type="hidden" name="productkey" value="{$contactproductkey}"/>
							<input type="hidden" name="irtprule" value="{$irtprule}"/>
							<input type="hidden" name="tab" value="modifycontact"/>
							<input type="hidden" name="registrantContactId" value="{$regcContactid}"/>
							<input type="hidden" name="adminContactId" value="{$admincContactid}"/>
							<input type="hidden" name="techContactId" value="{$techcContactid}"/>
							<input type="hidden" name="billingContactId" value="{$billcContactid}"/>
							<input type="hidden" name="contactId" value="{$regcContactid}"/>
							<input class="btn btn-primary" type="submit" value="Manage Contacts"/>
						</form>
					</div>
				</td>
			</tr>
			<tr>
				<td><strong>Contact ID</strong></td>
				<td>{$regcContactid}</td>
			</tr>
			<tr>
				<td><strong>Name</strong></td>
				<td>{$regcName}</td>
			</tr>
			<tr>
				<td><strong>Company Name</strong></td>
				<td>{$regcCompany}</td>
			</tr>
			<tr>
				<td><strong>Email Address</strong></td>
				<td>{$regcEmailaddr}</td>
			</tr>
			<tr>
				<td><strong>Address 1</strong></td>
				<td>{$regcAddress1}</td>
			</tr>
			<tr>
				<td><strong>Address 2</strong></td>
				<td>{$regcAddress2}</td>
			</tr>
			<tr>
				<td><strong>Address 3</strong></td>
				<td>{$regcAddress3}</td>
			</tr>
			<tr>
				<td><strong>Postcode</strong></td>
				<td>{$regcZip}</td>
			</tr>
			<tr>
				<td><strong>City</strong></td>
				<td>{$regcCity}</td>
			</tr>
			<tr>
				<td><strong>State</strong></td>
				<td>{$regcState}</td>
			</tr>
			<tr>
				<td><strong>Country</strong></td>
				<td>{$regcCountry}</td>
			</tr>
			<tr>
				<td><strong>Phone Prefix</strong></td>
				<td>{$regcTelnocc}</td>
			</tr>
			<tr>
				<td><strong>Phone Number</strong></td>
				<td>{$regcTelno}</td>
			</tr>
			<tr>
				<td><strong>Fax Prefix</strong></td>
				<td>{$regcFaxnocc}</td>
			</tr>
			<tr>
				<td><strong>Fax Number</strong></td>
				<td>{$regcFaxno}</td>
			</tr>
		</table>
	</div>

    <div class="tab-pane fade" id="tabAdminc">
		{include file="$template/includes/alert.tpl" type="info" msg=$LANG.rcdom_contactdesc}	
		<table class="table table-bordered table-hover dm-rc-contact-table">
			<tr>
				<td colspan="2">
					<div class="pull-left">
						<h3>{$LANG.rcdom_admincdetails}</h3>
					</div>
					<div class="pull-right" style="padding:0px 3px 2px 0px;">
						<form method="post" action="domainmanagement.php?action=changedomaincontacts">
							<input type="hidden" name="domainid" value="{$domainid}"/>
							<input type="hidden" name="domain" value="{$domain}"/>
							<input type="hidden" name="irtprule" value="{$irtprule}"/>
							<input type="hidden" name="tab" value="modifycontact"/>
							<input type="hidden" name="productkey" value="{$contactproductkey}"/>
							<input type="hidden" name="registrantContactId" value="{$regcContactid}"/>
							<input type="hidden" name="adminContactId" value="{$admincContactid}"/>
							<input type="hidden" name="techContactId" value="{$techcContactid}"/>
							<input type="hidden" name="billingContactId" value="{$billcContactid}"/>
							<input type="hidden" name="contactId" value="{$admincContactid}"/>
							<input class="btn btn-primary" type="submit" value="Manage Contacts"/>
						</form>
					</div>
				</td>
			</tr>
			<tr>
				<td><strong>Contact ID</strong></td>
				<td>{$admincContactid}</td>
			</tr>
			<tr>
				<td><strong>Name</strong></td>
				<td>{$admincName}</td>
			</tr>
			<tr>
				<td><strong>Company Name</strong></td>
				<td>{$admincCompany}</td>
			</tr>
			<tr>
				<td><strong>Email Address</strong></td>
				<td>{$admincEmailaddr}</td>
			</tr>
			<tr>
				<td><strong>Address 1</strong></td>
				<td>{$admincAddress1}</td>
			</tr>
			<tr>
				<td><strong>Address 2</strong></td>
				<td>{$admincAddress2}</td>
			</tr>
			<tr>
				<td><strong>Address 3</strong></td>
				<td>{$admincAddress3}</td>
			</tr>
			<tr>
				<td><strong>Postcode</strong></td>
				<td>{$admincZip}</td>
			</tr>
			<tr>
				<td><strong>City</strong></td>
				<td>{$admincCity}</td>
			</tr>
			<tr>
				<td><strong>State</strong></td>
				<td>{$admincState}</td>
			</tr>
			<tr>
				<td><strong>Country</strong></td>
				<td>{$admincCountry}</td>
			</tr>
			<tr>
				<td><strong>Phone Prefix</strong></td>
				<td>{$admincTelnocc}</td>
			</tr>
			<tr>
				<td><strong>Phone Number</strong></td>
				<td>{$admincTelno}</td>
			</tr>
			<tr>
				<td><strong>Fax Prefix</strong></td>
				<td>{$admincFaxnocc}</td>
			</tr>
			<tr>
				<td><strong>Fax Number</strong></td>
				<td>{$admincFaxno}</td>
			</tr>
		</table>	
	</div>
	
	<div class="tab-pane fade" id="tabTechc">
		{include file="$template/includes/alert.tpl" type="info" msg=$LANG.rcdom_contactdesc}	
		<table class="table table-bordered table-hover dm-rc-contact-table">
			<tr>
				<td colspan="2">
					<div class="pull-left">
						<h3>{$LANG.rcdom_techcdetails}</h3>
					</div>
					<div class="pull-right" style="padding:0px 3px 2px 0px;">
						<form method="post" action="domainmanagement.php?action=changedomaincontacts">
							<input type="hidden" name="domainid" value="{$domainid}"/>
							<input type="hidden" name="domain" value="{$domain}"/>
							<input type="hidden" name="irtprule" value="{$irtprule}"/>
							<input type="hidden" name="tab" value="modifycontact"/>
							<input type="hidden" name="productkey" value="{$contactproductkey}"/>
							<input type="hidden" name="registrantContactId" value="{$regcContactid}"/>
							<input type="hidden" name="adminContactId" value="{$admincContactid}"/>
							<input type="hidden" name="techContactId" value="{$techcContactid}"/>
							<input type="hidden" name="billingContactId" value="{$billcContactid}"/>
							<input type="hidden" name="contactId" value="{$techcContactid}"/>
							<input class="btn btn-primary" type="submit" value="Manage Contacts"/>
						</form>
					</div>
				</td>
			</tr>
			<tr>
				<td><strong>Contact ID</strong></td>
				<td>{$techcContactid}</td>
			</tr>
			<tr>
				<td><strong>Name</strong></td>
				<td>{$techcName}</td>
			</tr>
			<tr>
				<td><strong>Company Name</strong></td>
				<td>{$techcCompany}</td>
			</tr>
			<tr>
				<td><strong>Email Address</strong></td>
				<td>{$techcEmailaddr}</td>
			</tr>
			<tr>
				<td><strong>Address 1</strong></td>
				<td>{$techcAddress1}</td>
			</tr>
			<tr>
				<td><strong>Address 2</strong></td>
				<td>{$techcAddress2}</td>
			</tr>
			<tr>
				<td><strong>Address 3</strong></td>
				<td>{$techcAddress3}</td>
			</tr>
			<tr>
				<td><strong>Postcode</strong></td>
				<td>{$techcZip}</td>
			</tr>
			<tr>
				<td><strong>City</strong></td>
				<td>{$techcCity}</td>
			</tr>
			<tr>
				<td><strong>State</strong></td>
				<td>{$techcState}</td>
			</tr>
			<tr>
				<td><strong>Country</strong></td>
				<td>{$techcCountry}</td>
			</tr>
			<tr>
				<td><strong>Phone Prefix</strong></td>
				<td>{$techcTelnocc}</td>
			</tr>
			<tr>
				<td><strong>Phone Number</strong></td>
				<td>{$techcTelno}</td>
			</tr>
			<tr>
				<td><strong>Fax Prefix</strong></td>
				<td>{$techcFaxnocc}</td>
			</tr>
			<tr>
				<td><strong>Fax Number</strong></td>
				<td>{$techcFaxno}</td>
			</tr>
		</table>	
	</div>

	<div class="tab-pane fade" id="tabBillc">
		{include file="$template/includes/alert.tpl" type="info" msg=$LANG.rcdom_contactdesc}	
		<table class="table table-bordered table-hover dm-rc-contact-table">
			<tr>
				<td colspan="2">
					<div class="pull-left">
						<h3>{$LANG.rcdom_billcdetails}</h3>
					</div>
					<div class="pull-right" style="padding:0px 3px 2px 0px;">
						<form method="post" action="domainmanagement.php?action=changedomaincontacts">
							<input type="hidden" name="domainid" value="{$domainid}"/>
							<input type="hidden" name="domain" value="{$domain}"/>
							<input type="hidden" name="irtprule" value="{$irtprule}"/>
							<input type="hidden" name="tab" value="modifycontact"/>
							<input type="hidden" name="productkey" value="{$contactproductkey}"/>
							<input type="hidden" name="registrantContactId" value="{$regcContactid}"/>
							<input type="hidden" name="adminContactId" value="{$admincContactid}"/>
							<input type="hidden" name="techContactId" value="{$techcContactid}"/>
							<input type="hidden" name="billingContactId" value="{$billcContactid}"/>
							<input type="hidden" name="contactId" value="{$billcContactid}"/>
							<input class="btn btn-primary" type="submit" value="Manage Contacts"/>
						</form>
					</div>
				</td>
			</tr>
			<tr>
				<td><strong>Contact ID</strong></td>
				<td>{$billcContactid}</td>
			</tr>
			<tr>
				<td><strong>Name</strong></td>
				<td>{$billcName}</td>
			</tr>
			<tr>
				<td><strong>Company Name</strong></td>
				<td>{$billcCompany}</td>
			</tr>
			<tr>
				<td><strong>Email Address</strong></td>
				<td>{$billcEmailaddr}</td>
			</tr>
			<tr>
				<td><strong>Address 1</strong></td>
				<td>{$billcAddress1}</td>
			</tr>
			<tr>
				<td><strong>Address 2</strong></td>
				<td>{$billcAddress2}</td>
			</tr>
			<tr>
				<td><strong>Address 3</strong></td>
				<td>{$billcAddress3}</td>
			</tr>
			<tr>
				<td><strong>Postcode</strong></td>
				<td>{$billcZip}</td>
			</tr>
			<tr>
				<td><strong>City</strong></td>
				<td>{$billcCity}</td>
			</tr>
			<tr>
				<td><strong>State</strong></td>
				<td>{$billcState}</td>
			</tr>
			<tr>
				<td><strong>Country</strong></td>
				<td>{$billcCountry}</td>
			</tr>
			<tr>
				<td><strong>Phone Prefix</strong></td>
				<td>{$billcTelnocc}</td>
			</tr>
			<tr>
				<td><strong>Phone Number</strong></td>
				<td>{$billcTelno}</td>
			</tr>
			<tr>
				<td><strong>Fax Prefix</strong></td>
				<td>{$billcFaxnocc}</td>
			</tr>
			<tr>
				<td><strong>Fax Number</strong></td>
				<td>{$billcFaxno}</td>
			</tr>
		</table>
	</div>
	
	<div class="tab-pane fade" id="tabWhoisid">
		{include file="$template/includes/alert.tpl" type="info" msg=$LANG.rcdom_whoisiddesc}
		<div class="internalpadding">
			{if $idprotecterror}
			<br />
			<div class="alert alert-danger">
				<p>{$LANG.clientareaerrors}</p>
				<ul>
					{$idprotecterror}
				</ul>
			</div>
			{/if}
			
			{if $idprotectsuccess}
			<br />
			<div class="alert alert-success">
				<p>{$LANG.moduleactionsuccess}</p>
				<ul>
					{$idprotectsuccess}
				</ul>
			</div>
			{/if}
		
			{if $isprivacyallowed neq "true"}
			<table class="table table-bordered table-hover dm-rc-contact-table">
				<tr>
					<td class="textcenter">
						<h3>{$LANG.rcdom_idwhoistitle}</h3>
					</td>
				</tr>
				<tr>
					<td align="center"><strong>{$LANG.rcdom_idprotectionnotavailable}</strong></td>
				</tr>
			</table>
			{else}
			<form method="post" action="domainmanagement.php?action=domaincontacts#tabWhoisid">
				<input type="hidden" name="request" value="doidprotect"/>
				<input type="hidden" name="domain" value="{$domain}"/>
				<input type="hidden" name="domainid" value="{$domainid}"/>
				<input type="hidden" name="irtprule" value="{$irtprule}"/>
				<input type="hidden" name="tab" value="modifywhois"/>
				<table class="table table-hover">
					<tr>
						<td colspan="2">
							<h3>
								{$LANG.rcdom_idwhoistitle}
								{$LANG.rcdom_idprotectioncurrentstatus}:
								{if $isprivacyon eq "true"}
									<span class="label label-success">{$LANG.rcdom_idprotectionenabled}</span>
								{else}
									<span class="label label-danger">{$LANG.rcdom_idprotectiondisabled}</span>
								{/if}
							</h3>
						</td>
					</tr>
					<tr>
						<td style="vertical-align: middle;">
							<input name="privacystatus" type="checkbox" {if $isprivacyon eq "true"} checked="checked"{/if}/>
						</td>
						<td>
							{$LANG.rcdom_idprotectioncheck}<br />{$LANG.rcdom_idprotectionuncheck}
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<p align="center"><input type="submit" class="btn btn-success" value="{$LANG.clientareasavechanges}"/></p>
						</td>
					</tr>
				</table>
			</form>
			{/if}
		</div>	
	</div>
</div>
</div>
