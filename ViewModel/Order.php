<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\DeliveryDate\ViewModel;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order as OrderModel;
use Zero\DeliveryDate\Model\Dates;
use Zero\DeliveryDate\Model\PlaceOrder\DeliveryDate;

/**
 * An order's delivery date as text, for the order page, the admin order
 * page and the order e-mail.
 */
class Order implements ArgumentInterface
{
    public function __construct(
        private readonly Dates $dates,
        private readonly OrderRepositoryInterface $orderRepository
    ) {
    }

    /**
     * "Thursday, October 15, 2026, 9:00–13:00", or '' when none was picked.
     */
    public function text(?OrderModel $order): string
    {
        if (!$order) {
            return '';
        }

        return $this->dates->label(
            $order->getData(DeliveryDate::DATE),
            $order->getData(DeliveryDate::SLOT),
            (int) $order->getStoreId()
        );
    }

    /**
     * The order of an e-mail that only passed its id.
     */
    public function load(int $orderId): ?OrderModel
    {
        try {
            $order = $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException) {
            return null;
        }

        return $order instanceof OrderModel ? $order : null;
    }
}
