# ClouDNS WHMCS v1.9 DM - UI Consistency Pass 1 Test Checklist

## Page title consistency

- [ ] DNS Records page shows body title `DNS Records`.
- [ ] Zone Transfers page shows body title `Zone Transfers`.
- [ ] SOA page shows body title `SOA`.
- [ ] DNSSEC inactive page shows body title `DNSSEC` and status line `Status: Inactive`.
- [ ] DNSSEC active page shows body title `DNSSEC` and status line `Status: Active`.
- [ ] Import Zone File page shows body title `Import Zone File`.
- [ ] Export Zone File page shows body title `Export Zone File`.
- [ ] Status page shows body title `Status` and still shows the Check Zone File and Request New Update buttons.
- [ ] SSL page shows body title `SSL`.

## Regression checks

- [ ] DNS Records table loads.
- [ ] DNS Records +Add button remains right-aligned with the action icon column.
- [ ] DNS Records sorting still works.
- [ ] Bulk action controls still work.
- [ ] Advanced dropdown order remains SOA, DNSSEC, SSL, Zone Transfers, Import Zone File, Export Zone File, Deactivate Zone.
- [ ] Import and Export Zone File actions still submit/view/download correctly.
- [ ] DNSSEC activate/deactivate buttons still submit correctly.
