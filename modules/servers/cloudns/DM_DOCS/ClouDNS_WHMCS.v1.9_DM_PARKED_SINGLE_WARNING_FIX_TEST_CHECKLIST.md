# ClouDNS WHMCS v1.9 DM - Parked Templates Single Warning Fix Test Checklist

## Non-parked zone

- [ ] Open Parked Templates on a parked zone.
- [ ] Use Switch Domain to switch to a non-parked zone.
- [ ] Confirm only one warning displays.
- [ ] Confirm the generic parked-template help text does not also display.
- [ ] Confirm Apply controls do not display.
- [ ] Confirm Switch Domain control remains visible.

## Parked zone

- [ ] Switch back to a parked zone.
- [ ] Confirm the Apply interface displays.
- [ ] Confirm the first-time "no settings yet" message still works if no template has been applied.
- [ ] Confirm Apply buttons still use the orange Switch-style button.

## Regression check

- [ ] No Smarty error for cloudns-ui-common.tpl.
- [ ] DNS Records page loads.
- [ ] Add Zone page loads.
- [ ] Parked Zone add form loads.
