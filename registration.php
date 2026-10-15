<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 *
 * Zero_DeliveryDate: a delivery date (and time window) picked in Zero
 * Checkout, kept on the order and shown to the shopper, in the order
 * e-mail, on the admin order page and in the orders grid.
 */

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Zero_DeliveryDate', __DIR__);
