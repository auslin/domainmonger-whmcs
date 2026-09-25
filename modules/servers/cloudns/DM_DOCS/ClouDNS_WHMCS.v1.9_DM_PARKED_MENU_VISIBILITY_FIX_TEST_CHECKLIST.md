# ClouDNS WHMCS v1.9 DM - Parked Menu Visibility Fix Test Checklist

## Parked zone

- [ ] Open a parked zone on its normal page.
- [ ] Open the Advanced dropdown.
- [ ] Confirm Parked Templates appears.
- [ ] Click Parked Templates.
- [ ] Confirm Apply interface displays.

## Non-parked zone

- [ ] Open a non-parked zone.
- [ ] Open the Advanced dropdown.
- [ ] Confirm Parked Templates does not appear.
- [ ] Manually opening Parked Templates still shows one warning only.

## Regression check

- [ ] Switch Domain control still works.
- [ ] Newly-created parked zones with no settings still show the Apply interface.
- [ ] No Smarty error for cloudns-ui-common.tpl.
- [ ] DNS Records page loads.
- [ ] Add Zone page loads.
