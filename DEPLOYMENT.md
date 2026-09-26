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
