# ClouDNS WHMCS v1.9 DM Status Check Zone Page Link

Baseline used: `ClouDNS_WHMCS.v1.9_DM_STATUS_MENU_SPACING_POLISH_install_only.zip`.

## Scope

This is a targeted navigation refinement only. It does not change core API behavior, DNS record logic, zone creation, or database behavior.

## Changes

- Kept `Status` as a primary/main settings menu item.
- Removed the external `Check on dns.computer` item from the desktop Advanced dropdown.
- Removed the same check item from the mobile Advanced section.
- Added a `Check Zone File` action button directly on the Status page.
- Added small flex/gap styling to the Status page action row so `Check Zone File` and `Request New Update` align cleanly when shown together.

## Files changed

- `templates/header-settings.tpl`
- `templates/update-status.tpl`

## Validation

- PHP syntax validation passed for all PHP files in this package.
- Verified the Advanced menu no longer contains the check-zone external link.
- Verified the Status page template contains the new `Check Zone File` action link.
