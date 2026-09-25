# Page-by-Page Fix Pass 1

Baseline: `ClouDNS_WHMCS.v1.9_DM_ZONES_COUNTER_RIGHT_ALIGNED_install_only.zip`

## Changes

### DNS Records
- Reduced the Host / Points To search toggle button width.

### Mail Forwards
- Right-aligned the `+Add` button in the table header/action column.

### Statistics
- Changed `Last 30 days` to `Last 30 Days`.
- Changed `Yearly statistics` to `Yearly Statistics`.
- Added active-state styling for the selected statistics view.

### Zone Transfers
- Changed `Add new slave server IP:` to `Add Slave Server IP:`.
- Made the add-IP row more compact so the label/input stay on one line on desktop.

### Import Zone File
- Changed `Paste the records from your zone file here` to `Paste Zone File Records Here`.
- Changed `Delete all existing records` to `Delete Existing Records`.

### Export Zone File
- Changed help text to `Choose Export Format, then View or Download`.
- Made the export output textarea grow based on output up to 30 lines, then scroll.

### Zones List
- Refined right-side alignment so `Actions`, `+Add`, and the row action buttons align to the same action column.
- Confirmed `Actions` spelling.

## Notes
No DNS/API/controller behavior was intentionally changed.
