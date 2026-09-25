<div class="newZoneContainer cloudns-add-zone-type-panel cloudns-bulk-zone-panel">
	<form action="clientarea.php?action=productdetails&id={$serviceid}&customAction=bulk-delete-zones" method="post" id="cloudnsBulkDeleteZonesForm">
		<p class="cloudns-bulk-delete-warning">Deleting a zone permanently removes the zone and all of its DNS records from DNSPlus.</p>

		{if $zonesForBulk|@count gt 0}
			<div class="cloudns-bulk-delete-tools">
				<label class="cloudns-bulk-select-all-label">
					<input type="checkbox" id="cloudnsBulkDeleteAll" />
					<span>Select All</span>
				</label>
				<input type="search" id="cloudnsBulkDeleteSearch" class="form-control" placeholder="Search zones" autocomplete="off" />
				<span id="cloudnsBulkDeleteCount" class="cloudns-bulk-delete-count">0 selected</span>
			</div>

			<div class="table-responsive cloudns-bulk-delete-table-wrap">
				<table class="table table-hover cloudns-bulk-delete-table">
					<thead>
						<tr>
							<th class="cloudns-bulk-delete-check-col"><span class="sr-only">Select</span></th>
							<th>DNS Zone</th>
						</tr>
					</thead>
					<tbody>
					{foreach from=$zonesForBulk item=bulkZone}
						<tr class="cloudns-bulk-delete-zone-row" data-zone-search="{$bulkZone.name|lower|escape:'html'}">
							<td class="cloudns-bulk-delete-check-col">
								<input type="checkbox" class="cloudns-bulk-delete-checkbox" name="bulkDeleteZones[]" value="{$bulkZone.name|escape:'html'}" />
							</td>
							<td>{if $bulkZone.ascii}{$bulkZone.ascii|escape:'html'}{else}{$bulkZone.name|escape:'html'}{/if}</td>
						</tr>
					{/foreach}
					</tbody>
				</table>
			</div>

			<div class="cloudns-bulk-zone-footer">
				<button type="submit" id="cloudnsBulkDeleteSubmit" class="btn cloudns-btn-danger" disabled>Delete Selected Zones</button>
				<button type="button" class="btn cloudns-btn-secondary cloudns-zone-tool-cancel" data-return-url="{if isset($zone) && $zone != ''}clientarea.php?action=productdetails&amp;id={$serviceid}&amp;customAction=zone-settings&amp;zone={$zone}{else}clientarea.php?action=productdetails&amp;id={$serviceid}{/if}">Cancel</button>
			</div>
		{else}
			<div class="notification">There are no DNS zones available to delete.</div>
			<div class="cloudns-bulk-zone-footer">
				<button type="button" class="btn cloudns-btn-secondary cloudns-zone-tool-cancel" data-return-url="clientarea.php?action=productdetails&amp;id={$serviceid}">Back</button>
			</div>
		{/if}
	</form>
</div>
