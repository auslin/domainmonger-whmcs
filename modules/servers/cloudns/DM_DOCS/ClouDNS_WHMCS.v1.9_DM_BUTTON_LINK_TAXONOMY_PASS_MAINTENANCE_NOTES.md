# Button / Link Taxonomy Pass

Baseline: `ClouDNS_WHMCS.v1.9_DM_ADVANCED_TAB_FLAT_FIX_install_only.zip`

## Taxonomy

### Primary orange
Used for normal positive/forward actions:
- Add / +Add
- Create
- Save / Update
- Switch
- Execute when the selected bulk action is non-destructive
- View / Download
- Activate
- Apply
- Import Records
- Request New Update

### Secondary outline
Used for neutral/navigation/support actions:
- Manage
- Check Zone File
- Change
- Reset
- Back / History / Notifications style navigation buttons

### Danger red
Used for destructive actions:
- Delete
- Bulk Delete
- Deactivate Zone
- Deactivate SSL
- Deactivate DNSSEC
- Deactivate Failover/Monitoring

## Notes
- Added reusable CSS classes:
  - `cloudns-btn-primary`
  - `cloudns-btn-secondary`
  - `cloudns-btn-danger`
  - `cloudns-action-icon-danger`
  - `cloudns-danger-link`
- Existing legacy classes such as `cloudns-switch-style-button` were left in place for compatibility.
- Added taxonomy CSS to shared and standalone template surfaces so WHMCS theme inheritance does not override the taxonomy.
- Added dynamic styling for the DNS Records bulk Execute button:
  - red when the selected action is Delete
  - orange when the selected action is Change TTL
- No DNS/API/controller logic was intentionally changed.
