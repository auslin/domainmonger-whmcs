# ClouDNS WHMCS v1.9 DM Advanced Menu Order Maintenance Notes

## Baseline
- Built from: `ClouDNS_WHMCS.v1.9_DM_STATUS_CHECK_ZONE_PAGE_LINK_install_only.zip`

## Change
Updated the Advanced menu order and labels in `templates/header-settings.tpl` for both desktop and mobile menus.

Final Advanced menu structure:

- SOA
- DNSSEC
- SSL
- Zone Transfers
- Import Zone File
- Export Zone File
- Deactivate Zone

Separators are placed after DNSSEC, after Zone Transfers, and before Deactivate Zone.

## Validation
- PHP lint passed across module PHP files.
- Desktop and mobile Advanced menu markup both updated.
