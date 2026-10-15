<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\DeliveryDate\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Stores > Configuration > Zero > Delivery Date (defaults in etc/config.xml).
 */
class Config
{
    private const PATH = 'zero_delivery_date/general/';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::PATH . 'enabled', ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isRequired(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::PATH . 'required', ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function label(?int $storeId = null): string
    {
        $label = trim($this->value('label', $storeId));

        return $label !== '' ? $label : (string) __('Delivery date');
    }

    /**
     * Days from today to the earliest delivery day: 0 today, 1 tomorrow.
     */
    public function leadDays(?int $storeId = null): int
    {
        return max(0, (int) $this->value('lead_days', $storeId));
    }

    /**
     * The hour (0–23, store time) from which orders start a day later; null for no cut-off.
     */
    public function cutoffHour(?int $storeId = null): ?int
    {
        $hour = trim($this->value('cutoff_hour', $storeId));

        return $hour === '' || !is_numeric($hour) ? null : max(0, min(23, (int) $hour));
    }

    /**
     * How many calendar days, from the earliest one, the shopper picks from.
     */
    public function daysOffered(?int $storeId = null): int
    {
        return max(1, (int) $this->value('days_offered', $storeId) ?: 14);
    }

    /**
     * Weekdays without delivery as date('w') numbers: 0 Sunday … 6 Saturday.
     *
     * @return int[]
     */
    public function excludedWeekdays(?int $storeId = null): array
    {
        $days = [];
        foreach (explode(',', $this->value('excluded_weekdays', $storeId)) as $day) {
            if (trim($day) !== '' && is_numeric($day)) {
                $days[] = (int) $day;
            }
        }

        return $days;
    }

    /**
     * Dates without delivery, as YYYY-MM-DD.
     *
     * @return string[]
     */
    public function excludedDates(?int $storeId = null): array
    {
        $dates = [];
        foreach ($this->lines($this->value('excluded_dates', $storeId)) as $date) {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $dates[] = $date;
            }
        }

        return $dates;
    }

    /**
     * The delivery time windows to pick from; none for a date only.
     *
     * @return string[]
     */
    public function slots(?int $storeId = null): array
    {
        return $this->lines($this->value('slots', $storeId));
    }

    private function value(string $field, ?int $storeId): string
    {
        return (string) $this->scopeConfig->getValue(self::PATH . $field, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return string[]
     */
    private function lines(string $text): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', $text) ?: []), 'strlen'));
    }
}
