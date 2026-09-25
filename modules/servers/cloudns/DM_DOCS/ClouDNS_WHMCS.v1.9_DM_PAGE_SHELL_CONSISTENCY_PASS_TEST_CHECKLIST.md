# Test Checklist

## Shared-header pages
Confirm each page follows this structure:

Page Reference/Header → Main Menu → White Content Box

Pages to check:
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
- Parked Templates / parked-zone pages if enabled
- Failover / monitoring pages if enabled

## Standalone pages
Confirm these pages use the same white content box treatment:
- DNS Zones
- Add New DNS Zone
- Add New DNS Zone type panels after selecting a type
- zone error / notice pages
- forward error page

## Visual checks
- Spacing between menu and content box is consistent.
- White boxes have matching border, radius, padding, and subtle shadow.
- No duplicate page title appears inside the top of the content box unless it is a standalone page title.
- Toolbars align consistently above tables.
- Notices sit cleanly between menu/header and content.
- Mobile view stacks cleanly.

## Regression checks
- Main menu still works.
- Advanced dropdown still opens and is not clipped.
- DNS Records add/edit/delete still works.
- Mail Forwards add/edit/delete still works.
- Add New DNS Zone type selector still reveals the correct form.
- No DNS/API behavior changed.
