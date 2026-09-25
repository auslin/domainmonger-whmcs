{* the paths are different for the different versions *}
{assign var="path" value="../modules/servers/cloudns/templates"}
{if $version gte '6'}
	{assign var="path" value="./"}

<style type="text/css">
{literal}
	.addNewRecordMobile {
		display: none;
	}

	@media only screen and (max-width: 650px) {
		.addNewRecord {
			display: none;
		}

		.addNewRecordMobile {
			display: inline-block;
			width: 100%;
			text-align: center;
		}

		.table-bordered>thead>tr>td,
		.table-bordered>thead>tr>th {
			border-bottom-width: 0px;
		}

		.overflow .overflowDiv {
			width: 100% !important;
			position: unset !important;
		}

		.overflow .overflowDiv.overflowRecordRecord {
			max-width: 500px !important;
			position: unset !important;
		}
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

{/literal}
</style>
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
.cloudns-record-toolbar {
	--cloudns-toolbar-height: 34px;
	display: flex;
	align-items: center;
	gap: 8px;
	flex-wrap: nowrap;
	margin-bottom: 8px;
	width: 100%;
}

.cloudns-record-toolbar .btn,
.cloudns-record-toolbar select.form-control,
.cloudns-record-toolbar input.form-control,
.cloudns-record-toolbar .cloudns-record-search,
.cloudns-record-toolbar .cloudns-search-toggle,
.cloudns-record-toolbar #cloudns-record-search {
	height: var(--cloudns-toolbar-height);
	box-sizing: border-box;
}

.cloudns-record-toolbar .btn {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	line-height: 1;
	padding-top: 0;
	padding-bottom: 0;
}

.cloudns-record-filter {
	display: inline-flex;
	align-items: center;
	gap: 6px;
	margin: 0;
}

.cloudns-record-filter label {
	font-weight: normal;
	margin: 0;
}

.cloudns-visually-hidden {
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

.cloudns-record-filter select.form-control {
	width: auto;
	min-width: 145px;
}

.cloudns-record-count {
	margin-left: 0;
	white-space: nowrap;
}

.cloudns-record-count-indicator {
	display: inline-flex;
	align-items: center;
	height: var(--cloudns-toolbar-height);
	padding: 0 10px;
	margin-left: auto;
	border: 1px solid #dddddd;
	border-radius: 4px;
	background: #fafafa;
	color: #444444;
	font-size: 12px;
	font-weight: 600;
	line-height: 1;
	white-space: nowrap;
}


.cloudns-sub-menu {
	display: inline-flex;
	align-items: center;
	gap: 6px;
	margin-left: auto;
	flex-wrap: nowrap;
	min-width: 0;
}

.cloudns-sub-menu-item,
.cloudns-sub-menu-item:visited {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	height: var(--cloudns-toolbar-height);
	box-sizing: border-box;
	padding: 0 10px;
	border: 1px solid transparent;
	border-radius: 17px;
	background: #f1f1f1;
	color: #333333 !important;
	font-size: 12px;
	font-weight: 600;
	line-height: 1;
	text-decoration: none !important;
	white-space: nowrap;
	transition: background-color 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease, color 0.15s ease;
}

.cloudns-sub-menu-item:hover,
.cloudns-sub-menu-item:focus {
	background: #ffffff;
	border-color: #f7941d;
	color: #f7941d !important;
	text-decoration: none !important;
	box-shadow: 0 0 0 2px rgba(247, 148, 29, 0.18);
	outline: none;
}

.cloudns-sub-menu-item:active {
	background: #fff4e8;
	border-color: #d77b13;
	color: #d77b13 !important;
}

.cloudns-sub-menu-button {
	font-family: inherit;
	cursor: pointer;
}

.cloudns-record-search {
	display: inline-flex;
	align-items: stretch;
	border: 1px solid #d6d6d6;
	border-radius: 4px;
	overflow: hidden;
	background: #fff;
}

.cloudns-record-search input {
	border: 0 !important;
	box-shadow: none !important;
	min-width: 190px;
	width: 190px;
	padding: 0 9px;
	line-height: normal;
}

.cloudns-search-toggle {
	border: 0;
	border-right: 1px solid #d6d6d6;
	background: #f7f7f7;
	min-width: 42px;
	font-size: 16px;
	line-height: 1;
	padding: 0;
}

.cloudns-search-toggle:hover,
.cloudns-search-toggle:focus {
	background: #eeeeee;
	outline: none;
	box-shadow: inset 0 -2px 0 #f7941d;
}

@media only screen and (max-width: 760px) {
	.cloudns-record-toolbar {
		flex-wrap: wrap;
	}

	.cloudns-record-count-indicator {
		margin-left: 0;
	}

	.cloudns-sub-menu {
		margin-left: 0;
		flex-wrap: wrap;
		width: 100%;
	}

	.cloudns-record-search input {
		min-width: 180px;
		width: 180px;
	}
}


#records-table.cloudns-sortable thead th,
#records-table.cloudns-sortable.dataTable thead th {
	position: relative;
}

#records-table.cloudns-sortable.dataTable thead th.sorting,
#records-table.cloudns-sortable.dataTable thead th.sorting_asc,
#records-table.cloudns-sortable.dataTable thead th.sorting_desc,
#records-table.cloudns-sortable.dataTable thead th.sorting_disabled {
	background-image: none !important;
	padding-right: 28px !important;
}

#records-table.cloudns-sortable.dataTable thead th.sorting {
	background-color: #f1f1f1 !important;
	color: #333333 !important;
}

#records-table.cloudns-sortable.dataTable thead th.sorting_asc,
#records-table.cloudns-sortable.dataTable thead th.sorting_desc {
	background-color: #ee9d4a !important;
	color: #ffffff !important;
}

#records-table.cloudns-sortable.dataTable thead th.sorting::before,
#records-table.cloudns-sortable.dataTable thead th.sorting::after,
#records-table.cloudns-sortable.dataTable thead th.sorting_asc::before,
#records-table.cloudns-sortable.dataTable thead th.sorting_asc::after,
#records-table.cloudns-sortable.dataTable thead th.sorting_desc::before,
#records-table.cloudns-sortable.dataTable thead th.sorting_desc::after {
	position: absolute;
	right: 9px;
	font-size: 10px;
	line-height: 1;
	color: #d77b13;
	opacity: 1;
}

#records-table.cloudns-sortable.dataTable thead th.sorting::before,
#records-table.cloudns-sortable.dataTable thead th.sorting_asc::before,
#records-table.cloudns-sortable.dataTable thead th.sorting_desc::before {
	content: "▲";
	top: calc(50% - 9px);
}

#records-table.cloudns-sortable.dataTable thead th.sorting::after,
#records-table.cloudns-sortable.dataTable thead th.sorting_asc::after,
#records-table.cloudns-sortable.dataTable thead th.sorting_desc::after {
	content: "▼";
	top: calc(50% + 1px);
}

#records-table.cloudns-sortable.dataTable thead th.sorting_asc::before,
#records-table.cloudns-sortable.dataTable thead th.sorting_desc::after {
	color: #ffffff;
	opacity: 1;
}

#records-table.cloudns-sortable.dataTable thead th.sorting_asc::after,
#records-table.cloudns-sortable.dataTable thead th.sorting_desc::before {
	color: #f7ca9c;
	opacity: 0.95;
}

#records-table .action-col {
	width: 154px;
	min-width: 154px;
	max-width: 154px;
	white-space: nowrap;
	text-align: center !important;
}

/* DM main records +Add alignment pass */
#records-table thead th.addNewRecord.action-col {
	text-align: right !important;
}

#records-table.cloudns-sortable.dataTable thead th.addNewRecord.action-col.sorting_disabled {
	padding-right: 8px !important;
}

#records-table thead th.addNewRecord.action-col .cloudns-add-button {
	margin-left: auto;
	margin-right: 0;
}

#records-table .record-host-col {
	width: 238px !important;
}

#records-table .record-type-col {
	width: 50px !important;
	min-width: 50px !important;
	max-width: 50px !important;
}

#records-table .record-points-col {
	width: 322px !important;
}

#records-table .record-ttl-col {
	width: 50px !important;
	min-width: 50px !important;
	max-width: 50px !important;
}

#records-table.cloudns-monitoring-icons-off .action-col {
	width: 104px !important;
	min-width: 104px !important;
	max-width: 104px !important;
}

#records-table.cloudns-monitoring-icons-off .record-host-col {
	width: 254px !important;
}

#records-table.cloudns-monitoring-icons-off .record-points-col {
	width: 339px !important;
}

#records-table.cloudns-monitoring-icons-off.dataTable thead th.action-col.sorting_disabled {
	width: 104px !important;
	min-width: 104px !important;
	max-width: 104px !important;
}

#records-table td.record-type-col,
#records-table td.record-ttl-col {
	white-space: nowrap;
}

#records-table .cloudns-add-button,
#records-table .cloudns-add-button:visited {
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

#records-table .cloudns-add-button:hover,
#records-table .cloudns-add-button:focus {
	background: #f05f12 !important;
	color: #ffffff !important;
	text-decoration: none !important;
	border-color: #f05f12;
	box-shadow: 0 0 0 2px rgba(255, 107, 26, 0.22);
	outline: none;
}

#records-table .cloudns-add-button:active {
	background: #dd520c !important;
	color: #ffffff !important;
	border-color: #dd520c;
	box-shadow: 0 0 0 2px rgba(255, 107, 26, 0.28);
}

#mass-ttl-select {
	min-width: 120px;
	max-width: 140px;
	height: 34px !important;
	padding: 6px 12px;
	font-size: 13px;
	font-weight: 400;
	line-height: 1.42857143;
}

.cloudns-bulk-actions-row {
	display: flex;
	align-items: center;
	gap: 8px;
	margin: 8px 0 0;
}

.cloudns-bulk-actions-row select.form-control,
.cloudns-bulk-actions-row .btn {
	height: 34px;
	box-sizing: border-box;
	font-size: 13px;
	font-weight: 400;
	line-height: 1.42857143;
}

.cloudns-bulk-action-select {
	width: auto;
	min-width: 96px;
	height: 34px !important;
	padding: 6px 12px;
	font-size: 13px;
	font-weight: 400;
	line-height: 1.42857143;
}

.cloudns-bulk-action-text {
	white-space: nowrap;
	color: #444444;
}

.cloudns-bulk-ttl-inline {
	display: none;
	align-items: center;
	gap: 6px;
}

.cloudns-bulk-ttl-inline-label {
	white-space: nowrap;
	color: #444444;
	font-weight: normal;
	margin: 0;
}

#bulk-action-execute {
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

#bulk-action-execute:hover,
#bulk-action-execute:focus {
	background: #f05f12 !important;
	border-color: #f05f12;
	color: #ffffff !important;
	box-shadow: 0 0 0 2px rgba(255, 107, 26, 0.22);
	outline: none;
}

#bulk-action-execute:active {
	background: #dd520c !important;
	border-color: #dd520c;
	color: #ffffff !important;
}

#bulk-action-execute[disabled] {
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

.cloudns-bulk-domain-switcher label {
	margin: 0;
	font-weight: 600;
	font-size: 12px;
	color: #444444;
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
	.cloudns-bulk-actions-row {
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

#records-table .checkbox-sort-col,
#records-table .checkbox-col {
	width: 42px !important;
	min-width: 42px !important;
	max-width: 42px !important;
	text-align: center !important;
	padding-left: 0 !important;
	padding-right: 0 !important;
	vertical-align: middle !important;
}

#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting,
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting_asc,
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting_desc,
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting_disabled {
	padding-left: 0 !important;
	padding-right: 0 !important;
	text-align: center !important;
}

#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting::before,
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting_asc::before,
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting_desc::before,
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting::after,
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting_asc::after,
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting_desc::after {
	right: 3px !important;
}

#records-table .checkbox-sort-col input,
#records-table .checkbox-col input {
	position: relative;
	z-index: 2;
	display: block;
	margin-left: auto !important;
	margin-right: auto !important;
}

#records-table th.checkbox-sort-col {
	text-align: center !important;
}

#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting::before,
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting_asc::before,
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting_desc::before,
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting::after,
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting_asc::after,
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting_desc::after {
	right: 3px;
}

.cloudns-action-icon {
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

.cloudns-action-grid {
	display: inline-flex;
	align-items: center;
	justify-content: flex-end;
	gap: 3px;
	flex-wrap: nowrap;
	width: 100%;
	vertical-align: middle;
}

.cloudns-action-icon:hover,
.cloudns-action-icon:focus {
	border-color: #f7941d;
	color: #f7941d !important;
	text-decoration: none !important;
	box-shadow: 0 0 0 2px rgba(247, 148, 29, 0.18);
}


/* DomainMonger Patch 1816: crisp, consistent DNS record action icons. */
.cloudns-action-icon svg {
	width: 14px;
	height: 14px;
	display: block;
	fill: none;
	stroke-width: 2.2;
	stroke-linecap: round;
	stroke-linejoin: round;
	pointer-events: none;
}

.cloudns-action-icon-edit,
.cloudns-action-icon-duplicate {
	background: #ffffff !important;
	border-color: #cfd8e3 !important;
	color: #163a5f !important;
}
.cloudns-action-icon-edit svg,
.cloudns-action-icon-duplicate svg {
	stroke: #163a5f !important;
}

.cloudns-action-icon-edit:hover,
.cloudns-action-icon-edit:focus,
.cloudns-action-icon-duplicate:hover,
.cloudns-action-icon-duplicate:focus {
	background: #f3f7fa !important;
	border-color: #163a5f !important;
	color: #214e7a !important;
	box-shadow: 0 0 0 2px rgba(22, 58, 95, 0.12) !important;
}
.cloudns-action-icon-edit:hover svg,
.cloudns-action-icon-edit:focus svg,
.cloudns-action-icon-duplicate:hover svg,
.cloudns-action-icon-duplicate:focus svg {
	stroke: #214e7a !important;
}

.cloudns-action-icon-danger,
.cloudns-action-icon.cloudns-action-icon-danger {
	background: #ffffff !important;
	border-color: #cfd8e3 !important;
	color: #b94a48 !important;
}
.cloudns-action-icon-danger svg,
.cloudns-action-icon.cloudns-action-icon-danger svg {
	stroke: #b94a48 !important;
}

.cloudns-response {
	padding: 10px 12px;
	margin-bottom: 10px;
	border-radius: 4px;
	border: 1px solid #bce8f1;
	background: #d9edf7;
	color: #31708f;
	word-break: break-word;
}

.cloudns-response.cloudns-response-error {
	border-color: #ebccd1;
	background: #f2dede;
	color: #a94442;
}

.cloudns-search-full {
	display: none;
}

/* DM table header vertical alignment pass */
#records-table > thead > tr > th,
#records-table.cloudns-sortable.dataTable > thead > tr > th {
	vertical-align: middle !important;
	line-height: 34px !important;
	padding-top: 6px !important;
	padding-bottom: 6px !important;
}

#records-table > thead > tr > th.addNewRecord.action-col,
#records-table.cloudns-sortable.dataTable > thead > tr > th.addNewRecord.action-col {
	line-height: 1 !important;
	vertical-align: middle !important;
}

#records-table > thead > tr > th.checkbox-sort-col input {
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
#records-table .action-col {
	width: 166px !important;
	min-width: 166px !important;
	max-width: 166px !important;
}

#records-table > tbody > tr > td.action-col,
#records-table > thead > tr > th.action-col {
	padding-left: 6px !important;
	padding-right: 6px !important;
	overflow: visible !important;
}

#records-table .cloudns-action-grid {
	width: 100% !important;
	padding-left: 4px !important;
	box-sizing: border-box !important;
	justify-content: flex-end !important;
}

#records-table .cloudns-action-icon {
	width: 24px !important;
	height: 24px !important;
	min-width: 24px !important;
	margin: 0 1px !important;
}


/* DM TTL/action icon spacing fix
   Make TTL slightly narrower and give that room to the action icon column. */
#records-table .record-ttl-col {
	width: 42px !important;
	min-width: 42px !important;
	max-width: 42px !important;
}

#records-table .action-col {
	width: 174px !important;
	min-width: 174px !important;
	max-width: 174px !important;
}

#records-table.cloudns-sortable.dataTable > thead > tr > th.record-ttl-col,
#records-table > tbody > tr > td.record-ttl-col {
	padding-left: 7px !important;
	padding-right: 7px !important;
}

#records-table.cloudns-monitoring-icons-off .action-col,
#records-table.cloudns-monitoring-icons-off.dataTable thead th.action-col.sorting_disabled {
	width: 116px !important;
	min-width: 116px !important;
	max-width: 116px !important;
}

#records-table.cloudns-monitoring-icons-off .record-ttl-col {
	width: 42px !important;
	min-width: 42px !important;
	max-width: 42px !important;
}


/* DM icon cleanup pass
   Remove the left-side search icon from DNS Records and make the search field standalone. */
.cloudns-record-search {
	border-radius: 5px !important;
	overflow: visible !important;
	background: transparent !important;
	border: 0 !important;
}

.cloudns-record-search input,
.cloudns-record-toolbar #cloudns-record-search {
	min-width: 220px !important;
	width: 220px !important;
	height: 34px !important;
	border: 1px solid #cccccc !important;
	border-radius: 5px !important;
	background: #ffffff !important;
	background-color: #ffffff !important;
	background-image: none !important;
	color: #333333 !important;
	padding: 6px 10px !important;
	box-shadow: none !important;
}

.cloudns-record-search input:focus,
.cloudns-record-toolbar #cloudns-record-search:focus {
	border-color: #f7941d !important;
	box-shadow: 0 0 0 2px rgba(247, 148, 29, 0.18) !important;
	outline: none !important;
}

.cloudns-search-mode-toggle {
	min-width: 82px !important;
	width: auto !important;
	height: 34px !important;
	min-height: 34px !important;
	max-height: 34px !important;
	margin-left: 6px !important;
	padding: 0 10px !important;
	font-size: 12px !important;
	font-weight: 700 !important;
	line-height: 1 !important;
	white-space: nowrap !important;
}








/* DM page-by-page fix pass 1: smaller Host / Points To search toggle. */
.cloudns-record-toolbar .cloudns-search-mode-toggle,
.cloudns-search-mode-toggle {
	min-width: 64px !important;
	height: 34px !important;
	padding: 0 8px !important;
	font-size: 12px !important;
}



/* DM page-by-page fix pass 2: compact Host / Points To toggle. */
.cloudns-record-toolbar .cloudns-record-search {
	gap: 6px !important;
}

.cloudns-record-toolbar .cloudns-search-mode-toggle,
.cloudns-search-mode-toggle,
#cloudns-search-toggle {
	min-width: 48px !important;
	width: auto !important;
	height: 34px !important;
	padding: 0 7px !important;
	font-size: 12px !important;
	line-height: 1 !important;
	white-space: nowrap !important;
}

#cloudns-search-toggle.cloudns-search-mode-points {
	min-width: 66px !important;
}



/* DM page-by-page fix pass 3: final compact search toggle sizing. */
.cloudns-record-toolbar .cloudns-record-search {
	gap: 5px !important;
}

.cloudns-record-toolbar #cloudns-search-toggle,
#cloudns-search-toggle.cloudns-search-mode-toggle,
.cloudns-record-toolbar button#cloudns-search-toggle.btn {
	min-width: 44px !important;
	width: 44px !important;
	max-width: 44px !important;
	height: 28px !important;
	min-height: 28px !important;
	max-height: 28px !important;
	padding: 0 5px !important;
	font-size: 11px !important;
	line-height: 1 !important;
}

.cloudns-record-toolbar #cloudns-search-toggle.cloudns-search-mode-points,
#cloudns-search-toggle.cloudns-search-mode-points {
	min-width: 62px !important;
	width: 62px !important;
	max-width: 62px !important;
}



/* DM Final RC Patch 1: search toggle matches search input height and fits Points To. */
.cloudns-record-toolbar #cloudns-search-toggle,
.cloudns-record-toolbar #cloudns-search-toggle.cloudns-search-mode-toggle,
#cloudns-search-toggle.cloudns-search-mode-toggle,
.cloudns-record-toolbar button#cloudns-search-toggle.btn {
	height: var(--cloudns-toolbar-height, 34px) !important;
	min-height: var(--cloudns-toolbar-height, 34px) !important;
	max-height: var(--cloudns-toolbar-height, 34px) !important;
	width: 76px !important;
	min-width: 76px !important;
	max-width: 76px !important;
	padding: 0 8px !important;
	font-size: 12px !important;
	font-weight: 700 !important;
	line-height: 1 !important;
	white-space: nowrap !important;
	box-sizing: border-box !important;
}

.cloudns-record-toolbar #cloudns-search-toggle.cloudns-search-mode-points,
#cloudns-search-toggle.cloudns-search-mode-points {
	width: 76px !important;
	min-width: 76px !important;
	max-width: 76px !important;
}


/* DomainMonger Patch 495: DNS Records toolbar height/text clipping cleanup.
   Keep the filter dropdown, search input, and Host/Points To toggle on the same 34px height.
   This also fixes the search input growing taller from content-box padding and keeps text vertically centered. */
.cloudns-record-toolbar {
	align-items: center !important;
}

.cloudns-record-toolbar .cloudns-record-filter,
.cloudns-record-toolbar .cloudns-record-search {
	display: inline-flex !important;
	align-items: center !important;
	height: var(--cloudns-toolbar-height, 34px) !important;
	min-height: var(--cloudns-toolbar-height, 34px) !important;
	max-height: var(--cloudns-toolbar-height, 34px) !important;
	box-sizing: border-box !important;
}

.cloudns-record-toolbar #recordsType.form-control,
.cloudns-record-toolbar #cloudns-record-search,
.cloudns-record-toolbar #cloudns-search-toggle,
.cloudns-record-toolbar button#cloudns-search-toggle.btn {
	height: var(--cloudns-toolbar-height, 34px) !important;
	min-height: var(--cloudns-toolbar-height, 34px) !important;
	max-height: var(--cloudns-toolbar-height, 34px) !important;
	box-sizing: border-box !important;
	vertical-align: middle !important;
}

.cloudns-record-toolbar #recordsType.form-control,
.cloudns-record-toolbar #cloudns-record-search {
	padding-top: 6px !important;
	padding-bottom: 6px !important;
	line-height: 1.42857143 !important;
	font-size: 13px !important;
	overflow: visible !important;
}

.cloudns-record-toolbar #cloudns-record-search {
	display: inline-block !important;
}

.cloudns-record-toolbar #cloudns-search-toggle,
.cloudns-record-toolbar button#cloudns-search-toggle.btn {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: center !important;
	line-height: 1.2 !important;
	overflow: visible !important;
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

	/* Patch 1520: sort DNS targets naturally, matching Register DNS. */
	if ($.fn.dataTable && $.fn.dataTable.ext && $.fn.dataTable.ext.type
		&& $.fn.dataTable.ext.type.order) {
		var dnsTargetSortText1520 = function (value) {
			return String(value == null ? '' : value)
				.replace(/<[^>]*>/g, '')
				.replace(/&nbsp;/gi, ' ')
				.trim();
		};
		var compareDnsTargets1520 = function (a, b) {
			return dnsTargetSortText1520(a).localeCompare(
				dnsTargetSortText1520(b),
				undefined,
				{numeric: true, sensitivity: 'base'}
			);
		};

		$.fn.dataTable.ext.type.order['domainmonger-dns-target-asc'] = compareDnsTargets1520;
		$.fn.dataTable.ext.type.order['domainmonger-dns-target-desc'] = function (a, b) {
			return compareDnsTargets1520(b, a);
		};
	}

	/* DomainMonger Patch 1550: shared Register DNS / DNSPlus list controls.
	 * Search and type filtering are display-only. Provider add/edit/delete/TTL
	 * requests continue through the existing DNSPlus actions. */
	var $recordsTableElement = $('#records-table');
	var recordsTable = null;
	var defaultRecordOrder = [[3, 'desc']];
	var initialRecordOrder = defaultRecordOrder;
	var recordSortStateKey = 'domainmonger-cloudns-record-sort:'
		+ String($recordsTableElement.attr('data-cloudns-service') || '')
		+ ':' + String($recordsTableElement.attr('data-cloudns-zone') || '');

	try {
		var savedRecordOrder = JSON.parse(window.sessionStorage.getItem(recordSortStateKey));
		if (Array.isArray(savedRecordOrder) && savedRecordOrder.length === 1
			&& Array.isArray(savedRecordOrder[0]) && savedRecordOrder[0].length === 2) {
			var savedColumn = parseInt(savedRecordOrder[0][0], 10);
			var savedDirection = String(savedRecordOrder[0][1]).toLowerCase();
			if (savedColumn >= 1 && savedColumn <= 4
				&& (savedDirection === 'asc' || savedDirection === 'desc')) {
				initialRecordOrder = [[savedColumn, savedDirection]];
			}
		}
	} catch (recordSortReadError) {
		initialRecordOrder = defaultRecordOrder;
	}

	var getVisibleRecordCheckboxes = function () {
		if (!recordsTable) {
			return $recordsTableElement.find('tbody .record-checkbox:visible');
		}
		return recordsTable.rows({page: 'current', search: 'applied'}).nodes().to$().find('.record-checkbox');
	};

	var syncBulkActionUi = function () {
		var action = $('#bulk-action-select').val();
		$('#bulk-action-execute')
			.toggleClass('cloudns-btn-danger', action === 'delete')
			.toggleClass('cloudns-btn-primary', action !== 'delete');
		if (action === 'ttl') {
			$('.cloudns-bulk-ttl-inline').css('display', 'inline-flex');
		} else {
			$('.cloudns-bulk-ttl-inline').hide();
		}
	};

	var setBulkControlsState = function () {
		var checkedCount = $('.record-checkbox:checked').length;
		var hasSelection = checkedCount > 0;
		var action = $('#bulk-action-select').val();
		var hasAction = action === 'delete' || action === 'ttl';
		$('#bulk-action-select').prop('disabled', !hasSelection);
		$('#mass-ttl-select').prop('disabled', !hasSelection || action !== 'ttl');
		$('#bulk-action-execute').prop('disabled', !hasSelection || !hasAction);
	};

	var updateCheckAllState = function () {
		var $visible = getVisibleRecordCheckboxes();
		var checkedVisible = $visible.filter(':checked').length;
		$('#check-all')
			.prop('checked', $visible.length > 0 && checkedVisible === $visible.length)
			.prop('indeterminate', checkedVisible > 0 && checkedVisible < $visible.length);
	};

	var discardSelectionsOutsideCurrentPage = function () {
		var visible = getVisibleRecordCheckboxes().get();
		$('.record-checkbox').each(function () {
			if (visible.indexOf(this) === -1) {
				this.checked = false;
			}
		});
		updateCheckAllState();
		setBulkControlsState();
	};

	var updateDisplayedRecordCount = function () {
		var displayedCount = recordsTable
			? recordsTable.rows({search: 'applied'}).nodes().to$().find('.record-checkbox').length
			: $recordsTableElement.find('tbody .record-checkbox').length;
		$('.cloudns-zone-record-count-indicator')
			.text('Records: ' + displayedCount)
			.attr('aria-label', 'Records: ' + displayedCount);
	};

	var updatePagerUi = function () {
		if (!recordsTable) {
			return;
		}
		var info = recordsTable.page.info();
		var total = info.recordsDisplay || 0;
		var summary = total > 0 ? ((info.start + 1) + '–' + info.end + ' of ' + total) : '0 of 0';
		$('.cloudns-pagination-summary').text(summary);
		$('.cloudns-page-prev').prop('disabled', info.page <= 0 || total === 0);
		$('.cloudns-page-next').prop('disabled', info.page >= info.pages - 1 || total === 0);
		$('.cloudns-page-size-select').val(info.length === -1 ? 'all' : String(info.length));
	};

	var updateAddRecordLinks = function () {
		var selectedType = String($('#recordsType').val() || 'all');
		$('.cloudns-add-record-link').each(function () {
			var baseHref = String($(this).attr('data-base-href') || $(this).attr('href') || '');
			$(this).attr('data-base-href', baseHref.replace(/&type=[^&]*/i, ''));
			$(this).attr('href', $(this).attr('data-base-href')
				+ (selectedType !== 'all' ? '&type=' + encodeURIComponent(selectedType) : ''));
		});
	};

	if ($.fn.DataTable) {
		recordsTable = $recordsTableElement.DataTable({
			bInfo: false,
			lengthChange: false,
			bFilter: true,
			bPaginate: true,
			pageLength: 25,
			lengthMenu: [[25, 50, -1], [25, 50, 'All']],
			ordering: true,
			orderMulti: false,
			order: initialRecordOrder,
			dom: 't',
			autoWidth: false,
			language: {
				emptyTable: 'No DNS records have been added.',
				zeroRecords: 'No records match the current search and filter.'
			},
			columnDefs: [
				{width: 44, targets: 0, orderDataType: 'dom-checkbox', searchable: false},
				{width: '24%', targets: 1},
				{width: 68, targets: 2},
				{targets: 3, type: 'domainmonger-dns-target'},
				{width: 64, targets: 4},
				{orderable: false, searchable: false, width: 108, targets: 5}
			]
		});

		recordsTable.on('draw.dt', function () {
			updateDisplayedRecordCount();
			updatePagerUi();
			discardSelectionsOutsideCurrentPage();
		});

		recordsTable.on('order.dt', function () {
			var activeOrder = recordsTable.order();
			if (!activeOrder || !activeOrder.length) {
				return;
			}
			var activeColumn = parseInt(activeOrder[0][0], 10);
			var activeDirection = String(activeOrder[0][1]).toLowerCase();
			if (activeColumn < 1 || activeColumn > 4
				|| (activeDirection !== 'asc' && activeDirection !== 'desc')) {
				return;
			}
			try {
				window.sessionStorage.setItem(
					recordSortStateKey,
					JSON.stringify([[activeColumn, activeDirection]])
				);
			} catch (recordSortWriteError) {
				/* Storage can be unavailable in restrictive browser modes. */
			}
		});
	}

	var applyTypeFilter = function () {
		if (!recordsTable) {
			return;
		}
		var selectedType = String($('#recordsType').val() || 'all');
		var escapedType = $.fn.dataTable.util.escapeRegex(selectedType);
		recordsTable.column(2).search(selectedType === 'all' ? '' : '^' + escapedType + '$', true, false);
		recordsTable.page(0).draw();
		updateAddRecordLinks();
	};

	$('#recordsType').on('change', applyTypeFilter);

	$('#cloudns-record-search').on('input', function () {
		if (recordsTable) {
			recordsTable.search(this.value, false, true).page(0).draw();
		}
	});

	$('#cloudns-record-search').on('keydown', function (event) {
		if (event.key === 'Escape') {
			event.preventDefault();
			this.value = '';
			if (recordsTable) {
				recordsTable.search('').page(0).draw();
			}
		}
	});

	$('.cloudns-page-size-select').on('change', function () {
		if (!recordsTable) {
			return;
		}
		var value = String(this.value || '25');
		recordsTable.page.len(value === 'all' ? -1 : parseInt(value, 10)).page(0).draw();
	});

	$(document).off('click.dmCloudnsRecords1612', '.cloudns-page-prev, .cloudns-page-next').on('click.dmCloudnsRecords1612', '.cloudns-page-prev, .cloudns-page-next', function () {
		if (!recordsTable || this.disabled) {
			return;
		}
		recordsTable.page($(this).hasClass('cloudns-page-prev') ? 'previous' : 'next').draw('page');
	});

	$('#check-all').on('click', function (event) {
		event.stopPropagation();
		getVisibleRecordCheckboxes().prop('checked', this.checked);
		updateCheckAllState();
		setBulkControlsState();
	});

	$(document).off('click.dmCloudnsRecords1612', '.record-checkbox').on('click.dmCloudnsRecords1612', '.record-checkbox', function (event) {
		event.stopPropagation();
		updateCheckAllState();
		setBulkControlsState();
	});

	$('#bulk-action-select').on('change', function () {
		syncBulkActionUi();
		setBulkControlsState();
	});

	$('#bulk-action-execute').on('click', function () {
		var action = $('#bulk-action-select').val();
		if (!$('.record-checkbox:checked').length || !action) {
			return;
		}
		if (action === 'delete') {
			$('#records-form input[name="mass-edit-ttl"]').remove();
			$('#records-form input[name="massTtl"]').remove();
			if (confirm('Are you sure you want to delete the selected records?')) {
				$('#records-form').submit();
			}
		} else if (action === 'ttl') {
			var selectedTtl = $('#mass-ttl-select').val();
			if (selectedTtl && confirm('Apply this TTL to the selected records?')) {
				if (!$('#records-form input[name="mass-edit-ttl"]').length) {
					$('#records-form').append('<input type="hidden" name="mass-edit-ttl" value="1">');
				}
				$('#records-form input[name="massTtl"]').remove();
				$('#records-form').append('<input type="hidden" name="massTtl" value="' + selectedTtl + '">');
				$('#records-form').submit();
			}
		}
	});

	syncBulkActionUi();
	setBulkControlsState();
	updateAddRecordLinks();
	if (recordsTable) {
		applyTypeFilter();
	} else {
		updateDisplayedRecordCount();
	}
});
{/literal}
</script>

{literal}
<style type="text/css">
/* DomainMonger Patch 506: DNS Records single-line tools row.
   Keep Records, record-type filter, live search, search-mode toggle, and Delete Zone on one desktop row. */
.cloudns-records-panel .cloudns-record-toolbar-singleline {
    display: flex !important;
    align-items: center !important;
    flex-wrap: nowrap !important;
    gap: 8px !important;
    width: 100% !important;
    margin: 0 0 14px !important;
}

.cloudns-records-panel .cloudns-zone-record-count-indicator {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    height: 34px;
    min-height: 34px;
    padding: 0 11px;
    border: 1px solid #163a5f;
    border-radius: 6px;
    background: #163a5f;
    color: #ffffff;
    font-size: 12px;
    font-weight: 800;
    line-height: 32px;
    white-space: nowrap;
    margin-left: auto;
}

.cloudns-records-panel .cloudns-record-toolbar-singleline .cloudns-record-filter,
.cloudns-records-panel .cloudns-record-toolbar-singleline .cloudns-record-search {
    flex: 0 0 auto !important;
}

.cloudns-records-panel .cloudns-record-toolbar-singleline #recordsType.form-control {
    width: 118px !important;
    min-width: 118px !important;
    max-width: 118px !important;
}

.cloudns-records-panel .cloudns-record-toolbar-singleline #cloudns-record-search {
    width: 125px !important;
    min-width: 125px !important;
    max-width: 125px !important;
}

.cloudns-records-panel .cloudns-record-toolbar-singleline #cloudns-search-toggle,
.cloudns-records-panel .cloudns-record-toolbar-singleline button#cloudns-search-toggle.btn {
    width: 126px !important;
    min-width: 126px !important;
    max-width: 126px !important;
    height: 34px !important;
    min-height: 34px !important;
    max-height: 34px !important;
    padding: 0 8px !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    line-height: 1.1 !important;
    white-space: nowrap !important;
    overflow: visible !important;
}

.cloudns-records-panel .cloudns-zone-delete-button {
    margin-left: 0 !important;
    min-height: 34px;
    line-height: 1.2;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    white-space: nowrap;
    background: #b94a48 !important;
    border-color: #b94a48 !important;
    color: #ffffff !important;
}

.cloudns-records-panel .cloudns-zone-delete-button:hover,
.cloudns-records-panel .cloudns-zone-delete-button:focus {
    background: #a6403e !important;
    border-color: #a6403e !important;
    color: #ffffff !important;
}

@media (max-width: 767px) {
    .cloudns-records-panel .cloudns-record-toolbar-singleline {
        flex-wrap: wrap !important;
        justify-content: flex-start !important;
    }

    .cloudns-records-panel .cloudns-zone-delete-button,
    .cloudns-records-panel .cloudns-zone-record-count-indicator {
        margin-left: 0 !important;
    }
}
</style>
{/literal}

{if $error}
	<div class="notification">You have reached your limit of {$recordsLimit} records per zone.</div><br />
{/if}

{if isset($response) && is_array($response) && (isset($response.description) || isset($response.statusDescription))}
	<div class="cloudns-response {if isset($response.status) && ($response.status == 'error' || $response.status == 'Failed')}cloudns-response-error{/if}">
		{if isset($response.description)}{$response.description|@htmlspecialchars}{else}{$response.statusDescription|@htmlspecialchars}{/if}
	</div>
{/if}

<div class="cloudns-record-page-header">
	<div>
		<strong>DNS Records</strong>
		<span>Manage DNS records{if isset($zone) && $zone != ''} for {$zone|@htmlspecialchars}{/if}</span>
	</div>
</div>

<div class="cloudns-body-panel cloudns-records-panel">
	<div class="cloudns-record-toolbar cloudns-record-toolbar-singleline" aria-label="DNS records tools">
		<div class="cloudns-record-toolbar-left">
			<div class="cloudns-zone-record-count-indicator" aria-label="Records count">Records{if $recordsCount gt '0'}: {$recordsCount}{else}: 0{/if}</div>

			<label class="cloudns-page-size-label" title="Records per page">
				<select class="form-control cloudns-page-size-select" aria-label="Records per page">
					<option value="25" selected="selected">25</option>
					<option value="50">50</option>
					<option value="all">All</option>
				</select>
			</label>

			<div class="cloudns-record-filter">
				<label for="recordsType" class="cloudns-visually-hidden">Filter records by type</label>
				<select id="recordsType" class="form-control" aria-label="Filter records by type">
				<option value="all"{if $defaultType == 'all'} selected="selected"{/if}>All Records</option>
			<option value="A"{if $defaultType == 'A'} selected="selected"{/if}>A</option>
			<option value="CNAME"{if $defaultType == 'CNAME'} selected="selected"{/if}>CNAME</option>
			<option value="MX"{if $defaultType == 'MX'} selected="selected"{/if}>MX</option>
			<option value="NS"{if $defaultType == 'NS'} selected="selected"{/if}>NS</option>
			<option value="SPF"{if $defaultType == 'SPF'} selected="selected"{/if}>SPF</option>
			<option value="SRV"{if $defaultType == 'SRV'} selected="selected"{/if}>SRV</option>
			<option value="TXT"{if $defaultType == 'TXT'} selected="selected"{/if}>TXT</option>
			<option value="WR"{if $defaultType == 'WR'} selected="selected"{/if}>Web Redirect</option>
			<option value="AAAA"{if $defaultType == 'AAAA'} selected="selected"{/if}>AAAA</option>
			<option value="ALIAS"{if $defaultType == 'ALIAS'} selected="selected"{/if}>ALIAS</option>
			<option value="CAA"{if $defaultType == 'CAA'} selected="selected"{/if}>CAA</option>
			<option value="CERT"{if $defaultType == 'CERT'} selected="selected"{/if}>CERT</option>
			<option value="DNAME"{if $defaultType == 'DNAME'} selected="selected"{/if}>DNAME</option>
			<option value="DS"{if $defaultType == 'DS'} selected="selected"{/if}>DS</option>
			<option value="HINFO"{if $defaultType == 'HINFO'} selected="selected"{/if}>HINFO</option>
			<option value="HTTPS"{if $defaultType == 'HTTPS'} selected="selected"{/if}>HTTPS</option>
			<option value="LOC"{if $defaultType == 'LOC'} selected="selected"{/if}>LOC</option>
			<option value="NAPTR"{if $defaultType == 'NAPTR'} selected="selected"{/if}>NAPTR</option>
			<option value="OPENPGPKEY"{if $defaultType == 'OPENPGPKEY'} selected="selected"{/if}>OPENPGPKEY</option>
			<option value="PTR"{if $defaultType == 'PTR'} selected="selected"{/if}>PTR</option>
			<option value="RP"{if $defaultType == 'RP'} selected="selected"{/if}>RP</option>
			<option value="SMIMEA"{if $defaultType == 'SMIMEA'} selected="selected"{/if}>SMIMEA</option>
			<option value="SSHFP"{if $defaultType == 'SSHFP'} selected="selected"{/if}>SSHFP</option>
			<option value="SVCB"{if $defaultType == 'SVCB'} selected="selected"{/if}>SVCB</option>
			<option value="TLSA"{if $defaultType == 'TLSA'} selected="selected"{/if}>TLSA</option>
		
				</select>
			</div>

			<div class="cloudns-record-search">
				<input type="search" id="cloudns-record-search" placeholder="Search records" aria-label="Search DNS records" autocomplete="off" autocapitalize="off" spellcheck="false">
			</div>
		</div>

		<div class="cloudns-record-toolbar-right">
			<div class="cloudns-pagination-row cloudns-pagination-top" aria-label="DNS records pagination">
				<button type="button" class="cloudns-page-button cloudns-page-prev">Previous</button>
				<span class="cloudns-pagination-summary" aria-live="polite">0 of 0</span>
				<button type="button" class="cloudns-page-button cloudns-page-next">Next</button>
			</div>

			<a class="btn cloudns-switch-style-button cloudns-btn-primary cloudns-add-record-link" data-base-href="clientarea.php?action=productdetails&id={$serviceid}&customAction=add-new-record&zone={$zone}" href="clientarea.php?action=productdetails&id={$serviceid}&customAction=add-new-record&zone={$zone}">+ Add Record</a>

		</div>
	</div>

{literal}
<style type="text/css">
/* Patch 154: ClouDNS DNS List sort-arrow spacing.
   Keep the approved ClouDNS text-arrow look, but give the up/down arrows more
   vertical breathing room and a consistent size on the DNS Records table. */

#records-table.cloudns-sortable.dataTable thead th.sorting,
#records-table.cloudns-sortable.dataTable thead th.sorting_asc,
#records-table.cloudns-sortable.dataTable thead th.sorting_desc {
    padding-right: 32px !important;
}

#records-table.cloudns-sortable.dataTable thead th.sorting::before,
#records-table.cloudns-sortable.dataTable thead th.sorting_asc::before,
#records-table.cloudns-sortable.dataTable thead th.sorting_desc::before,
#records-table.cloudns-sortable.dataTable thead th.sorting::after,
#records-table.cloudns-sortable.dataTable thead th.sorting_asc::after,
#records-table.cloudns-sortable.dataTable thead th.sorting_desc::after {
    right: 10px !important;
    font-size: 12px !important;
    line-height: 1 !important;
    opacity: 1 !important;
}

#records-table.cloudns-sortable.dataTable thead th.sorting::before,
#records-table.cloudns-sortable.dataTable thead th.sorting_asc::before,
#records-table.cloudns-sortable.dataTable thead th.sorting_desc::before {
    top: calc(50% - 14px) !important;
}

#records-table.cloudns-sortable.dataTable thead th.sorting::after,
#records-table.cloudns-sortable.dataTable thead th.sorting_asc::after,
#records-table.cloudns-sortable.dataTable thead th.sorting_desc::after {
    top: calc(50% + 4px) !important;
}

/* The checkbox sort column is narrow, so keep it aligned without shrinking the arrows. */
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting,
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting_asc,
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting_desc,
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting_disabled {
    width: 52px !important;
    min-width: 52px !important;
    max-width: 52px !important;
}

#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting::before,
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting_asc::before,
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting_desc::before,
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting::after,
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting_asc::after,
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting_desc::after {
    right: 5px !important;
}
</style>
{/literal}

{literal}
<style type="text/css">
/* Patch 155: ClouDNS DNS List Host / Points To arrow spacing.
   The Host and Points To columns are wide, so their DataTables arrows were pushed
   to the far right of the header cell. Keep the checkbox/type/TTL behavior from
   Patch 154, but draw Host and Points To arrows inline next to the labels. */

#records-table.cloudns-sortable.dataTable thead th.record-host-col.sorting::before,
#records-table.cloudns-sortable.dataTable thead th.record-host-col.sorting_asc::before,
#records-table.cloudns-sortable.dataTable thead th.record-host-col.sorting_desc::before,
#records-table.cloudns-sortable.dataTable thead th.record-points-col.sorting::before,
#records-table.cloudns-sortable.dataTable thead th.record-points-col.sorting_asc::before,
#records-table.cloudns-sortable.dataTable thead th.record-points-col.sorting_desc::before {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
    content: "" !important;
}

#records-table.cloudns-sortable.dataTable thead th.record-host-col.sorting::after,
#records-table.cloudns-sortable.dataTable thead th.record-host-col.sorting_asc::after,
#records-table.cloudns-sortable.dataTable thead th.record-host-col.sorting_desc::after,
#records-table.cloudns-sortable.dataTable thead th.record-points-col.sorting::after,
#records-table.cloudns-sortable.dataTable thead th.record-points-col.sorting_asc::after,
#records-table.cloudns-sortable.dataTable thead th.record-points-col.sorting_desc::after {
    content: "" !important;
    position: static !important;
    display: inline-block !important;
    visibility: visible !important;
    opacity: 1 !important;
    width: 14px !important;
    height: 30px !important;
    margin-left: 12px !important;
    margin-right: 0 !important;
    padding: 0 !important;
    transform: none !important;
    vertical-align: -10px !important;
    background-repeat: no-repeat !important;
    background-position: center center !important;
    background-size: 14px 30px !important;
    border: 0 !important;
    font-size: 0 !important;
    line-height: 0 !important;
}

#records-table.cloudns-sortable.dataTable thead th.record-host-col.sorting::after,
#records-table.cloudns-sortable.dataTable thead th.record-points-col.sorting::after {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='30' viewBox='0 0 14 30'%3E%3Cpath fill='rgba(255,255,255,.78)' d='M7 1L13 10H1z'/%3E%3Cpath fill='rgba(255,255,255,.78)' d='M7 29L1 20h12z'/%3E%3C/svg%3E") !important;
}

#records-table.cloudns-sortable.dataTable thead th.record-host-col.sorting_asc::after,
#records-table.cloudns-sortable.dataTable thead th.record-points-col.sorting_asc::after {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='30' viewBox='0 0 14 30'%3E%3Cpath fill='white' d='M7 1L13 10H1z'/%3E%3Cpath fill='rgba(255,255,255,.42)' d='M7 29L1 20h12z'/%3E%3C/svg%3E") !important;
}

#records-table.cloudns-sortable.dataTable thead th.record-host-col.sorting_desc::after,
#records-table.cloudns-sortable.dataTable thead th.record-points-col.sorting_desc::after {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='30' viewBox='0 0 14 30'%3E%3Cpath fill='rgba(255,255,255,.42)' d='M7 1L13 10H1z'/%3E%3Cpath fill='white' d='M7 29L1 20h12z'/%3E%3C/svg%3E") !important;
}
</style>
{/literal}

{literal}
<style type="text/css">
/* Patch 156: ClouDNS DNS List remaining sort arrows.
   Patch 155 fixed Host and Points To. This scales the remaining checkbox,
   Type, and TTL arrows so all DNS List arrows match in size. */

/* Hide the smaller text-arrow pair on the remaining columns. */
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting::before,
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting_asc::before,
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting_desc::before,
#records-table.cloudns-sortable.dataTable thead th.record-type-col.sorting::before,
#records-table.cloudns-sortable.dataTable thead th.record-type-col.sorting_asc::before,
#records-table.cloudns-sortable.dataTable thead th.record-type-col.sorting_desc::before,
#records-table.cloudns-sortable.dataTable thead th.record-ttl-col.sorting::before,
#records-table.cloudns-sortable.dataTable thead th.record-ttl-col.sorting_asc::before,
#records-table.cloudns-sortable.dataTable thead th.record-ttl-col.sorting_desc::before {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
    content: "" !important;
}

/* Use the same larger SVG stack as Host and Points To. */
#records-table.cloudns-sortable.dataTable thead th.record-type-col.sorting::after,
#records-table.cloudns-sortable.dataTable thead th.record-type-col.sorting_asc::after,
#records-table.cloudns-sortable.dataTable thead th.record-type-col.sorting_desc::after,
#records-table.cloudns-sortable.dataTable thead th.record-ttl-col.sorting::after,
#records-table.cloudns-sortable.dataTable thead th.record-ttl-col.sorting_asc::after,
#records-table.cloudns-sortable.dataTable thead th.record-ttl-col.sorting_desc::after {
    content: "" !important;
    position: static !important;
    display: inline-block !important;
    visibility: visible !important;
    opacity: 1 !important;
    width: 14px !important;
    height: 30px !important;
    margin-left: 10px !important;
    margin-right: 0 !important;
    padding: 0 !important;
    transform: none !important;
    vertical-align: -10px !important;
    background-repeat: no-repeat !important;
    background-position: center center !important;
    background-size: 14px 30px !important;
    border: 0 !important;
    font-size: 0 !important;
    line-height: 0 !important;
}

/* Checkbox column has no text label, so keep its arrow stack positioned beside the checkbox. */
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting::after,
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting_asc::after,
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting_desc::after {
    content: "" !important;
    position: absolute !important;
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    width: 14px !important;
    height: 30px !important;
    right: 3px !important;
    top: 50% !important;
    margin: 0 !important;
    padding: 0 !important;
    transform: translateY(-50%) !important;
    background-repeat: no-repeat !important;
    background-position: center center !important;
    background-size: 14px 30px !important;
    border: 0 !important;
    font-size: 0 !important;
    line-height: 0 !important;
}

/* Unsorted state */
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting::after,
#records-table.cloudns-sortable.dataTable thead th.record-type-col.sorting::after,
#records-table.cloudns-sortable.dataTable thead th.record-ttl-col.sorting::after {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='30' viewBox='0 0 14 30'%3E%3Cpath fill='rgba(255,255,255,.78)' d='M7 1L13 10H1z'/%3E%3Cpath fill='rgba(255,255,255,.78)' d='M7 29L1 20h12z'/%3E%3C/svg%3E") !important;
}

/* Ascending */
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting_asc::after,
#records-table.cloudns-sortable.dataTable thead th.record-type-col.sorting_asc::after,
#records-table.cloudns-sortable.dataTable thead th.record-ttl-col.sorting_asc::after {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='30' viewBox='0 0 14 30'%3E%3Cpath fill='white' d='M7 1L13 10H1z'/%3E%3Cpath fill='rgba(255,255,255,.42)' d='M7 29L1 20h12z'/%3E%3C/svg%3E") !important;
}

/* Descending */
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting_desc::after,
#records-table.cloudns-sortable.dataTable thead th.record-type-col.sorting_desc::after,
#records-table.cloudns-sortable.dataTable thead th.record-ttl-col.sorting_desc::after {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='30' viewBox='0 0 14 30'%3E%3Cpath fill='rgba(255,255,255,.42)' d='M7 1L13 10H1z'/%3E%3Cpath fill='white' d='M7 29L1 20h12z'/%3E%3C/svg%3E") !important;
}
</style>
{/literal}

{literal}
<style type="text/css">
/* Patch 1518: Keep all DNSPlus record columns inside the available client-area panel.
   Fixed legacy desktop widths exceeded the module width and were clipped by the
   rounded table shell. Host keeps a useful fixed share, Points To absorbs the
   remaining room, and TTL/+Add remain visible. */
.cloudns-table-shell .dataTables_wrapper {
    width: 100% !important;
    min-width: 0 !important;
}

#records-table,
#records-table.dataTable,
#records-table.cloudns-sortable.dataTable {
    width: 100% !important;
    min-width: 0 !important;
    max-width: 100% !important;
    table-layout: fixed !important;
}

#records-table th,
#records-table td {
    box-sizing: border-box !important;
}

#records-table .checkbox-sort-col,
#records-table .checkbox-col,
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting,
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting_asc,
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting_desc,
#records-table.cloudns-sortable.dataTable thead th.checkbox-sort-col.sorting_disabled {
    width: 44px !important;
    min-width: 44px !important;
    max-width: 44px !important;
}

#records-table .record-host-col {
    width: 24% !important;
    min-width: 0 !important;
    max-width: none !important;
}

#records-table .record-type-col {
    width: 68px !important;
    min-width: 68px !important;
    max-width: 68px !important;
}

#records-table .record-points-col {
    width: auto !important;
    min-width: 0 !important;
    max-width: none !important;
}

#records-table .record-ttl-col,
#records-table.cloudns-monitoring-icons-off .record-ttl-col {
    width: 64px !important;
    min-width: 64px !important;
    max-width: 64px !important;
}

#records-table .action-col,
#records-table.cloudns-monitoring-icons-off .action-col,
#records-table.cloudns-monitoring-icons-off.dataTable thead th.action-col.sorting_disabled {
    width: 108px !important;
    min-width: 108px !important;
    max-width: 108px !important;
}

#records-table:not(.cloudns-monitoring-icons-off) .action-col {
    width: 150px !important;
    min-width: 150px !important;
    max-width: 150px !important;
}

#records-table.cloudns-sortable.dataTable thead th.record-type-col,
#records-table.cloudns-sortable.dataTable thead th.record-ttl-col {
    padding-left: 7px !important;
    padding-right: 7px !important;
    white-space: nowrap !important;
}

#records-table.cloudns-sortable.dataTable thead th.record-type-col.sorting::after,
#records-table.cloudns-sortable.dataTable thead th.record-type-col.sorting_asc::after,
#records-table.cloudns-sortable.dataTable thead th.record-type-col.sorting_desc::after,
#records-table.cloudns-sortable.dataTable thead th.record-ttl-col.sorting::after,
#records-table.cloudns-sortable.dataTable thead th.record-ttl-col.sorting_asc::after,
#records-table.cloudns-sortable.dataTable thead th.record-ttl-col.sorting_desc::after {
    margin-left: 5px !important;
}

#records-table td.record-host-col,
#records-table td.record-points-col {
    overflow: hidden !important;
}

#records-table td.record-host-col .overflowDiv,
#records-table td.record-points-col .overflowDiv {
    max-width: 100% !important;
}
</style>
{/literal}

{literal}
<style type="text/css">
/* DomainMonger Patch 1550: shared DNS Records controls and pagination. */
.cloudns-record-page-header {
	display: flex !important;
	align-items: center !important;
	justify-content: space-between !important;
	gap: 14px !important;
	margin: 0 0 10px !important;
	padding: 13px 16px !important;
	border-radius: 7px !important;
	background: #163a5f !important;
	color: #ffffff !important;
}

.cloudns-record-page-header strong {
	display: block !important;
	margin: 0 !important;
	color: #ffffff !important;
	font-size: 16px !important;
	font-weight: 700 !important;
	line-height: 1.25 !important;
}

.cloudns-record-page-header span {
	display: block !important;
	margin-top: 2px !important;
	color: rgba(255, 255, 255, .80) !important;
	font-size: 12px !important;
	font-weight: 400 !important;
	line-height: 1.35 !important;
}

.cloudns-records-panel .cloudns-record-toolbar-singleline {
	display: flex !important;
	align-items: center !important;
	flex-wrap: wrap !important;
	gap: 8px !important;
	width: 100% !important;
	margin: 0 0 8px !important;
}

.cloudns-records-panel .cloudns-record-filter,
.cloudns-records-panel .cloudns-page-size-label,
.cloudns-records-panel .cloudns-record-search {
	display: inline-flex !important;
	align-items: center !important;
	gap: 6px !important;
	margin: 0 !important;
}

.cloudns-records-panel .cloudns-page-size-label {
	font-size: 13px !important;
	font-weight: 600 !important;
	color: #333333 !important;
	white-space: nowrap !important;
}

.cloudns-records-panel .cloudns-record-toolbar-singleline #recordsType.form-control {
	width: 126px !important;
	min-width: 126px !important;
	max-width: 126px !important;
}

.cloudns-records-panel .cloudns-page-size-select.form-control {
	width: 72px !important;
	min-width: 72px !important;
	max-width: 72px !important;
	height: 34px !important;
	padding: 6px 9px !important;
	font-size: 13px !important;
}

.cloudns-records-panel .cloudns-record-search {
	border: 1px solid #d6d6d6 !important;
	border-radius: 5px !important;
	background: #ffffff !important;
	overflow: hidden !important;
}

.cloudns-records-panel .cloudns-record-toolbar-singleline #cloudns-record-search {
	display: block !important;
	width: 190px !important;
	min-width: 190px !important;
	max-width: 190px !important;
	height: 34px !important;
	margin: 0 !important;
	padding: 6px 10px !important;
	border: 0 !important;
	border-radius: 0 !important;
	background: #ffffff !important;
	box-shadow: none !important;
	font-size: 13px !important;
	line-height: 1.42857143 !important;
}

.cloudns-records-panel .cloudns-record-toolbar-singleline #cloudns-record-search:focus {
	box-shadow: inset 0 0 0 2px rgba(245, 130, 32, 0.22) !important;
	outline: none !important;
}

.cloudns-records-panel .cloudns-add-record-link {
	background: #f58220 !important;
	background-color: #f58220 !important;
	border-color: #f58220 !important;
	color: #ffffff !important;
}

.cloudns-records-panel .cloudns-add-record-link:hover,
.cloudns-records-panel .cloudns-add-record-link:focus {
	background: #d8741f !important;
	background-color: #d8741f !important;
	border-color: #d8741f !important;
	color: #ffffff !important;
}

.cloudns-records-panel .cloudns-zone-record-count-indicator {
	margin-left: auto !important;
	background: #163a5f !important;
	border-color: #163a5f !important;
	color: #ffffff !important;
}

.cloudns-pagination-row {
	display: flex !important;
	align-items: center !important;
	justify-content: center !important;
	gap: 8px !important;
	min-height: 34px !important;
}

.cloudns-pagination-top {
	margin: 0 0 10px !important;
}

.cloudns-pagination-bottom {
	margin: 0 !important;
}

.cloudns-page-button {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: center !important;
	min-width: 82px !important;
	height: 34px !important;
	padding: 0 13px !important;
	border: 1px solid #163a5f !important;
	border-radius: 5px !important;
	background: #163a5f !important;
	color: #ffffff !important;
	font-size: 13px !important;
	font-weight: 700 !important;
	line-height: 1 !important;
	text-decoration: none !important;
	box-shadow: none !important;
}

.cloudns-page-button:hover,
.cloudns-page-button:focus {
	border-color: #214e7a !important;
	background: #214e7a !important;
	color: #ffffff !important;
	outline: none !important;
}

.cloudns-page-button:disabled {
	border-color: #c9cfd6 !important;
	background: #e7eaee !important;
	color: #7a838c !important;
	cursor: not-allowed !important;
	opacity: 1 !important;
}

.cloudns-pagination-summary {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: center !important;
	min-width: 96px !important;
	height: 34px !important;
	padding: 0 8px !important;
	color: #333333 !important;
	font-size: 13px !important;
	font-weight: 600 !important;
	line-height: 1 !important;
	white-space: nowrap !important;
}

.cloudns-record-bottom-row {
	display: flex !important;
	align-items: center !important;
	justify-content: center !important;
	gap: 12px !important;
	margin: 10px 0 0 !important;
	position: relative !important;
}

.cloudns-record-bottom-row .cloudns-add-record-link {
	margin-left: auto !important;
}

.cloudns-record-bottom-row .cloudns-pagination-bottom {
	position: absolute !important;
	left: 50% !important;
	transform: translateX(-50%) !important;
}

#records-table.dataTable tbody td.dataTables_empty {
	padding: 18px 12px !important;
	text-align: center !important;
	color: #666666 !important;
}

@media only screen and (max-width: 900px) {
	.cloudns-records-panel .cloudns-zone-record-count-indicator {
		margin-left: 0 !important;
	}
}

/* DomainMonger Patch 1551: align the DNSPlus header controls with Register DNS.
   Header only: search behavior and footer controls are intentionally unchanged. */
.cloudns-records-panel .cloudns-record-toolbar-singleline {
	align-items: center !important;
	flex-wrap: nowrap !important;
	justify-content: space-between !important;
	gap: 8px !important;
	min-height: 32px !important;
}

.cloudns-records-panel .cloudns-record-toolbar-left,
.cloudns-records-panel .cloudns-record-toolbar-right {
	display: inline-flex !important;
	align-items: center !important;
	flex-flow: row nowrap !important;
	gap: 6px !important;
	white-space: nowrap !important;
}

.cloudns-records-panel .cloudns-record-toolbar-left {
	flex: 1 1 auto !important;
	min-width: 0 !important;
}

.cloudns-records-panel .cloudns-record-toolbar-right {
	flex: 0 0 auto !important;
	justify-content: flex-end !important;
	margin-left: auto !important;
}

.cloudns-records-panel .cloudns-record-toolbar-left .cloudns-zone-record-count-indicator,
.cloudns-records-panel .cloudns-record-toolbar-left select.form-control,
.cloudns-records-panel .cloudns-record-toolbar-left #cloudns-record-search,
.cloudns-records-panel .cloudns-record-toolbar-right .cloudns-page-button,
.cloudns-records-panel .cloudns-record-toolbar-right .cloudns-add-record-link,
.cloudns-records-panel .cloudns-record-toolbar-right .cloudns-zone-delete-button {
	height: 32px !important;
	min-height: 32px !important;
	max-height: 32px !important;
	box-sizing: border-box !important;
}

.cloudns-records-panel .cloudns-record-toolbar-left .cloudns-zone-record-count-indicator {
	margin-left: 0 !important;
	padding: 0 9px !important;
	font-size: 12px !important;
	line-height: 30px !important;
}

.cloudns-records-panel .cloudns-record-toolbar-left #recordsType.form-control {
	width: 92px !important;
	min-width: 92px !important;
	max-width: 92px !important;
	padding: 5px 8px !important;
}

.cloudns-records-panel .cloudns-record-toolbar-left .cloudns-page-size-select.form-control {
	width: 54px !important;
	min-width: 54px !important;
	max-width: 54px !important;
	padding: 5px 7px !important;
}

.cloudns-records-panel .cloudns-record-toolbar-left #cloudns-record-search {
	width: 125px !important;
	min-width: 125px !important;
	max-width: 125px !important;
	padding: 5px 9px !important;
}

.cloudns-records-panel .cloudns-record-toolbar-right .cloudns-pagination-top {
	margin: 0 !important;
	min-height: 32px !important;
	gap: 6px !important;
}

.cloudns-records-panel .cloudns-record-toolbar-right .cloudns-page-button {
	min-width: 60px !important;
	width: 60px !important;
	padding: 0 7px !important;
	font-size: 12px !important;
}

.cloudns-records-panel .cloudns-record-toolbar-right .cloudns-pagination-summary {
	min-width: 72px !important;
	width: 72px !important;
	height: 32px !important;
	padding: 0 3px !important;
	font-size: 12px !important;
}

.cloudns-records-panel .cloudns-record-toolbar-right .cloudns-add-record-link,
.cloudns-records-panel .cloudns-record-toolbar-right .cloudns-zone-delete-button {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: center !important;
	margin: 0 !important;
	padding: 0 9px !important;
	font-size: 12px !important;
	line-height: 30px !important;
	white-space: nowrap !important;
}

@media only screen and (max-width: 1000px) {
	.cloudns-records-panel .cloudns-record-toolbar-singleline {
		align-items: flex-start !important;
		flex-wrap: wrap !important;
	}

	.cloudns-records-panel .cloudns-record-toolbar-right {
		justify-content: flex-end !important;
		margin-left: 0 !important;
		width: 100% !important;
	}
}

/* Patch 1560: shared DNS record-table header typography.
   Use an explicit common font stack so DNSPlus and Register DNS render the
   labels identically instead of inheriting different table fonts. */
#records-table > thead > tr > th,
#records-table.cloudns-sortable.dataTable > thead > tr > th {
	font-family: Arial, Helvetica, sans-serif !important;
	font-size: 12px !important;
	font-style: normal !important;
	font-weight: 700 !important;
	letter-spacing: .02em !important;
	line-height: 1.25 !important;
	padding-top: 10px !important;
	padding-bottom: 10px !important;
	text-transform: uppercase !important;
	vertical-align: middle !important;
	white-space: nowrap !important;
}

@media only screen and (max-width: 650px) {
	.cloudns-records-panel .cloudns-record-toolbar-singleline #cloudns-record-search {
		width: 180px !important;
		min-width: 180px !important;
		max-width: 180px !important;
	}

	.cloudns-record-bottom-row {
		align-items: stretch !important;
		flex-direction: column !important;
	}

	.cloudns-record-bottom-row .cloudns-pagination-bottom {
		position: static !important;
		left: auto !important;
		transform: none !important;
	}

	.cloudns-record-bottom-row .cloudns-add-record-link {
		align-self: flex-end !important;
		margin-left: 0 !important;
	}
}
</style>
{/literal}

<form id="records-form" method="post" action="clientarea.php?action=productdetails&id={$serviceid}&customAction=zone-settings&zone={$zone}">
	<input type="hidden" name="mass-delete" value="1">
	<div class="table-responsive cloudns-table-shell">
		<table id="records-table" class="table table-hover table-bordered dns-records cloudns-sortable {if !isset($monitoringIconsEnabled) || !$monitoringIconsEnabled}cloudns-monitoring-icons-off{/if}" data-cloudns-service="{$serviceid|@htmlspecialchars}" data-cloudns-zone="{$zone|@htmlspecialchars}" style="width:100%;">
			<thead>
			<tr class="active" style="background-color: #f5f5f5;">
				<th class="checkbox-sort-col"><input type="checkbox" id="check-all"></th>
				<th class="record-host-col">Host</th>
				<th class="record-type-col">Type</th>
				<th class="record-points-col">Value</th>
				<th class="ttl record-ttl-col">TTL</th>
				<th class="text-right action-col">Actions</th>
			</tr>
			</thead>
			<tbody>
			{foreach from=$records item=record}
				<tr>
					<td class="checkbox-col"><input type="checkbox" name="records[]" class="record-checkbox" value="{$record.id}"></td>
					<td class="overflow record-host-col" title="{$record.shortHost.title|@htmlspecialchars}"><span class="cloudns-search-full">{$record.shortHost.title|@htmlspecialchars}</span><div class="overflowDiv"><div>{$record.shortHost.title|@htmlspecialchars}</div></div></td>
					<td class="record-type-col">{$record.type}</td>
					<td class="overflow record-points-col" title="{$record.shortRecord.title|@htmlspecialchars}"><span class="cloudns-search-full">{$record.shortRecord.title|@htmlspecialchars}</span><div class="overflowDiv overflowRecordRecord"><div>{$record.shortRecord.title|@htmlspecialchars}</div></div></td>
					<td class="record-ttl-col">{$record.ttl_seconds}</td>
					<td class="text-right action-col">
						{if isset($monitoringIconsEnabled) && $monitoringIconsEnabled}
							<div class="cloudns-action-grid">
								<a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=edit-record&zone={$zone}&dns_record_id={$record.id}" title="Edit Record" aria-label="Edit Record" class="cloudns-action-icon cloudns-action-icon-edit"><svg viewBox="0 0 18 18" aria-hidden="true" focusable="false"><path d="M3 13.5V15h1.5L14 5.5 12.5 4 3 13.5z"></path><path d="M11.5 5l1.5 1.5"></path></svg></a>
								{if $failoverChecks > 0 && $foColumn == 1 && ($record.type == 'A' || $record.type == 'AAAA')}
									<a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=get-failover-settings&zone={$zone}&dns_record_id={$record.id}" class="cloudns-action-icon" {if $record.failover == '1'} title="Edit DNS Failover & Monitoring" {else} title="Activate DNS Failover & Monitoring"{/if}>&#8644;</a>
								{/if}
								<a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=add-new-record&zone={$zone}&source_record={$record.id}" title="Duplicate Record" aria-label="Duplicate Record" class="cloudns-action-icon cloudns-action-icon-duplicate"><svg viewBox="0 0 18 18" aria-hidden="true" focusable="false"><rect x="6.5" y="6.5" width="9" height="9" rx="1.25"></rect><path d="M4.5 12.5H3.75A1.25 1.25 0 0 1 2.5 11.25v-7.5A1.25 1.25 0 0 1 3.75 2.5h7.5A1.25 1.25 0 0 1 12.5 3.75v.75"></path></svg></a>
								{if $record.type == 'A' || $record.type == 'AAAA'}
									<a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=activate-dynamic-url&zone={$zone}&dns_record_id={$record.id}" title="Active Dynamic URL" class="cloudns-action-icon">&#8635;</a>
								{/if}
								{if $record.type == 'A' || $record.type == 'AAAA' || $record.type == 'CNAME' || $record.type == 'NS' || $record.type == 'MX' || $record.type == 'TXT' || $record.type == 'SPF' || $record.type == 'SRV' || $record.type == 'CAA' || $record.type == 'DS' || $record.type == 'TLSA' || $record.type == 'HTTPS' || $record.type == 'SVCB'}
									<a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=create-monitoring-check&zone={$zone}&dns_record_id={$record.id}" title="Create New Monitoring Check" class="cloudns-action-icon" onclick="return confirm('Create a new Monitoring Check for this record?');">&#128065;</a>
								{/if}
								<a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=delete-record&zone={$zone}&record={$record.id}" title="Delete Record" aria-label="Delete Record" onclick="return confirm('Are you sure you want to delete this record?');" class="cloudns-action-icon cloudns-action-icon-danger"><svg viewBox="0 0 18 18" aria-hidden="true" focusable="false"><path d="M3 5h12"></path><path d="M7 5V3h4v2"></path><path d="M5 5l1 10h6l1-10"></path><path d="M8 8v4"></path><path d="M10 8v4"></path></svg></a>
							</div>
						{else}
							<div class="cloudns-action-grid">
								<a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=edit-record&zone={$zone}&dns_record_id={$record.id}" title="Edit Record" aria-label="Edit Record" class="cloudns-action-icon cloudns-action-icon-edit"><svg viewBox="0 0 18 18" aria-hidden="true" focusable="false"><path d="M3 13.5V15h1.5L14 5.5 12.5 4 3 13.5z"></path><path d="M11.5 5l1.5 1.5"></path></svg></a>
								<a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=add-new-record&zone={$zone}&source_record={$record.id}" title="Duplicate Record" aria-label="Duplicate Record" class="cloudns-action-icon cloudns-action-icon-duplicate"><svg viewBox="0 0 18 18" aria-hidden="true" focusable="false"><rect x="6.5" y="6.5" width="9" height="9" rx="1.25"></rect><path d="M4.5 12.5H3.75A1.25 1.25 0 0 1 2.5 11.25v-7.5A1.25 1.25 0 0 1 3.75 2.5h7.5A1.25 1.25 0 0 1 12.5 3.75v.75"></path></svg></a>
								{if $record.type == 'A' || $record.type == 'AAAA'}
									<a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=activate-dynamic-url&zone={$zone}&dns_record_id={$record.id}" title="Active Dynamic URL" class="cloudns-action-icon">&#8635;</a>
								{/if}
								<a href="clientarea.php?action=productdetails&id={$serviceid}&customAction=delete-record&zone={$zone}&record={$record.id}" title="Delete Record" aria-label="Delete Record" onclick="return confirm('Are you sure you want to delete this record?');" class="cloudns-action-icon cloudns-action-icon-danger"><svg viewBox="0 0 18 18" aria-hidden="true" focusable="false"><path d="M3 5h12"></path><path d="M7 5V3h4v2"></path><path d="M5 5l1 10h6l1-10"></path><path d="M8 8v4"></path><path d="M10 8v4"></path></svg></a>
							</div>
						{/if}
					</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>
	<div class="cloudns-record-footer-row">
		<div class="cloudns-bulk-actions-row">
			<button type="button" id="bulk-action-execute" class="btn cloudns-execute-button cloudns-switch-style-button cloudns-btn-primary" disabled>Apply</button>
			<select id="bulk-action-select" class="form-control cloudns-bulk-action-select" disabled>
				<option value="" selected="selected">Choose Action</option>
				<option value="delete">Delete Selected</option>
				<option value="ttl">Change TTL</option>
			</select>
			<div class="cloudns-bulk-ttl-inline">
				<label for="mass-ttl-select" class="cloudns-bulk-ttl-inline-label">TTL</label>
				<select id="mass-ttl-select" name="massTtl" class="form-control" form="records-form" disabled>
					{foreach from=$ttls key=ttlLabel item=ttlValue}
						<option value="{$ttlValue}"{if $ttlValue == '3600'} selected="selected"{/if}>{$ttlLabel}</option>
					{/foreach}
				</select>
			</div>
		</div>

		<div class="cloudns-record-bottom-row">
			<div class="cloudns-pagination-row cloudns-pagination-bottom" aria-label="DNS records pagination">
				<button type="button" class="cloudns-page-button cloudns-page-prev">Previous</button>
				<span class="cloudns-pagination-summary" aria-live="polite">0 of 0</span>
				<button type="button" class="cloudns-page-button cloudns-page-next">Next</button>
			</div>
			<a class="btn cloudns-switch-style-button cloudns-btn-primary cloudns-add-record-link" data-base-href="clientarea.php?action=productdetails&id={$serviceid}&customAction=add-new-record&zone={$zone}" href="clientarea.php?action=productdetails&id={$serviceid}&customAction=add-new-record&zone={$zone}">+ Add Record</a>
		</div>
	</div>

{literal}
<style type="text/css">
/* DomainMonger Patch 1552/1553: DNSPlus toolbar/footer cleanup and Apply-first bulk actions. */
.cloudns-records-panel .cloudns-page-size-label {
	gap: 0 !important;
}

.cloudns-records-panel .cloudns-record-toolbar-left .cloudns-page-size-select.form-control {
	width: 54px !important;
	min-width: 54px !important;
	max-width: 54px !important;
}

.cloudns-records-panel .cloudns-record-toolbar-left #recordsType.form-control {
	width: 126px !important;
	min-width: 126px !important;
	max-width: 126px !important;
	padding-left: 5px !important;
	padding-right: 24px !important;
}

.cloudns-record-footer-row {
	display: flex !important;
	align-items: center !important;
	justify-content: space-between !important;
	gap: 12px !important;
	margin: 10px 0 0 !important;
	width: 100% !important;
}

.cloudns-record-footer-row .cloudns-bulk-actions-row,
.cloudns-record-footer-row .cloudns-record-bottom-row {
	display: inline-flex !important;
	align-items: center !important;
	flex-flow: row nowrap !important;
	gap: 6px !important;
	margin: 0 !important;
	position: static !important;
	white-space: nowrap !important;
}

.cloudns-record-footer-row .cloudns-record-bottom-row {
	justify-content: flex-end !important;
	margin-left: auto !important;
}

.cloudns-record-footer-row .cloudns-pagination-bottom {
	position: static !important;
	left: auto !important;
	transform: none !important;
	margin: 0 !important;
	gap: 6px !important;
}

.cloudns-record-footer-row .cloudns-add-record-link {
	margin: 0 !important;
}

/* Patch 1554: keep Apply typography identical when switching between the
 * primary Change TTL state and the danger Delete Selected state. */
#bulk-action-execute,
#bulk-action-execute.cloudns-btn-primary,
#bulk-action-execute.cloudns-btn-danger {
	font-size: 13px !important;
	font-weight: 700 !important;
	line-height: 1 !important;
	letter-spacing: normal !important;
}

@media only screen and (max-width: 900px) {
	.cloudns-record-footer-row {
		align-items: flex-start !important;
		flex-wrap: wrap !important;
	}

	.cloudns-record-footer-row .cloudns-record-bottom-row {
		margin-left: 0 !important;
		width: 100% !important;
		justify-content: flex-end !important;
	}
}

@media only screen and (max-width: 650px) {
	.cloudns-record-footer-row .cloudns-bulk-actions-row,
	.cloudns-record-footer-row .cloudns-record-bottom-row {
		flex-wrap: wrap !important;
		white-space: normal !important;
	}
}
</style>
{/literal}


{literal}
<style type="text/css">
/* DomainMonger Patch 1558: shared DNS control sizing.
   Match DNSPlus and Register DNS at the normal 34px system control height,
   and use the same 170px Search records width. Row action icons and modal
   controls are intentionally excluded. */
.cloudns-records-panel .cloudns-record-toolbar-left .cloudns-zone-record-count-indicator,
.cloudns-records-panel .cloudns-record-toolbar-left select.form-control,
.cloudns-records-panel .cloudns-record-toolbar-left #cloudns-record-search,
.cloudns-records-panel .cloudns-record-toolbar-right .cloudns-page-button,
.cloudns-records-panel .cloudns-record-toolbar-right .cloudns-add-record-link,
.cloudns-record-footer-row .cloudns-bulk-actions-row .btn,
.cloudns-record-footer-row .cloudns-bulk-actions-row select.form-control,
.cloudns-record-footer-row .cloudns-record-bottom-row .cloudns-page-button,
.cloudns-record-footer-row .cloudns-record-bottom-row .cloudns-add-record-link {
    box-sizing: border-box !important;
    height: 34px !important;
    min-height: 34px !important;
    max-height: 34px !important;
}
.cloudns-records-panel .cloudns-record-toolbar-left .cloudns-zone-record-count-indicator,
.cloudns-records-panel .cloudns-record-toolbar-right .cloudns-page-button,
.cloudns-records-panel .cloudns-record-toolbar-right .cloudns-add-record-link,
.cloudns-record-footer-row .cloudns-bulk-actions-row .btn,
.cloudns-record-footer-row .cloudns-record-bottom-row .cloudns-page-button,
.cloudns-record-footer-row .cloudns-record-bottom-row .cloudns-add-record-link {
    line-height: 32px !important;
    padding-top: 0 !important;
    padding-bottom: 0 !important;
}
.cloudns-records-panel .cloudns-record-toolbar-right .cloudns-pagination-top,
.cloudns-records-panel .cloudns-record-toolbar-right .cloudns-pagination-summary {
    height: 34px !important;
    min-height: 34px !important;
    max-height: 34px !important;
}
.cloudns-records-panel .cloudns-record-toolbar-singleline #cloudns-record-search {
    width: 170px !important;
    min-width: 170px !important;
    max-width: 170px !important;
    line-height: 32px !important;
    padding-top: 0 !important;
    padding-bottom: 0 !important;
}
.cloudns-records-panel .cloudns-record-search {
    height: 34px !important;
    min-height: 34px !important;
    max-height: 34px !important;
}
</style>
{/literal}

</form>
</div>
