# Typography / Forms / Tables Pass

Baseline: `ClouDNS_WHMCS.v1.9_DM_MENU_NAVIGATION_POLISH_PASS_install_only.zip`

## Goal
Make typography, forms, labels, tables, helper text, and action icons more consistent across the module.

## Changes
- Added a final typography/forms/tables CSS layer.
- Included standalone pages directly, including:
  - DNS Zones / Zones List
  - Add New DNS Zone
  - zone/forward notice/error pages
  - slave header/master server pages
- Standardized:
  - base text size and line-height
  - page title typography
  - h3/h4 heading sizes
  - label size/weight
  - small/helper text
  - input/select height and padding
  - textarea padding and font size
  - checkbox/radio alignment
  - table font size
  - table header/cell padding
  - table vertical alignment
  - action icon size/alignment
  - notice text size and padding
- Kept existing width rules and form layouts intact where possible.
- No DNS/API/controller behavior was intentionally changed.
