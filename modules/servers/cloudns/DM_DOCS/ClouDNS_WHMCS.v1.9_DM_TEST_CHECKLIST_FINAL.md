# ClouDNS WHMCS v1.9 DM - Final Test Checklist

Use this after installing the final package and after every future patch.

## Install sanity

- [ ] Upload/replace the `cloudns/` folder in `modules/servers/`.
- [ ] Confirm `cloudns/cloudns_config.php` exists.
- [ ] Confirm WHMCS client area product page does **not** fall back to the default product summary only.
- [ ] Confirm no page shows a blank page or PHP fatal error.

## Free SSL final visual check

- [ ] Free SSL page loads.
- [ ] Free SSL bottom buttons are same size/color/font: Activate, Change, Deactivate.
- [ ] Free SSL `Change` button submits issuer change correctly.
- [ ] Free SSL `Deactivate` button displays orange like the other action buttons.
- [ ] Free SSL button labels fit inside buttons.

## General smoke test

- [ ] DNS Records page loads.
- [ ] Mail Forwards page loads.
- [ ] Switch Domain works on DNS Records.
- [ ] Switch Domain works on Mail Forwards.
- [ ] Advanced menu opens correctly.
- [ ] Updated page loads.
- [ ] Request New Update button matches Switch button styling.
- [ ] Zone Transfers page loads.
- [ ] Export Zone File page loads.
