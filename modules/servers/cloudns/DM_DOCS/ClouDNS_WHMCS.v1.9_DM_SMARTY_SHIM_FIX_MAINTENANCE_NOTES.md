# Known-Good Restore Point

This version is the known-good working DM build after the failed shared-template cleanup rollback.

Use this package as the restore point if a future cleanup or ClouDNS upgrade causes the WHMCS client-area module pages to fall back to the default product summary page.

Known-good package names:

```text
ClouDNS_WHMCS.v1.9_DM_install_only.zip
ClouDNS_WHMCS.v1.9_DM_source_with_notes.zip
```

Important rule:

```text
Do not reintroduce runtime `{include file="cloudns-ui-common.tpl"}` statements across templates unless tested directly inside WHMCS.
```


---

# ClouDNS WHMCS v1.9 DM - Maintenance and Future Update Notes

## Important repair note

A previous cleanup attempt centralized runtime UI CSS with a shared Smarty include. In WHMCS, that approach caused the client-area module pages to fall back to the default product summary page.

This repaired package rolls back the runtime shared-template include change and returns to the last known working module state.

The maintenance notes are still included as documentation, but the module runtime does **not** depend on a shared `cloudns-ui-common.tpl` include.

## Purpose

This file documents the custom DM changes so future ClouDNS module updates are easier to merge.

When ClouDNS releases a new WHMCS module version, upload the new original ZIP and compare it against:

1. The current ClouDNS original module used as the vendor baseline.
2. The current DM customized module.
3. The new ClouDNS original module.

The goal is to separate vendor updates from DM customizations and avoid redoing UI work manually.

---

## Future cleanup guidance

The best long-term cleanup is still to centralize duplicated UI styles, but it should be done in a WHMCS-safe way.

Avoid adding raw Smarty includes at the top of every template unless tested inside WHMCS.

Safer options for a future cleanup:

```text
Option A: keep the current working duplicated CSS and document it clearly.
Option B: move shared CSS into header-settings.tpl and slave-header-settings.tpl only.
Option C: load a physical CSS file through a stable web path, if WHMCS/template path behavior is confirmed.
```

Do not repeat the broken approach:

```smarty
{include file="cloudns-ui-common.tpl"}
```

at the top of every template without WHMCS runtime testing.

---

## Config file setting

Monitoring icons are controlled by:

```text
cloudns/cloudns_config.php
```

Default:

```php
'show_monitoring_icons' => false,
```

To show monitoring icons:

```php
'show_monitoring_icons' => true,
```

Do not overwrite this file during future upgrades unless intentionally resetting local module settings.

---

## Main DM feature map

### DNS Records page

Key template:

```text
cloudns/templates/records.tpl
```

Customizations:

```text
custom sort arrows
search toggle for Host / Points To
bulk Delete / Change TTL row
orange execute button
Switch Domain controller
+Add button
main-page action icon style
config-controlled monitoring icons
dynamic action column width when monitoring icons are hidden
```

### Mail Forwards page

Key template:

```text
cloudns/templates/mail-forwarding.tpl
```

Customizations:

```text
Mail Forward MX record check
Add missing Mail Forward MX Records button
Delete button instead of Mass Delete
Switch Domain controller
+Add button
main-page action icons
centered checkbox column
equal Email and Points To columns
```

### Header / menu

Key template:

```text
cloudns/templates/header-settings.tpl
```

Customizations:

```text
Advanced dropdown
Free SSL menu item
Zone Transfers menu item
Updated menu item
Export Zone File menu item
Parked Templates menu item
Cloud Domains removed
hover/click behavior for Advanced menu
```

### Free SSL

Files:

```text
cloudns/templates/free-ssl.tpl
cloudns/cloudns_core/freessl.php
```

Routes:

```text
free-ssl
freessl-activate
freessl-deactivate
freessl-change-issuer
```

### Zone Transfers

Files:

```text
cloudns/templates/zone-transfers.tpl
cloudns/cloudns_core/zonetransfers.php
```

Routes:

```text
zone-transfers
zone-transfers-add
zone-transfers-delete
```

### Export Zone File

Files:

```text
cloudns/templates/export-zone-file.tpl
cloudns/cloudns_core/zoneexport.php
```

Route:

```text
export-zone-file
```

### Parked Templates

Files:

```text
cloudns/templates/parked-templates.tpl
cloudns/cloudns_core/parkedtemplates.php
```

Route/menu label:

```text
Parked Templates
```

### Updated / server update status

Files:

```text
cloudns/templates/update-status.tpl
cloudns/cloudns_core/zones.php
```

Custom API wrapper added for server-by-server update status.

---

## Important custom files added by DM

These files are DM custom additions and should be preserved/reapplied during future upgrades:

```text
cloudns/cloudns_config.php
cloudns/templates/free-ssl.tpl
cloudns/templates/zone-transfers.tpl
cloudns/templates/export-zone-file.tpl
cloudns/templates/parked-templates.tpl
cloudns/cloudns_core/freessl.php
cloudns/cloudns_core/zonetransfers.php
cloudns/cloudns_core/zoneexport.php
cloudns/cloudns_core/parkedtemplates.php
```

Also preserve/review:

```text
cloudns/DM_MAINTENANCE_NOTES.md
```

---

## Existing ClouDNS files with DM modifications

Review these carefully during future upgrades:

```text
cloudns/cloudns.php
cloudns/cloudns_core/controller.php
cloudns/cloudns_core/zones.php
cloudns/templates/header-settings.tpl
cloudns/templates/records.tpl
cloudns/templates/mail-forwarding.tpl
cloudns/templates/update-status.tpl
cloudns/templates/zones.tpl
cloudns/templates/slave/master-servers.tpl
```

---

## Recommended future upgrade workflow

When ClouDNS releases a new module:

### 1. Save three ZIPs

```text
A = current ClouDNS original baseline
B = current DM customized module
C = new ClouDNS original module
```

### 2. Compare vendor changes

Compare:

```text
A -> C
```

This shows what ClouDNS changed.

### 3. Compare DM customizations

Compare:

```text
A -> B
```

This shows what DM customized.

### 4. Merge in this order

Recommended merge order:

```text
1. Start from new ClouDNS original module.
2. Reapply DM config file.
3. Reapply cloudns.php route/action additions.
4. Reapply added cloudns_core classes.
5. Reapply header/menu changes.
6. Reapply records.tpl custom table UI.
7. Reapply mail-forwarding.tpl custom UI and MX logic.
8. Reapply added feature templates.
9. Re-run PHP syntax validation.
10. Run client-area manual tests.
```

### 5. Watch for overlap

Review carefully if ClouDNS changed these areas:

```text
cloudns.php routing/customAction logic
cloudns_core/controller.php zone ownership or zone list logic
cloudns_core/zones.php API wrappers
templates/header-settings.tpl
templates/records.tpl
templates/mail-forwarding.tpl
templates/update-status.tpl
```

These are the highest-overlap files.

---

## Safe validation checklist after future upgrades

### PHP syntax

Run:

```bash
find cloudns -name '*.php' -print0 | xargs -0 -n1 php -l
```

### Template/client-area tests

Test these pages:

```text
DNS Records page loads
Record add works
Record edit works
Record delete works
Bulk Delete works
Change TTL works
Switch Domain works on DNS Records
Mail Forwards page loads
Mail forward add works
Mail forward edit works
Mail forward delete works
Mail Forward MX prompt works when records are missing
Switch Domain works on Mail Forwards
Free SSL page loads
Zone Transfers page loads
Export Zone File page loads
Parked Templates page loads
Updated page loads
Advanced menu hover/click works
Monitoring icons hide/show via cloudns_config.php
```

If any page shows only the WHMCS product summary panel, treat that as a template rendering failure and roll back the most recent template include/path change.

---

## Do not refactor during vendor upgrades unless needed

For future ClouDNS updates, avoid unnecessary PHP/controller rewrites.

Preferred approach:

```text
keep vendor PHP logic as close to ClouDNS original as possible
keep DM UI customizations documented and isolated where WHMCS-safe
only modify controller/API files where a DM feature requires it
```

This reduces merge risk.

## Final Free SSL button adjustment

The Free SSL page bottom buttons were normalized so they match in color, font, and size.

Buttons affected:

```text
Activate
Change
Deactivate
```

The `Change` button still submits to the Free SSL issuer change route. The visible label was shortened from `Change Issuer` to `Change` because `Change Issuer` did not fit cleanly inside the Switch-style button width.

## Free SSL button final fix

The Free SSL page bottom buttons were normalized so they match in color, font, and size.

Buttons affected:

```text
Activate
Change
Deactivate
```

The `Change` button still submits to the Free SSL issuer change route. The visible label was shortened from `Change Issuer` to `Change` because `Change Issuer` did not fit cleanly inside the Switch-style button width.

No shared-template runtime include was added.

## Export Zone File button fix

The Export Zone File page action buttons were normalized so `View` and `Download` match in color, font, and size.

Buttons affected:

```text
View
Download
```

Both buttons now use the same scoped Export action button CSS and Switch-style orange treatment.

No shared-template runtime include was added.

## Parked Templates Apply action

The Parked Templates page now includes real actions.

Added API methods:

```text
dns/get-parked-templates.json
dns/get-parked-settings.json
dns/set-parked-settings.json
```

Added client-area route:

```text
parked-templates-apply
```

The page now lets the user:

```text
view current parked settings
choose a parked template
edit optional title, description, and keywords
choose contact form behavior
apply a template from the form
apply a template directly from the template table
```

All `Apply` action buttons on the Parked Templates page use the same orange Switch-style color, font, height, hover, and active styling.

No shared-template runtime include was added.

## Parked Templates Switch Domain control

The Parked Templates page now includes a Switch Domain control matching the Email Forwarding pattern.

Added UI behavior:

```text
domain selector
Switch button
redirect to customAction=parked-templates for the selected zone
```

The Switch button uses the same orange Switch-style color, font, height, hover, and active styling.

No shared-template runtime include was added.

## Parked Zone Add Flow

The Add Zone page now includes a Parked Zone option.

Added UI:

```text
Add new DNS zone -> Type -> Parked Zone
```

Added backend support:

```text
zoneType = parkedZoneType
dns/register.json
zone-type = parked
```

After a parked zone is created successfully, the module redirects to:

```text
customAction=parked-templates
```

Parked Templates availability:

```text
Master zone  -> Advanced / Parked Templates hidden
Slave zone   -> Advanced / Parked Templates hidden
Parked zone  -> Advanced / Parked Templates visible
```

The Parked Templates page is also gated, so if a non-parked zone URL is opened manually, the page displays a not-parked notice and does not expose template Apply controls.

No shared-template runtime include was added.

## Parked Zone Detection Fix

The Parked Templates page no longer relies only on `zoneInfo.type == parked`.

Reason:

```text
ClouDNS may not return the exact string `parked` in zone info immediately after creating a parked zone.
```

Updated behavior:

```text
Parked Templates page calls dns/get-parked-settings.json.
If parked settings succeeds, the zone is treated as parked.
If parked settings returns a not-parked error, Apply controls stay hidden.
```

The Apply action also validates through parked settings before calling `dns/set-parked-settings.json`.

The Advanced menu gate was adjusted so Parked Templates can remain visible on the Parked Templates page and when the API confirms the zone is parked.

No shared-template runtime include was added.

## cloudns-ui-common.tpl compatibility shim

A safe empty compatibility shim was added at:

```text
templates/cloudns-ui-common.tpl
```

Reason:

```text
Some installs or stale WHMCS compiled templates may still reference cloudns-ui-common.tpl.
```

The module source does not use the shared runtime include approach. The shim prevents Smarty from crashing if a stale compiled template or incomplete upload still references the file.

Recommended install cleanup:

```text
Upload the entire cloudns/ folder from the install ZIP.
Clear WHMCS compiled templates/cache after upload.
```

No shared-template runtime include was reintroduced.
