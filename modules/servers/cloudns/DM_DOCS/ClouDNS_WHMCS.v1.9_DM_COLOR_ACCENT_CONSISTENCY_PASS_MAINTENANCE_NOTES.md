# Color / Accent Consistency Pass

Baseline: `ClouDNS_WHMCS.v1.9_DM_PAGE_SHELL_CONSISTENCY_PASS_install_only.zip`

## Goal
Make colors feel intentional across the module.

## Color rules
- Orange is the action/link/focus accent.
- Blue is reserved for informational notices.
- Red is reserved for destructive/error states.
- Neutral grays are used for tables, borders, and body text.

## Changes
- Added a final color/accent CSS layer to shared module pages.
- Added the same color/accent layer directly to standalone pages, including:
  - DNS Zones / Zones List
  - Add New DNS Zone
  - zone/forward notice/error pages
  - slave header/master server page
- Standardized table header/body colors.
- Standardized table hover color to a very light orange tint.
- Standardized normal text color to dark gray.
- Standardized form focus states with the ClouDNS orange accent.
- Standardized non-button links to orange with a darker hover.
- Kept the DNS Zones / Zones List domain link color aligned with the previously approved orange reference, with a slightly darker hover.
- Standardized info notices to blue.
- Standardized error notices to red.
- No DNS/API/controller behavior was intentionally changed.
