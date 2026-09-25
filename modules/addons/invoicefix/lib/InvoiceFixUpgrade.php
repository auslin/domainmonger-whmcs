<?php

declare(strict_types=1);

namespace InvoiceFix\Module;

use RuntimeException;
use Throwable;
use WHMCS\Database\Capsule;

if (!defined('WHMCS')) {
    exit('This file cannot be accessed directly');
}

/**
 * Versioned, idempotent InvoiceFix installation and database migrations.
 *
 * WHMCS invokes invoicefix_upgrade() after it detects a version change. The
 * module also performs a lightweight self-check when its admin page or invoice
 * hook is used, so manually refreshed files cannot leave InvoiceFix in a
 * partially upgraded state.
 */
final class InvoiceFixUpgrade
{
    public const MODULE_VERSION = '1.17.1';
    public const CURRENT_SCHEMA_VERSION = '1.17.1';

    public const SETTING_SCHEMA_VERSION = 'schema_version';
    public const SETTING_LAST_UPGRADE_FROM = 'last_upgrade_from';
    public const SETTING_LAST_UPGRADE_TO = 'last_upgrade_to';
    public const SETTING_LAST_UPGRADE_AT = 'last_upgrade_at';

    private const MODULE = 'invoicefix';
    private const LOG_TABLE = 'mod_invoicefix_log';

    /** @var array<string,string> Version => migration method */
    private const MIGRATIONS = [
        '1.7.0' => 'migrateTo170',
        '1.8.0' => 'migrateTo180',
        '1.9.0' => 'migrateTo190',
        '1.10.0' => 'migrateTo1100',
        '1.11.0' => 'migrateTo1110',
        '1.12.0' => 'migrateTo1120',
        '1.13.0' => 'migrateTo1130',
        '1.14.0' => 'migrateTo1140',
        '1.15.0' => 'migrateTo1150',
        '1.16.0' => 'migrateTo1160',
        '1.17.0' => 'migrateTo1170',
        '1.17.1' => 'migrateTo1171',
    ];

    /**
     * Exact module-relative files that a future migration may safely remove.
     * This list is intentionally empty in 1.17.1.
     *
     * @var string[]
     */
    private const DEPRECATED_FILES = [];

    private static bool $checkedThisRequest = false;

    public static function activate(): void
    {
        self::runMigrations('0.0.0', true);
    }

    public static function upgrade(string $previousVersion): void
    {
        $previousVersion = self::normalizeVersion($previousVersion, '0.0.0');
        self::runMigrations($previousVersion, false);
    }

    /**
     * Repair a missing migration marker or incomplete file-only upgrade.
     * Repeated calls in one request are no-ops.
     */
    public static function ensureCurrent(): void
    {
        if (self::$checkedThisRequest) {
            return;
        }
        self::$checkedThisRequest = true;

        $recordedSetting = self::readSetting(self::SETTING_SCHEMA_VERSION, '');
        $fallbackVersion = self::normalizeVersion(self::readSetting('version', '0.0.0'), '0.0.0');
        $recorded = self::normalizeVersion($recordedSetting, $fallbackVersion);

        if (
            version_compare($recorded, self::CURRENT_SCHEMA_VERSION, '>=')
            && Capsule::schema()->hasTable(self::LOG_TABLE)
        ) {
            return;
        }

        self::runMigrations($recorded, false);
    }

    /**
     * @return array{module_version:string,schema_version:string,last_upgrade_from:string,last_upgrade_to:string,last_upgrade_at:string,is_current:bool,log_table_exists:bool}
     */
    public static function status(): array
    {
        $schemaVersion = self::normalizeVersion(
            self::readSetting(self::SETTING_SCHEMA_VERSION, '0.0.0'),
            '0.0.0'
        );
        $logTableExists = Capsule::schema()->hasTable(self::LOG_TABLE);

        return [
            'module_version' => self::MODULE_VERSION,
            'schema_version' => $schemaVersion,
            'last_upgrade_from' => self::readSetting(self::SETTING_LAST_UPGRADE_FROM, ''),
            'last_upgrade_to' => self::readSetting(self::SETTING_LAST_UPGRADE_TO, ''),
            'last_upgrade_at' => self::readSetting(self::SETTING_LAST_UPGRADE_AT, ''),
            'is_current' => $logTableExists
                && version_compare($schemaVersion, self::CURRENT_SCHEMA_VERSION, '>='),
            'log_table_exists' => $logTableExists,
        ];
    }

    private static function runMigrations(string $fallbackVersion, bool $freshActivation): void
    {
        $fallbackVersion = self::normalizeVersion($fallbackVersion, '0.0.0');
        $recordedVersion = self::readSetting(self::SETTING_SCHEMA_VERSION, '');
        $fromVersion = $recordedVersion !== ''
            ? self::normalizeVersion($recordedVersion, $fallbackVersion)
            : $fallbackVersion;

        $originalVersion = $fromVersion;
        $appliedAny = false;

        foreach (self::MIGRATIONS as $targetVersion => $method) {
            if (version_compare($fromVersion, $targetVersion, '>=')) {
                continue;
            }

            if (!method_exists(self::class, $method)) {
                throw new RuntimeException('InvoiceFix migration method is missing: ' . $method);
            }

            self::{$method}();
            self::writeSetting(self::SETTING_SCHEMA_VERSION, $targetVersion);
            $fromVersion = $targetVersion;
            $appliedAny = true;
        }

        // A missing migration marker or table can occur after a manual file
        // refresh or incomplete earlier installation. Verify and repair only the
        // InvoiceFix audit structure, then record the current schema version.
        if ($recordedVersion === '' || !Capsule::schema()->hasTable(self::LOG_TABLE)) {
            self::ensureLogTableAndColumns();
            self::writeSetting(self::SETTING_SCHEMA_VERSION, self::CURRENT_SCHEMA_VERSION);
            $fromVersion = self::CURRENT_SCHEMA_VERSION;
            $appliedAny = true;
        }

        self::removeDeprecatedFiles();

        if ($freshActivation || $appliedAny || $recordedVersion === '') {
            self::writeSetting(self::SETTING_LAST_UPGRADE_FROM, $originalVersion);
            self::writeSetting(self::SETTING_LAST_UPGRADE_TO, self::MODULE_VERSION);
            self::writeSetting(self::SETTING_LAST_UPGRADE_AT, gmdate('Y-m-d H:i:s') . ' UTC');
        }

        if (version_compare($fromVersion, self::CURRENT_SCHEMA_VERSION, '<')) {
            throw new RuntimeException(
                'InvoiceFix database schema did not reach version ' . self::CURRENT_SCHEMA_VERSION . '.'
            );
        }
    }

    private static function migrateTo170(): void
    {
        self::ensureLogTableAndColumns();
    }

    private static function migrateTo180(): void
    {
        // InvoiceFix 1.8.0 adds read-only diagnostics and does not require
        // new tables or columns. Re-verify the existing audit structure so a
        // file-only upgrade remains safe and idempotent.
        self::ensureLogTableAndColumns();
    }

    private static function migrateTo190(): void
    {
        // InvoiceFix 1.9.0 adds read-only Work Queue search and date filters.
        // No new tables or columns are required.
        self::ensureLogTableAndColumns();
    }

    private static function migrateTo1100(): void
    {
        // InvoiceFix 1.10.0 adds read-only Work Queue summary counts.
        // No new tables or columns are required.
        self::ensureLogTableAndColumns();
    }

    private static function migrateTo1110(): void
    {
        // InvoiceFix 1.11.0 adds read-only Work Queue column sorting.
        // No new tables or columns are required.
        self::ensureLogTableAndColumns();
    }


    private static function migrateTo1120(): void
    {
        // InvoiceFix 1.12.0 adds a read-only filtered Work Queue CSV export.
        // No new tables or columns are required.
        self::ensureLogTableAndColumns();
    }

    private static function migrateTo1130(): void
    {
        // InvoiceFix 1.13.0 adds read-only Work Queue aging indicators.
        // No new tables or columns are required.
        self::ensureLogTableAndColumns();
    }

    private static function migrateTo1140(): void
    {
        // InvoiceFix 1.14.0 adds a read-only Stale Work Queue filter and
        // summary count based on the existing configurable aging threshold.
        // No new tables or columns are required.
        self::ensureLogTableAndColumns();
    }

    private static function migrateTo1150(): void
    {
        // InvoiceFix 1.15.0 adds administrator-only notes to the module's
        // audit records. These columns never alter WHMCS invoice or ledger data.
        self::ensureLogTableAndColumns();
    }

    private static function migrateTo1160(): void
    {
        // InvoiceFix 1.16.0 adds administrator-only follow-up ownership and
        // due dates to the module audit record. WHMCS invoice data is untouched.
        self::ensureLogTableAndColumns();
    }

    private static function migrateTo1170(): void
    {
        // InvoiceFix 1.17.0 adds read-only My Follow-Ups and Overdue Work
        // Queue filters using the existing assignment columns.
        // No new tables or columns are required.
        self::ensureLogTableAndColumns();
    }

    private static function migrateTo1171(): void
    {
        // InvoiceFix 1.17.1 removes a duplicate addon-page heading and tightens
        // administrator-page spacing. No new tables or columns are required.
        self::ensureLogTableAndColumns();
    }

    private static function ensureLogTableAndColumns(): void
    {
        $schema = Capsule::schema();

        if (!$schema->hasTable(self::LOG_TABLE)) {
            $schema->create(self::LOG_TABLE, function ($table): void {
                $table->increments('id');
                $table->unsignedInteger('source_invoice_id')->index();
                $table->unsignedInteger('draft_invoice_id')->unique();
                $table->unsignedInteger('admin_id')->nullable()->index();
                $table->string('source_status', 32)->default('');
                $table->decimal('source_total', 18, 2)->default(0);
                $table->string('result', 16)->default('success');
                $table->text('message')->nullable();
                $table->text('internal_note')->nullable();
                $table->unsignedInteger('internal_note_admin_id')->nullable();
                $table->timestamp('internal_note_updated_at')->nullable();
                $table->unsignedInteger('follow_up_admin_id')->nullable();
                $table->date('follow_up_due_date')->nullable();
                $table->unsignedInteger('follow_up_updated_by_admin_id')->nullable();
                $table->timestamp('follow_up_updated_at')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index(['source_invoice_id', 'created_at']);
            });
            return;
        }

        // These checks make upgrades safe for early or incomplete installations.
        $columns = [
            'source_invoice_id' => static fn ($table) => $table->unsignedInteger('source_invoice_id')->default(0),
            'draft_invoice_id' => static fn ($table) => $table->unsignedInteger('draft_invoice_id')->default(0),
            'admin_id' => static fn ($table) => $table->unsignedInteger('admin_id')->nullable(),
            'source_status' => static fn ($table) => $table->string('source_status', 32)->default(''),
            'source_total' => static fn ($table) => $table->decimal('source_total', 18, 2)->default(0),
            'result' => static fn ($table) => $table->string('result', 16)->default('success'),
            'message' => static fn ($table) => $table->text('message')->nullable(),
            'internal_note' => static fn ($table) => $table->text('internal_note')->nullable(),
            'internal_note_admin_id' => static fn ($table) => $table->unsignedInteger('internal_note_admin_id')->nullable(),
            'internal_note_updated_at' => static fn ($table) => $table->timestamp('internal_note_updated_at')->nullable(),
            'follow_up_admin_id' => static fn ($table) => $table->unsignedInteger('follow_up_admin_id')->nullable(),
            'follow_up_due_date' => static fn ($table) => $table->date('follow_up_due_date')->nullable(),
            'follow_up_updated_by_admin_id' => static fn ($table) => $table->unsignedInteger('follow_up_updated_by_admin_id')->nullable(),
            'follow_up_updated_at' => static fn ($table) => $table->timestamp('follow_up_updated_at')->nullable(),
            'created_at' => static fn ($table) => $table->timestamp('created_at')->nullable(),
        ];

        foreach ($columns as $column => $definition) {
            if ($schema->hasColumn(self::LOG_TABLE, $column)) {
                continue;
            }

            $schema->table(self::LOG_TABLE, function ($table) use ($definition): void {
                $definition($table);
            });
        }
    }

    private static function removeDeprecatedFiles(): void
    {
        if (self::DEPRECATED_FILES === []) {
            return;
        }

        $moduleRoot = realpath(dirname(__DIR__));
        if ($moduleRoot === false) {
            return;
        }

        foreach (self::DEPRECATED_FILES as $relativePath) {
            $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
            if ($relativePath === '' || str_contains($relativePath, '../')) {
                continue;
            }

            $candidate = $moduleRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            if (!is_file($candidate)) {
                continue;
            }

            $realCandidate = realpath($candidate);
            if ($realCandidate === false || !str_starts_with($realCandidate, $moduleRoot . DIRECTORY_SEPARATOR)) {
                continue;
            }

            @unlink($realCandidate);
        }
    }

    private static function readSetting(string $name, string $default): string
    {
        try {
            $value = Capsule::table('tbladdonmodules')
                ->where('module', self::MODULE)
                ->where('setting', $name)
                ->value('value');

            return $value === null ? $default : (string) $value;
        } catch (Throwable $e) {
            return $default;
        }
    }

    private static function writeSetting(string $name, string $value): void
    {
        Capsule::table('tbladdonmodules')->updateOrInsert(
            [
                'module' => self::MODULE,
                'setting' => $name,
            ],
            ['value' => $value]
        );
    }

    private static function normalizeVersion(string $version, string $fallback): string
    {
        $version = trim($version);
        if ($version === '' || preg_match('/^\d+(?:\.\d+){0,3}(?:[-+][0-9A-Za-z.-]+)?$/', $version) !== 1) {
            return $fallback;
        }

        return $version;
    }
}
