# ClouDNS WHMCS v1.9 DM Dark Background Text Fix

Baseline: `ClouDNS_WHMCS.v1.9_DM_FINAL_UI_CONSISTENCY_PASS_install_only.zip`

## Change Summary

- Added a final CSS hardening layer for table backgrounds on:
  - SOA page
  - SSL page
  - Import Zone File page
- Forces module table headers back to the shared light gray style.
- Forces table body cells back to white with dark readable text.
- Removes inherited WHMCS/theme background images/gradients from those table areas.

## Scope

UI/CSS only. No DNS, API, controller, database, or WHMCS service logic was changed.
