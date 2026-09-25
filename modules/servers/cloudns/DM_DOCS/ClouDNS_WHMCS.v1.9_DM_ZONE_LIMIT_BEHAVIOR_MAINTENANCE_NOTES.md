# Zone Limit Behavior

Baseline: `ClouDNS_WHMCS.v1.9_DM_PAGE_BY_PAGE_FIX_PASS3_install_only.zip`

## Goal
Make the Zones List usage counter actionable by changing the add-zone control when the package zone limit is reached.

## Changes
- If the service is under the package zone limit:
  - show the normal `+Add` button.
- If the service is at or over the package zone limit:
  - replace `+Add` with a disabled-looking `Limit Reached` control.
- The `Limit Reached` control:
  - is not clickable
  - has `aria-disabled="true"`
  - uses a disabled gray treatment
  - includes the title `Zone limit reached for this package`
- The existing counter remains visible:
  - example: `Zones: 10/10`

## Notes
- Registered-domain-only products still do not show the add-zone button.
- No DNS/API/controller behavior was intentionally changed.
- This is a UI guard only. The existing backend limit check remains the final enforcement.
