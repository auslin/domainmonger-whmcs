# Zones Counter

Baseline: `ClouDNS_WHMCS.v1.9_DM_ZONES_LIST_CONSISTENCY_PASS_install_only.zip`

## Goal
Show the customer how many zones are active compared with the package zone limit.

## Changes
- Added `zonesCount` to the Zones List template variables.
- Added `zonesLimit` to the Zones List template variables.
- Added a Zones List title-row badge matching the existing Records indicator style.
- Badge format:
  - `Zones: 3/10`
- For registered-domain-only products, the counter uses the registered domain count as the effective limit.
- No DNS/API/controller behavior was intentionally changed.
