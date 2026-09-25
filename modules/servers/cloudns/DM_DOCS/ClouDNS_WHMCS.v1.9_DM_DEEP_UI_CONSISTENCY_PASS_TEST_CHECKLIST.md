# ClouDNS WHMCS v1.9 DM Deep UI Consistency Pass - Test Checklist

## Visual Checks

- DNS Records page: content box is white, rounded, and aligned; Add button and toolbar controls remain aligned.
- Mail Forwards page: white content box matches DNS Records page; Add/Delete controls look consistent.
- Statistics page: white content box and table styling match the rest of the module.
- Status page: status panel, server rows, and action buttons retain spacing and consistent styling.
- SOA page: form fields and Save/Reset buttons have consistent heights and spacing.
- DNSSEC pages: inactive/active/waiting states retain the DNSSEC page reference and use consistent notice/panel styling.
- SSL page: action boxes, selects, buttons, and response messages match the module style.
- Zone Transfers page: notice, add-IP form, list rows, Save/Delete buttons, and empty state are visually consistent.
- Import Zone File page: table, textarea, and Import Records button are aligned and styled consistently.
- Export Zone File page: helper text, format dropdown, View/Download buttons, and output area match the module style.
- DNS Zones list: white rounded list, Manage/Delete buttons, and mobile menu still display correctly.
- Add New DNS Zone: type selector and all zone-type forms display inside consistent white panels.

## Functional Smoke Checks

- Switch Domain still redirects correctly.
- Advanced dropdown still opens on hover/click and keeps the requested order.
- DNS Records list still sorts/searches.
- Add/Edit/Delete DNS record paths still load.
- Mail Forwards add/edit/delete paths still load.
- Zone Transfers Save/Delete still submit.
- SOA Save/Reset still submit.
- SSL Activate/Change/Deactivate still submit.
- Import and Export Zone File actions still submit.
- Status page still shows Request New Update and Check Zone File actions.

## Validation Performed

- PHP lint on all PHP files.
- ZIP integrity test.
