# ClouDNS WHMCS v1.9 DM - UI Consistency Pass 1 Maintenance Notes

Baseline: `ClouDNS_WHMCS.v1.9_DM_MAIN_ADD_ALIGN_install_only.zip`

## Scope

This pass standardizes visible page/body headings and related title styling across the main DNS Records page and Advanced/Status pages. It does not change ClouDNS API calls, DNS record CRUD logic, zone ownership logic, or menu routing.

## Changes

### Shared styling

- Added shared `.cloudns-page-title` styling in `templates/header-settings.tpl`.
- Added shared `.cloudns-page-status` styling for status lines that should appear under a page title.
- Kept record table overflow visible on the new DNS Records body panel to avoid clipping existing record/action UI.

### Page/body headings

Standardized the visible body title pattern:

- DNS Records page: added `DNS Records` body title.
- Zone Transfers page: changed body title from `Zone Transfers - <zone>` to `Zone Transfers`.
- SOA page: added `SOA` body title.
- DNSSEC inactive page: changed body title from `Status: inactive` to `DNSSEC`, with `Status: Inactive` below it.
- DNSSEC active page: changed body title from `Status: active` to `DNSSEC`, with `Status: Active` below it.
- DNSSEC waiting page: changed body title from `Status: in progress` to `DNSSEC`, with `Status: In Progress` below it.
- Import Zone page: added `Import Zone File` body title.
- Export Zone page: changed body title from `Export Zone File - <zone>` to `Export Zone File`.
- Status page: changed body title from `Status for <zone>` to `Status`.
- SSL page: changed body title from `Free SSL for <zone>` to `SSL` to match the Advanced dropdown text.

### Module header text

Capitalized/standardized selected module header page titles produced by `cloudns_core/actions.php` and `cloudns.php`:

- `DNS records of ...` -> `DNS Records of ...`
- `Zone transfers - ...` -> `Zone Transfers - ...`
- `Update status of ...` -> `Status - ...`
- `Import records to ...` -> `Import Zone File - ...`
- `Free SSL for ...` -> `SSL - ...`
- `DNSSEC for ...` -> `DNSSEC - ...`
- `SOA settings of ...` -> `SOA - ...`

## Validation

- PHP lint passed on all PHP files.
- ZIP integrity test passed after packaging.
