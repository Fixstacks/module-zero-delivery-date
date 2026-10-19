<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\DeliveryDate\ViewModel;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;
use Zero\DeliveryDate\Model\Config;

/**
 * The product page's delivery estimate (templates/product/eta.phtml): the
 * Delivery Date rules for eta.js, which works the earliest day out in the
 * shopper's browser in the store's time zone (the page is cached; a date
 * printed on it would go stale). The same rules as the checkout's first
 * offered day (Model\Dates::options()).
 */
class Eta implements ArgumentInterface
{
    public function __construct(
        private readonly Config $config,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly TimezoneInterface $timezone,
        private readonly ResolverInterface $localeResolver
    ) {
    }

    /**
     * A physical product in stock, with the estimate switched on.
     */
    public function isShown(?Product $product): bool
    {
        return $product !== null
            && $this->scopeConfig->isSetFlag('zero_delivery_date/eta/enabled', ScopeInterface::SCOPE_STORE)
            && !$product->isVirtual()
            && $product->isSaleable();
    }

    /**
     * @return array<string, mixed>
     */
    public function settings(): array
    {
        return [
            'timeZone' => $this->timezone->getConfigTimezone(ScopeInterface::SCOPE_STORE),
            'locale' => str_replace('_', '-', $this->localeResolver->getLocale()),
            'lead' => $this->config->leadDays(),
            'cutoff' => $this->config->cutoffHour(),
            'days' => $this->config->daysOffered(),
            'noWeekdays' => array_values($this->config->excludedWeekdays()),
            'noDates' => array_values($this->config->excludedDates()),
            'text' => [
                'cutoff' => (string) __('Order within %1 for delivery as early as %2', '{time}', '{date}'),
                'plain' => (string) __('Delivery as early as %1', '{date}'),
                'hours' => (string) __('%1 h %2 min', '{h}', '{m}'),
                'minutes' => (string) __('%1 min', '{m}'),
            ],
        ];
    }
}
