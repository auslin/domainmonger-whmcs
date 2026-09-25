# Social Cleanup Removed

Baseline: `ClouDNS_WHMCS.v1.9_DM_SEARCH_TOGGLE_SOCIAL_CLEANUP_install_only.zip`

## Goal
Remove the failed social-icon suppression workaround from the module and leave social icons to be handled through WHMCS/theme/settings.

## Changes
- Removed DNS Records social-share suppression CSS from `templates/records.tpl`.
- Removed extra common social-widget suppression selectors from `templates/records.tpl`.
- Kept the DNS Records search toggle:
  - `Host`
  - `Points To`
- Kept prior UI fixes:
  - TTL/action icon spacing
  - action icon column spacing
  - typography/forms/tables
  - menu/navigation polish
  - color/accent consistency
  - page shell consistency
- No DNS/API/controller behavior was intentionally changed.
