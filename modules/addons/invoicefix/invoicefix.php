<?php

declare(strict_types=1);

use InvoiceFix\Module\InvoiceFixAging;
use InvoiceFix\Module\InvoiceFixComparison;
use InvoiceFix\Module\InvoiceFixDetails;
use InvoiceFix\Module\InvoiceFixExport;
use InvoiceFix\Module\InvoiceFixFollowUp;
use InvoiceFix\Module\InvoiceFixHealth;
use InvoiceFix\Module\InvoiceFixNote;
use InvoiceFix\Module\InvoiceFixQueueExport;
use InvoiceFix\Module\InvoiceFixService;
use InvoiceFix\Module\InvoiceFixText;
use InvoiceFix\Module\InvoiceFixUpgrade;
use InvoiceFix\Module\InvoiceFixWorkQueue;

if (!defined('WHMCS')) {
    exit('This file cannot be accessed directly');
}

require_once __DIR__ . '/lib/InvoiceFixService.php';
require_once __DIR__ . '/lib/InvoiceFixAging.php';
require_once __DIR__ . '/lib/InvoiceFixDetails.php';
require_once __DIR__ . '/lib/InvoiceFixExport.php';
require_once __DIR__ . '/lib/InvoiceFixFollowUp.php';
require_once __DIR__ . '/lib/InvoiceFixHealth.php';
require_once __DIR__ . '/lib/InvoiceFixNote.php';
require_once __DIR__ . '/lib/InvoiceFixQueueExport.php';
require_once __DIR__ . '/lib/InvoiceFixComparison.php';
require_once __DIR__ . '/lib/InvoiceFixWorkQueue.php';
require_once __DIR__ . '/lib/InvoiceFixUpgrade.php';

function invoicefix_config(): array
{
    return [
        'name' => 'InvoiceFix',
        'description' => InvoiceFixText::get('module_description') . ' ' . InvoiceFixText::get('module_text_editor_hint'),
        'author' => 'InvoiceFix',
        'language' => 'english',
        'version' => InvoiceFixUpgrade::MODULE_VERSION,
        'fields' => [
            'show_linked_invoice_notice' => [
                'FriendlyName' => InvoiceFixText::get('setting_show_linked_name'),
                'Type' => 'yesno',
                'Description' => InvoiceFixText::get('setting_show_linked_description'),
                'Default' => 'on',
            ],
            'linked_invoice_notice_style' => [
                'FriendlyName' => InvoiceFixText::get('setting_notice_style_name'),
                'Type' => 'dropdown',
                'Options' => InvoiceFixText::get('setting_notice_style_options'),
                'Description' => InvoiceFixText::get('setting_notice_style_description'),
                'Default' => 'Compact',
            ],
            'show_work_queue_aging' => [
                'FriendlyName' => InvoiceFixText::get('setting_queue_aging_name'),
                'Type' => 'yesno',
                'Description' => InvoiceFixText::get('setting_queue_aging_description'),
                'Default' => 'on',
            ],
            'work_queue_stale_days' => [
                'FriendlyName' => InvoiceFixText::get('setting_queue_stale_days_name'),
                'Type' => 'text',
                'Size' => '5',
                'Description' => InvoiceFixText::get('setting_queue_stale_days_description'),
                'Default' => '7',
            ],
        ],
    ];
}

function invoicefix_activate(): array
{
    try {
        InvoiceFixUpgrade::activate();

        return [
            'status' => 'success',
            'description' => InvoiceFixText::get('activate_success'),
        ];
    } catch (Throwable $e) {
        return [
            'status' => 'error',
            'description' => InvoiceFixText::get('activate_error', ['details' => $e->getMessage()]),
        ];
    }
}

function invoicefix_upgrade(array $vars): void
{
    InvoiceFixUpgrade::upgrade((string) ($vars['version'] ?? '0.0.0'));
}

function invoicefix_deactivate(): array
{
    return [
        'status' => 'success',
        'description' => InvoiceFixText::get('deactivate_success'),
    ];
}

function invoicefix_output(array $vars): void
{
    $upgradeError = '';
    try {
        InvoiceFixUpgrade::ensureCurrent();
    } catch (Throwable $e) {
        $upgradeError = $e->getMessage();
    }

    $moduleLink = trim((string) ($vars['modulelink'] ?? ''));
    if ($moduleLink === '') {
        $moduleLink = 'addonmodules.php?module=invoicefix';
    }

    echo invoicefix_styles();
    echo '<div class="invoicefix-admin">';

    echo '<div class="invoicefix-page-heading">';
    echo '<div class="invoicefix-heading-actions">';
    $systemCheckUrl = invoicefix_module_url($moduleLink, ['system_check' => 1]);
    echo '<a class="btn btn-default" href="' . invoicefix_escape($systemCheckUrl) . '">';
    echo '<i class="fas fa-stethoscope" aria-hidden="true"></i> ' . invoicefix_escape(InvoiceFixText::get('health_button'));
    echo '</a>';
    echo '<button type="button" class="btn btn-default" data-toggle="modal" data-target="#invoicefixTextModal">';
    echo '<i class="fas fa-edit" aria-hidden="true"></i> ' . invoicefix_escape(InvoiceFixText::get('admin_edit_text_button'));
    echo '</button>';
    echo '</div>';
    echo '</div>';

    $flash = $_SESSION['invoicefix_flash'] ?? null;
    unset($_SESSION['invoicefix_flash']);
    if (is_array($flash) && isset($flash['message'])) {
        $type = ($flash['type'] ?? '') === 'error' ? 'danger' : 'success';
        echo '<div class="alert alert-' . $type . '">' . invoicefix_escape((string) $flash['message']) . '</div>';
    }

    if ($upgradeError !== '') {
        echo '<div class="alert alert-danger">' . invoicefix_escape(InvoiceFixText::get('upgrade_error', [
            'details' => $upgradeError,
        ])) . '</div>';
    }

    echo '<div class="alert alert-info">';
    echo '<strong>' . invoicefix_escape(InvoiceFixText::get('admin_safe_workflow_title')) . '</strong> ';
    echo invoicefix_escape(InvoiceFixText::get('admin_safe_workflow_text'));
    echo '</div>';

    echo '<p class="text-muted">' . invoicefix_escape(InvoiceFixText::get('admin_settings_note')) . '</p>';

    if ((string) ($_GET['system_check'] ?? '') === '1') {
        $admin = null;
        $adminError = '';
        try {
            $admin = InvoiceFixService::adminContext();
        } catch (Throwable $e) {
            $adminError = $e->getMessage();
        }

        echo invoicefix_render_system_check($moduleLink, InvoiceFixHealth::run($admin, $adminError));
        echo invoicefix_render_text_modal($moduleLink);
        echo '</div>';
        return;
    }

    if (!InvoiceFixService::logTableExists()) {
        echo '<div class="alert alert-danger">' . invoicefix_escape(InvoiceFixText::get('admin_audit_missing')) . '</div>';
        echo invoicefix_render_upgrade_status($upgradeError);
        echo invoicefix_render_text_modal($moduleLink);
        echo '</div>';
        return;
    }

    $detailsLogId = max(0, (int) ($_GET['details_log_id'] ?? 0));
    if ($detailsLogId > 0) {
        try {
            echo invoicefix_render_details($moduleLink, InvoiceFixDetails::load($detailsLogId));
        } catch (Throwable $e) {
            echo '<div class="alert alert-warning">' . invoicefix_escape(InvoiceFixText::get('details_load_error', [
                'details' => $e->getMessage(),
            ])) . '</div>';
            $backUrl = invoicefix_module_url($moduleLink, invoicefix_queue_navigation_parameters());
            echo '<p><a class="btn btn-default" href="' . invoicefix_escape($backUrl) . '">'
                . invoicefix_escape(InvoiceFixText::get('details_back_queue')) . '</a></p>';
        }

        echo invoicefix_render_text_modal($moduleLink);
        echo '</div>';
        return;
    }

    $compareLogId = max(0, (int) ($_GET['compare_log_id'] ?? 0));
    if ($compareLogId > 0) {
        try {
            echo invoicefix_render_comparison($moduleLink, InvoiceFixComparison::load($compareLogId));
        } catch (Throwable $e) {
            echo '<div class="alert alert-warning">' . invoicefix_escape(InvoiceFixText::get('compare_load_error', [
                'details' => $e->getMessage(),
            ])) . '</div>';
            $backUrl = invoicefix_module_url($moduleLink, invoicefix_queue_navigation_parameters());
            echo '<p><a class="btn btn-default" href="' . invoicefix_escape($backUrl) . '">'
                . invoicefix_escape(InvoiceFixText::get('compare_back_queue')) . '</a></p>';
        }

        echo invoicefix_render_text_modal($moduleLink);
        echo '</div>';
        return;
    }

    try {
        $showAging = InvoiceFixService::moduleSettingEnabled('show_work_queue_aging', true);
        $currentAdminId = max(0, (int) ($_SESSION['adminid'] ?? 0));
        $staleDays = invoicefix_work_queue_stale_days();
        $filter = InvoiceFixWorkQueue::normalizeFilter((string) ($_GET['queue_filter'] ?? 'all'));
        if (!$showAging && $filter === InvoiceFixWorkQueue::FILTER_STALE) {
            $filter = InvoiceFixWorkQueue::FILTER_ALL;
        }
        $page = max(1, (int) ($_GET['queue_page'] ?? 1));
        $search = InvoiceFixWorkQueue::normalizeSearch((string) ($_GET['queue_search'] ?? ''));
        $dateFrom = InvoiceFixWorkQueue::normalizeDate((string) ($_GET['queue_date_from'] ?? ''));
        $dateTo = InvoiceFixWorkQueue::normalizeDate((string) ($_GET['queue_date_to'] ?? ''));
        $sort = InvoiceFixWorkQueue::normalizeSort((string) ($_GET['queue_sort'] ?? InvoiceFixWorkQueue::SORT_CREATED));
        $direction = InvoiceFixWorkQueue::normalizeDirection((string) ($_GET['queue_direction'] ?? InvoiceFixWorkQueue::DIRECTION_DESC));
        $queue = InvoiceFixWorkQueue::page($filter, $page, 50, $search, $dateFrom, $dateTo, $sort, $direction, $staleDays, $currentAdminId);
        $summary = InvoiceFixWorkQueue::summary(
            (string) $queue['search'],
            (string) $queue['date_from'],
            (string) $queue['date_to'],
            $staleDays,
            $currentAdminId
        );

        echo '<div class="panel panel-default invoicefix-panel">';
        echo '<div class="panel-heading"><strong>' . invoicefix_escape(InvoiceFixText::get('queue_title')) . '</strong></div>';
        echo '<div class="panel-body invoicefix-queue-toolbar">';
        echo '<p class="text-muted invoicefix-queue-intro">' . invoicefix_escape(InvoiceFixText::get('queue_intro')) . '</p>';
        echo invoicefix_render_queue_search_form($moduleLink, $queue);
        echo invoicefix_render_queue_summary($moduleLink, $queue, $summary, $showAging);
        echo '<div class="invoicefix-queue-filter-row">';
        echo invoicefix_render_queue_filters($moduleLink, $queue, $showAging);
        echo invoicefix_render_queue_export_form($moduleLink, $queue);
        echo '</div>';
        echo '</div>';
        echo '<div class="table-responsive"><table class="table table-striped table-bordered invoicefix-queue-table">';
        echo '<thead><tr>';
        echo invoicefix_render_queue_sortable_header($moduleLink, $queue, InvoiceFixWorkQueue::SORT_ORIGINAL, 'queue_table_original');
        echo invoicefix_render_queue_sortable_header($moduleLink, $queue, InvoiceFixWorkQueue::SORT_REPLACEMENT, 'queue_table_replacement');
        echo invoicefix_render_queue_sortable_header($moduleLink, $queue, InvoiceFixWorkQueue::SORT_WORKFLOW, 'queue_table_workflow');
        echo invoicefix_render_queue_sortable_header($moduleLink, $queue, InvoiceFixWorkQueue::SORT_CREATED, 'queue_table_created');
        if ($showAging) {
            echo '<th>' . invoicefix_escape(InvoiceFixText::get('queue_table_age')) . '</th>';
        }
        echo '<th class="text-right">' . invoicefix_escape(InvoiceFixText::get('queue_table_actions')) . '</th>';
        echo '</tr></thead><tbody>';

        if ($queue['rows'] === []) {
            echo '<tr><td colspan="' . ($showAging ? '6' : '5') . '" class="text-center text-muted">' . invoicefix_escape(InvoiceFixText::get('queue_no_results')) . '</td></tr>';
        }

        foreach ($queue['rows'] as $row) {
            $sourceId = (int) ($row->source_invoice_id ?? 0);
            $replacementId = (int) ($row->draft_invoice_id ?? 0);
            $sourceExists = (int) ($row->source_exists_id ?? 0) > 0;
            $replacementExists = (int) ($row->replacement_exists_id ?? 0) > 0;
            $sourceStatus = trim((string) ($row->source_current_status ?? ''));
            $replacementStatus = trim((string) ($row->replacement_current_status ?? ''));
            $state = InvoiceFixWorkQueue::normalizeFilter((string) ($row->workflow_state ?? 'attention'));
            $aging = invoicefix_work_queue_age((string) ($row->created_at ?? ''), $state, $staleDays);

            echo '<tr' . ($showAging && $aging['stale'] ? ' class="invoicefix-queue-row-stale"' : '') . '>';
            echo '<td>' . invoicefix_render_queue_invoice($sourceId, $sourceStatus, $sourceExists) . '</td>';
            echo '<td>' . invoicefix_render_queue_invoice($replacementId, $replacementStatus, $replacementExists) . '</td>';
            echo '<td>' . invoicefix_render_queue_state($state) . invoicefix_render_queue_follow_up($row, $state) . '</td>';
            echo '<td>' . invoicefix_escape((string) ($row->created_at ?? '')) . '</td>';
            if ($showAging) {
                echo '<td>' . invoicefix_render_queue_age($aging) . '</td>';
            }
            echo '<td class="text-right invoicefix-queue-actions">';
            echo invoicefix_render_queue_action($sourceId, $sourceExists, InvoiceFixText::get('queue_open_original'));
            echo ' ';
            echo invoicefix_render_queue_action($replacementId, $replacementExists, InvoiceFixText::get('queue_open_replacement'));
            echo ' ';
            echo invoicefix_render_details_action(
                $moduleLink,
                (int) ($row->log_id ?? 0),
                (string) $queue['filter'],
                (int) $queue['page']
            );
            echo ' ';
            echo invoicefix_render_compare_action(
                $moduleLink,
                (int) ($row->log_id ?? 0),
                $sourceExists && $replacementExists,
                (string) $queue['filter'],
                (int) $queue['page']
            );
            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody></table></div>';
        echo invoicefix_render_queue_pagination($moduleLink, $queue);
        echo '</div>';
    } catch (Throwable $e) {
        echo '<div class="alert alert-warning">' . invoicefix_escape(InvoiceFixText::get('queue_load_error', [
            'details' => $e->getMessage(),
        ])) . '</div>';
    }

    echo invoicefix_render_upgrade_status($upgradeError);
    echo invoicefix_render_text_modal($moduleLink);
    echo '</div>';
}

/**
 * @param array{generated_at:string,checks:array<int,array{key:string,label:string,status:string,details:string}>,summary:array{pass:int,warning:int,fail:int,info:int}} $report
 */
function invoicefix_render_system_check(string $moduleLink, array $report): string
{
    $backUrl = $moduleLink;
    $summary = $report['summary'];
    $hasFailures = (int) $summary['fail'] > 0;
    $hasWarnings = (int) $summary['warning'] > 0;
    $overallClass = $hasFailures ? 'danger' : ($hasWarnings ? 'warning' : 'success');
    $overallText = $hasFailures
        ? InvoiceFixText::get('health_overall_failed')
        : ($hasWarnings ? InvoiceFixText::get('health_overall_warning') : InvoiceFixText::get('health_overall_passed'));

    $html = '<div class="invoicefix-health">';
    $html .= '<div class="invoicefix-health-heading">';
    $html .= '<div><h3>' . invoicefix_escape(InvoiceFixText::get('health_title')) . '</h3>';
    $html .= '<p class="text-muted">' . invoicefix_escape(InvoiceFixText::get('health_intro')) . '</p></div>';
    $html .= '<a class="btn btn-default" href="' . invoicefix_escape($backUrl) . '">' . invoicefix_escape(InvoiceFixText::get('health_back_queue')) . '</a>';
    $html .= '</div>';

    $html .= '<div class="alert alert-info"><strong>' . invoicefix_escape(InvoiceFixText::get('health_read_only_title')) . '</strong> '
        . invoicefix_escape(InvoiceFixText::get('health_read_only_text')) . '</div>';

    $html .= '<div class="panel panel-' . $overallClass . ' invoicefix-health-summary">';
    $html .= '<div class="panel-heading"><strong>' . invoicefix_escape($overallText) . '</strong></div>';
    $html .= '<div class="panel-body">';
    $html .= '<span class="label label-success">' . invoicefix_escape(InvoiceFixText::get('health_summary_pass', ['count' => (int) $summary['pass']])) . '</span> ';
    $html .= '<span class="label label-warning">' . invoicefix_escape(InvoiceFixText::get('health_summary_warning', ['count' => (int) $summary['warning']])) . '</span> ';
    $html .= '<span class="label label-danger">' . invoicefix_escape(InvoiceFixText::get('health_summary_fail', ['count' => (int) $summary['fail']])) . '</span> ';
    $html .= '<span class="label label-info">' . invoicefix_escape(InvoiceFixText::get('health_summary_info', ['count' => (int) $summary['info']])) . '</span>';
    $html .= '<p class="text-muted invoicefix-health-generated">' . invoicefix_escape(InvoiceFixText::get('health_generated_at', ['date' => (string) $report['generated_at']])) . '</p>';
    $html .= '</div></div>';

    $html .= '<div class="panel panel-default">';
    $html .= '<div class="panel-heading"><strong>' . invoicefix_escape(InvoiceFixText::get('health_results_title')) . '</strong></div>';
    $html .= '<div class="table-responsive"><table class="table table-bordered table-striped invoicefix-health-table">';
    $html .= '<thead><tr><th>' . invoicefix_escape(InvoiceFixText::get('health_table_check')) . '</th>';
    $html .= '<th>' . invoicefix_escape(InvoiceFixText::get('health_table_status')) . '</th>';
    $html .= '<th>' . invoicefix_escape(InvoiceFixText::get('health_table_details')) . '</th></tr></thead><tbody>';

    foreach ($report['checks'] as $check) {
        $status = (string) ($check['status'] ?? InvoiceFixHealth::STATUS_INFO);
        $badgeClass = [
            InvoiceFixHealth::STATUS_PASS => 'success',
            InvoiceFixHealth::STATUS_WARNING => 'warning',
            InvoiceFixHealth::STATUS_FAIL => 'danger',
            InvoiceFixHealth::STATUS_INFO => 'info',
        ][$status] ?? 'info';
        $statusText = [
            InvoiceFixHealth::STATUS_PASS => InvoiceFixText::get('health_status_pass'),
            InvoiceFixHealth::STATUS_WARNING => InvoiceFixText::get('health_status_warning'),
            InvoiceFixHealth::STATUS_FAIL => InvoiceFixText::get('health_status_fail'),
            InvoiceFixHealth::STATUS_INFO => InvoiceFixText::get('health_status_info'),
        ][$status] ?? InvoiceFixText::get('health_status_info');

        $html .= '<tr><th>' . invoicefix_escape((string) $check['label']) . '</th>';
        $html .= '<td><span class="label label-' . $badgeClass . '">' . invoicefix_escape($statusText) . '</span></td>';
        $html .= '<td>' . invoicefix_escape((string) $check['details']) . '</td></tr>';
    }

    $html .= '</tbody></table></div></div></div>';
    return $html;
}

function invoicefix_render_upgrade_status(string $upgradeError = ''): string
{
    try {
        $status = InvoiceFixUpgrade::status();
    } catch (Throwable $e) {
        $upgradeError = $upgradeError !== '' ? $upgradeError : $e->getMessage();
        $status = [
            'module_version' => InvoiceFixUpgrade::MODULE_VERSION,
            'schema_version' => '0.0.0',
            'last_upgrade_from' => '',
            'last_upgrade_to' => '',
            'last_upgrade_at' => '',
            'is_current' => false,
            'log_table_exists' => false,
        ];
    }

    $isCurrent = !empty($status['is_current']) && $upgradeError === '';
    $statusClass = $isCurrent ? 'success' : 'danger';
    $statusText = $isCurrent
        ? InvoiceFixText::get('upgrade_status_current')
        : InvoiceFixText::get('upgrade_status_attention');

    $fromVersion = trim((string) ($status['last_upgrade_from'] ?? ''));
    $toVersion = trim((string) ($status['last_upgrade_to'] ?? ''));
    $lastUpgrade = trim((string) ($status['last_upgrade_at'] ?? ''));
    $upgradePath = ($fromVersion !== '' && $toVersion !== '')
        ? InvoiceFixText::get('upgrade_path_value', [
            'from' => $fromVersion === '0.0.0' ? InvoiceFixText::get('upgrade_fresh_install') : $fromVersion,
            'to' => $toVersion,
        ])
        : InvoiceFixText::get('upgrade_not_recorded');

    $html = '<div class="panel panel-default invoicefix-upgrade-panel">';
    $html .= '<div class="panel-heading"><strong>' . invoicefix_escape(InvoiceFixText::get('upgrade_panel_title')) . '</strong></div>';
    $html .= '<div class="table-responsive"><table class="table table-bordered invoicefix-upgrade-table"><tbody>';
    $html .= '<tr><th>' . invoicefix_escape(InvoiceFixText::get('upgrade_module_version')) . '</th><td>'
        . invoicefix_escape((string) ($status['module_version'] ?? InvoiceFixUpgrade::MODULE_VERSION)) . '</td></tr>';
    $html .= '<tr><th>' . invoicefix_escape(InvoiceFixText::get('upgrade_schema_version')) . '</th><td>'
        . invoicefix_escape((string) ($status['schema_version'] ?? '0.0.0')) . '</td></tr>';
    $html .= '<tr><th>' . invoicefix_escape(InvoiceFixText::get('upgrade_last_upgrade')) . '</th><td>'
        . invoicefix_escape($lastUpgrade !== '' ? $lastUpgrade : InvoiceFixText::get('upgrade_not_recorded')) . '</td></tr>';
    $html .= '<tr><th>' . invoicefix_escape(InvoiceFixText::get('upgrade_path')) . '</th><td>'
        . invoicefix_escape($upgradePath) . '</td></tr>';
    $html .= '<tr><th>' . invoicefix_escape(InvoiceFixText::get('upgrade_status')) . '</th><td><span class="label label-'
        . $statusClass . '">' . invoicefix_escape($statusText) . '</span></td></tr>';
    if ($upgradeError !== '') {
        $html .= '<tr class="danger"><th>' . invoicefix_escape(InvoiceFixText::get('upgrade_error_label')) . '</th><td>'
            . invoicefix_escape($upgradeError) . '</td></tr>';
    }
    $html .= '</tbody></table></div></div>';

    return $html;
}

/**
 * @param array{filter:string,search:string,date_from:string,date_to:string,has_criteria:bool,sort:string,direction:string} $queue
 */

/**
 * Return the configured age threshold for unfinished InvoiceFix work.
 */
function invoicefix_work_queue_stale_days(): int
{
    return InvoiceFixAging::configuredThreshold();
}

/**
 * @return array{days:int,stale:bool,label:string,title:string}
 */
function invoicefix_work_queue_age(string $createdAt, string $state, int $staleDays): array
{
    $analysis = InvoiceFixAging::analyze($createdAt, $state, $staleDays);
    $days = (int) $analysis['days'];

    if ($days === 0) {
        $label = InvoiceFixText::get('queue_age_today');
    } elseif ($days === 1) {
        $label = InvoiceFixText::get('queue_age_one_day');
    } else {
        $label = InvoiceFixText::get('queue_age_days', ['days' => $days]);
    }

    $stale = (bool) $analysis['stale'];

    return [
        'days' => $days,
        'stale' => $stale,
        'label' => $label,
        'title' => $stale
            ? InvoiceFixText::get('queue_stale_title', ['days' => $days, 'threshold' => $staleDays])
            : '',
    ];
}

/**
 * @param array{days:int,stale:bool,label:string,title:string} $aging
 */
function invoicefix_render_queue_age(array $aging): string
{
    $html = '<span class="invoicefix-queue-age">' . invoicefix_escape((string) $aging['label']) . '</span>';
    if ($aging['stale']) {
        $html .= ' <span class="label label-warning" title="' . invoicefix_escape((string) $aging['title']) . '">'
            . invoicefix_escape(InvoiceFixText::get('queue_stale_badge')) . '</span>';
    }

    return $html;
}

function invoicefix_render_queue_search_form(string $moduleLink, array $queue): string
{
    $parts = parse_url(html_entity_decode($moduleLink, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    $action = is_array($parts) && isset($parts['path']) && (string) $parts['path'] !== ''
        ? (string) $parts['path']
        : 'addonmodules.php';
    $fixedParameters = [];
    if (is_array($parts) && isset($parts['query'])) {
        parse_str((string) $parts['query'], $fixedParameters);
    }
    if (!isset($fixedParameters['module'])) {
        $fixedParameters['module'] = 'invoicefix';
    }

    $html = '<form method="get" action="' . invoicefix_escape($action) . '" class="invoicefix-queue-search">';
    foreach ($fixedParameters as $name => $value) {
        if (!is_scalar($value)) {
            continue;
        }
        $html .= '<input type="hidden" name="' . invoicefix_escape((string) $name) . '" value="'
            . invoicefix_escape((string) $value) . '">';
    }
    $html .= '<input type="hidden" name="queue_filter" value="' . invoicefix_escape((string) $queue['filter']) . '">';
    $html .= '<input type="hidden" name="queue_sort" value="' . invoicefix_escape((string) $queue['sort']) . '">';
    $html .= '<input type="hidden" name="queue_direction" value="' . invoicefix_escape((string) $queue['direction']) . '">';
    $html .= '<div class="form-group invoicefix-queue-search-field"><label for="invoicefixQueueSearch">'
        . invoicefix_escape(InvoiceFixText::get('queue_search_label')) . '</label>';
    $html .= '<input type="search" class="form-control" id="invoicefixQueueSearch" name="queue_search" maxlength="120" value="'
        . invoicefix_escape((string) $queue['search']) . '" placeholder="'
        . invoicefix_escape(InvoiceFixText::get('queue_search_placeholder')) . '"></div>';
    $html .= '<div class="form-group"><label for="invoicefixQueueDateFrom">'
        . invoicefix_escape(InvoiceFixText::get('queue_date_from_label')) . '</label>';
    $html .= '<input type="date" class="form-control" id="invoicefixQueueDateFrom" name="queue_date_from" value="'
        . invoicefix_escape((string) $queue['date_from']) . '"></div>';
    $html .= '<div class="form-group"><label for="invoicefixQueueDateTo">'
        . invoicefix_escape(InvoiceFixText::get('queue_date_to_label')) . '</label>';
    $html .= '<input type="date" class="form-control" id="invoicefixQueueDateTo" name="queue_date_to" value="'
        . invoicefix_escape((string) $queue['date_to']) . '"></div>';
    $html .= '<div class="invoicefix-queue-search-actions"><button type="submit" class="btn btn-primary">'
        . invoicefix_escape(InvoiceFixText::get('queue_apply_search')) . '</button>';
    if ((bool) $queue['has_criteria']) {
        $clearUrl = invoicefix_module_url($moduleLink, [
            'queue_filter' => (string) $queue['filter'],
        ] + invoicefix_queue_sort_parameters($queue));
        $html .= '<a class="btn btn-default" href="' . invoicefix_escape($clearUrl) . '">'
            . invoicefix_escape(InvoiceFixText::get('queue_clear_search')) . '</a>';
    }
    $html .= '</div></form>';

    return $html;
}


/**
 * @param array{filter:string,search:string,date_from:string,date_to:string,sort:string,direction:string} $queue
 * @param array{counts:array<string,int>,search:string,date_from:string,date_to:string,has_criteria:bool} $summary
 */
function invoicefix_render_queue_summary(string $moduleLink, array $queue, array $summary, bool $includeStale): string
{
    $labels = [
        InvoiceFixWorkQueue::FILTER_ALL => InvoiceFixText::get('queue_filter_all'),
        InvoiceFixWorkQueue::FILTER_DRAFT => InvoiceFixText::get('queue_filter_draft'),
        InvoiceFixWorkQueue::FILTER_REVIEW => InvoiceFixText::get('queue_filter_review'),
        InvoiceFixWorkQueue::FILTER_COMPLETED => InvoiceFixText::get('queue_filter_completed'),
        InvoiceFixWorkQueue::FILTER_ATTENTION => InvoiceFixText::get('queue_filter_attention'),
        InvoiceFixWorkQueue::FILTER_MY_FOLLOW_UPS => InvoiceFixText::get('queue_filter_my_followups'),
        InvoiceFixWorkQueue::FILTER_OVERDUE => InvoiceFixText::get('queue_filter_overdue'),
    ];
    if ($includeStale) {
        $labels[InvoiceFixWorkQueue::FILTER_STALE] = InvoiceFixText::get('queue_filter_stale');
    }

    $activeFilter = (string) $queue['filter'];
    $criteria = invoicefix_queue_criteria_parameters($queue) + invoicefix_queue_sort_parameters($queue);
    $html = '<div class="invoicefix-queue-summary-heading">';
    $html .= '<strong>' . invoicefix_escape(InvoiceFixText::get('queue_summary_title')) . '</strong>';
    $html .= '<span class="text-muted">' . invoicefix_escape(InvoiceFixText::get('queue_summary_intro')) . '</span>';
    $html .= '</div>';
    $html .= '<div class="invoicefix-queue-summary" role="navigation" aria-label="'
        . invoicefix_escape(InvoiceFixText::get('queue_summary_aria')) . '">';

    foreach ($labels as $filter => $label) {
        $count = (int) ($summary['counts'][$filter] ?? 0);
        $url = invoicefix_module_url($moduleLink, ['queue_filter' => $filter] + $criteria);
        $activeClass = $filter === $activeFilter ? ' active' : '';
        $html .= '<a class="invoicefix-summary-card' . $activeClass . '" href="' . invoicefix_escape($url) . '">';
        $html .= '<span class="invoicefix-summary-count">' . $count . '</span>';
        $html .= '<span class="invoicefix-summary-label">' . invoicefix_escape($label) . '</span>';
        $html .= '</a>';
    }

    return $html . '</div>';
}

/**
 * @param array{filter:string,search:string,date_from:string,date_to:string,sort:string,direction:string} $queue
 */
function invoicefix_render_queue_filters(string $moduleLink, array $queue, bool $includeStale): string
{
    $labels = [
        InvoiceFixWorkQueue::FILTER_ALL => InvoiceFixText::get('queue_filter_all'),
        InvoiceFixWorkQueue::FILTER_DRAFT => InvoiceFixText::get('queue_filter_draft'),
        InvoiceFixWorkQueue::FILTER_REVIEW => InvoiceFixText::get('queue_filter_review'),
        InvoiceFixWorkQueue::FILTER_COMPLETED => InvoiceFixText::get('queue_filter_completed'),
        InvoiceFixWorkQueue::FILTER_ATTENTION => InvoiceFixText::get('queue_filter_attention'),
        InvoiceFixWorkQueue::FILTER_MY_FOLLOW_UPS => InvoiceFixText::get('queue_filter_my_followups'),
        InvoiceFixWorkQueue::FILTER_OVERDUE => InvoiceFixText::get('queue_filter_overdue'),
    ];
    if ($includeStale) {
        $labels[InvoiceFixWorkQueue::FILTER_STALE] = InvoiceFixText::get('queue_filter_stale');
    }

    $activeFilter = (string) $queue['filter'];
    $criteria = invoicefix_queue_criteria_parameters($queue) + invoicefix_queue_sort_parameters($queue);
    $html = '<div class="btn-group invoicefix-queue-filters" role="group" aria-label="' . invoicefix_escape(InvoiceFixText::get('queue_filter_aria')) . '">';
    foreach ($labels as $filter => $label) {
        $class = $filter === $activeFilter ? 'btn-primary' : 'btn-default';
        $url = invoicefix_module_url($moduleLink, ['queue_filter' => $filter] + $criteria);
        $html .= '<a class="btn ' . $class . '" href="' . invoicefix_escape($url) . '">' . invoicefix_escape($label) . '</a>';
    }

    return $html . '</div>';
}


/**
 * @param array{filter:string,total:int,search:string,date_from:string,date_to:string,sort:string,direction:string} $queue
 */
function invoicefix_render_queue_export_form(string $moduleLink, array $queue): string
{
    $label = InvoiceFixText::get('queue_export_button');
    if ((int) ($queue['total'] ?? 0) <= 0) {
        return '<span class="btn btn-default disabled" aria-disabled="true">'
            . '<i class="fas fa-download" aria-hidden="true"></i> ' . invoicefix_escape($label) . '</span>';
    }

    $token = function_exists('generate_token') ? (string) generate_token('plain') : '';
    $action = invoicefix_module_url($moduleLink, ['action' => 'export_work_queue']);
    $html = '<form method="post" action="' . invoicefix_escape($action) . '" class="invoicefix-inline-form invoicefix-queue-export">';
    $html .= '<input type="hidden" name="token" value="' . invoicefix_escape($token) . '">';
    $html .= '<input type="hidden" name="queue_filter" value="' . invoicefix_escape((string) $queue['filter']) . '">';
    $html .= '<input type="hidden" name="queue_search" value="' . invoicefix_escape((string) $queue['search']) . '">';
    $html .= '<input type="hidden" name="queue_date_from" value="' . invoicefix_escape((string) $queue['date_from']) . '">';
    $html .= '<input type="hidden" name="queue_date_to" value="' . invoicefix_escape((string) $queue['date_to']) . '">';
    $html .= '<input type="hidden" name="queue_sort" value="' . invoicefix_escape((string) $queue['sort']) . '">';
    $html .= '<input type="hidden" name="queue_direction" value="' . invoicefix_escape((string) $queue['direction']) . '">';
    $html .= '<button type="submit" class="btn btn-default" title="'
        . invoicefix_escape(InvoiceFixText::get('queue_export_button_title')) . '">';
    $html .= '<i class="fas fa-download" aria-hidden="true"></i> ' . invoicefix_escape($label) . '</button></form>';

    return $html;
}

/**
 * @param array{filter:string,search:string,date_from:string,date_to:string,sort:string,direction:string} $queue
 */
function invoicefix_render_queue_sortable_header(
    string $moduleLink,
    array $queue,
    string $targetSort,
    string $labelKey
): string {
    $targetSort = InvoiceFixWorkQueue::normalizeSort($targetSort);
    $currentSort = InvoiceFixWorkQueue::normalizeSort((string) ($queue['sort'] ?? ''));
    $currentDirection = InvoiceFixWorkQueue::normalizeDirection((string) ($queue['direction'] ?? ''));
    $isActive = $targetSort === $currentSort;
    $nextDirection = $isActive
        ? ($currentDirection === InvoiceFixWorkQueue::DIRECTION_ASC
            ? InvoiceFixWorkQueue::DIRECTION_DESC
            : InvoiceFixWorkQueue::DIRECTION_ASC)
        : InvoiceFixWorkQueue::defaultDirection($targetSort);

    $label = InvoiceFixText::get($labelKey);
    $directionText = $nextDirection === InvoiceFixWorkQueue::DIRECTION_ASC
        ? InvoiceFixText::get('queue_sort_ascending')
        : InvoiceFixText::get('queue_sort_descending');
    $title = InvoiceFixText::get('queue_sort_link_title', [
        'column' => $label,
        'direction' => $directionText,
    ]);
    $url = invoicefix_module_url($moduleLink, [
        'queue_filter' => (string) $queue['filter'],
        'queue_page' => 1,
        'queue_sort' => $targetSort,
        'queue_direction' => $nextDirection,
    ] + invoicefix_queue_criteria_parameters($queue));

    $ariaSort = 'none';
    $indicator = '&#8597;';
    if ($isActive) {
        $ariaSort = $currentDirection === InvoiceFixWorkQueue::DIRECTION_ASC ? 'ascending' : 'descending';
        $indicator = $currentDirection === InvoiceFixWorkQueue::DIRECTION_ASC ? '&#9650;' : '&#9660;';
    }

    return '<th class="invoicefix-sortable-heading" aria-sort="' . $ariaSort . '">'
        . '<a href="' . invoicefix_escape($url) . '" title="' . invoicefix_escape($title) . '">'
        . invoicefix_escape($label) . ' <span class="invoicefix-sort-indicator" aria-hidden="true">'
        . $indicator . '</span></a></th>';
}

function invoicefix_render_queue_invoice(int $invoiceId, string $status, bool $exists): string
{
    if (!$exists || $invoiceId <= 0) {
        return '<span class="text-muted">' . invoicefix_escape(InvoiceFixText::get('queue_invoice_missing')) . '</span>';
    }

    $displayStatus = $status !== '' ? $status : InvoiceFixText::get('admin_status_unknown');

    return '<a href="invoices.php?action=edit&id=' . $invoiceId . '">#' . $invoiceId . '</a>'
        . '<br><span class="text-muted">' . invoicefix_escape($displayStatus) . '</span>';
}

function invoicefix_render_queue_state(string $state): string
{
    $settings = [
        InvoiceFixWorkQueue::FILTER_DRAFT => ['info', 'queue_state_draft'],
        InvoiceFixWorkQueue::FILTER_REVIEW => ['warning', 'queue_state_review'],
        InvoiceFixWorkQueue::FILTER_COMPLETED => ['success', 'queue_state_completed'],
        InvoiceFixWorkQueue::FILTER_ATTENTION => ['danger', 'queue_state_attention'],
    ];

    [$badge, $textKey] = $settings[$state] ?? $settings[InvoiceFixWorkQueue::FILTER_ATTENTION];

    return '<span class="label label-' . $badge . '">' . invoicefix_escape(InvoiceFixText::get($textKey)) . '</span>';
}


function invoicefix_render_queue_follow_up(object $row, string $workflowState): string
{
    $ownerId = (int) ($row->follow_up_admin_id ?? 0);
    $dueDate = trim((string) ($row->follow_up_due_date ?? ''));
    if ($ownerId <= 0 && $dueDate === '') {
        return '';
    }

    $parts = [];
    if ($ownerId > 0) {
        $first = trim((string) ($row->follow_up_admin_firstname ?? ''));
        $last = trim((string) ($row->follow_up_admin_lastname ?? ''));
        $username = trim((string) ($row->follow_up_admin_username ?? ''));
        $owner = trim($first . ' ' . $last);
        if ($owner === '') {
            $owner = $username !== '' ? $username : '#' . $ownerId;
        }
        $parts[] = InvoiceFixText::get('queue_follow_up_owner', ['admin' => $owner]);
    }

    if ($dueDate !== '') {
        $parts[] = InvoiceFixText::get('queue_follow_up_due', ['date' => $dueDate]);
    }

    $html = '<div class="invoicefix-queue-follow-up text-muted">' . invoicefix_escape(implode(' · ', $parts));
    if (InvoiceFixFollowUp::isOverdue($dueDate, $workflowState)) {
        $html .= ' <span class="label label-danger invoicefix-follow-up-overdue">'
            . invoicefix_escape(InvoiceFixText::get('followup_status_overdue')) . '</span>';
    }

    return $html . '</div>';
}

function invoicefix_render_queue_action(int $invoiceId, bool $exists, string $label): string
{
    if (!$exists || $invoiceId <= 0) {
        return '<span class="btn btn-default btn-sm disabled" aria-disabled="true">' . invoicefix_escape($label) . '</span>';
    }

    return '<a class="btn btn-default btn-sm" href="invoices.php?action=edit&id=' . $invoiceId . '">' . invoicefix_escape($label) . '</a>';
}

/**
 * @param array{filter:string,page:int,per_page:int,total:int,total_pages:int,rows:array<int,object>,search:string,date_from:string,date_to:string,has_criteria:bool,sort:string,direction:string} $queue
 */
function invoicefix_render_queue_pagination(string $moduleLink, array $queue): string
{
    $total = (int) $queue['total'];
    $page = (int) $queue['page'];
    $perPage = (int) $queue['per_page'];
    $totalPages = (int) $queue['total_pages'];
    $filter = (string) $queue['filter'];

    if ($total === 0) {
        return '';
    }

    $first = (($page - 1) * $perPage) + 1;
    $last = min($total, $page * $perPage);
    $html = '<div class="panel-footer invoicefix-queue-footer">';
    $html .= '<span class="text-muted">' . invoicefix_escape(InvoiceFixText::get('queue_pagination_summary', [
        'first' => $first,
        'last' => $last,
        'total' => $total,
    ])) . '</span>';

    if ($totalPages > 1) {
        $html .= '<div class="btn-group">';
        if ($page > 1) {
            $previousUrl = invoicefix_module_url($moduleLink, [
                'queue_filter' => $filter,
                'queue_page' => $page - 1,
            ] + invoicefix_queue_criteria_parameters($queue) + invoicefix_queue_sort_parameters($queue));
            $html .= '<a class="btn btn-default btn-sm" href="' . invoicefix_escape($previousUrl) . '">' . invoicefix_escape(InvoiceFixText::get('queue_previous')) . '</a>';
        }
        if ($page < $totalPages) {
            $nextUrl = invoicefix_module_url($moduleLink, [
                'queue_filter' => $filter,
                'queue_page' => $page + 1,
            ] + invoicefix_queue_criteria_parameters($queue) + invoicefix_queue_sort_parameters($queue));
            $html .= '<a class="btn btn-default btn-sm" href="' . invoicefix_escape($nextUrl) . '">' . invoicefix_escape(InvoiceFixText::get('queue_next')) . '</a>';
        }
        $html .= '</div>';
    }

    return $html . '</div>';
}

function invoicefix_module_url(string $moduleLink, array $parameters): string
{
    $separator = str_contains($moduleLink, '?') ? '&' : '?';

    return $moduleLink . $separator . http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
}


/**
 * @param array<string,mixed>|null $source
 * @return array<string,string>
 */
function invoicefix_queue_criteria_parameters(?array $source = null): array
{
    $source = $source ?? $_GET;
    $search = InvoiceFixWorkQueue::normalizeSearch((string) ($source['search'] ?? $source['queue_search'] ?? ''));
    $dateFrom = InvoiceFixWorkQueue::normalizeDate((string) ($source['date_from'] ?? $source['queue_date_from'] ?? ''));
    $dateTo = InvoiceFixWorkQueue::normalizeDate((string) ($source['date_to'] ?? $source['queue_date_to'] ?? ''));

    $parameters = [];
    if ($search !== '') {
        $parameters['queue_search'] = $search;
    }
    if ($dateFrom !== '') {
        $parameters['queue_date_from'] = $dateFrom;
    }
    if ($dateTo !== '') {
        $parameters['queue_date_to'] = $dateTo;
    }

    return $parameters;
}


/**
 * @param array<string,mixed>|null $source
 * @return array{queue_sort:string,queue_direction:string}
 */
function invoicefix_queue_sort_parameters(?array $source = null): array
{
    $source = $source ?? $_GET;

    return [
        'queue_sort' => InvoiceFixWorkQueue::normalizeSort((string) ($source['sort'] ?? $source['queue_sort'] ?? InvoiceFixWorkQueue::SORT_CREATED)),
        'queue_direction' => InvoiceFixWorkQueue::normalizeDirection((string) ($source['direction'] ?? $source['queue_direction'] ?? InvoiceFixWorkQueue::DIRECTION_DESC)),
    ];
}

/** @return array<string,string|int> */
function invoicefix_queue_navigation_parameters(?array $source = null): array
{
    $source = $source ?? $_GET;

    return [
        'queue_filter' => InvoiceFixWorkQueue::normalizeFilter((string) ($source['queue_filter'] ?? 'all')),
        'queue_page' => max(1, (int) ($source['queue_page'] ?? 1)),
    ] + invoicefix_queue_criteria_parameters($source) + invoicefix_queue_sort_parameters($source);
}


function invoicefix_render_details_action(
    string $moduleLink,
    int $logId,
    string $queueFilter,
    int $queuePage
): string {
    $label = InvoiceFixText::get('queue_details');
    if ($logId <= 0) {
        return '<span class="btn btn-default btn-sm disabled" aria-disabled="true">' . invoicefix_escape($label) . '</span>';
    }

    $url = invoicefix_module_url($moduleLink, [
        'details_log_id' => $logId,
        'queue_filter' => InvoiceFixWorkQueue::normalizeFilter($queueFilter),
        'queue_page' => max(1, $queuePage),
    ] + invoicefix_queue_criteria_parameters() + invoicefix_queue_sort_parameters());

    return '<a class="btn btn-default btn-sm" href="' . invoicefix_escape($url) . '">' . invoicefix_escape($label) . '</a>';
}

/**
 * @param array{log:object,source:object|null,replacement:object|null,admin:object|null,note_admin:object|null,follow_up_admin:object|null,follow_up_updated_by_admin:object|null,active_admins:array<int,object>,workflow_state:string} $details
 */
function invoicefix_render_details(string $moduleLink, array $details): string
{
    $queueNavigation = invoicefix_queue_navigation_parameters();
    $filter = (string) $queueNavigation['queue_filter'];
    $page = (int) $queueNavigation['queue_page'];
    $queueCriteria = invoicefix_queue_criteria_parameters() + invoicefix_queue_sort_parameters();
    $backUrl = invoicefix_module_url($moduleLink, $queueNavigation);

    $log = $details['log'];
    $source = $details['source'];
    $replacement = $details['replacement'];
    $admin = $details['admin'];
    $noteAdmin = $details['note_admin'];
    $followUpAdmin = $details['follow_up_admin'];
    $followUpUpdatedByAdmin = $details['follow_up_updated_by_admin'];
    $activeAdmins = $details['active_admins'];
    $workflowState = (string) $details['workflow_state'];
    $sourceId = (int) ($log->source_invoice_id ?? 0);
    $replacementId = (int) ($log->draft_invoice_id ?? 0);

    $html = '<div class="invoicefix-details">';
    $html .= '<div class="invoicefix-details-heading">';
    $html .= '<div><h3>' . invoicefix_escape(InvoiceFixText::get('details_title')) . '</h3>';
    $html .= '<p class="text-muted">' . invoicefix_escape(InvoiceFixText::get('details_intro')) . '</p></div>';
    $html .= '<a class="btn btn-default" href="' . invoicefix_escape($backUrl) . '">'
        . invoicefix_escape(InvoiceFixText::get('details_back_queue')) . '</a>';
    $html .= '</div>';

    $html .= '<div class="alert alert-info"><strong>' . invoicefix_escape(InvoiceFixText::get('details_read_only_title')) . '</strong> '
        . invoicefix_escape(InvoiceFixText::get('details_read_only_text')) . '</div>';

    $html .= '<div class="row invoicefix-details-invoices">';
    $html .= invoicefix_render_details_invoice_card(
        InvoiceFixText::get('details_original_heading', ['invoice_id' => $sourceId]),
        $sourceId,
        $source
    );
    $html .= invoicefix_render_details_invoice_card(
        InvoiceFixText::get('details_replacement_heading', ['invoice_id' => $replacementId]),
        $replacementId,
        $replacement
    );
    $html .= '</div>';

    $html .= '<div class="panel panel-default invoicefix-details-panel">';
    $html .= '<div class="panel-heading"><strong>' . invoicefix_escape(InvoiceFixText::get('details_audit_title')) . '</strong></div>';
    $html .= '<div class="table-responsive"><table class="table table-bordered invoicefix-details-table"><tbody>';
    $html .= invoicefix_render_details_row(InvoiceFixText::get('details_workflow_status'), invoicefix_render_queue_state($workflowState), true);
    $html .= invoicefix_render_details_row(InvoiceFixText::get('details_created_at'), (string) ($log->created_at ?? ''));
    $html .= invoicefix_render_details_row(InvoiceFixText::get('details_created_by'), invoicefix_details_admin_name($admin, (int) ($log->admin_id ?? 0)));
    $html .= invoicefix_render_details_row(InvoiceFixText::get('details_result'), invoicefix_details_result((string) ($log->result ?? '')));
    $html .= invoicefix_render_details_row(InvoiceFixText::get('details_original_status_at_creation'), (string) ($log->source_status ?? ''));
    $html .= invoicefix_render_details_row(InvoiceFixText::get('details_original_total_at_creation'), invoicefix_format_compare_value($log->source_total ?? '0.00', 'amount'));
    $html .= invoicefix_render_details_row(InvoiceFixText::get('details_module_message'), nl2br(invoicefix_escape(trim((string) ($log->message ?? '')) !== '' ? (string) $log->message : InvoiceFixText::get('details_no_message'))), true);
    $html .= '</tbody></table></div></div>';

    $followUpOwnerId = (int) ($log->follow_up_admin_id ?? 0);
    $followUpDueDate = trim((string) ($log->follow_up_due_date ?? ''));
    $followUpUpdatedAt = trim((string) ($log->follow_up_updated_at ?? ''));
    $followUpUpdatedById = (int) ($log->follow_up_updated_by_admin_id ?? 0);
    $followUpAction = invoicefix_module_url($moduleLink, ['action' => 'save_reissue_follow_up']);
    $followUpToken = function_exists('generate_token') ? (string) generate_token('plain') : '';

    $html .= '<div class="panel panel-default invoicefix-follow-up-panel">';
    $html .= '<div class="panel-heading"><strong>' . invoicefix_escape(InvoiceFixText::get('followup_panel_title')) . '</strong></div>';
    $html .= '<div class="panel-body">';
    $html .= '<p class="text-muted">' . invoicefix_escape(InvoiceFixText::get('followup_panel_intro')) . '</p>';
    if ($followUpUpdatedAt !== '') {
        $html .= '<p class="small text-muted invoicefix-follow-up-meta">' . invoicefix_escape(InvoiceFixText::get('followup_updated_meta', [
            'admin' => invoicefix_details_admin_name($followUpUpdatedByAdmin, $followUpUpdatedById),
            'date' => $followUpUpdatedAt,
        ])) . '</p>';
    }
    if (InvoiceFixFollowUp::isOverdue($followUpDueDate, $workflowState)) {
        $html .= '<div class="alert alert-warning invoicefix-follow-up-alert">'
            . invoicefix_escape(InvoiceFixText::get('followup_overdue_notice', ['date' => $followUpDueDate])) . '</div>';
    }
    $html .= '<form method="post" action="' . invoicefix_escape($followUpAction) . '">';
    $html .= '<input type="hidden" name="token" value="' . invoicefix_escape($followUpToken) . '">';
    $html .= '<input type="hidden" name="log_id" value="' . (int) ($log->id ?? 0) . '">';
    foreach ($queueNavigation as $name => $value) {
        $html .= '<input type="hidden" name="' . invoicefix_escape((string) $name) . '" value="' . invoicefix_escape((string) $value) . '">';
    }
    $html .= '<div class="row"><div class="col-md-6"><div class="form-group">';
    $html .= '<label for="invoicefixFollowUpOwner">' . invoicefix_escape(InvoiceFixText::get('followup_owner_label')) . '</label>';
    $html .= '<select class="form-control" id="invoicefixFollowUpOwner" name="follow_up_admin_id">';
    $html .= '<option value="0">' . invoicefix_escape(InvoiceFixText::get('followup_unassigned')) . '</option>';
    $activeAdminIds = [];
    foreach ($activeAdmins as $availableAdmin) {
        $availableAdminId = (int) ($availableAdmin->id ?? 0);
        if ($availableAdminId <= 0) {
            continue;
        }
        $activeAdminIds[] = $availableAdminId;
        $selected = $availableAdminId === $followUpOwnerId ? ' selected' : '';
        $html .= '<option value="' . $availableAdminId . '"' . $selected . '>'
            . invoicefix_escape(invoicefix_details_admin_name($availableAdmin, $availableAdminId)) . '</option>';
    }
    if ($followUpOwnerId > 0 && !in_array($followUpOwnerId, $activeAdminIds, true)) {
        $html .= '<option value="' . $followUpOwnerId . '" selected>'
            . invoicefix_escape(InvoiceFixText::get('followup_missing_owner', ['admin_id' => $followUpOwnerId])) . '</option>';
    }
    $html .= '</select></div></div>';
    $html .= '<div class="col-md-6"><div class="form-group">';
    $html .= '<label for="invoicefixFollowUpDue">' . invoicefix_escape(InvoiceFixText::get('followup_due_date_label')) . '</label>';
    $html .= '<input type="date" class="form-control" id="invoicefixFollowUpDue" name="follow_up_due_date" value="'
        . invoicefix_escape($followUpDueDate) . '">';
    $html .= '<p class="help-block">' . invoicefix_escape(InvoiceFixText::get('followup_due_date_help')) . '</p>';
    $html .= '</div></div></div>';
    $html .= '<button type="submit" class="btn btn-primary">' . invoicefix_escape(InvoiceFixText::get('followup_save_button')) . '</button>';
    if ($followUpOwnerId > 0 || $followUpDueDate !== '') {
        $html .= ' <span class="text-muted small">' . invoicefix_escape(InvoiceFixText::get('followup_clear_help')) . '</span>';
    }
    $html .= '</form></div></div>';

    $note = trim((string) ($log->internal_note ?? ''));
    $noteUpdatedAt = trim((string) ($log->internal_note_updated_at ?? ''));
    $noteAdminId = (int) ($log->internal_note_admin_id ?? 0);
    $noteAction = invoicefix_module_url($moduleLink, ['action' => 'save_reissue_note']);
    $noteToken = function_exists('generate_token') ? (string) generate_token('plain') : '';

    $html .= '<div class="panel panel-default invoicefix-note-panel">';
    $html .= '<div class="panel-heading"><strong>' . invoicefix_escape(InvoiceFixText::get('note_panel_title')) . '</strong></div>';
    $html .= '<div class="panel-body">';
    $html .= '<p class="text-muted">' . invoicefix_escape(InvoiceFixText::get('note_panel_intro')) . '</p>';
    if ($note !== '' && $noteUpdatedAt !== '') {
        $html .= '<p class="small text-muted invoicefix-note-meta">' . invoicefix_escape(InvoiceFixText::get('note_updated_meta', [
            'admin' => invoicefix_details_admin_name($noteAdmin, $noteAdminId),
            'date' => $noteUpdatedAt,
        ])) . '</p>';
    }
    $html .= '<form method="post" action="' . invoicefix_escape($noteAction) . '">';
    $html .= '<input type="hidden" name="token" value="' . invoicefix_escape($noteToken) . '">';
    $html .= '<input type="hidden" name="log_id" value="' . (int) ($log->id ?? 0) . '">';
    foreach ($queueNavigation as $name => $value) {
        $html .= '<input type="hidden" name="' . invoicefix_escape((string) $name) . '" value="' . invoicefix_escape((string) $value) . '">';
    }
    $html .= '<div class="form-group"><label for="invoicefixInternalNote">' . invoicefix_escape(InvoiceFixText::get('note_field_label')) . '</label>';
    $html .= '<textarea class="form-control" id="invoicefixInternalNote" name="internal_note" rows="5" maxlength="' . InvoiceFixNote::MAX_LENGTH . '" placeholder="'
        . invoicefix_escape(InvoiceFixText::get('note_field_placeholder')) . '">' . invoicefix_escape($note) . '</textarea>';
    $html .= '<p class="help-block">' . invoicefix_escape(InvoiceFixText::get('note_field_help', ['limit' => InvoiceFixNote::MAX_LENGTH])) . '</p></div>';
    $html .= '<button type="submit" class="btn btn-primary">' . invoicefix_escape(InvoiceFixText::get('note_save_button')) . '</button>';
    if ($note !== '') {
        $html .= ' <span class="text-muted small">' . invoicefix_escape(InvoiceFixText::get('note_clear_help')) . '</span>';
    }
    $html .= '</form></div></div>';

    $html .= '<div class="invoicefix-details-actions">';
    if ($source && $replacement) {
        $compareUrl = invoicefix_module_url($moduleLink, [
            'compare_log_id' => (int) ($log->id ?? 0),
            'compare_mode' => 'changes',
            'queue_filter' => $filter,
            'queue_page' => $page,
        ] + $queueCriteria);
        $html .= '<a class="btn btn-default" href="' . invoicefix_escape($compareUrl) . '">'
            . invoicefix_escape(InvoiceFixText::get('details_compare_button')) . '</a>';
    }

    $exportAction = invoicefix_module_url($moduleLink, ['action' => 'export_reissue_record']);
    $token = function_exists('generate_token') ? (string) generate_token('plain') : '';
    $html .= '<form method="post" action="' . invoicefix_escape($exportAction) . '" class="invoicefix-inline-form">';
    $html .= '<input type="hidden" name="token" value="' . invoicefix_escape($token) . '">';
    $html .= '<input type="hidden" name="log_id" value="' . (int) ($log->id ?? 0) . '">';
    $html .= '<input type="hidden" name="queue_filter" value="' . invoicefix_escape($filter) . '">';
    $html .= '<input type="hidden" name="queue_page" value="' . $page . '">';
    foreach ($queueCriteria as $name => $value) {
        $html .= '<input type="hidden" name="' . invoicefix_escape($name) . '" value="' . invoicefix_escape($value) . '">';
    }
    $html .= '<button type="submit" class="btn btn-default">'
        . invoicefix_escape(InvoiceFixText::get('details_export_button')) . '</button>';
    $html .= '</form></div>';

    return $html . '</div>';
}

function invoicefix_render_details_invoice_card(string $heading, int $invoiceId, ?object $invoice): string
{
    $html = '<div class="col-md-6"><div class="panel panel-default">';
    $html .= '<div class="panel-heading"><strong>' . invoicefix_escape($heading) . '</strong></div>';
    $html .= '<div class="panel-body">';

    if (!$invoice) {
        $html .= '<div class="text-muted">' . invoicefix_escape(InvoiceFixText::get('details_invoice_missing')) . '</div>';
        $html .= '</div></div></div>';
        return $html;
    }

    $status = trim((string) ($invoice->status ?? ''));
    $html .= '<div><strong>' . invoicefix_escape(InvoiceFixText::get('details_card_status')) . ':</strong> '
        . invoicefix_escape($status !== '' ? $status : InvoiceFixText::get('admin_status_unknown')) . '</div>';
    $html .= '<div><strong>' . invoicefix_escape(InvoiceFixText::get('details_card_total')) . ':</strong> '
        . invoicefix_escape(invoicefix_format_compare_value($invoice->total ?? '0.00', 'amount')) . '</div>';
    $html .= '<div><strong>' . invoicefix_escape(InvoiceFixText::get('details_card_invoice_date')) . ':</strong> '
        . invoicefix_escape((string) ($invoice->date ?? '')) . '</div>';
    $html .= '<div><strong>' . invoicefix_escape(InvoiceFixText::get('details_card_due_date')) . ':</strong> '
        . invoicefix_escape((string) ($invoice->duedate ?? '')) . '</div>';

    $datePaid = trim((string) ($invoice->datepaid ?? ''));
    if ($datePaid !== '' && $datePaid !== '0000-00-00 00:00:00') {
        $html .= '<div><strong>' . invoicefix_escape(InvoiceFixText::get('details_card_paid_date')) . ':</strong> '
            . invoicefix_escape($datePaid) . '</div>';
    }

    $html .= '<div class="invoicefix-details-card-action"><a class="btn btn-default btn-sm" href="invoices.php?action=edit&id=' . max(0, $invoiceId) . '">'
        . invoicefix_escape(InvoiceFixText::get('details_open_invoice')) . '</a></div>';
    $html .= '</div></div></div>';

    return $html;
}

function invoicefix_render_details_row(string $label, string $value, bool $valueIsHtml = false): string
{
    if (!$valueIsHtml) {
        $value = invoicefix_escape($value);
    }

    return '<tr><th class="invoicefix-details-label">' . invoicefix_escape($label) . '</th><td>' . $value . '</td></tr>';
}

function invoicefix_details_admin_name(?object $admin, int $adminId): string
{
    if ($admin) {
        $name = trim((string) ($admin->firstname ?? '') . ' ' . (string) ($admin->lastname ?? ''));
        $username = trim((string) ($admin->username ?? ''));
        if ($name !== '' && $username !== '') {
            return InvoiceFixText::get('details_admin_name_username', [
                'name' => $name,
                'username' => $username,
                'admin_id' => $adminId,
            ]);
        }
        if ($name !== '') {
            return InvoiceFixText::get('details_admin_name_id', ['name' => $name, 'admin_id' => $adminId]);
        }
        if ($username !== '') {
            return InvoiceFixText::get('details_admin_username_id', ['username' => $username, 'admin_id' => $adminId]);
        }
    }

    return $adminId > 0
        ? InvoiceFixText::get('details_admin_id_only', ['admin_id' => $adminId])
        : InvoiceFixText::get('details_admin_unknown');
}

function invoicefix_details_result(string $result): string
{
    return strtolower(trim($result)) === 'success'
        ? InvoiceFixText::get('admin_result_success')
        : InvoiceFixText::get('admin_result_warning');
}

function invoicefix_render_compare_action(
    string $moduleLink,
    int $logId,
    bool $enabled,
    string $queueFilter,
    int $queuePage
): string {
    $label = InvoiceFixText::get('queue_compare');
    if (!$enabled || $logId <= 0) {
        return '<span class="btn btn-default btn-sm disabled" aria-disabled="true">' . invoicefix_escape($label) . '</span>';
    }

    $url = invoicefix_module_url($moduleLink, [
        'compare_log_id' => $logId,
        'compare_mode' => 'changes',
        'queue_filter' => InvoiceFixWorkQueue::normalizeFilter($queueFilter),
        'queue_page' => max(1, $queuePage),
    ] + invoicefix_queue_criteria_parameters() + invoicefix_queue_sort_parameters());

    return '<a class="btn btn-default btn-sm" href="' . invoicefix_escape($url) . '">' . invoicefix_escape($label) . '</a>';
}

/**
 * @param array<string,mixed> $comparison
 */
function invoicefix_render_comparison(string $moduleLink, array $comparison): string
{
    $mode = strtolower(trim((string) ($_GET['compare_mode'] ?? 'changes')));
    if (!in_array($mode, ['changes', 'all'], true)) {
        $mode = 'changes';
    }

    $queueNavigation = invoicefix_queue_navigation_parameters();
    $filter = (string) $queueNavigation['queue_filter'];
    $page = (int) $queueNavigation['queue_page'];
    $queueCriteria = invoicefix_queue_criteria_parameters() + invoicefix_queue_sort_parameters();
    $backUrl = invoicefix_module_url($moduleLink, $queueNavigation);
    $baseParameters = [
        'compare_log_id' => (int) $comparison['log_id'],
        'queue_filter' => $filter,
        'queue_page' => $page,
    ] + $queueCriteria;
    $changesUrl = invoicefix_module_url($moduleLink, $baseParameters + ['compare_mode' => 'changes']);
    $allUrl = invoicefix_module_url($moduleLink, $baseParameters + ['compare_mode' => 'all']);

    $sourceId = (int) $comparison['source_invoice_id'];
    $replacementId = (int) $comparison['replacement_invoice_id'];
    $sourceStatus = trim((string) ($comparison['source']->status ?? ''));
    $replacementStatus = trim((string) ($comparison['replacement']->status ?? ''));
    $sourceTotal = $comparison['source']->total ?? '0.00';
    $replacementTotal = $comparison['replacement']->total ?? '0.00';

    $html = '<div class="invoicefix-compare">';
    $html .= '<div class="invoicefix-compare-heading">';
    $html .= '<div><h3>' . invoicefix_escape(InvoiceFixText::get('compare_title')) . '</h3>';
    $html .= '<p class="text-muted">' . invoicefix_escape(InvoiceFixText::get('compare_intro')) . '</p></div>';
    $html .= '<a class="btn btn-default" href="' . invoicefix_escape($backUrl) . '">'
        . invoicefix_escape(InvoiceFixText::get('compare_back_queue')) . '</a>';
    $html .= '</div>';

    $html .= '<div class="alert alert-info"><strong>' . invoicefix_escape(InvoiceFixText::get('compare_read_only_title')) . '</strong> '
        . invoicefix_escape(InvoiceFixText::get('compare_read_only_text')) . '</div>';

    $html .= '<div class="row invoicefix-compare-invoices">';
    $html .= invoicefix_render_compare_invoice_card(
        InvoiceFixText::get('compare_original_heading', ['invoice_id' => $sourceId]),
        $sourceId,
        $sourceStatus,
        $sourceTotal
    );
    $html .= invoicefix_render_compare_invoice_card(
        InvoiceFixText::get('compare_replacement_heading', ['invoice_id' => $replacementId]),
        $replacementId,
        $replacementStatus,
        $replacementTotal
    );
    $html .= '</div>';

    $html .= '<div class="invoicefix-compare-summary">';
    $html .= '<span class="label label-default">' . invoicefix_escape(InvoiceFixText::get('compare_header_change_summary', [
        'count' => (int) $comparison['header_change_count'],
    ])) . '</span> ';
    $html .= '<span class="label label-default">' . invoicefix_escape(InvoiceFixText::get('compare_item_change_summary', [
        'count' => (int) $comparison['item_change_count'],
    ])) . '</span> ';
    $html .= '<span class="text-muted">' . invoicefix_escape(InvoiceFixText::get('compare_item_count_summary', [
        'original' => (int) $comparison['original_item_count'],
        'replacement' => (int) $comparison['replacement_item_count'],
    ])) . '</span>';
    $html .= '<div class="btn-group pull-right invoicefix-compare-mode" role="group" aria-label="'
        . invoicefix_escape(InvoiceFixText::get('compare_mode_aria')) . '">';
    $html .= '<a class="btn btn-sm ' . ($mode === 'changes' ? 'btn-primary' : 'btn-default') . '" href="'
        . invoicefix_escape($changesUrl) . '">' . invoicefix_escape(InvoiceFixText::get('compare_mode_changes')) . '</a>';
    $html .= '<a class="btn btn-sm ' . ($mode === 'all' ? 'btn-primary' : 'btn-default') . '" href="'
        . invoicefix_escape($allUrl) . '">' . invoicefix_escape(InvoiceFixText::get('compare_mode_all')) . '</a>';
    $html .= '</div><div class="clearfix"></div></div>';

    $html .= invoicefix_render_header_comparison($comparison['header_rows'], $mode);
    $html .= invoicefix_render_item_comparison($comparison['item_rows'], $mode);
    $html .= '</div>';

    return $html;
}

function invoicefix_render_compare_invoice_card(string $heading, int $invoiceId, string $status, $total): string
{
    $status = $status !== '' ? $status : InvoiceFixText::get('admin_status_unknown');

    $html = '<div class="col-md-6"><div class="panel panel-default">';
    $html .= '<div class="panel-heading"><strong>' . invoicefix_escape($heading) . '</strong></div>';
    $html .= '<div class="panel-body">';
    $html .= '<div><strong>' . invoicefix_escape(InvoiceFixText::get('compare_card_status')) . ':</strong> '
        . invoicefix_escape($status) . '</div>';
    $html .= '<div><strong>' . invoicefix_escape(InvoiceFixText::get('compare_card_total')) . ':</strong> '
        . invoicefix_escape(invoicefix_format_compare_value($total, 'amount')) . '</div>';
    $html .= '<div class="invoicefix-compare-card-action"><a class="btn btn-default btn-sm" href="invoices.php?action=edit&id=' . $invoiceId . '">'
        . invoicefix_escape(InvoiceFixText::get('compare_open_invoice')) . '</a></div>';
    $html .= '</div></div></div>';

    return $html;
}

/** @param array<int,array<string,mixed>> $rows */
function invoicefix_render_header_comparison(array $rows, string $mode): string
{
    $visibleRows = array_values(array_filter($rows, static function (array $row) use ($mode): bool {
        return $mode === 'all' || !empty($row['changed']);
    }));

    $html = '<div class="panel panel-default invoicefix-compare-panel">';
    $html .= '<div class="panel-heading"><strong>' . invoicefix_escape(InvoiceFixText::get('compare_details_title')) . '</strong></div>';
    $html .= '<div class="table-responsive"><table class="table table-bordered table-striped invoicefix-compare-table">';
    $html .= '<thead><tr><th>' . invoicefix_escape(InvoiceFixText::get('compare_table_field')) . '</th>';
    $html .= '<th>' . invoicefix_escape(InvoiceFixText::get('compare_table_original')) . '</th>';
    $html .= '<th>' . invoicefix_escape(InvoiceFixText::get('compare_table_replacement')) . '</th>';
    $html .= '<th>' . invoicefix_escape(InvoiceFixText::get('compare_table_result')) . '</th></tr></thead><tbody>';

    if ($visibleRows === []) {
        $html .= '<tr><td colspan="4" class="text-center text-muted">'
            . invoicefix_escape(InvoiceFixText::get('compare_no_detail_changes')) . '</td></tr>';
    }

    foreach ($visibleRows as $row) {
        $changed = !empty($row['changed']);
        $html .= '<tr' . ($changed ? ' class="warning"' : '') . '>';
        $html .= '<th>' . invoicefix_escape(InvoiceFixText::get((string) $row['label_key'])) . '</th>';
        $html .= '<td>' . invoicefix_render_compare_value($row['original'] ?? null, (string) ($row['type'] ?? 'text')) . '</td>';
        $html .= '<td>' . invoicefix_render_compare_value($row['replacement'] ?? null, (string) ($row['type'] ?? 'text')) . '</td>';
        $html .= '<td>' . invoicefix_render_compare_result($changed ? 'changed' : 'unchanged') . '</td>';
        $html .= '</tr>';
    }

    return $html . '</tbody></table></div></div>';
}

/** @param array<int,array<string,mixed>> $rows */
function invoicefix_render_item_comparison(array $rows, string $mode): string
{
    $visibleRows = array_values(array_filter($rows, static function (array $row) use ($mode): bool {
        return $mode === 'all' || (string) ($row['state'] ?? '') !== 'unchanged';
    }));

    $html = '<div class="panel panel-default invoicefix-compare-panel">';
    $html .= '<div class="panel-heading"><strong>' . invoicefix_escape(InvoiceFixText::get('compare_items_title')) . '</strong></div>';
    $html .= '<div class="table-responsive"><table class="table table-bordered invoicefix-compare-table invoicefix-item-compare-table">';
    $html .= '<thead><tr><th>' . invoicefix_escape(InvoiceFixText::get('compare_item_line')) . '</th>';
    $html .= '<th>' . invoicefix_escape(InvoiceFixText::get('compare_table_original')) . '</th>';
    $html .= '<th>' . invoicefix_escape(InvoiceFixText::get('compare_table_replacement')) . '</th>';
    $html .= '<th>' . invoicefix_escape(InvoiceFixText::get('compare_table_result')) . '</th></tr></thead><tbody>';

    if ($visibleRows === []) {
        $html .= '<tr><td colspan="4" class="text-center text-muted">'
            . invoicefix_escape(InvoiceFixText::get('compare_no_item_changes')) . '</td></tr>';
    }

    foreach ($visibleRows as $row) {
        $state = (string) ($row['state'] ?? 'changed');
        $class = $state === 'added' ? 'success' : ($state === 'removed' ? 'danger' : ($state === 'changed' ? 'warning' : ''));
        $originalPosition = $row['original_position'] ?? null;
        $replacementPosition = $row['replacement_position'] ?? null;
        $position = invoicefix_compare_position($originalPosition, $replacementPosition);

        $html .= '<tr' . ($class !== '' ? ' class="' . $class . '"' : '') . '>';
        $html .= '<th>' . invoicefix_escape($position) . '</th>';
        $html .= '<td>' . invoicefix_render_compare_item($row['original'] ?? null) . '</td>';
        $html .= '<td>' . invoicefix_render_compare_item($row['replacement'] ?? null) . '</td>';
        $html .= '<td>' . invoicefix_render_compare_result($state) . '</td>';
        $html .= '</tr>';
    }

    return $html . '</tbody></table></div></div>';
}

function invoicefix_compare_position($originalPosition, $replacementPosition): string
{
    if ($originalPosition !== null && $replacementPosition !== null) {
        if ((int) $originalPosition === (int) $replacementPosition) {
            return '#' . (int) $originalPosition;
        }
        return '#' . (int) $originalPosition . ' → #' . (int) $replacementPosition;
    }
    if ($originalPosition !== null) {
        return '#' . (int) $originalPosition;
    }
    if ($replacementPosition !== null) {
        return '#' . (int) $replacementPosition;
    }

    return InvoiceFixText::get('compare_blank_value');
}

function invoicefix_render_compare_result(string $state): string
{
    $settings = [
        'changed' => ['warning', 'compare_result_changed'],
        'unchanged' => ['default', 'compare_result_unchanged'],
        'added' => ['success', 'compare_result_added'],
        'removed' => ['danger', 'compare_result_removed'],
    ];
    [$class, $key] = $settings[$state] ?? $settings['changed'];

    return '<span class="label label-' . $class . '">' . invoicefix_escape(InvoiceFixText::get($key)) . '</span>';
}

function invoicefix_render_compare_value($value, string $type): string
{
    $formatted = invoicefix_format_compare_value($value, $type);
    if ($formatted === '') {
        return '<span class="text-muted">' . invoicefix_escape(InvoiceFixText::get('compare_blank_value')) . '</span>';
    }

    if ($type === 'multiline') {
        return nl2br(invoicefix_escape($formatted));
    }

    return invoicefix_escape($formatted);
}

function invoicefix_format_compare_value($value, string $type): string
{
    if ($value === null) {
        return '';
    }

    if ($type === 'amount') {
        return number_format((float) $value, 2, '.', ',');
    }
    if ($type === 'rate') {
        return rtrim(rtrim(number_format((float) $value, 3, '.', ''), '0'), '.') . '%';
    }
    if ($type === 'boolean') {
        return (bool) $value ? InvoiceFixText::get('compare_yes') : InvoiceFixText::get('compare_no');
    }

    return trim(str_replace(["\r\n", "\r"], "\n", (string) $value));
}

function invoicefix_render_compare_item($item): string
{
    if ($item === null) {
        return '<span class="text-muted">' . invoicefix_escape(InvoiceFixText::get('compare_blank_value')) . '</span>';
    }

    $value = static function ($object, string $field) {
        if (is_array($object)) {
            return $object[$field] ?? null;
        }
        return property_exists($object, $field) ? $object->{$field} : null;
    };

    $description = trim((string) $value($item, 'description'));
    $amount = invoicefix_format_compare_value($value($item, 'amount'), 'amount');
    $taxed = (bool) $value($item, 'taxed');
    $type = trim((string) $value($item, 'type'));
    $relid = (int) $value($item, 'relid');
    $duedate = trim((string) $value($item, 'duedate'));
    $paymentMethod = trim((string) $value($item, 'paymentmethod'));
    $notes = trim((string) $value($item, 'notes'));

    $html = '<div class="invoicefix-item-description">'
        . ($description !== '' ? nl2br(invoicefix_escape($description)) : '<span class="text-muted">' . invoicefix_escape(InvoiceFixText::get('compare_blank_value')) . '</span>')
        . '</div>';
    $html .= '<div class="text-muted invoicefix-item-meta">';
    $html .= invoicefix_escape(InvoiceFixText::get('compare_item_amount', ['amount' => $amount]));
    $html .= ' · ' . invoicefix_escape($taxed ? InvoiceFixText::get('compare_item_taxable') : InvoiceFixText::get('compare_item_not_taxable'));
    $html .= '</div>';

    if ($type !== '' || $relid > 0) {
        $html .= '<div class="text-muted invoicefix-item-meta">' . invoicefix_escape(InvoiceFixText::get('compare_item_relationship', [
            'type' => $type !== '' ? $type : InvoiceFixText::get('compare_item_relationship_unknown'),
            'relid' => $relid,
        ])) . '</div>';
    }
    if ($duedate !== '' && $duedate !== '0000-00-00') {
        $html .= '<div class="text-muted invoicefix-item-meta">' . invoicefix_escape(InvoiceFixText::get('compare_item_due_date', ['date' => $duedate])) . '</div>';
    }
    if ($paymentMethod !== '') {
        $html .= '<div class="text-muted invoicefix-item-meta">' . invoicefix_escape(InvoiceFixText::get('compare_item_payment_method', ['method' => $paymentMethod])) . '</div>';
    }
    if ($notes !== '') {
        $html .= '<div class="text-muted invoicefix-item-meta">' . nl2br(invoicefix_escape(InvoiceFixText::get('compare_item_notes', ['notes' => $notes]))) . '</div>';
    }

    return $html;
}


function invoicefix_render_text_modal(string $moduleLink): string
{
    $fields = InvoiceFixText::editorFields();
    $groups = [];
    foreach ($fields as $key => $field) {
        $group = trim((string) $field['group']);
        if ($group === '') {
            $group = InvoiceFixText::get('admin_text_group_other');
        }
        $groups[$group][$key] = $field;
    }

    $token = function_exists('generate_token') ? (string) generate_token('plain') : '';
    $html = '<div class="modal fade" id="invoicefixTextModal" tabindex="-1" role="dialog" aria-hidden="true">';
    $html .= '<div class="modal-dialog modal-lg" role="document"><div class="modal-content">';
    $separator = str_contains($moduleLink, '?') ? '&' : '?';
    $formAction = $moduleLink . $separator . 'action=save_text_settings';
    $html .= '<form method="post" action="' . invoicefix_escape($formAction) . '">';
    $html .= '<input type="hidden" name="token" value="' . invoicefix_escape($token) . '">';
    $html .= '<div class="modal-header">';
    $html .= '<button type="button" class="close" data-dismiss="modal" aria-label="'
        . invoicefix_escape(InvoiceFixText::get('admin_text_close_aria'))
        . '"><span aria-hidden="true">&times;</span></button>';
    $html .= '<h4 class="modal-title">' . invoicefix_escape(InvoiceFixText::get('admin_text_modal_title')) . '</h4>';
    $html .= '</div>';
    $html .= '<div class="modal-body invoicefix-text-modal-body">';
    $html .= '<p class="text-muted">' . invoicefix_escape(InvoiceFixText::get('admin_text_modal_intro')) . '</p>';

    foreach ($groups as $groupName => $groupFields) {
        $html .= '<div class="panel panel-default invoicefix-text-group">';
        $html .= '<div class="panel-heading"><strong>' . invoicefix_escape((string) $groupName) . '</strong></div>';
        $html .= '<div class="panel-body">';

        foreach ($groupFields as $key => $field) {
            $fieldId = 'invoicefix_text_' . preg_replace('/[^a-z0-9_\-]/i', '_', (string) $key);
            $html .= '<div class="form-group">';
            $html .= '<label for="' . invoicefix_escape($fieldId) . '">' . invoicefix_escape($field['label']) . '</label>';

            if ($field['type'] === 'textarea') {
                $html .= '<textarea class="form-control" id="' . invoicefix_escape($fieldId) . '" name="invoicefix_text[' . invoicefix_escape((string) $key) . ']" rows="' . (int) $field['rows'] . '">';
                $html .= invoicefix_escape($field['value']);
                $html .= '</textarea>';
            } else {
                $html .= '<input type="text" class="form-control" id="' . invoicefix_escape($fieldId) . '" name="invoicefix_text[' . invoicefix_escape((string) $key) . ']" value="' . invoicefix_escape($field['value']) . '">';
            }

            if ($field['help'] !== '') {
                $html .= '<p class="help-block">' . invoicefix_escape($field['help']) . '</p>';
            }
            $html .= '</div>';
        }

        $html .= '</div></div>';
    }

    $html .= '</div>';
    $html .= '<div class="modal-footer">';
    $html .= '<button type="button" class="btn btn-default" data-dismiss="modal">' . invoicefix_escape(InvoiceFixText::get('admin_text_cancel_button')) . '</button>';
    $html .= '<button type="submit" class="btn btn-danger" name="invoicefix_text_action" value="reset" id="invoicefixResetTextBtn">' . invoicefix_escape(InvoiceFixText::get('admin_text_reset_button')) . '</button>';
    $html .= '<button type="submit" class="btn btn-primary" name="invoicefix_text_action" value="save">' . invoicefix_escape(InvoiceFixText::get('admin_text_save_button')) . '</button>';
    $html .= '</div></form></div></div></div>';

    $resetConfirm = json_encode(InvoiceFixText::get('admin_text_reset_confirm'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    $html .= '<script>(function(){var b=document.getElementById("invoicefixResetTextBtn");if(b){b.addEventListener("click",function(e){if(!window.confirm(' . $resetConfirm . ')){e.preventDefault();}});}})();</script>';

    if ((string) ($_GET['open_text_editor'] ?? '') === '1') {
        $html .= '<script>(function(){function openEditor(){if(window.jQuery){window.jQuery("#invoicefixTextModal").modal("show");}}if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",openEditor);}else{openEditor();}})();</script>';
    }

    return $html;
}

function invoicefix_dispatch(): void
{
    $action = (string) ($_REQUEST['action'] ?? '');

    if ($action === 'save_text_settings') {
        invoicefix_save_text_settings();
    }

    if ($action === 'export_reissue_record') {
        invoicefix_export_reissue_record();
    }

    if ($action === 'export_work_queue') {
        invoicefix_export_work_queue();
    }

    if ($action === 'save_reissue_note') {
        invoicefix_save_reissue_note();
    }

    if ($action === 'save_reissue_follow_up') {
        invoicefix_save_reissue_follow_up();
    }

    if ($action !== 'create_editable_draft') {
        return;
    }

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        invoicefix_json(['ok' => false, 'error' => InvoiceFixText::get('error_post_only')], 405);
    }

    check_token('WHMCS.admin.default');

    try {
        $admin = InvoiceFixService::adminContext();

        if (!InvoiceFixService::roleHasModuleAccess((int) $admin['role_id'])) {
            throw new RuntimeException(InvoiceFixText::get('error_role_access'));
        }

        if (!InvoiceFixService::hasRequiredInvoicePermissions($admin['permissions'])) {
            throw new RuntimeException(InvoiceFixText::get('error_required_permissions'));
        }

        $sourceInvoiceId = (int) ($_POST['invoiceid'] ?? 0);
        $created = InvoiceFixService::createEditableDraft($sourceInvoiceId, $admin);
        $draftId = (int) $created['draft_invoice_id'];

        invoicefix_json([
            'ok' => true,
            'source_invoice_id' => (int) $created['source_invoice_id'],
            'draft_invoice_id' => $draftId,
            'source_status' => (string) $created['source_status'],
            'warning' => (string) $created['warning'],
            'redirect' => 'invoices.php?action=edit&id=' . $draftId,
        ]);
    } catch (Throwable $e) {
        invoicefix_json(['ok' => false, 'error' => $e->getMessage()], 400);
    }
}



function invoicefix_save_reissue_follow_up(): void
{
    $logId = max(0, (int) ($_POST['log_id'] ?? 0));
    $navigation = invoicefix_queue_navigation_parameters($_POST);

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        invoicefix_set_flash('error', InvoiceFixText::get('error_post_only'));
        invoicefix_redirect_to_module(['details_log_id' => $logId] + $navigation);
    }

    try {
        InvoiceFixUpgrade::ensureCurrent();
        check_token('WHMCS.admin.default');
        $admin = InvoiceFixService::adminContext();

        if (!InvoiceFixService::roleHasModuleAccess((int) $admin['role_id'])) {
            throw new RuntimeException(InvoiceFixText::get('error_role_access'));
        }

        if (!InvoiceFixService::hasRequiredInvoicePermissions($admin['permissions'])) {
            throw new RuntimeException(InvoiceFixText::get('error_required_permissions'));
        }

        $ownerAdminId = max(0, (int) ($_POST['follow_up_admin_id'] ?? 0));
        $dueDate = trim((string) ($_POST['follow_up_due_date'] ?? ''));
        InvoiceFixFollowUp::save($logId, $ownerAdminId, $dueDate, (int) $admin['admin_id']);

        invoicefix_set_flash('success', InvoiceFixText::get(
            $ownerAdminId > 0 || $dueDate !== '' ? 'followup_saved' : 'followup_cleared'
        ));
    } catch (Throwable $e) {
        invoicefix_set_flash('error', InvoiceFixText::get('followup_save_error', [
            'details' => $e->getMessage(),
        ]));
    }

    invoicefix_redirect_to_module(['details_log_id' => $logId] + $navigation);
}


function invoicefix_save_reissue_note(): void
{
    $logId = max(0, (int) ($_POST['log_id'] ?? 0));
    $navigation = invoicefix_queue_navigation_parameters($_POST);

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        invoicefix_set_flash('error', InvoiceFixText::get('error_post_only'));
        invoicefix_redirect_to_module(['details_log_id' => $logId] + $navigation);
    }

    try {
        InvoiceFixUpgrade::ensureCurrent();
        check_token('WHMCS.admin.default');
        $admin = InvoiceFixService::adminContext();

        if (!InvoiceFixService::roleHasModuleAccess((int) $admin['role_id'])) {
            throw new RuntimeException(InvoiceFixText::get('error_role_access'));
        }

        if (!InvoiceFixService::hasRequiredInvoicePermissions($admin['permissions'])) {
            throw new RuntimeException(InvoiceFixText::get('error_required_permissions'));
        }

        InvoiceFixNote::save(
            $logId,
            (string) ($_POST['internal_note'] ?? ''),
            (int) $admin['admin_id']
        );

        $savedNote = trim((string) ($_POST['internal_note'] ?? ''));
        invoicefix_set_flash('success', InvoiceFixText::get(
            $savedNote !== '' ? 'note_saved' : 'note_cleared'
        ));
    } catch (Throwable $e) {
        invoicefix_set_flash('error', InvoiceFixText::get('note_save_error', [
            'details' => $e->getMessage(),
        ]));
    }

    invoicefix_redirect_to_module(['details_log_id' => $logId] + $navigation);
}

function invoicefix_export_work_queue(): void
{
    $queueParameters = invoicefix_queue_navigation_parameters($_POST);
    unset($queueParameters['queue_page']);

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        invoicefix_set_flash('error', InvoiceFixText::get('error_post_only'));
        invoicefix_redirect_to_module($queueParameters);
    }

    try {
        check_token('WHMCS.admin.default');
        $admin = InvoiceFixService::adminContext();

        if (!InvoiceFixService::roleHasModuleAccess((int) $admin['role_id'])) {
            throw new RuntimeException(InvoiceFixText::get('error_role_access'));
        }

        if (!InvoiceFixService::hasRequiredInvoicePermissions($admin['permissions'])) {
            throw new RuntimeException(InvoiceFixText::get('error_required_permissions'));
        }

        $export = InvoiceFixQueueExport::build(
            (string) ($_POST['queue_filter'] ?? InvoiceFixWorkQueue::FILTER_ALL),
            (string) ($_POST['queue_search'] ?? ''),
            (string) ($_POST['queue_date_from'] ?? ''),
            (string) ($_POST['queue_date_to'] ?? ''),
            (string) ($_POST['queue_sort'] ?? InvoiceFixWorkQueue::SORT_CREATED),
            (string) ($_POST['queue_direction'] ?? InvoiceFixWorkQueue::DIRECTION_DESC),
            (int) $admin['admin_id']
        );
        invoicefix_send_csv((string) $export['filename'], $export['rows']);
    } catch (Throwable $e) {
        invoicefix_set_flash('error', InvoiceFixText::get('queue_export_error', [
            'details' => $e->getMessage(),
        ]));
        invoicefix_redirect_to_module($queueParameters);
    }
}

function invoicefix_export_reissue_record(): void
{
    $logId = max(0, (int) ($_POST['log_id'] ?? 0));

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        invoicefix_set_flash('error', InvoiceFixText::get('error_post_only'));
        invoicefix_redirect_to_module(['details_log_id' => $logId]);
    }

    try {
        check_token('WHMCS.admin.default');
        $admin = InvoiceFixService::adminContext();

        if (!InvoiceFixService::roleHasModuleAccess((int) $admin['role_id'])) {
            throw new RuntimeException(InvoiceFixText::get('error_role_access'));
        }

        if (!InvoiceFixService::hasRequiredInvoicePermissions($admin['permissions'])) {
            throw new RuntimeException(InvoiceFixText::get('error_required_permissions'));
        }

        $export = InvoiceFixExport::build($logId);
        invoicefix_send_csv((string) $export['filename'], $export['rows']);
    } catch (Throwable $e) {
        invoicefix_set_flash('error', InvoiceFixText::get('export_error_download', [
            'details' => $e->getMessage(),
        ]));
        invoicefix_redirect_to_module([
            'details_log_id' => $logId,
        ] + invoicefix_queue_navigation_parameters($_POST));
    }
}

/** @param array<int,array<int,string>> $rows */
function invoicefix_send_csv(string $filename, array $rows): void
{
    $filename = preg_replace('/[^a-zA-Z0-9._-]+/', '-', $filename) ?: 'invoicefix-export.csv';
    $filename = trim($filename, '-');
    if (!str_ends_with(strtolower($filename), '.csv')) {
        $filename .= '.csv';
    }

    if (ob_get_length() !== false && ob_get_length() > 0) {
        ob_clean();
    }

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');

    $handle = fopen('php://output', 'wb');
    if ($handle === false) {
        throw new RuntimeException(InvoiceFixText::get('export_error_stream'));
    }

    fwrite($handle, "\xEF\xBB\xBF");
    foreach ($rows as $row) {
        $safeRow = array_map(
            static fn ($cell): string => InvoiceFixExport::safeCell((string) $cell),
            $row
        );
        fputcsv($handle, $safeRow, ',', '"', '\\');
    }

    fclose($handle);
    exit;
}

function invoicefix_save_text_settings(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        invoicefix_set_flash('error', InvoiceFixText::get('error_post_only'));
        invoicefix_redirect_to_module();
    }

    try {
        check_token('WHMCS.admin.default');
        $admin = InvoiceFixService::adminContext();

        if (!InvoiceFixService::roleHasModuleAccess((int) $admin['role_id'])) {
            throw new RuntimeException(InvoiceFixText::get('error_role_access'));
        }

        if (!InvoiceFixService::hasRequiredInvoicePermissions($admin['permissions'])) {
            throw new RuntimeException(InvoiceFixText::get('error_required_permissions'));
        }

        $textAction = (string) ($_POST['invoicefix_text_action'] ?? 'save');
        if ($textAction === 'reset') {
            InvoiceFixText::resetOverrides();
            invoicefix_set_flash('success', InvoiceFixText::get('admin_text_reset'));
        } else {
            $submitted = $_POST['invoicefix_text'] ?? [];
            InvoiceFixText::saveOverrides(is_array($submitted) ? $submitted : []);
            invoicefix_set_flash('success', InvoiceFixText::get('admin_text_saved'));
        }
    } catch (Throwable $e) {
        invoicefix_set_flash('error', InvoiceFixText::get('admin_text_save_error', ['details' => $e->getMessage()]));
    }

    invoicefix_redirect_to_module();
}

function invoicefix_set_flash(string $type, string $message): void
{
    $_SESSION['invoicefix_flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function invoicefix_redirect_to_module(array $parameters = []): void
{
    $url = 'addonmodules.php?module=invoicefix';
    if ($parameters !== []) {
        $url .= '&' . http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
    }

    header('Location: ' . $url);
    exit;
}

function invoicefix_json(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function invoicefix_escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function invoicefix_styles(): string
{
    return '<style>
        .invoicefix-admin{max-width:1400px}
        .invoicefix-panel .table{margin-bottom:0}
        .invoicefix-upgrade-panel{margin-top:20px}
        .invoicefix-upgrade-table{margin-bottom:0}
        .invoicefix-upgrade-table th{width:260px}
        .invoicefix-page-heading{display:flex;align-items:center;justify-content:flex-end;gap:15px;min-height:34px;margin:0 0 16px}
        #contentarea h1 + .invoicefix-admin .invoicefix-page-heading{margin-top:-40px}
        .invoicefix-heading-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-left:auto}
        .invoicefix-text-modal-body{max-height:68vh;overflow-y:auto}
        .invoicefix-text-group:last-child{margin-bottom:0}
        .invoicefix-text-group textarea{resize:vertical}
        .invoicefix-queue-toolbar{display:block}
        .invoicefix-queue-intro{margin:0 0 15px;max-width:none}
        .invoicefix-queue-search{display:flex;align-items:flex-end;gap:10px;flex-wrap:wrap;margin:0 0 15px}
        .invoicefix-queue-search .form-group{margin:0;min-width:160px}
        .invoicefix-queue-search-field{flex:1 1 360px;min-width:240px!important}
        .invoicefix-queue-search-actions{display:flex;gap:8px;align-items:center}
        .invoicefix-queue-summary-heading{display:flex;align-items:baseline;gap:10px;flex-wrap:wrap;margin:0 0 8px}
        .invoicefix-queue-summary{display:flex;gap:10px;flex-wrap:wrap;margin:0 0 15px}
        .invoicefix-summary-card{display:flex;align-items:center;gap:10px;flex:1 1 165px;min-width:145px;padding:11px 13px;border:1px solid #ddd;border-radius:4px;background:#fff;color:inherit;text-decoration:none}
        .invoicefix-summary-card:hover,.invoicefix-summary-card:focus{background:#f5f5f5;color:inherit;text-decoration:none}
        .invoicefix-summary-card.active{border-color:#337ab7;box-shadow:inset 0 0 0 1px #337ab7}
        .invoicefix-summary-count{font-size:22px;font-weight:600;line-height:1}
        .invoicefix-summary-label{line-height:1.2}
        .invoicefix-queue-filter-row{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
        .invoicefix-queue-export{margin-left:auto}
        .invoicefix-queue-table{margin-bottom:0}
        .invoicefix-queue-table td{vertical-align:middle!important}
        .invoicefix-queue-row-stale>td{background:#fcf8e3!important}
        .invoicefix-queue-age{white-space:nowrap}
        .invoicefix-sortable-heading a{color:inherit;text-decoration:none;white-space:nowrap}
        .invoicefix-sortable-heading a:hover,.invoicefix-sortable-heading a:focus{text-decoration:none}
        .invoicefix-sort-indicator{display:inline-block;margin-left:4px;font-size:11px;opacity:.7}
        .invoicefix-queue-actions{white-space:nowrap}
        .invoicefix-queue-footer{display:flex;align-items:center;justify-content:space-between;gap:15px}
        .invoicefix-compare-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:15px;margin:5px 0 15px}
        .invoicefix-compare-heading h3{margin-top:0}
        .invoicefix-compare-heading p{margin-bottom:0}
        .invoicefix-compare-invoices .panel{height:100%}
        .invoicefix-compare-card-action{margin-top:12px}
        .invoicefix-compare-summary{margin:0 0 15px}
        .invoicefix-compare-summary>.label{display:inline-block;font-size:12px;margin-bottom:5px;padding:6px 8px}
        .invoicefix-compare-mode{margin-left:15px}
        .invoicefix-compare-table{margin-bottom:0}
        .invoicefix-compare-table td,.invoicefix-compare-table th{vertical-align:middle!important}
        .invoicefix-compare-table td{white-space:normal;word-break:break-word}
        .invoicefix-item-description{font-weight:600;margin-bottom:5px}
        .invoicefix-item-meta{font-size:12px;margin-top:2px}
        .invoicefix-details-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:15px;margin:5px 0 15px}
        .invoicefix-details-heading h3{margin-top:0}
        .invoicefix-details-heading p{margin-bottom:0}
        .invoicefix-details-invoices .panel{height:100%}
        .invoicefix-details-card-action{margin-top:12px}
        .invoicefix-details-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-top:12px}
        .invoicefix-inline-form{display:inline-block;margin:0}
        .invoicefix-details-panel{margin-top:5px}
        .invoicefix-follow-up-panel{margin-top:15px}
        .invoicefix-follow-up-meta{margin:0 0 10px}
        .invoicefix-follow-up-alert{margin:0 0 12px}
        .invoicefix-queue-follow-up{font-size:12px;line-height:1.4;margin-top:6px}
        .invoicefix-follow-up-overdue{display:inline-block;margin-left:4px;font-size:10px;vertical-align:1px}
        .invoicefix-note-panel{margin-top:15px}
        .invoicefix-note-panel textarea{resize:vertical}
        .invoicefix-note-meta{margin:0 0 10px}
        .invoicefix-details-table{margin-bottom:0}
        .invoicefix-details-table td,.invoicefix-details-table th{vertical-align:middle!important}
        .invoicefix-details-label{width:260px}
        .invoicefix-health-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:15px;margin:5px 0 15px}
        .invoicefix-health-heading h3{margin-top:0}
        .invoicefix-health-heading p{margin-bottom:0}
        .invoicefix-health-summary .panel-body{padding-bottom:10px}
        .invoicefix-health-summary .label{display:inline-block;font-size:12px;margin:0 5px 5px 0;padding:6px 8px}
        .invoicefix-health-generated{margin:8px 0 0}
        .invoicefix-health-table{margin-bottom:0}
        .invoicefix-health-table th:first-child{width:260px}
        .invoicefix-health-table th:nth-child(2){width:110px}
        .invoicefix-health-table td,.invoicefix-health-table th{vertical-align:middle!important}
        @media(max-width:767px){.invoicefix-page-heading,#contentarea h1 + .invoicefix-admin .invoicefix-page-heading{align-items:stretch;flex-direction:column;margin:0 0 15px;min-height:0}.invoicefix-heading-actions{align-self:flex-start;margin-left:0}.invoicefix-heading-actions .btn{flex:1 1 auto}.invoicefix-queue-footer,.invoicefix-compare-heading,.invoicefix-details-heading,.invoicefix-health-heading{align-items:stretch;flex-direction:column}.invoicefix-queue-search{align-items:stretch;flex-direction:column}.invoicefix-queue-search .form-group,.invoicefix-queue-search-field{min-width:0!important;width:100%}.invoicefix-queue-search-actions .btn{flex:1 1 auto}.invoicefix-summary-card{flex-basis:100%}.invoicefix-queue-filter-row{align-items:stretch;flex-direction:column}.invoicefix-queue-export{margin-left:0}.invoicefix-queue-export .btn,.invoicefix-queue-filter-row>.disabled{width:100%}.invoicefix-queue-filters{display:flex;flex-wrap:wrap}.invoicefix-queue-filters>.btn{flex:1 1 auto}.invoicefix-queue-actions{white-space:normal}.invoicefix-queue-actions .btn{display:block;margin:3px 0;width:100%}.invoicefix-details-label,.invoicefix-upgrade-table th,.invoicefix-health-table th:first-child,.invoicefix-health-table th:nth-child(2){width:auto}.invoicefix-compare-mode{float:none!important;margin:10px 0 0}.invoicefix-compare-mode .btn{width:50%}}
    </style>';
}

invoicefix_dispatch();
