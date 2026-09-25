# Test Checklist

## Primary buttons
- DNS Records: +Add is orange.
- DNS Records: Execute becomes orange when Change TTL is selected.
- Add/Edit DNS Record: Save/Update is orange.
- Mail Forwards: +Add and Add Mail Forward MX Records are orange.
- Import Zone File: Import Records is orange.
- Export Zone File: View and Download are orange.
- Status: Request New Update is orange.
- DNSSEC activate is orange.
- Add New DNS Zone: Create is orange.
- Parked Templates: Apply is orange.

## Secondary buttons/links
- DNS Zones page: Manage is secondary outline.
- Status page: Check Zone File is secondary outline.
- SSL page: Change is secondary outline.
- SOA page: Reset is secondary outline.
- Failover back/history/notification navigation buttons are secondary outline.

## Danger buttons/links
- DNS Records row delete icons are red.
- DNS Records Execute is red when Delete is selected.
- Mail Forwards row delete icons are red.
- Mail Forwards bulk Delete is red.
- DNS Zones Delete is red.
- Zone Transfers Delete is red.
- SSL Deactivate is red.
- DNSSEC Deactivate is red.
- Advanced > Deactivate Zone is red/danger styled.
- Mobile Deactivate Zone link is red/danger styled.

## Regression checks
- Advanced dropdown still opens and is not clipped.
- Main menu styling remains unchanged.
- DNS Records add/edit/delete still works.
- Mail Forwards add/edit/delete still works.
- Zone Transfers add/delete still works.
- DNS/API behavior is unchanged.
