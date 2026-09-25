# Zones List Consistency Pass

Baseline: `ClouDNS_WHMCS.v1.9_DM_SWITCH_DOMAIN_LABEL_REMOVED_install_only.zip`

## Goal
Bring the standalone DNS Zones / Zones List page closer to the rest of the module UI.

## Changes
- Changed the standalone page title from `DNS Zones` to `Zones List`.
- Added a table header row:
  - Domain
  - Actions
- Added a proper `<thead>` and `<tbody>` structure to the Zones List table.
- Tightened the desktop action button group for Manage/Delete.
- Kept the button taxonomy:
  - `+Add` = primary orange
  - `Manage` = secondary outline
  - `Delete` = danger red
- Kept the orange domain link style.
- Kept the white panel / table / typography consistency layers.
- No DNS/API/controller behavior was intentionally changed.
