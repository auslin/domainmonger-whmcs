# Zones Counter Right Aligned

Baseline: `ClouDNS_WHMCS.v1.9_DM_ZONES_COUNTER_install_only.zip`

## Goal
Move the Zones usage counter next to `+Add` and align the right-side header/action area with the table action buttons.

## Changes
- Moved `Zones: used/limit` out of the title group.
- Placed the counter immediately left of `+Add`.
- Kept `Zones List` clean on the left.
- Confirmed the table header says `Actions`.
- Aligned the `Actions` header with the Manage/Delete action column.
- Aligned the right-side `Zones: used/limit` + `+Add` group with the action column below.
- Kept the Records-style counter badge styling.
- No DNS/API/controller behavior was intentionally changed.
