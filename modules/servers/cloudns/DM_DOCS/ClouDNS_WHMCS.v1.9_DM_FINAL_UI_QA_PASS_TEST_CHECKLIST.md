# Final UI QA Test Checklist

## DNS Records
- Host / Points To search toggle is visible.
- Toggle starts on Host.
- Toggle changes to Points To.
- Search works in both modes.
- TTL column remains readable.
- Action icons remain inside the action column.
- +Add button remains aligned.

## Main module pages
Check layout, menu, buttons, and table styling on:
- DNS Records
- Mail Forwards
- Statistics
- Status
- SOA
- DNSSEC
- SSL
- Zone Transfers
- Import Zone File
- Export Zone File

## Standalone pages
Check:
- DNS Zones / Zones List
- Add New DNS Zone
- zone/error notice pages if encountered

## Regression
- DNS Records add/edit/delete still works.
- Mail Forwards add/edit/delete still works.
- Add New DNS Zone still works.
- Advanced dropdown works.
- No DNS/API behavior changed.
- Social icons are handled via WHMCS/theme/settings.
