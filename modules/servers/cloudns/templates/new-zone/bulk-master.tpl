<div class="newZoneContainer cloudns-add-zone-type-panel cloudns-bulk-zone-panel">
	<form action="clientarea.php?action=productdetails&id={$serviceid}&customAction=bulk-add-zones" method="post" id="cloudnsBulkAddZonesForm">
		<div class="cloudns-auto-ns-note">The required DomainMonger nameservers will be added automatically to every zone.</div>
		<p class="cloudns-bulk-zone-help">Enter one domain zone per row. Blank rows are ignored.</p>

		<div id="cloudnsBulkZoneRows" class="cloudns-bulk-zone-rows">
			<div class="cloudns-bulk-zone-row">
				<span class="cloudns-bulk-zone-number">1</span>
				<input type="text" name="bulkZones[]" class="form-control cloudns-bulk-zone-input" placeholder="example.com" autocomplete="off" />
				<button type="button" class="btn cloudns-btn-secondary cloudns-bulk-zone-remove" aria-label="Remove zone row">Remove</button>
			</div>
			<div class="cloudns-bulk-zone-row">
				<span class="cloudns-bulk-zone-number">2</span>
				<input type="text" name="bulkZones[]" class="form-control cloudns-bulk-zone-input" placeholder="example.net" autocomplete="off" />
				<button type="button" class="btn cloudns-btn-secondary cloudns-bulk-zone-remove" aria-label="Remove zone row">Remove</button>
			</div>
			<div class="cloudns-bulk-zone-row">
				<span class="cloudns-bulk-zone-number">3</span>
				<input type="text" name="bulkZones[]" class="form-control cloudns-bulk-zone-input" placeholder="example.org" autocomplete="off" />
				<button type="button" class="btn cloudns-btn-secondary cloudns-bulk-zone-remove" aria-label="Remove zone row">Remove</button>
			</div>
		</div>

		<div class="cloudns-bulk-zone-toolbar">
			<button type="button" id="cloudnsAddAnotherZone" class="btn cloudns-btn-secondary">+ Add Another Zone</button>
		</div>

		<div class="cloudns-bulk-zone-footer">
			<button type="submit" id="cloudnsBulkAddSubmit" class="btn cloudns-btn-primary">Add Zones</button>
			<button type="button" class="btn cloudns-btn-secondary cloudns-zone-tool-cancel" data-return-url="{if isset($zone) && $zone != ''}clientarea.php?action=productdetails&amp;id={$serviceid}&amp;customAction=zone-settings&amp;zone={$zone}{else}clientarea.php?action=productdetails&amp;id={$serviceid}{/if}">Cancel</button>
		</div>
	</form>
</div>
