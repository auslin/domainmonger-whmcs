<style id="dm-rcdns-view-header-status-fix-713">
.dm-rcdns-view-page .dm-rcdns-zone-table > tbody > tr:first-child > td,
.dm-rcdns-view-page .dm-rcdns-zone-table > tr:first-child > td {
    font-weight: 700;
    color: #163a5f;
    background: #f8fafc;
    white-space: nowrap;
}
.dm-rcdns-view-page .label.label-success {
    display: inline-block;
    min-width: 52px;
    padding: 4px 8px;
    text-align: center;
}
</style>
<style>
.dm-rcdns-view-page .dm-rcdns-table-wrap {
    width: 100%;
    overflow-x: auto;
    overflow-y: visible;
}
.dm-rcdns-view-page .dm-rcdns-table-wrap > .table {
    width: 100%;
    max-width: 100%;
    margin-left: 0 !important;
    margin-right: 0 !important;
}
.dm-rcdns-view-page .table th,
.dm-rcdns-view-page .table td {
    vertical-align: top;
}
@media (max-width: 767px) {
    .dm-rcdns-view-page .dm-rcdns-table-wrap > .table {
        min-width: 760px;
    }
}
</style>
<div class="dm-rcdns-view-page">
{include file="$template/includes/alert.tpl" type="info" msg=$LANG.rcdns_dnsoverviewtitledesc|cat:$domain}

<div class="dm-rcdns-table-wrap">
<table class="table table-bordered table-hover dm-rcdns-zone-table">
	<tr>
		<td>Record Type</td>
		<td>Host</td>
		<td>Value</td>
		<td>TTL</td>
		<td>Priority</td>
		<td>Weight</td>
		<td>Port</td>
		<td>Status</td>
	</tr>
	{foreach key=num item=service from=$dnsrecords}
	<tr>
		<td>{$service.type}</td>
		<td><div style="width: 200px; word-wrap: break-word;">{$service.hostname}</div></td>
		<td><div style="width: 150px; word-wrap: break-word;">{$service.value}</div></td>
		<td>{$service.timetolive}</td>
		<td>{$service.priority}</td>
		<td>{$service.weight}</td>
		<td>{$service.port}</td>
		<td>{if $service.status eq "Active"}<span class="label label-success">Active</span>{elseif $service.status}<span class="label label-suspended">{$service.status}</span>{else}<span class="label label-suspended">Inactive</span>{/if}</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="8">No records found</td>
	</tr>
	{/foreach}		
</table>
</div>
</div>
