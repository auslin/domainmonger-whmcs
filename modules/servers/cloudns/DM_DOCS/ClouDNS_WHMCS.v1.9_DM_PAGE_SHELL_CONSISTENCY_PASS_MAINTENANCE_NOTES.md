# Page Shell Consistency Pass

Baseline: `ClouDNS_WHMCS.v1.9_DM_BUTTON_LINK_TAXONOMY_PASS_install_only.zip`

## Goal
Standardize the visual page shell across the module:

1. Page reference/header
2. Main menu
3. White content panel
4. Page-specific content

## Changes
- Added/confirmed a shared shell consistency CSS layer across the shared-header pages.
- Added/confirmed the same shell layer on standalone pages that do not use the shared header:
  - DNS Zones
  - Add New DNS Zone
  - zone/forward error pages
  - registered-domain zone notice page
- Standardized spacing between:
  - page reference/header and menu
  - menu and content box
  - notices and content panels
  - toolbars and tables
- Standardized white content panel rules:
  - 1px light gray border
  - 8px border radius
  - white background
  - subtle shadow
  - consistent padding
  - consistent bottom margin
- Standardized first/last child spacing inside panels to remove random top/bottom gaps.
- Tightened mobile stacking for headers, toolbars, and action rows.
- Added explicit Add New Zone child panel class:
  - `cloudns-add-zone-type-panel`
- No DNS/API/controller behavior was intentionally changed.
