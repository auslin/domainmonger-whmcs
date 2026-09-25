# Icon Cleanup Pass

Baseline: `ClouDNS_WHMCS.v1.9_DM_TTL_ACTION_ICON_SPACING_FIX_install_only.zip`

## Changes
- Removed the left-side search icon/toggle button from the DNS Records toolbar.
- Restyled the DNS Records search input as a standalone field.
- Kept the filter dropdown, search input, records count indicator, +Add column, and action icons intact.
- This is a visual cleanup pass; no DNS/API/controller behavior was intentionally changed.

## Notes
- The visible magnifying-glass style search button was removed from `records.tpl`.
- Search remains available through the search input.
