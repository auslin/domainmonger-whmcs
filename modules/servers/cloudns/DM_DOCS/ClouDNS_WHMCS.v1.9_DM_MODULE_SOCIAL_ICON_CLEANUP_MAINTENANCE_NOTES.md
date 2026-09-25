# Module-Wide Social Icon Cleanup

Baseline: `ClouDNS_WHMCS.v1.9_DM_SEARCH_TOGGLE_SOCIAL_CLEANUP_install_only.zip`

## Issue
Facebook/X/LinkedIn social share icons were appearing on most ClouDNS module pages, except Mail Forwards.

## Cause
The social icons were not found in the ClouDNS module source, so they are most likely injected by the WHMCS theme or a site-wide social sharing script.

## Changes
- Added a module-wide social-share suppression CSS layer.
- Applied the cleanup to:
  - shared-header module pages
  - DNS Zones / Zones List
  - Add New DNS Zone
  - zone/forward notice/error pages
  - slave header pages
- Targeted common social sharing systems:
  - ShareThis
  - AddToAny
  - AddThis
  - Heateor/social share wrappers
  - common floating social/share containers
- Kept the DNS Records Host / Points To search toggle intact.
- No DNS/API/controller behavior was intentionally changed.
