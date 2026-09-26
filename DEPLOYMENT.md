# DomainMonger WHMCS Deployment Workflow

GitHub is the source of truth for maintained DomainMonger WHMCS customizations.

## Environments

### Production

- URL: `https://domainmonger.com/manage/`
- WHMCS root: `/home/register/public_html/manage/`
- PHP: 8.3

### Staging

- URL: `https://staging.domainmonger.com/manage/`
- WHMCS root: `/home/register/staging/domainmonger.com/public_html/manage/`
- PHP: 8.3

WordPress and WHMCS remain separate projects even though they share the same hostname hierarchy. Files outside `/manage/` belong to the WordPress project unless explicitly documented otherwise.

## Standard workflow

1. Start with a GitHub Issue.
2. Create a feature/fix branch from `main`.
3. Make the smallest scoped change needed.
4. Deploy the branch to staging.
5. Test the affected WHMCS routes and workflows on staging.
6. Open/update the PR with the staging results.
7. Merge only after staging validation passes.
8. Back up affected production files/data when the change requires it.
9. Deploy the merged `main` change to production.
10. Purge applicable production page/server/CDN caches for the affected `/manage/` routes.
11. Re-request the affected public routes after the purge.
12. Verify the public response reflects the newly deployed code/assets.
13. Record the production verification in the related Issue/PR.

## WordPress theme / WHMCS integration production gate

The WHMCS integration consumes frontend output/assets from the WordPress WizardPanel/Stellar stack. Those two repositories therefore form one release pair whenever shared frontend output changes.

**Production is blocked if shared staging shows any unresolved integration regression.**

For a linked WizardPanel/Stellar update:

1. Use the exact WordPress version already deployed to shared staging as the integration source.
2. Regenerate and commit the matching WHMCS integration fragments.
3. Deploy the matching integration to WHMCS staging.
4. Validate affected `/manage/` pages together with the staged WordPress site, including shared header/footer/assets and an order/domain-registration route when relevant.
5. If buttons, icons, CSS, JavaScript, menus, layout, or any shared presentation is broken on staging, keep both production deployments blocked.
6. Resolve the linked staging issue and retest before either project is promoted.
7. Once both sides pass, deploy the WordPress version and its matching WHMCS integration together in the same coordinated production window.
8. Purge applicable caches and verify live `/manage/` responses reference the intended matching WordPress assets.

Never knowingly deploy one side of a mismatched theme/integration pair to production with a plan to repair the other side afterward.

## Required cache invalidation after deployment

A successful file copy is **not** sufficient production verification.

After any production deployment that can affect rendered WHMCS HTML, CSS, JavaScript, templates, hooks, or client-area output:

- purge any applicable LiteSpeed/server/CDN page cache for the affected `/manage/` routes;
- request the affected live URLs again after the purge;
- confirm the response is not an old cached copy;
- verify at least one concrete marker of the new deployment, such as:
  - an expected asset/version reference,
  - changed HTML/text,
  - a new CSS/JS file timestamp or query version,
  - the corrected UI element or workflow behavior;
- where response headers expose cache state, record whether the first request was a cache miss and subsequent requests are serving the newly generated response.

Do not assume the WordPress LiteSpeed Cache plugin is responsible for WHMCS caching. WHMCS cache invalidation should use the applicable server/hosting/CDN mechanism for `/manage/`.

## Cache-related regression lesson

During the WizardPanel 3.1 WordPress production migration, the new files were deployed correctly but LiteSpeed continued serving stale pre-upgrade HTML. Direct application rendering showed the new code was present, while public HTTP responses still referenced the old assets. The issue disappeared after the production page cache was purged and the affected routes were re-primed.

The WHMCS deployment workflow therefore treats **cache purge + public post-purge verification** as a mandatory deployment step.

## Rollback

1. Identify the last known-good Git commit.
2. Restore the affected files/data from the pre-deployment backup if needed.
3. Purge applicable caches.
4. Re-request the affected public routes.
5. Verify the rollback response and workflow behavior.
6. Record the rollback in the related GitHub Issue/PR.

## DomainMonger source-of-truth rules

- GitHub `main` is authoritative for maintained WHMCS custom code.
- Production and staging are deployed/runtime copies, not development baselines.
- Do not use local PC project files, old ZIPs, or deployed runtime files as the source for new development unless explicitly approved.
- If deployed runtime code exists that is not represented in GitHub, reconcile it into GitHub before further work.
- Production promotion must come from GitHub `main`, not from the staging filesystem.

## Staging safety rules

- Staging WHMCS database: `register_whmcs_stg`.
- WHMCS email sending remains disabled on staging.
- No cron entry may target `staging.domainmonger.com` or `/home/register/staging/`.
- Do not enable production payment processors merely to expose a staging UI element.
- Offline `mailin` may be used for controlled WHMCS metadata/tests where a payment-method identifier is required.
- Registrar/provider actions on staging are only for explicit controlled tests.
- Do not commit license keys, database credentials, API keys, encryption hashes, or other secrets.

## Maintained language overrides

`lang/overrides/english.php` is maintained source code and follows the same Git-based deployment workflow as other tracked WHMCS customizations.

- GitHub `main` is authoritative for the complete intended file.
- Make language changes in Git, deploy that version to staging, test it, and promote the same approved version to production.
- Do not preserve undocumented staging-only or production-only language edits. Treat them as drift and reconcile any legitimate change back into Git before the next deployment.
- The former merge-only / never-overwrite exception is retired now that GitHub is the source of truth.

## Protected project paths

### Stellar integration folder

Do not modify `stellar-software-integration-whmcs/integration` unless that integration content is explicitly part of the requested change.

## Scope and regression rules

- Prefer page-specific hooks/templates for page-specific changes.
- Avoid broad rewrites of stable code.
- Preserve the custom v8x Register Domain namespinner unless explicitly changing it.
- Do not reintroduce the old 17-result cap, sticky/reset experiments, or reset filters.
- RegistrarDNS changes must preserve the RecordID-first and synthetic-ID fail-closed invariants tracked in Issue #8.
- Failed changes are cleaned up in the next fix from the last confirmed-good baseline or restore point.

## Retired environment

`domainmonger.info` and the old `domaininfo` cPanel/database environment are historical references only.

They must not be used as staging targets, deployment targets, code sources, or fallbacks for current work.
