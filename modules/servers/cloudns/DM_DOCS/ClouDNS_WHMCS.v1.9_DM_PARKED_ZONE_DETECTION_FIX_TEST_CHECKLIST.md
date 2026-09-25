# ClouDNS WHMCS v1.9 DM - Parked Zone Detection Fix Test Checklist

## Parked zone

- [ ] Add a Parked Zone from Add new DNS zone.
- [ ] Successful creation opens Parked Templates.
- [ ] Parked Templates no longer says the new parked zone is not parked.
- [ ] Current parked settings section displays.
- [ ] Template Apply controls display.
- [ ] Switch Domain control displays.
- [ ] Apply button uses the Switch-style orange button.

## Non-parked zone

- [ ] Master zone does not show Apply controls on Parked Templates URL.
- [ ] Non-parked zone still shows a not-parked notice if URL is opened manually.

## Regression check

- [ ] DNS Records page loads.
- [ ] Mail Forwards page loads.
- [ ] Free SSL page loads.
- [ ] Export Zone File page loads.
- [ ] Advanced menu works.
- [ ] No page falls back to the default WHMCS product summary panel.
