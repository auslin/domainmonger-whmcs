# ClouDNS WHMCS v1.9 - Main Menu Edge Flush Notes

Baseline: `ClouDNS_WHMCS.v1.9_DM_MAIN_MENU_BORDER_MATCH_install_only.zip`

## Change

- Removed the internal horizontal padding from the bordered ClouDNS main menu container.
- This lets the first menu item, **DNS Records**, sit flush with the left edge of the bordered menu box.
- Preserved the menu border, rounded corners, shadow, active/hover states, and Advanced dropdown overflow behavior.

## Files Changed

- `templates/header-settings.tpl`

## Behavior

- UI-only change.
- No DNS/API/controller behavior changed.
