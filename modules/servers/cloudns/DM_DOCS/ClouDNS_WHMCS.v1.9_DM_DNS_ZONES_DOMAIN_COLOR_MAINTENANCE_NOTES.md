# DM DNS Zones Domain Color Pass

Baseline: `ClouDNS_WHMCS.v1.9_DM_ZONES_LIST_LABEL_install_only.zip`

## Change
- Updated DNS Zones page domain link color in `templates/zones.tpl` to match the orange domain text shown in the provided WHMCS reference screenshot.
- Updated DNS Zones hover/focus state to darken slightly instead of shifting to a brighter orange.

## Files changed
- `templates/zones.tpl`

## Behavior impact
- Visual-only CSS/template change.
- No DNS/API/controller logic changed.
