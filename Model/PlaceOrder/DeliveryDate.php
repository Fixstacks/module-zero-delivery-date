<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\DeliveryDate\Model\PlaceOrder;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Zero\Checkout\Api\PlaceOrderValidatorInterface;
use Zero\DeliveryDate\Model\Config;
use Zero\DeliveryDate\Model\Dates;

/**
 * The checkout's `zero_delivery[date]` and `zero_delivery[slot]` fields
 * (checkout/delivery-date.phtml): the date must be one on offer, the time
 * one of the configured windows. They go on the quote; Magento copies them
 * to the order (etc/fieldset.xml).
 */
class DeliveryDate implements PlaceOrderValidatorInterface
{
    public const DATE = 'zero_delivery_date';
    public const SLOT = 'zero_delivery_slot';

    public function __construct(
        private readonly Config $config,
        private readonly Dates $dates
    ) {
    }

    /**
     * @inheritDoc
     */
    public function validate(Quote $quote, array $request): void
    {
        $storeId = (int) $quote->getStoreId();
        if (!$this->config->isEnabled($storeId)) {
            return;
        }
        $posted = (array) ($request['zero_delivery'] ?? []);
        $date = trim((string) ($posted['date'] ?? ''));
        $slot = trim((string) ($posted['slot'] ?? ''));
        if ($date === '') {
            if ($this->config->isRequired($storeId)) {
                throw new LocalizedException(__('Please pick a delivery date.'));
            }
            $quote->setData(self::DATE, null)->setData(self::SLOT, null);

            return;
        }
        if (!$this->dates->isOffered($date, $storeId)) {
            throw new LocalizedException(__('That delivery date is no longer available. Please pick another.'));
        }
        $slots = $this->dates->slots($storeId);
        if ($slots && !in_array($slot, $slots, true)) {
            throw new LocalizedException(__('Please pick a delivery time.'));
        }
        $quote->setData(self::DATE, $date)->setData(self::SLOT, $slots ? $slot : null);
    }
}
