# ClouDNS WHMCS v1.9 DM Main Menu Border Match - Maintenance Notes

Baseline package:
- `ClouDNS_WHMCS.v1.9_DM_DARK_BG_TEXT_FIX_install_only.zip`

Change summary:
- Added a final CSS override in `templates/header-settings.tpl` for `ul#cloudnsSettingsMenu`.
- The main ClouDNS menu now uses the same visual treatment as the white body panels:
  - `1px solid #dddddd` border
  - `8px` rounded corners
  - white background
  - subtle panel shadow
- Preserved `overflow: visible` so the Advanced dropdown can still open outside the menu box.

Behavior impact:
- UI-only change.
- No DNS/API/PHP business logic changed.
