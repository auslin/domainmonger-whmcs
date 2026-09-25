# ClouDNS WHMCS v1.9 DM - Parked Initial Settings Fix Test Checklist

## Parked zone with no settings

- [ ] Open the newly-created parked zone.
- [ ] Parked Templates page no longer says the zone is not parked.
- [ ] Page shows: This parked zone does not have template settings yet.
- [ ] Template dropdown/table displays.
- [ ] Apply buttons display.
- [ ] Apply a parked template.
- [ ] Refresh page and confirm current parked settings display.

## Security cleanup

- [ ] Rotate/regenerate the exposed ClouDNS API password.
- [ ] Turn WHMCS Module Logging off after testing.

## Regression check

- [ ] DNS Records page loads.
- [ ] Add Zone page loads.
- [ ] Parked Zone add form loads.
- [ ] No Smarty error for cloudns-ui-common.tpl.
