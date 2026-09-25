# Page-by-Page Fix Pass 3

Baseline: `ClouDNS_WHMCS.v1.9_DM_PAGE_BY_PAGE_FIX_PASS2_install_only.zip`

## Scope
Follow-up patch for the items that did not visually change enough in Pass 2.

## Changes

### DNS Records
- Made the Host / Points To toggle more compact with stronger ID-level CSS.
- Host mode is narrower.
- Points To mode still has enough width to fit.

### Mail Forwards
- Added inline/flex alignment directly around the `+Add` button.
- Increased the DataTables action-column width from 82px to 104px.
- Adjusted Email / Points To column width calculation to account for the wider action column.
- Forced the `+Add` button to the far-right edge of the action header cell.

### Zones List
- Wrapped the `Actions` text in a right-aligned label span.
- Aligned the `Actions` label with the right edge of the Delete button.
- Preserved the counter next to `+Add`.

### Statistics
- Added high-specificity `!important` styles so the active toggle has white text on orange.
- Prevented global link color styles from overriding the active toggle text color.

## Notes
The working fixes from Pass 1 and Pass 2 were left intact.

No DNS/API/controller behavior was intentionally changed.
