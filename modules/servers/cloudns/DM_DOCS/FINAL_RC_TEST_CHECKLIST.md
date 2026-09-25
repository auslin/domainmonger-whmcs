# Final RC Test Checklist

## General
- Clear WHMCS template cache.
- Hard refresh browser cache.
- Confirm module client-area pages load without PHP/Smarty errors.

## Zones List
- Title says `Zones List`.
- Counter displays, for example `Zones: 3/10`.
- Under limit: `+Add` is visible and opens Add New DNS Zone.
- At/over limit: `Limit Reached` is visible and not clickable.
- `Actions` aligns with the action column.
- Manage/Delete still work.

## DNS Records
- Page loads.
- Records display correctly.
- Host / Points To toggle works.
- Search by Host works.
- Search by Points To works.
- Add/Edit/Delete DNS records work.
- TTL/action icon spacing remains correct.

## Mail Forwards
- Page loads.
- `+Add` is right-aligned in the action column.
- Add/Edit/Delete mail forwards work.
- MX warning/action still works if shown.

## Statistics
- Last 30 Days / Yearly Statistics show as toggle buttons.
- Active toggle has white text on orange.
- Both views load.

## Advanced Pages
- SOA loads.
- DNSSEC loads.
- SSL loads.
- Zone Transfers loads.
- Import Zone File loads.
- Export Zone File loads and output scrolls after about 30 lines.
- Status page loads.
