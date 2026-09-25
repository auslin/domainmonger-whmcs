# ClouDNS WHMCS v1.9 DM Page Reference Pass 2 - Test Checklist

After upload, test these client-area pages for one active zone:

- DNS Records: header should read `DNS Records - domain.com`; no duplicate `DNS Records` title inside the content box.
- Mail Forwards: header should read `Mail Forwards - domain.com`; content should sit in a white box similar to DNS Records.
- Statistics: header should read `Statistics - domain.com`; date links and stats table should remain inside the white content box.
- Status: header should read `Status - domain.com`; server update content should remain inside the content box.
- SOA: header should read `SOA - domain.com`; no duplicate SOA title inside the content box.
- DNSSEC: header should read `DNSSEC - domain.com`; DNSSEC status should remain inside the content box as status/detail text.
- SSL: header should read `SSL - domain.com`; no duplicate SSL title inside the content box.
- Zone Transfers: header should read `Zone Transfers - domain.com`; no duplicate title inside the content box.
- Import Zone File: header should read `Import Zone File - domain.com`; no duplicate title inside the content box.
- Export Zone File: header should read `Export Zone File - domain.com`; no duplicate title inside the content box.
- Confirm no visible duplicate `DNS zones list` link appears in the module header, mobile menu, or page body.
- Confirm the WHMCS left navigation under Overview still provides the expected return path.
