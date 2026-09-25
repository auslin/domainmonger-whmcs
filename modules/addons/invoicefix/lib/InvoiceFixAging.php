<?php

declare(strict_types=1);

namespace InvoiceFix\Module;

if (!defined('WHMCS')) {
    exit('This file cannot be accessed directly');
}

final class InvoiceFixAging
{
    public const DEFAULT_STALE_DAYS = 7;
    public const MIN_STALE_DAYS = 1;
    public const MAX_STALE_DAYS = 3650;

    public static function configuredThreshold(): int
    {
        $value = (int) InvoiceFixService::moduleSetting(
            'work_queue_stale_days',
            (string) self::DEFAULT_STALE_DAYS
        );

        if ($value < self::MIN_STALE_DAYS) {
            return self::DEFAULT_STALE_DAYS;
        }

        return min(self::MAX_STALE_DAYS, $value);
    }

    /**
     * @return array{days:int,stale:bool}
     */
    public static function analyze(string $createdAt, string $state, int $staleDays, ?int $now = null): array
    {
        $staleDays = max(self::MIN_STALE_DAYS, min(self::MAX_STALE_DAYS, $staleDays));
        $timestamp = strtotime($createdAt);
        $now = $now ?? time();
        $days = 0;

        if ($timestamp !== false) {
            $days = max(0, (int) floor(($now - $timestamp) / 86400));
        }

        $unfinished = in_array($state, [
            InvoiceFixWorkQueue::FILTER_DRAFT,
            InvoiceFixWorkQueue::FILTER_REVIEW,
            InvoiceFixWorkQueue::FILTER_ATTENTION,
        ], true);

        return [
            'days' => $days,
            'stale' => $unfinished && $days >= $staleDays,
        ];
    }
}
