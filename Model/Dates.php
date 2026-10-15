<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\DeliveryDate\Model;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * The delivery days a store offers right now, and how a chosen day reads:
 * from today plus the lead days (one more past the cut-off hour), the
 * configured number of calendar days, minus the excluded weekdays and dates,
 * in the store's time zone. Labels are the locale's full date, "Thursday,
 * October 15, 2026", with the time window after a comma.
 */
class Dates
{
    public function __construct(
        private readonly Config $config,
        private readonly TimezoneInterface $timezone
    ) {
    }

    /**
     * The days on offer: YYYY-MM-DD => label, earliest first.
     *
     * @param \DateTimeImmutable|null $now For tests; the store's current time otherwise.
     * @return array<string, string>
     */
    public function options(?int $storeId = null, ?\DateTimeImmutable $now = null): array
    {
        $timezone = $this->storeTimezone($storeId);
        $now = ($now ?? new \DateTimeImmutable('now', $timezone))->setTimezone($timezone);
        $lead = $this->config->leadDays($storeId);
        $cutoff = $this->config->cutoffHour($storeId);
        if ($cutoff !== null && (int) $now->format('G') >= $cutoff) {
            $lead++;
        }
        $first = $now->setTime(12, 0)->modify(sprintf('+%d days', $lead));
        $excludedWeekdays = $this->config->excludedWeekdays($storeId);
        $excludedDates = $this->config->excludedDates($storeId);
        $options = [];
        for ($i = 0, $days = $this->config->daysOffered($storeId); $i < $days; $i++) {
            $day = $first->modify(sprintf('+%d days', $i));
            if (in_array((int) $day->format('w'), $excludedWeekdays, true)
                || in_array($day->format('Y-m-d'), $excludedDates, true)
            ) {
                continue;
            }
            $options[$day->format('Y-m-d')] = $this->dayLabel($day, $timezone);
        }

        return $options;
    }

    public function isOffered(string $date, ?int $storeId = null): bool
    {
        return isset($this->options($storeId)[$date]);
    }

    /**
     * @return string[]
     */
    public function slots(?int $storeId = null): array
    {
        return $this->config->slots($storeId);
    }

    /**
     * A stored date and time window as shown to people; '' for no date.
     */
    public function label(?string $date, ?string $slot, ?int $storeId = null): string
    {
        if ($date === null || !preg_match('/^\d{4}-\d{2}-\d{2}/', $date)) {
            return '';
        }
        $timezone = $this->storeTimezone($storeId);
        $text = $this->dayLabel(new \DateTimeImmutable(substr($date, 0, 10) . ' 12:00:00', $timezone), $timezone);
        $slot = trim((string) $slot);

        return $slot === '' ? $text : $text . ', ' . $slot;
    }

    private function dayLabel(\DateTimeImmutable $day, \DateTimeZone $timezone): string
    {
        return $this->timezone->formatDateTime($day, \IntlDateFormatter::FULL, \IntlDateFormatter::NONE, null, $timezone);
    }

    private function storeTimezone(?int $storeId): \DateTimeZone
    {
        return new \DateTimeZone($this->timezone->getConfigTimezone(ScopeInterface::SCOPE_STORE, $storeId));
    }
}
