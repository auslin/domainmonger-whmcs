<style type="text/css">
{literal}
.notification {
background-color: #dbe3ff;
border-color: #a2b4ee;
color: #585b66;
display:block;
font-style:normal;
padding: 10px 10px 10px 36px;
line-height: 1.5em;
}
.addNewRecord, .editNewRecord {
width: 102px;
}
.ttl {
width: 50px;
}
form.recordsForm select,
.inputTitle,
#addRecordHost, #editRecordHost,
.RP_fields div, .RP_fields input {
width: 49%;
}

.inputSRV {
width: 32.7%;
margin-left: 8px;
}
.SRV_fields .noMargin {
margin: 0;
}
.pointsTo {
width: 100%;
}
.breadcrumb, .form-control {
text-align: left;	
}
.backToZones {
text-align: right;
list-style-type: none;
{/literal}{if $theme eq 'six'}{literal}margin: 10px 0 0 0;{/literal}{/if}{literal}
}
.newMasterServer td form span {
margin-top: 5px;
}
.newMasterServer td form .btn {
margin-left: 5px;
}
.clear {
clear: both;
}
.masterServers .text-right {
text-align: right;
}

.text-left {
text-align: left;
}
.masterServers .masterServerDelete {
width: 16px;	
}

{/literal} {if $version gte '6'}{literal}

#cloudnsMobileSettingsMenu {
	display: none;
}		
		
@media only screen and (max-width: 870px) {
	#cloudnsSlaveMenu {
		display: none;
	}
	
	#cloudnsMobileSettingsMenu {
		display: block;
	}
	
	.backToZones {
		display: none;
	}
	
	.backToZonesMobile {
		list-style-type: none;
	}
	
	section#header .logo-text {
		font-size: 2.3em;
	}
	
	li {
		list-style-type: none;
	}
}

@media only screen and (max-width: 320px) {
	section#header .logo-text {
		font-size: 1.9em;
	}
}
{/literal}{/if}
{if $version gte '7.7'}
{literal}
ul#cloudnsSlaveMenu li {
	float: left;
	list-style-type: none;
	margin-left: 10px;
}
ul#cloudnsSlaveMenu li a {
	margin-right: 10px;	
}	
{/literal}
{/if}


/* DM color/accent consistency pass
   Orange is the module action/link/focus accent.
   Blue is reserved for informational notices.
   Red is reserved for destructive/error states.
   Zones List domain links keep the approved orange text with a slight darken-on-hover. */
.cloudns-body-panel,
.cloudns-zones-panel,
.cloudns-import-panel,
.cloudns-soa-panel,
.cloudns-statistics-panel,
.cloudns-dnssec-panel,
.cloudns-mailforward-panel,
.cloudns-mailforward-mx-panel,
.cloudns-export-panel,
.cloudns-free-ssl-panel,
.cloudns-zone-transfers-panel,
.cloudns-parked-panel,
.cloudns-updated-panel,
.cloudns-form-panel,
.cloudns-record-form-panel,
.cloudns-forward-form-panel,
.newZoneContainer {
	color: #333333 !important;
}

.cloudns-body-panel .table > thead > tr > th,
.cloudns-body-panel .table > tbody > tr > th,
.cloudns-zones-panel .table > thead > tr > th,
.cloudns-zones-panel .table > tbody > tr > th,
.cloudns-import-panel .table > thead > tr > th,
.cloudns-import-panel .table > tbody > tr > th,
.cloudns-soa-panel .table > thead > tr > th,
.cloudns-soa-panel .table > tbody > tr > th,
.cloudns-statistics-panel .table > thead > tr > th,
.cloudns-statistics-panel .table > tbody > tr > th,
.cloudns-dnssec-panel .table > thead > tr > th,
.cloudns-dnssec-panel .table > tbody > tr > th,
.cloudns-mailforward-panel .table > thead > tr > th,
.cloudns-mailforward-panel .table > tbody > tr > th,
.cloudns-export-panel .table > thead > tr > th,
.cloudns-export-panel .table > tbody > tr > th,
.cloudns-free-ssl-panel .table > thead > tr > th,
.cloudns-free-ssl-panel .table > tbody > tr > th,
.cloudns-zone-transfers-panel .table > thead > tr > th,
.cloudns-zone-transfers-panel .table > tbody > tr > th,
.cloudns-parked-panel .table > thead > tr > th,
.cloudns-parked-panel .table > tbody > tr > th,
.cloudns-updated-panel .table > thead > tr > th,
.cloudns-updated-panel .table > tbody > tr > th,
#zones-list th {
	background: #f7f7f7 !important;
	background-color: #f7f7f7 !important;
	background-image: none !important;
	border-color: #dddddd !important;
	color: #333333 !important;
}

.cloudns-body-panel .table > tbody > tr > td,
.cloudns-zones-panel .table > tbody > tr > td,
.cloudns-import-panel .table > tbody > tr > td,
.cloudns-soa-panel .table > tbody > tr > td,
.cloudns-statistics-panel .table > tbody > tr > td,
.cloudns-dnssec-panel .table > tbody > tr > td,
.cloudns-mailforward-panel .table > tbody > tr > td,
.cloudns-export-panel .table > tbody > tr > td,
.cloudns-free-ssl-panel .table > tbody > tr > td,
.cloudns-zone-transfers-panel .table > tbody > tr > td,
.cloudns-parked-panel .table > tbody > tr > td,
.cloudns-updated-panel .table > tbody > tr > td,
#zones-list td {
	background-image: none !important;
	border-color: #eeeeee !important;
	color: #333333 !important;
}

.cloudns-body-panel .table-hover > tbody > tr:hover > td,
.cloudns-body-panel .table-hover > tbody > tr:hover > th,
.cloudns-zones-panel .table-hover > tbody > tr:hover > td,
.cloudns-zones-panel .table-hover > tbody > tr:hover > th,
.cloudns-statistics-panel .table-hover > tbody > tr:hover > td,
.cloudns-statistics-panel .table-hover > tbody > tr:hover > th,
.cloudns-mailforward-panel .table-hover > tbody > tr:hover > td,
.cloudns-mailforward-panel .table-hover > tbody > tr:hover > th,
.cloudns-zone-transfers-panel .table-hover > tbody > tr:hover > td,
.cloudns-zone-transfers-panel .table-hover > tbody > tr:hover > th,
.cloudns-free-ssl-panel .table-hover > tbody > tr:hover > td,
.cloudns-free-ssl-panel .table-hover > tbody > tr:hover > th,
.cloudns-parked-panel .table-hover > tbody > tr:hover > td,
.cloudns-parked-panel .table-hover > tbody > tr:hover > th,
#zones-list.table-hover > tbody > tr:hover > td,
#zones-list.table-hover > tbody > tr:hover > th {
	background-color: #fff8ec !important;
	color: #333333 !important;
}

.cloudns-body-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-import-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-soa-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-statistics-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-dnssec-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-mailforward-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-export-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-free-ssl-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-zone-transfers-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-parked-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-updated-panel a:not(.btn):not(.cloudns-action-icon),
.newZoneContainer a:not(.btn):not(.cloudns-action-icon) {
	color: #d75b0b !important;
	text-decoration: underline !important;
	text-underline-offset: 2px !important;
}

.cloudns-body-panel a:not(.btn):not(.cloudns-action-icon):hover,
.cloudns-body-panel a:not(.btn):not(.cloudns-action-icon):focus,
.cloudns-import-panel a:not(.btn):not(.cloudns-action-icon):hover,
.cloudns-import-panel a:not(.btn):not(.cloudns-action-icon):focus,
.cloudns-soa-panel a:not(.btn):not(.cloudns-action-icon):hover,
.cloudns-soa-panel a:not(.btn):not(.cloudns-action-icon):focus,
.cloudns-statistics-panel a:not(.btn):not(.cloudns-action-icon):hover,
.cloudns-statistics-panel a:not(.btn):not(.cloudns-action-icon):focus,
.cloudns-dnssec-panel a:not(.btn):not(.cloudns-action-icon):hover,
.cloudns-dnssec-panel a:not(.btn):not(.cloudns-action-icon):focus,
.cloudns-mailforward-panel a:not(.btn):not(.cloudns-action-icon):hover,
.cloudns-mailforward-panel a:not(.btn):not(.cloudns-action-icon):focus,
.cloudns-export-panel a:not(.btn):not(.cloudns-action-icon):hover,
.cloudns-export-panel a:not(.btn):not(.cloudns-action-icon):focus,
.cloudns-free-ssl-panel a:not(.btn):not(.cloudns-action-icon):hover,
.cloudns-free-ssl-panel a:not(.btn):not(.cloudns-action-icon):focus,
.cloudns-zone-transfers-panel a:not(.btn):not(.cloudns-action-icon):hover,
.cloudns-zone-transfers-panel a:not(.btn):not(.cloudns-action-icon):focus,
.cloudns-parked-panel a:not(.btn):not(.cloudns-action-icon):hover,
.cloudns-parked-panel a:not(.btn):not(.cloudns-action-icon):focus,
.cloudns-updated-panel a:not(.btn):not(.cloudns-action-icon):hover,
.cloudns-updated-panel a:not(.btn):not(.cloudns-action-icon):focus,
.newZoneContainer a:not(.btn):not(.cloudns-action-icon):hover,
.newZoneContainer a:not(.btn):not(.cloudns-action-icon):focus {
	color: #a94708 !important;
	text-decoration: underline !important;
}

/* Explicitly include the standalone DNS Zones / Zones List page. */
.cloudns-zones-panel #zones-list a:not(.btn):not(.cloudns-action-icon),
#zones-list a:not(.btn):not(.cloudns-action-icon) {
	color: #ef9846 !important;
	text-decoration: none !important;
	font-weight: 600 !important;
}

.cloudns-zones-panel #zones-list a:not(.btn):not(.cloudns-action-icon):hover,
.cloudns-zones-panel #zones-list a:not(.btn):not(.cloudns-action-icon):focus,
#zones-list a:not(.btn):not(.cloudns-action-icon):hover,
#zones-list a:not(.btn):not(.cloudns-action-icon):focus {
	color: #d8873f !important;
	text-decoration: underline !important;
	text-underline-offset: 2px !important;
}

.cloudns-body-panel input.form-control:focus,
.cloudns-body-panel select.form-control:focus,
.cloudns-body-panel textarea.form-control:focus,
.cloudns-zones-panel input.form-control:focus,
.cloudns-zones-panel select.form-control:focus,
.cloudns-zones-panel textarea.form-control:focus,
.cloudns-import-panel input.form-control:focus,
.cloudns-import-panel select.form-control:focus,
.cloudns-import-panel textarea.form-control:focus,
.cloudns-soa-panel input.form-control:focus,
.cloudns-soa-panel select.form-control:focus,
.cloudns-soa-panel textarea.form-control:focus,
.cloudns-statistics-panel input.form-control:focus,
.cloudns-statistics-panel select.form-control:focus,
.cloudns-statistics-panel textarea.form-control:focus,
.cloudns-dnssec-panel input.form-control:focus,
.cloudns-dnssec-panel select.form-control:focus,
.cloudns-dnssec-panel textarea.form-control:focus,
.cloudns-mailforward-panel input.form-control:focus,
.cloudns-mailforward-panel select.form-control:focus,
.cloudns-mailforward-panel textarea.form-control:focus,
.cloudns-export-panel input.form-control:focus,
.cloudns-export-panel select.form-control:focus,
.cloudns-export-panel textarea.form-control:focus,
.cloudns-free-ssl-panel input.form-control:focus,
.cloudns-free-ssl-panel select.form-control:focus,
.cloudns-free-ssl-panel textarea.form-control:focus,
.cloudns-zone-transfers-panel input.form-control:focus,
.cloudns-zone-transfers-panel select.form-control:focus,
.cloudns-zone-transfers-panel textarea.form-control:focus,
.cloudns-parked-panel input.form-control:focus,
.cloudns-parked-panel select.form-control:focus,
.cloudns-parked-panel textarea.form-control:focus,
.newZoneContainer input.form-control:focus,
.newZoneContainer select.form-control:focus,
.newZoneContainer textarea.form-control:focus {
	border-color: #f7941d !important;
	box-shadow: 0 0 0 2px rgba(247, 148, 29, 0.18) !important;
	outline: none !important;
}

.notification,
.cloudns-response:not(.cloudns-response-error),
.cloudns-mailforward-response:not(.cloudns-mailforward-response-error),
.cloudns-free-ssl-response:not(.cloudns-free-ssl-response-error),
.cloudns-zone-transfer-response:not(.cloudns-zone-transfer-response-error),
.cloudns-zone-transfer-notice,
.cloudns-parked-response:not(.cloudns-parked-response-error),
.cloudns-export-notice,
.cloudns-updated-empty {
	background: #f5f8ff !important;
	background-color: #f5f8ff !important;
	background-image: none !important;
	border: 1px solid #b8c7ff !important;
	border-radius: 6px !important;
	color: #30446c !important;
}

.cloudns-response-error,
.cloudns-mailforward-response-error,
.cloudns-free-ssl-response-error,
.cloudns-zone-transfer-response-error,
.cloudns-parked-response-error,
.cloudns-export-error {
	background: #fff1f1 !important;
	background-color: #fff1f1 !important;
	background-image: none !important;
	border: 1px solid #f0c8c8 !important;
	border-radius: 6px !important;
	color: #8f3634 !important;
}

.cloudns-page-status,
.cloudns-updated-summary,
.cloudns-updated-server-meta {
	color: #333333 !important;
}



/* DM typography / forms / tables pass
   Standardize readable text, labels, fields, table density, and helper text.
   Includes standalone DNS Zones / Zones List page. */
.cloudns-module-header,
.cloudns-body-panel,
.cloudns-zones-panel,
.cloudns-import-panel,
.cloudns-soa-panel,
.cloudns-statistics-panel,
.cloudns-dnssec-panel,
.cloudns-mailforward-panel,
.cloudns-mailforward-mx-panel,
.cloudns-export-panel,
.cloudns-free-ssl-panel,
.cloudns-zone-transfers-panel,
.cloudns-parked-panel,
.cloudns-updated-panel,
.cloudns-form-panel,
.cloudns-record-form-panel,
.cloudns-forward-form-panel,
.newZoneContainer {
	font-size: 13px !important;
	line-height: 1.45 !important;
	color: #333333 !important;
}

.cloudns-module-title {
	font-size: 18px !important;
	font-weight: 700 !important;
	line-height: 1.25 !important;
	letter-spacing: 0 !important;
}

.cloudns-body-panel h3,
.cloudns-zones-panel h3,
.cloudns-import-panel h3,
.cloudns-soa-panel h3,
.cloudns-statistics-panel h3,
.cloudns-dnssec-panel h3,
.cloudns-mailforward-panel h3,
.cloudns-export-panel h3,
.cloudns-free-ssl-panel h3,
.cloudns-zone-transfers-panel h3,
.cloudns-parked-panel h3,
.cloudns-updated-panel h3,
.newZoneContainer h3 {
	font-size: 18px !important;
	font-weight: 700 !important;
	line-height: 1.3 !important;
	margin: 0 0 12px 0 !important;
	color: #333333 !important;
}

.cloudns-body-panel h4,
.cloudns-zones-panel h4,
.cloudns-import-panel h4,
.cloudns-soa-panel h4,
.cloudns-statistics-panel h4,
.cloudns-dnssec-panel h4,
.cloudns-mailforward-panel h4,
.cloudns-export-panel h4,
.cloudns-free-ssl-panel h4,
.cloudns-zone-transfers-panel h4,
.cloudns-parked-panel h4,
.cloudns-updated-panel h4,
.newZoneContainer h4 {
	font-size: 16px !important;
	font-weight: 700 !important;
	line-height: 1.3 !important;
	margin: 0 0 12px 0 !important;
	color: #333333 !important;
}

.cloudns-body-panel label,
.cloudns-zones-panel label,
.cloudns-import-panel label,
.cloudns-soa-panel label,
.cloudns-statistics-panel label,
.cloudns-dnssec-panel label,
.cloudns-mailforward-panel label,
.cloudns-export-panel label,
.cloudns-free-ssl-panel label,
.cloudns-zone-transfers-panel label,
.cloudns-parked-panel label,
.cloudns-updated-panel label,
.newZoneContainer label,
#recordsForm label,
.recordsForm label {
	display: inline-block;
	max-width: 100%;
	margin-bottom: 6px !important;
	color: #333333 !important;
	font-size: 13px !important;
	font-weight: 600 !important;
	line-height: 1.35 !important;
}

.cloudns-body-panel .small,
.cloudns-body-panel small,
.cloudns-zones-panel .small,
.cloudns-zones-panel small,
.cloudns-import-panel .small,
.cloudns-import-panel small,
.cloudns-soa-panel .small,
.cloudns-soa-panel small,
.cloudns-statistics-panel .small,
.cloudns-statistics-panel small,
.cloudns-dnssec-panel .small,
.cloudns-dnssec-panel small,
.cloudns-mailforward-panel .small,
.cloudns-mailforward-panel small,
.cloudns-export-panel .small,
.cloudns-export-panel small,
.cloudns-free-ssl-panel .small,
.cloudns-free-ssl-panel small,
.cloudns-zone-transfers-panel .small,
.cloudns-zone-transfers-panel small,
.cloudns-parked-panel .small,
.cloudns-parked-panel small,
.cloudns-updated-panel .small,
.cloudns-updated-panel small,
.newZoneContainer .small,
.newZoneContainer small {
	color: #666666 !important;
	font-size: 12px !important;
	line-height: 1.4 !important;
}

.cloudns-body-panel input.form-control,
.cloudns-body-panel select.form-control,
.cloudns-zones-panel input.form-control,
.cloudns-zones-panel select.form-control,
.cloudns-import-panel input.form-control,
.cloudns-import-panel select.form-control,
.cloudns-soa-panel input.form-control,
.cloudns-soa-panel select.form-control,
.cloudns-statistics-panel input.form-control,
.cloudns-statistics-panel select.form-control,
.cloudns-dnssec-panel input.form-control,
.cloudns-dnssec-panel select.form-control,
.cloudns-mailforward-panel input.form-control,
.cloudns-mailforward-panel select.form-control,
.cloudns-export-panel input.form-control,
.cloudns-export-panel select.form-control,
.cloudns-free-ssl-panel input.form-control,
.cloudns-free-ssl-panel select.form-control,
.cloudns-zone-transfers-panel input.form-control,
.cloudns-zone-transfers-panel select.form-control,
.cloudns-parked-panel input.form-control,
.cloudns-parked-panel select.form-control,
.cloudns-updated-panel input.form-control,
.cloudns-updated-panel select.form-control,
.newZoneContainer input.form-control,
.newZoneContainer select.form-control,
#recordsForm input.form-control,
#recordsForm select.form-control,
.recordsForm input.form-control,
.recordsForm select.form-control {
	min-height: 34px !important;
	height: 34px !important;
	padding: 6px 10px !important;
	border: 1px solid #cccccc !important;
	border-radius: 5px !important;
	background-color: #ffffff !important;
	background-image: none !important;
	color: #333333 !important;
	font-size: 13px !important;
	line-height: 1.35 !important;
	box-sizing: border-box !important;
	box-shadow: none !important;
}

.cloudns-body-panel textarea.form-control,
.cloudns-zones-panel textarea.form-control,
.cloudns-import-panel textarea.form-control,
.cloudns-soa-panel textarea.form-control,
.cloudns-statistics-panel textarea.form-control,
.cloudns-dnssec-panel textarea.form-control,
.cloudns-mailforward-panel textarea.form-control,
.cloudns-export-panel textarea.form-control,
.cloudns-free-ssl-panel textarea.form-control,
.cloudns-zone-transfers-panel textarea.form-control,
.cloudns-parked-panel textarea.form-control,
.cloudns-updated-panel textarea.form-control,
.newZoneContainer textarea.form-control,
#recordsForm textarea.form-control,
.recordsForm textarea.form-control {
	min-height: 120px !important;
	padding: 8px 10px !important;
	border: 1px solid #cccccc !important;
	border-radius: 5px !important;
	background-color: #ffffff !important;
	background-image: none !important;
	color: #333333 !important;
	font-size: 13px !important;
	line-height: 1.45 !important;
	box-sizing: border-box !important;
	box-shadow: none !important;
}

.cloudns-import-panel textarea.form-control,
.importForm textarea {
	min-height: 320px !important;
}

.cloudns-body-panel input[type="checkbox"],
.cloudns-zones-panel input[type="checkbox"],
.cloudns-import-panel input[type="checkbox"],
.cloudns-soa-panel input[type="checkbox"],
.cloudns-statistics-panel input[type="checkbox"],
.cloudns-dnssec-panel input[type="checkbox"],
.cloudns-mailforward-panel input[type="checkbox"],
.cloudns-export-panel input[type="checkbox"],
.cloudns-free-ssl-panel input[type="checkbox"],
.cloudns-zone-transfers-panel input[type="checkbox"],
.cloudns-parked-panel input[type="checkbox"],
.cloudns-updated-panel input[type="checkbox"],
.newZoneContainer input[type="checkbox"],
.cloudns-body-panel input[type="radio"],
.cloudns-zones-panel input[type="radio"],
.cloudns-import-panel input[type="radio"],
.cloudns-soa-panel input[type="radio"],
.cloudns-statistics-panel input[type="radio"],
.cloudns-dnssec-panel input[type="radio"],
.cloudns-mailforward-panel input[type="radio"],
.cloudns-export-panel input[type="radio"],
.cloudns-free-ssl-panel input[type="radio"],
.cloudns-zone-transfers-panel input[type="radio"],
.cloudns-parked-panel input[type="radio"],
.cloudns-updated-panel input[type="radio"],
.newZoneContainer input[type="radio"] {
	margin: 2px 6px 0 0 !important;
	vertical-align: middle !important;
}

.cloudns-body-panel table,
.cloudns-zones-panel table,
.cloudns-import-panel table,
.cloudns-soa-panel table,
.cloudns-statistics-panel table,
.cloudns-dnssec-panel table,
.cloudns-mailforward-panel table,
.cloudns-export-panel table,
.cloudns-free-ssl-panel table,
.cloudns-zone-transfers-panel table,
.cloudns-parked-panel table,
.cloudns-updated-panel table,
.newZoneContainer table,
#zones-list {
	width: 100% !important;
	margin: 0 !important;
	border-collapse: collapse !important;
	font-size: 13px !important;
	line-height: 1.4 !important;
}

.cloudns-body-panel .table > thead > tr > th,
.cloudns-body-panel .table > tbody > tr > th,
.cloudns-zones-panel .table > thead > tr > th,
.cloudns-zones-panel .table > tbody > tr > th,
.cloudns-import-panel .table > thead > tr > th,
.cloudns-import-panel .table > tbody > tr > th,
.cloudns-soa-panel .table > thead > tr > th,
.cloudns-soa-panel .table > tbody > tr > th,
.cloudns-statistics-panel .table > thead > tr > th,
.cloudns-statistics-panel .table > tbody > tr > th,
.cloudns-dnssec-panel .table > thead > tr > th,
.cloudns-dnssec-panel .table > tbody > tr > th,
.cloudns-mailforward-panel .table > thead > tr > th,
.cloudns-mailforward-panel .table > tbody > tr > th,
.cloudns-export-panel .table > thead > tr > th,
.cloudns-export-panel .table > tbody > tr > th,
.cloudns-free-ssl-panel .table > thead > tr > th,
.cloudns-free-ssl-panel .table > tbody > tr > th,
.cloudns-zone-transfers-panel .table > thead > tr > th,
.cloudns-zone-transfers-panel .table > tbody > tr > th,
.cloudns-parked-panel .table > thead > tr > th,
.cloudns-parked-panel .table > tbody > tr > th,
.cloudns-updated-panel .table > thead > tr > th,
.cloudns-updated-panel .table > tbody > tr > th,
#zones-list th {
	padding: 9px 10px !important;
	font-size: 13px !important;
	font-weight: 700 !important;
	line-height: 1.35 !important;
	text-align: left !important;
	vertical-align: middle !important;
	white-space: nowrap !important;
}

.cloudns-body-panel .table > tbody > tr > td,
.cloudns-zones-panel .table > tbody > tr > td,
.cloudns-import-panel .table > tbody > tr > td,
.cloudns-soa-panel .table > tbody > tr > td,
.cloudns-statistics-panel .table > tbody > tr > td,
.cloudns-dnssec-panel .table > tbody > tr > td,
.cloudns-mailforward-panel .table > tbody > tr > td,
.cloudns-export-panel .table > tbody > tr > td,
.cloudns-free-ssl-panel .table > tbody > tr > td,
.cloudns-zone-transfers-panel .table > tbody > tr > td,
.cloudns-parked-panel .table > tbody > tr > td,
.cloudns-updated-panel .table > tbody > tr > td,
#zones-list td {
	padding: 9px 10px !important;
	font-size: 13px !important;
	line-height: 1.45 !important;
	vertical-align: middle !important;
}

.cloudns-body-panel .text-right,
.cloudns-zones-panel .text-right,
.cloudns-mailforward-panel .text-right,
.cloudns-records-panel .text-right,
#zones-list .text-right {
	text-align: right !important;
}

.cloudns-action-col,
.action-col,
.cloudns-sortable .action-col,
#zones-list .zones-options {
	white-space: nowrap !important;
}

.cloudns-action-icon {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: center !important;
	width: 26px !important;
	height: 26px !important;
	min-width: 26px !important;
	margin: 0 1px !important;
	border-radius: 4px !important;
	font-size: 14px !important;
	line-height: 1 !important;
	text-align: center !important;
	vertical-align: middle !important;
}

.cloudns-response,
.cloudns-mailforward-response,
.cloudns-free-ssl-response,
.cloudns-zone-transfer-response,
.cloudns-zone-transfer-notice,
.cloudns-parked-response,
.cloudns-export-error,
.cloudns-updated-empty,
.notification {
	font-size: 13px !important;
	line-height: 1.45 !important;
	padding: 10px 12px !important;
}

.cloudns-record-toolbar,
.cloudns-bulk-actions-row,
.cloudns-mailforward-actions-row,
.cloudns-export-actions,
.cloudns-free-ssl-actions,
.cloudns-zone-transfer-add,
.newZoneButtonsContainer {
	font-size: 13px !important;
	line-height: 1.35 !important;
}

.cloudns-record-toolbar label,
.cloudns-bulk-actions-row label,
.cloudns-export-actions label,
.cloudns-free-ssl-actions label,
.cloudns-zone-transfer-add label,
.newZoneButtonsContainer label {
	margin-bottom: 0 !important;
}

@media only screen and (max-width: 650px) {
	.cloudns-body-panel .table > thead > tr > th,
	.cloudns-body-panel .table > tbody > tr > th,
	.cloudns-zones-panel .table > thead > tr > th,
	.cloudns-zones-panel .table > tbody > tr > th,
	.cloudns-mailforward-panel .table > thead > tr > th,
	.cloudns-mailforward-panel .table > tbody > tr > th,
	#zones-list th,
	.cloudns-body-panel .table > tbody > tr > td,
	.cloudns-zones-panel .table > tbody > tr > td,
	.cloudns-mailforward-panel .table > tbody > tr > td,
	#zones-list td {
		padding: 8px 8px !important;
		font-size: 12px !important;
	}

	.cloudns-module-title {
		font-size: 17px !important;
	}
}


/* DomainMonger Patch 1637: align the slave-zone header/menu stack with the
   confirmed DNSPlus master-zone layout without exposing master-only tools. */
.cloudns-slave-manage-domain-header {
	margin-bottom: 0 !important;
}

.cloudns-slave-zone-switcher label {
	display: none !important;
}

ul#cloudnsSlaveMenu {
	display: flex !important;
	align-items: stretch !important;
	gap: 0 !important;
	flex-wrap: wrap !important;
	min-height: 40px !important;
	padding: 0 !important;
	margin: 0 0 10px 0 !important;
	border: 1px solid #dddddd !important;
	border-radius: 0 0 8px 8px !important;
	background: #ffffff !important;
	background-color: #ffffff !important;
	background-image: none !important;
	box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
	overflow: visible !important;
}

ul#cloudnsSlaveMenu > li {
	display: flex !important;
	align-items: stretch !important;
	float: none !important;
	margin: 0 !important;
	padding: 0 !important;
	list-style: none !important;
}

ul#cloudnsSlaveMenu > li > a {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: center !important;
	min-height: 40px !important;
	padding: 0 14px !important;
	margin: 0 !important;
	border: 0 !important;
	border-bottom: 3px solid transparent !important;
	border-radius: 0 !important;
	background: transparent !important;
	background-color: transparent !important;
	background-image: none !important;
	color: #333333 !important;
	font-size: 13px !important;
	font-weight: 700 !important;
	line-height: 1 !important;
	text-decoration: none !important;
	white-space: nowrap !important;
	box-shadow: none !important;
	box-sizing: border-box !important;
}

ul#cloudnsSlaveMenu > li > a:hover,
ul#cloudnsSlaveMenu > li > a:focus {
	background: #fff7ef !important;
	background-color: #fff7ef !important;
	border-bottom-color: #f7941d !important;
	color: #d75b0b !important;
	text-decoration: none !important;
	outline: none !important;
}

ul#cloudnsSlaveMenu > li.cloudns-menu-active > a {
	background: #fff7ef !important;
	background-color: #fff7ef !important;
	border-bottom-color: #ff6b1a !important;
	color: #d75b0b !important;
}

ul#cloudnsSlaveMenu > li.cloudns-menu-zone-count {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: center !important;
	min-height: 40px !important;
	padding: 0 12px !important;
	margin-left: auto !important;
	border-left: 1px solid #eeeeee !important;
	background: #ffffff !important;
	color: #333333 !important;
	font-size: 13px !important;
	font-weight: 700 !important;
	line-height: 1 !important;
	white-space: nowrap !important;
	box-sizing: border-box !important;
}

ul#cloudnsSlaveMenu > li.cloudns-menu-zone-count + li.cloudns-menu-add-zone {
	margin-left: 0 !important;
}

ul#cloudnsSlaveMenu > li.cloudns-menu-add-zone > a,
ul#cloudnsSlaveMenu > li.cloudns-menu-add-zone > a:visited {
	background: #f58220 !important;
	background-color: #f58220 !important;
	border-bottom-color: #d8741f !important;
	color: #ffffff !important;
}

ul#cloudnsSlaveMenu > li.cloudns-menu-add-zone > a:hover,
ul#cloudnsSlaveMenu > li.cloudns-menu-add-zone > a:focus {
	background: #d8741f !important;
	background-color: #d8741f !important;
	border-bottom-color: #d8741f !important;
	color: #ffffff !important;
}

/* DomainMonger Patch 1638: lock the slave Add Zone action to the confirmed
   DNSPlus primary-button colors so breadcrumb/link rules cannot override it. */
ul#cloudnsSlaveMenu > li.cloudns-menu-add-zone > a#cloudns-slave-add-zone-button,
ul#cloudnsSlaveMenu > li.cloudns-menu-add-zone > a#cloudns-slave-add-zone-button:link,
ul#cloudnsSlaveMenu > li.cloudns-menu-add-zone > a#cloudns-slave-add-zone-button:visited {
	background: #f58220 !important;
	background-color: #f58220 !important;
	background-image: none !important;
	border-color: transparent !important;
	border-bottom-color: #d8741f !important;
	color: #ffffff !important;
	-webkit-text-fill-color: #ffffff !important;
	text-shadow: none !important;
	filter: none !important;
	opacity: 1 !important;
}

ul#cloudnsSlaveMenu > li.cloudns-menu-add-zone > a#cloudns-slave-add-zone-button:hover,
ul#cloudnsSlaveMenu > li.cloudns-menu-add-zone > a#cloudns-slave-add-zone-button:focus,
ul#cloudnsSlaveMenu > li.cloudns-menu-add-zone > a#cloudns-slave-add-zone-button:active {
	background: #d8741f !important;
	background-color: #d8741f !important;
	background-image: none !important;
	border-bottom-color: #d8741f !important;
	color: #ffffff !important;
	-webkit-text-fill-color: #ffffff !important;
	text-shadow: none !important;
	filter: none !important;
	opacity: 1 !important;
}

ul#cloudnsSlaveMenu > li.cloudns-menu-delete > a,
ul#cloudnsSlaveMenu > li.cloudns-menu-delete > a:visited {
	color: #9b2f2f !important;
}

ul#cloudnsSlaveMenu > li.cloudns-menu-delete > a:hover,
ul#cloudnsSlaveMenu > li.cloudns-menu-delete > a:focus {
	background: #fff1f1 !important;
	border-bottom-color: #b94a48 !important;
	color: #9b2f2f !important;
}

@media only screen and (max-width: 870px) {
	ul#cloudnsSlaveMenu {
		display: none !important;
	}

	#cloudnsMobileSettingsMenu {
		display: block !important;
		margin: 0 0 10px 0 !important;
	}
}

</style>
<!-- DomainMonger Patch 57 ClouDNS visual CSS loader -->
<link rel="stylesheet" href="{$WEB_ROOT}/modules/servers/cloudns/templates/cloudns-v57-site-align.css?v=57">
<!-- DomainMonger Patch 64 ClouDNS global late visual override loader -->
<script type="text/javascript">
(function () {
    var cssHref = '{$WEB_ROOT}/modules/servers/cloudns/templates/cloudns-v65-late-override.css?v=176';
    var cssId = 'dm-cloudns-v65-late-override';
    function loadCloudnsLateOverride() {
        if (document.getElementById(cssId)) {
            return;
        }
        var link = document.createElement('link');
        link.id = cssId;
        link.rel = 'stylesheet';
        link.type = 'text/css';
        link.href = cssHref;
        (document.body || document.getElementsByTagName('body')[0] || document.head).appendChild(link);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadCloudnsLateOverride);
    } else {
        loadCloudnsLateOverride();
    }
})();
</script>



<script type="text/javascript" id="dm-cloudns-slave-header-tools-1637">
{literal}
function toggleOptions (element) {
	$('#' + element).toggle();
}

(function ($) {
	$(document)
		.off('click.dmCloudnsSlaveHeader1637', '#cloudns-slave-zone-switcher-button')
		.on('click.dmCloudnsSlaveHeader1637', '#cloudns-slave-zone-switcher-button', function () {
			var selectedZone = $('#cloudns-slave-zone-switcher-select').val();
			if (!selectedZone) {
				return;
			}

			var serviceId = $(this).data('serviceid');
			var url = 'clientarea.php?action=productdetails&id=' + encodeURIComponent(serviceId)
				+ '&customAction=zone-settings&zone=' + encodeURIComponent(selectedZone);

			var softNavigator = window.dmCloudnsSoftNavigate1612 || window.dmCloudnsSoftNavigate1611;
			if (softNavigator && softNavigator.canHandle(url)) {
				softNavigator.navigate(url);
				return;
			}

			window.location.href = url;
		});
})(jQuery);
{/literal}
</script>

<div class="cloudns-module-header cloudns-manage-domain-header cloudns-slave-manage-domain-header">
	<div class="cloudns-module-title-block">
		<span class="cloudns-module-eyebrow">Manage Slave Zone</span>
		<strong class="cloudns-module-domain">{if isset($zone) && $zone != ''}{$zone|@htmlspecialchars}{else}{$pagetitle}{/if}</strong>
	</div>
	<div class="cloudns-header-tools cloudns-slave-header-tools">
		{if isset($domainSwitchZones) && is_array($domainSwitchZones) && count($domainSwitchZones) gt 1}
			<div class="cloudns-global-domain-switcher cloudns-slave-zone-switcher">
				<select id="cloudns-slave-zone-switcher-select" class="form-control" aria-label="Choose DNS zone">
					{foreach from=$domainSwitchZones item=switchZone}
						<option value="{$switchZone.name|@htmlspecialchars}"{if $switchZone.name == $zone} selected="selected"{/if}>{$switchZone.ascii|@htmlspecialchars}</option>
					{/foreach}
				</select>
				<button type="button" id="cloudns-slave-zone-switcher-button" class="btn cloudns-domain-switch-button cloudns-switch-style-button cloudns-btn-primary" data-serviceid="{$serviceid|@htmlspecialchars}">Switch</button>
			</div>
		{/if}
	</div>
</div>
<ul class="breadcrumb" id="cloudnsSlaveMenu">
	<li{if !isset($cloudAction) || $cloudAction == 'zone-settings' || $cloudAction == 'add-master-server' || $cloudAction == 'delete-master-server'} class="cloudns-menu-active"{/if}><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=zone-settings&zone={$zone}">Master Servers</a></li>
	<li{if isset($cloudAction) && $cloudAction == 'bind-settings'} class="cloudns-menu-active"{/if}><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=bind-settings&zone={$zone}">BIND Settings</a></li>
	<li{if isset($cloudAction) && $cloudAction == 'statistics'} class="cloudns-menu-active"{/if}><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=statistics&zone={$zone}&date=last-30-days">Statistics</a></li>
	<li{if isset($cloudAction) && ($cloudAction == 'update-status' || $cloudAction == 'update')} class="cloudns-menu-active"{/if}><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=update-status&zone={$zone}">Status</a></li>
	<li class="cloudns-menu-delete{if isset($cloudAction) && $cloudAction == 'delete-zone'} cloudns-menu-active{/if}"><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=delete-zone&zone={$zone}" title="Delete this DNS zone" onclick="return confirm('Are you sure you want to delete {$zone}?');">Delete</a></li>
	{if $registeredDomains != 'on'}
		<li class="cloudns-menu-zone-count" aria-label="Zones count">Zones: {if isset($zonesCount)}{$zonesCount}{elseif isset($domainSwitchZones) && is_array($domainSwitchZones)}{$domainSwitchZones|@count}{else}0{/if}{if isset($zonesLimit) && $zonesLimit != '' && $zonesLimit != '-1'}/{$zonesLimit}{/if}</li>
		<li class="cloudns-menu-add-zone"><a id="cloudns-slave-add-zone-button" href="clientarea.php?action=productdetails&id={$serviceid}&customAction=add-new-zone">+ Add Zone</a></li>
	{/if}
</ul>
<div class="dropdown" id="cloudnsMobileSettingsMenu">
	<a href="#" class="dropdown-toggle" data-toggle="dropdown">Settings Menu<b class="caret"></b></a>
	<ul class="dropdown-menu">
		<li{if !isset($cloudAction) || $cloudAction == 'zone-settings' || $cloudAction == 'add-master-server' || $cloudAction == 'delete-master-server'} class="cloudns-mobile-menu-active"{/if}><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=zone-settings&zone={$zone}">Master Servers</a></li>
		<li{if isset($cloudAction) && $cloudAction == 'bind-settings'} class="cloudns-mobile-menu-active"{/if}><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=bind-settings&zone={$zone}">BIND Settings</a></li>
		<li{if isset($cloudAction) && $cloudAction == 'statistics'} class="cloudns-mobile-menu-active"{/if}><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=statistics&zone={$zone}&date=last-30-days">Statistics</a></li>
		<li{if isset($cloudAction) && ($cloudAction == 'update-status' || $cloudAction == 'update')} class="cloudns-mobile-menu-active"{/if}><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=update-status&zone={$zone}">Status</a></li>
		<li{if isset($cloudAction) && $cloudAction == 'delete-zone'} class="cloudns-mobile-menu-active"{/if}><a class="cloudns-danger-link" href="clientarea.php?action=productdetails&id={$serviceid}&customAction=delete-zone&zone={$zone}" title="Delete this DNS zone" onclick="return confirm('Are you sure you want to delete {$zone}?');">Delete</a></li>
		{if $registeredDomains != 'on'}
			<li class="divider"></li>
			<li class="dropdown-header">Zones: {if isset($zonesCount)}{$zonesCount}{elseif isset($domainSwitchZones) && is_array($domainSwitchZones)}{$domainSwitchZones|@count}{else}0{/if}{if isset($zonesLimit) && $zonesLimit != '' && $zonesLimit != '-1'}/{$zonesLimit}{/if}</li>
			<li><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=add-new-zone">+ Add Zone</a></li>
		{/if}
	</ul>
</div>
<div class="clear"></div>

{if isset($response.status) && $response.status=='error'}
<div class="notification">{$response.description}</div><br />
{/if}
<br />