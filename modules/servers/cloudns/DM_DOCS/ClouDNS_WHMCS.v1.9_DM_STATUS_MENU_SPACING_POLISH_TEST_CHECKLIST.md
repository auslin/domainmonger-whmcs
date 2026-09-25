# ClouDNS WHMCS v1.9 DM - Status/Menu/Spacing Polish Test Checklist

## Status

- [ ] Top-level `Status` still opens the status/update page.
- [ ] Page heading says `Status for <domain>`.
- [ ] Old `Servers update info` wording is gone.

## Advanced menu

- [ ] `Check Zone File` no longer appears in Advanced.
- [ ] `Check on dns.computer` / `Check Zone File` no longer appears in Advanced.
- [ ] `Check Zone File` appears as an action button on the Status page.
- [ ] Advanced dropdown separators still look good.

## Zone Transfers

- [ ] Heading says `Zone Transfers - <domain>`.

## DNSSEC

- [ ] Blue DNSSEC content/info box spacing matches the Zone Transfers notice more closely.
- [ ] DNSSEC active/inactive pages still work.

## Regression

- [ ] Export dropdown remains left-justified.
- [ ] Button heights still match adjacent controls.
- [ ] Records action icons remain right-justified.
- [ ] No Smarty error for cloudns-ui-common.tpl.
