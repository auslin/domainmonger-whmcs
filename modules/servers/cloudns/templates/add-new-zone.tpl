
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

.newZoneBox {

{/literal}{if $version gte '6' && $theme eq 'six'}{literal}height: 90px; width: 32.2%;{/literal}{else}{literal}height: 30px; width: 24.4%;{/literal}{/if}
{literal}
border: 1px solid #dddddd;
padding: 20px;
float: left;
margin: 0 14px 10px 0;
cursor: pointer;
vertical-align: middle;
text-align: center;
}
.newZoneBox:hover {
text-decoration: underline;
}
.noMargin {
margin-right: 0;
}
.clear {
clear: both;
}
.zoneType {
display: none;
}
.newSlaveZoneAdd tr td {
width: 42%;
}
.newSlaveZoneAdd tr td input {
width: 93%;
}
.newSlaveZoneAdd tr td input.btn {
width: auto;
}
form.recordsForm select {
width: auto;
}
form.recordsForm input.form-control {
width: 67%;
}
form.recordsForm input.slaveZone {
width: 78%;
}
.newZoneContainer label {
text-align: left;	
}
.zoneType {
text-align: left;	
}
#masterZone span {
margin-top: 3px;	
}
.zoneType .btn {
margin-left: 5px;	
}
.newSlaveZoneAdd input {
margin-bottom: 0;	
}
.breadcrumb, .form-control {
text-align: left;	
}
.backToZones {
text-align: right;
list-style-type: none;
{/literal}{if $theme eq 'six'}{literal}margin: 10px 0 0 0;{/literal}{/if}{literal}
}
.nsServer {
margin-right: 15px;
}
.newZoneContainer .servers-list {
{/literal}{if $version gte '6' && $theme eq 'six'}{literal}margin-top: 10px;{/literal}{else}{literal}margin-top: 3px;{/literal}{/if}{literal}
}
{/literal}
{literal}
.chooseZoneType {
width: auto;
}

/* DM deep UI consistency pass: add zone */
.newZoneButtonsContainer {
	display: flex;
	align-items: center;
	gap: 8px;
	flex-wrap: wrap;
	margin: 0 0 14px 0;
}

.newZoneButtonsContainer .pull-left {
	float: none !important;
}

.chooseZoneType.form-control {
	height: 34px !important;
	min-height: 34px !important;
	border-radius: 5px !important;
	border: 1px solid #cccccc !important;
	font-size: 13px !important;
}

.newZoneContainer {
	border: 1px solid #dddddd;
	border-radius: 8px;
	background: #ffffff;
	box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
	padding: 14px;
	margin: 0 0 14px 0;
	color: #333333;
	font-size: 13px;
	line-height: 1.45;
}

.newZoneContainer h4 {
	margin-top: 0;
	font-size: 18px;
	font-weight: 700;
	color: #333333;
}

.newZoneContainer input[type="text"],
.newZoneContainer input.form-control,
.newZoneContainer select.form-control {
	height: 34px !important;
	min-height: 34px !important;
	border: 1px solid #cccccc !important;
	border-radius: 5px !important;
	background: #ffffff !important;
	color: #333333 !important;
	font-size: 13px !important;
	box-shadow: none !important;
	box-sizing: border-box !important;
}

.newZoneContainer .notification {
	border: 1px solid #9fb7ff !important;
	border-radius: 6px !important;
	background: #dce6ff !important;
	color: #30446c !important;
	padding: 12px 14px !important;
}

/* DM final visual consistency pass: standalone Add Zone page */
.cloudns-add-zone-overview {
	margin-bottom: 14px !important;
}

.cloudns-add-zone-overview .cloudns-module-title {
	float: none !important;
	margin: 0 0 12px 0 !important;
	font-size: 18px !important;
	font-weight: 700 !important;
	line-height: 1.25 !important;
	color: #333333 !important;
}

.cloudns-add-zone-overview .newZoneButtonsContainer {
	margin: 0 !important;
}

.newZoneContainer table,
.newZoneContainer .newSlaveZoneAdd {
	width: 100% !important;
	border-collapse: collapse !important;
}

.newZoneContainer table td,
.newZoneContainer .newSlaveZoneAdd td {
	padding: 6px 8px !important;
	vertical-align: middle !important;
	color: #333333 !important;
	font-size: 13px !important;
}

.newZoneContainer label {
	font-weight: 600 !important;
	color: #333333 !important;
}

.newZoneContainer .btn.cloudns-switch-style-button,
.newZoneContainer input.btn.cloudns-switch-style-button {
	margin-left: 8px !important;
}

.newZoneContainer .notification {
	margin-top: 14px !important;
	margin-bottom: 0 !important;
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

/* Patch 1596: Add Zone is the active item in the shared DNSPlus menu. */
.cloudns-add-zone-shared-shell:not(.cloudns-delete-zone-shared-shell) ul#cloudnsSettingsMenu > li.cloudns-menu-add-zone > a {
	background: #f58220 !important;
	background-color: #f58220 !important;
	border-color: #f58220 !important;
	color: #ffffff !important;
}

.cloudns-add-zone-shared-shell:not(.cloudns-delete-zone-shared-shell) ul#cloudnsSettingsMenu > li.cloudns-menu-add-zone > a:hover,
.cloudns-add-zone-shared-shell:not(.cloudns-delete-zone-shared-shell) ul#cloudnsSettingsMenu > li.cloudns-menu-add-zone > a:focus {
	background: #214e7a !important;
	background-color: #214e7a !important;
	border-color: #214e7a !important;
	color: #ffffff !important;
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


/* DomainMonger Patch 1591: automatic nameservers, bulk tools, and server-rendered tool panels. */
.cloudns-auto-ns-note {
	margin: 0 0 14px 0 !important;
	padding: 10px 12px !important;
	border: 1px solid #ead9a1 !important;
	border-radius: 6px !important;
	background: #fff8dc !important;
	color: #4f4530 !important;
	font-size: 13px !important;
	line-height: 1.45 !important;
}

.cloudns-bulk-zone-help {
	margin: 0 0 12px 0 !important;
	color: #555555 !important;
}

.cloudns-bulk-zone-rows {
	display: flex !important;
	flex-direction: column !important;
	gap: 8px !important;
	margin: 0 0 10px 0 !important;
}

.cloudns-bulk-zone-row {
	display: grid !important;
	grid-template-columns: 34px minmax(180px, 1fr) auto !important;
	align-items: center !important;
	gap: 8px !important;
}

.cloudns-bulk-zone-number {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: center !important;
	width: 30px !important;
	height: 34px !important;
	border: 1px solid #d8d8d8 !important;
	border-radius: 5px !important;
	background: #f7f7f7 !important;
	color: #555555 !important;
	font-weight: 700 !important;
}

.cloudns-bulk-zone-row .cloudns-bulk-zone-input {
	width: 100% !important;
	margin: 0 !important;
}

.cloudns-bulk-zone-toolbar,
.cloudns-bulk-zone-footer,
.cloudns-bulk-delete-tools {
	display: flex !important;
	align-items: center !important;
	gap: 8px !important;
	flex-wrap: wrap !important;
}

.cloudns-bulk-zone-toolbar {
	margin: 0 0 14px 42px !important;
}

.cloudns-bulk-zone-footer {
	justify-content: flex-end !important;
	margin-top: 14px !important;
}

.cloudns-bulk-delete-warning {
	margin: 0 0 14px 0 !important;
	padding: 10px 12px !important;
	border: 1px solid #e0c0bf !important;
	border-radius: 6px !important;
	background: #fff1f1 !important;
	color: #7a2f2d !important;
	font-size: 13px !important;
	font-weight: 600 !important;
	line-height: 1.45 !important;
}

.cloudns-bulk-delete-tools {
	justify-content: space-between !important;
	margin: 0 0 10px 0 !important;
}

.cloudns-bulk-select-all-label {
	display: inline-flex !important;
	align-items: center !important;
	gap: 6px !important;
	margin: 0 !important;
	white-space: nowrap !important;
}

#cloudnsBulkDeleteSearch {
	width: 240px !important;
	margin-left: auto !important;
}

.cloudns-bulk-delete-count {
	min-width: 76px !important;
	color: #555555 !important;
	font-weight: 600 !important;
	text-align: right !important;
}

.cloudns-bulk-delete-table-wrap {
	max-height: 420px !important;
	overflow-y: auto !important;
	border: 1px solid #dddddd !important;
	border-radius: 6px !important;
}

.cloudns-bulk-delete-table {
	margin: 0 !important;
}

.cloudns-bulk-delete-table thead th {
	position: sticky !important;
	top: 0 !important;
	z-index: 2 !important;
	background: #163a5f !important;
	color: #ffffff !important;
	border-color: #163a5f !important;
}

.cloudns-bulk-delete-check-col {
	width: 44px !important;
	text-align: center !important;
}

.cloudns-bulk-delete-check-col input[type="checkbox"] {
	margin: 0 !important;
}

.cloudns-bulk-zone-result {
	margin: 0 0 14px 0 !important;
	padding: 12px 14px !important;
	border: 1px solid #b7c9d9 !important;
	border-radius: 8px !important;
	background: #f4f8fb !important;
	color: #333333 !important;
}

.cloudns-bulk-zone-result.cloudns-bulk-result-success {
	border-color: #b8d5a5 !important;
	background: #eef8e8 !important;
}

.cloudns-bulk-zone-result.cloudns-bulk-result-partial {
	border-color: #ead9a1 !important;
	background: #fff8dc !important;
}

.cloudns-bulk-zone-result.cloudns-bulk-result-error {
	border-color: #e0c0bf !important;
	background: #fff1f1 !important;
}


.cloudns-bulk-zone-result h4 {
	margin: 0 0 8px 0 !important;
	font-size: 16px !important;
	font-weight: 700 !important;
}

.cloudns-bulk-zone-result p,
.cloudns-bulk-zone-result ul {
	margin: 6px 0 0 0 !important;
}

.cloudns-bulk-zone-result ul {
	padding-left: 20px !important;
}

@media only screen and (max-width: 650px) {
	.cloudns-bulk-zone-row {
		grid-template-columns: 30px minmax(0, 1fr) !important;
	}
	.cloudns-bulk-zone-row .cloudns-bulk-zone-remove {
		grid-column: 2 !important;
		justify-self: flex-start !important;
	}
	.cloudns-bulk-zone-toolbar {
		margin-left: 38px !important;
	}
	.cloudns-bulk-zone-footer,
	.cloudns-bulk-delete-tools {
		align-items: stretch !important;
		flex-direction: column !important;
	}
	#cloudnsBulkDeleteSearch {
		width: 100% !important;
		margin-left: 0 !important;
	}
	.cloudns-bulk-delete-count {
		text-align: left !important;
	}
}


/* DomainMonger Patch 1601: keep Advanced above its local table without raising the entire DNSPlus page above WHMCS navigation. */
.cloudns-add-zone-shared-shell {
    position: relative !important;
    z-index: 20 !important;
    overflow: visible !important;
}

.cloudns-add-zone-shared-shell ul#cloudnsSettingsMenu {
    position: relative !important;
    z-index: 30 !important;
    overflow: visible !important;
}

.cloudns-add-zone-shared-shell ul#cloudnsSettingsMenu > li.cloudns-advanced-menu {
    position: relative !important;
    z-index: 40 !important;
}

.cloudns-add-zone-shared-shell ul#cloudnsSettingsMenu .cloudns-advanced-dropdown {
    z-index: 50 !important;
}

.cloudns-add-zone-shared-shell ~ .newZoneContainer,
.cloudns-add-zone-shared-shell ~ .cloudns-bulk-zone-result {
    position: relative !important;
    z-index: 1 !important;
}


</style>
{* DomainMonger Patch 1600: the established DNSPlus stylesheets are now loaded
   from ClientAreaHeadOutput before body paint; do not load them late here. *}

{literal}
	<script type="text/javascript" id="dm-cloudns-zone-tools-script-1611">
		function renumberBulkZoneRows() {
			$('#cloudnsBulkZoneRows .cloudns-bulk-zone-row').each(function(index) {
				$(this).find('.cloudns-bulk-zone-number').text(index + 1);
			});
		}

		function updateBulkDeleteControls() {
			var checked = $('.cloudns-bulk-delete-checkbox:checked').length;
			var visible = $('.cloudns-bulk-delete-zone-row:visible .cloudns-bulk-delete-checkbox');
			var visibleChecked = visible.filter(':checked').length;
			$('#cloudnsBulkDeleteCount').text(checked + ' selected');
			$('#cloudnsBulkDeleteSubmit').prop('disabled', checked === 0);
			$('#cloudnsBulkDeleteAll').prop('checked', visible.length > 0 && visibleChecked === visible.length);
			$('#cloudnsBulkDeleteAll').prop('indeterminate', visibleChecked > 0 && visibleChecked < visible.length);
		}

		function dmCloudnsZoneToolNavigate1611(targetUrl) {
			targetUrl = String(targetUrl || '');
			if (targetUrl === '') {
				return;
			}
			if (window.dmCloudnsSoftNavigate1611 && window.dmCloudnsSoftNavigate1611.canHandle(targetUrl)) {
				window.dmCloudnsSoftNavigate1611.navigate(targetUrl);
				return;
			}
			window.location.href = targetUrl;
		}

		$(function() {
			$(document)
				.off('change.dmCloudnsZoneTools1611', '.chooseZoneType')
				.on('change.dmCloudnsZoneTools1611', '.chooseZoneType', function() {
					dmCloudnsZoneToolNavigate1611(this.value);
				});

			$(document)
				.off('click.dmCloudnsZoneTools1611', '.cloudns-zone-tool-cancel')
				.on('click.dmCloudnsZoneTools1611', '.cloudns-zone-tool-cancel', function() {
					var targetUrl = String($(this).data('return-url') || $('.chooseZoneType').data('return-url') || '');
					dmCloudnsZoneToolNavigate1611(targetUrl);
				});

			$('#cloudnsAddAnotherZone').off('click.dmCloudnsZoneTools1611').on('click.dmCloudnsZoneTools1611', function() {
				var row = $('<div class="cloudns-bulk-zone-row">' +
					'<span class="cloudns-bulk-zone-number"></span>' +
					'<input type="text" name="bulkZones[]" class="form-control cloudns-bulk-zone-input" placeholder="example.com" autocomplete="off" />' +
					'<button type="button" class="btn cloudns-btn-secondary cloudns-bulk-zone-remove" aria-label="Remove zone row">Remove</button>' +
				'</div>');
				$('#cloudnsBulkZoneRows').append(row);
				renumberBulkZoneRows();
				row.find('input').focus();
			});

			$(document).off('click.dmCloudnsZoneTools1611', '.cloudns-bulk-zone-remove').on('click.dmCloudnsZoneTools1611', '.cloudns-bulk-zone-remove', function() {
				var rows = $('#cloudnsBulkZoneRows .cloudns-bulk-zone-row');
				if (rows.length <= 1) {
					$(this).closest('.cloudns-bulk-zone-row').find('input').val('').focus();
					return;
				}
				$(this).closest('.cloudns-bulk-zone-row').remove();
				renumberBulkZoneRows();
			});

			$('#cloudnsBulkAddZonesForm').off('submit.dmCloudnsZoneTools1611').on('submit.dmCloudnsZoneTools1611', function(event) {
				var hasZone = false;
				$(this).find('.cloudns-bulk-zone-input').each(function() {
					if ($.trim($(this).val()) !== '') {
						hasZone = true;
					}
				});
				if (!hasZone) {
					event.preventDefault();
					window.alert('Enter at least one DNS zone.');
					return false;
				}
				$('#cloudnsBulkAddSubmit').prop('disabled', true).text('Adding Zones...');
			});

			$('#cloudnsBulkDeleteSearch').off('input.dmCloudnsZoneTools1611').on('input.dmCloudnsZoneTools1611', function() {
				var query = $.trim($(this).val()).toLowerCase();
				$('.cloudns-bulk-delete-zone-row').each(function() {
					var zone = String($(this).data('zone-search') || '').toLowerCase();
					$(this).toggle(query === '' || zone.indexOf(query) !== -1);
				});
				updateBulkDeleteControls();
			});

			$('#cloudnsBulkDeleteAll').off('change.dmCloudnsZoneTools1611').on('change.dmCloudnsZoneTools1611', function() {
				$('.cloudns-bulk-delete-zone-row:visible .cloudns-bulk-delete-checkbox').prop('checked', this.checked);
				updateBulkDeleteControls();
			});

			$(document).off('change.dmCloudnsZoneTools1611', '.cloudns-bulk-delete-checkbox').on('change.dmCloudnsZoneTools1611', '.cloudns-bulk-delete-checkbox', updateBulkDeleteControls);

			$('#cloudnsBulkDeleteZonesForm').off('submit.dmCloudnsZoneTools1611').on('submit.dmCloudnsZoneTools1611', function(event) {
				var checked = $('.cloudns-bulk-delete-checkbox:checked').length;
				if (checked === 0) {
					event.preventDefault();
					return false;
				}
				var message = 'Delete ' + checked + (checked === 1 ? ' selected DNS zone' : ' selected DNS zones') + ' and all of their records? This cannot be undone.';
				if (!window.confirm(message)) {
					event.preventDefault();
					return false;
				}
				$('#cloudnsBulkDeleteSubmit').prop('disabled', true).text('Deleting Zones...');
			});

			renumberBulkZoneRows();
			updateBulkDeleteControls();
		});
	</script>
{/literal}


{assign var="path" value="../modules/servers/cloudns/templates/new-zone"}
{if $version gte '6'}
	{assign var="path" value="./new-zone"}
{/if}


{* DomainMonger Patch 1596: use the exact shared DNSPlus header/menu. *}
{assign var="cloudnsZoneToolsMenuZone" value=""}
{if isset($domainSwitchZones) && is_array($domainSwitchZones) && count($domainSwitchZones) gt 0}
	{foreach from=$domainSwitchZones item=cloudnsZoneToolsSwitchZone name=cloudnsZoneToolsMenuLoop}
		{if $smarty.foreach.cloudnsZoneToolsMenuLoop.first}
			{assign var="cloudnsZoneToolsMenuZone" value=$cloudnsZoneToolsSwitchZone.name}
		{/if}
	{/foreach}
{/if}

{assign var="zone" value=$cloudnsZoneToolsMenuZone}
{assign var="cloudnsZoneToolsGlobalPage" value=true}
{if $selectedZoneType eq 'bulkDelete'}
	{assign var="cloudAction" value="add-new-zone-bulk-delete"}
	{assign var="cloudnsZoneToolsHeaderTitle" value="Delete Zones"}
{else}
	{assign var="cloudAction" value="add-new-zone"}
	{assign var="cloudnsZoneToolsHeaderTitle" value="Add Zones"}
{/if}
{assign var="cloudnsSavedResponse" value=$response}
{assign var="response" value=null}

{* DomainMonger Patch 1607: these zone-management pages are service-wide and
   have no single domain context. Suppress the shared Active DNS System
   indicator directly in this definitive page template. This remains scoped
   to Add Zones, Delete Zones, and the other domainless zone-creation tools. *}
<style type="text/css">
#dm-dns-system-indicator-1337 {
    display: none !important;
    visibility: hidden !important;
}
</style>
{literal}
<script type="text/javascript" id="dm-cloudns-zone-tools-remove-domain-indicator-1607">
(function () {
    function removeDomainIndicator() {
        var indicator = document.getElementById('dm-dns-system-indicator-1337');
        if (indicator && indicator.parentNode) {
            indicator.parentNode.removeChild(indicator);
        }
    }

    removeDomainIndicator();

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', removeDomainIndicator, { once: true });
    }

    var observer = new MutationObserver(function () {
        removeDomainIndicator();
    });
    observer.observe(document.documentElement, { childList: true, subtree: true });
    window.setTimeout(function () {
        observer.disconnect();
        removeDomainIndicator();
    }, 4000);
})();
</script>
{/literal}

{assign var="cloudnsHeaderTemplatePath" value="../modules/servers/cloudns/templates"}
{if $version gte '6'}
	{assign var="cloudnsHeaderTemplatePath" value="."}
{/if}
<div class="cloudns-add-zone-shared-shell{if $selectedZoneType eq 'bulkDelete'} cloudns-delete-zone-shared-shell{/if}">
	{include file="$cloudnsHeaderTemplatePath/header-settings.tpl"}
</div>
{assign var="response" value=$cloudnsSavedResponse}

{if $selectedZoneType ne 'bulkDelete'}
<div class="newZoneContainer cloudns-add-zone-overview">
	<h4 class="cloudns-module-title">DNS Zone Tools</h4>
	{if $response}
		<div class="notification">{$response.description|escape:'html'}</div>
	{/if}
	<div class="newZoneButtonsContainer">
		<div class="pull-left">Action:&nbsp;</div>
		<select name="zoneTypeSelect" class="chooseZoneType form-control pull-left" data-return-url="clientarea.php?action=productdetails&amp;id={$serviceid}&amp;customAction=add-new-zone-bulk-master">
			<option value="clientarea.php?action=productdetails&amp;id={$serviceid}&amp;customAction=add-new-zone-bulk-master"{if $selectedZoneType eq 'bulkMaster' || $selectedZoneType eq 'master'} selected="selected"{/if}>Add Zones</option>
			<option value="clientarea.php?action=productdetails&amp;id={$serviceid}&amp;customAction=add-new-zone-slave"{if $selectedZoneType eq 'slave'} selected="selected"{/if}>Add Slave (Secondary) Domain Zone</option>
			<option value="clientarea.php?action=productdetails&amp;id={$serviceid}&amp;customAction=add-new-zone-parked"{if $selectedZoneType eq 'parked'} selected="selected"{/if}>Add Parked Zone</option>
			<option value="clientarea.php?action=productdetails&amp;id={$serviceid}&amp;customAction=add-new-zone-master-reverse"{if $selectedZoneType eq 'masterReverse'} selected="selected"{/if}>Add Master (Primary) Reverse Zone</option>
			<option value="clientarea.php?action=productdetails&amp;id={$serviceid}&amp;customAction=add-new-zone-slave-reverse"{if $selectedZoneType eq 'slaveReverse'} selected="selected"{/if}>Add Slave (Secondary) Reverse Zone</option>
		</select>
	</div>
</div>
{elseif $response}
	<div class="notification">{$response.description|escape:'html'}</div>
{/if}

{if $bulkZoneResult.result}
	{assign var="bulkResult" value=$bulkZoneResult.result}
	<div class="cloudns-bulk-zone-result cloudns-bulk-result-{$bulkResult.status|escape:'html'}">
		<h4>{if $bulkZoneResult.action eq 'bulkDelete'}Delete Results{else}Add Results{/if}</h4>
		{if $bulkResult.description}<p>{$bulkResult.description|escape:'html'}</p>{/if}
		{if $bulkResult.created|@count gt 0}
			<p><strong>Created ({$bulkResult.created|@count}):</strong></p>
			<ul>{foreach from=$bulkResult.created item=resultZone}<li>{$resultZone|escape:'html'}</li>{/foreach}</ul>
		{/if}
		{if $bulkResult.deleted|@count gt 0}
			<p><strong>Deleted ({$bulkResult.deleted|@count}):</strong></p>
			<ul>{foreach from=$bulkResult.deleted item=resultZone}<li>{$resultZone|escape:'html'}</li>{/foreach}</ul>
		{/if}
		{if $bulkResult.failed|@count gt 0}
			<p><strong>Not completed ({$bulkResult.failed|@count}):</strong></p>
			<ul>
			{foreach from=$bulkResult.failed item=resultFailure}
				<li><strong>{$resultFailure.zone|escape:'html'}:</strong> {$resultFailure.description|escape:'html'}</li>
			{/foreach}
			</ul>
		{/if}
		{if $bulkResult.skipped|@count gt 0}
			<p><strong>Skipped ({$bulkResult.skipped|@count}):</strong></p>
			<ul>
			{foreach from=$bulkResult.skipped item=resultSkipped}
				<li><strong>{$resultSkipped.zone|escape:'html'}:</strong> {$resultSkipped.description|escape:'html'}</li>
			{/foreach}
			</ul>
		{/if}
	</div>
{/if}

{if $selectedZoneType eq 'bulkMaster' || $selectedZoneType eq 'master'}
	{include file="$path/bulk-master.tpl"}
{elseif $selectedZoneType eq 'bulkDelete'}
	{include file="$path/bulk-delete.tpl"}
{elseif $selectedZoneType eq 'slave'}
	{include file="$path/slave.tpl"}
{elseif $selectedZoneType eq 'parked'}
	{include file="$path/parked.tpl"}
{elseif $selectedZoneType eq 'masterReverse'}
	{include file="$path/master-reverse.tpl"}
{elseif $selectedZoneType eq 'slaveReverse'}
	{include file="$path/slave-reverse.tpl"}
{else}
	{include file="$path/master.tpl"}
{/if}