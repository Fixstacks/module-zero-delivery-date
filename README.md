# Zero_DeliveryDate

A delivery date, and optionally a time window, picked in Zero Checkout
and kept on the order.

## Install

Requires Magento Open Source 2.4.7–2.4.9 and PHP 8.2–8.4 with the GD extension built with WebP (`php -r 'var_dump(function_exists("imagewebp"));'` says `true`; Magento's own PHP requirements cover the rest). Without WebP in GD, Zero skips its WebP image copies and serves the originals.

```bash
composer require zerofront/module-zero-delivery-date
bin/magento setup:upgrade
bin/magento setup:di:compile           # production mode
bin/magento setup:static-content:deploy # production mode
bin/magento cache:flush
```

Composer installs `zerofront/module-zero-core`, `zerofront/module-zero-checkout` with it.

Part of [`zerofront/zero`](https://zerofront.dev), which installs the Zero theme and every Zero module at once.

## Storefront

Under the shipping methods: a "Delivery date" select of the days on offer
and, when time windows are configured, a "Delivery time" select. Plain
`<select>`s, so excluded days are simply not listed and nothing needs
JavaScript. "No preference" is the first option unless the field is
required.

The days on offer: from today plus the lead days (one more once the
cut-off hour has passed), the configured number of calendar days, minus
the excluded weekdays and dates — in the store's time zone. Place Order
checks the posted date against that list again
(`Model\PlaceOrder\DeliveryDate`, a `PlaceOrderValidatorInterface` of
Zero_Checkout), so a stale page can't order a day that is gone.

The date is kept on the quote (`quote.zero_delivery_date`,
`zero_delivery_slot`) and copied to the order as it is placed (`Observer\CopyToOrder`, on
`sales_model_service_quote_submit_before`), so the order confirmation
e-mail already has it.

## Where it shows

- My Orders > order: "Delivery date: …" under the order date.
- The order confirmation e-mail, after the items (`Plugin\Email\ItemsDeliveryDate`).
- Sales > Orders > order: a "Delivery" section under the order information.
- Sales > Orders grid: a "Delivery Date" column (sortable, date-range filter).

Dates read as the locale's full date, "Thursday, October 15, 2026", with the
time window after a comma.

## Configuration

Stores > Configuration > Zero > Delivery Date:

| Setting | Meaning |
|---|---|
| Enabled | The field in checkout (default yes) |
| Required | No: "No preference" is offered |
| Field Label | Empty: "Delivery date" |
| Earliest Delivery (Days From Today) | 0 today, 1 tomorrow (default 1) |
| Cut-Off Hour | Orders from this hour on (store time) start one day later; empty for none |
| Days Offered | Calendar days to pick from, from the earliest (default 14) |
| No Delivery On | Weekdays without delivery (default Sunday) |
| Excluded Dates | Holidays, one `YYYY-MM-DD` per line |
| Time Windows | One per line, e.g. `9:00–13:00`; empty for a date only |

## Tests

- `Test/Unit/Model/DatesTest.php`: the day list (lead days, cut-off, excluded
  weekdays and dates) and the label.
- `dev/tests/zero-e2e/tests/delivery-date.spec.ts`: a customer picks a date at
  checkout and sees it on the order page; `delivery-date-admin.spec.ts`: the
  admin order page shows it.

## Delivery estimate on product pages

"Order within 3 h 12 min for delivery as early as Thursday, October 22"
under the price of a product in stock (not virtual): the first day the
checkout would offer, by the same rules (earliest delivery, one day more
past the cut-off hour, days and dates without delivery). Without a cut-off
hour, "Delivery as early as …". eta.js works it out in the shopper's
browser in the store's time zone and re-counts every minute: the page is
cached, so a date written into it would go stale. Hidden without
JavaScript. Stores > Configuration > Zero > Delivery Date > Delivery
Estimate on Product Pages (off by default).

## License

OSL-3.0 or AFL-3.0, your choice: see `NOTICE.md`.

---

<a href="https://vilmate.com"><picture>
  <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/zerofrontdev/zero/main/docs/images/vilmate-logo-dark.svg">
  <img src="https://raw.githubusercontent.com/zerofrontdev/zero/main/docs/images/vilmate-logo.svg" alt="Vilmate" height="34" align="right">
</picture></a>

Zero is made by [Fixstacks](https://fixstacks.com) in partnership with [Vilmate](https://vilmate.com), a software development company.
Documentation: https://zerofront.dev/docs. Demo store: https://demo.zerofront.dev.
