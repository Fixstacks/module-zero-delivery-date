<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\DeliveryDate\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Zero\DeliveryDate\Model\PlaceOrder\DeliveryDate;

/**
 * sales_model_service_quote_submit_before: the delivery date and time go
 * from the quote to the order before it is saved, so the order e-mail has
 * them. (A sales_convert_quote fieldset wouldn't do: QuoteManagement merges
 * the converted order through OrderInterface, which drops other fields.)
 */
class CopyToOrder implements ObserverInterface
{
    /**
     * @inheritDoc
     */
    public function execute(Observer $observer): void
    {
        $quote = $observer->getEvent()->getData('quote');
        $order = $observer->getEvent()->getData('order');
        foreach ([DeliveryDate::DATE, DeliveryDate::SLOT] as $field) {
            $order->setData($field, $quote->getData($field) ?: null);
        }
    }
}
