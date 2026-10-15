<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\DeliveryDate\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use PHPUnit\Framework\TestCase;
use Zero\DeliveryDate\Model\Config;
use Zero\DeliveryDate\Model\Dates;

class DatesTest extends TestCase
{
    /** Wednesday 2026-10-14; Sunday excluded, Friday the 16th a holiday, 7 days offered. */
    private const SETTINGS = [
        'lead_days' => '1',
        'cutoff_hour' => '14',
        'days_offered' => '7',
        'excluded_weekdays' => '0',
        'excluded_dates' => "2026-10-16\nnot a date\n",
        'slots' => "9:00–13:00\n\n13:00–18:00",
    ];

    public function testOffersTheWindowWithoutExcludedDays(): void
    {
        $options = $this->dates()->options(null, new \DateTimeImmutable('2026-10-14 10:00:00', new \DateTimeZone('Europe/Kyiv')));

        self::assertSame(['2026-10-15', '2026-10-17', '2026-10-19', '2026-10-20', '2026-10-21'], array_keys($options));
        self::assertSame('Thursday, October 15, 2026', $options['2026-10-15']);
    }

    public function testStartsADayLaterPastTheCutOff(): void
    {
        $options = $this->dates()->options(null, new \DateTimeImmutable('2026-10-14 14:00:00', new \DateTimeZone('Europe/Kyiv')));

        self::assertSame(['2026-10-17', '2026-10-19', '2026-10-20', '2026-10-21', '2026-10-22'], array_keys($options));
    }

    public function testUsesTheStoreTimeZoneForTheCutOff(): void
    {
        // 12:30 UTC is 15:30 in Kyiv: past the cut-off.
        $options = $this->dates()->options(null, new \DateTimeImmutable('2026-10-14 12:30:00', new \DateTimeZone('UTC')));

        self::assertSame('2026-10-17', array_key_first($options));
    }

    public function testLabelsADateWithItsSlot(): void
    {
        $dates = $this->dates();

        self::assertSame('Thursday, October 15, 2026, 9:00–13:00', $dates->label('2026-10-15', '9:00–13:00'));
        self::assertSame('Thursday, October 15, 2026', $dates->label('2026-10-15 00:00:00', null));
        self::assertSame('', $dates->label(null, '9:00–13:00'));
        self::assertSame('', $dates->label('soon', null));
        self::assertSame(['9:00–13:00', '13:00–18:00'], $dates->slots());
    }

    private function dates(): Dates
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturnCallback(
            static fn (string $path) => self::SETTINGS[substr($path, strlen('zero_delivery_date/general/'))] ?? null
        );
        $timezone = $this->createMock(TimezoneInterface::class);
        $timezone->method('getConfigTimezone')->willReturn('Europe/Kyiv');
        $timezone->method('formatDateTime')->willReturnCallback(
            static fn (\DateTimeInterface $date) => $date->format('l, F j, Y')
        );

        return new Dates(new Config($scopeConfig), $timezone);
    }
}
