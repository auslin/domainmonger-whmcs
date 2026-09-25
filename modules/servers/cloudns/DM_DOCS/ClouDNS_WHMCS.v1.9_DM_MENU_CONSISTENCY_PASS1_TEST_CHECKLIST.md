# ClouDNS WHMCS v1.9 DM - Menu Consistency Pass 1 Test Checklist

## Shared header

- [ ] Switch Domain appears top-right in the shared module header.
- [ ] Switch Domain works on DNS Records.
- [ ] Switch Domain works on Mail Forwards.
- [ ] Switch Domain works on Parked Templates.
- [ ] Switch Domain preserves Statistics date when used on DNS Statistics.

## Main menu

- [ ] Main menu appears as tab-style navigation instead of grey pills.
- [ ] Active tab highlights correctly on DNS Records.
- [ ] Active tab highlights correctly on Mail Forwards.
- [ ] Active tab highlights correctly on DNS Statistics.
- [ ] Active tab highlights correctly on Status.
- [ ] Advanced dropdown opens and closes correctly.
- [ ] Deactivate is separated visually as a danger action.

## Advanced dropdown

- [ ] Check Zone File appears inside Advanced.
- [ ] Check on dns.computer appears inside Advanced.
- [ ] Check on dns.computer opens in a new tab.
- [ ] Parked Templates still appears only for parked zones.

## DNS Records toolbar

- [ ] Old grey pill submenu is gone.
- [ ] Records count appears as a non-clickable indicator.
- [ ] Filter still works.
- [ ] Search still works.
- [ ] Search toggle still switches Host / Points To.

## Regression check

- [ ] No Smarty error for cloudns-ui-common.tpl.
- [ ] Parked Zone add flow still works.
- [ ] New parked zones with no settings still show Apply interface.
- [ ] Non-parked Parked Templates page still shows one warning only.
