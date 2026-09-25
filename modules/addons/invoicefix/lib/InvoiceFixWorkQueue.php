<?php

declare(strict_types=1);

namespace InvoiceFix\Module;

use DateTimeImmutable;
use WHMCS\Database\Capsule;

if (!defined('WHMCS')) {
    exit('This file cannot be accessed directly');
}

require_once __DIR__ . '/InvoiceFixService.php';

final class InvoiceFixWorkQueue
{
    public const FILTER_ALL = 'all';
    public const FILTER_DRAFT = 'draft';
    public const FILTER_REVIEW = 'review';
    public const FILTER_COMPLETED = 'completed';
    public const FILTER_ATTENTION = 'attention';
    public const FILTER_STALE = 'stale';
    public const FILTER_MY_FOLLOW_UPS = 'my_followups';
    public const FILTER_OVERDUE = 'overdue';

    public const SORT_CREATED = 'created';
    public const SORT_ORIGINAL = 'original';
    public const SORT_REPLACEMENT = 'replacement';
    public const SORT_WORKFLOW = 'workflow';

    public const DIRECTION_ASC = 'asc';
    public const DIRECTION_DESC = 'desc';

    /**
     * @return string[]
     */
    public static function filters(): array
    {
        return [
            self::FILTER_ALL,
            self::FILTER_DRAFT,
            self::FILTER_REVIEW,
            self::FILTER_COMPLETED,
            self::FILTER_ATTENTION,
            self::FILTER_STALE,
            self::FILTER_MY_FOLLOW_UPS,
            self::FILTER_OVERDUE,
        ];
    }

    public static function normalizeFilter(string $filter): string
    {
        $filter = strtolower(trim($filter));

        return in_array($filter, self::filters(), true) ? $filter : self::FILTER_ALL;
    }

    /**
     * @return string[]
     */
    public static function sorts(): array
    {
        return [
            self::SORT_CREATED,
            self::SORT_ORIGINAL,
            self::SORT_REPLACEMENT,
            self::SORT_WORKFLOW,
        ];
    }

    public static function normalizeSort(string $sort): string
    {
        $sort = strtolower(trim($sort));

        return in_array($sort, self::sorts(), true) ? $sort : self::SORT_CREATED;
    }

    public static function normalizeDirection(string $direction): string
    {
        $direction = strtolower(trim($direction));

        return $direction === self::DIRECTION_ASC ? self::DIRECTION_ASC : self::DIRECTION_DESC;
    }

    public static function defaultDirection(string $sort): string
    {
        return self::normalizeSort($sort) === self::SORT_CREATED
            ? self::DIRECTION_DESC
            : self::DIRECTION_ASC;
    }

    public static function normalizeSearch(string $search): string
    {
        $search = preg_replace('/\s+/u', ' ', trim($search)) ?? trim($search);
        if (trim($search, "# \t\n\r\0\x0B") === '') {
            return '';
        }

        if (function_exists('mb_substr')) {
            return mb_substr($search, 0, 120);
        }

        return substr($search, 0, 120);
    }

    public static function normalizeDate(string $date): string
    {
        $date = trim($date);
        if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $matches)) {
            return '';
        }

        return checkdate((int) $matches[2], (int) $matches[3], (int) $matches[1]) ? $date : '';
    }

    /**
     * Load one page of the read-only work queue.
     *
     * @return array{rows:array<int,object>,filter:string,page:int,per_page:int,total:int,total_pages:int,search:string,date_from:string,date_to:string,has_criteria:bool,sort:string,direction:string}
     */
    public static function page(
        string $filter,
        int $page,
        int $perPage = 50,
        string $search = '',
        string $dateFrom = '',
        string $dateTo = '',
        string $sort = self::SORT_CREATED,
        string $direction = self::DIRECTION_DESC,
        int $staleDays = 7,
        int $currentAdminId = 0
    ): array {
        $filter = self::normalizeFilter($filter);
        $page = max(1, $page);
        $perPage = max(10, min(100, $perPage));
        $search = self::normalizeSearch($search);
        $dateFrom = self::normalizeDate($dateFrom);
        $dateTo = self::normalizeDate($dateTo);
        $sort = self::normalizeSort($sort);
        $direction = self::normalizeDirection($direction);

        if ($dateFrom !== '' && $dateTo !== '' && $dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        $query = self::baseQuery();

        self::applyFilter($query, $filter, $staleDays, $currentAdminId);
        self::applyCriteria($query, $search, $dateFrom, $dateTo);

        $total = (int) (clone $query)->count('invoicefix_log.id');
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $totalPages);

        self::applyOrdering($query, $sort, $direction);

        $rows = $query
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get()
            ->all();

        foreach ($rows as $row) {
            $row->workflow_state = self::state($row);
        }

        return [
            'rows' => $rows,
            'filter' => $filter,
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => $totalPages,
            'search' => $search,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'has_criteria' => $search !== '' || $dateFrom !== '' || $dateTo !== '',
            'sort' => $sort,
            'direction' => $direction,
        ];
    }


    /**
     * Load all read-only Work Queue rows matching the current filters.
     * Used by exports so the CSV reflects the complete result set, not only
     * the currently displayed page.
     *
     * @return array{rows:array<int,object>,filter:string,total:int,search:string,date_from:string,date_to:string,has_criteria:bool,sort:string,direction:string}
     */
    public static function all(
        string $filter,
        string $search = '',
        string $dateFrom = '',
        string $dateTo = '',
        string $sort = self::SORT_CREATED,
        string $direction = self::DIRECTION_DESC,
        int $staleDays = 7,
        int $currentAdminId = 0
    ): array {
        $filter = self::normalizeFilter($filter);
        $search = self::normalizeSearch($search);
        $dateFrom = self::normalizeDate($dateFrom);
        $dateTo = self::normalizeDate($dateTo);
        $sort = self::normalizeSort($sort);
        $direction = self::normalizeDirection($direction);

        if ($dateFrom !== '' && $dateTo !== '' && $dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        $query = self::baseQuery();
        self::applyFilter($query, $filter, $staleDays, $currentAdminId);
        self::applyCriteria($query, $search, $dateFrom, $dateTo);
        self::applyOrdering($query, $sort, $direction);

        $rows = $query->get()->all();
        foreach ($rows as $row) {
            $row->workflow_state = self::state($row);
        }

        return [
            'rows' => $rows,
            'filter' => $filter,
            'total' => count($rows),
            'search' => $search,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'has_criteria' => $search !== '' || $dateFrom !== '' || $dateTo !== '',
            'sort' => $sort,
            'direction' => $direction,
        ];
    }


    /**
     * Return read-only workflow counts for the current search/date criteria.
     * Counts are independent of the currently selected workflow filter so the
     * dashboard always shows the complete distribution for the criteria.
     *
     * @return array{counts:array<string,int>,search:string,date_from:string,date_to:string,has_criteria:bool}
     */
    public static function summary(string $search = '', string $dateFrom = '', string $dateTo = '', int $staleDays = 7, int $currentAdminId = 0): array
    {
        $search = self::normalizeSearch($search);
        $dateFrom = self::normalizeDate($dateFrom);
        $dateTo = self::normalizeDate($dateTo);

        if ($dateFrom !== '' && $dateTo !== '' && $dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        $counts = [];
        foreach (self::filters() as $filter) {
            $query = self::baseQuery();
            self::applyFilter($query, $filter, $staleDays, $currentAdminId);
            self::applyCriteria($query, $search, $dateFrom, $dateTo);
            $counts[$filter] = (int) $query->count('invoicefix_log.id');
        }

        return [
            'counts' => $counts,
            'search' => $search,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'has_criteria' => $search !== '' || $dateFrom !== '' || $dateTo !== '',
        ];
    }

    public static function state(object $row): string
    {
        $result = strtolower(trim((string) ($row->result ?? '')));
        $sourceExists = (int) ($row->source_exists_id ?? 0) > 0;
        $replacementExists = (int) ($row->replacement_exists_id ?? 0) > 0;
        $sourceStatus = strtolower(trim((string) ($row->source_current_status ?? '')));
        $replacementStatus = strtolower(trim((string) ($row->replacement_current_status ?? '')));

        if ($result !== 'success' || !$sourceExists || !$replacementExists || $sourceStatus === '') {
            return self::FILTER_ATTENTION;
        }

        if ($replacementStatus === 'draft') {
            return $sourceStatus === 'cancelled' ? self::FILTER_ATTENTION : self::FILTER_DRAFT;
        }

        if (in_array($replacementStatus, ['cancelled', 'refunded'], true) || $replacementStatus === '') {
            return self::FILTER_ATTENTION;
        }

        if ($sourceStatus === 'cancelled') {
            return self::FILTER_COMPLETED;
        }

        return self::FILTER_REVIEW;
    }


    private static function baseQuery()
    {
        return Capsule::table(InvoiceFixService::LOG_TABLE . ' as invoicefix_log')
            ->leftJoin('tblinvoices as invoicefix_source', 'invoicefix_source.id', '=', 'invoicefix_log.source_invoice_id')
            ->leftJoin('tblinvoices as invoicefix_replacement', 'invoicefix_replacement.id', '=', 'invoicefix_log.draft_invoice_id')
            ->leftJoin('tblclients as invoicefix_source_client', 'invoicefix_source_client.id', '=', 'invoicefix_source.userid')
            ->leftJoin('tblclients as invoicefix_replacement_client', 'invoicefix_replacement_client.id', '=', 'invoicefix_replacement.userid')
            ->leftJoin('tbladmins as invoicefix_follow_up_admin', 'invoicefix_follow_up_admin.id', '=', 'invoicefix_log.follow_up_admin_id')
            ->select([
                'invoicefix_log.id as log_id',
                'invoicefix_log.source_invoice_id',
                'invoicefix_log.draft_invoice_id',
                'invoicefix_log.admin_id',
                'invoicefix_log.result',
                'invoicefix_log.message',
                'invoicefix_log.created_at',
                'invoicefix_log.follow_up_admin_id',
                'invoicefix_log.follow_up_due_date',
                'invoicefix_log.follow_up_updated_by_admin_id',
                'invoicefix_log.follow_up_updated_at',
                'invoicefix_source.id as source_exists_id',
                'invoicefix_source.invoicenum as source_invoice_number',
                'invoicefix_source.userid as source_user_id',
                'invoicefix_source.status as source_current_status',
                'invoicefix_replacement.id as replacement_exists_id',
                'invoicefix_replacement.invoicenum as replacement_invoice_number',
                'invoicefix_replacement.userid as replacement_user_id',
                'invoicefix_replacement.status as replacement_current_status',
                'invoicefix_source_client.firstname as source_client_firstname',
                'invoicefix_source_client.lastname as source_client_lastname',
                'invoicefix_source_client.companyname as source_client_company',
                'invoicefix_source_client.email as source_client_email',
                'invoicefix_replacement_client.firstname as replacement_client_firstname',
                'invoicefix_replacement_client.lastname as replacement_client_lastname',
                'invoicefix_replacement_client.companyname as replacement_client_company',
                'invoicefix_replacement_client.email as replacement_client_email',
                'invoicefix_follow_up_admin.username as follow_up_admin_username',
                'invoicefix_follow_up_admin.firstname as follow_up_admin_firstname',
                'invoicefix_follow_up_admin.lastname as follow_up_admin_lastname',
            ]);
    }

    private static function applyOrdering($query, string $sort, string $direction): void
    {
        $sort = self::normalizeSort($sort);
        $direction = self::normalizeDirection($direction);
        $sqlDirection = $direction === self::DIRECTION_ASC ? 'ASC' : 'DESC';

        if ($sort === self::SORT_ORIGINAL) {
            $query
                ->orderBy('invoicefix_log.source_invoice_id', $direction)
                ->orderBy('invoicefix_log.id', $direction);
            return;
        }

        if ($sort === self::SORT_REPLACEMENT) {
            $query
                ->orderBy('invoicefix_log.draft_invoice_id', $direction)
                ->orderBy('invoicefix_log.id', $direction);
            return;
        }

        if ($sort === self::SORT_WORKFLOW) {
            $workflowOrder = self::workflowOrderExpression();
            $query
                ->orderByRaw($workflowOrder . ' ' . $sqlDirection)
                ->orderByDesc('invoicefix_log.id');
            return;
        }

        $query
            ->orderBy('invoicefix_log.created_at', $direction)
            ->orderBy('invoicefix_log.id', $direction);
    }

    private static function applyCriteria($query, string $search, string $dateFrom, string $dateTo): void
    {
        if ($search !== '') {
            $needle = trim(ltrim($search, '#'));
            if ($needle !== '') {
                $like = '%' . addcslashes($needle, '\\%_') . '%';

                $query->where(function ($searchQuery) use ($needle, $like): void {
                    if (ctype_digit($needle)) {
                        $invoiceId = (int) $needle;
                        $searchQuery
                            ->where('invoicefix_log.source_invoice_id', $invoiceId)
                            ->orWhere('invoicefix_log.draft_invoice_id', $invoiceId);
                    } else {
                        $searchQuery->whereRaw('1 = 0');
                    }

                    $searchQuery
                        ->orWhere('invoicefix_source.invoicenum', 'like', $like)
                        ->orWhere('invoicefix_replacement.invoicenum', 'like', $like)
                        ->orWhere('invoicefix_source_client.firstname', 'like', $like)
                        ->orWhere('invoicefix_source_client.lastname', 'like', $like)
                        ->orWhereRaw("CONCAT_WS(' ', invoicefix_source_client.firstname, invoicefix_source_client.lastname) LIKE ?", [$like])
                        ->orWhere('invoicefix_source_client.companyname', 'like', $like)
                        ->orWhere('invoicefix_source_client.email', 'like', $like)
                        ->orWhere('invoicefix_replacement_client.firstname', 'like', $like)
                        ->orWhere('invoicefix_replacement_client.lastname', 'like', $like)
                        ->orWhereRaw("CONCAT_WS(' ', invoicefix_replacement_client.firstname, invoicefix_replacement_client.lastname) LIKE ?", [$like])
                        ->orWhere('invoicefix_replacement_client.companyname', 'like', $like)
                        ->orWhere('invoicefix_replacement_client.email', 'like', $like);
                });
            }
        }

        if ($dateFrom !== '') {
            $query->where('invoicefix_log.created_at', '>=', $dateFrom . ' 00:00:00');
        }

        if ($dateTo !== '') {
            $exclusiveEnd = (new DateTimeImmutable($dateTo))->modify('+1 day')->format('Y-m-d 00:00:00');
            $query->where('invoicefix_log.created_at', '<', $exclusiveEnd);
        }
    }

    private static function workflowOrderExpression(): string
    {
        return <<<'SQL'
CASE
    WHEN invoicefix_log.result <> 'success'
      OR invoicefix_source.id IS NULL
      OR invoicefix_replacement.id IS NULL
      OR COALESCE(invoicefix_source.status, '') = ''
      OR COALESCE(invoicefix_replacement.status, '') IN ('', 'Cancelled', 'Refunded')
      OR (invoicefix_replacement.status = 'Draft' AND invoicefix_source.status = 'Cancelled')
        THEN 4
    WHEN invoicefix_replacement.status = 'Draft'
        THEN 1
    WHEN invoicefix_source.status = 'Cancelled'
        THEN 3
    ELSE 2
END
SQL;
    }

    private static function applyFilter($query, string $filter, int $staleDays = 7, int $currentAdminId = 0): void
    {
        if ($filter === self::FILTER_ALL) {
            return;
        }

        if ($filter === self::FILTER_STALE) {
            $staleDays = max(1, min(3650, $staleDays));
            $cutoff = date('Y-m-d H:i:s', time() - ($staleDays * 86400));
            $query
                ->where('invoicefix_log.created_at', '<=', $cutoff)
                ->whereRaw('(' . self::workflowOrderExpression() . ') <> 3');
            return;
        }

        if ($filter === self::FILTER_MY_FOLLOW_UPS) {
            if ($currentAdminId <= 0) {
                $query->whereRaw('1 = 0');
                return;
            }

            $query
                ->where('invoicefix_log.follow_up_admin_id', $currentAdminId)
                ->whereRaw('(' . self::workflowOrderExpression() . ') <> 3');
            return;
        }

        if ($filter === self::FILTER_OVERDUE) {
            $query
                ->whereNotNull('invoicefix_log.follow_up_due_date')
                ->where('invoicefix_log.follow_up_due_date', '<', date('Y-m-d'))
                ->whereRaw('(' . self::workflowOrderExpression() . ') <> 3');
            return;
        }

        if ($filter === self::FILTER_DRAFT) {
            $query
                ->where('invoicefix_log.result', 'success')
                ->whereNotNull('invoicefix_source.id')
                ->whereNotNull('invoicefix_replacement.id')
                ->where('invoicefix_replacement.status', 'Draft')
                ->where('invoicefix_source.status', '!=', 'Cancelled')
                ->where('invoicefix_source.status', '!=', '');
            return;
        }

        if ($filter === self::FILTER_REVIEW) {
            $query
                ->where('invoicefix_log.result', 'success')
                ->whereNotNull('invoicefix_source.id')
                ->whereNotNull('invoicefix_replacement.id')
                ->whereNotIn('invoicefix_replacement.status', ['Draft', 'Cancelled', 'Refunded'])
                ->where('invoicefix_source.status', '!=', 'Cancelled')
                ->where('invoicefix_source.status', '!=', '');
            return;
        }

        if ($filter === self::FILTER_COMPLETED) {
            $query
                ->where('invoicefix_log.result', 'success')
                ->whereNotNull('invoicefix_source.id')
                ->whereNotNull('invoicefix_replacement.id')
                ->whereNotIn('invoicefix_replacement.status', ['Draft', 'Cancelled', 'Refunded'])
                ->where('invoicefix_source.status', 'Cancelled');
            return;
        }

        $query->where(function ($attention): void {
            $attention
                ->where('invoicefix_log.result', '!=', 'success')
                ->orWhereNull('invoicefix_source.id')
                ->orWhereNull('invoicefix_replacement.id')
                ->orWhere('invoicefix_source.status', '')
                ->orWhereIn('invoicefix_replacement.status', ['Cancelled', 'Refunded'])
                ->orWhere('invoicefix_replacement.status', '')
                ->orWhere(function ($draftWithCancelledSource): void {
                    $draftWithCancelledSource
                        ->where('invoicefix_replacement.status', 'Draft')
                        ->where('invoicefix_source.status', 'Cancelled');
                });
        });
    }
}
