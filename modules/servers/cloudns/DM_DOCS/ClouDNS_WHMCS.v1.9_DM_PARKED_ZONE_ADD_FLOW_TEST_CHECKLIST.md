# ClouDNS WHMCS v1.9 DM - Parked Zone Add Flow Test Checklist

## Add Zone page

- [ ] Add new DNS zone page loads.
- [ ] Type dropdown includes Parked Zone.
- [ ] Selecting Parked Zone shows the parked-zone form.
- [ ] Parked Zone create button matches the main-page Switch button style.
- [ ] Creating a parked zone calls `dns/register.json` with `zone-type=parked`.
- [ ] Successful parked zone creation redirects to Parked Templates.

## Parked Templates page

- [ ] Parked Templates appears in Advanced menu for parked zones.
- [ ] Parked Templates does not appear in Advanced menu for master zones.
- [ ] Parked Templates does not appear in Advanced menu for slave zones.
- [ ] Parked Templates page shows Apply controls for parked zones.
- [ ] Parked Templates page hides Apply controls for non-parked zones.
- [ ] Parked Templates page keeps the Switch Domain control.

## Regression check

- [ ] DNS Records page loads.
- [ ] Mail Forwards page loads.
- [ ] Free SSL page loads.
- [ ] Export Zone File page loads.
- [ ] Advanced menu works.
- [ ] No page falls back to the default WHMCS product summary panel.
