
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

#recordsForm .addNewRecord, #recordsForm .editNewRecord {
width: 105px;
}
#recordsForm .addNewRecord img {
margin-right: 5px;
}
#recordsForm.ttl {
width: 50px;
}
form#recordsForm.recordsForm .selectTLSA, 
form#recordsForm.recordsForm .selectDS,
form#recordsForm.recordsForm .selectCERT,
form#recordsForm.recordsForm .selectHINFO {
width: 32.2%;
margin-right: 8.7px;
float: left;
}
form#recordsForm.recordsForm select,
#recordsForm .inputTitle,
#recordsForm .RP_fields div {
width: 49.7%;
box-sizing: border-box;
}
#recordsForm .RP_fields input,
#recordsForm #addRecordHost, 
#recordsForm #editRecordHost, 
#recordsForm .MX_fields input,
#recordsForm .NAPTR_fields input,
#recordsForm .HINFO_fields input{
width: 47.3%;
}
#recordsForm .inputSRV {
width: 29.8%;
margin-left: 8px;
float: left;
}
#recordsForm .titleLOC {
width: 24.8%;
margin-left: 0;
float: left;
}
#recordsForm .inputLOC {
width: 23%;
margin-left: 14px;
float: left;
}
#recordsForm .inputFirstLOC {
width: 23%;
margin-left: 0;
float: left;
}
form#recordsForm.recordsForm .selectLOC {
width: 23%;
margin-left: 14px;
float: left;
}
#recordsForm div.inputSRV.srvInputTitle {
width: 32.4%;
margin-left: 5px;
float: left;
}
#recordsForm div.inputTLSA.tlsaInputTitle,
#recordsForm div.inputDS.dsInputTitle,
#recordsForm div.inputCERT.certInputTitle,
#recordsForm div.inputHINFO.hinfoInputTitle {
width: 32.2%;
margin-right: 9.3px;
float: left;
}
#recordsForm div.inputSRV.noMargin {
margin: 0;
}

#recordsForm .SRV_fields .noMargin {
margin: 0;
}
#recordsForm .pointsTo {
width: 98%;
}
#recordsForm .importForm table th {
width: 100px;
}

{/literal}
{if $version gte '6'}
{literal}


ul#cloudnsSettingsMenu .cloudns-advanced-dropdown li a {
	display: block;
	min-height: 0;
	padding: 8px 12px;
	border: 0;
	border-bottom: 0;
	border-radius: 0;
	background: #ffffff;
	color: #333333;
	font-size: 13px;
	font-weight: 600;
	line-height: 1.2;
	white-space: nowrap;
}

ul#cloudnsSettingsMenu .cloudns-advanced-dropdown li a:hover,
ul#cloudnsSettingsMenu .cloudns-advanced-dropdown li a:focus {
	background: #fff7ef;
	border: 0;
	color: #f7941d;
}

ul#cloudnsSettingsMenu .cloudns-advanced-dropdown li.cloudns-advanced-active a {
	background: #fff7ef;
	color: #d75b0b;
	box-shadow: inset 3px 0 0 #ff6b1a;
}

ul#cloudnsSettingsMenu .cloudns-advanced-dropdown li.cloudns-advanced-group-divider {
	height: 1px;
	margin: 6px 0;
	background: #e5e5e5;
}

ul#cloudnsSettingsMenu .cloudns-advanced-dropdown li.cloudns-advanced-divider {
	height: 1px;
	margin: 6px 0;
	background: #e5e5e5;
}

ul#cloudnsSettingsMenu .cloudns-advanced-dropdown li.cloudns-advanced-danger a {
	color: #9b2f2f;
}

ul#cloudnsSettingsMenu .cloudns-advanced-dropdown li.cloudns-advanced-danger a:hover,
ul#cloudnsSettingsMenu .cloudns-advanced-dropdown li.cloudns-advanced-danger a:focus {
	background: #fff1f1;
	color: #9b2f2f;
}

@media only screen and (max-width: 870px) {
	.cloudns-module-header {
		align-items: flex-start;
		flex-direction: column;
	}

	.cloudns-header-tools,
	.cloudns-global-domain-switcher {
		width: 100%;
		justify-content: flex-start;
	}

	.cloudns-global-domain-switcher select.form-control {
		min-width: 0;
		flex: 1 1 180px;
	}
}

#cloudnsMobileSettingsMenu {
	display: none;
}	
	
#recordsForm .RP_fields input,
#recordsForm #addRecordHost, 
#recordsForm #editRecordHost, 
#recordsForm .MX_fields input,
#recordsForm .NAPTR_fields input,
#recordsForm .HINFO_fields input{
width: 49.7%;
}

#recordsForm .inputSRV {
width: 32.7%;
margin-left: 5px;
float: left;
}

#recordsForm div.inputSRV.srvInputTitle {
width: 32.7%;
float: left;
}
	
#recordsForm .pointsTo {
width: 100%;
}

@media only screen and (max-width: 870px) {
	#cloudnsSettingsMenu {
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
}

@media only screen and (max-width: 320px) {
	section#header .logo-text {
		font-size: 1.9em;
	}
}
{/literal}
{/if}
{literal}
.importForm textarea {
{/literal}
{if $version gte '6' && $theme eq 'six'}{literal}width: 100%;{/literal}{else}{literal}width: 98%;{/literal}
{/if}{literal}
	
height: 600px;
}
.clear {
clear: both;
}


/* DM button height consistency pass */
.cloudns-global-domain-switcher select.form-control,
.cloudns-bulk-actions-row select.form-control,
.cloudns-bulk-actions-row .btn,
.cloudns-zone-transfer-add input.form-control,
.cloudns-zone-transfer-add .btn,
.cloudns-export-actions select.form-control,
.cloudns-export-actions .btn,
.cloudns-free-ssl-panel select.form-control,
.cloudns-free-ssl-panel .btn,
.cloudns-parked-template-form select.form-control,
.cloudns-parked-template-form .btn {
	height: 34px !important;
	min-height: 34px !important;
	max-height: 34px !important;
	box-sizing: border-box !important;
}

.cloudns-domain-switch-button,
.cloudns-domain-switch-button:visited,
.cloudns-switch-style-button,
.cloudns-switch-style-button:visited,
a.btn.cloudns-switch-style-button,
button.btn.cloudns-switch-style-button,
input.btn.cloudns-switch-style-button {
	height: 34px !important;
	min-height: 34px !important;
	max-height: 34px !important;
	line-height: 1 !important;
	box-sizing: border-box !important;
}

/* DM body consistency pass */
.cloudns-body-panel,
.importForm.cloudns-body-panel,
.cloudns-import-panel,
.cloudns-soa-panel,
.cloudns-statistics-panel,
.cloudns-dnssec-panel,
.cloudns-mailforward-panel,
.cloudns-export-panel,
.cloudns-free-ssl-panel,
.cloudns-zone-transfers-panel,
.cloudns-parked-panel,
.cloudns-updated-panel {
	border: 1px solid #dddddd !important;
	border-radius: 8px !important;
	background: #ffffff !important;
	box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
	padding: 14px !important;
	margin: 0 0 14px 0 !important;
	box-sizing: border-box !important;
	overflow: hidden !important;
}

.cloudns-body-panel > h3:first-child,
.cloudns-body-panel > h4:first-child,
.cloudns-export-panel > h3:first-child,
.cloudns-export-panel > h4:first-child,
.cloudns-free-ssl-panel > h3:first-child,
.cloudns-free-ssl-panel > h4:first-child,
.cloudns-zone-transfers-panel > h3:first-child,
.cloudns-zone-transfers-panel > h4:first-child,
.cloudns-parked-panel > h3:first-child,
.cloudns-parked-panel > h4:first-child,
.cloudns-updated-panel > h3:first-child,
.cloudns-updated-panel > h4:first-child {
	margin-top: 0 !important;
}

.cloudns-body-panel table,
.cloudns-import-panel table,
.cloudns-soa-panel table,
.cloudns-statistics-panel table,
.cloudns-dnssec-panel table,
.cloudns-mailforward-panel table {
	margin-bottom: 0 !important;
}

.cloudns-rounded-table,
.cloudns-body-panel .table,
.cloudns-import-panel .table,
.cloudns-soa-panel .table,
.cloudns-statistics-panel .table,
.cloudns-dnssec-panel .table,
.cloudns-mailforward-panel .table,
.cloudns-updated-panel .table,
.cloudns-zone-transfers-panel .table,
.cloudns-parked-panel .table {
	border-radius: 7px !important;
	overflow: hidden !important;
	background: #ffffff !important;
}

.cloudns-table-shell,
.table-responsive {
	border-radius: 8px;
	overflow: hidden;
}

.cloudns-body-panel textarea.form-control,
.cloudns-import-panel textarea.form-control {
	border-radius: 6px !important;
}

.cloudns-section-tools,
.cloudns-statistics-links {
	display: flex;
	align-items: center;
	gap: 8px;
	flex-wrap: wrap;
	margin: 0 0 12px 0;
}

.cloudns-muted-note {
	color: #666666;
	font-size: 12px;
}

/* DM UI consistency pass: body page titles and status text */
.cloudns-page-title {
	margin: 0 0 14px 0 !important;
	padding: 0 !important;
	color: #333333 !important;
	font-size: 20px !important;
	font-weight: 700 !important;
	line-height: 1.25 !important;
}

.cloudns-page-status {
	margin: -4px 0 14px 0 !important;
	color: #444444 !important;
	font-size: 14px !important;
	font-weight: 600 !important;
	line-height: 1.4 !important;
}

.cloudns-page-status strong {
	color: #222222 !important;
	font-weight: 700 !important;
}

.cloudns-records-panel {
	overflow: visible !important;
}

/* DM body consistency pass 2 */
.cloudns-body-panel,
.importForm.cloudns-body-panel,
.cloudns-import-panel,
.cloudns-soa-panel,
.cloudns-statistics-panel,
.cloudns-dnssec-panel,
.cloudns-mailforward-panel,
.cloudns-export-panel,
.cloudns-free-ssl-panel,
.cloudns-zone-transfers-panel,
.cloudns-parked-panel,
.cloudns-updated-panel {
	width: 100% !important;
	max-width: none !important;
	display: block !important;
}

.cloudns-dnssec-panel,
.cloudns-dnssec-panel .table-responsive,
.cloudns-dnssec-panel .table,
.cloudns-dnssec-panel table,
.cloudns-dnssec-panel tbody,
.cloudns-dnssec-panel tr,
.cloudns-dnssec-panel td,
.cloudns-dnssec-panel th {
	background-color: #ffffff !important;
}

.cloudns-dnssec-panel .table-responsive {
	border: 1px solid #dddddd;
	border-radius: 8px;
	margin-bottom: 12px;
	overflow: hidden;
}

.cloudns-dnssec-panel .notification {
	background: #dce6ff !important;
	border: 1px solid #9fb7ff !important;
	border-radius: 6px !important;
	padding: 12px 14px !important;
	margin: 0 0 18px 0 !important;
	color: #30446c !important;
	line-height: 1.45 !important;
}

.cloudns-dnssec-panel br.clear {
	display: none;
}

.cloudns-dnssec-panel h3:first-child {
	margin-bottom: 18px !important;
}

.cloudns-dnssec-panel h3:first-child {
	margin-top: 0 !important;
}

.cloudns-zone-transfers-panel {
	width: 100% !important;
	max-width: none !important;
	margin-left: 0 !important;
	margin-right: 0 !important;
}

.cloudns-zone-transfer-list {
	width: 100% !important;
}

.cloudns-zone-transfer-row {
	width: 100% !important;
	box-sizing: border-box !important;
}

.cloudns-export-panel,
.cloudns-free-ssl-panel,
.cloudns-updated-panel,
.cloudns-zone-transfers-panel,
.cloudns-parked-panel {
	max-width: none !important;
}

@media only screen and (max-width: 650px) {
	.cloudns-body-panel,
	.importForm.cloudns-body-panel,
	.cloudns-import-panel,
	.cloudns-soa-panel,
	.cloudns-statistics-panel,
	.cloudns-dnssec-panel,
	.cloudns-mailforward-panel,
	.cloudns-export-panel,
	.cloudns-free-ssl-panel,
	.cloudns-zone-transfers-panel,
	.cloudns-parked-panel,
	.cloudns-updated-panel {
		padding: 10px !important;
	}
}

/* DM deep UI consistency pass */
.cloudns-body-panel,
.importForm.cloudns-body-panel,
.cloudns-import-panel,
.cloudns-soa-panel,
.cloudns-statistics-panel,
.cloudns-dnssec-panel,
.cloudns-mailforward-panel,
.cloudns-export-panel,
.cloudns-free-ssl-panel,
.cloudns-zone-transfers-panel,
.cloudns-parked-panel,
.cloudns-updated-panel,
form#recordsForm.recordsForm:not(.cloudns-record-filter) {
	font-size: 13px !important;
	line-height: 1.45 !important;
	color: #333333 !important;
}

form#recordsForm.recordsForm:not(.cloudns-record-filter) {
	border: 1px solid #dddddd !important;
	border-radius: 8px !important;
	background: #ffffff !important;
	box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
	padding: 14px !important;
	margin: 0 0 14px 0 !important;
	box-sizing: border-box !important;
}

.cloudns-body-panel input.form-control,
.cloudns-body-panel select.form-control,
.cloudns-body-panel textarea.form-control,
.cloudns-import-panel input.form-control,
.cloudns-import-panel select.form-control,
.cloudns-import-panel textarea.form-control,
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
form#recordsForm.recordsForm input.form-control,
form#recordsForm.recordsForm select.form-control {
	border: 1px solid #cccccc !important;
	border-radius: 5px !important;
	background-color: #ffffff !important;
	color: #333333 !important;
	font-size: 13px !important;
	box-shadow: none !important;
}

.cloudns-body-panel input.form-control,
.cloudns-body-panel select.form-control,
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
form#recordsForm.recordsForm input.form-control,
form#recordsForm.recordsForm select.form-control {
	height: 34px !important;
	min-height: 34px !important;
	box-sizing: border-box !important;
}

.cloudns-body-panel textarea.form-control,
.cloudns-import-panel textarea.form-control {
	min-height: 180px !important;
	line-height: 1.45 !important;
}

.cloudns-body-panel .table > thead > tr > th,
.cloudns-body-panel .table > tbody > tr > th,
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
.cloudns-free-ssl-panel .table > thead > tr > th,
.cloudns-free-ssl-panel .table > tbody > tr > th {
	background-color: #f7f7f7;
	border-color: #dddddd;
	color: #333333;
	font-size: 13px;
	font-weight: 700;
}

.cloudns-body-panel .table > tbody > tr > td,
.cloudns-import-panel .table > tbody > tr > td,
.cloudns-soa-panel .table > tbody > tr > td,
.cloudns-statistics-panel .table > tbody > tr > td,
.cloudns-dnssec-panel .table > tbody > tr > td,
.cloudns-mailforward-panel .table > tbody > tr > td,
.cloudns-free-ssl-panel .table > tbody > tr > td {
	border-color: #eeeeee;
	color: #333333;
	font-size: 13px;
}

.cloudns-body-panel a,
.cloudns-import-panel a,
.cloudns-soa-panel a,
.cloudns-statistics-panel a,
.cloudns-dnssec-panel a,
.cloudns-mailforward-panel a,
.cloudns-export-panel a,
.cloudns-free-ssl-panel a,
.cloudns-zone-transfers-panel a,
.cloudns-updated-panel a {
	color: #d75b0b;
}

.cloudns-body-panel a:hover,
.cloudns-body-panel a:focus,
.cloudns-import-panel a:hover,
.cloudns-import-panel a:focus,
.cloudns-soa-panel a:hover,
.cloudns-soa-panel a:focus,
.cloudns-statistics-panel a:hover,
.cloudns-statistics-panel a:focus,
.cloudns-dnssec-panel a:hover,
.cloudns-dnssec-panel a:focus,
.cloudns-mailforward-panel a:hover,
.cloudns-mailforward-panel a:focus,
.cloudns-export-panel a:hover,
.cloudns-export-panel a:focus,
.cloudns-free-ssl-panel a:hover,
.cloudns-free-ssl-panel a:focus,
.cloudns-zone-transfers-panel a:hover,
.cloudns-zone-transfers-panel a:focus,
.cloudns-updated-panel a:hover,
.cloudns-updated-panel a:focus {
	color: #f7941d;
	text-decoration: underline;
}

.notification,
.cloudns-zone-transfer-notice,
.cloudns-mailforward-mx-panel,
.cloudns-export-help,
.cloudns-updated-empty,
.cloudns-parked-zone-note {
	border: 1px solid #9fb7ff !important;
	border-radius: 6px !important;
	background: #dce6ff !important;
	color: #30446c !important;
	font-size: 13px !important;
	line-height: 1.45 !important;
	padding: 12px 14px !important;
}

.cloudns-mailforward-mx-panel,
.cloudns-zone-transfer-add,
.cloudns-free-ssl-actions,
.cloudns-export-actions,
.cloudns-updated-actions,
.cloudns-section-tools {
	border-radius: 7px !important;
}

.cloudns-zone-transfer-add,
.cloudns-free-ssl-actions,
.cloudns-export-actions {
	background: #f7f7f7 !important;
	border: 1px solid #e2e2e2 !important;
}

.cloudns-zone-transfer-delete,
.cloudns-zone-transfer-delete:visited,
.zones-options .btn-danger,
.zones-options .btn-danger:visited {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: center !important;
	height: 34px !important;
	min-height: 34px !important;
	max-height: 34px !important;
	min-width: 72px !important;
	padding: 0 14px !important;
	border: 1px solid #b94a48 !important;
	border-radius: 5px !important;
	background: #b94a48 !important;
	background-image: none !important;
	color: #ffffff !important;
	font-size: 14px !important;
	font-weight: 700 !important;
	line-height: 1 !important;
	text-decoration: none !important;
	text-shadow: none !important;
	box-shadow: none !important;
}

.cloudns-zone-transfer-delete:hover,
.cloudns-zone-transfer-delete:focus,
.zones-options .btn-danger:hover,
.zones-options .btn-danger:focus {
	background: #a94442 !important;
	border-color: #a94442 !important;
	color: #ffffff !important;
	text-decoration: none !important;
	box-shadow: 0 0 0 2px rgba(185, 74, 72, 0.18) !important;
	outline: none !important;
}

.cloudns-zone-transfer-delete:active,
.zones-options .btn-danger:active {
	background: #8f3634 !important;
	border-color: #8f3634 !important;
	box-shadow: 0 0 0 2px rgba(185, 74, 72, 0.25) !important;
}

.cloudns-module-title {
	color: #333333 !important;	
}

@media only screen and (max-width: 650px) {
	form#recordsForm.recordsForm:not(.cloudns-record-filter) {
		padding: 10px !important;
	}
}


/* DM final visual consistency pass */
.cloudns-body-panel,
.cloudns-zones-panel,
.cloudns-import-panel,
.cloudns-soa-panel,
.cloudns-statistics-panel,
.cloudns-dnssec-panel,
.cloudns-mailforward-panel,
.cloudns-export-panel,
.cloudns-free-ssl-panel,
.cloudns-zone-transfers-panel,
.cloudns-parked-panel,
.cloudns-updated-panel,
form#recordsForm.recordsForm:not(.cloudns-record-filter),
.newZoneContainer {
	border-color: #dddddd !important;
	border-radius: 8px !important;
	background-color: #ffffff !important;
	box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
	color: #333333 !important;
	font-size: 13px !important;
	line-height: 1.45 !important;
}

.cloudns-body-panel,
.cloudns-zones-panel,
.cloudns-import-panel,
.cloudns-soa-panel,
.cloudns-statistics-panel,
.cloudns-dnssec-panel,
.cloudns-mailforward-panel,
.cloudns-export-panel,
.cloudns-free-ssl-panel,
.cloudns-zone-transfers-panel,
.cloudns-parked-panel,
.cloudns-updated-panel,
form#recordsForm.recordsForm:not(.cloudns-record-filter) {
	padding: 14px !important;
	margin-top: 0 !important;
	margin-bottom: 14px !important;
}

.cloudns-body-panel h3,
.cloudns-body-panel h4,
.cloudns-zones-panel h3,
.cloudns-zones-panel h4,
.cloudns-import-panel h3,
.cloudns-import-panel h4,
.cloudns-soa-panel h3,
.cloudns-soa-panel h4,
.cloudns-statistics-panel h3,
.cloudns-statistics-panel h4,
.cloudns-dnssec-panel h3,
.cloudns-dnssec-panel h4,
.cloudns-mailforward-panel h3,
.cloudns-mailforward-panel h4,
.cloudns-export-panel h3,
.cloudns-export-panel h4,
.cloudns-free-ssl-panel h3,
.cloudns-free-ssl-panel h4,
.cloudns-zone-transfers-panel h3,
.cloudns-zone-transfers-panel h4,
.cloudns-parked-panel h3,
.cloudns-parked-panel h4,
.cloudns-updated-panel h3,
.cloudns-updated-panel h4,
.newZoneContainer h3,
.newZoneContainer h4 {
	color: #333333 !important;
	font-size: 18px !important;
	font-weight: 700 !important;
	line-height: 1.25 !important;
}

.cloudns-table-shell,
.cloudns-body-panel .table-responsive,
.cloudns-zones-panel .table-responsive,
.cloudns-import-panel .table-responsive,
.cloudns-soa-panel .table-responsive,
.cloudns-statistics-panel .table-responsive,
.cloudns-dnssec-panel .table-responsive,
.cloudns-mailforward-panel .table-responsive,
.cloudns-export-panel .table-responsive,
.cloudns-free-ssl-panel .table-responsive,
.cloudns-zone-transfers-panel .table-responsive,
.cloudns-parked-panel .table-responsive,
.cloudns-updated-panel .table-responsive {
	border-radius: 8px !important;
	overflow: hidden !important;
}

.cloudns-body-panel .table,
.cloudns-zones-panel .table,
.cloudns-import-panel .table,
.cloudns-soa-panel .table,
.cloudns-statistics-panel .table,
.cloudns-dnssec-panel .table,
.cloudns-mailforward-panel .table,
.cloudns-export-panel .table,
.cloudns-free-ssl-panel .table,
.cloudns-zone-transfers-panel .table,
.cloudns-parked-panel .table,
.cloudns-updated-panel .table,
#zones-list {
	border-collapse: collapse !important;
	border-radius: 8px !important;
	background: #ffffff !important;
	margin-bottom: 0 !important;
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
	background-color: #f7f7f7 !important;
	border-color: #dddddd !important;
	color: #333333 !important;
	font-size: 13px !important;
	font-weight: 700 !important;
	line-height: 1.35 !important;
	padding: 9px 10px !important;
	vertical-align: middle !important;
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
	border-color: #eeeeee !important;
	color: #333333 !important;
	font-size: 13px !important;
	line-height: 1.45 !important;
	padding: 10px !important;
	vertical-align: middle !important;
}

.cloudns-body-panel input.form-control,
.cloudns-body-panel select.form-control,
.cloudns-body-panel textarea.form-control,
.cloudns-zones-panel input.form-control,
.cloudns-zones-panel select.form-control,
.cloudns-zones-panel textarea.form-control,
.cloudns-import-panel input.form-control,
.cloudns-import-panel select.form-control,
.cloudns-import-panel textarea.form-control,
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
form#recordsForm.recordsForm input.form-control,
form#recordsForm.recordsForm select.form-control,
.newZoneContainer input[type="text"],
.newZoneContainer input.form-control,
.newZoneContainer select.form-control {
	border: 1px solid #cccccc !important;
	border-radius: 5px !important;
	background-color: #ffffff !important;
	color: #333333 !important;
	font-size: 13px !important;
	box-shadow: none !important;
	transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
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
.cloudns-statistics-panel input.form-control:focus,
.cloudns-statistics-panel select.form-control:focus,
.cloudns-dnssec-panel input.form-control:focus,
.cloudns-dnssec-panel select.form-control:focus,
.cloudns-mailforward-panel input.form-control:focus,
.cloudns-mailforward-panel select.form-control:focus,
.cloudns-export-panel input.form-control:focus,
.cloudns-export-panel select.form-control:focus,
.cloudns-free-ssl-panel input.form-control:focus,
.cloudns-free-ssl-panel select.form-control:focus,
.cloudns-zone-transfers-panel input.form-control:focus,
.cloudns-zone-transfers-panel select.form-control:focus,
.cloudns-parked-panel input.form-control:focus,
.cloudns-parked-panel select.form-control:focus,
form#recordsForm.recordsForm input.form-control:focus,
form#recordsForm.recordsForm select.form-control:focus,
.newZoneContainer input[type="text"]:focus,
.newZoneContainer input.form-control:focus,
.newZoneContainer select.form-control:focus {
	border-color: #f7941d !important;
	box-shadow: 0 0 0 2px rgba(247, 148, 29, 0.18) !important;
	outline: none !important;
}

.cloudns-switch-style-button,
.cloudns-domain-switch-button,
.cloudns-add-button,
#bulk-action-execute,
#mass-delete.cloudns-mailforward-delete-button,
.cloudns-zone-transfer-button {
	transition: background-color 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease !important;
}

.cloudns-action-icon,
#table-records .cloudns-action-icon,
#table-forwards .cloudns-action-icon,
#zones-list .cloudns-action-icon {
	transition: border-color 0.15s ease, color 0.15s ease, box-shadow 0.15s ease !important;
}

.notification,
.cloudns-zone-transfer-notice,
.cloudns-zone-transfer-response,
.cloudns-mailforward-mx-panel,
.cloudns-export-help,
.cloudns-updated-empty,
.cloudns-parked-zone-note {
	border: 1px solid #9fb7ff !important;
	border-radius: 6px !important;
	background: #dce6ff !important;
	color: #30446c !important;
	font-size: 13px !important;
	line-height: 1.45 !important;
	padding: 12px 14px !important;
	margin-bottom: 14px !important;
}

.cloudns-zone-transfer-response-error,
.cloudns-free-ssl-response-error,
.cloudns-export-error {
	border-color: #ebccd1 !important;
	background: #f2dede !important;
	color: #a94442 !important;
}

#cloudnsSettingsMenu + .clear + .cloudns-body-panel,
#cloudnsSettingsMenu + .clear + form#recordsForm.recordsForm,
#cloudnsSettingsMenu + .clear + .cloudns-free-ssl-response + .cloudns-free-ssl-panel,
#cloudnsSettingsMenu + .clear + .cloudns-zone-transfer-response + .cloudns-zone-transfers-panel {
	margin-top: 0 !important;
}

.cloudns-statistics-links,
.cloudns-section-tools,
.cloudns-export-actions,
.cloudns-free-ssl-actions,
.cloudns-zone-transfer-add,
.cloudns-updated-actions,
.cloudns-bulk-actions-row,
.cloudns-mailforward-actions-row {
	gap: 8px !important;
}

@media only screen and (max-width: 650px) {
	.cloudns-body-panel,
	.cloudns-zones-panel,
	.cloudns-import-panel,
	.cloudns-soa-panel,
	.cloudns-statistics-panel,
	.cloudns-dnssec-panel,
	.cloudns-mailforward-panel,
	.cloudns-export-panel,
	.cloudns-free-ssl-panel,
	.cloudns-zone-transfers-panel,
	.cloudns-parked-panel,
	.cloudns-updated-panel,
	form#recordsForm.recordsForm:not(.cloudns-record-filter),
	.newZoneContainer {
		padding: 10px !important;
	}
}

.pointer {
cursor: pointer;
}
.newZoneContainer label {
text-align: left;	
}

.breadcrumb {
text-align: left;	
}

.form-control {
text-align: left;
display: inline;
}

.backToZones {
text-align: right;
list-style-type: none;
}

.cloudns-module-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 16px;
	margin-bottom: 10px;
	clear: both;
}

.cloudns-module-title-block {
	display: flex;
	align-items: baseline;
	gap: 10px;
	flex-wrap: wrap;
	min-width: 0;
}

.cloudns-module-title {
	margin: 0;
	font-size: 18px;
	font-weight: 700;
	line-height: 1.3;
}

.cloudns-header-tools {
	display: inline-flex;
	align-items: center;
	justify-content: flex-end;
	gap: 10px;
	flex-wrap: wrap;
	margin-left: auto;
}

.cloudns-global-domain-switcher {
	display: inline-flex;
	align-items: center;
	gap: 8px;
	white-space: nowrap;
}

.cloudns-global-domain-switcher label {
	margin: 0;
	font-size: 12px;
	font-weight: 600;
	color: #444444;
}

.cloudns-global-domain-switcher select.form-control {
	height: 34px !important;
	min-height: 34px !important;
	max-height: 34px !important;
	min-width: 225px;
	width: auto;
	padding: 4px 10px !important;
	font-size: 13px;
	font-weight: 400;
	line-height: normal !important;
	box-sizing: border-box !important;
	vertical-align: middle !important;
}

.cloudns-domain-switch-button,
.cloudns-domain-switch-button:visited {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: center !important;
	min-width: 72px !important;
	height: 34px !important;
	min-height: 34px !important;
	max-height: 34px !important;
	padding: 0 14px !important;
	border: 1px solid #ff6b1a !important;
	border-radius: 5px !important;
	background: #ff6b1a !important;
	background-color: #ff6b1a !important;
	background-image: none !important;
	color: #ffffff !important;
	font-size: 14px !important;
	font-weight: 700 !important;
	line-height: 1 !important;
	text-align: center !important;
	text-decoration: none !important;
	text-transform: none !important;
	text-shadow: none !important;
	box-shadow: none !important;
	white-space: nowrap !important;
	box-sizing: border-box !important;
	vertical-align: middle !important;
}

.cloudns-domain-switch-button:hover,
.cloudns-domain-switch-button:focus {
	background: #f05f12 !important;
	background-color: #f05f12 !important;
	background-image: none !important;
	border-color: #f05f12 !important;
	color: #ffffff !important;
	text-decoration: none !important;
	text-shadow: none !important;
	box-shadow: 0 0 0 2px rgba(255, 107, 26, 0.22) !important;
	outline: none !important;
}

.cloudns-domain-switch-button:active {
	background: #dd520c !important;
	background-color: #dd520c !important;
	background-image: none !important;
	border-color: #dd520c !important;
	color: #ffffff !important;
	box-shadow: 0 0 0 2px rgba(255, 107, 26, 0.28) !important;
}

.cloudns-back-link,
.cloudns-back-link:visited {
	font-size: 12px;
	font-weight: 600;
	color: #555555;
	text-decoration: none;
	white-space: nowrap;
}

.cloudns-back-link:hover,
.cloudns-back-link:focus {
	color: #f7941d;
	text-decoration: underline;
}


.whmcscontainer .moduleoutput form {
text-align: left;	
}
#table-records.dns-records .text-right {
text-align: right;	
}
.text-left {
text-align: left;
}
.table-update-status, .import-table, .import-table tbody label {
width: 100%;
}
#recordsType {
width: auto;
}
#recordsForm label {
width: 100%;
}

ul#cloudnsSettingsMenu {
	clear: both;
	display: flex;
	align-items: center;
	gap: 0;
	flex-wrap: wrap;
	min-height: 40px;
	padding: 0;
	margin: 0 0 6px;
	background: #ffffff;
	border-bottom: 1px solid #dddddd;
	overflow: visible;
}

ul#cloudnsSettingsMenu li {
	position: relative;
	float: none;
	list-style-type: none;
	margin-left: 0;
	flex: 0 0 auto;
}

ul#cloudnsSettingsMenu li a,
ul#cloudnsSettingsMenu li button {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	min-height: 38px;
	padding: 9px 13px;
	border: 0;
	border-bottom: 3px solid transparent;
	border-radius: 0;
	background: transparent;
	color: #333333;
	font-size: 13px;
	font-weight: 600;
	line-height: 1.1;
	text-decoration: none;
	white-space: nowrap;
	transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
}

ul#cloudnsSettingsMenu li a:hover,
ul#cloudnsSettingsMenu li a:focus,
ul#cloudnsSettingsMenu li button:hover,
ul#cloudnsSettingsMenu li button:focus,
ul#cloudnsSettingsMenu li.cloudns-menu-open > a,
ul#cloudnsSettingsMenu li.cloudns-menu-open > button,
ul#cloudnsSettingsMenu li.cloudns-advanced-menu:hover > a,
ul#cloudnsSettingsMenu li.cloudns-advanced-menu:focus-within > a {
	background: #fff7ef;
	border-bottom-color: #f7941d;
	color: #f7941d;
	text-decoration: none;
	box-shadow: none;
	outline: none;
}

ul#cloudnsSettingsMenu li.cloudns-menu-active > a,
ul#cloudnsSettingsMenu li.cloudns-menu-active > button {
	background: #fff7ef;
	border-bottom-color: #ff6b1a;
	color: #d75b0b;
}

ul#cloudnsSettingsMenu li.cloudns-menu-danger > a {
	color: #9b2f2f;
}

ul#cloudnsSettingsMenu li.cloudns-menu-danger > a:hover,
ul#cloudnsSettingsMenu li.cloudns-menu-danger > a:focus {
	background: #fff1f1;
	border-bottom-color: #b94a48;
	color: #9b2f2f;
}

ul#cloudnsSettingsMenu li a:active,
ul#cloudnsSettingsMenu li button:active {
	background: #fff4e8;
	border-bottom-color: #d77b13;
	color: #d77b13;
}

.cloudns-advanced-menu > a:after {
	content: " ▾";
	font-size: 11px;
	line-height: 1;
}

.cloudns-advanced-menu {
	padding-bottom: 8px;
	margin-bottom: -8px;
	z-index: 10000;
}

.cloudns-advanced-menu:after {
	content: "";
	position: absolute;
	left: 0;
	top: 100%;
	width: 100%;
	height: 10px;
}

.cloudns-advanced-dropdown {
	display: none;
	position: absolute;
	top: 100%;
	left: 0;
	z-index: 9999;
	min-width: 220px;
	padding: 6px 0;
	margin: 0;
	border: 1px solid #d6d6d6;
	border-radius: 6px;
	background: #ffffff;
	box-shadow: 0 8px 22px rgba(0, 0, 0, 0.16);
}

.cloudns-advanced-menu:hover .cloudns-advanced-dropdown,
.cloudns-advanced-menu:focus-within .cloudns-advanced-dropdown,
.cloudns-advanced-menu.cloudns-menu-open .cloudns-advanced-dropdown,
.cloudns-advanced-menu.cloudns-menu-hover .cloudns-advanced-dropdown {
	display: block;
}

ul#cloudnsSettingsMenu .cloudns-advanced-dropdown li {
	display: block;
	width: 100%;
}

ul#cloudnsSettingsMenu .cloudns-advanced-dropdown li a,
ul#cloudnsSettingsMenu .cloudns-advanced-dropdown li button {
	display: flex;
	justify-content: flex-start;
	width: 100%;
	min-height: 32px;
	padding: 8px 12px;
	border: 0;
	border-radius: 0;
	background: #ffffff;
	box-shadow: none;
	font-size: 13px;
	font-weight: 600;
	text-align: left;
}

ul#cloudnsSettingsMenu .cloudns-advanced-dropdown li a:hover,
ul#cloudnsSettingsMenu .cloudns-advanced-dropdown li a:focus,
ul#cloudnsSettingsMenu .cloudns-advanced-dropdown li button:hover,
ul#cloudnsSettingsMenu .cloudns-advanced-dropdown li button:focus {
	background: #fff4e8;
	color: #f7941d;
	box-shadow: inset 3px 0 0 #f7941d;
	outline: none;
}

.cloudns-disabled-menu-item {
	cursor: default;
	opacity: 0.72;
}

.pull-left {
text-align: left;
}
.pull-right {
text-align: right;
}
.fleft {
float: left;
}
.fright {
float: right;
}
form#recordsForm {
text-align: left;
}
#table-records .overflow {
overflow: hidden;
position: relative;
width: 32%;
}
#table-records .overflow .overflowDiv {
width: 94%; 
overflow: hidden;
position: absolute;
white-space: nowrap;
}


/* DM main menu border match pass
   Match the main ClouDNS menu container to the white body panel border style. */
ul#cloudnsSettingsMenu {
	border: 1px solid #dddddd !important;
	border-radius: 8px !important;
	background: #ffffff !important;
	box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
	margin: 0 0 10px 0 !important;
	padding: 0 !important;
	overflow: visible !important;
}

ul#cloudnsSettingsMenu > li:first-child > a,
ul#cloudnsSettingsMenu > li:first-child > button {
	border-top-left-radius: 7px !important;
	border-bottom-left-radius: 7px !important;
}

ul#cloudnsSettingsMenu > li:last-child > a,
ul#cloudnsSettingsMenu > li:last-child > button {
	border-top-right-radius: 7px !important;
	border-bottom-right-radius: 7px !important;
}

/* DM dark background/table header hardening pass
   Some WHMCS themes apply dark gradient backgrounds to table header cells via the
   shorthand background property. Force affected module panels back to the shared
   white/gray table style and remove inherited background images. */
.cloudns-soa-panel,
.cloudns-import-panel,
.cloudns-free-ssl-panel,
.cloudns-soa-panel table,
.cloudns-import-panel table,
.cloudns-free-ssl-panel table,
.cloudns-soa-panel thead,
.cloudns-import-panel thead,
.cloudns-free-ssl-panel thead,
.cloudns-soa-panel tbody,
.cloudns-import-panel tbody,
.cloudns-free-ssl-panel tbody,
.cloudns-soa-panel tfoot,
.cloudns-import-panel tfoot,
.cloudns-free-ssl-panel tfoot,
.cloudns-soa-panel tr,
.cloudns-import-panel tr,
.cloudns-free-ssl-panel tr {
	background: #ffffff !important;
	background-color: #ffffff !important;
	background-image: none !important;
	color: #333333 !important;
}

.cloudns-soa-panel .table > thead > tr > th,
.cloudns-soa-panel .table > tbody > tr > th,
.cloudns-soa-panel .table > tfoot > tr > th,
.cloudns-import-panel .table > thead > tr > th,
.cloudns-import-panel .table > tbody > tr > th,
.cloudns-import-panel .table > tfoot > tr > th,
.cloudns-free-ssl-panel .table > thead > tr > th,
.cloudns-free-ssl-panel .table > tbody > tr > th,
.cloudns-free-ssl-panel .table > tfoot > tr > th,
.cloudns-free-ssl-table th {
	background: #f7f7f7 !important;
	background-color: #f7f7f7 !important;
	background-image: none !important;
	border-color: #dddddd !important;
	color: #333333 !important;
	font-size: 13px !important;
	font-weight: 700 !important;
	line-height: 1.35 !important;
	vertical-align: middle !important;
}

.cloudns-soa-panel .table > thead > tr > td,
.cloudns-soa-panel .table > tbody > tr > td,
.cloudns-soa-panel .table > tfoot > tr > td,
.cloudns-import-panel .table > thead > tr > td,
.cloudns-import-panel .table > tbody > tr > td,
.cloudns-import-panel .table > tfoot > tr > td,
.cloudns-free-ssl-panel .table > thead > tr > td,
.cloudns-free-ssl-panel .table > tbody > tr > td,
.cloudns-free-ssl-panel .table > tfoot > tr > td,
.cloudns-free-ssl-table td {
	background: #ffffff !important;
	background-color: #ffffff !important;
	background-image: none !important;
	border-color: #eeeeee !important;
	color: #333333 !important;
	font-size: 13px !important;
	line-height: 1.45 !important;
	vertical-align: middle !important;
}


.cloudns-statistics-panel .table > thead > tr,
.cloudns-statistics-panel .table > tbody > tr,
.cloudns-statistics-panel .table > tfoot > tr,
.cloudns-statistics-panel .table > thead > tr.active,
.cloudns-statistics-panel .table > tbody > tr.active,
.cloudns-statistics-panel .table > tfoot > tr.active {
	background: #ffffff !important;
	background-color: #ffffff !important;
	background-image: none !important;
	color: #333333 !important;
}

.cloudns-statistics-panel .table > thead > tr > th,
.cloudns-statistics-panel .table > tbody > tr > th,
.cloudns-statistics-panel .table > tfoot > tr > th,
.cloudns-statistics-panel .table > thead > tr.active > th,
.cloudns-statistics-panel .table > tbody > tr.active > th,
.cloudns-statistics-panel .table > tfoot > tr.active > th,
.cloudns-statistics-panel .table > thead > tr.active > td,
.cloudns-statistics-panel .table > tbody > tr.active > td,
.cloudns-statistics-panel .table > tfoot > tr.active > td {
	background: #f7f7f7 !important;
	background-color: #f7f7f7 !important;
	background-image: none !important;
	border-color: #dddddd !important;
	color: #333333 !important;
	font-size: 13px !important;
	font-weight: 700 !important;
	line-height: 1.35 !important;
	vertical-align: middle !important;
}

.cloudns-statistics-panel .table > thead > tr > td,
.cloudns-statistics-panel .table > tbody > tr > td,
.cloudns-statistics-panel .table > tfoot > tr > td {
	background: #ffffff !important;
	background-color: #ffffff !important;
	background-image: none !important;
	border-color: #eeeeee !important;
	color: #333333 !important;
	font-size: 13px !important;
	line-height: 1.45 !important;
	vertical-align: middle !important;
}

/* DM Advanced tab flat fix
   Keep DNS Records edge rounding, but prevent the Advanced tab itself from rounding up
   like a standalone pill when it is active/hovered/open. */
ul#cloudnsSettingsMenu > li.cloudns-advanced-menu > a,
ul#cloudnsSettingsMenu > li.cloudns-advanced-menu > button,
ul#cloudnsSettingsMenu > li.cloudns-advanced-menu.cloudns-menu-active > a,
ul#cloudnsSettingsMenu > li.cloudns-advanced-menu.cloudns-menu-active > button,
ul#cloudnsSettingsMenu > li.cloudns-advanced-menu.cloudns-menu-open > a,
ul#cloudnsSettingsMenu > li.cloudns-advanced-menu.cloudns-menu-open > button,
ul#cloudnsSettingsMenu > li.cloudns-advanced-menu:hover > a,
ul#cloudnsSettingsMenu > li.cloudns-advanced-menu:focus-within > a {
	border-radius: 0 !important;
}

{/literal}

/* DM button/link taxonomy pass
   Primary = orange action, Secondary = neutral outline, Danger = destructive. */
a.btn.cloudns-btn-primary,
button.btn.cloudns-btn-primary,
input.btn.cloudns-btn-primary,
.btn.cloudns-btn-primary,
a.btn.cloudns-switch-style-button.cloudns-btn-primary,
button.btn.cloudns-switch-style-button.cloudns-btn-primary,
input.btn.cloudns-switch-style-button.cloudns-btn-primary {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: center !important;
	min-width: 72px !important;
	width: auto !important;
	height: 34px !important;
	min-height: 34px !important;
	max-height: 34px !important;
	padding: 0 14px !important;
	border: 1px solid #ff6b1a !important;
	border-radius: 5px !important;
	background: #ff6b1a !important;
	background-color: #ff6b1a !important;
	background-image: none !important;
	color: #ffffff !important;
	font-size: 13px !important;
	font-weight: 700 !important;
	line-height: 1 !important;
	text-decoration: none !important;
	box-shadow: none !important;
	cursor: pointer !important;
	box-sizing: border-box !important;
}

a.btn.cloudns-btn-primary:hover,
a.btn.cloudns-btn-primary:focus,
button.btn.cloudns-btn-primary:hover,
button.btn.cloudns-btn-primary:focus,
input.btn.cloudns-btn-primary:hover,
input.btn.cloudns-btn-primary:focus,
.btn.cloudns-btn-primary:hover,
.btn.cloudns-btn-primary:focus {
	border-color: #f05f12 !important;
	background: #f05f12 !important;
	background-color: #f05f12 !important;
	background-image: none !important;
	color: #ffffff !important;
	text-decoration: none !important;
	box-shadow: none !important;
	outline: none !important;
}

a.btn.cloudns-btn-primary:active,
button.btn.cloudns-btn-primary:active,
input.btn.cloudns-btn-primary:active,
.btn.cloudns-btn-primary:active {
	border-color: #dd520c !important;
	background: #dd520c !important;
	background-color: #dd520c !important;
	color: #ffffff !important;
}

a.btn.cloudns-btn-secondary,
button.btn.cloudns-btn-secondary,
input.btn.cloudns-btn-secondary,
.btn.cloudns-btn-secondary,
a.btn.cloudns-switch-style-button.cloudns-btn-secondary,
button.btn.cloudns-switch-style-button.cloudns-btn-secondary,
input.btn.cloudns-switch-style-button.cloudns-btn-secondary {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: center !important;
	min-width: 72px !important;
	width: auto !important;
	height: 34px !important;
	min-height: 34px !important;
	max-height: 34px !important;
	padding: 0 14px !important;
	border: 1px solid #d75b0b !important;
	border-radius: 5px !important;
	background: #ffffff !important;
	background-color: #ffffff !important;
	background-image: none !important;
	color: #d75b0b !important;
	font-size: 13px !important;
	font-weight: 700 !important;
	line-height: 1 !important;
	text-decoration: none !important;
	box-shadow: none !important;
	cursor: pointer !important;
	box-sizing: border-box !important;
}

a.btn.cloudns-btn-secondary:hover,
a.btn.cloudns-btn-secondary:focus,
button.btn.cloudns-btn-secondary:hover,
button.btn.cloudns-btn-secondary:focus,
input.btn.cloudns-btn-secondary:hover,
input.btn.cloudns-btn-secondary:focus,
.btn.cloudns-btn-secondary:hover,
.btn.cloudns-btn-secondary:focus {
	border-color: #f7941d !important;
	background: #fff7ef !important;
	background-color: #fff7ef !important;
	background-image: none !important;
	color: #d75b0b !important;
	text-decoration: none !important;
	box-shadow: none !important;
	outline: none !important;
}

a.btn.cloudns-btn-danger,
button.btn.cloudns-btn-danger,
input.btn.cloudns-btn-danger,
.btn.cloudns-btn-danger,
a.btn.btn-danger.cloudns-btn-danger,
button.btn.btn-danger.cloudns-btn-danger,
input.btn.btn-danger.cloudns-btn-danger,
a.btn.cloudns-switch-style-button.cloudns-btn-danger,
button.btn.cloudns-switch-style-button.cloudns-btn-danger,
input.btn.cloudns-switch-style-button.cloudns-btn-danger {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: center !important;
	min-width: 72px !important;
	width: auto !important;
	height: 34px !important;
	min-height: 34px !important;
	max-height: 34px !important;
	padding: 0 14px !important;
	border: 1px solid #b94a48 !important;
	border-radius: 5px !important;
	background: #b94a48 !important;
	background-color: #b94a48 !important;
	background-image: none !important;
	color: #ffffff !important;
	font-size: 13px !important;
	font-weight: 700 !important;
	line-height: 1 !important;
	text-decoration: none !important;
	box-shadow: none !important;
	cursor: pointer !important;
	box-sizing: border-box !important;
}

a.btn.cloudns-btn-danger:hover,
a.btn.cloudns-btn-danger:focus,
button.btn.cloudns-btn-danger:hover,
button.btn.cloudns-btn-danger:focus,
input.btn.cloudns-btn-danger:hover,
input.btn.cloudns-btn-danger:focus,
.btn.cloudns-btn-danger:hover,
.btn.cloudns-btn-danger:focus {
	border-color: #a94442 !important;
	background: #a94442 !important;
	background-color: #a94442 !important;
	background-image: none !important;
	color: #ffffff !important;
	text-decoration: none !important;
	box-shadow: none !important;
	outline: none !important;
}

a.btn.cloudns-btn-primary[disabled],
button.btn.cloudns-btn-primary[disabled],
input.btn.cloudns-btn-primary[disabled],
a.btn.cloudns-btn-secondary[disabled],
button.btn.cloudns-btn-secondary[disabled],
input.btn.cloudns-btn-secondary[disabled],
a.btn.cloudns-btn-danger[disabled],
button.btn.cloudns-btn-danger[disabled],
input.btn.cloudns-btn-danger[disabled],
.btn.cloudns-btn-primary.disabled,
.btn.cloudns-btn-secondary.disabled,
.btn.cloudns-btn-danger.disabled,
.btn.cloudns-btn-primary:disabled,
.btn.cloudns-btn-secondary:disabled,
.btn.cloudns-btn-danger:disabled {
	opacity: 0.55 !important;
	cursor: not-allowed !important;
	box-shadow: none !important;
}

.cloudns-body-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-zones-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-statistics-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-import-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-soa-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-dnssec-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-mailforward-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-export-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-free-ssl-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-zone-transfers-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-parked-panel a:not(.btn):not(.cloudns-action-icon),
.cloudns-updated-panel a:not(.btn):not(.cloudns-action-icon) {
	color: #d75b0b !important;
	text-decoration: underline !important;
	text-underline-offset: 2px !important;
}

.cloudns-body-panel a:not(.btn):not(.cloudns-action-icon):hover,
.cloudns-body-panel a:not(.btn):not(.cloudns-action-icon):focus,
.cloudns-zones-panel a:not(.btn):not(.cloudns-action-icon):hover,
.cloudns-zones-panel a:not(.btn):not(.cloudns-action-icon):focus,
.cloudns-statistics-panel a:not(.btn):not(.cloudns-action-icon):hover,
.cloudns-statistics-panel a:not(.btn):not(.cloudns-action-icon):focus {
	color: #a94708 !important;
	text-decoration: underline !important;
}

.cloudns-action-icon-danger,
.cloudns-action-icon.cloudns-action-icon-danger {
	color: #b94a48 !important;
}

.cloudns-action-icon-danger:hover,
.cloudns-action-icon-danger:focus,
.cloudns-action-icon.cloudns-action-icon-danger:hover,
.cloudns-action-icon.cloudns-action-icon-danger:focus {
	background: #fff1f1 !important;
	border-color: #f0c8c8 !important;
	color: #a94442 !important;
	text-decoration: none !important;
}

ul#cloudnsSettingsMenu .cloudns-advanced-dropdown li.cloudns-advanced-danger a,
#cloudnsMobileSettingsMenu a.cloudns-danger-link {
	color: #9b2f2f !important;
}

ul#cloudnsSettingsMenu .cloudns-advanced-dropdown li.cloudns-advanced-danger a:hover,
ul#cloudnsSettingsMenu .cloudns-advanced-dropdown li.cloudns-advanced-danger a:focus,
#cloudnsMobileSettingsMenu a.cloudns-danger-link:hover,
#cloudnsMobileSettingsMenu a.cloudns-danger-link:focus {
	background: #fff1f1 !important;
	color: #9b2f2f !important;
}


/* DM page shell consistency pass
   Standard page structure: page reference row, menu, then white content panel. */
.cloudns-module-header {
	clear: both !important;
	display: flex !important;
	align-items: center !important;
	justify-content: space-between !important;
	gap: 16px !important;
	margin: 0 0 10px 0 !important;
	padding: 0 !important;
	box-sizing: border-box !important;
}

.cloudns-module-title-block {
	display: flex !important;
	align-items: center !important;
	gap: 10px !important;
	min-height: 34px !important;
	margin: 0 !important;
	padding: 0 !important;
}

.cloudns-module-title {
	margin: 0 !important;
	color: #333333 !important;
	font-size: 18px !important;
	font-weight: 700 !important;
	line-height: 1.25 !important;
}

.cloudns-header-tools {
	display: flex !important;
	align-items: center !important;
	justify-content: flex-end !important;
	gap: 10px !important;
	margin: 0 !important;
	padding: 0 !important;
}

ul#cloudnsSettingsMenu {
	margin: 0 0 10px 0 !important;
}

#cloudnsMobileSettingsMenu {
	margin: 0 0 10px 0 !important;
}

#cloudnsSettingsMenu + .clear,
#cloudnsMobileSettingsMenu + .clear {
	height: 0 !important;
	margin: 0 !important;
	padding: 0 !important;
	line-height: 0 !important;
}

#cloudnsSettingsMenu + .clear + br,
#cloudnsMobileSettingsMenu + .clear + br {
	display: none !important;
}

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
.newZoneContainer,
form#recordsForm.recordsForm:not(.cloudns-record-filter) {
	border: 1px solid #dddddd !important;
	border-radius: 8px !important;
	background: #ffffff !important;
	background-color: #ffffff !important;
	box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
	color: #333333 !important;
	box-sizing: border-box !important;
	margin-top: 0 !important;
	margin-bottom: 14px !important;
	padding: 14px !important;
}

.cloudns-body-panel:first-child,
.cloudns-zones-panel:first-child,
.newZoneContainer:first-child {
	margin-top: 0 !important;
}

.cloudns-body-panel > :first-child,
.cloudns-zones-panel > :first-child,
.cloudns-import-panel > :first-child,
.cloudns-soa-panel > :first-child,
.cloudns-statistics-panel > :first-child,
.cloudns-dnssec-panel > :first-child,
.cloudns-mailforward-panel > :first-child,
.cloudns-export-panel > :first-child,
.cloudns-free-ssl-panel > :first-child,
.cloudns-zone-transfers-panel > :first-child,
.cloudns-parked-panel > :first-child,
.cloudns-updated-panel > :first-child,
.cloudns-form-panel > :first-child,
.newZoneContainer > :first-child {
	margin-top: 0 !important;
}

.cloudns-body-panel > :last-child,
.cloudns-zones-panel > :last-child,
.cloudns-import-panel > :last-child,
.cloudns-soa-panel > :last-child,
.cloudns-statistics-panel > :last-child,
.cloudns-dnssec-panel > :last-child,
.cloudns-mailforward-panel > :last-child,
.cloudns-export-panel > :last-child,
.cloudns-free-ssl-panel > :last-child,
.cloudns-zone-transfers-panel > :last-child,
.cloudns-parked-panel > :last-child,
.cloudns-updated-panel > :last-child,
.cloudns-form-panel > :last-child,
.newZoneContainer > :last-child {
	margin-bottom: 0 !important;
}

.cloudns-zones-heading-row,
.cloudns-record-toolbar,
.cloudns-mailforward-toolbar,
.cloudns-section-tools,
.cloudns-export-actions,
.cloudns-free-ssl-actions,
.cloudns-zone-transfer-add,
.cloudns-updated-actions,
.cloudns-bulk-actions-row,
.cloudns-mailforward-actions-row,
.newZoneButtonsContainer {
	display: flex !important;
	align-items: center !important;
	gap: 8px !important;
	box-sizing: border-box !important;
}

.cloudns-zones-heading-row,
.cloudns-record-toolbar,
.cloudns-mailforward-toolbar,
.cloudns-section-tools {
	justify-content: space-between !important;
	margin: 0 0 12px 0 !important;
}

.cloudns-statistics-links {
	margin: 0 0 12px 0 !important;
}

.notification,
.cloudns-response,
.cloudns-zone-transfer-response,
.cloudns-free-ssl-response,
.cloudns-export-notice,
.cloudns-export-error {
	margin-top: 0 !important;
	margin-bottom: 14px !important;
}

@media only screen and (max-width: 870px) {
	.cloudns-module-header {
		align-items: flex-start !important;
		flex-direction: column !important;
	}
	.cloudns-header-tools,
	.cloudns-global-domain-switcher {
		width: 100% !important;
		justify-content: flex-start !important;
	}
}

@media only screen and (max-width: 650px) {
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
	.newZoneContainer,
	form#recordsForm.recordsForm:not(.cloudns-record-filter) {
		padding: 10px !important;
	}
	.cloudns-zones-heading-row,
	.cloudns-record-toolbar,
	.cloudns-mailforward-toolbar,
	.cloudns-section-tools,
	.cloudns-export-actions,
	.cloudns-free-ssl-actions,
	.cloudns-zone-transfer-add,
	.cloudns-updated-actions,
	.cloudns-bulk-actions-row,
	.cloudns-mailforward-actions-row,
	.newZoneButtonsContainer {
		align-items: stretch !important;
		flex-direction: column !important;
	}
}



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



/* DM menu/navigation polish pass
   Keep the main menu text-only and flat, align desktop/mobile states,
   and keep Advanced grouped/clean with Deactivate Zone clearly dangerous. */
ul#cloudnsSettingsMenu {
	display: flex !important;
	align-items: stretch !important;
	gap: 0 !important;
	flex-wrap: wrap !important;
	min-height: 40px !important;
	padding: 0 !important;
	margin: 0 0 10px 0 !important;
	border: 1px solid #dddddd !important;
	border-radius: 8px !important;
	background: #ffffff !important;
	background-color: #ffffff !important;
	background-image: none !important;
	box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
	overflow: visible !important;
}

ul#cloudnsSettingsMenu > li {
	display: flex !important;
	align-items: stretch !important;
	margin: 0 !important;
	padding: 0 !important;
	list-style: none !important;
}

ul#cloudnsSettingsMenu > li > a,
ul#cloudnsSettingsMenu > li > button {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: center !important;
	min-height: 40px !important;
	padding: 0 14px !important;
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

/* DomainMonger Patch 487: Add Zone is a global zone action, aligned to the far-right edge of the ClouDNS menu below Switch. */
ul#cloudnsSettingsMenu > li.cloudns-menu-add-zone {
	margin-left: auto !important;
}

/* DomainMonger Patch 490: show zone count immediately to the left of the global Add Zone action. */
ul#cloudnsSettingsMenu > li.cloudns-menu-zone-count {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: center !important;
	min-height: 40px !important;
	padding: 0 12px !important;
	margin-left: auto !important;
	border-left: 1px solid #eeeeee !important;
	background: #ffffff !important;
	background-color: #ffffff !important;
	color: #333333 !important;
	font-size: 13px !important;
	font-weight: 700 !important;
	line-height: 1 !important;
	white-space: nowrap !important;
	box-sizing: border-box !important;
}

ul#cloudnsSettingsMenu > li.cloudns-menu-zone-count + li.cloudns-menu-add-zone {
	margin-left: 0 !important;
}

ul#cloudnsSettingsMenu > li.cloudns-menu-add-zone > a,
ul#cloudnsSettingsMenu > li.cloudns-menu-add-zone > a:visited {
	background: #f58220 !important;
	background-color: #f58220 !important;
	border-bottom-color: #d8741f !important;
	color: #ffffff !important;
}

ul#cloudnsSettingsMenu > li.cloudns-menu-add-zone > a:hover,
ul#cloudnsSettingsMenu > li.cloudns-menu-add-zone > a:focus {
	background: #d8741f !important;
	background-color: #d8741f !important;
	border-bottom-color: #d8741f !important;
	color: #ffffff !important;
}

@media only screen and (max-width: 870px) {
	ul#cloudnsSettingsMenu > li.cloudns-menu-zone-count,
	ul#cloudnsSettingsMenu > li.cloudns-menu-add-zone {
		margin-left: 0 !important;
	}
}

ul#cloudnsSettingsMenu > li:first-child > a,
ul#cloudnsSettingsMenu > li:first-child > button {
	border-top-left-radius: 7px !important;
	border-bottom-left-radius: 7px !important;
}

ul#cloudnsSettingsMenu > li:last-child > a,
ul#cloudnsSettingsMenu > li:last-child > button {
	border-top-right-radius: 7px !important;
	border-bottom-right-radius: 7px !important;
}

ul#cloudnsSettingsMenu > li > a:hover,
ul#cloudnsSettingsMenu > li > a:focus,
ul#cloudnsSettingsMenu > li > button:hover,
ul#cloudnsSettingsMenu > li > button:focus,
ul#cloudnsSettingsMenu > li.cloudns-menu-open > a,
ul#cloudnsSettingsMenu > li.cloudns-menu-open > button,
ul#cloudnsSettingsMenu > li.cloudns-advanced-menu:hover > a,
ul#cloudnsSettingsMenu > li.cloudns-advanced-menu:focus-within > a {
	background: #fff7ef !important;
	background-color: #fff7ef !important;
	background-image: none !important;
	border-bottom-color: #f7941d !important;
	color: #d75b0b !important;
	text-decoration: none !important;
	outline: none !important;
	box-shadow: none !important;
}

ul#cloudnsSettingsMenu > li.cloudns-menu-active > a,
ul#cloudnsSettingsMenu > li.cloudns-menu-active > button {
	background: #fff7ef !important;
	background-color: #fff7ef !important;
	background-image: none !important;
	border-bottom-color: #ff6b1a !important;
	color: #d75b0b !important;
}

ul#cloudnsSettingsMenu > li.cloudns-advanced-menu > a,
ul#cloudnsSettingsMenu > li.cloudns-advanced-menu > button,
ul#cloudnsSettingsMenu > li.cloudns-advanced-menu.cloudns-menu-active > a,
ul#cloudnsSettingsMenu > li.cloudns-advanced-menu.cloudns-menu-active > button,
ul#cloudnsSettingsMenu > li.cloudns-advanced-menu.cloudns-menu-open > a,
ul#cloudnsSettingsMenu > li.cloudns-advanced-menu.cloudns-menu-open > button,
ul#cloudnsSettingsMenu > li.cloudns-advanced-menu:hover > a,
ul#cloudnsSettingsMenu > li.cloudns-advanced-menu:focus-within > a {
	border-radius: 0 !important;
}

.cloudns-advanced-menu > a:after {
	content: " ▾" !important;
	font-size: 11px !important;
	line-height: 1 !important;
	margin-left: 2px !important;
}

.cloudns-advanced-dropdown {
	top: calc(100% + 0px) !important;
	min-width: 230px !important;
	padding: 6px 0 !important;
	margin: 0 !important;
	border: 1px solid #d6d6d6 !important;
	border-radius: 8px !important;
	background: #ffffff !important;
	background-color: #ffffff !important;
	background-image: none !important;
	box-shadow: 0 8px 22px rgba(0, 0, 0, 0.16) !important;
	overflow: hidden !important;
}

ul#cloudnsSettingsMenu .cloudns-advanced-dropdown li {
	display: block !important;
	margin: 0 !important;
	padding: 0 !important;
	list-style: none !important;
}

ul#cloudnsSettingsMenu .cloudns-advanced-dropdown li a {
	display: flex !important;
	align-items: center !important;
	justify-content: flex-start !important;
	width: 100% !important;
	min-height: 34px !important;
	padding: 8px 13px !important;
	border: 0 !important;
	border-radius: 0 !important;
	background: #ffffff !important;
	background-color: #ffffff !important;
	background-image: none !important;
	color: #333333 !important;
	font-size: 13px !important;
	font-weight: 600 !important;
	line-height: 1.2 !important;
	text-align: left !important;
	text-decoration: none !important;
	box-shadow: none !important;
	box-sizing: border-box !important;
}

ul#cloudnsSettingsMenu .cloudns-advanced-dropdown li a:hover,
ul#cloudnsSettingsMenu .cloudns-advanced-dropdown li a:focus,
ul#cloudnsSettingsMenu .cloudns-advanced-dropdown li.cloudns-advanced-active a {
	background: #fff7ef !important;
	background-color: #fff7ef !important;
	background-image: none !important;
	color: #d75b0b !important;
	text-decoration: none !important;
	outline: none !important;
	box-shadow: inset 3px 0 0 #ff6b1a !important;
}

ul#cloudnsSettingsMenu .cloudns-advanced-dropdown li.cloudns-advanced-group-divider,
ul#cloudnsSettingsMenu .cloudns-advanced-dropdown li.cloudns-advanced-divider {
	height: 1px !important;
	min-height: 1px !important;
	margin: 6px 0 !important;
	padding: 0 !important;
	background: #e5e5e5 !important;
	background-color: #e5e5e5 !important;
}

ul#cloudnsSettingsMenu .cloudns-advanced-dropdown li.cloudns-advanced-danger a {
	color: #9b2f2f !important;
	font-weight: 700 !important;
}

ul#cloudnsSettingsMenu .cloudns-advanced-dropdown li.cloudns-advanced-danger a:hover,
ul#cloudnsSettingsMenu .cloudns-advanced-dropdown li.cloudns-advanced-danger a:focus {
	background: #fff1f1 !important;
	background-color: #fff1f1 !important;
	color: #9b2f2f !important;
	box-shadow: inset 3px 0 0 #b94a48 !important;
}

/* Mobile Settings Menu: same state language as desktop. */
#cloudnsMobileSettingsMenu > a.dropdown-toggle {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: center !important;
	min-height: 38px !important;
	padding: 0 14px !important;
	border: 1px solid #dddddd !important;
	border-radius: 8px !important;
	background: #ffffff !important;
	background-color: #ffffff !important;
	background-image: none !important;
	color: #333333 !important;
	font-size: 13px !important;
	font-weight: 700 !important;
	line-height: 1 !important;
	text-decoration: none !important;
	box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
}

#cloudnsMobileSettingsMenu > a.dropdown-toggle:hover,
#cloudnsMobileSettingsMenu > a.dropdown-toggle:focus {
	border-color: #f7941d !important;
	background: #fff7ef !important;
	background-color: #fff7ef !important;
	color: #d75b0b !important;
	text-decoration: none !important;
	outline: none !important;
}

#cloudnsMobileSettingsMenu .dropdown-menu {
	padding: 6px 0 !important;
	border: 1px solid #d6d6d6 !important;
	border-radius: 8px !important;
	background: #ffffff !important;
	background-color: #ffffff !important;
	background-image: none !important;
	box-shadow: 0 8px 22px rgba(0, 0, 0, 0.16) !important;
	overflow: hidden !important;
}

#cloudnsMobileSettingsMenu .dropdown-menu > li > a {
	display: flex !important;
	align-items: center !important;
	min-height: 34px !important;
	padding: 8px 13px !important;
	color: #333333 !important;
	font-size: 13px !important;
	font-weight: 600 !important;
	text-decoration: none !important;
	background-image: none !important;
}

#cloudnsMobileSettingsMenu .dropdown-menu > li > a:hover,
#cloudnsMobileSettingsMenu .dropdown-menu > li > a:focus,
#cloudnsMobileSettingsMenu .dropdown-menu > li.cloudns-mobile-menu-active > a {
	background: #fff7ef !important;
	background-color: #fff7ef !important;
	background-image: none !important;
	color: #d75b0b !important;
	text-decoration: none !important;
	box-shadow: inset 3px 0 0 #ff6b1a !important;
	outline: none !important;
}

#cloudnsMobileSettingsMenu .dropdown-header {
	padding: 8px 13px 6px !important;
	color: #666666 !important;
	font-size: 11px !important;
	font-weight: 700 !important;
	letter-spacing: 0.04em !important;
	text-transform: uppercase !important;
}

#cloudnsMobileSettingsMenu .divider {
	height: 1px !important;
	margin: 6px 0 !important;
	background-color: #e5e5e5 !important;
}

#cloudnsMobileSettingsMenu a.cloudns-danger-link {
	color: #9b2f2f !important;
	font-weight: 700 !important;
}

#cloudnsMobileSettingsMenu a.cloudns-danger-link:hover,
#cloudnsMobileSettingsMenu a.cloudns-danger-link:focus {
	background: #fff1f1 !important;
	background-color: #fff1f1 !important;
	color: #9b2f2f !important;
	box-shadow: inset 3px 0 0 #b94a48 !important;
}

@media only screen and (max-width: 870px) {
	ul#cloudnsSettingsMenu {
		display: none !important;
	}

	#cloudnsMobileSettingsMenu {
		display: block !important;
		margin: 0 0 10px 0 !important;
	}
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



/* DM Switch Domain label removal
   Keep the Switch Domain label available to screen readers but hidden visually. */
.cloudns-global-domain-switcher label.cloudns-visually-hidden {
	position: absolute !important;
	width: 1px !important;
	height: 1px !important;
	padding: 0 !important;
	margin: -1px !important;
	overflow: hidden !important;
	clip: rect(0, 0, 0, 0) !important;
	white-space: nowrap !important;
	border: 0 !important;
}

</style>
<!-- DomainMonger Patch 57 ClouDNS visual CSS loader -->
<link rel="stylesheet" href="{$WEB_ROOT}/modules/servers/cloudns/templates/cloudns-v57-site-align.css?v=1362">
<!-- DomainMonger Patch 64 ClouDNS global late visual override loader -->
<script type="text/javascript" id="dm-cloudns-header-settings-css-loader-1612">
(function () {
    var cssHref = '{$WEB_ROOT}/modules/servers/cloudns/templates/cloudns-v65-late-override.css?v=1362';
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




<script type="text/javascript" id="dm-cloudns-header-settings-script-1612">
{literal}
function toggleOptions (element) {
	$('#' + element).toggle();
}

$(document).ready(function () {
	var cloudnsAdvancedCloseTimer = null;

	var cloudnsOpenAdvancedMenu = function (menu) {
		clearTimeout(cloudnsAdvancedCloseTimer);
		$(menu).addClass('cloudns-menu-hover');
	};

	var cloudnsQueueAdvancedClose = function (menu) {
		clearTimeout(cloudnsAdvancedCloseTimer);
		cloudnsAdvancedCloseTimer = setTimeout(function () {
			$(menu).removeClass('cloudns-menu-hover');
		}, 350);
	};

	$(document).on('mouseenter', '.cloudns-advanced-menu', function () {
		cloudnsOpenAdvancedMenu(this);
	});

	$(document).on('mouseleave', '.cloudns-advanced-menu', function () {
		cloudnsQueueAdvancedClose(this);
	});

	$(document).on('click', '#cloudnsAdvancedToggle', function (event) {
		event.preventDefault();
		event.stopPropagation();
		clearTimeout(cloudnsAdvancedCloseTimer);
		$(this).parent('li').toggleClass('cloudns-menu-open');
	});
	$(document).on('click', function () {
		$('#cloudnsAdvancedToggle').parent('li').removeClass('cloudns-menu-open cloudns-menu-hover');
	});
	$(document).on('click', '.cloudns-advanced-dropdown', function (event) {
		event.stopPropagation();
	});

	$(document)
		.off('click.dmCloudnsHeader1612', '#cloudns-global-domain-switcher-button')
		.on('click.dmCloudnsHeader1612', '#cloudns-global-domain-switcher-button', function () {
			var selectedZone = $('#cloudns-global-domain-switcher-select').val();
			if (!selectedZone) {
				return;
			}

			var serviceId = $(this).data('serviceid');
			var action = $(this).data('action') || 'zone-settings';
			var date = $(this).data('date') || '';

			var url = 'clientarea.php?action=productdetails&id=' + encodeURIComponent(serviceId) + '&customAction=' + encodeURIComponent(action) + '&zone=' + encodeURIComponent(selectedZone);
			if (action === 'statistics' && date) {
				url += '&date=' + encodeURIComponent(date);
			}

			var softNavigator = window.dmCloudnsSoftNavigate1612 || window.dmCloudnsSoftNavigate1611;
			if (softNavigator && softNavigator.canHandle(url)) {
				softNavigator.navigate(url);
				return;
			}
			window.location.href = url;
		});
});
{/literal}
</script>
{*
{assign var="menuSeparator" value="&nbsp;&nbsp;"}
{if $theme eq 'five' || $theme eq 'default'}
	{assign var="menuSeparator" value=" /"}
{/if}
*}
{assign var="menuSeparator" value=" /"}
{if $theme eq 'six'}
	{assign var="menuSeparator" value=""}
{/if}

{if isset($cloudnsZoneToolsGlobalPage) && $cloudnsZoneToolsGlobalPage}
<div class="cloudns-module-header cloudns-manage-domain-header cloudns-zone-tools-global-header">
	<div class="cloudns-module-title-block">
		<span class="cloudns-module-eyebrow">DNSPlus</span>
		<strong class="cloudns-module-domain">{if isset($cloudnsZoneToolsHeaderTitle) && $cloudnsZoneToolsHeaderTitle != ''}{$cloudnsZoneToolsHeaderTitle|@htmlspecialchars}{else}Zone Management{/if}</strong>
	</div>
</div>
{else}
<div class="cloudns-module-header cloudns-manage-domain-header">
	<div class="cloudns-module-title-block">
		<span class="cloudns-module-eyebrow">Manage Domain</span>
		<strong class="cloudns-module-domain">{if isset($zone) && $zone != ''}{$zone|@htmlspecialchars}{else}{$pagetitle}{/if}</strong>
	</div>
	<div class="cloudns-header-tools">
		{if isset($domainSwitchZones) && is_array($domainSwitchZones) && count($domainSwitchZones) gt 1}
			<div class="cloudns-global-domain-switcher">
				<select id="cloudns-global-domain-switcher-select" class="form-control" aria-label="Choose domain">
					{foreach from=$domainSwitchZones item=switchZone}
						<option value="{$switchZone.name|@htmlspecialchars}"{if $switchZone.name == $zone} selected="selected"{/if}>{$switchZone.ascii|@htmlspecialchars}</option>
					{/foreach}
				</select>
				<button type="button" id="cloudns-global-domain-switcher-button" class="btn cloudns-domain-switch-button cloudns-switch-style-button cloudns-btn-primary" data-serviceid="{$serviceid|@htmlspecialchars}" data-action="{$domainSwitchAction|@htmlspecialchars}"{if $domainSwitchAction == 'statistics' && isset($domainSwitchDate) && $domainSwitchDate != ''} data-date="{$domainSwitchDate|@htmlspecialchars}"{/if}>Switch</button>
			</div>
		{/if}
	</div>
</div>
{/if}
<ul class="breadcrumb" id="cloudnsSettingsMenu"> 
	<li{if !isset($cloudAction) || $cloudAction == 'zone-settings'} class="cloudns-menu-active"{/if}><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=zone-settings&zone={$zone}">DNS Records</a></li>
	<li{if isset($cloudAction) && $cloudAction == 'mail-forwarding'} class="cloudns-menu-active"{/if}><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=mail-forwarding&zone={$zone}">Mail Forwards</a></li>
	<li{if isset($cloudAction) && $cloudAction == 'statistics'} class="cloudns-menu-active"{/if}><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=statistics&zone={$zone}&date=last-30-days">Statistics</a></li>
	<li{if isset($cloudAction) && ($cloudAction == 'update-status' || $cloudAction == 'update')} class="cloudns-menu-active"{/if}><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=update-status&zone={$zone}">Status</a></li>
	<li class="cloudns-advanced-menu{if isset($cloudAction) && ($cloudAction == 'soa-settings' || $cloudAction == 'dnssec' || $cloudAction == 'dnssec-show' || $cloudAction == 'dnssec-settings' || $cloudAction == 'dnssec-waiting' || $cloudAction == 'dnssec-activate' || $cloudAction == 'dnssec-deactivate' || $cloudAction == 'free-ssl' || $cloudAction == 'copy-zone' || $cloudAction == 'zone-transfers' || $cloudAction == 'import' || $cloudAction == 'export-zone-file' || $cloudAction == 'add-new-zone-bulk-delete')} cloudns-menu-active{/if}">
		<a href="#" id="cloudnsAdvancedToggle" aria-haspopup="true" aria-expanded="false">Advanced</a>
		<ul class="cloudns-advanced-dropdown" aria-label="Advanced menu">
			<li{if isset($cloudAction) && $cloudAction == 'soa-settings'} class="cloudns-advanced-active"{/if}><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=soa-settings&zone={$zone}">SOA</a></li>
			<li{if isset($cloudAction) && ($cloudAction == 'dnssec' || $cloudAction == 'dnssec-show' || $cloudAction == 'dnssec-settings' || $cloudAction == 'dnssec-waiting' || $cloudAction == 'dnssec-activate' || $cloudAction == 'dnssec-deactivate')} class="cloudns-advanced-active"{/if}><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=dnssec-show&zone={$zone}" data-dm-dnssec-target="dnssec-show">DNSSEC</a></li>
			<li class="cloudns-advanced-group-divider" role="separator"></li>
			<li{if isset($cloudAction) && $cloudAction == 'free-ssl'} class="cloudns-advanced-active"{/if}><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=free-ssl&zone={$zone}">SSL</a></li>
			<li{if isset($cloudAction) && $cloudAction == 'zone-transfers'} class="cloudns-advanced-active"{/if}><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=zone-transfers&zone={$zone}">Zone Transfers</a></li>
			<li class="cloudns-advanced-group-divider" role="separator"></li>
			<li{if isset($cloudAction) && $cloudAction == 'import'} class="cloudns-advanced-active"{/if}><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=import&zone={$zone}">Import Zone File</a></li>
			<li{if isset($cloudAction) && $cloudAction == 'export-zone-file'} class="cloudns-advanced-active"{/if}><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=export-zone-file&zone={$zone}">Export Zone File</a></li>
			<li{if isset($cloudAction) && $cloudAction == 'copy-zone'} class="cloudns-advanced-active"{/if}><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=copy-zone&zone={$zone}">Copy Zone</a></li>
			{if $registeredDomains != 'on'}
				<li class="cloudns-advanced-group-divider" role="separator"></li>
				<li class="cloudns-advanced-danger{if isset($cloudAction) && $cloudAction == 'add-new-zone-bulk-delete'} cloudns-advanced-active{/if}"><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=add-new-zone-bulk-delete" title="Delete one or more DNS zones">Delete Zones</a></li>
			{/if}
			<li class="cloudns-advanced-group-divider" role="separator"></li>
			<li class="cloudns-advanced-danger"><a href="clientarea.php?action=cancel&id={$serviceid}">Request Cancelation</a></li>
		</ul>
	</li>
	{if $registeredDomains != 'on'}
		<li class="cloudns-menu-zone-count" aria-label="Zones count">Zones: {if isset($zonesCount)}{$zonesCount}{elseif isset($domainSwitchZones) && is_array($domainSwitchZones)}{$domainSwitchZones|@count}{else}0{/if}{if isset($zonesLimit) && $zonesLimit != '' && $zonesLimit != '-1'}/{$zonesLimit}{/if}</li>
		<li class="cloudns-menu-add-zone"><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=add-new-zone">+ Add Zone</a></li>
	{/if}

</ul>
<div class="dropdown" id="cloudnsMobileSettingsMenu">
	<a href="#" class="dropdown-toggle" data-toggle="dropdown">Settings Menu<b class="caret"></b></a>
    <ul class="dropdown-menu">
	    <li{if !isset($cloudAction) || $cloudAction == 'zone-settings'} class="cloudns-mobile-menu-active"{/if}><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=zone-settings&zone={$zone}">DNS Records</a></li>
	    {if $registeredDomains != 'on'}<li class="dropdown-header">Zones: {if isset($zonesCount)}{$zonesCount}{elseif isset($domainSwitchZones) && is_array($domainSwitchZones)}{$domainSwitchZones|@count}{else}0{/if}{if isset($zonesLimit) && $zonesLimit != '' && $zonesLimit != '-1'}/{$zonesLimit}{/if}</li><li><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=add-new-zone">+ Add Zone</a></li>{/if}
	    <li{if isset($cloudAction) && $cloudAction == 'mail-forwarding'} class="cloudns-mobile-menu-active"{/if}><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=mail-forwarding&zone={$zone}">Mail Forwards</a></li>
	    <li{if isset($cloudAction) && $cloudAction == 'statistics'} class="cloudns-mobile-menu-active"{/if}><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=statistics&zone={$zone}&date=last-30-days">Statistics</a></li>
	    <li{if isset($cloudAction) && ($cloudAction == 'update-status' || $cloudAction == 'update')} class="cloudns-mobile-menu-active"{/if}><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=update-status&zone={$zone}">Status</a></li>
	    <li class="divider"></li>
	    <li class="dropdown-header">Advanced</li>
	    <li{if isset($cloudAction) && $cloudAction == 'soa-settings'} class="cloudns-mobile-menu-active"{/if}><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=soa-settings&zone={$zone}">SOA</a></li>
	    <li{if isset($cloudAction) && ($cloudAction == 'dnssec' || $cloudAction == 'dnssec-show' || $cloudAction == 'dnssec-settings' || $cloudAction == 'dnssec-waiting' || $cloudAction == 'dnssec-activate' || $cloudAction == 'dnssec-deactivate')} class="cloudns-mobile-menu-active"{/if}><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=dnssec-show&zone={$zone}" data-dm-dnssec-target="dnssec-show">DNSSEC</a></li>
	    <li class="divider"></li>
	    <li{if isset($cloudAction) && $cloudAction == 'free-ssl'} class="cloudns-mobile-menu-active"{/if}><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=free-ssl&zone={$zone}">SSL</a></li>
	    <li{if isset($cloudAction) && $cloudAction == 'zone-transfers'} class="cloudns-mobile-menu-active"{/if}><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=zone-transfers&zone={$zone}">Zone Transfers</a></li>
	    <li class="divider"></li>
	    <li{if isset($cloudAction) && $cloudAction == 'import'} class="cloudns-mobile-menu-active"{/if}><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=import&zone={$zone}">Import Zone File</a></li>
	    <li{if isset($cloudAction) && $cloudAction == 'export-zone-file'} class="cloudns-mobile-menu-active"{/if}><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=export-zone-file&zone={$zone}">Export Zone File</a></li>
    <li{if isset($cloudAction) && $cloudAction == 'copy-zone'} class="cloudns-mobile-menu-active"{/if}><a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=copy-zone&zone={$zone}">Copy Zone</a></li>
{if $registeredDomains != 'on'}
	    <li class="divider"></li>
	    <li{if isset($cloudAction) && $cloudAction == 'add-new-zone-bulk-delete'} class="cloudns-mobile-menu-active"{/if}><a class="cloudns-danger-link" href="clientarea.php?action=productdetails&id={$serviceid}&customAction=add-new-zone-bulk-delete" title="Delete one or more DNS zones">Delete Zones</a></li>{/if}
	    <li class="divider"></li>
	    <li><a class="cloudns-danger-link" href="clientarea.php?action=cancel&id={$serviceid}">Request Cancelation</a></li>
    </ul>
</div>
<div class="clear"></div>
{if (!isset($cloudAction) || ($cloudAction != 'parked-templates' && $cloudAction != 'copy-zone')) && (isset($response.status) && ($response.status=='error' || $response.status=='info'))}
<div class="notification">{$response.description}</div><br />
{/if}
