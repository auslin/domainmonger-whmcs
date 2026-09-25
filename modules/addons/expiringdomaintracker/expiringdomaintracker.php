<?php
/**
 * Expiring Domain Tracker
 *
 * DomainMonger WHMCS 9 / PHP 8.3 addon for tracking domains through:
 * Active -> Expired -> Redemption -> Pending Delete -> Available.
 *
 * Data sources:
 * - WHMCS tbldomains for domain/client/expiry/registrar data
 * - WHMCS invoice tables for orphaned domain-invoice auditing
 * - Addon configuration for lifecycle defaults + TLD exceptions
 * - NetEarthOne / LogicBoxes API for NEO Customer IDs (cached)
 * - Addon tables for manual ratings, Reconciliation entries, and NEO terminal snapshots
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

const EDT_VERSION = '1.9.10';
const EDT_RATINGS_TABLE = 'mod_expiringdomaintracker_ratings';
const EDT_RECON_TABLE = 'mod_expiringdomaintracker_reconciliation';
const EDT_NEO_TABLE = 'mod_expiringdomaintracker_neo_cache';
const EDT_META_TABLE = 'mod_expiringdomaintracker_meta';
const EDT_NEO_LOG_TABLE = 'mod_expiringdomaintracker_neo_refresh_log';
const EDT_NEO_TERMINAL_TABLE = 'mod_expiringdomaintracker_neo_terminal';
const EDT_NEO_TERMINAL_STATE_META = 'neo_terminal_scan_state';
const EDT_NEO_TERMINAL_COMPLETE_KEY_META = 'neo_terminal_complete_key';
const EDT_NEO_TERMINAL_COMPLETE_INFO_META = 'neo_terminal_complete_info';
const EDT_NEO_TERMINAL_SCAN_VERSION = 2;

function expiringdomaintracker_config(): array
{
    $defaultExceptions = implode("\n", [
        'org,16,,',
        'io,36,,',
        'ooo,36,,',
        'tv,36,,',
        'us,36,36,',
        'ca,40,,',
        'co,40,,',
        'digital,40,,',
        'lol,40,,',
        'co.uk,90,,',
        'org.uk,90,,',
        'uk,90,,',
        'eu,,40,',
    ]);

    return [
        'name' => 'Expiring Domain Tracker',
        'description' => 'Track expiring domains through Expired, Redemption, Pending Delete, and Available, with Watch List, Reconciliation, registrar-scoped NEO terminal-order auditing, native WHMCS domain-invoice cleanup, orphaned-invoice cancellation, and expired-domain cleanup.',
        'version' => EDT_VERSION,
        'author' => 'DomainMonger',
        'language' => 'english',
        'fields' => [
            'expiration_window_days' => [
                'FriendlyName' => 'Expiration Window',
                'Type' => 'text',
                'Size' => '8',
                'Default' => '15',
                'Description' => 'Days before expiration that a domain enters the Expiring Domains list.',
            ],
            'default_spread_days' => [
                'FriendlyName' => 'Default Expiration → Redemption',
                'Type' => 'text',
                'Size' => '8',
                'Default' => '30',
                'Description' => 'Default Spread: days after expiration before Redemption begins.',
            ],
            'default_redemption_days' => [
                'FriendlyName' => 'Default Redemption → Pending Delete',
                'Type' => 'text',
                'Size' => '8',
                'Default' => '30',
                'Description' => 'Default number of days a domain remains in Redemption before Pending Delete.',
            ],
            'default_pending_delete_days' => [
                'FriendlyName' => 'Default Pending Delete → Available',
                'Type' => 'text',
                'Size' => '8',
                'Default' => '6',
                'Description' => 'Default number of days in Pending Delete before the domain becomes Available.',
            ],
            'available_display_days' => [
                'FriendlyName' => 'Available Display Days',
                'Type' => 'text',
                'Size' => '8',
                'Default' => '1',
                'Description' => 'Number of days to keep a domain visible as Available on Expiring Domains. 1 = the calculated Available Date only. Watch List domains remain tracked independently.',
            ],
            'lifecycle_exceptions' => [
                'FriendlyName' => 'TLD Lifecycle Exceptions',
                'Type' => 'textarea',
                'Rows' => '14',
                'Cols' => '70',
                'Default' => $defaultExceptions,
                'Description' => 'One per line: TLD,Spread,RedemptionDays,PendingDeleteDays. Leave a value blank to use the default. Example: digital,40,,',
            ],
            'search_engine' => [
                'FriendlyName' => 'Domain Name Search Engine',
                'Type' => 'dropdown',
                'Options' => 'Google,Bing,DuckDuckGo,Brave',
                'Default' => 'Google',
                'Description' => 'Search engine used when clicking the Domain Name text in Expiring Domains or Watch List.',
            ],
            'nightly_neo_refresh' => [
                'FriendlyName' => 'Nightly NEO Refresh',
                'Type' => 'dropdown',
                'Options' => 'Enabled,Disabled',
                'Default' => 'Enabled',
                'Description' => 'Refresh NEO data automatically once per night using the WHMCS system cron.',
            ],
            'nightly_neo_start_hour' => [
                'FriendlyName' => 'Nightly NEO Start Hour',
                'Type' => 'text',
                'Size' => '4',
                'Default' => '1',
                'Description' => 'Hour (0-23) when the nightly NEO sweep may begin. Default 1 = 1:00 AM in the WHMCS/PHP timezone.',
            ],
        ],
    ];
}

function expiringdomaintracker_activate(): array
{
    try {
        edt_ensure_schema();
        return [
            'status' => 'success',
            'description' => 'Expiring Domain Tracker is ready. Configure lifecycle defaults and TLD exceptions in the addon settings.',
        ];
    } catch (Throwable $e) {
        return [
            'status' => 'error',
            'description' => 'Expiring Domain Tracker could not create its database tables: ' . $e->getMessage(),
        ];
    }
}

function expiringdomaintracker_deactivate(): array
{
    return [
        'status' => 'success',
        'description' => 'Expiring Domain Tracker has been deactivated. Ratings, Watch List data, Reconciliation entries, and NEO cache were retained.',
    ];
}

function expiringdomaintracker_upgrade(array $vars): void
{
    edt_ensure_schema();
}

function edt_ensure_schema(): void
{
    $schema = Capsule::schema();

    if (!$schema->hasTable(EDT_RATINGS_TABLE)) {
        $schema->create(EDT_RATINGS_TABLE, function ($table) {
            $table->increments('id');
            $table->unsignedInteger('domain_id');
            $table->string('domain', 255);
            $table->unsignedTinyInteger('rating')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique('domain_id', 'edt_rating_domain_unique');
            $table->index(['rating', 'domain_id'], 'edt_rating_lookup');
        });
    }

    if (!$schema->hasTable(EDT_RECON_TABLE)) {
        $schema->create(EDT_RECON_TABLE, function ($table) {
            $table->increments('id');
            $table->string('domain', 255);
            $table->string('action_needed', 255);
            $table->string('blocked_by', 255)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('Open');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->index(['status', 'created_at'], 'edt_recon_status');
            $table->index('domain', 'edt_recon_domain');
        });
    }

    if (!$schema->hasTable(EDT_NEO_TABLE)) {
        $schema->create(EDT_NEO_TABLE, function ($table) {
            $table->increments('id');
            $table->unsignedInteger('domain_id');
            $table->string('domain', 255);
            $table->string('customer_id', 64)->nullable();
            $table->string('order_status', 80)->nullable();
            $table->date('registry_created_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->unique('domain_id', 'edt_neo_domain_unique');
            $table->index('customer_id', 'edt_neo_customer');
        });
    } else {
        if (!$schema->hasColumn(EDT_NEO_TABLE, 'order_status')) {
            $schema->table(EDT_NEO_TABLE, function ($table) {
                $table->string('order_status', 80)->nullable()->after('customer_id');
            });
        }
        if (!$schema->hasColumn(EDT_NEO_TABLE, 'registry_created_at')) {
            $schema->table(EDT_NEO_TABLE, function ($table) {
                $table->date('registry_created_at')->nullable()->after('order_status');
            });
        }
    }

    if (!$schema->hasTable(EDT_META_TABLE)) {
        $schema->create(EDT_META_TABLE, function ($table) {
            $table->increments('id');
            $table->string('meta_key', 80);
            $table->longText('meta_value')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique('meta_key', 'edt_meta_key_unique');
        });
    }

    if (!$schema->hasTable(EDT_NEO_LOG_TABLE)) {
        $schema->create(EDT_NEO_LOG_TABLE, function ($table) {
            $table->increments('id');
            $table->string('run_date', 10);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('total_domains')->default(0);
            $table->unsignedInteger('checked_domains')->default(0);
            $table->unsignedInteger('current_domains')->default(0);
            $table->unsignedInteger('deleted_domains')->default(0);
            $table->unsignedInteger('failed_domains')->default(0);
            $table->string('result', 20)->default('Running');
            $table->text('last_error')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique('run_date', 'edt_neo_log_run_date_unique');
            $table->index('started_at', 'edt_neo_log_started');
        });
    }

    if (!$schema->hasTable(EDT_NEO_TERMINAL_TABLE)) {
        $schema->create(EDT_NEO_TERMINAL_TABLE, function ($table) {
            $table->increments('id');
            $table->string('scan_key', 40);
            $table->char('domain_hash', 40);
            $table->string('domain', 255);
            $table->string('neo_order_id', 40)->nullable();
            $table->string('customer_id', 64)->nullable();
            $table->string('order_status', 20);
            $table->string('order_timestamp', 40)->nullable();
            $table->string('source_registrar', 64)->nullable();
            $table->timestamp('seen_at')->nullable();
            $table->unique(['scan_key', 'domain_hash'], 'edt_neo_terminal_scan_domain_unique');
            $table->index(['scan_key', 'order_status'], 'edt_neo_terminal_scan_status');
            $table->index('domain_hash', 'edt_neo_terminal_domain_hash');
        });
    }
}

function expiringdomaintracker_output(array $vars): void
{
    try {
        edt_ensure_schema();
    } catch (Throwable $e) {
        echo edt_alert('danger', 'The addon database tables are not available: ' . $e->getMessage());
        return;
    }

    $baseModuleLink = (string) ($vars['modulelink'] ?? 'addonmodules.php?module=expiringdomaintracker');
    $config = edt_lifecycle_config($vars);
    $moduleLink = edt_module_link_with_window($baseModuleLink, (int) $config['window']);
    $tab = edt_active_tab();
    $notice = null;
    $error = null;

    if (strtolower(trim((string) ($_GET['edt_diag'] ?? ''))) === 'invoice-cleanup') {
        echo edt_styles();
        echo '<div class="edt-wrap"><div class="edt-title"><div><h2>Expiring Domain Tracker</h2>';
        echo '<p>Read-only WHMCS invoice-cleanup capability diagnostic.</p></div></div>';
        echo edt_invoice_cleanup_diagnostic($moduleLink);
        echo '</div>';
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_token('WHMCS.admin.default');
        try {
            [$notice, $error] = edt_handle_post($vars, $moduleLink, $tab);
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }

        if ((string) ($_POST['edt_ajax'] ?? '') === 'rating') {
            $ajaxOk = $error === null;
            $ajaxMessage = $ajaxOk ? (string) ($notice ?? 'Rating saved.') : (string) $error;
            echo '<span data-edt-rating-ajax-result data-ok="' . ($ajaxOk ? '1' : '0') . '" data-message="' . edt_escape($ajaxMessage) . '"></span>';
            return;
        }
    }

    $ratings = edt_load_ratings();
    [$mainRows, $watchRows] = edt_load_domain_rows($config, $ratings);
    $expiredRows = edt_load_expired_cleanup_rows();
    $neoCache = edt_load_neo_cache(array_unique(array_merge(
        array_column($mainRows, 'id'),
        array_column($watchRows, 'id'),
        array_column($expiredRows, 'id')
    )));

    echo edt_styles();
    echo '<div class="edt-wrap">';
    echo '<div class="edt-title"><div><h2>Expiring Domain Tracker</h2>';
    echo '<p>Track expiring domains through Redemption, Pending Delete, and Availability.</p></div></div>';
    echo edt_tabs($moduleLink, $tab);

    if ($notice) {
        echo edt_alert('success', $notice);
    }
    if ($error) {
        echo edt_alert('danger', $error);
    }

    if ($tab === 'watch') {
        echo edt_domain_tab($moduleLink, 'watch', $watchRows, $neoCache, $config);
    } elseif ($tab === 'reconciliation') {
        echo edt_reconciliation_tab($moduleLink);
    } elseif ($tab === 'expired') {
        echo edt_expired_domains_tab($moduleLink, $expiredRows, $neoCache);
    } elseif ($tab === 'neo-log') {
        echo edt_neo_refresh_log_tab();
    } elseif ($tab === 'neo-terminal') {
        echo edt_neo_terminal_tab($moduleLink);
    } elseif ($tab === 'orphan-invoices') {
        echo edt_orphan_invoices_tab($moduleLink);
    } else {
        echo edt_domain_tab($moduleLink, 'expiring', $mainRows, $neoCache, $config);
    }

    echo edt_scripts();
    echo '</div>';
}

/**
 * Display runtime capability metadata only. No client, domain, or invoice data
 * is read or changed by this diagnostic.
 */
function edt_invoice_cleanup_diagnostic(string $moduleLink): string
{
    $lines = [
        'Expiring Domain Tracker: ' . EDT_VERSION,
        'PHP: ' . PHP_VERSION,
    ];

    try {
        $whmcsVersion = trim((string) Capsule::table('tblconfiguration')->where('setting', 'Version')->value('value'));
        if ($whmcsVersion !== '') {
            $lines[] = 'WHMCS: ' . $whmcsVersion;
        }
    } catch (Throwable $e) {
        $lines[] = 'WHMCS: version unavailable';
    }

    $helperFiles = [
        'includes/domainfunctions.php',
        'includes/invoicefunctions.php',
        'includes/adminfunctions.php',
    ];
    foreach ($helperFiles as $helperFile) {
        $helperPath = defined('ROOTDIR') ? rtrim((string) ROOTDIR, '/\\') . '/' . $helperFile : '';
        if ($helperPath === '' || !is_file($helperPath)) {
            $lines[] = 'HELPER ' . $helperFile . ': NOT FOUND';
            continue;
        }
        try {
            require_once $helperPath;
            $lines[] = 'HELPER ' . $helperFile . ': LOADED';
        } catch (Throwable $e) {
            $lines[] = 'HELPER ' . $helperFile . ': LOAD ERROR ' . get_class($e);
        }
    }

    $classes = [
        'WHMCS\\Billing\\Domains\\Invoice',
        'WHMCS\\Admin\\Domain\\DomainController',
        'WHMCS\\Admin\\Domain\\DomainRouteProvider',
        'WHMCS\\Domains\\Controller\\DomainController',
        'WHMCS\\Domain\\DomainProcessor',
        'WHMCS\\Domain\\DomainRepository',
        'WHMCS\\Domain\\Domain',
    ];

    foreach ($classes as $className) {
        $lines[] = '';
        if (!class_exists($className)) {
            $lines[] = 'CLASS ' . $className . ': NOT AVAILABLE';
            continue;
        }

        try {
            $reflection = new ReflectionClass($className);
            $lines[] = 'CLASS ' . $className . ': AVAILABLE';
            $methods = [];
            foreach ($reflection->getMethods() as $method) {
                if ($method->getDeclaringClass()->getName() !== $className) {
                    continue;
                }

                $visibility = $method->isPublic() ? 'public' : ($method->isProtected() ? 'protected' : 'private');
                $signature = $visibility . ($method->isStatic() ? ' static ' : ' ') . $method->getName() . '(';
                $parameters = [];
                foreach ($method->getParameters() as $parameter) {
                    $parameterText = '';
                    if ($parameter->hasType()) {
                        $parameterText .= (string) $parameter->getType() . ' ';
                    }
                    if ($parameter->isPassedByReference()) {
                        $parameterText .= '&';
                    }
                    if ($parameter->isVariadic()) {
                        $parameterText .= '...';
                    }
                    $parameterText .= '$' . $parameter->getName();
                    if ($parameter->isDefaultValueAvailable()) {
                        try {
                            $parameterText .= '=' . var_export($parameter->getDefaultValue(), true);
                        } catch (Throwable $e) {
                            $parameterText .= '=DEFAULT';
                        }
                    }
                    $parameters[] = $parameterText;
                }
                $signature .= implode(', ', $parameters) . ')';
                if ($method->hasReturnType()) {
                    $signature .= ': ' . (string) $method->getReturnType();
                }
                $methods[] = $signature;
            }
            natcasesort($methods);
            if (!$methods) {
                $lines[] = '  (no methods declared directly on this class)';
            } else {
                foreach ($methods as $method) {
                    $lines[] = '  ' . $method;
                }
            }
        } catch (Throwable $e) {
            $lines[] = 'CLASS ' . $className . ': REFLECTION ERROR ' . get_class($e);
        }
    }

    $lines[] = '';
    $lines[] = 'RELEVANT LOADED FUNCTIONS';
    $functions = get_defined_functions();
    $userFunctions = is_array($functions['user'] ?? null) ? $functions['user'] : [];
    $relevantFunctions = [];
    foreach ($userFunctions as $functionName) {
        if (str_starts_with(strtolower((string) $functionName), 'edt_')
            || str_starts_with(strtolower((string) $functionName), 'expiringdomaintracker_')) {
            continue;
        }
        if (preg_match('/(?:domain|invoice).*(?:cancel|delete|remove|renew)|(?:cancel|delete|remove|renew).*(?:domain|invoice)/i', (string) $functionName)) {
            $relevantFunctions[] = (string) $functionName;
        }
    }
    natcasesort($relevantFunctions);
    if (!$relevantFunctions) {
        $lines[] = '  (none loaded)';
    } else {
        foreach (array_slice($relevantFunctions, 0, 250) as $functionName) {
            try {
                $function = new ReflectionFunction($functionName);
                $parameters = [];
                foreach ($function->getParameters() as $parameter) {
                    $parameterText = '';
                    if ($parameter->hasType()) {
                        $parameterText .= (string) $parameter->getType() . ' ';
                    }
                    if ($parameter->isPassedByReference()) {
                        $parameterText .= '&';
                    }
                    if ($parameter->isVariadic()) {
                        $parameterText .= '...';
                    }
                    $parameterText .= '$' . $parameter->getName();
                    if ($parameter->isDefaultValueAvailable()) {
                        try {
                            $parameterText .= '=' . var_export($parameter->getDefaultValue(), true);
                        } catch (Throwable $e) {
                            $parameterText .= '=DEFAULT';
                        }
                    }
                    $parameters[] = $parameterText;
                }
                $signature = $functionName . '(' . implode(', ', $parameters) . ')';
                if ($function->hasReturnType()) {
                    $signature .= ': ' . (string) $function->getReturnType();
                }
                $lines[] = '  ' . $signature;
            } catch (Throwable $e) {
                $lines[] = '  ' . $functionName . '(SIGNATURE UNAVAILABLE)';
            }
        }
    }

    $diagnostic = implode("\n", $lines);
    $html = '<div class="edt-panel"><div class="edt-panel-head"><div><h3>Invoice Cleanup Diagnostic</h3>';
    $html .= '<p>This page only lists available WHMCS classes and method signatures. It does not read or change domains or invoices.</p></div></div>';
    $html .= '<div class="alert alert-info edt-audit-note">Select and copy the complete result below, then paste it into the chat.</div>';
    $html .= '<textarea id="edt-invoice-cleanup-diagnostic" class="form-control" readonly style="width:100%;min-height:520px;font-family:monospace;font-size:12px;white-space:pre">' . edt_escape($diagnostic) . '</textarea>';
    $html .= '<div class="edt-table-controls"><div class="edt-actions">';
    $html .= '<button type="button" class="btn edt-btn-secondary" onclick="var e=document.getElementById(\'edt-invoice-cleanup-diagnostic\');e.focus();e.select();">Select Diagnostic</button>';
    $html .= '<a class="btn edt-btn-primary" href="' . edt_escape($moduleLink) . '">Return to Tracker</a>';
    $html .= '</div></div></div>';
    return $html;
}

function edt_active_tab(): string
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $posted = strtolower(trim((string) ($_POST['edt_tab'] ?? '')));
        if (in_array($posted, ['expiring', 'watch', 'reconciliation', 'expired', 'neo-log', 'neo-terminal', 'orphan-invoices'], true)) {
            return $posted;
        }
    }

    $tab = strtolower(trim((string) ($_GET['tab'] ?? 'expiring')));
    return in_array($tab, ['expiring', 'watch', 'reconciliation', 'expired', 'neo-log', 'neo-terminal', 'orphan-invoices'], true) ? $tab : 'expiring';
}

function edt_handle_post(array $vars, string $moduleLink, string $tab): array
{
    $action = (string) ($_POST['edt_action'] ?? '');

    if ($action === 'save_ratings') {
        $submitted = is_array($_POST['rating'] ?? null) ? $_POST['rating'] : [];
        $domainIds = array_values(array_unique(array_filter(array_map('intval', array_keys($submitted)))));
        $validDomains = [];
        if ($domainIds) {
            $validDomains = Capsule::table('tbldomains')
                ->whereIn('id', $domainIds)
                ->pluck('domain', 'id')
                ->all();
        }

        $saved = 0;
        foreach ($submitted as $domainIdRaw => $ratingRaw) {
            $domainId = (int) $domainIdRaw;
            if ($domainId <= 0 || !isset($validDomains[$domainId])) {
                continue;
            }

            $ratingText = trim((string) $ratingRaw);
            if ($ratingText === '') {
                Capsule::table(EDT_RATINGS_TABLE)->where('domain_id', $domainId)->delete();
                continue;
            }

            $rating = (int) $ratingText;
            if ($rating < 1 || $rating > 4) {
                continue;
            }

            $now = date('Y-m-d H:i:s');
            $exists = Capsule::table(EDT_RATINGS_TABLE)->where('domain_id', $domainId)->exists();
            if ($exists) {
                Capsule::table(EDT_RATINGS_TABLE)
                    ->where('domain_id', $domainId)
                    ->update([
                        'domain' => strtolower((string) $validDomains[$domainId]),
                        'rating' => $rating,
                        'updated_at' => $now,
                    ]);
            } else {
                Capsule::table(EDT_RATINGS_TABLE)->insert([
                    'domain_id' => $domainId,
                    'domain' => strtolower((string) $validDomains[$domainId]),
                    'rating' => $rating,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            $saved++;
        }

        return [$saved . ' rating' . ($saved === 1 ? '' : 's') . ' saved.', null];
    }

    if ($action === 'refresh_neo') {
        $ids = edt_parse_id_list((string) ($_POST['neo_domain_ids'] ?? ''));
        if (!$ids) {
            return [null, 'No NEO domains were available to refresh.'];
        }
        $force = !empty($_POST['neo_force']);
        $result = edt_refresh_neo_customer_ids($ids, 50, $force);
        $message = 'NEO Data refresh: ' . $result['success'] . ' updated';
        if ($result['skipped'] > 0) {
            $message .= ', ' . $result['skipped'] . ' already current';
        }
        if ($result['failed'] > 0) {
            $message .= ', ' . $result['failed'] . ' failed';
        }
        if ($result['remaining'] > 0) {
            $message .= '. ' . $result['remaining'] . ' still need refresh; run NEO again.';
        } else {
            $message .= '.';
        }
        return [$message, null];
    }

    if ($action === 'scan_neo_terminal') {
        $restart = !empty($_POST['neo_terminal_restart']);
        $result = edt_run_neo_terminal_scan($restart, 6);
        if (!$result['ok']) {
            return [null, (string) ($result['error'] ?? 'The NEO Deleted/Archived scan could not continue.')];
        }

        // The tab renders the persistent yellow in-progress or blue completed
        // scan state. Do not repeat the same details in a one-time green notice.
        return [null, null];
    }

    if ($action === 'neo_terminal_domain_action') {
        $domainIds = edt_parse_id_list((string) ($_POST['neo_domain_ids'] ?? ''));
        $operation = strtolower(trim((string) ($_POST['neo_domain_operation'] ?? '')));
        if (!$domainIds || !in_array($operation, ['cancel', 'delete'], true)) {
            return [null, 'The requested NEO domain action was not valid.'];
        }

        $domains = Capsule::table('tbldomains')
            ->whereIn('id', $domainIds)
            ->get(['id', 'userid', 'domain', 'expirydate', 'status', 'registrar', 'donotrenew']);
        $domainsById = [];
        foreach ($domains as $domain) {
            $domainsById[(int) $domain->id] = $domain;
        }

        $validationRegistrars = array_values(array_map(
            static fn(array $account): string => (string) ($account['registrar'] ?? ''),
            edt_neo_terminal_registrar_accounts()
        ));
        $completed = [];
        $blocked = [];
        foreach ($domainIds as $domainId) {
            $domain = $domainsById[$domainId] ?? null;
            if (!$domain) {
                $blocked[] = '#' . $domainId . ' no longer exists in WHMCS';
                continue;
            }

            [$ok, $resultMessage] = edt_apply_neo_terminal_domain_action($domain, $operation, $validationRegistrars);
            if ($ok) {
                $completed[] = edt_normalize_domain((string) ($domain->domain ?? ''));
            } else {
                $blocked[] = $resultMessage;
            }
        }

        $success = null;
        $error = null;
        if ($completed) {
            $pastTense = $operation === 'delete' ? 'deleted' : 'cancelled';
            $success = count($completed) . ' selected domain' . (count($completed) === 1 ? ' was' : 's were') . ' ' . $pastTense
                . ' in WHMCS after current NEO confirmation and verified native WHMCS invoice handling. No registrar command was sent.';
        }
        if ($blocked) {
            $pastTense = $operation === 'delete' ? 'deleted' : 'cancelled';
            $error = count($blocked) . ' selected domain' . (count($blocked) === 1 ? ' was' : 's were') . ' not ' . $pastTense . ': ' . implode('; ', $blocked) . '.';
        }
        return [$success, $error];
    }

    if ($action === 'cancel_orphan_invoices') {
        $invoiceIds = edt_parse_id_list((string) ($_POST['orphan_invoice_ids'] ?? ''));
        if (!$invoiceIds) {
            return [null, 'Select at least one Invoice Only row to cancel.'];
        }

        $cancelled = [];
        $blocked = [];
        foreach ($invoiceIds as $invoiceId) {
            [$ok, $message] = edt_cancel_orphan_invoice_with_whmcs($invoiceId);
            if ($ok) {
                $cancelled[] = $message;
            } else {
                $blocked[] = $message;
            }
        }

        $success = null;
        $error = null;
        if ($cancelled) {
            $success = count($cancelled) . ' orphaned invoice' . (count($cancelled) === 1 ? ' was' : 's were')
                . ' cancelled through WHMCS after live Invoice Only validation.';
        }
        if ($blocked) {
            $error = count($blocked) . ' selected invoice' . (count($blocked) === 1 ? ' was' : 's were')
                . ' not cancelled: ' . implode('; ', $blocked) . '.';
        }
        return [$success, $error];
    }

    if ($action === 'delete_expired_domains') {
        $domainIds = edt_parse_id_list((string) ($_POST['expired_domain_ids'] ?? ''));
        if (!$domainIds) {
            return [null, 'Select at least one expired domain to delete.'];
        }

        $domains = Capsule::table('tbldomains')
            ->whereIn('id', $domainIds)
            ->get(['id', 'userid', 'domain', 'expirydate', 'status', 'registrar', 'donotrenew']);
        $domainsById = [];
        foreach ($domains as $domain) {
            $domainsById[(int) $domain->id] = $domain;
        }

        $validationRegistrars = array_values(array_map(
            static fn(array $account): string => (string) ($account['registrar'] ?? ''),
            edt_neo_terminal_registrar_accounts()
        ));
        $deleted = [];
        $blocked = [];
        foreach ($domainIds as $domainId) {
            $domain = $domainsById[$domainId] ?? null;
            if (!$domain) {
                $blocked[] = '#' . $domainId . ' no longer exists in WHMCS';
                continue;
            }

            $actualDomain = edt_normalize_domain((string) ($domain->domain ?? ''));
            if (strcasecmp((string) ($domain->status ?? ''), 'Expired') !== 0) {
                $blocked[] = $actualDomain . ' is no longer Expired in WHMCS';
                continue;
            }
            if (!edt_is_neo_registrar(strtolower(trim((string) ($domain->registrar ?? ''))))) {
                $blocked[] = $actualDomain . ' is not assigned to the NEO registrar module';
                continue;
            }

            // Always perform a fresh current-order check across configured NEO
            // accounts immediately before destructive WHMCS cleanup.
            $current = edt_neo_current_domain_state(
                $actualDomain,
                $validationRegistrars,
                strtolower(trim((string) ($domain->registrar ?? '')))
            );
            $lookup = is_array($current['lookup'] ?? null) ? $current['lookup'] : null;
            if ($lookup !== null) {
                edt_store_neo_cache($domainId, $actualDomain, $lookup);
            }
            if (($current['result'] ?? '') === 'live') {
                $blocked[] = $actualDomain . ': current NEO status ' . (trim((string) ($current['order_status'] ?? '')) ?: 'Live');
                continue;
            }
            if (($current['result'] ?? '') !== 'terminal') {
                $blocked[] = $actualDomain . ': current NEO status could not be confirmed';
                continue;
            }
            $neoStatus = trim((string) ($current['order_status'] ?? ''));

            // Disable Auto Renew, then delegate existing invoice handling to
            // WHMCS's native domain billing service and verify its result.
            [$renewalOk, $renewalChanged, $renewalError] = edt_disable_domain_auto_renew($domain);
            if (!$renewalOk) {
                $blocked[] = $actualDomain . ': ' . $renewalError;
                continue;
            }

            [$invoiceCleanupOk, $invoiceCleanupChanged, $invoiceCleanupError] = edt_cleanup_domain_invoices_with_whmcs($domainId);
            if (!$invoiceCleanupOk) {
                $blocked[] = $actualDomain . ': ' . $invoiceCleanupError;
                continue;
            }

            try {
                Capsule::connection()->transaction(function () use ($domainId): void {
                    $count = Capsule::table('tbldomains')->where('id', $domainId)->where('status', 'Expired')->delete();
                    if ($count !== 1) {
                        throw new RuntimeException('The WHMCS domain record changed before deletion.');
                    }
                    Capsule::table(EDT_RATINGS_TABLE)->where('domain_id', $domainId)->delete();
                    Capsule::table(EDT_NEO_TABLE)->where('domain_id', $domainId)->delete();
                });
                $deleted[] = $actualDomain;
                if (function_exists('logActivity')) {
                    logActivity(
                        'Expiring Domain Tracker: Deleted expired WHMCS domain ' . $actualDomain . ' after live NEO status ' . $neoStatus
                        . '. Auto Renew ' . ($renewalChanged ? 'was disabled through WHMCS first' : 'was already disabled')
                        . '. Native WHMCS invoice cleanup ' . ($invoiceCleanupChanged ? 'was run' : 'was not needed') . '. No registrar command was sent.',
                        (int) ($domain->userid ?? 0)
                    );
                }
            } catch (Throwable $e) {
                $blocked[] = $actualDomain . ': ' . $e->getMessage();
            }
        }

        $success = null;
        $error = null;
        if ($deleted) {
            $success = count($deleted) . ' expired domain' . (count($deleted) === 1 ? '' : 's')
                . ' deleted from WHMCS after live NEO confirmation and verified native WHMCS invoice handling. No registrar delete command was sent.';
        }
        if ($blocked) {
            $error = count($blocked) . ' selected domain' . (count($blocked) === 1 ? ' was' : 's were') . ' not deleted: ' . implode('; ', $blocked) . '.';
        }
        return [$success, $error];
    }

    if ($action === 'recon_add') {
        $domain = edt_normalize_domain((string) ($_POST['domain'] ?? ''));
        $actionNeeded = trim((string) ($_POST['action_needed'] ?? ''));
        $blockedBy = trim((string) ($_POST['blocked_by'] ?? ''));
        $notes = trim((string) ($_POST['notes'] ?? ''));

        if ($domain === '' || strpos($domain, '.') === false) {
            return [null, 'Enter a valid domain for Reconciliation.'];
        }
        if ($actionNeeded === '') {
            return [null, 'Enter the action that needs to be completed.'];
        }

        $now = date('Y-m-d H:i:s');
        Capsule::table(EDT_RECON_TABLE)->insert([
            'domain' => $domain,
            'action_needed' => mb_substr($actionNeeded, 0, 255),
            'blocked_by' => $blockedBy === '' ? null : mb_substr($blockedBy, 0, 255),
            'notes' => $notes === '' ? null : $notes,
            'status' => 'Open',
            'created_at' => $now,
            'updated_at' => $now,
            'resolved_at' => null,
        ]);
        return ['Reconciliation entry added.', null];
    }

    if (in_array($action, ['recon_resolve', 'recon_reopen', 'recon_delete'], true)) {
        $id = (int) ($_POST['recon_id'] ?? 0);
        if ($id <= 0 || !Capsule::table(EDT_RECON_TABLE)->where('id', $id)->exists()) {
            return [null, 'The Reconciliation entry could not be found.'];
        }

        if ($action === 'recon_delete') {
            Capsule::table(EDT_RECON_TABLE)->where('id', $id)->delete();
            return ['Reconciliation entry deleted.', null];
        }

        $now = date('Y-m-d H:i:s');
        if ($action === 'recon_resolve') {
            Capsule::table(EDT_RECON_TABLE)->where('id', $id)->update([
                'status' => 'Resolved',
                'resolved_at' => $now,
                'updated_at' => $now,
            ]);
            return ['Reconciliation entry marked Resolved.', null];
        }

        Capsule::table(EDT_RECON_TABLE)->where('id', $id)->update([
            'status' => 'Open',
            'resolved_at' => null,
            'updated_at' => $now,
        ]);
        return ['Reconciliation entry reopened.', null];
    }

    return [null, 'No supported action was submitted.'];
}

/**
 * Apply one selected NEO terminal-domain action after confirming that no
 * configured NEO account currently has a live order for the domain.
 *
 * @return array{0: bool, 1: string}
 */
function edt_apply_neo_terminal_domain_action($domain, string $operation, array $validationRegistrars): array
{
    $domainId = (int) ($domain->id ?? 0);
    $actualDomain = edt_normalize_domain((string) ($domain->domain ?? ''));
    $registrar = strtolower(trim((string) ($domain->registrar ?? '')));
    if ($domainId <= 0 || $actualDomain === '' || !in_array($operation, ['cancel', 'delete'], true)) {
        return [false, ($actualDomain !== '' ? $actualDomain : '#' . $domainId) . ': the selected domain action was not valid'];
    }
    if (!edt_is_neo_registrar($registrar)) {
        return [false, $actualDomain . ': the domain is not assigned to the NEO registrar module'];
    }

    // Reconfirm current status across every configured NEO account. A live
    // registration or transfer always wins over an older Deleted/Archived order.
    $current = edt_neo_current_domain_state($actualDomain, $validationRegistrars, $registrar);
    $lookup = is_array($current['lookup'] ?? null) ? $current['lookup'] : null;
    if ($lookup !== null) {
        edt_store_neo_cache($domainId, $actualDomain, $lookup);
    }
    if (($current['result'] ?? '') === 'live') {
        $status = trim((string) ($current['order_status'] ?? ''));
        return [false, $actualDomain . ': current NEO status is ' . ($status !== '' ? $status : 'Live') . ', so no WHMCS change was made'];
    }
    if (($current['result'] ?? '') !== 'terminal') {
        return [false, $actualDomain . ': current NEO status could not be confirmed, so no WHMCS change was made'];
    }

    $neoStatus = trim((string) ($current['order_status'] ?? ''));

    // Set Disable Auto Renew, then delegate existing invoice handling to
    // WHMCS's native domain billing service. The addon does not mutate invoice
    // records itself.
    [$renewalOk, $renewalChanged, $renewalError] = edt_disable_domain_auto_renew($domain);
    if (!$renewalOk) {
        return [false, $actualDomain . ': ' . $renewalError];
    }

    [$invoiceCleanupOk, $invoiceCleanupChanged, $invoiceCleanupError] = edt_cleanup_domain_invoices_with_whmcs($domainId);
    if (!$invoiceCleanupOk) {
        return [false, $actualDomain . ': ' . $invoiceCleanupError];
    }

    if ($operation === 'cancel') {
        if (strcasecmp(trim((string) ($domain->status ?? '')), 'Cancelled') !== 0) {
            [$updated, $updateError] = edt_whmcs_update_client_domain($domainId, ['status' => 'Cancelled']);
            if (!$updated) {
                return [false, $actualDomain . ': WHMCS invoice handling completed, but the domain could not be marked Cancelled: ' . $updateError];
            }

            $storedStatus = trim((string) Capsule::table('tbldomains')->where('id', $domainId)->value('status'));
            if (strcasecmp($storedStatus, 'Cancelled') !== 0) {
                return [false, $actualDomain . ': WHMCS returned success but the domain status was not saved as Cancelled'];
            }
        }

        if (function_exists('logActivity')) {
            logActivity(
                'Expiring Domain Tracker: Marked WHMCS domain ' . $actualDomain . ' Cancelled after current NEO status ' . $neoStatus
                . '. Auto Renew ' . ($renewalChanged ? 'was disabled through WHMCS first' : 'was already disabled')
                . '. Native WHMCS invoice cleanup ' . ($invoiceCleanupChanged ? 'was run' : 'was not needed') . '. No registrar command was sent.',
                (int) ($domain->userid ?? 0)
            );
        }
        return [true, $actualDomain];
    }

    try {
        Capsule::connection()->transaction(function () use ($domainId, $domain): void {
            $count = Capsule::table('tbldomains')
                ->where('id', $domainId)
                ->where('domain', (string) ($domain->domain ?? ''))
                ->where('status', (string) ($domain->status ?? ''))
                ->where('registrar', (string) ($domain->registrar ?? ''))
                ->delete();
            if ($count !== 1) {
                throw new RuntimeException('The WHMCS domain record changed before deletion.');
            }
            Capsule::table(EDT_RATINGS_TABLE)->where('domain_id', $domainId)->delete();
            Capsule::table(EDT_NEO_TABLE)->where('domain_id', $domainId)->delete();
        });
    } catch (Throwable $e) {
        return [false, $actualDomain . ': WHMCS invoice handling completed, but the domain could not be deleted: ' . $e->getMessage()];
    }

    if (function_exists('logActivity')) {
        logActivity(
            'Expiring Domain Tracker: Deleted WHMCS domain ' . $actualDomain . ' after current NEO status ' . $neoStatus
            . '. Auto Renew ' . ($renewalChanged ? 'was disabled through WHMCS first' : 'was already disabled')
            . '. Native WHMCS invoice cleanup ' . ($invoiceCleanupChanged ? 'was run' : 'was not needed') . '. No registrar command was sent.',
            (int) ($domain->userid ?? 0)
        );
    }
    return [true, $actualDomain];
}

/**
 * Update a domain through WHMCS's supported Internal API. Invoice state is
 * verified separately because this API does not run the interactive Admin Area
 * Disable Auto Renew invoice-cleanup workflow.
 *
 * @return array{0: bool, 1: string}
 */
function edt_whmcs_update_client_domain(int $domainId, array $changes): array
{
    if ($domainId <= 0) {
        return [false, 'The WHMCS domain ID was not valid.'];
    }
    if (!function_exists('localAPI')) {
        return [false, 'The WHMCS Internal API is unavailable.'];
    }

    $adminId = (int) ($_SESSION['adminid'] ?? 0);
    if ($adminId <= 0) {
        return [false, 'The current WHMCS administrator session could not be identified.'];
    }

    try {
        $adminUsername = trim((string) Capsule::table('tbladmins')->where('id', $adminId)->value('username'));
        if ($adminUsername === '') {
            return [false, 'The current WHMCS administrator account could not be identified.'];
        }

        $response = localAPI('UpdateClientDomain', array_merge(['domainid' => $domainId], $changes), $adminUsername);
    } catch (Throwable $e) {
        return [false, 'WHMCS could not update the domain: ' . $e->getMessage()];
    }

    if (!is_array($response) || strcasecmp(trim((string) ($response['result'] ?? '')), 'success') !== 0) {
        $message = trim((string) ($response['message'] ?? $response['error'] ?? ''));
        if ($message === '') {
            $message = 'WHMCS returned an unsuccessful response.';
        }
        return [false, mb_substr($message, 0, 500)];
    }

    return [true, ''];
}

/**
 * Set Disable Auto Renew through the supported domain API. The API saves the
 * flag but does not guarantee the interactive Admin Area invoice-cleanup path,
 * so callers must verify invoice state before cancelling or deleting a domain.
 *
 * @return array{0: bool, 1: bool, 2: string}
 */
function edt_disable_domain_auto_renew($domain): array
{
    $domainId = (int) ($domain->id ?? 0);
    if ($domainId <= 0) {
        return [false, false, 'The WHMCS domain ID was not valid.'];
    }
    if ((int) ($domain->donotrenew ?? 0) === 1) {
        return [true, false, ''];
    }

    [$updated, $error] = edt_whmcs_update_client_domain($domainId, ['donotrenew' => true]);
    if (!$updated) {
        return [false, false, 'WHMCS Disable Auto Renew failed: ' . $error];
    }

    $storedValue = (int) Capsule::table('tbldomains')->where('id', $domainId)->value('donotrenew');
    if ($storedValue !== 1) {
        return [false, false, 'WHMCS returned success but Disable Auto Renew was not saved.'];
    }

    return [true, true, ''];
}

/**
 * Let WHMCS perform its own domain invoice cancellation/credit-note workflow.
 * The addon only selects the domain and verifies the resulting billing state.
 *
 * @return array{0: bool, 1: bool, 2: string}
 */
function edt_cleanup_domain_invoices_with_whmcs(int $domainId): array
{
    if ($domainId <= 0) {
        return [false, false, 'The WHMCS domain ID was not valid for invoice cleanup.'];
    }

    $before = edt_domain_invoice_cleanup_state($domainId);
    if (empty($before['open_items'])) {
        return [true, false, ''];
    }

    $serviceClass = 'WHMCS\\Billing\\Domains\\Invoice';
    $domainClass = 'WHMCS\\Domain\\Domain';
    $collectionClass = 'Illuminate\\Database\\Eloquent\\Collection';
    $method = 'cancelInvoiceForExpiredDomains';
    if (!class_exists($serviceClass)
        || !class_exists($domainClass)
        || !class_exists($collectionClass)
        || !method_exists($serviceClass, $method)) {
        return [false, false, 'The native WHMCS domain invoice cleanup service is unavailable.'];
    }

    try {
        $domain = $domainClass::find($domainId);
        if (!$domain) {
            return [false, false, 'The WHMCS domain could not be loaded for native invoice cleanup.'];
        }

        $domains = new $collectionClass([$domain]);
        $service = new $serviceClass();
        $service->{$method}($domains);
    } catch (Throwable $e) {
        return [false, false, 'WHMCS native domain invoice cleanup failed: ' . mb_substr($e->getMessage(), 0, 500)];
    }

    [$verified, $verificationError] = edt_verify_native_domain_invoice_cleanup($domainId, $before);
    if (!$verified) {
        return [false, true, $verificationError];
    }

    return [true, true, ''];
}

/**
 * Capture the invoice items and latest domain credit-note item before WHMCS
 * performs cleanup. This allows both native outcomes to be verified: a
 * cancelled/removed invoice item, or a credit note on a mixed invoice.
 */
function edt_domain_invoice_cleanup_state(int $domainId): array
{
    $openItems = edt_open_domain_invoice_items($domainId);
    $creditNoteItemMaxId = 0;
    $creditNoteTracking = false;

    try {
        if (Capsule::schema()->hasTable('tblbillingnoteitems')) {
            $query = Capsule::table('tblbillingnoteitems')
                ->where('relid', $domainId)
                ->where('note_type', 'credit');
            edt_apply_domain_invoice_item_filter($query, 'type');
            $creditNoteItemMaxId = (int) $query->max('id');
            $creditNoteTracking = true;
        }
    } catch (Throwable $e) {
        $creditNoteItemMaxId = 0;
        $creditNoteTracking = false;
    }

    return [
        'open_items' => $openItems,
        'credit_note_item_max_id' => $creditNoteItemMaxId,
        'credit_note_tracking' => $creditNoteTracking,
    ];
}

function edt_open_domain_invoice_items(int $domainId): array
{
    if ($domainId <= 0) {
        return [];
    }

    $query = Capsule::table('tblinvoiceitems as ii')
        ->join('tblinvoices as i', 'i.id', '=', 'ii.invoiceid')
        ->where('ii.relid', $domainId)
        ->whereNotIn('i.status', edt_terminal_invoice_statuses());
    edt_apply_domain_invoice_item_filter($query, 'ii.type');

    $items = $query->orderBy('i.id', 'desc')->orderBy('ii.id')->get([
        'ii.id as item_id',
        'ii.invoiceid',
        'ii.type',
        'i.invoicenum',
        'i.status',
    ]);

    $result = [];
    foreach ($items as $item) {
        $result[] = [
            'item_id' => (int) ($item->item_id ?? 0),
            'invoice_id' => (int) ($item->invoiceid ?? 0),
            'invoicenum' => (string) ($item->invoicenum ?? ''),
            'status' => (string) ($item->status ?? ''),
            'type' => (string) ($item->type ?? ''),
        ];
    }
    return $result;
}

/**
 * @return array{0: bool, 1: string}
 */
function edt_verify_native_domain_invoice_cleanup(int $domainId, array $before): array
{
    $remaining = edt_open_domain_invoice_items($domainId);
    if (!$remaining) {
        return [true, ''];
    }

    $newCreditItems = 0;
    $creditNoteItemMaxId = max(0, (int) ($before['credit_note_item_max_id'] ?? 0));
    try {
        if (!empty($before['credit_note_tracking']) && Capsule::schema()->hasTable('tblbillingnoteitems')) {
            $query = Capsule::table('tblbillingnoteitems')
                ->where('id', '>', $creditNoteItemMaxId)
                ->where('relid', $domainId)
                ->where('note_type', 'credit')
                ->whereIn('status', ['issued', 'closed']);
            edt_apply_domain_invoice_item_filter($query, 'type');
            $newCreditItems = (int) $query->count();
        }
    } catch (Throwable $e) {
        $newCreditItems = 0;
    }

    if ($newCreditItems >= count($remaining)) {
        return [true, ''];
    }

    $labels = [];
    foreach ($remaining as $item) {
        $invoiceId = (int) ($item['invoice_id'] ?? 0);
        $status = trim((string) ($item['status'] ?? ''));
        if ($invoiceId > 0) {
            $labels[] = '#' . $invoiceId . ($status !== '' ? ' (' . $status . ')' : '');
        }
    }
    $labels = array_values(array_unique($labels));
    $invoiceLabel = $labels ? implode(', ', $labels) : 'an open invoice';

    return [
        false,
        'WHMCS native cleanup returned, but ' . $invoiceLabel
        . ' still contains an unverified domain charge. Cancel/Delete stopped before the domain status or record was changed',
    ];
}

function edt_lifecycle_config(array $vars): array
{
    $windowDefault = edt_positive_int($vars['expiration_window_days'] ?? 15, 15, 1, 365);
    $windowRaw = $_POST['edt_window'] ?? $_GET['edt_window'] ?? null;
    $window = $windowRaw === null || trim((string) $windowRaw) === ''
        ? $windowDefault
        : edt_positive_int($windowRaw, $windowDefault, 1, 365);
    $defaultSpread = edt_positive_int($vars['default_spread_days'] ?? 30, 30, 0, 365);
    $defaultRedemption = edt_positive_int($vars['default_redemption_days'] ?? 30, 30, 0, 365);
    $defaultPending = edt_positive_int($vars['default_pending_delete_days'] ?? 6, 6, 0, 365);
    $availableDisplayDays = edt_positive_int($vars['available_display_days'] ?? 1, 1, 1, 365);
    $exceptions = edt_parse_exceptions((string) ($vars['lifecycle_exceptions'] ?? ''));
    $searchEngine = edt_normalize_search_engine((string) ($vars['search_engine'] ?? 'Google'));

    return [
        'window' => $window,
        'windowDefault' => $windowDefault,
        'defaultSpread' => $defaultSpread,
        'defaultRedemption' => $defaultRedemption,
        'defaultPending' => $defaultPending,
        'availableDisplayDays' => $availableDisplayDays,
        'exceptions' => $exceptions,
        'searchEngine' => $searchEngine,
    ];
}

function edt_module_link_with_window(string $moduleLink, int $window): string
{
    $separator = str_contains($moduleLink, '?') ? '&' : '?';
    return $moduleLink . $separator . 'edt_window=' . max(1, min(365, $window));
}

function edt_expiration_window_control(int $window, int $defaultWindow): string
{
    $title = $window === $defaultWindow
        ? 'Using the Addon Config default.'
        : 'Temporary override for this visit. Leaving the addon and returning resets to the Addon Config default of ' . $defaultWindow . ' days.';

    // Keep this intentionally compact like the Show Records selector.  Include
    // common working windows, while always preserving any custom configured or
    // temporary value so an admin never loses access to the current setting.
    $choices = [1, 3, 5, 7, 10, 15, 20, 30, 45, 60, 90, 120, 180, 365, $defaultWindow, $window];
    $choices = array_values(array_unique(array_map(static fn($value): int => max(1, min(365, (int) $value)), $choices)));
    sort($choices, SORT_NUMERIC);

    $html = '<form method="get" action="addonmodules.php" class="edt-window-control" data-edt-window-control title="' . edt_escape($title) . '">';
    $html .= '<input type="hidden" name="module" value="expiringdomaintracker"><input type="hidden" name="tab" value="expiring">';
    $html .= '<select id="edt-window-days" class="form-control edt-window-select" name="edt_window" data-edt-window-days aria-label="Expiration window days">';
    foreach ($choices as $choice) {
        $selected = $choice === $window ? ' selected' : '';
        $html .= '<option value="' . $choice . '"' . $selected . '>' . $choice . ' Days</option>';
    }
    $html .= '</select>';
    $html .= '<i class="fas fa-spinner fa-spin edt-window-spinner" data-edt-window-spinner aria-hidden="true"></i>';
    if ($window !== $defaultWindow) {
        $html .= '<span class="edt-window-note">Default: ' . (int) $defaultWindow . ' Days</span>';
    }
    $html .= '</form>';
    return $html;
}

function edt_normalize_search_engine(string $value): string
{
    $value = strtolower(trim($value));
    return match ($value) {
        'bing' => 'bing',
        'duckduckgo' => 'duckduckgo',
        'brave' => 'brave',
        default => 'google',
    };
}

function edt_search_url(string $query, string $engine): string
{
    $encoded = rawurlencode(trim($query));
    return match (edt_normalize_search_engine($engine)) {
        'bing' => 'https://www.bing.com/search?q=' . $encoded,
        'duckduckgo' => 'https://duckduckgo.com/?q=' . $encoded,
        'brave' => 'https://search.brave.com/search?q=' . $encoded,
        default => 'https://www.google.com/search?q=' . $encoded,
    };
}

function edt_positive_int($value, int $default, int $min, int $max): int
{
    if (!is_numeric($value)) {
        return $default;
    }
    $n = (int) $value;
    return max($min, min($max, $n));
}

function edt_parse_exceptions(string $text): array
{
    $result = [];
    $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        $parts = array_map('trim', str_getcsv($line));
        $tld = strtolower(ltrim((string) ($parts[0] ?? ''), '.'));
        if ($tld === '' || !preg_match('/^[a-z0-9.-]+$/i', $tld)) {
            continue;
        }

        $result[$tld] = [
            'spread' => edt_optional_days($parts[1] ?? null),
            'redemption' => edt_optional_days($parts[2] ?? null),
            'pending' => edt_optional_days($parts[3] ?? null),
        ];
    }

    return $result;
}

function edt_optional_days($value): ?int
{
    $text = trim((string) $value);
    if ($text === '' || !is_numeric($text)) {
        return null;
    }
    return max(0, min(365, (int) $text));
}

function edt_effective_lifecycle(string $tld, array $config): array
{
    $tld = strtolower(ltrim($tld, '.'));
    $exception = $config['exceptions'][$tld] ?? [];

    return [
        'spread' => $exception['spread'] ?? $config['defaultSpread'],
        'redemption' => $exception['redemption'] ?? $config['defaultRedemption'],
        'pending' => $exception['pending'] ?? $config['defaultPending'],
    ];
}

function edt_max_lifecycle_days(array $config): int
{
    $maxSpread = $config['defaultSpread'];
    $maxRedemption = $config['defaultRedemption'];
    $maxPending = $config['defaultPending'];

    foreach ($config['exceptions'] as $row) {
        $maxSpread = max($maxSpread, (int) ($row['spread'] ?? $config['defaultSpread']));
        $maxRedemption = max($maxRedemption, (int) ($row['redemption'] ?? $config['defaultRedemption']));
        $maxPending = max($maxPending, (int) ($row['pending'] ?? $config['defaultPending']));
    }

    return $maxSpread + $maxRedemption + $maxPending + max(1, (int) ($config['availableDisplayDays'] ?? 1)) + 1;
}

function edt_load_ratings(): array
{
    $rows = Capsule::table(EDT_RATINGS_TABLE)->get(['domain_id', 'rating', 'domain']);
    $ratings = [];
    foreach ($rows as $row) {
        $ratings[(int) $row->domain_id] = (int) $row->rating;
    }
    return $ratings;
}

function edt_load_domain_rows(array $config, array $ratings): array
{
    $today = new DateTimeImmutable('today');
    $cutoff = $today->modify('+' . $config['window'] . ' days');
    $lowerBound = $today->modify('-' . edt_max_lifecycle_days($config) . ' days');

    $base = Capsule::table('tbldomains')
        ->select(['id', 'userid', 'domain', 'expirydate', 'status', 'registrar', 'donotrenew'])
        ->where('expirydate', '!=', '0000-00-00')
        ->where('expirydate', '>=', $lowerBound->format('Y-m-d'))
        ->where('expirydate', '<=', $cutoff->format('Y-m-d'))
        ->whereNotIn('status', ['Cancelled', 'Fraud', 'Transferred Away'])
        ->get();

    $rowsById = [];
    foreach ($base as $row) {
        $rowsById[(int) $row->id] = $row;
    }

    $watchIds = [];
    foreach ($ratings as $domainId => $rating) {
        if ($rating === 1 || $rating === 2) {
            $watchIds[] = (int) $domainId;
        }
    }

    $missingWatchIds = array_values(array_diff($watchIds, array_keys($rowsById)));
    if ($missingWatchIds) {
        $watchDomains = Capsule::table('tbldomains')
            ->select(['id', 'userid', 'domain', 'expirydate', 'status', 'registrar', 'donotrenew'])
            ->whereIn('id', $missingWatchIds)
            ->get();
        foreach ($watchDomains as $row) {
            $rowsById[(int) $row->id] = $row;
        }
    }

    $knownTlds = edt_known_tlds(array_keys($config['exceptions']));
    $main = [];
    $watch = [];

    foreach ($rowsById as $row) {
        $built = edt_build_domain_row($row, $ratings[(int) $row->id] ?? null, $config, $knownTlds, $today);
        if (!$built) {
            continue;
        }

        $isUpcoming = $built['expiryDate'] >= $today && $built['expiryDate'] <= $cutoff;
        $availableVisibleUntil = $built['availableDate']->modify('+' . max(0, ((int) ($config['availableDisplayDays'] ?? 1)) - 1) . ' days');
        $isCurrentLifecycle = $built['expiryDate'] < $today && $availableVisibleUntil >= $today;
        if ($isUpcoming || $isCurrentLifecycle) {
            $main[] = $built;
        }

        if ($built['rating'] === 1 || $built['rating'] === 2) {
            $watch[] = $built;
        }
    }

    usort($main, static function (array $a, array $b): int {
        $cmp = $a['expiryDate'] <=> $b['expiryDate'];
        return $cmp !== 0 ? $cmp : strcmp($a['domain'], $b['domain']);
    });
    usort($watch, static function (array $a, array $b): int {
        $cmp = $a['availableDate'] <=> $b['availableDate'];
        return $cmp !== 0 ? $cmp : strcmp($a['domain'], $b['domain']);
    });

    return [$main, $watch];
}

function edt_load_expired_cleanup_rows(): array
{
    $rows = Capsule::table('tbldomains')
        ->select(['id', 'userid', 'domain', 'expirydate', 'status', 'registrar'])
        ->where('status', 'Expired')
        ->where('expirydate', '!=', '0000-00-00')
        ->orderBy('expirydate', 'asc')
        ->orderBy('domain', 'asc')
        ->get();

    $result = [];
    foreach ($rows as $row) {
        $domain = edt_normalize_domain((string) ($row->domain ?? ''));
        $expiry = edt_date((string) ($row->expirydate ?? ''));
        if ($domain === '' || !$expiry) {
            continue;
        }
        $result[] = [
            'id' => (int) $row->id,
            'userid' => (int) $row->userid,
            'domain' => $domain,
            'expiryDate' => $expiry,
            'registrar' => strtolower(trim((string) ($row->registrar ?? ''))),
        ];
    }
    return $result;
}

function edt_known_tlds(array $exceptionTlds): array
{
    $tlds = [];
    foreach ($exceptionTlds as $tld) {
        $tld = strtolower(ltrim((string) $tld, '.'));
        if ($tld !== '') {
            $tlds[$tld] = true;
        }
    }

    try {
        $extensions = Capsule::table('tbldomainpricing')->pluck('extension')->all();
        foreach ($extensions as $extension) {
            $tld = strtolower(ltrim(trim((string) $extension), '.'));
            if ($tld !== '') {
                $tlds[$tld] = true;
            }
        }
    } catch (Throwable $e) {
        // Fall back to exceptions plus the last label.
    }

    $list = array_keys($tlds);
    usort($list, static fn(string $a, string $b): int => strlen($b) <=> strlen($a));
    return $list;
}

function edt_build_domain_row($row, ?int $rating, array $config, array $knownTlds, DateTimeImmutable $today): ?array
{
    $domain = edt_normalize_domain((string) ($row->domain ?? ''));
    if ($domain === '') {
        return null;
    }

    $expiry = edt_date((string) ($row->expirydate ?? ''));
    if (!$expiry) {
        return null;
    }

    [$sld, $tld] = edt_split_domain($domain, $knownTlds);
    $lifecycle = edt_effective_lifecycle($tld, $config);
    $redemption = $expiry->modify('+' . $lifecycle['spread'] . ' days');
    $pending = $redemption->modify('+' . $lifecycle['redemption'] . ' days');
    $available = $pending->modify('+' . $lifecycle['pending'] . ' days');

    if ($today < $expiry) {
        $status = 'Active';
    } elseif ($today < $redemption) {
        $status = 'Expired';
    } elseif ($today < $pending) {
        $status = 'Redemption';
    } elseif ($today < $available) {
        $status = 'Pending Delete';
    } else {
        $status = 'Available';
    }

    $daysToPending = (int) $today->diff($pending)->format('%r%a');

    return [
        'id' => (int) $row->id,
        'userid' => (int) $row->userid,
        'domain' => $domain,
        'sld' => $sld,
        'tld' => $tld,
        'length' => strlen($sld),
        'rating' => $rating,
        'whmcsStatus' => (string) ($row->status ?? ''),
        'autoRenew' => (int) ($row->donotrenew ?? 0) === 0,
        'registrar' => strtolower(trim((string) ($row->registrar ?? ''))),
        'status' => $status,
        'expiryDate' => $expiry,
        'redemptionDate' => $redemption,
        'pendingDate' => $pending,
        'availableDate' => $available,
        'daysToPending' => $daysToPending,
    ];
}

function edt_split_domain(string $domain, array $knownTlds): array
{
    $domain = strtolower(rtrim($domain, '.'));
    foreach ($knownTlds as $tld) {
        if ($domain === $tld) {
            continue;
        }
        $suffix = '.' . $tld;
        if (str_ends_with($domain, $suffix)) {
            $sld = substr($domain, 0, -strlen($suffix));
            if ($sld !== '') {
                return [$sld, $tld];
            }
        }
    }

    $pos = strrpos($domain, '.');
    if ($pos === false) {
        return [$domain, ''];
    }
    return [substr($domain, 0, $pos), substr($domain, $pos + 1)];
}

function edt_date(string $value): ?DateTimeImmutable
{
    $value = trim($value);
    if ($value === '' || $value === '0000-00-00') {
        return null;
    }
    try {
        return new DateTimeImmutable($value . ' 00:00:00');
    } catch (Throwable $e) {
        return null;
    }
}

function edt_sortable_th(string $label, int $index, string $type = 'text', string $extraClass = ''): string
{
    $class = trim('edt-sortable-heading sorting ' . $extraClass);
    return '<th class="' . edt_escape($class) . '" data-edt-sort-heading data-edt-sort-index="' . $index . '" data-edt-sort-type="' . edt_escape($type) . '">'
        . '<button type="button" class="edt-sort-button" data-edt-sort-button data-direction="none" aria-label="Sort by ' . edt_escape($label) . '" aria-sort="none">' . edt_escape($label) . '</button>'
        . '</th>';
}

function edt_page_size_control(bool $defaultAll = false): string
{
    return '<select class="form-control edt-page-size" data-edt-page-size aria-label="Rows per page" title="Rows per page"><option' . ($defaultAll ? '' : ' selected') . '>25</option><option>50</option><option>100</option><option value="all"' . ($defaultAll ? ' selected' : '') . '>All</option></select>';
}

function edt_domain_table_controls(bool $hasNeo, string $tab): string
{
    $html = '<div class="edt-table-controls">';
    $html .= '<div class="edt-actions">';
    if ($hasNeo) {
        $html .= '<button type="button" class="btn edt-btn-secondary" data-edt-neo-bulk-trigger data-edt-neo-bulk-form-id="edt-neo-bulk-' . edt_escape($tab) . '"><i class="fas fa-sync-alt" aria-hidden="true"></i> NEO</button>';
    }
    $html .= '<button type="button" class="btn edt-btn-secondary" data-edt-export-csv data-edt-export-name="' . edt_escape($tab === 'watch' ? 'watch-list' : 'expiring-domains') . '"><i class="fas fa-file-csv" aria-hidden="true"></i> Export</button>';
    $html .= '</div>';
    $html .= '<div class="edt-pager">' . edt_page_size_control($tab === 'expiring') . '<span data-edt-status>0 of 0</span><button type="button" class="btn edt-btn-secondary" data-edt-prev>Previous</button><button type="button" class="btn edt-btn-secondary" data-edt-next>Next</button></div>';
    $html .= '</div>';
    return $html;
}

function edt_domain_tab(string $moduleLink, string $tab, array $rows, array $neoCache, array $config): string
{
    $isWatch = $tab === 'watch';
    $title = $isWatch ? 'Watch List' : 'Expiring Domains';
    $description = $isWatch
        ? 'Domains rated 1 or 2 that you intend to acquire as they expire.'
        : 'Domains entering the current ' . (int) $config['window'] . '-day expiration window and their lifecycle stage.';

    $counts = ['Active' => 0, 'Expired' => 0, 'Redemption' => 0, 'Pending Delete' => 0, 'Available' => 0];
    foreach ($rows as $row) {
        if (isset($counts[$row['status']])) {
            $counts[$row['status']]++;
        }
    }

    $html = '<div class="edt-panel">';
    $html .= '<div class="edt-panel-head"><div><h3>' . edt_escape($title) . '</h3><p>' . edt_escape($description) . '</p></div>';
    $html .= '<div class="edt-count">' . count($rows) . ' domain' . (count($rows) === 1 ? '' : 's') . '</div></div>';

    $html .= '<div class="edt-status-summary">';
    foreach ($counts as $status => $count) {
        $html .= '<span class="edt-summary-item">' . edt_status_icon_html($status) . '<span>' . edt_escape($status) . '</span><strong>' . (int) $count . '</strong></span>';
    }
    $html .= '</div>';

    if (!$rows) {
        $html .= '<div class="edt-empty">' . ($isWatch
            ? 'No domains are currently rated 1 or 2.'
            : 'No domains are currently in the configured expiration/lifecycle window.') . '</div></div>';
        return $html;
    }

    $rootId = $isWatch ? 'edt-watch-table' : 'edt-expiring-table';
    $defaultStatuses = $isWatch
        ? ['Active', 'Expired', 'Redemption', 'Pending Delete', 'Available']
        : ['Active', 'Expired'];

    if (!$isWatch) {
        $html .= edt_expiration_window_control((int) $config['window'], (int) ($config['windowDefault'] ?? $config['window']));
    }

    $html .= '<div class="edt-toolbar">';
    $html .= '<div class="edt-toolbar-left">';
    $html .= '<div class="edt-status-filter-wrap" data-edt-status-filter>';
    $html .= '<button type="button" class="btn edt-btn-secondary edt-status-filter-toggle" data-edt-status-toggle aria-expanded="false"><span data-edt-status-label></span> <span class="caret"></span></button>';
    $html .= '<div class="edt-status-filter-menu" data-edt-status-menu>';
    foreach (['Active', 'Expired', 'Redemption', 'Pending Delete', 'Available'] as $status) {
        $checked = in_array($status, $defaultStatuses, true) ? ' checked' : '';
        $default = in_array($status, $defaultStatuses, true) ? ' data-edt-default-checked="1"' : '';
        $html .= '<label><input type="checkbox" value="' . edt_escape(strtolower($status)) . '" data-edt-status-choice' . $default . $checked . '> <span>' . edt_escape($status) . '</span></label>';
    }
    $html .= '<div class="edt-status-filter-actions"><button type="button" data-edt-status-all>All</button><button type="button" data-edt-status-default>Default</button></div>';
    $html .= '</div></div></div>';
    $html .= '<div class="edt-search"><i class="fas fa-search" aria-hidden="true"></i><input type="text" class="form-control" placeholder="Search domains, TLD, account, customer ID..." data-edt-search></div>';
    $html .= '</div>';

    $neoDomainIds = [];
    foreach ($rows as $candidateRow) {
        if (edt_is_neo_registrar((string) ($candidateRow['registrar'] ?? ''))) {
            $neoDomainIds[] = (int) $candidateRow['id'];
        }
    }
    $neoDomainIds = array_values(array_unique(array_filter($neoDomainIds)));
    $hasNeo = !empty($neoDomainIds);

    $html .= '<form method="post" action="' . edt_escape($moduleLink . '&tab=' . $tab) . '" id="' . edt_escape($rootId) . '" data-edt-table-root data-edt-export-table-name="' . edt_escape($isWatch ? 'watch-list' : 'expiring-domains') . '">';
    $html .= '<input type="hidden" name="token" value="' . edt_escape(generate_token('plain')) . '">';
    $html .= '<input type="hidden" name="edt_action" value="save_ratings">';
    $html .= '<input type="hidden" name="edt_tab" value="' . edt_escape($tab) . '">';
    $html .= '<input type="hidden" name="edt_window" value="' . (int) $config['window'] . '">';
    $html .= edt_domain_table_controls($hasNeo, $tab);

    $todayHeader = (new DateTimeImmutable('today'))->format('m/d/Y');
    $headers = [
        ['Rating', 'number'], ['Domain', 'text'], ['Reg Date', 'number'], ['Expiry', 'number'], ['Redemption', 'number'],
        ['WHMCS #', 'number'], ['NEO Cust. #', 'natural'], ['Domain Name', 'text'], ['TLD', 'text'],
        ['Length', 'number'], [$todayHeader, 'number'], ['Pending Delete', 'number'],
        ['Available', 'number'], ['Status', 'number'],
    ];
    $html .= '<div class="table-responsive edt-domain-table-wrap"><table class="table table-striped table-bordered edt-table edt-domain-table"><thead><tr>';
    foreach ($headers as $index => $header) {
        $html .= edt_sortable_th($header[0], $index, $header[1]);
    }
    $html .= '</tr></thead><tbody>';

    $statusOrder = ['Active' => 1, 'Expired' => 2, 'Redemption' => 3, 'Pending Delete' => 4, 'Available' => 5];
    foreach ($rows as $row) {
        $domainId = (int) $row['id'];
        $cache = $neoCache[$domainId] ?? null;
        $isNeo = edt_is_neo_registrar($row['registrar']);
        $neoCustomerRaw = trim((string) ($cache['customer_id'] ?? ''));
        $neoStatus = trim((string) ($cache['order_status'] ?? ''));
        $neoRegistryCreated = trim((string) ($cache['registry_created_at'] ?? ''));
        $neoCustomer = edt_neo_customer_for_display($neoCustomerRaw, $neoStatus);
        $neoError = trim((string) ($cache['last_error'] ?? ''));
        $searchText = implode(' ', [
            $row['domain'], $row['sld'], $row['tld'], $row['userid'], $neoCustomer,
            $row['status'], $row['rating'] ?: '', $row['whmcsStatus'], !empty($row['autoRenew']) ? 'auto renew' : '', ($row['rating'] === 1 || $row['rating'] === 2) ? 'watch list' : '',
        ]);

        $neoRowAttr = $isNeo ? ' data-edt-neo-domain-id="' . $domainId . '"' : '';
        $html .= '<tr data-edt-row data-status="' . edt_escape(strtolower($row['status'])) . '" data-search="' . edt_escape(strtolower($searchText)) . '"' . $neoRowAttr . '>';
        $ratingSort = $row['rating'] === null ? '' : (string) $row['rating'];
        $ratingTitle = match ($row['rating']) { 1 => '1 = Great', 2 => '2 = Good', 3 => '3 = Fair', 4 => '4 = Bad', default => 'Rating: 1 = Great, 4 = Bad' };
        $html .= '<td class="edt-rating-cell" data-edt-sort-value="' . edt_escape($ratingSort) . '" data-edt-export-value="' . edt_escape($ratingSort) . '"><select class="form-control edt-rating" name="rating[' . $domainId . ']" data-edt-rating-select data-edt-domain-id="' . $domainId . '" data-edt-saved-value="' . edt_escape($ratingSort) . '" title="' . edt_escape($ratingTitle) . '">';
        $html .= edt_rating_options($row['rating']);
        $html .= '</select></td>';
        $html .= '<td class="edt-domain" data-edt-sort-value="' . edt_escape($row['domain']) . '" data-edt-export-value="' . edt_escape($row['domain']) . '"><a href="clientsdomains.php?userid=' . (int) $row['userid'] . '&id=' . $domainId . '">' . edt_escape($row['domain']) . '</a></td>';
        $registryDate = edt_date($neoRegistryCreated);
        if ($registryDate) {
            $registryLabel = edt_format_date($registryDate);
            $html .= '<td class="edt-date" data-edt-sort-value="' . $registryDate->getTimestamp() . '" data-edt-export-value="' . edt_escape($registryLabel) . '" title="NEO registry creation date">' . edt_escape($registryLabel) . '</td>';
        } else {
            $registryTitle = $isNeo ? 'Registry creation date has not been cached yet. Refresh NEO data to retrieve it.' : 'Registry creation date is available only for NEO domains.';
            $html .= '<td class="edt-date edt-muted" data-edt-sort-value="" data-edt-export-value="--" title="' . edt_escape($registryTitle) . '">--</td>';
        }
        $html .= '<td class="edt-date" data-edt-sort-value="' . $row['expiryDate']->getTimestamp() . '" data-edt-export-value="' . edt_escape(edt_format_date($row['expiryDate'])) . '">' . edt_escape(edt_format_date($row['expiryDate'])) . '</td>';
        $html .= '<td class="edt-date" data-edt-sort-value="' . $row['redemptionDate']->getTimestamp() . '" data-edt-export-value="' . edt_escape(edt_format_date($row['redemptionDate'])) . '">' . edt_escape(edt_format_date($row['redemptionDate'])) . '</td>';
        $html .= '<td class="edt-num" data-edt-sort-value="' . (int) $row['userid'] . '" data-edt-export-value="' . (int) $row['userid'] . '"><a href="clientssummary.php?userid=' . (int) $row['userid'] . '">' . (int) $row['userid'] . '</a></td>';
        if (!$isNeo) {
            $html .= '<td class="edt-num edt-muted" data-edt-sort-value="" data-edt-export-value="--">--</td>';
        } else {
            if ($neoCustomer !== '') {
                $neoTitle = $neoStatus !== '' ? 'NEO status: ' . $neoStatus . '. Click to refresh.' : 'Click to refresh NEO Customer #.';
                $neoLabel = $neoCustomer;
                $neoClass = edt_neo_status_allows_whmcs_delete($neoStatus) ? 'edt-neo-account-button edt-neo-terminal' : 'edt-neo-account-button';
                $neoSort = $neoCustomer;
            } else {
                if (edt_neo_status_allows_whmcs_delete($neoStatus)) {
                    $neoTitle = 'NEO status: ' . $neoStatus . '. This domain is no longer shown as present at NEO. Click to refresh.';
                } elseif ($neoError !== '') {
                    $neoTitle = $neoError . ' Click to look up again.';
                } else {
                    $neoTitle = 'Click to look up the NEO Customer #.';
                }
                $neoLabel = '--';
                $neoClass = 'edt-neo-account-button edt-muted';
                $neoSort = '';
            }
            $html .= '<td class="edt-num" data-edt-sort-value="' . edt_escape($neoSort) . '" data-edt-export-value="' . edt_escape($neoLabel) . '"><button type="button" class="' . $neoClass . '" data-edt-neo-refresh data-edt-neo-form-id="edt-neo-single-' . edt_escape($tab) . '" data-edt-domain-id="' . $domainId . '" title="' . edt_escape($neoTitle) . '">' . edt_escape($neoLabel) . '</button></td>';
        }
        $searchUrl = edt_search_url((string) $row['sld'], (string) ($config['searchEngine'] ?? 'google'));
        $html .= '<td class="edt-name-cell" data-edt-sort-value="' . edt_escape($row['sld']) . '" data-edt-export-value="' . edt_escape($row['sld']) . '"><a class="edt-domain-search-link" href="' . edt_escape($searchUrl) . '" target="_blank" rel="noopener noreferrer" title="Search ' . edt_escape($row['sld']) . '">' . edt_escape($row['sld']) . '</a></td>';
        $html .= '<td class="edt-tld" data-edt-sort-value="' . edt_escape($row['tld']) . '" data-edt-export-value=".' . edt_escape($row['tld']) . '">.' . edt_escape($row['tld']) . '</td>';
        $html .= '<td class="edt-num" data-edt-sort-value="' . (int) $row['length'] . '" data-edt-export-value="' . (int) $row['length'] . '">' . (int) $row['length'] . '</td>';
        $daysClass = $row['daysToPending'] < 0 ? ' edt-negative' : '';
        $html .= '<td class="edt-num' . $daysClass . '" data-edt-sort-value="' . (int) $row['daysToPending'] . '" data-edt-export-value="' . (int) $row['daysToPending'] . '">' . (int) $row['daysToPending'] . '</td>';
        $html .= '<td class="edt-date" data-edt-sort-value="' . $row['pendingDate']->getTimestamp() . '" data-edt-export-value="' . edt_escape(edt_format_date($row['pendingDate'])) . '">' . edt_escape(edt_format_date($row['pendingDate'])) . '</td>';
        $html .= '<td class="edt-date" data-edt-sort-value="' . $row['availableDate']->getTimestamp() . '" data-edt-export-value="' . edt_escape(edt_format_date($row['availableDate'])) . '">' . edt_escape(edt_format_date($row['availableDate'])) . '</td>';
        $isWatchList = $row['rating'] === 1 || $row['rating'] === 2;
        $statusExport = edt_status_export_value($row['status'], !empty($row['autoRenew']), $isWatchList);
        $html .= '<td class="edt-status-cell" data-edt-sort-value="' . (int) ($statusOrder[$row['status']] ?? 99) . '" data-edt-export-value="' . edt_escape($statusExport) . '" data-edt-export-watch="' . ($isWatchList ? 'Yes' : 'No') . '" data-edt-export-autorenew="' . (!empty($row['autoRenew']) ? 'On' : 'Off') . '" data-edt-export-lifecycle="' . edt_escape($row['status']) . '">' . edt_status_icon_html($row['status'], !empty($row['autoRenew']), $isWatchList) . '</td>';
        $html .= '</tr>';
    }
    $html .= '</tbody></table></div>';
    $html .= '<div class="edt-no-match alert alert-warning" data-edt-no-match style="display:none">No matching domains.</div>';
    $html .= edt_domain_table_controls($hasNeo, $tab);
    $html .= '</form>';

    if ($neoDomainIds) {
        $html .= '<form method="post" action="' . edt_escape($moduleLink . '&tab=' . $tab) . '" id="edt-neo-single-' . edt_escape($tab) . '" class="edt-hidden-form" data-edt-neo-single-form>';
        $html .= '<input type="hidden" name="token" value="' . edt_escape(generate_token('plain')) . '">';
        $html .= '<input type="hidden" name="edt_action" value="refresh_neo"><input type="hidden" name="edt_tab" value="' . edt_escape($tab) . '"><input type="hidden" name="neo_force" value="1">';
        $html .= '<input type="hidden" name="neo_domain_ids" value="">';
        $html .= '</form>';

        $html .= '<form method="post" action="' . edt_escape($moduleLink . '&tab=' . $tab) . '" id="edt-neo-bulk-' . edt_escape($tab) . '" class="edt-hidden-form" data-edt-neo-bulk data-edt-neo-source="' . edt_escape($rootId) . '">';
        $html .= '<input type="hidden" name="token" value="' . edt_escape(generate_token('plain')) . '">';
        $html .= '<input type="hidden" name="edt_action" value="refresh_neo">';
        $html .= '<input type="hidden" name="edt_tab" value="' . edt_escape($tab) . '">';
        $html .= '<input type="hidden" name="neo_domain_ids" value="' . edt_escape(implode(',', array_unique($neoDomainIds))) . '">';
        $html .= '</form>';
        $html .= '<div class="edt-neo-note">NEO refresh follows the current table order from top to bottom; up to 50 domains are processed per run. The nightly sweep can refresh the complete tracker automatically.</div>';
    }

    $html .= '</div>';
    return $html;
}

function edt_expired_table_nav_controls(bool $hasNeo): string
{
    $html = '<div class="edt-table-controls">';
    $html .= '<div class="edt-actions edt-expired-actions">';
    $html .= '<button type="button" class="btn edt-btn-danger" data-edt-expired-delete-button disabled>Delete Selected</button>';
    $html .= '<span data-edt-expired-selected-count>0 selected</span>';
    if ($hasNeo) {
        $html .= '<button type="button" class="btn edt-btn-secondary" data-edt-neo-bulk-trigger data-edt-neo-bulk-form-id="edt-neo-bulk-expired"><i class="fas fa-sync-alt" aria-hidden="true"></i> NEO</button>';
    }
    $html .= '<button type="button" class="btn edt-btn-secondary" data-edt-export-csv data-edt-export-name="expired-domains"><i class="fas fa-file-csv" aria-hidden="true"></i> Export</button>';
    $html .= '</div>';
    $html .= '<div class="edt-pager">' . edt_page_size_control() . '<span data-edt-status>0 of 0</span><button type="button" class="btn edt-btn-secondary" data-edt-prev>Previous</button><button type="button" class="btn edt-btn-secondary" data-edt-next>Next</button></div>';
    $html .= '</div>';
    return $html;
}

function edt_expired_domains_tab(string $moduleLink, array $rows, array $neoCache): string
{
    $html = '<div class="edt-panel">';
    $html .= '<div class="edt-panel-head"><div><h3>Expired Domains</h3><p>WHMCS domains marked Expired. Select deletion candidates only after the NEO lookup shows Deleted.</p></div>';
    $html .= '<div class="edt-count">' . count($rows) . ' domain' . (count($rows) === 1 ? '' : 's') . '</div></div>';

    if (!$rows) {
        $html .= '<div class="edt-empty">No WHMCS domains are currently marked Expired.</div></div>';
        return $html;
    }

    $html .= '<div class="edt-toolbar edt-toolbar-search-only">';
    $html .= '<div class="edt-search"><i class="fas fa-search" aria-hidden="true"></i><input type="text" class="form-control" placeholder="Search domain, WHMCS #, NEO account..." data-edt-search></div>';
    $html .= '</div>';

    $neoDomainIds = [];
    foreach ($rows as $candidateRow) {
        if (edt_is_neo_registrar((string) ($candidateRow['registrar'] ?? ''))) {
            $neoDomainIds[] = (int) $candidateRow['id'];
        }
    }
    $neoDomainIds = array_values(array_unique(array_filter($neoDomainIds)));
    $hasNeo = !empty($neoDomainIds);

    $html .= '<div id="edt-expired-cleanup-table" data-edt-table-root data-edt-export-table-name="expired-domains">';
    $html .= edt_expired_table_nav_controls($hasNeo);
    $html .= '<div class="table-responsive"><table class="table table-striped table-bordered edt-table edt-expired-table"><thead><tr>';
    $html .= '<th class="edt-select-col"><input type="checkbox" data-edt-expired-select-all title="Select eligible domains on the current page"></th>';
    foreach ([['Domain', 'text'], ['Expiration', 'number'], ['WHMCS #', 'number'], ['NEO Account', 'natural']] as $index => $header) {
        $html .= edt_sortable_th($header[0], $index + 1, $header[1]);
    }
    $html .= '</tr></thead><tbody>';

    foreach ($rows as $row) {
        $domainId = (int) $row['id'];
        $isNeo = edt_is_neo_registrar($row['registrar']);
        $cache = $neoCache[$domainId] ?? [];
        $neoCustomerRaw = trim((string) ($cache['customer_id'] ?? ''));
        $neoStatus = trim((string) ($cache['order_status'] ?? ''));
        $neoDisplay = edt_neo_customer_for_display($neoCustomerRaw, $neoStatus);
        $neoError = trim((string) ($cache['last_error'] ?? ''));
        $eligible = $isNeo && edt_neo_status_allows_whmcs_delete($neoStatus);
        $searchText = strtolower(implode(' ', [$row['domain'], $row['userid'], $neoDisplay, $neoStatus]));

        $neoRowAttr = $isNeo ? ' data-edt-neo-domain-id="' . $domainId . '"' : '';
        $html .= '<tr data-edt-row data-search="' . edt_escape($searchText) . '"' . $neoRowAttr . '>';

        if ($eligible) {
            $checkboxTitle = 'Select ' . $row['domain'] . ' for WHMCS deletion. A fresh NEO status check will run before deletion.';
            $html .= '<td class="edt-select-col"><input type="checkbox" value="' . $domainId . '" data-edt-expired-select title="' . edt_escape($checkboxTitle) . '"></td>';
        } else {
            if (!$isNeo) {
                $checkboxTitle = 'Not assigned to the NEO registrar module.';
            } elseif ($neoStatus !== '') {
                $checkboxTitle = 'NEO status is ' . $neoStatus . '. Selection is enabled only for Deleted or Archived.';
            } elseif ($neoError !== '') {
                $checkboxTitle = $neoError;
            } else {
                $checkboxTitle = 'Click the NEO Account cell or run NEO first.';
            }
            $html .= '<td class="edt-select-col"><input type="checkbox" disabled title="' . edt_escape($checkboxTitle) . '"></td>';
        }

        $html .= '<td class="edt-domain" data-edt-sort-value="' . edt_escape($row['domain']) . '" data-edt-export-value="' . edt_escape($row['domain']) . '"><a href="clientsdomains.php?userid=' . (int) $row['userid'] . '&id=' . $domainId . '">' . edt_escape($row['domain']) . '</a></td>';
        $html .= '<td class="edt-date" data-edt-sort-value="' . $row['expiryDate']->getTimestamp() . '" data-edt-export-value="' . edt_escape(edt_format_date($row['expiryDate'])) . '">' . edt_escape(edt_format_date($row['expiryDate'])) . '</td>';
        $html .= '<td class="edt-num" data-edt-sort-value="' . (int) $row['userid'] . '" data-edt-export-value="' . (int) $row['userid'] . '"><a href="clientssummary.php?userid=' . (int) $row['userid'] . '">' . (int) $row['userid'] . '</a></td>';

        if (!$isNeo) {
            $html .= '<td class="edt-num edt-muted" data-edt-sort-value="">--</td>';
        } else {
            if ($neoDisplay !== '') {
                if (edt_neo_status_allows_whmcs_delete($neoStatus)) {
                    $neoTitle = 'NEO status: ' . $neoStatus . '. Click to refresh.';
                    $neoClass = 'edt-neo-account-button edt-neo-terminal';
                    $neoSort = $neoStatus;
                } else {
                    $neoTitle = $neoStatus !== '' ? 'NEO status: ' . $neoStatus . '. Click to refresh.' : 'Click to refresh NEO Customer #.';
                    $neoClass = 'edt-neo-account-button';
                    $neoSort = $neoDisplay;
                }
                $neoLabel = $neoDisplay;
            } else {
                if ($neoError !== '') {
                    $neoTitle = $neoError . ' Click to look up again.';
                } else {
                    $neoTitle = 'Click to look up the NEO Customer #.';
                }
                $neoLabel = '--';
                $neoClass = 'edt-neo-account-button edt-muted';
                $neoSort = '';
            }
            $html .= '<td class="edt-num" data-edt-sort-value="' . edt_escape($neoSort) . '"><button type="button" class="' . $neoClass . '" data-edt-neo-refresh data-edt-neo-form-id="edt-neo-single-expired" data-edt-domain-id="' . $domainId . '" title="' . edt_escape($neoTitle) . '">' . edt_escape($neoLabel) . '</button></td>';
        }
        $html .= '</tr>';
    }
    $html .= '</tbody></table></div>';
    $html .= '<div class="edt-no-match alert alert-warning" data-edt-no-match style="display:none">No matching expired domains.</div>';
    $html .= edt_expired_table_nav_controls($hasNeo);
    $html .= '</div>';

    $html .= '<form method="post" action="' . edt_escape($moduleLink . '&tab=expired') . '" id="edt-expired-delete-selected" class="edt-hidden-form" data-edt-expired-delete-form data-edt-confirm="Delete the selected expired domains from WHMCS? Each domain will be checked live at NEO first. Disable Auto Renew will be set when needed, then WHMCS will perform and verify its native domain-invoice cleanup. The domain will not be deleted if cleanup cannot be verified. Only Deleted/Archived NEO orders will be removed, and no registrar delete command will be sent.">';
    $html .= '<input type="hidden" name="token" value="' . edt_escape(generate_token('plain')) . '">';
    $html .= '<input type="hidden" name="edt_action" value="delete_expired_domains"><input type="hidden" name="edt_tab" value="expired">';
    $html .= '<input type="hidden" name="expired_domain_ids" value="">';
    $html .= '</form>';

    if ($neoDomainIds) {
        $html .= '<form method="post" action="' . edt_escape($moduleLink . '&tab=expired') . '" id="edt-neo-single-expired" class="edt-hidden-form" data-edt-neo-single-form>';
        $html .= '<input type="hidden" name="token" value="' . edt_escape(generate_token('plain')) . '">';
        $html .= '<input type="hidden" name="edt_action" value="refresh_neo"><input type="hidden" name="edt_tab" value="expired"><input type="hidden" name="neo_force" value="1">';
        $html .= '<input type="hidden" name="neo_domain_ids" value="">';
        $html .= '</form>';

        $html .= '<form method="post" action="' . edt_escape($moduleLink . '&tab=expired') . '" id="edt-neo-bulk-expired" class="edt-hidden-form" data-edt-neo-bulk data-edt-neo-source="edt-expired-cleanup-table">';
        $html .= '<input type="hidden" name="token" value="' . edt_escape(generate_token('plain')) . '">';
        $html .= '<input type="hidden" name="edt_action" value="refresh_neo"><input type="hidden" name="edt_tab" value="expired">';
        $html .= '<input type="hidden" name="neo_domain_ids" value="' . edt_escape(implode(',', array_unique($neoDomainIds))) . '">';
        $html .= '</form>';
        $html .= '<div class="edt-neo-note">Refresh walks from the top of the current table order downward, processing up to 50 domains per run. Live NEO orders show the Customer #; Deleted and Archived orders display as Deleted.</div>';
    }

    $html .= '</div>';
    return $html;
}

function edt_rating_options(?int $selected): string
{
    $options = [
        '' => '—',
        '1' => '1',
        '2' => '2',
        '3' => '3',
        '4' => '4',
    ];
    $html = '';
    foreach ($options as $value => $label) {
        $isSelected = (string) $selected === (string) $value ? ' selected' : '';
        if ($value === '' && $selected === null) {
            $isSelected = ' selected';
        }
        $html .= '<option value="' . edt_escape((string) $value) . '"' . $isSelected . '>' . edt_escape($label) . '</option>';
    }
    return $html;
}

function edt_reconciliation_tab(string $moduleLink): string
{
    $filter = strtolower(trim((string) ($_GET['recon_status'] ?? 'open')));
    if (!in_array($filter, ['open', 'resolved', 'all'], true)) {
        $filter = 'open';
    }

    $query = Capsule::table(EDT_RECON_TABLE)->orderBy('created_at', 'desc')->orderBy('id', 'desc');
    if ($filter === 'open') {
        $query->where('status', 'Open');
    } elseif ($filter === 'resolved') {
        $query->where('status', 'Resolved');
    }
    $rows = $query->get();

    $html = '<div class="edt-panel">';
    $html .= '<div class="edt-panel-head"><div><h3>Reconciliation</h3><p>Manual work queue for domains that need action but cannot be handled yet because of their current state.</p></div>';
    $html .= '<div class="edt-count">' . count($rows) . ' entr' . (count($rows) === 1 ? 'y' : 'ies') . '</div></div>';

    $html .= '<form method="post" action="' . edt_escape($moduleLink . '&tab=reconciliation') . '" class="edt-recon-add">';
    $html .= '<input type="hidden" name="token" value="' . edt_escape(generate_token('plain')) . '">';
    $html .= '<input type="hidden" name="edt_action" value="recon_add">';
    $html .= '<input type="hidden" name="edt_tab" value="reconciliation">';
    $html .= '<div class="edt-recon-grid">';
    $html .= '<div><label>Domain</label><input type="text" class="form-control" name="domain" placeholder="example.com" required></div>';
    $html .= '<div><label>Action Needed</label><input type="text" class="form-control" name="action_needed" placeholder="What needs to be done" required></div>';
    $html .= '<div><label>Current State / Blocker</label><input type="text" class="form-control" name="blocked_by" placeholder="Why it cannot be done now"></div>';
    $html .= '<div class="edt-recon-notes"><label>Notes</label><textarea class="form-control" name="notes" rows="2" placeholder="Optional details"></textarea></div>';
    $html .= '<div class="edt-recon-submit"><button type="submit" class="btn edt-btn-primary"><i class="fas fa-plus" aria-hidden="true"></i> Add to Reconciliation</button></div>';
    $html .= '</div></form>';

    $html .= '<div class="edt-recon-filter">';
    foreach (['open' => 'Open', 'resolved' => 'Resolved', 'all' => 'All'] as $key => $label) {
        $class = $filter === $key ? ' edt-filter-active' : '';
        $html .= '<a class="btn edt-btn-secondary' . $class . '" href="' . edt_escape($moduleLink . '&tab=reconciliation&recon_status=' . $key) . '">' . edt_escape($label) . '</a>';
    }
    $html .= '</div>';

    if (!$rows) {
        $html .= '<div class="edt-empty">No ' . edt_escape($filter === 'all' ? '' : $filter . ' ') . 'Reconciliation entries.</div></div>';
        return $html;
    }

    $html .= '<div data-edt-table-root><div class="table-responsive"><table class="table table-striped table-bordered edt-table edt-recon-table"><thead><tr>';
    foreach ([['Domain', 'text'], ['Action Needed', 'text'], ['Current State / Blocker', 'text'], ['Notes', 'text'], ['Status', 'text'], ['Added', 'number']] as $index => $header) {
        $html .= edt_sortable_th($header[0], $index, $header[1]);
    }
    $html .= '<th>Actions</th></tr></thead><tbody>';

    foreach ($rows as $row) {
        $id = (int) $row->id;
        $status = (string) $row->status;
        $created = (string) ($row->created_at ?? '');
        $createdSort = '';
        if ($created !== '') {
            try { $createdSort = (string) (new DateTimeImmutable($created))->getTimestamp(); } catch (Throwable $e) { $createdSort = ''; }
        }
        $html .= '<tr data-edt-row>';
        $html .= '<td class="edt-domain" data-edt-sort-value="' . edt_escape((string) $row->domain) . '">' . edt_escape((string) $row->domain) . '</td>';
        $html .= '<td data-edt-sort-value="' . edt_escape((string) $row->action_needed) . '">' . edt_escape((string) $row->action_needed) . '</td>';
        $html .= '<td data-edt-sort-value="' . edt_escape((string) ($row->blocked_by ?? '')) . '">' . edt_escape((string) ($row->blocked_by ?? '')) . '</td>';
        $html .= '<td class="edt-notes-cell" data-edt-sort-value="' . edt_escape((string) ($row->notes ?? '')) . '">' . nl2br(edt_escape((string) ($row->notes ?? ''))) . '</td>';
        $html .= '<td data-edt-sort-value="' . edt_escape($status) . '"><span class="edt-status ' . ($status === 'Resolved' ? 'edt-status-resolved' : 'edt-status-open') . '">' . edt_escape($status) . '</span></td>';
        $html .= '<td class="edt-date" data-edt-sort-value="' . edt_escape($createdSort) . '">' . edt_escape(edt_format_datetime($created)) . '</td>';
        $html .= '<td class="edt-row-actions">';
        if ($status === 'Resolved') {
            $html .= edt_recon_action_form($moduleLink, $id, 'recon_reopen', 'Reopen', 'edt-btn-secondary');
        } else {
            $html .= edt_recon_action_form($moduleLink, $id, 'recon_resolve', 'Resolve', 'edt-btn-primary');
        }
        $html .= edt_recon_action_form($moduleLink, $id, 'recon_delete', 'Delete', 'edt-btn-danger', true);
        $html .= '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody></table></div></div></div>';
    return $html;
}

function edt_recon_action_form(string $moduleLink, int $id, string $action, string $label, string $buttonClass, bool $confirm = false): string
{
    $confirmAttr = $confirm ? ' data-edt-confirm="Delete this Reconciliation entry?"' : '';
    return '<form method="post" action="' . edt_escape($moduleLink . '&tab=reconciliation') . '" class="edt-inline-form"' . $confirmAttr . '>'
        . '<input type="hidden" name="token" value="' . edt_escape(generate_token('plain')) . '">'
        . '<input type="hidden" name="edt_tab" value="reconciliation">'
        . '<input type="hidden" name="edt_action" value="' . edt_escape($action) . '">'
        . '<input type="hidden" name="recon_id" value="' . $id . '">'
        . '<button type="submit" class="btn ' . edt_escape($buttonClass) . '">' . edt_escape($label) . '</button>'
        . '</form>';
}

function edt_neo_refresh_log_tab(): string
{
    $rows = Capsule::table(EDT_NEO_LOG_TABLE)
        ->orderBy('run_date', 'desc')
        ->orderBy('id', 'desc')
        ->get();

    $html = '<div class="edt-panel">';
    $html .= '<div class="edt-panel-head"><div><h3>NEO Refresh Log</h3><p>One consolidated row per nightly NEO sweep. Counts are updated as the sweep advances and finalized when the full queue completes.</p></div>';
    $html .= '<div class="edt-count">' . count($rows) . ' nightly run' . (count($rows) === 1 ? '' : 's') . '</div></div>';

    if (!$rows) {
        $html .= '<div class="edt-empty">No nightly NEO refreshes have been logged yet.</div></div>';
        return $html;
    }

    $html .= '<div data-edt-table-root><div class="table-responsive"><table class="table table-striped table-bordered edt-table"><thead><tr>';
    $headers = [
        ['Date', 'number'], ['Started', 'number'], ['Completed', 'number'], ['Checked', 'number'],
        ['At NEO', 'number'], ['Deleted', 'number'], ['Failed', 'number'], ['Result', 'text'],
    ];
    foreach ($headers as $index => $header) {
        $html .= edt_sortable_th($header[0], $index, $header[1]);
    }
    $html .= '</tr></thead><tbody>';

    foreach ($rows as $row) {
        $runDate = trim((string) ($row->run_date ?? ''));
        $started = trim((string) ($row->started_at ?? ''));
        $completed = trim((string) ($row->completed_at ?? ''));
        $checked = (int) ($row->checked_domains ?? 0);
        $total = (int) ($row->total_domains ?? 0);
        $current = (int) ($row->current_domains ?? 0);
        $deleted = (int) ($row->deleted_domains ?? 0);
        $failed = (int) ($row->failed_domains ?? 0);
        $result = trim((string) ($row->result ?? 'Running')) ?: 'Running';
        $lastError = trim((string) ($row->last_error ?? ''));

        $runSort = '';
        if ($runDate !== '') {
            try { $runSort = (string) (new DateTimeImmutable($runDate . ' 00:00:00'))->getTimestamp(); } catch (Throwable $e) { $runSort = ''; }
        }
        $startedSort = '';
        if ($started !== '') {
            try { $startedSort = (string) (new DateTimeImmutable($started))->getTimestamp(); } catch (Throwable $e) { $startedSort = ''; }
        }
        $completedSort = '';
        if ($completed !== '') {
            try { $completedSort = (string) (new DateTimeImmutable($completed))->getTimestamp(); } catch (Throwable $e) { $completedSort = ''; }
        }

        $checkedLabel = $result === 'Running' && $total > 0 ? ($checked . ' / ' . $total) : (string) $checked;
        $resultClass = 'edt-status-open';
        if ($result === 'Complete') {
            $resultClass = 'edt-status-resolved';
        } elseif ($result === 'Error') {
            $resultClass = 'edt-status-error';
        }
        $resultTitle = $lastError !== '' ? ' title="' . edt_escape($lastError) . '"' : '';

        $html .= '<tr data-edt-row>';
        $html .= '<td class="edt-date" data-edt-sort-value="' . edt_escape($runSort) . '">' . edt_escape($runDate !== '' ? date('m/d/Y', strtotime($runDate)) : '—') . '</td>';
        $html .= '<td class="edt-date" data-edt-sort-value="' . edt_escape($startedSort) . '">' . edt_escape(edt_format_datetime($started)) . '</td>';
        $html .= '<td class="edt-date" data-edt-sort-value="' . edt_escape($completedSort) . '">' . edt_escape($completed !== '' ? edt_format_datetime($completed) : '—') . '</td>';
        $html .= '<td class="edt-num" data-edt-sort-value="' . $checked . '">' . edt_escape($checkedLabel) . '</td>';
        $html .= '<td class="edt-num" data-edt-sort-value="' . $current . '">' . $current . '</td>';
        $html .= '<td class="edt-num" data-edt-sort-value="' . $deleted . '">' . $deleted . '</td>';
        $html .= '<td class="edt-num" data-edt-sort-value="' . $failed . '">' . $failed . '</td>';
        $html .= '<td data-edt-sort-value="' . edt_escape($result) . '"><span class="edt-status ' . edt_escape($resultClass) . '"' . $resultTitle . '>' . edt_escape($result) . '</span></td>';
        $html .= '</tr>';
    }

    $html .= '</tbody></table></div></div></div>';
    return $html;
}

function edt_report_table_controls(string $exportName): string
{
    $html = '<div class="edt-table-controls">';
    $html .= '<div class="edt-actions"><button type="button" class="btn edt-btn-secondary" data-edt-export-csv data-edt-export-name="' . edt_escape($exportName) . '"><i class="fas fa-file-csv" aria-hidden="true"></i> Export</button></div>';
    $html .= '<div class="edt-pager">' . edt_page_size_control() . '<span data-edt-status>0 of 0</span><button type="button" class="btn edt-btn-secondary" data-edt-prev>Previous</button><button type="button" class="btn edt-btn-secondary" data-edt-next>Next</button></div>';
    $html .= '</div>';
    return $html;
}

function edt_neo_terminal_table_controls(): string
{
    $html = '<div class="edt-table-controls">';
    $html .= '<div class="edt-actions edt-neo-terminal-actions">';
    $html .= '<button type="button" class="btn edt-btn-secondary" data-edt-export-csv data-edt-export-name="neo-deleted-archived-whmcs-matches"><i class="fas fa-file-csv" aria-hidden="true"></i> Export</button>';
    $html .= '<button type="button" class="btn edt-btn-secondary" data-edt-neo-terminal-bulk-action="cancel" disabled>Cancel Selected</button>';
    $html .= '<button type="button" class="btn edt-btn-danger" data-edt-neo-terminal-bulk-action="delete" disabled>Delete Selected</button>';
    $html .= '<span data-edt-neo-terminal-selected-count>0 selected</span></div>';
    $html .= '<div class="edt-pager">' . edt_page_size_control() . '<span data-edt-status>0 of 0</span><button type="button" class="btn edt-btn-secondary" data-edt-prev>Previous</button><button type="button" class="btn edt-btn-secondary" data-edt-next>Next</button></div>';
    $html .= '</div>';
    return $html;
}

function edt_orphan_invoice_table_controls(): string
{
    $html = '<div class="edt-table-controls">';
    $html .= '<div class="edt-actions edt-orphan-invoice-actions">';
    $html .= '<button type="button" class="btn edt-btn-danger" data-edt-orphan-invoice-cancel-button disabled>Cancel Selected</button>';
    $html .= '<span data-edt-orphan-invoice-selected-count>0 selected</span>';
    $html .= '<button type="button" class="btn edt-btn-secondary" data-edt-export-csv data-edt-export-name="orphaned-domain-invoices"><i class="fas fa-file-csv" aria-hidden="true"></i> Export</button>';
    $html .= '</div>';
    $html .= '<div class="edt-pager">' . edt_page_size_control() . '<span data-edt-status>0 of 0</span><button type="button" class="btn edt-btn-secondary" data-edt-prev>Previous</button><button type="button" class="btn edt-btn-secondary" data-edt-next>Next</button></div>';
    $html .= '</div>';
    return $html;
}

function edt_neo_terminal_tab(string $moduleLink): string
{
    $state = edt_neo_terminal_scan_state();
    $storedCompleteKey = trim(edt_meta_get(EDT_NEO_TERMINAL_COMPLETE_KEY_META, ''));
    $completeInfo = edt_meta_json(EDT_NEO_TERMINAL_COMPLETE_INFO_META);
    $completeIsCurrent = $storedCompleteKey !== ''
        && (int) ($completeInfo['scan_version'] ?? 0) === EDT_NEO_TERMINAL_SCAN_VERSION;
    $completeKey = $completeIsCurrent ? $storedCompleteKey : '';
    $staleComplete = $storedCompleteKey !== '' && !$completeIsCurrent;
    $displayKey = $completeKey !== '' ? $completeKey : trim((string) ($state['scan_key'] ?? ''));

    $terminalRows = [];
    if ($displayKey !== '') {
        $terminalRows = Capsule::table(EDT_NEO_TERMINAL_TABLE)
            ->where('scan_key', $displayKey)
            ->orderBy('order_status')
            ->orderBy('domain')
            ->get()
            ->all();
    }

    $domainNames = [];
    foreach ($terminalRows as $terminalRow) {
        $domain = edt_normalize_domain((string) ($terminalRow->domain ?? ''));
        if ($domain !== '') {
            $domainNames[$domain] = $domain;
        }
    }

    $whmcsRows = [];
    if ($domainNames) {
        $whmcsRows = Capsule::table('tbldomains')
            ->whereIn('domain', array_values($domainNames))
            ->where('registrar', 'like', '%netearthone%')
            ->get(['id', 'userid', 'domain', 'expirydate', 'status', 'registrar', 'donotrenew'])
            ->all();
        $whmcsRows = array_values(array_filter(
            $whmcsRows,
            static fn($row): bool => edt_is_neo_registrar((string) ($row->registrar ?? ''))
        ));
    }

    $terminalByDomain = [];
    foreach ($terminalRows as $terminalRow) {
        $domain = edt_normalize_domain((string) ($terminalRow->domain ?? ''));
        if ($domain !== '') {
            $terminalByDomain[$domain] = $terminalRow;
        }
    }

    $domainIds = array_values(array_unique(array_filter(array_map(static fn($row): int => (int) ($row->id ?? 0), $whmcsRows))));
    $openInvoices = edt_open_invoice_map_for_domains($domainIds);
    $rows = [];
    foreach ($whmcsRows as $whmcsRow) {
        $domain = edt_normalize_domain((string) ($whmcsRow->domain ?? ''));
        $terminalRow = $terminalByDomain[$domain] ?? null;
        if (!$terminalRow) {
            continue;
        }
        $domainId = (int) ($whmcsRow->id ?? 0);
        $rows[] = [
            'domain' => $domain,
            'domain_id' => $domainId,
            'userid' => (int) ($whmcsRow->userid ?? 0),
            'whmcs_status' => trim((string) ($whmcsRow->status ?? '')),
            'expirydate' => trim((string) ($whmcsRow->expirydate ?? '')),
            'auto_renew' => empty($whmcsRow->donotrenew),
            'neo_status' => trim((string) ($terminalRow->order_status ?? '')),
            'neo_order_id' => trim((string) ($terminalRow->neo_order_id ?? '')),
            'open_invoices' => $openInvoices[$domainId] ?? [],
        ];
    }
    usort($rows, static fn(array $a, array $b): int => strcmp($a['domain'], $b['domain']));

    $html = '<div class="edt-panel">';
    $html .= '<div class="edt-panel-head"><div><h3>NEO Deleted/Archived</h3><p>Historical NEO terminal orders that still match WHMCS are shown only after the domain\'s current order is also confirmed Deleted or Archived. Selected actions recheck NEO, use WHMCS native domain-invoice cleanup, and stop if its result cannot be verified. No registrar command is sent.</p></div>';
    $html .= '<div class="edt-count">' . count($rows) . ' WHMCS match' . (count($rows) === 1 ? '' : 'es') . '</div></div>';

    $scanActive = is_array($state);
    $scanLabel = $scanActive ? 'Continue NEO Scan' : 'Scan NEO';
    $html .= '<div class="edt-audit-actions">';
    $html .= '<form method="post" action="' . edt_escape($moduleLink . '&tab=neo-terminal') . '" data-edt-audit-scan-form>';
    $html .= '<input type="hidden" name="token" value="' . edt_escape(generate_token('plain')) . '">';
    $html .= '<input type="hidden" name="edt_action" value="scan_neo_terminal"><input type="hidden" name="edt_tab" value="neo-terminal">';
    $html .= '<button type="submit" class="btn edt-btn-primary" data-edt-audit-scan-button><i class="fas fa-sync-alt" aria-hidden="true"></i> ' . edt_escape($scanLabel) . '</button>';
    $html .= '</form>';
    if ($scanActive) {
        $html .= '<form method="post" action="' . edt_escape($moduleLink . '&tab=neo-terminal') . '" data-edt-audit-scan-form data-edt-audit-restart-confirm="Restart the current NEO Deleted/Archived scan from the beginning?">';
        $html .= '<input type="hidden" name="token" value="' . edt_escape(generate_token('plain')) . '">';
        $html .= '<input type="hidden" name="edt_action" value="scan_neo_terminal"><input type="hidden" name="edt_tab" value="neo-terminal"><input type="hidden" name="neo_terminal_restart" value="1">';
        $html .= '<button type="submit" class="btn edt-btn-secondary" data-edt-audit-scan-button>Restart Scan</button></form>';
    }
    $html .= '</div>';

    if ($scanActive) {
        $processed = (int) ($state['processed'] ?? 0);
        $matches = (int) ($state['matches'] ?? 0);
        $currentLive = edt_neo_validation_count($state, 'live');
        $validationFailed = edt_neo_validation_count($state, 'failed');
        $html .= '<div class="alert alert-warning edt-audit-note">Scan in progress: ' . $processed . ' historical terminal order' . ($processed === 1 ? '' : 's') . ' checked and ' . $matches . ' current terminal WHMCS match' . ($matches === 1 ? '' : 'es') . ' found so far. ' . $currentLive . ' current live order' . ($currentLive === 1 ? '' : 's') . ' excluded; ' . $validationFailed . ' current-status validation failure' . ($validationFailed === 1 ? '' : 's') . ' omitted. Each run processes up to 3,000 NEO orders.</div>';
    } elseif ($completeKey !== '') {
        $completedAt = trim((string) ($completeInfo['completed_at'] ?? ''));
        $processed = (int) ($completeInfo['processed'] ?? 0);
        $currentLive = (int) ($completeInfo['current_live'] ?? 0);
        $validationFailed = (int) ($completeInfo['validation_failed'] ?? 0);
        $html .= '<div class="alert alert-info edt-audit-note">Last complete scan: ' . edt_escape($completedAt !== '' ? edt_format_datetime($completedAt) : 'Unknown') . '. ' . $processed . ' historical NEO terminal order' . ($processed === 1 ? '' : 's') . ' checked. ' . $currentLive . ' current live order' . ($currentLive === 1 ? '' : 's') . ' excluded; ' . $validationFailed . ' current-status validation failure' . ($validationFailed === 1 ? '' : 's') . ' omitted.</div>';
    } elseif ($staleComplete) {
        $html .= '<div class="alert alert-warning edt-audit-note">The previous report used historical Deleted/Archived orders without current-order validation and has been hidden. Run Scan NEO to build the corrected report.</div>';
    } else {
        $html .= '<div class="alert alert-info edt-audit-note">Run the first scan to compare NEO Deleted and Archived history with WHMCS and validate each match against its current NEO order.</div>';
    }

    if (!$rows) {
        $empty = $displayKey === ''
            ? ($staleComplete ? 'Run Scan NEO to replace the hidden historical-only report.' : 'No completed NEO Deleted/Archived comparison is available yet.')
            : 'No NEO Deleted/Archived domains in the displayed scan are still present in WHMCS.';
        $html .= '<div class="edt-empty">' . edt_escape($empty) . '</div></div>';
        return $html;
    }

    $html .= '<div class="edt-toolbar edt-toolbar-search-only"><div class="edt-search"><i class="fas fa-search" aria-hidden="true"></i><input type="text" class="form-control" placeholder="Search domain, status, account, invoice..." data-edt-search></div></div>';
    $html .= '<div id="edt-neo-terminal-table" data-edt-table-root data-edt-export-table-name="neo-deleted-archived-whmcs-matches">';
    $html .= edt_neo_terminal_table_controls();
    $html .= '<div class="table-responsive"><table class="table table-striped table-bordered edt-table edt-audit-table"><thead><tr>';
    $html .= '<th class="edt-select-col"><input type="checkbox" data-edt-neo-terminal-select-all title="Select current-page domains"></th>';
    $headers = [
        ['Domain', 'text'], ['NEO Status', 'text'], ['NEO Order #', 'natural'], ['WHMCS Status', 'text'],
        ['WHMCS #', 'number'], ['Expiration', 'number'], ['Auto Renew', 'text'], ['Open Invoices', 'natural'],
    ];
    foreach ($headers as $index => $header) {
        $html .= edt_sortable_th($header[0], $index + 1, $header[1]);
    }
    $html .= '</tr></thead><tbody>';

    foreach ($rows as $row) {
        $invoiceSearch = [];
        foreach ($row['open_invoices'] as $invoice) {
            $invoiceSearch[] = '#' . (int) $invoice['id'] . ' ' . (string) $invoice['status'];
        }
        $searchText = strtolower(implode(' ', [
            $row['domain'], $row['neo_status'], $row['neo_order_id'], $row['whmcs_status'],
            $row['userid'], $row['expirydate'], $row['auto_renew'] ? 'auto renew on' : 'auto renew off', implode(' ', $invoiceSearch),
        ]));
        $expiry = edt_date($row['expirydate']);
        $expirySort = $expiry ? (string) $expiry->getTimestamp() : '';
        $expiryLabel = $expiry ? edt_format_date($expiry) : '—';
        $invoiceSort = $row['open_invoices'] ? (string) $row['open_invoices'][0]['id'] : '';

        $html .= '<tr data-edt-row data-search="' . edt_escape($searchText) . '" data-edt-neo-terminal-domain-id="' . (int) $row['domain_id'] . '">';
        $html .= '<td class="edt-select-col"><input type="checkbox" value="' . (int) $row['domain_id'] . '" data-edt-neo-terminal-select title="Select ' . edt_escape($row['domain']) . '"></td>';
        $html .= '<td class="edt-domain" data-edt-sort-value="' . edt_escape($row['domain']) . '" data-edt-export-value="' . edt_escape($row['domain']) . '"><a href="clientsdomains.php?userid=' . (int) $row['userid'] . '&id=' . (int) $row['domain_id'] . '">' . edt_escape($row['domain']) . '</a></td>';
        $html .= '<td data-edt-sort-value="' . edt_escape($row['neo_status']) . '"><span class="edt-status edt-status-error">' . edt_escape($row['neo_status']) . '</span></td>';
        $html .= '<td class="edt-num" data-edt-sort-value="' . edt_escape($row['neo_order_id']) . '">' . edt_escape($row['neo_order_id'] !== '' ? $row['neo_order_id'] : '—') . '</td>';
        $html .= '<td data-edt-sort-value="' . edt_escape($row['whmcs_status']) . '">' . edt_escape($row['whmcs_status'] !== '' ? $row['whmcs_status'] : '—') . '</td>';
        $html .= '<td class="edt-num" data-edt-sort-value="' . (int) $row['userid'] . '"><a href="clientssummary.php?userid=' . (int) $row['userid'] . '">' . (int) $row['userid'] . '</a></td>';
        $html .= '<td class="edt-date" data-edt-sort-value="' . edt_escape($expirySort) . '">' . edt_escape($expiryLabel) . '</td>';
        $html .= '<td class="edt-num" data-edt-sort-value="' . ($row['auto_renew'] ? 'On' : 'Off') . '">' . ($row['auto_renew'] ? 'On' : 'Off') . '</td>';
        $html .= '<td class="edt-audit-invoices" data-edt-sort-value="' . edt_escape($invoiceSort) . '" data-edt-export-value="' . edt_escape(implode(' | ', $invoiceSearch)) . '">';
        if (!$row['open_invoices']) {
            $html .= '<span class="edt-muted">—</span>';
        } else {
            foreach ($row['open_invoices'] as $invoice) {
                $html .= '<a href="invoices.php?action=edit&id=' . (int) $invoice['id'] . '" title="' . edt_escape((string) $invoice['status']) . '">#' . (int) $invoice['id'] . '</a> ';
            }
        }
        $html .= '</td></tr>';
    }

    $html .= '</tbody></table></div><div class="edt-no-match alert alert-warning" data-edt-no-match style="display:none">No matching NEO terminal domains.</div>';
    $html .= edt_neo_terminal_table_controls();
    $html .= '</div>';

    $html .= '<form method="post" action="' . edt_escape($moduleLink . '&tab=neo-terminal') . '" id="edt-neo-terminal-selected-action" class="edt-hidden-form" data-edt-neo-terminal-action-form>';
    $html .= '<input type="hidden" name="token" value="' . edt_escape(generate_token('plain')) . '">';
    $html .= '<input type="hidden" name="edt_action" value="neo_terminal_domain_action"><input type="hidden" name="edt_tab" value="neo-terminal">';
    $html .= '<input type="hidden" name="neo_domain_ids" value=""><input type="hidden" name="neo_domain_operation" value="">';
    $html .= '</form></div>';
    return $html;
}

function edt_orphan_invoices_tab(string $moduleLink): string
{
    $rows = edt_load_orphan_invoice_rows();
    $mixedCount = count(array_filter($rows, static fn(array $row): bool => !empty($row['mixed'])));

    $html = '<div class="edt-panel">';
    $html .= '<div class="edt-panel-head"><div><h3>Orphaned Invoices</h3><p>Open invoices containing domain line items whose related domain ID no longer exists in WHMCS. Invoice Only rows can be cancelled through WHMCS.</p></div>';
    $html .= '<div class="edt-count">' . count($rows) . ' invoice' . (count($rows) === 1 ? '' : 's') . '</div></div>';
    $html .= '<div class="alert alert-info edt-audit-note">Invoice Only means every line item points to a missing domain and can be selected for cancellation. Mixed Invoice means the invoice also contains other charges and remains review-only for the Create Editable Draft workflow. Every selected invoice is revalidated immediately before cancellation. Terminal invoice statuses (Paid, Cancelled, and Refunded) are excluded.</div>';
    if ($mixedCount > 0) {
        $html .= '<div class="alert alert-warning edt-audit-note">' . $mixedCount . ' mixed invoice' . ($mixedCount === 1 ? '' : 's') . ' cannot be selected here. Use Create Editable Draft when one needs to be split or edited.</div>';
    }

    if (!$rows) {
        $html .= '<div class="edt-empty">No open invoices contain orphaned domain line items.</div></div>';
        return $html;
    }

    $html .= '<div class="edt-toolbar edt-toolbar-search-only"><div class="edt-search"><i class="fas fa-search" aria-hidden="true"></i><input type="text" class="form-control" placeholder="Search invoice, account, domain, description..." data-edt-search></div></div>';
    $html .= '<div id="edt-orphan-invoice-table" data-edt-table-root data-edt-export-table-name="orphaned-domain-invoices">';
    $html .= edt_orphan_invoice_table_controls();
    $html .= '<div class="table-responsive"><table class="table table-striped table-bordered edt-table edt-audit-table edt-orphan-invoice-table"><thead><tr>';
    $html .= '<th class="edt-select-col"><input type="checkbox" data-edt-orphan-invoice-select-all title="Select eligible invoices on the current page"></th>';
    $headers = [
        ['Invoice', 'number'], ['WHMCS #', 'number'], ['Status', 'text'], ['Invoice Date', 'number'], ['Due Date', 'number'],
        ['Total', 'number'], ['Orphan Domain(s)', 'text'], ['Orphan Items', 'number'], ['All Items', 'number'], ['Review', 'text'],
    ];
    foreach ($headers as $index => $header) {
        $html .= edt_sortable_th($header[0], $index + 1, $header[1]);
    }
    $html .= '</tr></thead><tbody>';

    foreach ($rows as $row) {
        $invoiceLabel = trim((string) $row['invoicenum']) !== '' ? trim((string) $row['invoicenum']) : '#' . (int) $row['invoice_id'];
        $date = edt_date((string) $row['date']);
        $dueDate = edt_date((string) $row['duedate']);
        $dateSort = $date ? (string) $date->getTimestamp() : '';
        $dueSort = $dueDate ? (string) $dueDate->getTimestamp() : '';
        $domainsLabel = $row['domains'] ? implode(', ', $row['domains']) : 'RelID ' . implode(', ', $row['relids']);
        $reviewLabel = $row['mixed'] ? 'Mixed Invoice' : 'Invoice Only';
        $reviewClass = $row['mixed'] ? 'edt-status-error' : 'edt-status-resolved';
        $descriptionTitle = implode(' | ', $row['descriptions']);
        $searchText = strtolower(implode(' ', [
            $row['invoice_id'], $invoiceLabel, $row['userid'], $row['status'], $row['date'], $row['duedate'],
            $row['total'], $domainsLabel, $descriptionTitle, $reviewLabel,
        ]));

        $html .= '<tr data-edt-row data-search="' . edt_escape($searchText) . '" data-edt-orphan-invoice-id="' . (int) $row['invoice_id'] . '">';
        if ($row['mixed']) {
            $html .= '<td class="edt-select-col"><input type="checkbox" disabled title="Mixed invoices must be handled through Create Editable Draft"></td>';
        } else {
            $html .= '<td class="edt-select-col"><input type="checkbox" value="' . (int) $row['invoice_id'] . '" data-edt-orphan-invoice-select title="Select invoice #' . (int) $row['invoice_id'] . '"></td>';
        }
        $html .= '<td class="edt-num" data-edt-sort-value="' . (int) $row['invoice_id'] . '" data-edt-export-value="' . edt_escape($invoiceLabel) . '"><a href="invoices.php?action=edit&id=' . (int) $row['invoice_id'] . '">' . edt_escape($invoiceLabel) . '</a></td>';
        $html .= '<td class="edt-num" data-edt-sort-value="' . (int) $row['userid'] . '"><a href="clientssummary.php?userid=' . (int) $row['userid'] . '">' . (int) $row['userid'] . '</a></td>';
        $html .= '<td data-edt-sort-value="' . edt_escape((string) $row['status']) . '"><span class="edt-status edt-status-open">' . edt_escape((string) $row['status']) . '</span></td>';
        $html .= '<td class="edt-date" data-edt-sort-value="' . edt_escape($dateSort) . '">' . edt_escape($date ? edt_format_date($date) : '—') . '</td>';
        $html .= '<td class="edt-date" data-edt-sort-value="' . edt_escape($dueSort) . '">' . edt_escape($dueDate ? edt_format_date($dueDate) : '—') . '</td>';
        $html .= '<td class="edt-num" data-edt-sort-value="' . edt_escape((string) $row['total']) . '">' . edt_escape(number_format((float) $row['total'], 2)) . '</td>';
        $html .= '<td class="edt-audit-domains" data-edt-sort-value="' . edt_escape($domainsLabel) . '" title="' . edt_escape($descriptionTitle) . '">' . edt_escape($domainsLabel) . '</td>';
        $html .= '<td class="edt-num" data-edt-sort-value="' . (int) $row['orphan_items'] . '">' . (int) $row['orphan_items'] . '</td>';
        $html .= '<td class="edt-num" data-edt-sort-value="' . (int) $row['total_items'] . '">' . (int) $row['total_items'] . '</td>';
        $html .= '<td data-edt-sort-value="' . edt_escape($reviewLabel) . '"><span class="edt-status ' . edt_escape($reviewClass) . '">' . edt_escape($reviewLabel) . '</span></td>';
        $html .= '</tr>';
    }

    $html .= '</tbody></table></div><div class="edt-no-match alert alert-warning" data-edt-no-match style="display:none">No matching orphaned invoices.</div>';
    $html .= edt_orphan_invoice_table_controls();
    $html .= '</div>';
    $html .= '<form method="post" action="' . edt_escape($moduleLink . '&tab=orphan-invoices') . '" id="edt-orphan-invoice-cancel-form" class="edt-hidden-form" data-edt-orphan-invoice-cancel-form>';
    $html .= '<input type="hidden" name="token" value="' . edt_escape(generate_token('plain')) . '">';
    $html .= '<input type="hidden" name="edt_action" value="cancel_orphan_invoices"><input type="hidden" name="edt_tab" value="orphan-invoices">';
    $html .= '<input type="hidden" name="orphan_invoice_ids" value="">';
    $html .= '</form></div>';
    return $html;
}

function edt_load_orphan_invoice_rows(): array
{
    $query = Capsule::table('tblinvoiceitems as ii')
        ->join('tblinvoices as i', 'i.id', '=', 'ii.invoiceid')
        ->leftJoin('tbldomains as d', 'd.id', '=', 'ii.relid')
        ->whereNotIn('i.status', edt_terminal_invoice_statuses())
        ->whereNull('d.id');
    edt_apply_domain_invoice_item_filter($query, 'ii.type');

    $orphanItems = $query
        ->orderBy('i.id', 'desc')
        ->get([
            'ii.id as item_id', 'ii.invoiceid', 'ii.relid', 'ii.type', 'ii.description', 'ii.amount',
            'i.userid', 'i.invoicenum', 'i.date', 'i.duedate', 'i.total', 'i.status',
        ]);

    if ($orphanItems->isEmpty()) {
        return [];
    }

    $invoiceIds = [];
    foreach ($orphanItems as $item) {
        $invoiceIds[] = (int) $item->invoiceid;
    }
    $invoiceIds = array_values(array_unique(array_filter($invoiceIds)));
    $totalItems = Capsule::table('tblinvoiceitems')
        ->whereIn('invoiceid', $invoiceIds)
        ->selectRaw('invoiceid, COUNT(*) AS item_count')
        ->groupBy('invoiceid')
        ->pluck('item_count', 'invoiceid')
        ->all();

    $grouped = [];
    foreach ($orphanItems as $item) {
        $invoiceId = (int) $item->invoiceid;
        if (!isset($grouped[$invoiceId])) {
            $grouped[$invoiceId] = [
                'invoice_id' => $invoiceId,
                'userid' => (int) $item->userid,
                'invoicenum' => (string) ($item->invoicenum ?? ''),
                'date' => (string) ($item->date ?? ''),
                'duedate' => (string) ($item->duedate ?? ''),
                'total' => (string) ($item->total ?? '0.00'),
                'status' => (string) ($item->status ?? ''),
                'item_ids' => [],
                'relids' => [],
                'domains' => [],
                'descriptions' => [],
            ];
        }
        $grouped[$invoiceId]['item_ids'][(int) $item->item_id] = (int) $item->item_id;
        $grouped[$invoiceId]['relids'][(int) $item->relid] = (int) $item->relid;
        $description = trim((string) ($item->description ?? ''));
        if ($description !== '') {
            $grouped[$invoiceId]['descriptions'][$description] = $description;
            $domain = edt_domain_from_invoice_description($description);
            if ($domain !== '') {
                $grouped[$invoiceId]['domains'][$domain] = $domain;
            }
        }
    }

    $rows = [];
    foreach ($grouped as $invoiceId => $row) {
        $row['item_ids'] = array_values($row['item_ids']);
        $row['relids'] = array_values($row['relids']);
        $row['domains'] = array_values($row['domains']);
        $row['descriptions'] = array_values($row['descriptions']);
        sort($row['domains'], SORT_NATURAL | SORT_FLAG_CASE);
        $row['orphan_items'] = count($row['item_ids']);
        $row['total_items'] = (int) ($totalItems[$invoiceId] ?? $row['orphan_items']);
        $row['mixed'] = $row['total_items'] > $row['orphan_items'];
        $rows[] = $row;
    }

    usort($rows, static fn(array $a, array $b): int => $b['invoice_id'] <=> $a['invoice_id']);
    return $rows;
}

/**
 * Cancel one live Invoice Only orphan through WHMCS's supported UpdateInvoice
 * Internal API. The invoice is reclassified from current database state before
 * the API is called; mixed invoices and terminal invoices fail closed.
 *
 * @return array{0: bool, 1: string}
 */
function edt_cancel_orphan_invoice_with_whmcs(int $invoiceId): array
{
    [$eligible, $invoice, $eligibilityError] = edt_orphan_invoice_cancel_candidate($invoiceId);
    if (!$eligible) {
        return [false, $eligibilityError];
    }
    if (!function_exists('localAPI')) {
        return [false, '#' . $invoiceId . ': the WHMCS Internal API is unavailable'];
    }

    $adminId = (int) ($_SESSION['adminid'] ?? 0);
    if ($adminId <= 0) {
        return [false, '#' . $invoiceId . ': the current WHMCS administrator session could not be identified'];
    }

    try {
        $adminUsername = trim((string) Capsule::table('tbladmins')->where('id', $adminId)->value('username'));
        if ($adminUsername === '') {
            return [false, '#' . $invoiceId . ': the current WHMCS administrator account could not be identified'];
        }

        $response = localAPI('UpdateInvoice', [
            'invoiceid' => $invoiceId,
            'status' => 'Cancelled',
        ], $adminUsername);
    } catch (Throwable $e) {
        return [false, '#' . $invoiceId . ': WHMCS could not cancel the invoice: ' . mb_substr($e->getMessage(), 0, 500)];
    }

    if (!is_array($response) || strcasecmp(trim((string) ($response['result'] ?? '')), 'success') !== 0) {
        $message = trim((string) ($response['message'] ?? $response['error'] ?? ''));
        if ($message === '') {
            $message = 'WHMCS returned an unsuccessful response';
        }
        return [false, '#' . $invoiceId . ': ' . mb_substr($message, 0, 500)];
    }

    $storedStatus = trim((string) Capsule::table('tblinvoices')->where('id', $invoiceId)->value('status'));
    if (strcasecmp($storedStatus, 'Cancelled') !== 0) {
        return [false, '#' . $invoiceId . ': WHMCS returned success but the invoice status was not saved as Cancelled'];
    }

    if (function_exists('logActivity')) {
        $orphanItems = (int) ($invoice['orphan_items'] ?? 0);
        logActivity(
            'Expiring Domain Tracker: Cancelled orphaned invoice #' . $invoiceId
            . ' through WHMCS after confirming that all ' . $orphanItems . ' line item' . ($orphanItems === 1 ? '' : 's')
            . ' referenced missing WHMCS domain records.',
            (int) ($invoice['userid'] ?? 0)
        );
    }

    return [true, '#' . $invoiceId];
}

/**
 * Build the current server-authoritative classification for a selected invoice.
 *
 * @return array{0: bool, 1: array<string, mixed>, 2: string}
 */
function edt_orphan_invoice_cancel_candidate(int $invoiceId): array
{
    if ($invoiceId <= 0) {
        return [false, [], 'The selected invoice ID was not valid'];
    }

    $invoice = Capsule::table('tblinvoices')
        ->where('id', $invoiceId)
        ->first(['id', 'userid', 'invoicenum', 'status']);
    if (!$invoice) {
        return [false, [], '#' . $invoiceId . ': the invoice no longer exists'];
    }

    $status = trim((string) ($invoice->status ?? ''));
    $terminalStatuses = array_map('strtolower', edt_terminal_invoice_statuses());
    if (in_array(strtolower($status), $terminalStatuses, true)) {
        return [false, [], '#' . $invoiceId . ': the invoice is already ' . ($status !== '' ? $status : 'terminal')];
    }

    $totalItems = (int) Capsule::table('tblinvoiceitems')->where('invoiceid', $invoiceId)->count();
    if ($totalItems <= 0) {
        return [false, [], '#' . $invoiceId . ': the invoice no longer contains any line items'];
    }

    $orphanQuery = Capsule::table('tblinvoiceitems as ii')
        ->leftJoin('tbldomains as d', 'd.id', '=', 'ii.relid')
        ->where('ii.invoiceid', $invoiceId)
        ->whereNull('d.id');
    edt_apply_domain_invoice_item_filter($orphanQuery, 'ii.type');
    $orphanItems = $orphanQuery->get(['ii.id']);
    $orphanCount = count($orphanItems);

    if ($orphanCount <= 0) {
        return [false, [], '#' . $invoiceId . ': it no longer contains an orphaned domain line item'];
    }
    if ($orphanCount !== $totalItems) {
        return [false, [], '#' . $invoiceId . ': it is now a Mixed Invoice and must be handled through Create Editable Draft'];
    }

    return [true, [
        'invoice_id' => $invoiceId,
        'userid' => (int) ($invoice->userid ?? 0),
        'invoicenum' => (string) ($invoice->invoicenum ?? ''),
        'status' => $status,
        'orphan_items' => $orphanCount,
        'total_items' => $totalItems,
    ], ''];
}

function edt_open_invoice_map_for_domains(array $domainIds): array
{
    $domainIds = array_values(array_unique(array_filter(array_map('intval', $domainIds))));
    if (!$domainIds) {
        return [];
    }

    $query = Capsule::table('tblinvoiceitems as ii')
        ->join('tblinvoices as i', 'i.id', '=', 'ii.invoiceid')
        ->whereIn('ii.relid', $domainIds)
        ->whereNotIn('i.status', edt_terminal_invoice_statuses());
    edt_apply_domain_invoice_item_filter($query, 'ii.type');
    $items = $query->orderBy('i.id', 'desc')->get(['ii.relid', 'i.id', 'i.invoicenum', 'i.status']);

    $result = [];
    $seen = [];
    foreach ($items as $item) {
        $domainId = (int) $item->relid;
        $invoiceId = (int) $item->id;
        $key = $domainId . ':' . $invoiceId;
        if (isset($seen[$key])) {
            continue;
        }
        $seen[$key] = true;
        $result[$domainId][] = [
            'id' => $invoiceId,
            'invoicenum' => (string) ($item->invoicenum ?? ''),
            'status' => (string) ($item->status ?? ''),
        ];
    }
    return $result;
}

function edt_terminal_invoice_statuses(): array
{
    return ['Paid', 'Cancelled', 'Refunded'];
}

function edt_apply_domain_invoice_item_filter($query, string $column): void
{
    $query->where(function ($where) use ($column): void {
        $where->where($column, 'like', 'Domain%')
            ->orWhere($column, 'like', 'PromoDomain%');
    });
}

function edt_domain_from_invoice_description(string $description): string
{
    if (preg_match('/\b((?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9-]{2,63})\b/i', $description, $match)) {
        return edt_normalize_domain((string) ($match[1] ?? ''));
    }
    return '';
}

function edt_tabs(string $moduleLink, string $active): string
{
    $tabs = [
        'expiring' => 'Expiring Domains',
        'watch' => 'Watch List',
        'reconciliation' => 'Reconciliation',
        'expired' => 'Expired Domains',
        'neo-log' => 'NEO Refresh Log',
        'neo-terminal' => 'NEO Deleted/Archived',
        'orphan-invoices' => 'Orphaned Invoices',
    ];
    $html = '<ul class="nav nav-tabs edt-tabs" role="tablist">';
    foreach ($tabs as $key => $label) {
        $class = $active === $key ? ' class="active"' : '';
        $html .= '<li role="presentation"' . $class . '><a href="' . edt_escape($moduleLink . '&tab=' . $key) . '">' . edt_escape($label) . '</a></li>';
    }
    $html .= '</ul>';
    return $html;
}

function edt_meta_get(string $key, string $default = ''): string
{
    try {
        $value = Capsule::table(EDT_META_TABLE)->where('meta_key', $key)->value('meta_value');
        return $value === null ? $default : (string) $value;
    } catch (Throwable $e) {
        return $default;
    }
}

function edt_meta_json(string $key): array
{
    $raw = edt_meta_get($key, '');
    if ($raw === '') {
        return [];
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function edt_meta_set(string $key, string $value): void
{
    $now = date('Y-m-d H:i:s');
    $exists = Capsule::table(EDT_META_TABLE)->where('meta_key', $key)->exists();
    if ($exists) {
        Capsule::table(EDT_META_TABLE)->where('meta_key', $key)->update(['meta_value' => $value, 'updated_at' => $now]);
    } else {
        Capsule::table(EDT_META_TABLE)->insert(['meta_key' => $key, 'meta_value' => $value, 'updated_at' => $now]);
    }
}

function edt_module_settings(): array
{
    try {
        return Capsule::table('tbladdonmodules')
            ->where('module', 'expiringdomaintracker')
            ->pluck('value', 'setting')
            ->all();
    } catch (Throwable $e) {
        return [];
    }
}

function edt_neo_refresh_log_ensure(string $runDate, int $totalDomains, ?string $startedAt = null): void
{
    $now = date('Y-m-d H:i:s');
    $existing = Capsule::table(EDT_NEO_LOG_TABLE)->where('run_date', $runDate)->first();
    if ($existing) {
        $effectiveTotal = $totalDomains > 0 ? $totalDomains : (int) ($existing->total_domains ?? 0);
        $updates = ['total_domains' => max(0, $effectiveTotal), 'updated_at' => $now];
        if (empty($existing->started_at)) {
            $updates['started_at'] = $startedAt ?: $now;
        }
        Capsule::table(EDT_NEO_LOG_TABLE)->where('run_date', $runDate)->update($updates);
        return;
    }

    Capsule::table(EDT_NEO_LOG_TABLE)->insert([
        'run_date' => $runDate,
        'started_at' => $startedAt ?: $now,
        'completed_at' => null,
        'total_domains' => max(0, $totalDomains),
        'checked_domains' => 0,
        'current_domains' => 0,
        'deleted_domains' => 0,
        'failed_domains' => 0,
        'result' => 'Running',
        'last_error' => null,
        'updated_at' => $now,
    ]);
}

function edt_neo_refresh_log_recalculate(string $runDate, array $processedIds, int $totalDomains): array
{
    $processedIds = array_values(array_unique(array_filter(array_map('intval', $processedIds))));
    $counts = ['checked' => count($processedIds), 'current' => 0, 'deleted' => 0, 'failed' => 0];

    if ($processedIds) {
        $cacheRows = Capsule::table(EDT_NEO_TABLE)
            ->whereIn('domain_id', $processedIds)
            ->get(['domain_id', 'customer_id', 'order_status', 'last_error']);
        $cacheById = [];
        foreach ($cacheRows as $cacheRow) {
            $cacheById[(int) $cacheRow->domain_id] = $cacheRow;
        }

        foreach ($processedIds as $domainId) {
            if (!isset($cacheById[$domainId])) {
                $counts['failed']++;
                continue;
            }
            $cache = $cacheById[$domainId];
            $customerId = trim((string) ($cache->customer_id ?? ''));
            $orderStatus = trim((string) ($cache->order_status ?? ''));
            if (edt_neo_status_allows_whmcs_delete($orderStatus)) {
                $counts['deleted']++;
            } elseif ($customerId !== '' || $orderStatus !== '') {
                $counts['current']++;
            } else {
                $counts['failed']++;
            }
        }
    }

    Capsule::table(EDT_NEO_LOG_TABLE)->where('run_date', $runDate)->update([
        'total_domains' => max(0, $totalDomains),
        'checked_domains' => $counts['checked'],
        'current_domains' => $counts['current'],
        'deleted_domains' => $counts['deleted'],
        'failed_domains' => $counts['failed'],
        'result' => 'Running',
        'last_error' => null,
        'updated_at' => date('Y-m-d H:i:s'),
    ]);

    return $counts;
}

function edt_neo_refresh_log_complete(string $runDate, array $processedIds, int $totalDomains): void
{
    $counts = edt_neo_refresh_log_recalculate($runDate, $processedIds, $totalDomains);
    $completedAt = date('Y-m-d H:i:s');
    Capsule::table(EDT_NEO_LOG_TABLE)->where('run_date', $runDate)->update([
        'completed_at' => $completedAt,
        'result' => 'Complete',
        'updated_at' => $completedAt,
    ]);

    try {
        logActivity(
            'Expiring Domain Tracker nightly NEO refresh completed: '
            . $counts['checked'] . ' checked, '
            . $counts['current'] . ' at NEO, '
            . $counts['deleted'] . ' deleted/archived, '
            . $counts['failed'] . ' failed.'
        );
    } catch (Throwable $ignored) {
    }
}

function edt_neo_refresh_log_error(string $runDate, string $message): void
{
    try {
        edt_neo_refresh_log_ensure($runDate, 0);
        Capsule::table(EDT_NEO_LOG_TABLE)->where('run_date', $runDate)->update([
            'result' => 'Error',
            'last_error' => $message,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    } catch (Throwable $ignored) {
    }
}

/**
 * Run one batch of the nightly NEO sweep. AfterCronJob can call this on every
 * WHMCS cron invocation; the queue starts at the configured hour and advances
 * in 50-domain batches until the entire tracker has been refreshed once that day.
 */
function edt_run_nightly_neo_refresh(): void
{
    try {
        edt_ensure_schema();
        $settings = edt_module_settings();
        $enabled = strtolower(trim((string) ($settings['nightly_neo_refresh'] ?? 'Enabled'))) !== 'disabled';
        if (!$enabled) {
            return;
        }

        $startHour = edt_positive_int($settings['nightly_neo_start_hour'] ?? 1, 1, 0, 23);
        $today = date('Y-m-d');
        if ((int) date('G') < $startHour || edt_meta_get('nightly_complete_date') === $today) {
            return;
        }

        $queueDate = edt_meta_get('nightly_queue_date');
        $queueJson = edt_meta_get('nightly_queue_json');
        $cursor = max(0, (int) edt_meta_get('nightly_queue_cursor', '0'));
        $queue = [];
        if ($queueDate === $today && $queueJson !== '') {
            $decoded = json_decode($queueJson, true);
            if (is_array($decoded)) {
                $queue = array_values(array_unique(array_filter(array_map('intval', $decoded))));
            }
        }

        if ($queueDate !== $today || !$queue) {
            $config = edt_lifecycle_config($settings);
            $ratings = edt_load_ratings();
            [$mainRows, $watchRows] = edt_load_domain_rows($config, $ratings);
            $expiredRows = edt_load_expired_cleanup_rows();
            $queue = [];
            foreach ([$mainRows, $watchRows, $expiredRows] as $rowSet) {
                foreach ($rowSet as $row) {
                    $id = (int) ($row['id'] ?? 0);
                    if ($id > 0 && edt_is_neo_registrar((string) ($row['registrar'] ?? '')) && !in_array($id, $queue, true)) {
                        $queue[] = $id;
                    }
                }
            }
            $cursor = 0;
            edt_meta_set('nightly_queue_date', $today);
            edt_meta_set('nightly_queue_json', json_encode($queue));
            edt_meta_set('nightly_queue_cursor', '0');
            $startedAt = date('Y-m-d H:i:s');
            edt_meta_set('nightly_last_started_at', $startedAt);
            edt_neo_refresh_log_ensure($today, count($queue), $startedAt);
        } else {
            $startedAt = edt_meta_get('nightly_last_started_at', date('Y-m-d H:i:s'));
            edt_neo_refresh_log_ensure($today, count($queue), $startedAt);
        }

        if (!$queue || $cursor >= count($queue)) {
            edt_meta_set('nightly_complete_date', $today);
            edt_meta_set('nightly_last_completed_at', date('Y-m-d H:i:s'));
            edt_meta_set('nightly_queue_cursor', (string) count($queue));
            edt_neo_refresh_log_complete($today, array_slice($queue, 0, $cursor), count($queue));
            return;
        }

        $batch = array_slice($queue, $cursor, 50);
        if (!$batch) {
            return;
        }
        $result = edt_refresh_neo_customer_ids($batch, 50, true);
        $cursor += count($batch);
        edt_meta_set('nightly_queue_cursor', (string) $cursor);
        edt_meta_set('nightly_last_result', json_encode([
            'at' => date('Y-m-d H:i:s'),
            'success' => (int) ($result['success'] ?? 0),
            'failed' => (int) ($result['failed'] ?? 0),
            'processed' => count($batch),
            'cursor' => $cursor,
            'total' => count($queue),
        ]));
        edt_neo_refresh_log_recalculate($today, array_slice($queue, 0, $cursor), count($queue));

        if ($cursor >= count($queue)) {
            edt_meta_set('nightly_complete_date', $today);
            edt_meta_set('nightly_last_completed_at', date('Y-m-d H:i:s'));
            edt_neo_refresh_log_complete($today, $queue, count($queue));
        }
    } catch (Throwable $e) {
        try {
            edt_meta_set('nightly_last_error', date('Y-m-d H:i:s') . ' ' . $e->getMessage());
        } catch (Throwable $ignored) {
        }
        try {
            edt_neo_refresh_log_error(date('Y-m-d'), $e->getMessage());
        } catch (Throwable $ignored) {
        }
    }
}

function edt_neo_terminal_scan_state(): ?array
{
    $state = edt_meta_json(EDT_NEO_TERMINAL_STATE_META);
    if (
        (int) ($state['scan_version'] ?? 0) !== EDT_NEO_TERMINAL_SCAN_VERSION
        || trim((string) ($state['scan_key'] ?? '')) === ''
        || !is_array($state['registrars'] ?? null)
        || !$state['registrars']
    ) {
        return null;
    }
    return $state;
}

function edt_neo_terminal_registrar_accounts(): array
{
    $registrars = [];
    try {
        $configured = Capsule::table('tblregistrars')
            ->where('registrar', 'like', '%netearthone%')
            ->distinct()
            ->pluck('registrar')
            ->all();
        foreach ($configured as $registrar) {
            $registrar = strtolower(trim((string) $registrar));
            if (edt_is_neo_registrar($registrar)) {
                $registrars[$registrar] = $registrar;
            }
        }
    } catch (Throwable $ignored) {
    }

    try {
        $assigned = Capsule::table('tbldomains')
            ->where('registrar', 'like', '%netearthone%')
            ->distinct()
            ->pluck('registrar')
            ->all();
        foreach ($assigned as $registrar) {
            $registrar = strtolower(trim((string) $registrar));
            if (edt_is_neo_registrar($registrar)) {
                $registrars[$registrar] = $registrar;
            }
        }
    } catch (Throwable $ignored) {
    }

    $accounts = [];
    $seen = [];
    foreach ($registrars as $registrar) {
        $credentials = edt_neo_credentials($registrar);
        $authUserId = trim((string) ($credentials['authUserId'] ?? ''));
        $apiKey = trim((string) ($credentials['apiKey'] ?? ''));
        if ($authUserId === '' || $apiKey === '') {
            continue;
        }
        $fingerprint = sha1(edt_neo_api_base($credentials) . '|' . $authUserId);
        if (isset($seen[$fingerprint])) {
            continue;
        }
        $seen[$fingerprint] = true;
        $accounts[] = ['registrar' => $registrar, 'credentials' => $credentials];
    }
    return $accounts;
}

function edt_neo_terminal_new_scan_key(): string
{
    try {
        return bin2hex(random_bytes(16));
    } catch (Throwable $e) {
        return sha1(uniqid('edt-neo-terminal-', true));
    }
}

function edt_run_neo_terminal_scan(bool $restart = false, int $maxPages = 6): array
{
    $maxPages = max(1, min(20, $maxPages));
    $state = edt_neo_terminal_scan_state();
    if ($restart && $state) {
        $oldKey = trim((string) ($state['scan_key'] ?? ''));
        if ($oldKey !== '' && $oldKey !== trim(edt_meta_get(EDT_NEO_TERMINAL_COMPLETE_KEY_META, ''))) {
            Capsule::table(EDT_NEO_TERMINAL_TABLE)->where('scan_key', $oldKey)->delete();
        }
        $state = null;
        edt_meta_set(EDT_NEO_TERMINAL_STATE_META, '');
    }

    if (!$state) {
        $accounts = edt_neo_terminal_registrar_accounts();
        if (!$accounts) {
            return ['ok' => false, 'complete' => false, 'processed' => 0, 'matches' => 0, 'error' => 'No configured NetEarthOne registrar account with readable API credentials was found.'];
        }
        $state = [
            'scan_version' => EDT_NEO_TERMINAL_SCAN_VERSION,
            'scan_key' => edt_neo_terminal_new_scan_key(),
            'registrars' => array_values(array_map(static fn(array $account): string => (string) $account['registrar'], $accounts)),
            'registrar_index' => 0,
            'status_index' => 0,
            'page' => 1,
            'processed' => 0,
            'matches' => 0,
            'validated_domains' => [],
            'started_at' => date('Y-m-d H:i:s'),
        ];
        edt_meta_set(EDT_NEO_TERMINAL_STATE_META, json_encode($state));
    }

    $statuses = ['Deleted', 'Archived'];
    $pageSize = 500;
    $pagesProcessed = 0;
    while ($pagesProcessed < $maxPages) {
        $registrars = array_values(array_filter(array_map('strval', (array) ($state['registrars'] ?? []))));
        $registrarIndex = max(0, (int) ($state['registrar_index'] ?? 0));
        if ($registrarIndex >= count($registrars)) {
            return edt_neo_terminal_finish_scan($state);
        }

        $statusIndex = max(0, (int) ($state['status_index'] ?? 0));
        if ($statusIndex >= count($statuses)) {
            $state['registrar_index'] = $registrarIndex + 1;
            $state['status_index'] = 0;
            $state['page'] = 1;
            edt_meta_set(EDT_NEO_TERMINAL_STATE_META, json_encode($state));
            continue;
        }

        $registrar = strtolower(trim($registrars[$registrarIndex]));
        $credentials = edt_neo_credentials($registrar);
        if (trim((string) ($credentials['authUserId'] ?? '')) === '' || trim((string) ($credentials['apiKey'] ?? '')) === '') {
            return [
                'ok' => false,
                'complete' => false,
                'processed' => (int) ($state['processed'] ?? 0),
                'matches' => (int) ($state['matches'] ?? 0),
                'error' => 'NEO API credentials could not be read for registrar module ' . $registrar . '.',
            ];
        }

        $wantedStatus = $statuses[$statusIndex];
        $page = max(1, (int) ($state['page'] ?? 1));
        [$ok, $response, $apiError] = edt_http_get(edt_neo_api_base($credentials) . '/domains/search.json', [
            'auth-userid' => $credentials['authUserId'],
            'api-key' => $credentials['apiKey'],
            'no-of-records' => $pageSize,
            'page-no' => $page,
            'order-by' => 'orderid desc',
            'status' => $wantedStatus,
        ]);
        if (!$ok || !is_array($response)) {
            return [
                'ok' => false,
                'complete' => false,
                'processed' => (int) ($state['processed'] ?? 0),
                'matches' => (int) ($state['matches'] ?? 0),
                'error' => 'NEO ' . $wantedStatus . ' search failed on page ' . $page . ($apiError !== '' ? ': ' . $apiError : '.'),
            ];
        }

        $records = edt_neo_terminal_records_from_search($response, $wantedStatus);
        $storeResult = edt_store_neo_terminal_matches(
            (string) $state['scan_key'],
            $registrar,
            $records,
            $registrars,
            is_array($state['validated_domains'] ?? null) ? $state['validated_domains'] : []
        );
        $state['validated_domains'] = $storeResult['validated_domains'];
        $state['processed'] = (int) ($state['processed'] ?? 0) + count($records);
        $state['matches'] = (int) Capsule::table(EDT_NEO_TERMINAL_TABLE)->where('scan_key', (string) $state['scan_key'])->count();

        $total = edt_neo_search_total_records($response);
        $hasMore = $total !== null
            ? ($page * $pageSize) < $total
            : count($records) >= $pageSize;
        if ($hasMore) {
            $state['page'] = $page + 1;
        } else {
            $state['status_index'] = $statusIndex + 1;
            $state['page'] = 1;
        }

        $pagesProcessed++;
        edt_meta_set(EDT_NEO_TERMINAL_STATE_META, json_encode($state));
    }

    $registrars = array_values(array_filter(array_map('strval', (array) ($state['registrars'] ?? []))));
    if ((int) ($state['registrar_index'] ?? 0) >= count($registrars)) {
        return edt_neo_terminal_finish_scan($state);
    }
    if ((int) ($state['registrar_index'] ?? 0) === count($registrars) - 1 && (int) ($state['status_index'] ?? 0) >= count($statuses)) {
        $state['registrar_index'] = count($registrars);
        edt_meta_set(EDT_NEO_TERMINAL_STATE_META, json_encode($state));
        return edt_neo_terminal_finish_scan($state);
    }

    return [
        'ok' => true,
        'complete' => false,
        'processed' => (int) ($state['processed'] ?? 0),
        'matches' => (int) ($state['matches'] ?? 0),
        'current_live' => edt_neo_validation_count($state, 'live'),
        'validation_failed' => edt_neo_validation_count($state, 'failed'),
        'error' => '',
    ];
}

function edt_neo_terminal_finish_scan(array $state): array
{
    $scanKey = trim((string) ($state['scan_key'] ?? ''));
    if ($scanKey === '') {
        return ['ok' => false, 'complete' => false, 'processed' => 0, 'matches' => 0, 'error' => 'The NEO scan state was incomplete.'];
    }

    $matches = (int) Capsule::table(EDT_NEO_TERMINAL_TABLE)->where('scan_key', $scanKey)->count();
    $currentLive = edt_neo_validation_count($state, 'live');
    $validationFailed = edt_neo_validation_count($state, 'failed');
    $completedAt = date('Y-m-d H:i:s');
    edt_meta_set(EDT_NEO_TERMINAL_COMPLETE_KEY_META, $scanKey);
    edt_meta_set(EDT_NEO_TERMINAL_COMPLETE_INFO_META, json_encode([
        'scan_version' => EDT_NEO_TERMINAL_SCAN_VERSION,
        'scan_key' => $scanKey,
        'started_at' => (string) ($state['started_at'] ?? ''),
        'completed_at' => $completedAt,
        'processed' => (int) ($state['processed'] ?? 0),
        'matches' => $matches,
        'current_live' => $currentLive,
        'validation_failed' => $validationFailed,
    ]));
    edt_meta_set(EDT_NEO_TERMINAL_STATE_META, '');
    Capsule::table(EDT_NEO_TERMINAL_TABLE)->where('scan_key', '!=', $scanKey)->delete();

    return [
        'ok' => true,
        'complete' => true,
        'processed' => (int) ($state['processed'] ?? 0),
        'matches' => $matches,
        'current_live' => $currentLive,
        'validation_failed' => $validationFailed,
        'error' => '',
    ];
}

function edt_neo_validation_count(array $state, string $wantedResult): int
{
    $validated = is_array($state['validated_domains'] ?? null) ? $state['validated_domains'] : [];
    $count = 0;
    foreach ($validated as $result) {
        if ((string) $result === $wantedResult) {
            $count++;
        }
    }
    return $count;
}

function edt_neo_terminal_records_from_search(array $response, string $fallbackStatus): array
{
    $found = [];
    $walk = function ($node) use (&$walk, &$found, $fallbackStatus): void {
        if (!is_array($node)) {
            return;
        }

        $domain = edt_normalize_domain(edt_neo_array_scalar($node, ['entity.description', 'domainname', 'domain-name', 'description']));
        if ($domain !== '' && str_contains($domain, '.')) {
            $status = trim(edt_neo_array_scalar($node, ['entity.currentstatus', 'currentstatus', 'current-status', 'status']));
            if ($status === '') {
                $status = $fallbackStatus;
            }
            if (edt_neo_status_allows_whmcs_delete($status)) {
                $orderId = trim(edt_neo_array_scalar($node, ['orders.orderid', 'orderid', 'order-id']));
                $record = [
                    'domain' => $domain,
                    'order_status' => strcasecmp($status, 'Archived') === 0 ? 'Archived' : 'Deleted',
                    'neo_order_id' => $orderId,
                    'customer_id' => trim(edt_neo_array_scalar($node, ['entity.customerid', 'customerid', 'customer-id'])),
                    'order_timestamp' => trim(edt_neo_array_scalar($node, ['orders.timestamp', 'timestamp', 'orders.creationdt', 'creationdt'])),
                ];
                $existing = $found[$domain] ?? null;
                if (!$existing || edt_neo_order_id_is_newer($orderId, (string) ($existing['neo_order_id'] ?? ''))) {
                    $found[$domain] = $record;
                }
            }
        }

        foreach ($node as $child) {
            if (is_array($child)) {
                $walk($child);
            }
        }
    };
    $walk($response);
    return array_values($found);
}

function edt_neo_array_scalar(array $node, array $keys): string
{
    foreach ($keys as $key) {
        if (array_key_exists($key, $node) && is_scalar($node[$key])) {
            return trim((string) $node[$key]);
        }
    }
    return '';
}

function edt_neo_order_id_is_newer(string $candidate, string $existing): bool
{
    if ($existing === '') {
        return true;
    }
    if ($candidate === '') {
        return false;
    }
    if (ctype_digit($candidate) && ctype_digit($existing)) {
        if (strlen($candidate) !== strlen($existing)) {
            return strlen($candidate) > strlen($existing);
        }
        return strcmp($candidate, $existing) > 0;
    }
    return strnatcasecmp($candidate, $existing) > 0;
}

function edt_neo_search_total_records(array $response): ?int
{
    $found = null;
    $walk = function ($node) use (&$walk, &$found): void {
        if ($found !== null || !is_array($node)) {
            return;
        }
        foreach (['recsindb', 'records-in-db', 'totalrecords', 'total-records'] as $key) {
            if (array_key_exists($key, $node) && is_numeric($node[$key])) {
                $found = max(0, (int) $node[$key]);
                return;
            }
        }
        foreach ($node as $child) {
            if (is_array($child)) {
                $walk($child);
            }
        }
    };
    $walk($response);
    return $found;
}

/**
 * Resolve the current order across all configured NEO accounts. Historical
 * terminal orders are retained only when no current live order is found.
 *
 * @return array{result: string, order_status: string, lookup: ?array}
 */
function edt_neo_current_domain_state(string $domain, array $registrars, string $preferredRegistrar = ''): array
{
    $domain = edt_normalize_domain($domain);
    if ($domain === '') {
        return ['result' => 'failed', 'order_status' => '', 'lookup' => null];
    }

    $ordered = [];
    $preferredRegistrar = strtolower(trim($preferredRegistrar));
    if (edt_is_neo_registrar($preferredRegistrar)) {
        $ordered[$preferredRegistrar] = $preferredRegistrar;
    }
    foreach ($registrars as $registrar) {
        $registrar = strtolower(trim((string) $registrar));
        if (edt_is_neo_registrar($registrar)) {
            $ordered[$registrar] = $registrar;
        }
    }
    if (!$ordered) {
        return ['result' => 'failed', 'order_status' => '', 'lookup' => null];
    }

    $terminalLookup = null;
    foreach ($ordered as $registrar) {
        $probe = (object) ['domain' => $domain, 'registrar' => $registrar];
        $lookup = edt_neo_lookup_domain_details($probe);
        if (!($lookup['ok'] ?? false)) {
            continue;
        }

        $status = trim((string) ($lookup['order_status'] ?? ''));
        if ($status === '') {
            continue;
        }
        if (!edt_neo_status_allows_whmcs_delete($status)) {
            return ['result' => 'live', 'order_status' => $status, 'lookup' => $lookup];
        }
        if ($terminalLookup === null) {
            $terminalLookup = $lookup;
        }
    }

    if ($terminalLookup !== null) {
        return [
            'result' => 'terminal',
            'order_status' => trim((string) ($terminalLookup['order_status'] ?? '')),
            'lookup' => $terminalLookup,
        ];
    }

    return ['result' => 'failed', 'order_status' => '', 'lookup' => null];
}

/**
 * @return array{validated_domains: array}
 */
function edt_store_neo_terminal_matches(
    string $scanKey,
    string $registrar,
    array $records,
    array $validationRegistrars,
    array $validatedDomains
): array
{
    $domains = [];
    foreach ($records as $record) {
        $domain = edt_normalize_domain((string) ($record['domain'] ?? ''));
        if ($domain !== '') {
            $domains[$domain] = $domain;
        }
    }
    if (!$domains) {
        return ['validated_domains' => $validatedDomains];
    }

    $present = [];
    $whmcsDomains = Capsule::table('tbldomains')
        ->whereIn('domain', array_values($domains))
        ->where('registrar', 'like', '%netearthone%')
        ->get(['id', 'domain', 'registrar']);
    foreach ($whmcsDomains as $whmcsDomain) {
        if (!edt_is_neo_registrar((string) ($whmcsDomain->registrar ?? ''))) {
            continue;
        }
        $domain = edt_normalize_domain((string) ($whmcsDomain->domain ?? ''));
        if ($domain !== '') {
            $present[$domain][] = (int) ($whmcsDomain->id ?? 0);
        }
    }
    if (!$present) {
        return ['validated_domains' => $validatedDomains];
    }

    $now = date('Y-m-d H:i:s');
    foreach ($records as $record) {
        $domain = edt_normalize_domain((string) ($record['domain'] ?? ''));
        if ($domain === '' || !isset($present[$domain])) {
            continue;
        }
        $domainHash = sha1($domain);
        $where = ['scan_key' => $scanKey, 'domain_hash' => $domainHash];
        $existing = Capsule::table(EDT_NEO_TERMINAL_TABLE)->where($where)->first();
        $candidateOrderId = trim((string) ($record['neo_order_id'] ?? ''));
        if ($existing && !edt_neo_order_id_is_newer($candidateOrderId, trim((string) ($existing->neo_order_id ?? '')))) {
            continue;
        }

        $validationResult = trim((string) ($validatedDomains[$domainHash] ?? ''));
        if ($validationResult === '') {
            $current = edt_neo_current_domain_state($domain, $validationRegistrars, $registrar);
            $validationResult = in_array((string) ($current['result'] ?? ''), ['terminal', 'live'], true)
                ? (string) $current['result']
                : 'failed';
            $validatedDomains[$domainHash] = $validationResult;

            $lookup = is_array($current['lookup'] ?? null) ? $current['lookup'] : null;
            if ($lookup !== null) {
                foreach (array_unique(array_filter(array_map('intval', $present[$domain]))) as $domainId) {
                    edt_store_neo_cache($domainId, $domain, $lookup);
                }
            }
        }

        if ($validationResult !== 'terminal') {
            if ($existing) {
                Capsule::table(EDT_NEO_TERMINAL_TABLE)->where('id', (int) $existing->id)->delete();
            }
            continue;
        }

        $values = [
            'domain' => $domain,
            'neo_order_id' => $candidateOrderId !== '' ? $candidateOrderId : null,
            'customer_id' => trim((string) ($record['customer_id'] ?? '')) ?: null,
            'order_status' => strcasecmp((string) ($record['order_status'] ?? ''), 'Archived') === 0 ? 'Archived' : 'Deleted',
            'order_timestamp' => trim((string) ($record['order_timestamp'] ?? '')) ?: null,
            'source_registrar' => $registrar,
            'seen_at' => $now,
        ];
        if ($existing) {
            Capsule::table(EDT_NEO_TERMINAL_TABLE)->where('id', (int) $existing->id)->update($values);
        } else {
            Capsule::table(EDT_NEO_TERMINAL_TABLE)->insert(array_merge($where, $values));
        }
    }

    return ['validated_domains' => $validatedDomains];
}

function edt_load_neo_cache(array $domainIds): array
{
    $domainIds = array_values(array_unique(array_filter(array_map('intval', $domainIds))));
    if (!$domainIds) {
        return [];
    }

    $rows = Capsule::table(EDT_NEO_TABLE)
        ->whereIn('domain_id', $domainIds)
        ->get(['domain_id', 'customer_id', 'order_status', 'registry_created_at', 'last_error', 'checked_at']);

    $cache = [];
    foreach ($rows as $row) {
        $cache[(int) $row->domain_id] = [
            'customer_id' => trim((string) ($row->customer_id ?? '')),
            'order_status' => trim((string) ($row->order_status ?? '')),
            'registry_created_at' => trim((string) ($row->registry_created_at ?? '')),
            'last_error' => trim((string) ($row->last_error ?? '')),
            'checked_at' => (string) ($row->checked_at ?? ''),
        ];
    }
    return $cache;
}

function edt_refresh_neo_customer_ids(array $domainIds, int $limit = 50, bool $force = false): array
{
    $domainIds = array_values(array_unique(array_filter(array_map('intval', $domainIds))));
    $result = ['success' => 0, 'failed' => 0, 'skipped' => 0, 'remaining' => 0];
    if (!$domainIds) {
        return $result;
    }

    $domainRows = Capsule::table('tbldomains')
        ->whereIn('id', $domainIds)
        ->get(['id', 'domain', 'registrar']);

    // WHERE IN does not guarantee row order. Rebuild the collection in the exact
    // order supplied by the UI so bulk NEO refresh always walks top-to-bottom.
    $domainsById = [];
    foreach ($domainRows as $domainRow) {
        $domainsById[(int) $domainRow->id] = $domainRow;
    }
    $domains = [];
    foreach ($domainIds as $domainId) {
        if (isset($domainsById[$domainId])) {
            $domains[] = $domainsById[$domainId];
        }
    }

    $now = new DateTimeImmutable('now');
    $toRefresh = [];
    foreach ($domains as $domain) {
        if (!edt_is_neo_registrar((string) $domain->registrar)) {
            continue;
        }

        $cache = Capsule::table(EDT_NEO_TABLE)->where('domain_id', (int) $domain->id)->first();
        $fresh = false;
        if ($cache && !empty($cache->checked_at)) {
            try {
                $checked = new DateTimeImmutable((string) $cache->checked_at);
                $customerId = trim((string) ($cache->customer_id ?? ''));
                $orderStatus = trim((string) ($cache->order_status ?? ''));
                $registryCreatedAt = trim((string) ($cache->registry_created_at ?? ''));
                $displayValue = edt_neo_customer_for_display($customerId, $orderStatus);

                // A cache row is only complete when it also contains the registry
                // creation date introduced in 1.9.8. This deliberately makes older
                // otherwise-fresh cache rows eligible for one refresh so Reg Date can
                // be backfilled without waiting for the 30-day cache window to expire.
                // Terminal Deleted/Archived orders may not expose creationtime through
                // details-by-name, so do not force repeated refreshes for those rows.
                $hasRegistryDate = $registryCreatedAt !== '' || edt_neo_status_allows_whmcs_delete($orderStatus);
                if ($displayValue !== '' && $hasRegistryDate) {
                    $fresh = $checked >= $now->modify('-30 days');
                }
            } catch (Throwable $e) {
                $fresh = false;
            }
        }
        if ($fresh && !$force) {
            $result['skipped']++;
            continue;
        }
        $toRefresh[] = $domain;
    }

    $result['remaining'] = max(0, count($toRefresh) - $limit);
    $toRefresh = array_slice($toRefresh, 0, $limit);

    foreach ($toRefresh as $domain) {
        $lookup = edt_neo_lookup_domain_details($domain);
        edt_store_neo_cache((int) $domain->id, edt_normalize_domain((string) $domain->domain), $lookup);
        if ($lookup['ok']) {
            $result['success']++;
        } else {
            $result['failed']++;
        }
    }

    return $result;
}

function edt_neo_lookup_domain_details($domain): array
{
    $domainName = edt_normalize_domain((string) ($domain->domain ?? ''));
    $registrar = strtolower(trim((string) ($domain->registrar ?? '')));
    if ($domainName === '' || !edt_is_neo_registrar($registrar)) {
        return ['ok' => false, 'customer_id' => '', 'order_status' => '', 'registry_created_at' => '', 'error' => 'This domain is not assigned to the NEO registrar module.'];
    }

    $credentials = edt_neo_credentials($registrar);
    if (($credentials['authUserId'] ?? '') === '' || ($credentials['apiKey'] ?? '') === '') {
        return ['ok' => false, 'customer_id' => '', 'order_status' => '', 'registry_created_at' => '', 'error' => 'NEO API credentials could not be read from registrar configuration.'];
    }

    [$ok, $response, $apiError] = edt_http_get(edt_neo_api_base($credentials) . '/domains/details-by-name.json', [
        'auth-userid' => $credentials['authUserId'],
        'api-key' => $credentials['apiKey'],
        'domain-name' => $domainName,
        // Use the same broad details request as BDM. NEO documents creationtime
        // (Order Creation at the Registry) in OrderDetails and All; All also keeps
        // the existing status/customer fields in one response.
        'options' => 'All',
    ]);

    if ($ok && is_array($response)) {
        $customerId = trim((string) ($response['customerid'] ?? $response['customer-id'] ?? ''));
        $orderStatus = trim((string) ($response['currentstatus'] ?? $response['current-status'] ?? $response['status'] ?? ''));
        $registryCreatedAt = edt_neo_registry_date($response['creationtime'] ?? $response['creation-time'] ?? '');
        if ($registryCreatedAt === '') {
            // Some NEO/LogicBoxes accounts omit creationtime from details-by-name
            // even when OrderDetails/All is requested. The domain search endpoint
            // independently exposes orders.creationtime (registry creation time),
            // so use that as an exact-domain fallback rather than WHMCS regdate.
            $registryCreatedAt = edt_neo_lookup_registry_created_at($credentials, $domainName);
        }
        if ($customerId !== '' || $orderStatus !== '') {
            return [
                'ok' => true,
                'customer_id' => $customerId,
                'order_status' => $orderStatus,
                'registry_created_at' => $registryCreatedAt,
                'error' => '',
            ];
        }
    }

    // LogicBoxes/NEO keeps historical domain orders after deletion. The normal
    // details-by-name endpoint may no longer return those terminal orders, so
    // explicitly search the domain-order history for Deleted/Archived records.
    // NEO documents Deleted as orders removed within 30 days and Archived as
    // orders removed more than 30 days ago.
    $terminal = edt_neo_lookup_terminal_domain_order($credentials, $domainName);
    if ($terminal['ok']) {
        return $terminal;
    }

    $errors = [];
    if ($apiError !== '') {
        $errors[] = $apiError;
    }
    if (($terminal['error'] ?? '') !== '') {
        $errors[] = (string) $terminal['error'];
    }

    return [
        'ok' => false,
        'customer_id' => '',
        'order_status' => '',
        'error' => $errors ? implode(' | ', array_values(array_unique($errors))) : 'NEO lookup did not find a current, Deleted, or Archived domain order.',
    ];
}

function edt_neo_lookup_registry_created_at(array $credentials, string $domainName): string
{
    [$ok, $response] = edt_http_get(edt_neo_api_base($credentials) . '/domains/search.json', [
        'auth-userid' => $credentials['authUserId'],
        'api-key' => $credentials['apiKey'],
        'no-of-records' => 10,
        'page-no' => 1,
        'order-by' => 'orderid desc',
        'domain-name' => $domainName,
    ]);

    if (!$ok || !is_array($response)) {
        return '';
    }

    $wantedDomain = edt_normalize_domain($domainName);
    $found = '';

    $walk = function ($node) use (&$walk, &$found, $wantedDomain): void {
        if ($found !== '' || !is_array($node)) {
            return;
        }

        $domain = '';
        foreach (['entity.description', 'domainname', 'domain-name', 'description'] as $key) {
            if (array_key_exists($key, $node) && is_scalar($node[$key])) {
                $candidate = edt_normalize_domain((string) $node[$key]);
                if ($candidate !== '') {
                    $domain = $candidate;
                    break;
                }
            }
        }
        if ($domain === '' && isset($node['entity']) && is_array($node['entity'])) {
            foreach (['description', 'domainname', 'domain-name'] as $key) {
                if (array_key_exists($key, $node['entity']) && is_scalar($node['entity'][$key])) {
                    $candidate = edt_normalize_domain((string) $node['entity'][$key]);
                    if ($candidate !== '') {
                        $domain = $candidate;
                        break;
                    }
                }
            }
        }

        if ($domain === $wantedDomain) {
            foreach (['orders.creationtime', 'creationtime', 'creation-time'] as $key) {
                if (array_key_exists($key, $node) && is_scalar($node[$key])) {
                    $candidate = edt_neo_registry_date($node[$key]);
                    if ($candidate !== '') {
                        $found = $candidate;
                        return;
                    }
                }
            }
            if (isset($node['orders']) && is_array($node['orders'])) {
                foreach (['creationtime', 'creation-time'] as $key) {
                    if (array_key_exists($key, $node['orders']) && is_scalar($node['orders'][$key])) {
                        $candidate = edt_neo_registry_date($node['orders'][$key]);
                        if ($candidate !== '') {
                            $found = $candidate;
                            return;
                        }
                    }
                }
            }
        }

        foreach ($node as $child) {
            if (is_array($child)) {
                $walk($child);
                if ($found !== '') {
                    return;
                }
            }
        }
    };

    $walk($response);
    return $found;
}

function edt_neo_registry_date($value): string
{
    if ($value === null || $value === '') {
        return '';
    }

    if (is_int($value) || is_float($value) || (is_string($value) && preg_match('/^\d+(?:\.\d+)?$/', trim($value)))) {
        $numeric = (float) $value;
        if ($numeric > 99999999999) {
            $numeric /= 1000;
        }
        $timestamp = (int) floor($numeric);
        if ($timestamp > 0) {
            return gmdate('Y-m-d', $timestamp);
        }
    }

    $text = trim((string) $value);
    if ($text === '' || $text === '0000-00-00' || $text === '0000-00-00 00:00:00') {
        return '';
    }

    try {
        return (new DateTimeImmutable($text))->format('Y-m-d');
    } catch (Throwable $e) {
        return '';
    }
}

function edt_neo_lookup_terminal_domain_order(array $credentials, string $domainName): array
{
    // Search terminal states separately. LogicBoxes documents status as an
    // array parameter, but NEO's archived-order search can behave differently
    // when Deleted and Archived are supplied together. Separate exact-domain
    // requests make Archived orders discoverable without weakening the match.
    $errors = [];
    foreach (['Deleted', 'Archived'] as $wantedStatus) {
        [$ok, $response, $apiError] = edt_http_get(edt_neo_api_base($credentials) . '/domains/search.json', [
            'auth-userid' => $credentials['authUserId'],
            'api-key' => $credentials['apiKey'],
            'no-of-records' => 10,
            'page-no' => 1,
            'order-by' => 'orderid desc',
            'domain-name' => $domainName,
            'status' => $wantedStatus,
        ]);

        if (!$ok || !is_array($response)) {
            if ($apiError !== '') {
                $errors[] = $wantedStatus . ': ' . $apiError;
            }
            continue;
        }

        $match = edt_neo_find_terminal_order_in_search($response, $domainName);
        if ($match !== null) {
            return [
                'ok' => true,
                'customer_id' => $match['customer_id'],
                'order_status' => $match['order_status'],
                'registry_created_at' => (string) ($match['registry_created_at'] ?? ''),
                'error' => '',
            ];
        }
    }

    return [
        'ok' => false,
        'customer_id' => '',
        'order_status' => '',
        'error' => $errors ? implode(' | ', array_values(array_unique($errors))) : 'NEO search did not return a Deleted or Archived order for this domain.',
    ];
}

function edt_neo_find_terminal_order_in_search(array $response, string $domainName): ?array
{
    $wantedDomain = edt_normalize_domain($domainName);
    $found = null;

    $walk = function ($node) use (&$walk, &$found, $wantedDomain): void {
        if ($found !== null || !is_array($node)) {
            return;
        }

        $domain = '';
        foreach (['entity.description', 'domainname', 'domain-name', 'description'] as $key) {
            if (array_key_exists($key, $node) && is_scalar($node[$key])) {
                $candidate = edt_normalize_domain((string) $node[$key]);
                if ($candidate !== '') {
                    $domain = $candidate;
                    break;
                }
            }
        }

        $status = '';
        foreach (['entity.currentstatus', 'currentstatus', 'current-status', 'status'] as $key) {
            if (array_key_exists($key, $node) && is_scalar($node[$key])) {
                $candidate = trim((string) $node[$key]);
                if ($candidate !== '') {
                    $status = $candidate;
                    break;
                }
            }
        }

        if ($domain === $wantedDomain && edt_neo_status_allows_whmcs_delete($status)) {
            $customerId = '';
            foreach (['entity.customerid', 'customerid', 'customer-id'] as $key) {
                if (array_key_exists($key, $node) && is_scalar($node[$key])) {
                    $candidate = trim((string) $node[$key]);
                    if ($candidate !== '') {
                        $customerId = $candidate;
                        break;
                    }
                }
            }
            $registryCreatedAt = '';
            foreach (['orders.creationtime', 'creationtime', 'creation-time'] as $key) {
                if (array_key_exists($key, $node) && is_scalar($node[$key])) {
                    $registryCreatedAt = edt_neo_registry_date($node[$key]);
                    if ($registryCreatedAt !== '') {
                        break;
                    }
                }
            }
            $found = [
                'customer_id' => $customerId,
                'order_status' => $status,
                'registry_created_at' => $registryCreatedAt,
            ];
            return;
        }

        foreach ($node as $child) {
            if (is_array($child)) {
                $walk($child);
                if ($found !== null) {
                    return;
                }
            }
        }
    };

    $walk($response);
    return $found;
}

function edt_store_neo_cache(int $domainId, string $domainName, array $lookup): void
{
    $payload = [
        'domain' => $domainName,
        'customer_id' => trim((string) ($lookup['customer_id'] ?? '')) !== '' ? trim((string) $lookup['customer_id']) : null,
        'order_status' => trim((string) ($lookup['order_status'] ?? '')) !== '' ? trim((string) $lookup['order_status']) : null,
        'last_error' => trim((string) ($lookup['error'] ?? '')) !== '' ? trim((string) $lookup['error']) : null,
        'checked_at' => date('Y-m-d H:i:s'),
    ];
    $registryCreatedAt = edt_neo_registry_date($lookup['registry_created_at'] ?? '');
    if ($registryCreatedAt !== '') {
        $payload['registry_created_at'] = $registryCreatedAt;
    }

    $exists = Capsule::table(EDT_NEO_TABLE)->where('domain_id', $domainId)->exists();
    if ($exists) {
        Capsule::table(EDT_NEO_TABLE)->where('domain_id', $domainId)->update($payload);
    } else {
        $payload['domain_id'] = $domainId;
        if (!array_key_exists('registry_created_at', $payload)) {
            $payload['registry_created_at'] = null;
        }
        Capsule::table(EDT_NEO_TABLE)->insert($payload);
    }
}

function edt_neo_customer_for_display(string $customerId, string $orderStatus): string
{
    $customerId = trim($customerId);
    $orderStatus = trim($orderStatus);

    // Both Deleted and Archived are terminal NEO states. For the admin UI,
    // normalize both to the single visible label "Deleted" while preserving
    // the actual cached order_status for the live deletion safeguard.
    if (edt_neo_status_allows_whmcs_delete($orderStatus)) {
        return 'Deleted';
    }

    return $customerId;
}

function edt_neo_status_allows_whmcs_delete(string $status): bool
{
    $normalized = strtolower(trim($status));
    return in_array($normalized, ['deleted', 'archived'], true);
}

function edt_is_neo_registrar(string $registrar): bool
{
    $registrar = strtolower(trim($registrar));
    return $registrar !== '' && str_contains($registrar, 'netearthone');
}

function edt_neo_credentials(string $registrar): array
{
    if (function_exists('dm_epp_find_credentials')) {
        try {
            $found = dm_epp_find_credentials($registrar);
            if (is_array($found) && trim((string) ($found['authUserId'] ?? '')) !== '' && trim((string) ($found['apiKey'] ?? '')) !== '') {
                return $found;
            }
        } catch (Throwable $e) {
            // Continue to local registrar-config fallback.
        }
    }

    $config = [];
    try {
        $rows = Capsule::table('tblregistrars')->where('registrar', $registrar)->get();
        foreach ($rows as $row) {
            $key = (string) ($row->setting ?? '');
            if ($key === '') {
                continue;
            }
            $config[$key] = edt_decrypt_candidates((string) ($row->value ?? ''));
        }
    } catch (Throwable $e) {
        return ['authUserId' => '', 'apiKey' => '', 'apiUrl' => '', 'testMode' => ''];
    }

    return [
        'authUserId' => edt_pick_config($config, ['auth-userid', 'authuserid', 'userid', 'resellerid', 'username'], true),
        'apiKey' => edt_pick_config($config, ['api-key', 'apikey', 'accesskey', 'key', 'password', 'notapplicable'], false),
        'apiUrl' => edt_pick_config($config, ['apiurl', 'url', 'endpoint'], false),
        'testMode' => edt_pick_config($config, ['testmode', 'sandbox', 'demo'], false),
    ];
}

function edt_decrypt_candidates(string $value): array
{
    $candidates = [];
    if ($value !== '') {
        $candidates[] = $value;
    }

    if ($value !== '' && function_exists('decrypt')) {
        try {
            $decrypted = decrypt($value);
            if (is_string($decrypted) && $decrypted !== '') {
                $candidates[] = $decrypted;
            }
        } catch (Throwable $e) {
            // Ignore and try supported encryption classes.
        }
    }

    foreach (['WHMCS\\Security\\Encryption', 'WHMCS\\Security\\Encryption\\Aes'] as $className) {
        if ($value === '' || !class_exists($className)) {
            continue;
        }
        try {
            $encryption = new $className();
            foreach (['decrypt', 'decryptValue'] as $method) {
                if (method_exists($encryption, $method)) {
                    $decrypted = $encryption->{$method}($value);
                    if (is_string($decrypted) && $decrypted !== '') {
                        $candidates[] = $decrypted;
                    }
                }
            }
        } catch (Throwable $e) {
            // Ignore.
        }
    }

    return array_values(array_unique(array_filter(array_map('trim', $candidates), static fn($item) => $item !== '')));
}

function edt_pick_config(array $config, array $names, bool $numeric): string
{
    $normalized = [];
    foreach ($config as $key => $values) {
        $norm = strtolower(preg_replace('/[^a-z0-9]/i', '', (string) $key) ?? '');
        $normalized[$norm] = is_array($values) ? $values : [(string) $values];
    }

    foreach ($names as $name) {
        $norm = strtolower(preg_replace('/[^a-z0-9]/i', '', (string) $name) ?? '');
        foreach ($normalized[$norm] ?? [] as $candidate) {
            $candidate = trim((string) $candidate);
            if ($candidate === '') {
                continue;
            }
            if ($numeric && !ctype_digit($candidate)) {
                continue;
            }
            return $candidate;
        }
    }
    return '';
}

function edt_neo_api_base(array $credentials): string
{
    $configured = trim((string) ($credentials['apiUrl'] ?? ''));
    if ($configured !== '' && preg_match('#^https?://#i', $configured)) {
        return rtrim($configured, '/');
    }

    $test = strtolower(trim((string) ($credentials['testMode'] ?? '')));
    if (in_array($test, ['1', 'on', 'yes', 'true', 'test', 'sandbox'], true)) {
        return 'https://test.httpapi.com/api';
    }
    return 'https://httpapi.com/api';
}

function edt_http_get(string $url, array $params): array
{
    if (!function_exists('curl_init')) {
        return [false, null, 'cURL is not available.'];
    }

    $queryParts = [];
    foreach ($params as $key => $value) {
        $values = is_array($value) ? $value : [$value];
        foreach ($values as $item) {
            $queryParts[] = rawurlencode((string) $key) . '=' . rawurlencode((string) $item);
        }
    }
    $url .= (str_contains($url, '?') ? '&' : '?') . implode('&', $queryParts);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 25);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_USERAGENT, 'DomainMonger-Expiring-Domain-Tracker/' . EDT_VERSION);
    $raw = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($raw === false) {
        return [false, null, $curlError !== '' ? $curlError : 'No response from NEO API.'];
    }

    $decoded = json_decode((string) $raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $decoded = trim((string) $raw);
    }

    if ($httpCode < 200 || $httpCode >= 300) {
        $detail = is_array($decoded)
            ? (string) ($decoded['message'] ?? $decoded['error'] ?? $decoded['status'] ?? '')
            : mb_substr(strip_tags((string) $decoded), 0, 240);
        return [false, $decoded, 'NEO API returned HTTP ' . $httpCode . ($detail !== '' ? ': ' . $detail : '.')];
    }

    if (is_array($decoded) && strtoupper((string) ($decoded['status'] ?? '')) === 'ERROR') {
        return [false, $decoded, (string) ($decoded['message'] ?? $decoded['error'] ?? 'NEO API returned an error.')];
    }

    return [true, $decoded, ''];
}

function edt_parse_id_list(string $list): array
{
    $parts = preg_split('/[^0-9]+/', $list) ?: [];
    return array_values(array_unique(array_filter(array_map('intval', $parts))));
}

function edt_normalize_domain(string $domain): string
{
    $domain = strtolower(trim($domain));
    $domain = preg_replace('#^https?://#i', '', $domain) ?? $domain;
    $domain = preg_replace('#[/:].*$#', '', $domain) ?? $domain;
    return rtrim($domain, '.');
}

function edt_format_date(DateTimeImmutable $date): string
{
    return $date->format('m/d/Y');
}

function edt_format_datetime(string $value): string
{
    if ($value === '') {
        return '';
    }
    try {
        return (new DateTimeImmutable($value))->format('m/d/Y g:i A');
    } catch (Throwable $e) {
        return $value;
    }
}

function edt_status_icon(string $status): string
{
    return match ($status) {
        'Active' => 'fa-check',
        'Expired' => 'fa-times',
        'Redemption' => 'fa-undo-alt',
        'Pending Delete' => 'fa-hourglass-half',
        'Available' => 'fa-unlock-alt',
        default => 'fa-circle',
    };
}

function edt_status_icon_html(string $status, bool $autoRenew = false, bool $watch = false): string
{
    $html = '<span class="edt-icon-cluster">';
    if ($watch) {
        $html .= '<span class="edt-flag-icon edt-flag-watch" title="Watch List" aria-label="Watch List"><i class="fas fa-star" aria-hidden="true"></i></span>';
    }
    if ($autoRenew) {
        $html .= '<span class="edt-flag-icon edt-flag-renew" title="Auto Renew enabled" aria-label="Auto Renew enabled"><i class="fas fa-sync-alt" aria-hidden="true"></i></span>';
    }
    $html .= '<span class="edt-state-icon ' . edt_status_class($status) . '" title="' . edt_escape($status) . '" aria-label="' . edt_escape($status) . '"><i class="fas ' . edt_escape(edt_status_icon($status)) . '" aria-hidden="true"></i></span>';
    $html .= '</span>';
    return $html;
}

function edt_status_export_value(string $status, bool $autoRenew, bool $watch): string
{
    $parts = [];
    if ($watch) {
        $parts[] = 'Watch List';
    }
    if ($autoRenew) {
        $parts[] = 'Auto Renew';
    }
    $parts[] = $status;
    return implode(' | ', $parts);
}

function edt_status_class(string $status): string
{
    return match ($status) {
        'Active' => 'edt-status-active',
        'Expired' => 'edt-status-expired',
        'Redemption' => 'edt-status-redemption',
        'Pending Delete' => 'edt-status-pending',
        'Available' => 'edt-status-available',
        default => 'edt-status-expired',
    };
}

function edt_alert(string $type, string $message): string
{
    return '<div class="alert alert-' . edt_escape($type) . '">' . edt_escape($message) . '</div>';
}

function edt_escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function edt_styles(): string
{
    return <<<'HTML'
<style>
.edt-wrap{color:#333}.edt-title{background:#163a5f;color:#fff;padding:15px 18px;border-radius:4px 4px 0 0;display:flex;align-items:center;justify-content:space-between}.edt-title h2{margin:0 0 3px;color:#fff;font-size:22px}.edt-title p{margin:0;color:#e8eef4}.edt-tabs{margin:0 0 16px;border-bottom:1px solid #d7dce2}.edt-tabs>li>a{color:#163a5f;background:#f7f8fa;border:1px solid transparent;border-top:0;text-decoration:none;font-weight:600;transition:none!important}.edt-tabs>li>a:hover,.edt-tabs>li>a:focus{color:#f58220;background:#fff;text-decoration:none}.edt-tabs>li.active>a,.edt-tabs>li.active>a:hover,.edt-tabs>li.active>a:focus{background:#163a5f!important;color:#fff!important;border-color:#163a5f!important}.edt-panel{background:#fff;border:1px solid #d7dce2;border-radius:4px;padding:0 14px 14px}.edt-panel-head{margin:0 -14px 12px;padding:11px 14px;background:#163a5f;color:#fff;display:flex;align-items:center;justify-content:space-between;gap:15px}.edt-panel-head h3{margin:0 0 2px;color:#fff;font-size:17px}.edt-panel-head p{margin:0;color:#e8eef4}.edt-count{font-weight:700;white-space:nowrap}.edt-status-summary{display:flex;gap:10px;flex-wrap:wrap;margin:0 0 12px}.edt-summary-item{display:flex;align-items:center;gap:6px;color:#555}.edt-summary-item strong{font-variant-numeric:tabular-nums}.edt-status{display:inline-block;padding:4px 8px;border-radius:12px;font-size:11px;font-weight:700;line-height:1.15;white-space:nowrap}.edt-status-active{background:#163a5f;color:#fff}.edt-status-expired{background:#777;color:#fff}.edt-status-redemption{background:#d8741f;color:#fff}.edt-status-pending{background:#fcf8e3;color:#8a6d3b;border:1px solid #faebcc}.edt-status-available{background:#3c763d;color:#fff}.edt-status-open{background:#d8741f;color:#fff}.edt-status-resolved{background:#777;color:#fff}.edt-status-error{background:#b94a48;color:#fff}.edt-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin:0 0 10px}.edt-toolbar-left{display:flex;align-items:center;gap:7px;flex-wrap:wrap}.edt-toolbar label{margin:0;color:#555;font-weight:600}.edt-page-size{width:82px!important;height:34px;padding-left:7px!important;padding-right:24px!important}.edt-search{position:relative;flex:0 1 340px;min-width:240px;margin-left:auto}.edt-search .fas{position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#7b8794;pointer-events:none}.edt-search .form-control{height:34px;padding-left:31px}.edt-table{margin-bottom:0;background:#fff}.edt-table thead th{background:#163a5f!important;color:#fff!important;border-color:#294f72!important;vertical-align:middle;white-space:normal;font-size:12px;line-height:1.15}.edt-table tbody td{vertical-align:middle;font-size:12px}.edt-table tbody tr:hover td{background:#f7f8fa!important}.edt-sortable-heading{padding-right:20px!important;position:relative;cursor:pointer;white-space:normal;user-select:none}.edt-sortable-heading.sorting:after,.edt-sortable-heading.sorting_asc:after,.edt-sortable-heading.sorting_desc:after{position:absolute;bottom:5px;right:8px;display:block;font-family:'Glyphicons Halflings';opacity:.5;font-weight:400}.edt-sortable-heading.sorting:after{opacity:.2;content:"\e150"}.edt-sortable-heading.sorting_asc:after{content:"\e155"}.edt-sortable-heading.sorting_desc:after{content:"\e156"}.edt-sort-button{display:block;width:100%;text-align:left;border:0!important;padding:0!important;margin:0!important;background:transparent!important;color:inherit!important;font:inherit;font-weight:600;cursor:pointer;box-shadow:none!important}.edt-sort-button:hover,.edt-sort-button:focus,.edt-sort-button:active{color:inherit!important;text-decoration:none!important;outline:none!important}.edt-domain a{color:#222;font-weight:600;text-decoration:none}.edt-domain a:hover,.edt-domain a:focus{color:#f58220;text-decoration:none}.edt-rating{min-width:0;width:64px!important;height:28px;padding:3px 4px;font-size:11px}.edt-rating-cell{width:68px;min-width:68px}.edt-num{text-align:center;font-variant-numeric:tabular-nums;white-space:nowrap}.edt-date{white-space:nowrap;font-variant-numeric:tabular-nums}.edt-tld{white-space:nowrap}.edt-muted{color:#999}.edt-negative{color:#b94a48;font-weight:700}.edt-bottom{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-top:10px}.edt-table-controls{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;margin:8px 0}.edt-table-controls .edt-actions{display:flex;align-items:center;gap:7px;flex-wrap:wrap}.edt-pager{margin-left:auto;display:flex;align-items:center;gap:8px;flex-wrap:wrap}.edt-pager .edt-page-size{flex:0 0 82px}.edt-pager [data-edt-status]{min-width:100px;color:#666;font-weight:600;text-align:right}.edt-btn-primary{background:#f58220!important;border-color:#e77615!important;color:#fff!important;transition:none!important}.edt-btn-primary:hover,.edt-btn-primary:focus{background:#d8741f!important;border-color:#c96816!important;color:#fff!important}.edt-btn-secondary{background:#163a5f!important;border-color:#163a5f!important;color:#fff!important;transition:none!important}.edt-btn-secondary:hover,.edt-btn-secondary:focus,.edt-filter-active{background:#214e7a!important;border-color:#214e7a!important;color:#fff!important}.edt-btn-danger{background:#b94a48!important;border-color:#a94442!important;color:#fff!important}.edt-btn-danger:hover,.edt-btn-danger:focus{background:#a94442!important;color:#fff!important}.edt-btn-danger[disabled]{background:#aaa!important;border-color:#999!important;color:#fff!important;opacity:.6}.edt-empty{padding:18px;background:#f7f8fa;border:1px solid #e1e5e9;text-align:center;color:#666}.edt-no-match{margin-top:10px}.edt-domain-search-link{color:#222;text-decoration:none;font-weight:600}.edt-domain-search-link:hover,.edt-domain-search-link:focus{color:#f58220;text-decoration:none}.edt-neo-form{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-top:12px;padding-top:12px;border-top:1px solid #e4e7ea;color:#666}.edt-hidden-form{display:none!important}.edt-neo-account-button{border:0;background:transparent;padding:0;margin:0;color:#222;font:inherit;font-weight:600;cursor:pointer;box-shadow:none!important;outline:none!important;text-decoration:none}.edt-neo-account-button:hover,.edt-neo-account-button:focus{color:#f58220;text-decoration:none;outline:none}.edt-neo-account-button.edt-muted{color:#999}.edt-neo-account-button.edt-muted:hover,.edt-neo-account-button.edt-muted:focus{color:#f58220}.edt-recon-add{background:#f7f8fa;border:1px solid #dfe3e7;padding:12px;margin-bottom:12px}.edt-recon-grid{display:grid;grid-template-columns:minmax(180px,1fr) minmax(220px,1.4fr) minmax(220px,1.4fr);gap:10px}.edt-recon-grid label{display:block;color:#163a5f;font-weight:600}.edt-recon-notes{grid-column:1 / span 3}.edt-recon-submit{grid-column:1 / span 3}.edt-recon-filter{display:flex;gap:7px;margin:0 0 10px}.edt-inline-form{display:inline-block;margin:0 4px 4px 0}.edt-row-actions{white-space:nowrap}.edt-notes-cell{min-width:220px;white-space:normal}.edt-recon-table .btn{padding:4px 8px;font-size:11px}.edt-tabs a.edt-loading{pointer-events:none;opacity:.8}.edt-tab-spinner{margin-right:6px}.edt-status-filter-wrap{position:relative}.edt-status-filter-toggle{height:34px;min-width:155px;text-align:left;display:flex;align-items:center;justify-content:space-between;gap:12px}.edt-status-filter-menu{display:none;position:absolute;top:100%;left:0;z-index:1050;min-width:205px;margin-top:3px;padding:6px;background:#fff;border:1px solid #c7ced6;border-radius:4px;box-shadow:0 4px 12px rgba(0,0,0,.16)}.edt-status-filter-wrap.edt-open .edt-status-filter-menu{display:block}.edt-status-filter-menu label{display:block;margin:0;padding:6px 8px;color:#163a5f;font-weight:600;cursor:pointer;border-radius:3px}.edt-status-filter-menu label:hover{background:#fff3e8;color:#163a5f}.edt-status-filter-menu input{margin:0 6px 0 0;vertical-align:middle}.edt-status-filter-actions{display:flex;justify-content:space-between;border-top:1px solid #e4e7ea;margin-top:4px;padding:6px 4px 0}.edt-status-filter-actions button{border:0;background:transparent;color:#163a5f;padding:2px 4px;font-weight:600}.edt-status-filter-actions button:hover,.edt-status-filter-actions button:focus{color:#f58220;outline:none}.edt-neo-terminal{color:#b94a48!important;font-weight:700}.edt-select-col{width:38px!important;min-width:38px;text-align:center!important;padding-left:6px!important;padding-right:6px!important}.edt-select-col input{margin:0;vertical-align:middle}.edt-expired-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap}.edt-expired-actions [data-edt-expired-selected-count]{color:#666;font-weight:600}.edt-expired-table th:nth-child(3),.edt-expired-table td:nth-child(3){width:110px}.edt-expired-table th:nth-child(4),.edt-expired-table td:nth-child(4){width:82px}.edt-expired-table th:nth-child(5),.edt-expired-table td:nth-child(5){width:120px}.edt-neo-form [data-edt-neo-bulk-button] .fa-spinner{margin-right:4px}.edt-table tbody tr.edt-neo-focus-row td{background:#fff3e8!important}.edt-domain-table-wrap{overflow-x:auto}.edt-domain-table th,.edt-domain-table td{padding:5px 4px!important;font-size:11px}.edt-domain-table .edt-domain{max-width:145px;white-space:normal;overflow-wrap:anywhere}.edt-domain-table .edt-name-cell{max-width:110px;white-space:normal;overflow-wrap:anywhere}.edt-domain-table th:nth-child(3),.edt-domain-table td:nth-child(3),.edt-domain-table th:nth-child(4),.edt-domain-table td:nth-child(4),.edt-domain-table th:nth-child(5),.edt-domain-table td:nth-child(5),.edt-domain-table th:nth-child(12),.edt-domain-table td:nth-child(12),.edt-domain-table th:nth-child(13),.edt-domain-table td:nth-child(13){width:74px}.edt-domain-table th:nth-child(6),.edt-domain-table td:nth-child(6){width:54px}.edt-domain-table th:nth-child(7),.edt-domain-table td:nth-child(7){width:68px}.edt-domain-table th:nth-child(9),.edt-domain-table td:nth-child(9){width:46px}.edt-domain-table th:nth-child(10),.edt-domain-table td:nth-child(10){width:42px}.edt-domain-table th:nth-child(11),.edt-domain-table td:nth-child(11){width:66px}.edt-neo-note{margin:4px 0 10px;color:#777;font-size:11px}.edt-table-controls [data-edt-neo-bulk-trigger] .fa-spinner{margin-right:4px}.edt-window-control{display:flex;align-items:center;gap:7px;flex-wrap:wrap;margin:0 0 10px}.edt-window-select{width:92px!important;height:34px!important;padding:6px 8px!important}.edt-window-note{color:#777;font-size:11px}.edt-window-spinner{display:none;color:#163a5f;font-size:12px}.edt-window-control.edt-window-loading .edt-window-spinner{display:inline-block}.edt-window-control.edt-window-loading .edt-window-select{opacity:.65}.edt-toolbar-search-only{justify-content:flex-end}.edt-toolbar-search-only .edt-search{margin-left:auto}.edt-rating{width:42px!important;min-width:42px!important;text-align:center;padding-left:4px;padding-right:4px}.edt-rating-cell{width:46px!important;min-width:46px!important}.edt-rating.edt-rating-saving{opacity:.6}.edt-rating.edt-rating-saved{box-shadow:0 0 0 1px #163a5f!important}.edt-rating.edt-rating-error{box-shadow:0 0 0 1px #b94a48!important}.edt-status-cell{width:58px!important;min-width:58px!important;text-align:right!important;white-space:nowrap!important;padding-left:3px!important;padding-right:3px!important}.edt-icon-cluster{display:inline-flex;align-items:center;justify-content:center;gap:2px;white-space:nowrap}.edt-state-icon,.edt-flag-icon{display:inline-flex;align-items:center;justify-content:center;width:16px;height:16px;border-radius:4px;font-size:9px;line-height:1}.edt-state-icon.edt-status-active{background:#163a5f;color:#fff}.edt-state-icon.edt-status-expired{background:#777;color:#fff}.edt-state-icon.edt-status-redemption{background:#d8741f;color:#fff}.edt-state-icon.edt-status-pending{background:#fcf8e3;color:#8a6d3b;border:1px solid #faebcc}.edt-state-icon.edt-status-available{background:#3c763d;color:#fff}.edt-flag-renew{background:#163a5f;color:#fff}.edt-flag-watch{background:#d8741f;color:#fff}.edt-status-summary .edt-icon-cluster{margin-right:1px}.edt-domain-table th:nth-child(14),.edt-domain-table td:nth-child(14){width:58px!important;min-width:58px!important}
.edt-audit-actions{display:flex;align-items:center;gap:7px;flex-wrap:wrap;margin:0 0 10px}.edt-audit-actions form{display:inline-block;margin:0}.edt-audit-note{margin:0 0 10px}.edt-audit-table td{vertical-align:middle}.edt-audit-invoices{white-space:nowrap}.edt-audit-invoices a{display:inline-block;margin:0 4px 2px 0;font-weight:600}.edt-audit-domains{min-width:180px;max-width:360px;white-space:normal;overflow-wrap:anywhere}.edt-orphan-invoice-table th:nth-child(8),.edt-orphan-invoice-table td:nth-child(8){min-width:180px}.edt-audit-actions [data-edt-audit-scan-button] .fa-spinner{margin-right:4px}.edt-neo-terminal-actions,.edt-orphan-invoice-actions{display:flex;align-items:center;gap:7px;flex-wrap:wrap}.edt-neo-terminal-actions [data-edt-neo-terminal-selected-count],.edt-orphan-invoice-actions [data-edt-orphan-invoice-selected-count]{color:#666;font-weight:600}.edt-neo-terminal-actions [data-edt-neo-terminal-bulk-action] .fa-spinner,.edt-orphan-invoice-actions [data-edt-orphan-invoice-cancel-button] .fa-spinner{margin-right:4px}
@media(max-width:900px){.edt-recon-grid{grid-template-columns:1fr}.edt-recon-notes,.edt-recon-submit{grid-column:1}.edt-search{flex:1 1 100%;max-width:none;margin-left:0}.edt-pager{margin-left:0}.edt-pager [data-edt-status]{min-width:0;text-align:left}}
</style>
HTML;
}

function edt_scripts(): string
{
    return <<<'HTML'
<script>
(function(){
    function initTable(root){
        if(!root || root.dataset.edtReady==='1'){return;}
        root.dataset.edtReady='1';
        var panel=root.closest('.edt-panel');
        var search=panel ? panel.querySelector('[data-edt-search]') : null;
        var statusWrap=panel ? panel.querySelector('[data-edt-status-filter]') : null;
        var statusBoxes=statusWrap ? Array.prototype.slice.call(statusWrap.querySelectorAll('[data-edt-status-choice]')) : [];
        var statusToggle=statusWrap ? statusWrap.querySelector('[data-edt-status-toggle]') : null;
        var statusMenu=statusWrap ? statusWrap.querySelector('[data-edt-status-menu]') : null;
        var statusLabel=statusWrap ? statusWrap.querySelector('[data-edt-status-label]') : null;
        var statusAll=statusWrap ? statusWrap.querySelector('[data-edt-status-all]') : null;
        var statusDefault=statusWrap ? statusWrap.querySelector('[data-edt-status-default]') : null;
        var sizes=Array.prototype.slice.call(root.querySelectorAll('[data-edt-page-size]'));
        var size=sizes.length?sizes[0]:null;
        var rows=Array.prototype.slice.call(root.querySelectorAll('[data-edt-row]'));
        var tbody=root.querySelector('tbody');
        var prevButtons=Array.prototype.slice.call(root.querySelectorAll('[data-edt-prev]'));
        var nextButtons=Array.prototype.slice.call(root.querySelectorAll('[data-edt-next]'));
        var labels=Array.prototype.slice.call(root.querySelectorAll('[data-edt-status]'));
        var noMatch=root.querySelector('[data-edt-no-match]') || (panel ? panel.querySelector('[data-edt-no-match]') : null);
        var sortHeaders=Array.prototype.slice.call(root.querySelectorAll('[data-edt-sort-heading]'));
        var page=1;
        rows.forEach(function(row,index){row._edtOriginalIndex=index;});

        var pageSizeStorageKey=(String(root.id||'')==='edt-expiring-table'?'edt-page-size-v2:':'edt-page-size:')+String(root.id||'table');
        if(size){
            try {
                var savedPageSize=window.sessionStorage?sessionStorage.getItem(pageSizeStorageKey):'';
                if(savedPageSize && Array.prototype.some.call(size.options,function(option){return String(option.value||option.text)==savedPageSize;})){
                    sizes.forEach(function(select){select.value=savedPageSize;});
                }
            } catch(e) {}
        }
        var bulkPageStorageKey='edt-bulk-page:'+String(root.id||'table');
        try {
            var savedBulkPage=window.sessionStorage?sessionStorage.getItem(bulkPageStorageKey):'';
            if(savedBulkPage && parseInt(savedBulkPage,10)>0){page=parseInt(savedBulkPage,10);}
            if(window.sessionStorage){sessionStorage.removeItem(bulkPageStorageKey);}
        } catch(e) {}
        var statusSelectionStorageKey='edt-status-selection:'+String(root.id||'table');
        var persistStatusSelection=String(root.id||'')==='edt-expiring-table';
        try {
            var savedStatusSelection=window.sessionStorage?sessionStorage.getItem(statusSelectionStorageKey):null;
            if(savedStatusSelection!==null){
                var decodedStatusSelection=JSON.parse(savedStatusSelection);
                if(Array.isArray(decodedStatusSelection)){
                    statusBoxes.forEach(function(box){box.checked=decodedStatusSelection.indexOf(String(box.value||'').toLowerCase())!==-1;});
                }
                if(window.sessionStorage&&!persistStatusSelection){sessionStorage.removeItem(statusSelectionStorageKey);}
            }
        } catch(e) {}
        function perPage(){if(!size){return 999999;}var v=size.value||'25';return v==='all'?999999:Math.max(1,parseInt(v,10)||25);}
        function selectedStatuses(){return statusBoxes.filter(function(box){return box.checked;}).map(function(box){return String(box.value||'').toLowerCase();});}
        function saveStatusSelection(){
            if(!persistStatusSelection){return;}
            try{if(window.sessionStorage){sessionStorage.setItem(statusSelectionStorageKey,JSON.stringify(selectedStatuses()));}}catch(e){}
        }
        function updateStatusLabel(){
            if(!statusLabel){return;}
            var selected=statusBoxes.filter(function(box){return box.checked;});
            if(selected.length===statusBoxes.length){statusLabel.textContent='All';return;}
            if(selected.length===0){statusLabel.textContent='None';return;}
            var names=selected.map(function(box){var span=box.parentNode?box.parentNode.querySelector('span'):null;return span?span.textContent:String(box.value||'');});
            statusLabel.textContent=names.length<=2?names.join(' + '):(names.length+' selected');
        }
        function matches(row){
            var q=search?String(search.value||'').toLowerCase().trim():'';
            if(statusBoxes.length){var selected=selectedStatuses();if(selected.indexOf(String(row.getAttribute('data-status')||'').toLowerCase())===-1){return false;}}
            if(q && String(row.getAttribute('data-search')||'').indexOf(q)===-1){return false;}
            return true;
        }
        function render(reset){
            if(reset){page=1;}
            var matched=rows.filter(matches), pp=perPage(), pages=Math.max(1,Math.ceil(matched.length/pp));
            if(page>pages){page=pages;}
            if(page<1){page=1;}
            root.setAttribute('data-edt-current-page',String(page));
            var start=(page-1)*pp, end=Math.min(start+pp,matched.length);
            rows.forEach(function(r){r.style.display='none';});
            matched.slice(start,end).forEach(function(r){r.style.display='';});
            var statusText=matched.length?((start+1)+'–'+end+' of '+matched.length):'0 of 0';
            labels.forEach(function(el){el.textContent=statusText;});
            prevButtons.forEach(function(btn){btn.disabled=page<=1||matched.length===0;});
            nextButtons.forEach(function(btn){btn.disabled=page>=pages||matched.length===0;});
            if(noMatch){noMatch.style.display=matched.length===0?'':'none';}
        }
        function cellValue(row,index,type){
            var cell=row.cells && row.cells[index] ? row.cells[index] : null;
            if(!cell){return null;}
            var select=cell.querySelector?cell.querySelector('[data-edt-rating-select]'):null;
            var raw=select?String(select.value||''):String(cell.getAttribute('data-edt-sort-value')||'');
            if(raw===''){return null;}
            if(type==='number'){var num=parseFloat(raw);return isNaN(num)?null:num;}
            return raw.toLowerCase();
        }
        function compareNatural(a,b){return String(a).localeCompare(String(b),undefined,{numeric:true,sensitivity:'base'});}
        function csvEscape(value){var text=String(value==null?'':value);return /[\",\r\n]/.test(text)?'\"'+text.replace(/\"/g,'\"\"')+'\"':text;}
        function exportCellValue(cell){
            if(!cell){return '';}
            var explicit=cell.getAttribute('data-edt-export-value');
            if(explicit!==null){return String(explicit);}
            var select=cell.querySelector?cell.querySelector('select'):null;
            if(select){return String(select.value||'');}
            var button=cell.querySelector?cell.querySelector('button'):null;
            if(button){return String(button.textContent||'').trim();}
            return String(cell.textContent||'').replace(/\s+/g,' ').trim();
        }
        function exportCsv(button){
            var table=root.querySelector('table');
            if(!table){return;}
            var splitStatus=!!table.querySelector('tbody td.edt-status-cell');
            var headerCells=Array.prototype.slice.call(table.querySelectorAll('thead th'));
            var headers=[];
            headerCells.forEach(function(th,index){
                if(th.classList.contains('edt-select-col')){return;}
                var bodyStatusCell=splitStatus?table.querySelector('tbody tr td:nth-child('+(index+1)+').edt-status-cell'):null;
                if(bodyStatusCell){
                    headers.push('Watch List','Auto Renew','Status');
                    return;
                }
                headers.push(String(th.textContent||'').replace(/\s+/g,' ').trim());
            });
            var matched=rows.filter(matches);
            var lines=[headers.map(csvEscape).join(',')];
            matched.forEach(function(row){
                var values=[];
                Array.prototype.slice.call(row.cells||[]).forEach(function(cell,index){
                    var th=headerCells[index];
                    if(th&&th.classList.contains('edt-select-col')){return;}
                    if(splitStatus&&cell.classList.contains('edt-status-cell')){
                        values.push(String(cell.getAttribute('data-edt-export-watch')||'No'));
                        values.push(String(cell.getAttribute('data-edt-export-autorenew')||'Off'));
                        values.push(String(cell.getAttribute('data-edt-export-lifecycle')||''));
                        return;
                    }
                    values.push(exportCellValue(cell));
                });
                lines.push(values.map(csvEscape).join(','));
            });
            var blob=new Blob(['\ufeff'+lines.join('\r\n')],{type:'text/csv;charset=utf-8'});
            var url=URL.createObjectURL(blob);var a=document.createElement('a');
            var name=String((button&&button.getAttribute('data-edt-export-name'))||root.getAttribute('data-edt-export-table-name')||'domains');
            var d=new Date();var stamp=d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0');
            a.href=url;a.download=name+'-'+stamp+'.csv';document.body.appendChild(a);a.click();document.body.removeChild(a);window.setTimeout(function(){URL.revokeObjectURL(url);},1000);
        }
        function sortBy(header,direction){
            if(!tbody){return;}
            var index=parseInt(header.getAttribute('data-edt-sort-index')||'0',10)||0;
            var type=String(header.getAttribute('data-edt-sort-type')||'text');
            var mult=direction==='desc'?-1:1;
            rows.sort(function(a,b){
                var av=cellValue(a,index,type), bv=cellValue(b,index,type);
                if(av===null && bv===null){return (a._edtOriginalIndex-b._edtOriginalIndex);}
                if(av===null){return 1;}
                if(bv===null){return -1;}
                var cmp;
                if(type==='number'){cmp=av===bv?0:(av<bv?-1:1);}else{cmp=compareNatural(av,bv);}
                if(cmp===0){cmp=a._edtOriginalIndex-b._edtOriginalIndex;}
                return cmp*mult;
            });
            rows.forEach(function(row){tbody.appendChild(row);});
            sortHeaders.forEach(function(h){h.classList.remove('sorting_asc','sorting_desc');h.classList.add('sorting');var b=h.querySelector('[data-edt-sort-button]');if(b){b.setAttribute('data-direction','none');b.setAttribute('aria-sort','none');}});
            header.classList.remove('sorting');header.classList.add(direction==='asc'?'sorting_asc':'sorting_desc');
            var button=header.querySelector('[data-edt-sort-button]');if(button){button.setAttribute('data-direction',direction);button.setAttribute('aria-sort',direction==='asc'?'ascending':'descending');}
            render(true);
        }

        if(search){search.addEventListener('input',function(){render(true);});}
        sizes.forEach(function(select){select.addEventListener('change',function(){
            var value=String(select.value||'25');
            sizes.forEach(function(other){if(other!==select){other.value=value;}});
            if(size){size.value=value;}
            try{if(window.sessionStorage){sessionStorage.setItem(pageSizeStorageKey,value);}}catch(e){}
            render(true);
        });});
        prevButtons.forEach(function(btn){btn.addEventListener('click',function(){if(page>1){page--;render(false);}});});
        nextButtons.forEach(function(btn){btn.addEventListener('click',function(){page++;render(false);});});
        statusBoxes.forEach(function(box){box.addEventListener('change',function(){saveStatusSelection();updateStatusLabel();render(true);});});
        if(statusToggle){statusToggle.addEventListener('click',function(e){e.preventDefault();e.stopPropagation();var open=statusWrap.classList.toggle('edt-open');statusToggle.setAttribute('aria-expanded',open?'true':'false');});}
        if(statusMenu){statusMenu.addEventListener('click',function(e){e.stopPropagation();});}
        if(statusAll){statusAll.addEventListener('click',function(){var allSelected=statusBoxes.length>0&&statusBoxes.every(function(box){return box.checked;});statusBoxes.forEach(function(box){box.checked=!allSelected;});saveStatusSelection();updateStatusLabel();render(true);});}
        if(statusDefault){statusDefault.addEventListener('click',function(){statusBoxes.forEach(function(box){box.checked=box.getAttribute('data-edt-default-checked')==='1';});saveStatusSelection();updateStatusLabel();render(true);});}
        sortHeaders.forEach(function(header){
            header.addEventListener('click',function(e){
                if(e){e.preventDefault();}
                var button=header.querySelector('[data-edt-sort-button]');
                var current=button?String(button.getAttribute('data-direction')||'none'):'none';
                sortBy(header,current==='asc'?'desc':'asc');
            });
        });
        function syncRatingVisual(select){
            var cell=select.closest('td');if(cell){cell.setAttribute('data-edt-sort-value',String(select.value||''));cell.setAttribute('data-edt-export-value',String(select.value||''));}
            var row=select.closest?select.closest('tr'):null;var statusCell=row?row.querySelector('.edt-status-cell'):null;var cluster=statusCell?statusCell.querySelector('.edt-icon-cluster'):null;
            if(cluster){
                var isWatch=String(select.value||'')==='1'||String(select.value||'')==='2';var watch=cluster.querySelector('.edt-flag-watch');
                if(isWatch&&!watch){watch=document.createElement('span');watch.className='edt-flag-icon edt-flag-watch';watch.title='Watch List';watch.setAttribute('aria-label','Watch List');watch.innerHTML='<i class="fas fa-star" aria-hidden="true"></i>';cluster.insertBefore(watch,cluster.firstChild);}
                if(!isWatch&&watch){watch.parentNode.removeChild(watch);}
                var state=cluster.querySelector('.edt-state-icon');var parts=[];if(isWatch){parts.push('Watch List');}if(cluster.querySelector('.edt-flag-renew')){parts.push('Auto Renew');}parts.push(state?String(state.getAttribute('title')||'Status'):'Status');statusCell.setAttribute('data-edt-export-value',parts.join(' | '));
            }
        }
        Array.prototype.forEach.call(root.querySelectorAll('[data-edt-rating-select]'),function(select){select.addEventListener('change',function(){
            var previous=String(select.getAttribute('data-edt-saved-value')||'');
            var domainId=String(select.getAttribute('data-edt-domain-id')||'');
            var form=select.closest?select.closest('form'):null;
            syncRatingVisual(select);
            if(!form||!domainId){return;}
            var token=form.querySelector('input[name="token"]');var tab=form.querySelector('input[name="edt_tab"]');var windowInput=form.querySelector('input[name="edt_window"]');
            var data=new FormData();
            if(token){data.append('token',String(token.value||''));}
            data.append('edt_action','save_ratings');data.append('edt_ajax','rating');
            if(tab){data.append('edt_tab',String(tab.value||''));}
            if(windowInput){data.append('edt_window',String(windowInput.value||''));}
            data.append('rating['+domainId+']',String(select.value||''));
            select.disabled=true;select.classList.remove('edt-rating-saved','edt-rating-error');select.classList.add('edt-rating-saving');
            fetch(form.action,{method:'POST',body:data,credentials:'same-origin'}).then(function(response){if(!response.ok){throw new Error('HTTP '+response.status);}return response.text();}).then(function(text){
                var doc=new DOMParser().parseFromString(text,'text/html');var result=doc.querySelector('[data-edt-rating-ajax-result]');
                if(!result||result.getAttribute('data-ok')!=='1'){throw new Error(result?String(result.getAttribute('data-message')||'Rating save failed.'):'Rating save failed.');}
                select.setAttribute('data-edt-saved-value',String(select.value||''));select.classList.remove('edt-rating-saving');select.classList.add('edt-rating-saved');
                window.setTimeout(function(){select.classList.remove('edt-rating-saved');},700);
            }).catch(function(error){
                select.value=previous;syncRatingVisual(select);select.classList.remove('edt-rating-saving');select.classList.add('edt-rating-error');
                window.setTimeout(function(){select.classList.remove('edt-rating-error');},1400);
                window.alert('Could not save rating: '+String(error&&error.message?error.message:error));
            }).finally(function(){select.disabled=false;});
        });});
        var exportButtons=panel?Array.prototype.slice.call(panel.querySelectorAll('[data-edt-export-csv]')):[];
        exportButtons.forEach(function(button){button.addEventListener('click',function(){exportCsv(button);});});
        updateStatusLabel();
        render(false);
    }

    Array.prototype.forEach.call(document.querySelectorAll('[data-edt-window-control]'),function(form){
        var input=form.querySelector('[data-edt-window-days]');
        if(!input){return;}
        var initial=String(input.value||'');
        input.addEventListener('change',function(){
            var value=parseInt(String(input.value||''),10);
            if(!value||value<1||value>365){input.value=initial;return;}
            if(String(value)===initial){return;}
            form.classList.add('edt-window-loading');input.disabled=true;
            // A disabled select is not submitted, so mirror its chosen value.
            var hidden=document.createElement('input');hidden.type='hidden';hidden.name='edt_window';hidden.value=String(value);form.appendChild(hidden);
            if(typeof form.requestSubmit==='function'){form.requestSubmit();}else{form.submit();}
        });
    });

    Array.prototype.forEach.call(document.querySelectorAll('[data-edt-table-root]'),initTable);

    // Individual NEO lookups reload the addon page. Remember the clicked
    // domain so the refreshed page returns to the page containing that row and
    // scrolls it back into view instead of jumping to the top of the table.
    (function restoreNeoFocus(){
        var focusId='';
        try{if(window.sessionStorage){focusId=String(sessionStorage.getItem('edt-neo-focus-domain')||'');}}catch(e){}
        if(!focusId){return;}
        var row=document.querySelector('tr[data-edt-neo-domain-id="'+focusId+'"]');
        if(!row){return;}
        var root=row.closest?row.closest('[data-edt-table-root]'):null;
        if(root){
            var size=(root.closest('.edt-panel')||document).querySelector('[data-edt-page-size]');
            var perPage=999999;
            if(size){var raw=String(size.value||'25');perPage=raw==='all'?999999:Math.max(1,parseInt(raw,10)||25);}
            var allRows=Array.prototype.slice.call(root.querySelectorAll('tbody tr[data-edt-row]'));
            var visibleCandidates=allRows.filter(function(r){
                var status=String(r.getAttribute('data-status')||'').toLowerCase();
                var panel=root.closest('.edt-panel');
                var boxes=panel?Array.prototype.slice.call(panel.querySelectorAll('[data-edt-status-choice]')):[];
                if(!boxes.length){return true;}
                var selected=boxes.filter(function(b){return b.checked;}).map(function(b){return String(b.value||'').toLowerCase();});
                return selected.indexOf(status)!==-1;
            });
            if(visibleCandidates.indexOf(row)===-1){
                var panel=root.closest('.edt-panel');
                var rowStatus=String(row.getAttribute('data-status')||'').toLowerCase();
                var statusBox=panel?panel.querySelector('[data-edt-status-choice][value="'+rowStatus+'"]'):null;
                if(statusBox){statusBox.checked=true;visibleCandidates=allRows.filter(function(r){var st=String(r.getAttribute('data-status')||'').toLowerCase();var boxes=Array.prototype.slice.call(panel.querySelectorAll('[data-edt-status-choice]'));var selected=boxes.filter(function(b){return b.checked;}).map(function(b){return String(b.value||'').toLowerCase();});return selected.indexOf(st)!==-1;});}
            }
            var index=visibleCandidates.indexOf(row);
            if(index>=0 && perPage<999999){
                var targetPage=Math.floor(index/perPage)+1;
                var prev=root.querySelector('[data-edt-prev]');
                for(var p=1;p<targetPage;p++){if(prev){var next=root.querySelector('[data-edt-next]');if(next&&!next.disabled){next.click();}}}
            }
        }
        window.setTimeout(function(){
            row.scrollIntoView({behavior:'auto',block:'center'});
            row.classList.add('edt-neo-focus-row');
            window.setTimeout(function(){row.classList.remove('edt-neo-focus-row');},1400);
            try{if(window.sessionStorage){sessionStorage.removeItem('edt-neo-focus-domain');}}catch(e){}
        },50);
    })();

    Array.prototype.forEach.call(document.querySelectorAll('[data-edt-neo-refresh]'),function(button){
        button.addEventListener('click',function(){
            var formId=String(button.getAttribute('data-edt-neo-form-id')||'');
            var domainId=String(button.getAttribute('data-edt-domain-id')||'');
            var form=formId?document.getElementById(formId):null;
            var input=form?form.querySelector('input[name="neo_domain_ids"]'):null;
            if(!form||!input||!domainId){return;}
            input.value=domainId;
            try{
                if(window.sessionStorage){
                    sessionStorage.setItem('edt-neo-focus-domain',domainId);
                    var root=button.closest?button.closest('[data-edt-table-root]'):null;
                    if(root){
                        var panel=root.closest?root.closest('.edt-panel'):null;
                        var boxes=panel?Array.prototype.slice.call(panel.querySelectorAll('[data-edt-status-choice]')):[];
                        var selected=boxes.filter(function(box){return box.checked;}).map(function(box){return String(box.value||'').toLowerCase();});
                        sessionStorage.setItem('edt-status-selection:'+String(root.id||'table'),JSON.stringify(selected));
                    }
                }
            }catch(e){}
            button.disabled=true;
            button.innerHTML='<i class="fas fa-spinner fa-spin" aria-hidden="true"></i>';
            form.submit();
        });
    });

    Array.prototype.forEach.call(document.querySelectorAll('[data-edt-neo-bulk-trigger]'),function(button){
        button.addEventListener('click',function(){
            var formId=String(button.getAttribute('data-edt-neo-bulk-form-id')||'');
            var form=formId?document.getElementById(formId):null;
            if(!form){return;}
            if(typeof form.requestSubmit==='function'){form.requestSubmit();}else{form.submit();}
        });
    });

    Array.prototype.forEach.call(document.querySelectorAll('[data-edt-neo-bulk]'),function(form){
        form.addEventListener('submit',function(){
            var sourceId=String(form.getAttribute('data-edt-neo-source')||'');
            var root=sourceId?document.getElementById(sourceId):null;
            var input=form.querySelector('input[name="neo_domain_ids"]');
            if(!root||!input){return;}
            var ids=[];
            Array.prototype.forEach.call(root.querySelectorAll('tbody tr[data-edt-neo-domain-id]'),function(row){
                var id=String(row.getAttribute('data-edt-neo-domain-id')||'');
                if(id&&ids.indexOf(id)===-1){ids.push(id);}
            });
            if(ids.length){input.value=ids.join(',');}
            try{
                if(window.sessionStorage){
                    sessionStorage.setItem('edt-bulk-page:'+String(sourceId||'table'),String(root.getAttribute('data-edt-current-page')||'1'));
                    var panel=root.closest?root.closest('.edt-panel'):null;
                    var boxes=panel?Array.prototype.slice.call(panel.querySelectorAll('[data-edt-status-choice]')):[];
                    var selected=boxes.filter(function(box){return box.checked;}).map(function(box){return String(box.value||'').toLowerCase();});
                    sessionStorage.setItem('edt-status-selection:'+String(root.id||'table'),JSON.stringify(selected));
                }
            }catch(e){}
            var button=form.querySelector('[data-edt-neo-bulk-button]');
            if(button){
                button.disabled=true;
                button.innerHTML='<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> NEO';
            }
            if(form.id){
                Array.prototype.forEach.call(document.querySelectorAll('[data-edt-neo-bulk-trigger][data-edt-neo-bulk-form-id="'+form.id+'"]'),function(trigger){
                    trigger.disabled=true;
                    trigger.innerHTML='<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> NEO';
                });
            }
        });
    });

    (function(){
        var root=document.getElementById('edt-neo-terminal-table');
        var form=document.querySelector('[data-edt-neo-terminal-action-form]');
        var actionButtons=root?Array.prototype.slice.call(root.querySelectorAll('[data-edt-neo-terminal-bulk-action]')):[];
        var master=root?root.querySelector('[data-edt-neo-terminal-select-all]'):null;
        var countEls=root?Array.prototype.slice.call(root.querySelectorAll('[data-edt-neo-terminal-selected-count]')):[];
        var panel=root&&root.closest?root.closest('.edt-panel'):null;
        var search=panel?panel.querySelector('[data-edt-search]'):null;
        if(!root||!form||!actionButtons.length){return;}

        function boxes(){return Array.prototype.slice.call(root.querySelectorAll('[data-edt-neo-terminal-select]'));}
        function checked(){return boxes().filter(function(box){return box.checked&&!box.disabled;});}
        function visibleEligible(){return boxes().filter(function(box){var row=box.closest?box.closest('tr'):null;return !box.disabled&&row&&row.style.display!=='none';});}
        function update(){
            var selected=checked();
            actionButtons.forEach(function(button){button.disabled=selected.length===0;});
            countEls.forEach(function(countEl){countEl.textContent=selected.length+' selected';});
            if(master){
                var visible=visibleEligible();
                var visibleChecked=visible.filter(function(box){return box.checked;});
                master.checked=visible.length>0&&visibleChecked.length===visible.length;
                master.indeterminate=visibleChecked.length>0&&visibleChecked.length<visible.length;
                master.disabled=visible.length===0;
            }
        }

        boxes().forEach(function(box){box.addEventListener('change',update);});
        if(master){master.addEventListener('change',function(){visibleEligible().forEach(function(box){box.checked=master.checked;});update();});}
        actionButtons.forEach(function(button){button.addEventListener('click',function(){
            var selected=checked();
            if(!selected.length){update();return;}
            var operation=String(button.getAttribute('data-edt-neo-terminal-bulk-action')||'');
            if(operation!=='cancel'&&operation!=='delete'){return;}
            var count=selected.length;
            var message=operation==='delete'
                ? 'Permanently delete the '+count+' selected domain'+(count===1?'':'s')+' from WHMCS? Each domain will be checked against its current NEO order first. Disable Auto Renew will be set when needed, then WHMCS will perform and verify its native domain-invoice cleanup. The domain will not be deleted if cleanup cannot be verified. No registrar command will be sent.'
                : 'Cancel the '+count+' selected domain'+(count===1?'':'s')+' in WHMCS? Each domain will be checked against its current NEO order first. Disable Auto Renew will be set when needed, then WHMCS will perform and verify its native domain-invoice cleanup. The domain will not be cancelled if cleanup cannot be verified. No registrar command will be sent.';
            if(!window.confirm(message)){return;}
            var idsInput=form.querySelector('input[name="neo_domain_ids"]');
            var operationInput=form.querySelector('input[name="neo_domain_operation"]');
            if(!idsInput||!operationInput){return;}
            idsInput.value=selected.map(function(box){return String(box.value||'');}).filter(Boolean).join(',');
            operationInput.value=operation;
            actionButtons.forEach(function(actionButton){actionButton.disabled=true;});
            button.innerHTML='<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Working...';
            form.submit();
        });});
        ['click','input','change'].forEach(function(eventName){root.addEventListener(eventName,function(){window.setTimeout(update,0);});});
        if(search){search.addEventListener('input',function(){window.setTimeout(update,0);});}
        update();
    })();

    (function(){
        var root=document.getElementById('edt-orphan-invoice-table');
        var form=document.querySelector('[data-edt-orphan-invoice-cancel-form]');
        var cancelButtons=root?Array.prototype.slice.call(root.querySelectorAll('[data-edt-orphan-invoice-cancel-button]')):[];
        var master=root?root.querySelector('[data-edt-orphan-invoice-select-all]'):null;
        var countEls=root?Array.prototype.slice.call(root.querySelectorAll('[data-edt-orphan-invoice-selected-count]')):[];
        var panel=root&&root.closest?root.closest('.edt-panel'):null;
        var search=panel?panel.querySelector('[data-edt-search]'):null;
        if(!root||!form||!cancelButtons.length){return;}

        function boxes(){return Array.prototype.slice.call(root.querySelectorAll('[data-edt-orphan-invoice-select]'));}
        function checked(){return boxes().filter(function(box){return box.checked&&!box.disabled;});}
        function visibleEligible(){return boxes().filter(function(box){var row=box.closest?box.closest('tr'):null;return !box.disabled&&row&&row.style.display!=='none';});}
        function update(){
            var selected=checked();
            cancelButtons.forEach(function(button){button.disabled=selected.length===0;});
            countEls.forEach(function(countEl){countEl.textContent=selected.length+' selected';});
            if(master){
                var visible=visibleEligible();
                var visibleChecked=visible.filter(function(box){return box.checked;});
                master.checked=visible.length>0&&visibleChecked.length===visible.length;
                master.indeterminate=visibleChecked.length>0&&visibleChecked.length<visible.length;
                master.disabled=visible.length===0;
            }
        }

        boxes().forEach(function(box){box.addEventListener('change',update);});
        if(master){master.addEventListener('change',function(){visibleEligible().forEach(function(box){box.checked=master.checked;});update();});}
        cancelButtons.forEach(function(cancelButton){cancelButton.addEventListener('click',function(){
            var selected=checked();
            if(!selected.length){update();return;}
            var count=selected.length;
            var message='Cancel the '+count+' selected orphaned invoice'+(count===1?'':'s')+' in WHMCS? Each invoice will be rechecked and must still contain only orphaned domain line items. Mixed invoices will be blocked. This changes the invoice status to Cancelled; it does not delete the invoice or its line items.';
            if(!window.confirm(message)){return;}
            var input=form.querySelector('input[name="orphan_invoice_ids"]');
            if(!input){return;}
            input.value=selected.map(function(box){return String(box.value||'');}).filter(Boolean).join(',');
            cancelButtons.forEach(function(button){button.disabled=true;});
            cancelButton.innerHTML='<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Working...';
            form.submit();
        });});
        ['click','input','change'].forEach(function(eventName){root.addEventListener(eventName,function(){window.setTimeout(update,0);});});
        if(search){search.addEventListener('input',function(){window.setTimeout(update,0);});}
        update();
    })();

    (function(){
        var root=document.getElementById('edt-expired-cleanup-table');
        var form=document.querySelector('[data-edt-expired-delete-form]');
        var deleteButtons=root?Array.prototype.slice.call(root.querySelectorAll('[data-edt-expired-delete-button]')):[];
        var master=root?root.querySelector('[data-edt-expired-select-all]'):null;
        var countEls=root?Array.prototype.slice.call(root.querySelectorAll('[data-edt-expired-selected-count]')):[];
        if(!root||!form||!deleteButtons.length){return;}

        function boxes(){return Array.prototype.slice.call(root.querySelectorAll('[data-edt-expired-select]'));}
        function checked(){return boxes().filter(function(box){return box.checked&&!box.disabled;});}
        function visibleEligible(){return boxes().filter(function(box){var row=box.closest?box.closest('tr'):null;return !box.disabled&&row&&row.style.display!=='none';});}
        function update(){
            var selected=checked();
            deleteButtons.forEach(function(button){button.disabled=selected.length===0;});
            countEls.forEach(function(countEl){countEl.textContent=selected.length+' selected';});
            if(master){
                var visible=visibleEligible();
                var visibleChecked=visible.filter(function(box){return box.checked;});
                master.checked=visible.length>0&&visibleChecked.length===visible.length;
                master.indeterminate=visibleChecked.length>0&&visibleChecked.length<visible.length;
                master.disabled=visible.length===0;
            }
        }

        boxes().forEach(function(box){box.addEventListener('change',update);});
        if(master){master.addEventListener('change',function(){visibleEligible().forEach(function(box){box.checked=master.checked;});update();});}
        deleteButtons.forEach(function(deleteButton){deleteButton.addEventListener('click',function(){
            var selected=checked();
            if(!selected.length){update();return;}
            var input=form.querySelector('input[name="expired_domain_ids"]');
            if(!input){return;}
            input.value=selected.map(function(box){return String(box.value||'');}).filter(Boolean).join(',');
            if(typeof form.requestSubmit==='function'){form.requestSubmit();}else{form.submit();}
        });});
        ['click','input','change'].forEach(function(eventName){root.addEventListener(eventName,function(){window.setTimeout(update,0);});});
        update();
    })();

    document.addEventListener('click',function(){Array.prototype.forEach.call(document.querySelectorAll('.edt-status-filter-wrap.edt-open'),function(wrap){wrap.classList.remove('edt-open');var toggle=wrap.querySelector('[data-edt-status-toggle]');if(toggle){toggle.setAttribute('aria-expanded','false');}});});

    Array.prototype.forEach.call(document.querySelectorAll('[data-edt-confirm]'),function(form){
        form.addEventListener('submit',function(e){
            var message=form.getAttribute('data-edt-confirm')||'Are you sure?';
            if(!window.confirm(message)){e.preventDefault();}
        });
    });

    Array.prototype.forEach.call(document.querySelectorAll('[data-edt-audit-scan-form]'),function(form){
        form.addEventListener('submit',function(e){
            var restartMessage=form.getAttribute('data-edt-audit-restart-confirm');
            if(restartMessage&&!window.confirm(restartMessage)){e.preventDefault();return;}
            var button=form.querySelector('[data-edt-audit-scan-button]');
            if(button){
                button.disabled=true;
                button.innerHTML='<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Working...';
            }
        });
    });

    var tabs=document.querySelector('.edt-tabs');
    if(tabs){
        tabs.addEventListener('click',function(e){
            var a=e.target.closest?e.target.closest('a'):null;
            if(!a||e.defaultPrevented||e.ctrlKey||e.metaKey||e.shiftKey||e.altKey){return;}
            if(typeof e.button==='number'&&e.button!==0){return;}
            var href=a.getAttribute('href');if(!href){return;}
            e.preventDefault();
            if(!a.querySelector('.edt-tab-spinner')){
                var i=document.createElement('i');i.className='fas fa-spinner fa-spin edt-tab-spinner';i.setAttribute('aria-hidden','true');a.insertBefore(i,a.firstChild);
            }
            a.classList.add('edt-loading');
            if(window.requestAnimationFrame){window.requestAnimationFrame(function(){window.location.href=href;});}else{window.location.href=href;}
        });
    }
})();
</script>
HTML;
}
