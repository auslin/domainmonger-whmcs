# Page-by-Page Fix Pass 2

Baseline: `ClouDNS_WHMCS.v1.9_DM_PAGE_BY_PAGE_FIX_PASS1_install_only.zip`

## Scope
Follow-up patch for the items that still needed adjustment after Pass 1, plus a Statistics toggle-button refinement.

## Changes

### DNS Records
- Reduced the Host / Points To toggle further.
- Added mode-specific sizing so `Host` can stay compact while `Points To` still fits.

### Mail Forwards
- Added a dedicated wrapper around the `+Add` button.
- Hard-aligned `+Add` to the far-right edge of the action header cell.

### Statistics
- Changed `Last 30 Days | Yearly Statistics` into a two-button toggle group.
- Added an active selected button state.

### Zones List
- Tightened the action column width.
- Re-aligned the right-side `Zones: used/limit` + `+Add` group with the row action column.
- Re-aligned `Actions` with the Manage/Delete column.
- Confirmed `Actions` spelling.

## Notes
The working fixes from Pass 1 for Zone Transfers, Import Zone File, and Export Zone File were left intact.

No DNS/API/controller behavior was intentionally changed.
