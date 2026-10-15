<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\DeliveryDate\Plugin\Email;

use Magento\Sales\Block\Order\Email\Items;

/**
 * Adds the delivery date under the items of the order e-mail. The e-mail's
 * `{{layout handle="sales_email_order_items"}}` renders only its root block,
 * and that block's template prints just the totals child, so the date block
 * (layout/sales_email_order_items.xml) is appended here.
 */
class ItemsDeliveryDate
{
    public function afterToHtml(Items $subject, string $html): string
    {
        return $html . $subject->getChildHtml('zero.delivery.date.email');
    }
}
