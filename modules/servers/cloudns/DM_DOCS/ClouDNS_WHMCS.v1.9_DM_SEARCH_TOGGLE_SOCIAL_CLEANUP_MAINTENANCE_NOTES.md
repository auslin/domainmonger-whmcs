# Search Toggle / Social Icon Cleanup

Baseline: `ClouDNS_WHMCS.v1.9_DM_ICON_CLEANUP_PASS_install_only.zip`

## Changes
- Restored the DNS Records search toggle functionality.
- Replaced the removed magnifying-glass toggle with a clear text toggle button:
  - `Host`
  - `Points To`
- The search input still changes placeholder text based on the active search mode.
- Added DNS-Records-page-only CSS to hide common theme/site-injected social share widgets, including common Facebook/X/Twitter/LinkedIn floating share containers.
- No DNS/API/controller behavior was intentionally changed.

## Notes
- Social icons were not found in the module source, so they are likely injected by the WHMCS theme or a global sharing script.
- The hiding CSS is scoped to the DNS Records page via `.cloudns-records-panel`.
