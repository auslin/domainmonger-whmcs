# ClouDNS WHMCS v1.9 DM Page Reference Pass 2 - Maintenance Notes

Baseline used: `ClouDNS_WHMCS.v1.9_DM_UI_CONSISTENCY_PASS1_install_only.zip`

## Purpose

This pass standardizes the page reference/header pattern and removes duplicate in-page navigation that already exists in the WHMCS left navigation under Overview.

## Changes

- Standardized primary page references to use the dash format:
  - `DNS Records - domain.com`
  - `Mail Forwards - domain.com`
  - `Statistics - domain.com`
  - `Status - domain.com`
  - `SOA - domain.com`
  - `DNSSEC - domain.com`
  - `SSL - domain.com`
  - `Zone Transfers - domain.com`
  - `Import Zone File - domain.com`
  - `Export Zone File - domain.com`
- Removed duplicate content-box page titles from the top of the content areas. The page reference now appears once in the module header.
- Removed duplicate `DNS zones list` / `back to the DNS zones list` links from module pages and mobile menu output.
- Added a white `cloudns-body-panel` wrapper to the Mail Forwards page so its content background matches the main DNS Records page style.
- Corrected the Statistics page structure so the module header/menu renders before the white content panel, matching the other pages.

## Files touched

- `cloudns_core/actions.php`
- `cloudns.php`
- `templates/header-settings.tpl`
- `templates/slave/slave-header-settings.tpl`
- `templates/records.tpl`
- `templates/mail-forwarding.tpl`
- `templates/statistics.tpl`
- `templates/update-status.tpl`
- `templates/soa.tpl`
- `templates/dnssec-show.tpl`
- `templates/dnssec-settings.tpl`
- `templates/dnssec-waiting.tpl`
- `templates/import.tpl`
- `templates/export-zone-file.tpl`
- `templates/zone-transfers.tpl`
- `templates/free-ssl.tpl`
- `templates/add-new-zone.tpl`
- `templates/registered-domain-zone-not-own.tpl`
- `templates/zone-error.tpl`
- `templates/forward-error.tpl`

## Scope

No ClouDNS API behavior, DNS record logic, zone logic, mail forwarding logic, or WHMCS service routing was intentionally changed.
