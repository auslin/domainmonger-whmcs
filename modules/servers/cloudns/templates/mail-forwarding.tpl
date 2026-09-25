{assign var="path" value="../modules/servers/cloudns/templates"}
{if $version gte '6'}
	{assign var="path" value="./"}
{/if}
{include file="$path/header-settings.tpl"}

{* we rewrite the $path var, because its current value is needed only above *}
{assign var="path" value="images/"}
{if $version gte '6'}
	{assign var="path" value="./assets/img/"}
{/if}

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.js"></script>

<style type="text/css">
{literal}
#table-forwards.cloudns-sortable thead th,
#table-forwards.cloudns-sortable.dataTable thead th {
	position: relative;
}

#table-forwards.cloudns-sortable.dataTable thead th.sorting,
#table-forwards.cloudns-sortable.dataTable thead th.sorting_asc,
#table-forwards.cloudns-sortable.dataTable thead th.sorting_desc,
#table-forwards.cloudns-sortable.dataTable thead th.sorting_disabled {
	background-image: none !important;
	padding-right: 28px !important;
}

#table-forwards.cloudns-sortable.dataTable thead th.sorting {
	background-color: #f1f1f1 !important;
	color: #4f4f4f !important;
}

#table-forwards.cloudns-sortable.dataTable thead th.sorting_asc,
#table-forwards.cloudns-sortable.dataTable thead th.sorting_desc {
	background-color: #ee9d4a !important;
	color: #ffffff !important;
}

#table-forwards.cloudns-sortable.dataTable thead th.sorting::before,
#table-forwards.cloudns-sortable.dataTable thead th.sorting::after,
#table-forwards.cloudns-sortable.dataTable thead th.sorting_asc::before,
#table-forwards.cloudns-sortable.dataTable thead th.sorting_asc::after,
#table-forwards.cloudns-sortable.dataTable thead th.sorting_desc::before,
#table-forwards.cloudns-sortable.dataTable thead th.sorting_desc::after {
	position: absolute;
	right: 9px;
	font-size: 10px;
	line-height: 1;
	color: #f3a24b;
	opacity: 0.9;
}

#table-forwards.cloudns-sortable.dataTable thead th.sorting::before,
#table-forwards.cloudns-sortable.dataTable thead th.sorting_asc::before,
#table-forwards.cloudns-sortable.dataTable thead th.sorting_desc::before {
	content: "▲";
	top: calc(50% - 9px);
}

#table-forwards.cloudns-sortable.dataTable thead th.sorting::after,
#table-forwards.cloudns-sortable.dataTable thead th.sorting_asc::after,
#table-forwards.cloudns-sortable.dataTable thead th.sorting_desc::after {
	content: "▼";
	top: calc(50% + 1px);
}

#table-forwards.cloudns-sortable.dataTable thead th.sorting_asc::before {
	color: #ffffff;
	opacity: 1;
}

#table-forwards.cloudns-sortable.dataTable thead th.sorting_asc::after {
	color: #f3a24b;
	opacity: 0.75;
}

#table-forwards.cloudns-sortable.dataTable thead th.sorting_desc::before {
	color: #f3a24b;
	opacity: 0.75;
}

#table-forwards.cloudns-sortable.dataTable thead th.sorting_desc::after {
	color: #ffffff;
	opacity: 1;
}

#table-forwards .checkbox-sort-col,
#table-forwards .checkbox-col {
	width: 42px !important;
	min-width: 42px !important;
	max-width: 42px !important;
	text-align: center !important;
	padding-left: 0 !important;
	padding-right: 0 !important;
	vertical-align: middle !important;
}

#table-forwards.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting,
#table-forwards.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting_asc,
#table-forwards.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting_desc,
#table-forwards.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting_disabled {
	padding-left: 0 !important;
	padding-right: 0 !important;
	text-align: center !important;
}

#table-forwards.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting::before,
#table-forwards.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting_asc::before,
#table-forwards.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting_desc::before,
#table-forwards.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting::after,
#table-forwards.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting_asc::after,
#table-forwards.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting_desc::after {
	right: 3px !important;
}

#table-forwards .action-col {
	width: 82px !important;
	min-width: 82px !important;
	max-width: 82px !important;
	white-space: nowrap;
}

#table-forwards .forward-email-col,
#table-forwards .forward-points-col {
	width: calc((100% - 146px) / 2) !important;
}

#table-forwards .checkbox-sort-col input,
#table-forwards .checkbox-col input {
	position: relative;
	z-index: 2;
	display: block;
	margin-left: auto !important;
	margin-right: auto !important;
}

#table-forwards .cloudns-add-button,
#table-forwards .cloudns-add-button:visited {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	min-width: 70px;
	
	padding: 0 14px;
	border: 1px solid #ff6b1a;
	border-radius: 5px;
	background: #ff6b1a !important;
	color: #ffffff !important;
	font-size: 14px;
	font-weight: 700;
	line-height: 1;
	text-decoration: none !important;
	box-shadow: none;
}

#table-forwards .cloudns-add-button:hover,
#table-forwards .cloudns-add-button:focus {
	background: #f05f12 !important;
	color: #ffffff !important;
	text-decoration: none !important;
	border-color: #f05f12;
	box-shadow: 0 0 0 2px rgba(255, 107, 26, 0.22);
	outline: none;
}

#table-forwards .cloudns-add-button:active {
	background: #dd520c !important;
	color: #ffffff !important;
	border-color: #dd520c;
	box-shadow: 0 0 0 2px rgba(255, 107, 26, 0.28);
}

#table-forwards .cloudns-action-icon {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 22px;
	height: 22px;
	margin-left: 3px;
	border: 1px solid #d4d4d4;
	border-radius: 3px;
	background: #fff;
	color: #555 !important;
	text-decoration: none !important;
	font-size: 13px;
	vertical-align: middle;
}

#table-forwards .cloudns-action-icon:hover,
#table-forwards .cloudns-action-icon:focus {
	border-color: #f7941d;
	color: #f7941d !important;
	text-decoration: none !important;
	box-shadow: 0 0 0 2px rgba(247, 148, 29, 0.18);
}

#table-forwards .cloudns-pencil {
	font-size: 15px;
}

.cloudns-mailforward-actions-row {
	display: flex;
	align-items: center;
	gap: 8px;
	margin: 10px 0 0;
}

.cloudns-mailforward-actions-row-bottom {
	justify-content: flex-start;
}

#mass-delete.cloudns-mailforward-delete-button {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	min-width: 72px;
	height: 34px !important;
	padding: 0 14px;
	border: 1px solid #ff6b1a;
	border-radius: 5px;
	background: #ff6b1a !important;
	color: #ffffff !important;
	font-size: 14px;
	font-weight: 700;
	line-height: 1;
	text-align: center;
	box-shadow: none;
}

#mass-delete.cloudns-mailforward-delete-button:hover,
#mass-delete.cloudns-mailforward-delete-button:focus {
	background: #f05f12 !important;
	border-color: #f05f12;
	color: #ffffff !important;
	box-shadow: 0 0 0 2px rgba(255, 107, 26, 0.22);
	outline: none;
}

#mass-delete.cloudns-mailforward-delete-button:active {
	background: #dd520c !important;
	border-color: #dd520c;
	color: #ffffff !important;
}

#mass-delete.cloudns-mailforward-delete-button[disabled] {
	background: #ff6b1a !important;
	border-color: #ff6b1a;
	color: #ffffff !important;
	opacity: 1;
	cursor: not-allowed;
}

.cloudns-bulk-domain-switcher {
	display: inline-flex;
	align-items: center;
	gap: 8px;
	margin-left: auto;
	white-space: nowrap;
}

.cloudns-bulk-domain-switcher select.form-control {
	height: 34px !important;
	min-width: 230px;
	width: auto;
	padding: 6px 12px;
	font-size: 13px;
	font-weight: 400;
	line-height: 1.42857143;
}

.cloudns-domain-switch-button,
.cloudns-domain-switch-button:visited {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	min-width: 72px;
	height: 34px !important;
	padding: 0 14px;
	border: 1px solid #ff6b1a;
	border-radius: 5px;
	background: #ff6b1a !important;
	color: #ffffff !important;
	font-size: 14px;
	font-weight: 700;
	line-height: 1;
	text-align: center;
	box-shadow: none;
}

.cloudns-domain-switch-button:hover,
.cloudns-domain-switch-button:focus {
	background: #f05f12 !important;
	border-color: #f05f12;
	color: #ffffff !important;
	box-shadow: 0 0 0 2px rgba(255, 107, 26, 0.22);
	outline: none;
}

.cloudns-domain-switch-button:active {
	background: #dd520c !important;
	border-color: #dd520c;
	color: #ffffff !important;
}

@media only screen and (max-width: 650px) {
	.cloudns-mailforward-actions-row {
		flex-wrap: wrap;
	}

	.cloudns-bulk-domain-switcher {
		width: 100%;
		margin-left: 0;
		flex-wrap: wrap;
	}

	.cloudns-bulk-domain-switcher select.form-control {
		min-width: 0;
		flex: 1 1 180px;
	}
}

.cloudns-mailforward-mx-panel {
	border: 1px solid #f0ad4e;
	border-radius: 6px;
	background: #fff8ec;
	color: #4a3a1a;
	padding: 12px 14px;
	margin: 0 0 14px 0;
	text-align: left;
}

.cloudns-mailforward-mx-panel strong {
	display: block;
	margin-bottom: 6px;
}

.cloudns-mailforward-mx-panel ul {
	margin: 6px 0 10px 20px;
	padding: 0;
}

.cloudns-mailforward-mx-panel form {
	margin: 0;
}

.cloudns-mailforward-mx-button {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	min-width: 210px;
	height: 34px;
	padding-left: 14px;
	padding-right: 14px;
	line-height: 1;
	text-align: center;
}

.cloudns-mailforward-response {
	padding: 10px 12px;
	margin-bottom: 12px;
	border-radius: 4px;
	border: 1px solid #bce8f1;
	background: #d9edf7;
	color: #31708f;
	text-align: left;
}

.cloudns-mailforward-response.cloudns-mailforward-response-error {
	border-color: #ebccd1;
	background: #f2dede;
	color: #a94442;
}

.cloudns-mailforward-response.cloudns-mailforward-response-success {
	border-color: #d6e9c6;
	background: #dff0d8;
	color: #3c763d;
}



a.btn.cloudns-switch-style-button,
button.btn.cloudns-switch-style-button,
input.btn.cloudns-switch-style-button,
.cloudns-switch-style-button,
.cloudns-switch-style-button:visited {
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
	appearance: none !important;
	-webkit-appearance: none !important;
}

a.btn.cloudns-switch-style-button:hover,
a.btn.cloudns-switch-style-button:focus,
button.btn.cloudns-switch-style-button:hover,
button.btn.cloudns-switch-style-button:focus,
input.btn.cloudns-switch-style-button:hover,
input.btn.cloudns-switch-style-button:focus,
.cloudns-switch-style-button:hover,
.cloudns-switch-style-button:focus {
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

a.btn.cloudns-switch-style-button:active,
button.btn.cloudns-switch-style-button:active,
input.btn.cloudns-switch-style-button:active,
.cloudns-switch-style-button:active {
	background: #dd520c !important;
	background-color: #dd520c !important;
	background-image: none !important;
	border-color: #dd520c !important;
	color: #ffffff !important;
	box-shadow: 0 0 0 2px rgba(255, 107, 26, 0.28) !important;
}

a.btn.cloudns-switch-style-button[disabled],
button.btn.cloudns-switch-style-button[disabled],
input.btn.cloudns-switch-style-button[disabled],
.cloudns-switch-style-button[disabled],
.cloudns-switch-style-button.disabled,
.cloudns-switch-style-button:disabled {
	background: #ff6b1a !important;
	background-color: #ff6b1a !important;
	background-image: none !important;
	border-color: #ff6b1a !important;
	color: #ffffff !important;
	opacity: 1 !important;
	cursor: not-allowed !important;
}

.btn-block.cloudns-switch-style-button {
	display: flex !important;
	width: 100% !important;
}

/* DM table header vertical alignment pass */
#table-forwards > thead > tr > th,
#table-forwards.cloudns-sortable.dataTable > thead > tr > th {
	vertical-align: middle !important;
	line-height: 34px !important;
	padding-top: 6px !important;
	padding-bottom: 6px !important;
}

#table-forwards > thead > tr > th.addNewRecord.action-col,
#table-forwards.cloudns-sortable.dataTable > thead > tr > th.addNewRecord.action-col {
	line-height: 1 !important;
	vertical-align: middle !important;
}

#table-forwards > thead > tr > th.checkbox-sort-col input {
	vertical-align: middle !important;
	margin-top: 0 !important;
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


/* DM action icon column overflow fix */
#table-forwards .action-col {
	width: 88px !important;
	min-width: 88px !important;
	max-width: 88px !important;
}

#table-forwards > tbody > tr > td.action-col,
#table-forwards > thead > tr > th.action-col {
	padding-left: 6px !important;
	padding-right: 6px !important;
	overflow: visible !important;
}

#table-forwards .cloudns-action-icon {
	width: 24px !important;
	height: 24px !important;
	min-width: 24px !important;
	margin: 0 1px !important;
}



/* DM page-by-page fix pass 1: Mail Forwards +Add right alignment. */
#table-forwards th.addNewRecord.action-col,
#table-forwards th.addNewRecord.text-right {
	text-align: right !important;
	padding-right: 10px !important;
	white-space: nowrap !important;
}

#table-forwards th.addNewRecord.action-col .cloudns-add-button,
#table-forwards th.addNewRecord.text-right .cloudns-add-button {
	float: none !important;
	margin-left: auto !important;
	margin-right: 0 !important;
	display: inline-flex !important;
	align-items: center !important;
	justify-content: center !important;
}



/* DM page-by-page fix pass 2: hard-align Mail Forwards +Add. */
#table-forwards th.cloudns-forward-add-header {
	text-align: right !important;
	padding-right: 10px !important;
	vertical-align: middle !important;
}

#table-forwards th.cloudns-forward-add-header .cloudns-forward-add-wrap {
	display: flex !important;
	align-items: center !important;
	justify-content: flex-end !important;
	width: 100% !important;
	margin: 0 !important;
	padding: 0 !important;
}

#table-forwards th.cloudns-forward-add-header .cloudns-add-button {
	float: none !important;
	margin: 0 !important;
}



/* DM page-by-page fix pass 3: final Mail Forwards +Add edge alignment. */
#table-forwards .action-col,
#table-forwards > tbody > tr > td.action-col,
#table-forwards > thead > tr > th.action-col,
#table-forwards.cloudns-sortable.dataTable > thead > tr > th.action-col {
	width: 104px !important;
	min-width: 104px !important;
	max-width: 104px !important;
	text-align: right !important;
	box-sizing: border-box !important;
}

#table-forwards th.cloudns-forward-add-header,
#table-forwards.cloudns-sortable.dataTable thead th.cloudns-forward-add-header {
	text-align: right !important;
	padding-right: 10px !important;
	padding-left: 6px !important;
}

#table-forwards th.cloudns-forward-add-header .cloudns-forward-add-wrap {
	display: flex !important;
	justify-content: flex-end !important;
	align-items: center !important;
	width: 100% !important;
}

#table-forwards th.cloudns-forward-add-header .cloudns-add-button {
	margin-left: auto !important;
	margin-right: 0 !important;
	float: right !important;
}

#table-forwards td.action-col {
	padding-right: 10px !important;
}

</style>

<script>
{literal}
$(document).ready(function () {
	if ($.fn.dataTable && $.fn.dataTable.ext && $.fn.dataTable.ext.order) {
		$.fn.dataTable.ext.order['dom-checkbox'] = function (settings, col) {
			return this.api().column(col, {order: 'index'}).nodes().map(function (td) {
				return $('input[type="checkbox"]', td).prop('checked') ? '1' : '0';
			});
		};
	}

	if ($.fn.DataTable) {
		$('#table-forwards').DataTable({
			bInfo: false,
			lengthChange: false,
			bFilter: false,
			bPaginate: false,
			ordering: true,
			orderMulti: false,
			order: [[1, 'asc']],
			columnDefs: [
				{ width: 42, targets: 0, orderDataType: 'dom-checkbox' },
				{ width: 'calc((100% - 146px) / 2)', targets: 1 },
				{ width: 'calc((100% - 146px) / 2)', targets: 2 },
				{ orderable: false, searchable: false, width: 104, targets: 3 }
			]
		});
	}

	$('#check-all').on('click', function (e) {
		e.stopPropagation();
		$('.forward-checkbox').prop('checked', this.checked);
		$('#mass-delete').prop('disabled', $('.forward-checkbox:checked').length === 0);
	});

	$(document).off('click.dmCloudnsMailForwards1612', '.forward-checkbox').on('click.dmCloudnsMailForwards1612', '.forward-checkbox', function (e) {
		e.stopPropagation();
		$('#mass-delete').prop('disabled', $('.forward-checkbox:checked').length === 0);
		$('#check-all').prop('checked', $('.forward-checkbox').length > 0 && $('.forward-checkbox:checked').length === $('.forward-checkbox').length);
	});

	$('#mass-delete').on('click', function () {
		if ($('.forward-checkbox:checked').length && confirm('Are you sure you want to delete the selected email forwards?')) {
			$('#forwards-form').submit();
		}
	});

});
{/literal}
</script>

<div class="cloudns-body-panel cloudns-mailforward-panel">

	{if isset($response) && is_array($response) && (isset($response.description) || isset($response.statusDescription))}
	<div class="cloudns-mailforward-response {if isset($response.status) && ($response.status == 'error' || $response.status == 'Failed')}cloudns-mailforward-response-error{elseif isset($response.status) && $response.status == 'success'}cloudns-mailforward-response-success{/if}">
		{if isset($response.description)}{$response.description|@htmlspecialchars}{else}{$response.statusDescription|@htmlspecialchars}{/if}
	</div>
{/if}

{if isset($mailForwardMxStatus) && isset($mailForwardMxStatus.error) && $mailForwardMxStatus.error != ''}
	<div class="cloudns-mailforward-mx-panel">
		<strong>Mail Forward MX Records</strong>
		Could not check the required ClouDNS mail forwarding MX records: {$mailForwardMxStatus.error|@htmlspecialchars}
	</div>
{elseif isset($mailForwardMxStatus) && !$mailForwardMxStatus.allActive}
	<div class="cloudns-mailforward-mx-panel">
		<strong>Mail Forward MX Records</strong>
		The following required mail forwarding MX records are not active for this zone:
		<ul>
		{foreach from=$mailForwardMxStatus.missing item=missingMx}
			<li>{$missingMx|@htmlspecialchars}</li>
		{/foreach}
		</ul>
		<form method="post" action="clientarea.php?action=productdetails&id={$serviceid}&customAction=mail-forwarding-add-mx&zone={$zone}" onsubmit="return confirm('Add the missing ClouDNS Mail Forward MX records with priority 100?');">
			<button type="submit" class="btn cloudns-mailforward-mx-button cloudns-switch-style-button cloudns-btn-primary">Add Mail Forward MX Records</button>
		</form>
	</div>
{/if}

<form id="forwards-form" method="post" action="clientarea.php?action=productdetails&id={$serviceid}&customAction=mail-forwarding&zone={$zone}">
	<input type="hidden" name="mass-delete" value="1">
	<div class="table-responsive cloudns-table-shell">
		<table id="table-forwards" class="table table-bordered cloudns-sortable" style="width:100%;">
			<thead>
			<tr class="active" style="background-color: #f5f5f5;">
				<th class="checkbox-sort-col"><input type="checkbox" id="check-all"></th>
				<th class="forward-email-col">Email</th>
				<th class="forward-points-col">Points To</th>
				<th class="addNewRecord text-right action-col cloudns-forward-add-header" style="text-align:right !important; padding-right:10px !important;">
					<div class="cloudns-forward-add-wrap" style="display:flex !important; justify-content:flex-end !important; align-items:center !important; width:100% !important; margin:0 !important; padding:0 !important;">
						<a class="btn cloudns-add-button cloudns-switch-style-button cloudns-btn-primary" style="float:right !important; margin-left:auto !important; margin-right:0 !important;" href="clientarea.php?action=productdetails&id={$serviceid}&customAction=add-new-forwarding&zone={$zone}">+Add</a>
					</div>
				</th>
			</tr>
			</thead>
			<tbody>
			{if empty($records)}
				<tr><td colspan="4">There are no mail forwards.</td></tr>
			{/if}
			{foreach from=$records item=record}
				{assign var="cloudnsForwardSourceDisplay" value=$record.source}
				{if $record.source|@substr:0:1 == '@'}
					{capture assign="cloudnsForwardSourceDisplay"}All {$record.source}{/capture}
				{/if}
				<tr>
					<td class="checkbox-col"><input type="checkbox" name="forwards[]" class="forward-checkbox" value="{$record.id}"></td>
					<td class="overflow forward-email-col" title="{$cloudnsForwardSourceDisplay|@htmlspecialchars}"><div class="overflowDiv"><div>{$cloudnsForwardSourceDisplay|@htmlspecialchars}</div></div></td>
					<td class="overflow forward-points-col" title="{$record.destination|@htmlspecialchars}"><div class="overflowDiv"><div>{$record.destination|@htmlspecialchars}</div></div></td>
					<td class="text-right action-col">
						<a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=do-edit-forward&zone={$zone}&forward_id={$record.id}" title="Edit this forward" class="cloudns-action-icon cloudns-pencil">&#9998;</a>
						<a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=delete-forward&zone={$zone}&record={$record.id}" title="Delete this forward" onclick="return confirm('Are you sure you want to delete this forward?');" class="cloudns-action-icon cloudns-action-icon-danger">&#10005;</a>
					</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>
	<div class="cloudns-mailforward-actions-row cloudns-mailforward-actions-row-bottom">
		<input type="button" class="btn cloudns-mailforward-delete-button cloudns-switch-style-button cloudns-btn-danger" value="Delete" id="mass-delete" disabled>
	</div>
</form>

</div>
