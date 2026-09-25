<?php

declare(strict_types=1);

namespace InvoiceFix\Module;

use Throwable;
use WHMCS\Database\Capsule;

if (!defined('WHMCS')) {
    exit('This file cannot be accessed directly');
}

require_once __DIR__ . '/InvoiceFixService.php';
require_once __DIR__ . '/InvoiceFixUpgrade.php';

/**
 * Read-only diagnostics for the InvoiceFix installation and audit links.
 */
final class InvoiceFixHealth
{
    public const STATUS_PASS = 'pass';
    public const STATUS_WARNING = 'warning';
    public const STATUS_FAIL = 'fail';
    public const STATUS_INFO = 'info';

    /** @var string[] */
    private const REQUIRED_LOG_COLUMNS = [
        'id',
        'source_invoice_id',
        'draft_invoice_id',
        'admin_id',
        'source_status',
        'source_total',
        'result',
        'message',
        'internal_note',
        'internal_note_admin_id',
        'internal_note_updated_at',
        'follow_up_admin_id',
        'follow_up_due_date',
        'follow_up_updated_by_admin_id',
        'follow_up_updated_at',
        'created_at',
    ];

    /** @var string[] */
    private const REQUIRED_FILES = [
        'invoicefix.php',
        'hooks.php',
        'assets/invoicefix.js',
        'lang/english.php',
        'lib/InvoiceFixService.php',
        'lib/InvoiceFixAging.php',
        'lib/InvoiceFixText.php',
        'lib/InvoiceFixUpgrade.php',
        'lib/InvoiceFixWorkQueue.php',
        'lib/InvoiceFixComparison.php',
        'lib/InvoiceFixDetails.php',
        'lib/InvoiceFixNote.php',
        'lib/InvoiceFixFollowUp.php',
        'lib/InvoiceFixExport.php',
        'lib/InvoiceFixQueueExport.php',
        'lib/InvoiceFixHealth.php',
    ];

    /**
     * @param array{admin_id:int,username:string,role_id:int,permissions:string[]}|null $admin
     * @return array{generated_at:string,checks:array<int,array{key:string,label:string,status:string,details:string}>,summary:array{pass:int,warning:int,fail:int,info:int}}
     */
    public static function run(?array $admin, string $adminError = ''): array
    {
        $checks = [];

        $checks[] = self::checkFiles();
        $checks[] = self::checkLocalApi();
        $checks[] = self::checkAdministrator($admin, $adminError);
        $checks[] = self::checkRoleAccess($admin);
        $checks[] = self::checkInvoicePermissions($admin);
        $checks[] = self::checkSchemaVersion();
        $checks[] = self::checkAuditTable();
        $checks[] = self::checkModuleSettings();
        $checks[] = self::checkTextOverrides();
        $checks[] = self::checkRelationships();
        $checks[] = self::checkClientRelationships();
        $checks[] = self::checkAuditWarnings();

        $summary = [
            self::STATUS_PASS => 0,
            self::STATUS_WARNING => 0,
            self::STATUS_FAIL => 0,
            self::STATUS_INFO => 0,
        ];

        foreach ($checks as $check) {
            $status = (string) ($check['status'] ?? self::STATUS_INFO);
            if (!array_key_exists($status, $summary)) {
                $status = self::STATUS_INFO;
            }
            $summary[$status]++;
        }

        return [
            'generated_at' => gmdate('Y-m-d H:i:s') . ' UTC',
            'checks' => $checks,
            'summary' => [
                'pass' => $summary[self::STATUS_PASS],
                'warning' => $summary[self::STATUS_WARNING],
                'fail' => $summary[self::STATUS_FAIL],
                'info' => $summary[self::STATUS_INFO],
            ],
        ];
    }

    /** @return array{key:string,label:string,status:string,details:string} */
    private static function checkFiles(): array
    {
        $root = dirname(__DIR__);
        $missing = [];

        foreach (self::REQUIRED_FILES as $relativePath) {
            $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            if (!is_file($path) || !is_readable($path)) {
                $missing[] = $relativePath;
            }
        }

        if ($missing !== []) {
            return self::result(
                'files',
                'health_check_files',
                self::STATUS_FAIL,
                InvoiceFixText::get('health_files_missing', ['files' => implode(', ', $missing)])
            );
        }

        return self::result('files', 'health_check_files', self::STATUS_PASS, InvoiceFixText::get('health_files_ok'));
    }

    /** @return array{key:string,label:string,status:string,details:string} */
    private static function checkLocalApi(): array
    {
        return function_exists('localAPI')
            ? self::result('local_api', 'health_check_local_api', self::STATUS_PASS, InvoiceFixText::get('health_local_api_ok'))
            : self::result('local_api', 'health_check_local_api', self::STATUS_FAIL, InvoiceFixText::get('health_local_api_missing'));
    }

    /** @param array{admin_id:int,username:string,role_id:int,permissions:string[]}|null $admin */
    private static function checkAdministrator(?array $admin, string $adminError): array
    {
        if ($admin === null) {
            $details = $adminError !== ''
                ? InvoiceFixText::get('health_admin_error', ['details' => $adminError])
                : InvoiceFixText::get('health_admin_missing');

            return self::result('administrator', 'health_check_administrator', self::STATUS_FAIL, $details);
        }

        return self::result(
            'administrator',
            'health_check_administrator',
            self::STATUS_PASS,
            InvoiceFixText::get('health_admin_ok', [
                'username' => (string) $admin['username'],
                'admin_id' => (int) $admin['admin_id'],
                'role_id' => (int) $admin['role_id'],
            ])
        );
    }

    /** @param array{admin_id:int,username:string,role_id:int,permissions:string[]}|null $admin */
    private static function checkRoleAccess(?array $admin): array
    {
        if ($admin === null) {
            return self::result('role_access', 'health_check_role_access', self::STATUS_FAIL, InvoiceFixText::get('health_role_unverified'));
        }

        return InvoiceFixService::roleHasModuleAccess((int) $admin['role_id'])
            ? self::result('role_access', 'health_check_role_access', self::STATUS_PASS, InvoiceFixText::get('health_role_ok'))
            : self::result('role_access', 'health_check_role_access', self::STATUS_FAIL, InvoiceFixText::get('health_role_missing'));
    }

    /** @param array{admin_id:int,username:string,role_id:int,permissions:string[]}|null $admin */
    private static function checkInvoicePermissions(?array $admin): array
    {
        if ($admin === null) {
            return self::result('invoice_permissions', 'health_check_invoice_permissions', self::STATUS_FAIL, InvoiceFixText::get('health_permissions_unverified'));
        }

        return InvoiceFixService::hasRequiredInvoicePermissions($admin['permissions'])
            ? self::result('invoice_permissions', 'health_check_invoice_permissions', self::STATUS_PASS, InvoiceFixText::get('health_permissions_ok'))
            : self::result('invoice_permissions', 'health_check_invoice_permissions', self::STATUS_FAIL, InvoiceFixText::get('health_permissions_missing'));
    }

    /** @return array{key:string,label:string,status:string,details:string} */
    private static function checkSchemaVersion(): array
    {
        try {
            $status = InvoiceFixUpgrade::status();
            $current = !empty($status['is_current']);
            $details = InvoiceFixText::get('health_schema_value', [
                'schema' => (string) ($status['schema_version'] ?? '0.0.0'),
                'module' => (string) ($status['module_version'] ?? InvoiceFixUpgrade::MODULE_VERSION),
            ]);

            return self::result(
                'schema_version',
                'health_check_schema',
                $current ? self::STATUS_PASS : self::STATUS_FAIL,
                $details
            );
        } catch (Throwable $e) {
            return self::result(
                'schema_version',
                'health_check_schema',
                self::STATUS_FAIL,
                InvoiceFixText::get('health_schema_error', ['details' => $e->getMessage()])
            );
        }
    }

    /** @return array{key:string,label:string,status:string,details:string} */
    private static function checkAuditTable(): array
    {
        try {
            $schema = Capsule::schema();
            if (!$schema->hasTable(InvoiceFixService::LOG_TABLE)) {
                return self::result('audit_table', 'health_check_audit_table', self::STATUS_FAIL, InvoiceFixText::get('health_audit_table_missing'));
            }

            $missing = [];
            foreach (self::REQUIRED_LOG_COLUMNS as $column) {
                if (!$schema->hasColumn(InvoiceFixService::LOG_TABLE, $column)) {
                    $missing[] = $column;
                }
            }

            if ($missing !== []) {
                return self::result(
                    'audit_table',
                    'health_check_audit_table',
                    self::STATUS_FAIL,
                    InvoiceFixText::get('health_audit_columns_missing', ['columns' => implode(', ', $missing)])
                );
            }

            $count = (int) Capsule::table(InvoiceFixService::LOG_TABLE)->count();
            return self::result(
                'audit_table',
                'health_check_audit_table',
                self::STATUS_PASS,
                InvoiceFixText::get('health_audit_table_ok', ['count' => $count])
            );
        } catch (Throwable $e) {
            return self::result(
                'audit_table',
                'health_check_audit_table',
                self::STATUS_FAIL,
                InvoiceFixText::get('health_audit_table_error', ['details' => $e->getMessage()])
            );
        }
    }

    /** @return array{key:string,label:string,status:string,details:string} */
    private static function checkModuleSettings(): array
    {
        try {
            $settings = Capsule::table('tbladdonmodules')
                ->where('module', InvoiceFixService::MODULE)
                ->pluck('value', 'setting')
                ->all();

            $required = ['access', InvoiceFixUpgrade::SETTING_SCHEMA_VERSION];
            $missing = [];
            foreach ($required as $name) {
                if (!array_key_exists($name, $settings) || trim((string) $settings[$name]) === '') {
                    $missing[] = $name;
                }
            }

            if ($missing !== []) {
                return self::result(
                    'module_settings',
                    'health_check_module_settings',
                    self::STATUS_WARNING,
                    InvoiceFixText::get('health_settings_missing', ['settings' => implode(', ', $missing)])
                );
            }

            return self::result(
                'module_settings',
                'health_check_module_settings',
                self::STATUS_PASS,
                InvoiceFixText::get('health_settings_ok', ['count' => count($settings)])
            );
        } catch (Throwable $e) {
            return self::result(
                'module_settings',
                'health_check_module_settings',
                self::STATUS_FAIL,
                InvoiceFixText::get('health_settings_error', ['details' => $e->getMessage()])
            );
        }
    }

    /** @return array{key:string,label:string,status:string,details:string} */
    private static function checkTextOverrides(): array
    {
        try {
            $raw = Capsule::table('tbladdonmodules')
                ->where('module', InvoiceFixService::MODULE)
                ->where('setting', InvoiceFixText::OVERRIDE_SETTING)
                ->value('value');

            if ($raw === null || trim((string) $raw) === '' || trim((string) $raw) === '[]') {
                return self::result('text_overrides', 'health_check_text_overrides', self::STATUS_PASS, InvoiceFixText::get('health_text_defaults'));
            }

            $decoded = json_decode((string) $raw, true);
            if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
                return self::result('text_overrides', 'health_check_text_overrides', self::STATUS_FAIL, InvoiceFixText::get('health_text_invalid_json'));
            }

            $editable = array_keys(InvoiceFixText::editorFields());
            $unknown = array_values(array_diff(array_map('strval', array_keys($decoded)), $editable));
            if ($unknown !== []) {
                return self::result(
                    'text_overrides',
                    'health_check_text_overrides',
                    self::STATUS_WARNING,
                    InvoiceFixText::get('health_text_unknown_keys', [
                        'count' => count($decoded),
                        'keys' => implode(', ', $unknown),
                    ])
                );
            }

            return self::result(
                'text_overrides',
                'health_check_text_overrides',
                self::STATUS_PASS,
                InvoiceFixText::get('health_text_ok', ['count' => count($decoded)])
            );
        } catch (Throwable $e) {
            return self::result(
                'text_overrides',
                'health_check_text_overrides',
                self::STATUS_FAIL,
                InvoiceFixText::get('health_text_error', ['details' => $e->getMessage()])
            );
        }
    }

    /** @return array{key:string,label:string,status:string,details:string} */
    private static function checkRelationships(): array
    {
        try {
            if (!Capsule::schema()->hasTable(InvoiceFixService::LOG_TABLE)) {
                return self::result('relationships', 'health_check_relationships', self::STATUS_FAIL, InvoiceFixText::get('health_relationships_unavailable'));
            }

            $total = (int) Capsule::table(InvoiceFixService::LOG_TABLE)->count();
            $missingOriginal = (int) Capsule::table(InvoiceFixService::LOG_TABLE . ' as l')
                ->leftJoin('tblinvoices as source', 'source.id', '=', 'l.source_invoice_id')
                ->whereNull('source.id')
                ->count();
            $missingReplacement = (int) Capsule::table(InvoiceFixService::LOG_TABLE . ' as l')
                ->leftJoin('tblinvoices as replacement', 'replacement.id', '=', 'l.draft_invoice_id')
                ->whereNull('replacement.id')
                ->count();

            if ($missingOriginal > 0 || $missingReplacement > 0) {
                return self::result(
                    'relationships',
                    'health_check_relationships',
                    self::STATUS_WARNING,
                    InvoiceFixText::get('health_relationships_missing', [
                        'total' => $total,
                        'original' => $missingOriginal,
                        'replacement' => $missingReplacement,
                    ])
                );
            }

            return self::result(
                'relationships',
                'health_check_relationships',
                self::STATUS_PASS,
                InvoiceFixText::get('health_relationships_ok', ['total' => $total])
            );
        } catch (Throwable $e) {
            return self::result(
                'relationships',
                'health_check_relationships',
                self::STATUS_FAIL,
                InvoiceFixText::get('health_relationships_error', ['details' => $e->getMessage()])
            );
        }
    }

    /** @return array{key:string,label:string,status:string,details:string} */
    private static function checkClientRelationships(): array
    {
        try {
            if (!Capsule::schema()->hasTable(InvoiceFixService::LOG_TABLE)) {
                return self::result('client_match', 'health_check_client_match', self::STATUS_FAIL, InvoiceFixText::get('health_client_match_unavailable'));
            }

            $mismatches = (int) Capsule::table(InvoiceFixService::LOG_TABLE . ' as l')
                ->join('tblinvoices as source', 'source.id', '=', 'l.source_invoice_id')
                ->join('tblinvoices as replacement', 'replacement.id', '=', 'l.draft_invoice_id')
                ->whereColumn('source.userid', '<>', 'replacement.userid')
                ->count();

            return $mismatches === 0
                ? self::result('client_match', 'health_check_client_match', self::STATUS_PASS, InvoiceFixText::get('health_client_match_ok'))
                : self::result('client_match', 'health_check_client_match', self::STATUS_FAIL, InvoiceFixText::get('health_client_match_failed', ['count' => $mismatches]));
        } catch (Throwable $e) {
            return self::result(
                'client_match',
                'health_check_client_match',
                self::STATUS_FAIL,
                InvoiceFixText::get('health_client_match_error', ['details' => $e->getMessage()])
            );
        }
    }

    /** @return array{key:string,label:string,status:string,details:string} */
    private static function checkAuditWarnings(): array
    {
        try {
            if (!Capsule::schema()->hasTable(InvoiceFixService::LOG_TABLE)) {
                return self::result('audit_warnings', 'health_check_audit_warnings', self::STATUS_FAIL, InvoiceFixText::get('health_audit_warnings_unavailable'));
            }

            $warnings = (int) Capsule::table(InvoiceFixService::LOG_TABLE)
                ->where('result', 'warning')
                ->count();

            return $warnings === 0
                ? self::result('audit_warnings', 'health_check_audit_warnings', self::STATUS_PASS, InvoiceFixText::get('health_audit_warnings_ok'))
                : self::result('audit_warnings', 'health_check_audit_warnings', self::STATUS_WARNING, InvoiceFixText::get('health_audit_warnings_found', ['count' => $warnings]));
        } catch (Throwable $e) {
            return self::result(
                'audit_warnings',
                'health_check_audit_warnings',
                self::STATUS_FAIL,
                InvoiceFixText::get('health_audit_warnings_error', ['details' => $e->getMessage()])
            );
        }
    }

    /** @return array{key:string,label:string,status:string,details:string} */
    private static function result(string $key, string $labelKey, string $status, string $details): array
    {
        return [
            'key' => $key,
            'label' => InvoiceFixText::get($labelKey),
            'status' => $status,
            'details' => $details,
        ];
    }
}
