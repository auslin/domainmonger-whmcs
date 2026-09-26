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

## Deployment access model

Normal development does not write to production.

- GitHub is the code source of truth and GitHub Actions is the normal deployment path.
- `Repository Validation` runs on `main` pushes, pull requests, and on demand; staging and production deployment workflows also run `scripts/validate-repo.sh` themselves before any deployment step.
- WHMCS staging deploys use the protected `staging` GitHub Environment and staging-only FTPS credentials restricted to the staging `/manage/` tree.
- WHMCS production deploys use the protected `production` GitHub Environment and a dedicated SSH key restricted by `authorized_keys` to the WHMCS deployment gate.
- The WHMCS production SSH identity cannot open a general shell, allocate a PTY, forward ports, forward an agent, or use X11.
- The restricted WHMCS SSH gate can invoke only the guarded WHMCS production deploy command.
- Production is a separate manual deployment action after staging passes and the user explicitly approves production deployment.
- Generic instructions such as `continue`, `do it`, or approval of a staging result are not production authorization.
- Desktop/WinSCP/mRemoteNG access is not part of the normal deployment path and is reserved for user-controlled break-glass diagnostics or recovery.
- Production deployment creates a timestamped rollback bundle and before/after SHA-256 manifests before the release is considered complete.

## Standard workflow

1. Start with a GitHub Issue.
2. Create the change in Git and commit it.
3. Deploy the exact intended commit to shared staging with **Actions → WHMCS Staging Deployment** and the required confirmation `DEPLOY-STAGING`.
4. Test the affected WHMCS routes and workflows on staging.
5. For shared frontend changes, validate WordPress and `/manage/` together and keep production blocked until the paired integration is clean.
6. Merge or otherwise ensure the validated commit is the current `main` commit.
7. Record the exact full 40-character SHA that passed staging.
8. Wait for explicit user authorization to deploy that validated release to production.
9. Start **Actions → WHMCS Production Deployment** from `main`.
10. Enter the exact staging-tested SHA and the required confirmation `DEPLOY-WHMCS-PRODUCTION`.
11. Pass the GitHub `production` Environment approval gate.
12. The workflow verifies the restricted WHMCS SSH gate, then the server independently validates the requested SHA against current `origin/main`.
13. The guarded server deployment creates the rollback bundle and pre-deploy SHA-256 manifest before writing managed production files.
14. The guarded server deployment applies the exact validated commit, clears WHMCS compiled templates, and writes the post-deploy manifest.
15. GitHub checks public `/manage/` reachability.
16. Re-request every affected live route and verify a concrete marker or workflow behavior; reachability alone is not final verification.
17. Record the production verification and rollback-bundle path in the related Issue/PR.

## Deployment commands

### Staging

The normal staging path is GitHub Actions:

1. Open **Actions → WHMCS Staging Deployment**.
2. Select the intended branch/commit.
3. Enter `DEPLOY-STAGING`.
4. Run the workflow.
5. Validate the affected staging WHMCS routes and workflows.

The staging workflow deploys only the maintained WHMCS payload through staging-only FTPS credentials restricted to the staging `/manage/` tree. It uses the repo-owned `scripts/ftps-overlay.py` uploader, which uploads/replaces files without deleting remote application files. It does not have production credentials.

The normal staging workflow intentionally excludes `templates/stellar-software-integration-whmcs/integration/`.

The server-side staging scripts remain available for controlled diagnostics or recovery, but they are not the normal deployment route.

### Production

The normal production path is the manual **WHMCS Production Deployment** GitHub Actions workflow.

The workflow requires all of the following before production code can be written:

- it must be started from `main`;
- the user must enter the full 40-character SHA of the exact staging-tested commit;
- that SHA must equal current `origin/main`;
- the user must enter `DEPLOY-WHMCS-PRODUCTION`;
- the GitHub `production` Environment gate must be approved;
- the pinned SSH host key must match;
- the dedicated WHMCS SSH key must authenticate as the restricted deployment account;
- the forced-command gate must reject a general shell command before deployment proceeds;
- the server-side deploy wrapper independently checks that the requested SHA equals current `origin/main`;
- the server Git checkout must be clean.

The restricted WHMCS SSH key cannot open a general shell. It is forced through the DomainMonger WHMCS SSH gate and may invoke only the guarded WHMCS production deployment command.

Before a production apply writes managed WHMCS files, the guarded server deployment creates a timestamped rollback bundle under `/home/register/production-backups/` plus a pre-deploy SHA-256 manifest. After deployment it writes a second manifest and clears compiled WHMCS templates.

The normal GitHub production workflow intentionally excludes `templates/stellar-software-integration-whmcs/integration/`. A coordinated WordPress/WHMCS integration release requires a separately reviewed deployment path after the paired release has passed shared staging; do not silently broaden the normal workflow to include those fragments.

`lang/overrides/english.php` is deployed normally as maintained Git source code.

The direct server-side production scripts remain the guarded deployment engine used underneath GitHub and are also available for user-controlled break-glass recovery. They are not the normal release trigger.

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
