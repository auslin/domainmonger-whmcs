# ClouDNS WHMCS v1.9 DM DNS Zones White Background

Baseline: `ClouDNS_WHMCS.v1.9_DM_TABLE_HEADER_ALIGN_install_only.zip`

## Change Summary

- Updated `templates/zones.tpl` so the DNS Zones list page uses a white rounded content panel.
- Moved the DNS Zones heading and `+Add` button into a shared heading row inside the white panel.
- Kept the existing Manage/Delete links and zone list behavior unchanged.
- Standardized the DNS Zones notification style to match the blue info notice style used elsewhere.
- Added responsive behavior so the title and `+Add` button stack cleanly on narrow screens.

## Files Changed

- `templates/zones.tpl`

## Scope

UI/template-only change. No DNS/API/PHP controller logic was intentionally changed.
