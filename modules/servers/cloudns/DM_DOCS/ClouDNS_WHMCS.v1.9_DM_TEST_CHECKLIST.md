# ClouDNS WHMCS v1.9 DM - Final Test Checklist

Use this after installing the final package and after every future patch.

## Install sanity

- [ ] Upload/replace the `cloudns/` folder in `modules/servers/`.
- [ ] Confirm `cloudns/cloudns_config.php` exists.
- [ ] Confirm WHMCS client area product page does **not** fall back to the default product summary only.
- [ ] Confirm no page shows a blank page or PHP fatal error.

## DNS Records

- [ ] DNS Records page loads.
- [ ] Existing DNS records display.
- [ ] Sort arrows display correctly.
- [ ] Checkbox column sort arrows have spacing from master checkbox.
- [ ] Host search works.
- [ ] Points To search works.
- [ ] Search toggle works.
- [ ] `+Add` button displays correctly.
- [ ] Add record works.
- [ ] Edit record works.
- [ ] Delete record works.
- [ ] Bulk Delete works.
- [ ] Change TTL works.
- [ ] `execute` button matches Switch button styling.
- [ ] Switch Domain works on DNS Records page.
- [ ] Monitoring icons hide/show based on `cloudns_config.php`.

## Mail Forwards

- [ ] Mail Forwards page loads.
- [ ] Email and Points To columns are same width.
- [ ] Checkbox column is centered.
- [ ] Checkbox column sort arrows have spacing from master checkbox.
- [ ] `Delete` button displays correctly.
- [ ] `+Add` button displays correctly.
- [ ] Add mail forward works.
- [ ] Edit mail forward works.
- [ ] Delete mail forward works.
- [ ] Bulk delete mail forwards works.
- [ ] Mail Forward MX prompt appears when required MX records are missing.
- [ ] Add Mail Forward MX Records button works.
- [ ] Switch Domain works on Mail Forwards page.

## Advanced menu

- [ ] Advanced menu opens on hover.
- [ ] Advanced menu opens/closes correctly on click.
- [ ] DNSSEC page loads.
- [ ] SOA page loads.
- [ ] Parked Templates page loads.
- [ ] Zone Transfers page loads.
- [ ] Updated page loads.
- [ ] Export Zone File page loads.
- [ ] Zone Import page loads.
- [ ] Free SSL page loads.

## Feature pages

- [ ] Free SSL page loads.
- [ ] Free SSL Activate button displays correctly.
- [ ] Free SSL Change Issuer button displays correctly.
- [ ] Zone Transfers page loads.
- [ ] Zone Transfer Save button displays correctly.
- [ ] Zone Transfer Delete button still appears as destructive/delete action.
- [ ] Export Zone File page loads.
- [ ] Export View works.
- [ ] Export Download works.
- [ ] Updated page loads.
- [ ] Request New Update button matches Switch button styling.
- [ ] Parked Templates page loads.

## Visual checks

- [ ] Orange action buttons match the main-page Switch button color, style, and font.
- [ ] Action icons match the DNS Records main page style.
- [ ] Help/info/status icons were not accidentally changed.
- [ ] No old image row-action icons remain.
- [ ] No lowercase `add` link remains where `+Add` should appear.

## Regression warning

If all module pages show only the default WHMCS product summary panel, roll back to the known-good package and inspect recent template include/path changes first.
