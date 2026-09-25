{assign var="path" value="../modules/servers/cloudns/templates"}
{if $version gte '6'}
	{assign var="path" value="."}
{/if}
{if $zoneInfo.type == 'master'}
	{include file="$path/header-settings.tpl"}
{else}
	{include file="$path/slave/slave-header-settings.tpl"}
{/if}
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.js"></script>
<div class="cloudns-body-panel cloudns-statistics-panel">
<style>
.cloudns-statistics-toggle {
	display: inline-flex;
	align-items: center;
	gap: 0;
	margin-bottom: 10px;
	border: 1px solid #dddddd;
	border-radius: 5px;
	background: #ffffff;
	overflow: hidden;
}

.cloudns-statistics-toggle .cloudns-stat-toggle-button {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	min-height: 34px;
	padding: 0 12px;
	border-right: 1px solid #dddddd;
	background: #ffffff;
	color: #333333;
	font-size: 13px;
	font-weight: 600;
	line-height: 1;
	text-decoration: none;
	white-space: nowrap;
}

.cloudns-statistics-toggle .cloudns-stat-toggle-button:last-child {
	border-right: 0;
}

.cloudns-statistics-toggle .cloudns-stat-toggle-button:hover,
.cloudns-statistics-toggle .cloudns-stat-toggle-button:focus {
	background: #fff7ef;
	color: #d75b0b;
	text-decoration: none;
	outline: none;
}

.cloudns-statistics-toggle .cloudns-stat-toggle-button-active {
	background: #f7941d;
	color: #ffffff;
	border-color: #f7941d;
}

.cloudns-statistics-toggle .cloudns-stat-toggle-button-active:hover,
.cloudns-statistics-toggle .cloudns-stat-toggle-button-active:focus {
	background: #d75b0b;
	color: #ffffff;
}
</style>

<style>
/* DM page-by-page fix pass 3: force statistics toggle contrast. */
.cloudns-statistics-panel .cloudns-statistics-toggle a.cloudns-stat-toggle-button-active,
.cloudns-statistics-panel .cloudns-statistics-toggle a.cloudns-stat-toggle-button-active:visited,
.cloudns-statistics-panel .cloudns-statistics-toggle a.cloudns-stat-toggle-button-active:hover,
.cloudns-statistics-panel .cloudns-statistics-toggle a.cloudns-stat-toggle-button-active:focus {
	background: #f7941d !important;
	background-color: #f7941d !important;
	color: #ffffff !important;
	border-color: #f7941d !important;
	text-decoration: none !important;
}

.cloudns-statistics-panel .cloudns-statistics-toggle a.cloudns-stat-toggle-button:not(.cloudns-stat-toggle-button-active) {
	color: #333333 !important;
	background: #ffffff !important;
	background-color: #ffffff !important;
}
</style>


<style>
/* DM Final RC Patch 1: sortable statistics table. */
.cloudns-statistics-summary {
	display: inline-flex;
	align-items: center;
	height: 34px;
	padding: 0 10px;
	margin: 0 0 10px 0;
	border: 1px solid #dddddd;
	border-radius: 4px;
	background: #fafafa;
	color: #444444;
	font-size: 12px;
	font-weight: 600;
	line-height: 1;
	white-space: nowrap;
}

#cloudns-statistics-table {
	width: 100% !important;
	margin-bottom: 0 !important;
}

#cloudns-statistics-table thead th {
	background: #f7f7f7 !important;
	color: #333333 !important;
	border-bottom: 1px solid #dddddd !important;
	font-weight: 700 !important;
	vertical-align: middle !important;
	cursor: pointer;
}

#cloudns-statistics-table td {
	vertical-align: middle !important;
}

#cloudns-statistics-table.dataTable thead th.sorting,
#cloudns-statistics-table.dataTable thead th.sorting_asc,
#cloudns-statistics-table.dataTable thead th.sorting_desc {
	position: relative;
	padding-right: 22px !important;
}

#cloudns-statistics-table.dataTable thead th.sorting::before,
#cloudns-statistics-table.dataTable thead th.sorting::after,
#cloudns-statistics-table.dataTable thead th.sorting_asc::before,
#cloudns-statistics-table.dataTable thead th.sorting_asc::after,
#cloudns-statistics-table.dataTable thead th.sorting_desc::before,
#cloudns-statistics-table.dataTable thead th.sorting_desc::after {
	right: 7px !important;
	color: #777777 !important;
	opacity: 0.65 !important;
}

#cloudns-statistics-table.dataTable thead th.sorting_asc::before,
#cloudns-statistics-table.dataTable thead th.sorting_desc::after {
	color: #333333 !important;
	opacity: 1 !important;
}
</style>


<style>
/* DM Final RC Patch 2: match Statistics sort arrows to DNS Records. */
#cloudns-statistics-table.cloudns-sortable.dataTable thead th,
#cloudns-statistics-table.dataTable thead th {
	position: relative !important;
}

#cloudns-statistics-table.cloudns-sortable.dataTable thead th.sorting,
#cloudns-statistics-table.cloudns-sortable.dataTable thead th.sorting_asc,
#cloudns-statistics-table.cloudns-sortable.dataTable thead th.sorting_desc,
#cloudns-statistics-table.cloudns-sortable.dataTable thead th.sorting_disabled,
#cloudns-statistics-table.dataTable thead th.sorting,
#cloudns-statistics-table.dataTable thead th.sorting_asc,
#cloudns-statistics-table.dataTable thead th.sorting_desc,
#cloudns-statistics-table.dataTable thead th.sorting_disabled {
	background-image: none !important;
	padding-right: 28px !important;
}

#cloudns-statistics-table.cloudns-sortable.dataTable thead th.sorting,
#cloudns-statistics-table.dataTable thead th.sorting {
	background-color: #f1f1f1 !important;
	color: #333333 !important;
}

#cloudns-statistics-table.cloudns-sortable.dataTable thead th.sorting_asc,
#cloudns-statistics-table.cloudns-sortable.dataTable thead th.sorting_desc,
#cloudns-statistics-table.dataTable thead th.sorting_asc,
#cloudns-statistics-table.dataTable thead th.sorting_desc {
	background-color: #ee9d4a !important;
	color: #ffffff !important;
}

#cloudns-statistics-table.cloudns-sortable.dataTable thead th.sorting::before,
#cloudns-statistics-table.cloudns-sortable.dataTable thead th.sorting::after,
#cloudns-statistics-table.cloudns-sortable.dataTable thead th.sorting_asc::before,
#cloudns-statistics-table.cloudns-sortable.dataTable thead th.sorting_asc::after,
#cloudns-statistics-table.cloudns-sortable.dataTable thead th.sorting_desc::before,
#cloudns-statistics-table.cloudns-sortable.dataTable thead th.sorting_desc::after,
#cloudns-statistics-table.dataTable thead th.sorting::before,
#cloudns-statistics-table.dataTable thead th.sorting::after,
#cloudns-statistics-table.dataTable thead th.sorting_asc::before,
#cloudns-statistics-table.dataTable thead th.sorting_asc::after,
#cloudns-statistics-table.dataTable thead th.sorting_desc::before,
#cloudns-statistics-table.dataTable thead th.sorting_desc::after {
	position: absolute !important;
	right: 9px !important;
	font-size: 10px !important;
	line-height: 1 !important;
	color: #d77b13 !important;
	opacity: 1 !important;
}

#cloudns-statistics-table.cloudns-sortable.dataTable thead th.sorting::before,
#cloudns-statistics-table.cloudns-sortable.dataTable thead th.sorting_asc::before,
#cloudns-statistics-table.cloudns-sortable.dataTable thead th.sorting_desc::before,
#cloudns-statistics-table.dataTable thead th.sorting::before,
#cloudns-statistics-table.dataTable thead th.sorting_asc::before,
#cloudns-statistics-table.dataTable thead th.sorting_desc::before {
	content: "▲" !important;
	top: calc(50% - 9px) !important;
}

#cloudns-statistics-table.cloudns-sortable.dataTable thead th.sorting::after,
#cloudns-statistics-table.cloudns-sortable.dataTable thead th.sorting_asc::after,
#cloudns-statistics-table.cloudns-sortable.dataTable thead th.sorting_desc::after,
#cloudns-statistics-table.dataTable thead th.sorting::after,
#cloudns-statistics-table.dataTable thead th.sorting_asc::after,
#cloudns-statistics-table.dataTable thead th.sorting_desc::after {
	content: "▼" !important;
	top: calc(50% + 1px) !important;
}

#cloudns-statistics-table.cloudns-sortable.dataTable thead th.sorting_asc::before,
#cloudns-statistics-table.cloudns-sortable.dataTable thead th.sorting_desc::after,
#cloudns-statistics-table.dataTable thead th.sorting_asc::before,
#cloudns-statistics-table.dataTable thead th.sorting_desc::after {
	color: #ffffff !important;
	opacity: 1 !important;
}

#cloudns-statistics-table.cloudns-sortable.dataTable thead th.sorting_asc::after,
#cloudns-statistics-table.cloudns-sortable.dataTable thead th.sorting_desc::before,
#cloudns-statistics-table.dataTable thead th.sorting_asc::after,
#cloudns-statistics-table.dataTable thead th.sorting_desc::before {
	color: #f7ca9c !important;
	opacity: 0.95 !important;
}
</style>


<style>
/* DM Final RC Patch 2: move Requests indicator to the right side. */
.cloudns-statistics-header-row {
	display: flex !important;
	align-items: center !important;
	justify-content: space-between !important;
	gap: 12px !important;
	flex-wrap: wrap !important;
	margin-bottom: 10px !important;
}

.cloudns-statistics-header-row .cloudns-statistics-toggle {
	margin-bottom: 0 !important;
}

.cloudns-statistics-header-row .cloudns-statistics-summary {
	margin: 0 0 0 auto !important;
}

@media only screen and (max-width: 650px) {
	.cloudns-statistics-header-row {
		align-items: stretch !important;
	}

	.cloudns-statistics-header-row .cloudns-statistics-summary {
		margin-left: 0 !important;
		width: 100% !important;
		justify-content: center !important;
	}
}
</style>

<div class="cloudns-statistics-header-row">
	<div class="text-left cloudns-statistics-toggle" role="group" aria-label="Statistics range">
		<a class="cloudns-stat-toggle-button{if isset($date) && $date == 'last-30-days'} cloudns-stat-toggle-button-active{/if}" href="clientarea.php?action=productdetails&id={$serviceid}&customAction=statistics&zone={$zone}&date=last-30-days">Last 30 Days</a>
		<a class="cloudns-stat-toggle-button{if !isset($date) || $date == ''} cloudns-stat-toggle-button-active{/if}" href="clientarea.php?action=productdetails&id={$serviceid}&customAction=statistics&zone={$zone}">Yearly Statistics</a>
	</div>
	<div class="cloudns-statistics-summary" aria-label="Total requests">Requests: {$requests|@number_format}</div>
</div>
<div class="clear"></div>
<table id="cloudns-statistics-table" class="table table-bordered table-hover cloudns-rounded-table cloudns-sortable" cellspacing="0" cellpadding="0">
	<thead>
		<tr>
			<th>Period</th>
			<th>Requests</th>
		</tr>
	</thead>
	<tbody>
		{* This var comes from actions.php and is the body of the table *}
		{$statsTable}
	</tbody>
</table>
</div>


<script>
{literal}
jQuery(function ($) {
	var $table = $('#cloudns-statistics-table');
	if (!$table.length || !$.fn.DataTable) {
		return;
	}

	$table.DataTable({
		paging: false,
		searching: false,
		info: false,
		lengthChange: false,
		autoWidth: false,
		order: [[0, 'desc']],
		columnDefs: [
			{ targets: 0, type: 'num' },
			{ targets: 1, type: 'num' }
		]
	});
});
{/literal}
</script>

