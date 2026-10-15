<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\DeliveryDate\ViewModel;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Zero\DeliveryDate\Model\Config;
use Zero\DeliveryDate\Model\Dates;
use Zero\DeliveryDate\Model\PlaceOrder\DeliveryDate;

/**
 * The checkout field (checkout/delivery-date.phtml): the days and time
 * windows on offer, and what the quote already has (after a failed submit).
 */
class Checkout implements ArgumentInterface
{
    public function __construct(
        private readonly Config $config,
        private readonly Dates $dates,
        private readonly CheckoutSession $checkoutSession
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->config->isEnabled();
    }

    public function isRequired(): bool
    {
        return $this->config->isRequired();
    }

    public function label(): string
    {
        return $this->config->label();
    }

    /**
     * @return array<string, string> YYYY-MM-DD => label
     */
    public function options(): array
    {
        return $this->dates->options();
    }

    /**
     * @return string[]
     */
    public function slots(): array
    {
        return $this->dates->slots();
    }

    public function currentDate(): string
    {
        return (string) $this->checkoutSession->getQuote()->getData(DeliveryDate::DATE);
    }

    public function currentSlot(): string
    {
        return (string) $this->checkoutSession->getQuote()->getData(DeliveryDate::SLOT);
    }
}
