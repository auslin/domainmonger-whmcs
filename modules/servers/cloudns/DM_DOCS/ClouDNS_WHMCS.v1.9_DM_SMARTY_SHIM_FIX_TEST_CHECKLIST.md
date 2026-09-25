# ClouDNS WHMCS v1.9 DM - Smarty Shim Fix Test Checklist

## Install cleanup

- [ ] Upload the entire `cloudns/` folder from the install-only ZIP.
- [ ] Confirm `templates/cloudns-ui-common.tpl` exists on the server.
- [ ] Clear WHMCS compiled templates/cache after upload.
- [ ] Refresh the client area page.

## Error check

- [ ] The Smarty error for `cloudns-ui-common.tpl` is gone.
- [ ] DNS Records page loads.
- [ ] Add Zone page loads.
- [ ] Parked Zone add form loads.
- [ ] Parked Templates page loads.
- [ ] No page falls back to the default WHMCS product summary panel.
