# Maintenance Notes

## Package Contents
Two final packages are provided:

1. `ClouDNS_WHMCS.v1.9_DM_FINAL_RC_install_only.zip`
   - Ready-to-upload WHMCS module package.
   - Contains the `cloudns/` module folder.

2. `ClouDNS_WHMCS.v1.9_DM_FINAL_RC_everything.zip`
   - Contains the install package, unpacked final module, diffs, release notes, validation output, and maintenance documentation.

## Recommended Install
1. Back up the existing WHMCS module folder:
   - `modules/servers/cloudns/`
2. Upload/replace the `cloudns/` folder from the Install Only package.
3. Clear WHMCS template cache.
4. Hard refresh browser cache.
5. Test client-area module pages.

## Important Notes
- Social share icons are not handled by this module package. They should be handled through WHMCS/theme/settings.
- Backend zone-limit enforcement remains in the original module flow.
- The new `Limit Reached` UI is a client-area guard/indicator, not a replacement for backend checks.
- The package zone limit is sourced from the module's existing `zonesLimit` / config option 1 mapping.
- Registered-domain-only products continue to hide the Add Zone action.

## Future Maintenance Guidance
- Avoid broad UI rewrites unless a real screenshot/test issue appears.
- Prefer targeted template/CSS patches.
- Keep changes isolated and validate with PHP lint before deployment.
- When comparing to upstream ClouDNS releases, use the included diff files as the project-specific customization map.
