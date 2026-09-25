# Menu / Navigation Polish Pass

Baseline: `ClouDNS_WHMCS.v1.9_DM_COLOR_ACCENT_CONSISTENCY_PASS_install_only.zip`

## Goal
Make the main menu, Advanced dropdown, and mobile Settings Menu feel like one consistent navigation system.

## Changes
- Kept the main menu text-only and flat.
- Preserved the first-item DNS Records edge rounding.
- Kept the Advanced tab flat like the other middle menu items.
- Standardized desktop main menu:
  - height
  - padding
  - active state
  - hover/focus state
  - border and rounded outer menu shell
- Standardized Advanced dropdown:
  - spacing
  - item height
  - active state
  - hover/focus state
  - dividers
  - danger styling for Deactivate Zone
- Added active-state logic to the mobile Settings Menu:
  - DNS Records
  - Mail Forwards
  - Statistics
  - Status
  - SOA
  - DNSSEC
  - SSL
  - Zone Transfers
  - Import Zone File
  - Export Zone File
- Standardized mobile Settings Menu:
  - dropdown button
  - item hover/focus states
  - active states
  - dividers
  - Advanced header text
  - Deactivate Zone danger treatment
- No DNS/API/controller behavior was intentionally changed.
