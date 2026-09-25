# ClouDNS WHMCS v1.9 DM Deep UI Consistency Pass

Baseline: `ClouDNS_WHMCS.v1.9_DM_PAGE_REFERENCE_PASS2_install_only.zip`

## Scope

This pass audited the module templates for visual consistency across DNS Records, Mail Forwards, Statistics, Status, SOA, DNSSEC, SSL, Zone Transfers, Import Zone File, Export Zone File, DNS Zones, and Add New DNS Zone flows.

No DNS/API behavior was intentionally changed. The changes are CSS/template presentation refinements only.

## Changes

- Standardized primary content panels with matching white backgrounds, borders, 8px corners, padding, subtle shadow, and full-width behavior.
- Applied the same panel treatment to add/edit record and add/edit forwarding forms so they no longer render as loose form fields under the menu.
- Standardized form controls inside module panels: 34px height, 5px corners, consistent border, font size, text color, and box sizing.
- Standardized table header/body text sizing, colors, borders, and table shell corners across content panels.
- Standardized blue informational notices across notifications, Zone Transfers notice, Mail Forwards MX notice, Export help text, and related info blocks.
- Standardized delete/destructive buttons in zones/zone-transfer areas with consistent height, radius, red color, hover, focus, and active states.
- Standardized action sub-panels such as Zone Transfers add form, SSL actions, and Export actions with a light gray background and matching border/corners.
- Improved DNS Zones list visual consistency with white background, border, rounded corners, row spacing, and consistent link styling.
- Improved Add New DNS Zone visual consistency with a white content panel, consistent controls, and title case labels.
- Updated minor text inconsistencies:
  - `Add new DNS zone` -> `Add New DNS Zone`
  - New-zone section headings title-cased
  - New-zone Create buttons title-cased
  - `Slave (Secondary) Reverse zone` -> `Slave (Secondary) Reverse Zone`
  - `Import records` -> `Import Records`
  - `Slave server IP's` -> `Slave Server IPs`
  - `Reverse zone name` -> `Reverse Zone Name`

## Files Changed

- `templates/header-settings.tpl`
- `templates/zones.tpl`
- `templates/add-new-zone.tpl`
- `templates/import.tpl`
- `templates/zone-transfers.tpl`
- `templates/new-zone/master.tpl`
- `templates/new-zone/slave.tpl`
- `templates/new-zone/parked.tpl`
- `templates/new-zone/master-reverse.tpl`
- `templates/new-zone/slave-reverse.tpl`

## Notes

The older repeated per-template button CSS blocks were left in place to avoid risky template churn. This pass adds higher-level consistency overrides where needed rather than restructuring the module.
