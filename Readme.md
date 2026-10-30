# Delivery Delay

Tells customers when their parcel arrives, as an interval of dates, computed per carrier.

The interval is shown:

- with each delivery option of the checkout (`minimumDeliveryDate` and `maximumDeliveryDate` of the `/api/front/delivery_modules` options, which the Flexy theme prints);
- on the product page, for the country of the shop;
- in the order confirmation e-mail, as it was at the time of the order.

## Requirements

- Thelia 3.3 or later. The module keeps no list of days off of its own: it counts on the delivery calendar of the core (Configuration > Delivery > Delivery dates), through `Thelia\Domain\Shipping\DeliveryDate\Service\DeliveryClosedDays`. Closed days of the week (of the shop or of a carrier) and the closures of the shop or of a carrier are all read from there.
- The Flexy front-office theme (or any Twig theme that raises the `product.details.bottom` theme hook) for the product page; the checkout reads the dates from the API options.

## Installation

Copy the module into `local/modules/DeliveryDelay` (the folder name must be `DeliveryDelay`), or install it with Composer:

```
composer require thelia/delivery-delay-module
```

Then activate it in the back office. Activating it creates its three tables (`delivery_delay_carrier`, `delivery_delay_product`, `delivery_delay_order_estimate`) with `CREATE TABLE IF NOT EXISTS`; nothing is ever dropped.

Version 3 does not read the data of version 2.x (`product_delay`, `undeliverable_date`): the delays have to be entered again, and the days off now come from the calendar of the core.

## Configuration

Module configuration page:

| Setting | Meaning |
|---|---|
| Preparation (days) | Working days the carrier needs to get an order ready. Empty means 0. |
| Transit minimum / maximum (days) | Working days between the departure and the arrival. Both are required, minimum at most maximum. |
| Cut-off time | `HH:MM`. An order placed at or after this time starts the next working day. Empty: no cut-off. |
| Carrier shown on the product page | Default: the first active delivery module (by position) that has delays. |

A carrier whose row is left empty has no delays and gets no estimate: its options keep the dates the carrier gave, and nothing breaks.

Product sheet, tab "Delivery delay":

| Setting | Meaning |
|---|---|
| Availability delay (days) | Always added to the preparation, for a product made to order. |
| Restock delay (days) | Added on top when the variant ordered is out of stock (quantity of 0 or less, product not virtual). |

An empty field is "not filled in", `0` is a value; the two are kept apart.

Reading needs the right to see the module, writing the right to update it.

## How the interval is computed

Everything is counted in working days of the carrier: a day is open when the core calendar does not close it for that carrier. Today is read on the clock and in the time zone of the shop.

1. The first working day is today when it is open and the cut-off time (if any) is not reached, the next open day otherwise.
2. The effective preparation is the preparation of the carrier plus the delay of the slowest product of the cart (the maximum over the lines of availability delay, plus restock delay for a line out of stock).
3. The first working day is the first day of preparation: the parcel leaves on the first working day plus `max(preparation - 1, 0)` open days.
4. The earliest arrival is the departure plus the minimum transit in open days, the latest arrival the departure plus the maximum.

Example: preparation 1, transit 2, order on Tuesday at 9:00 with no closure: the parcel leaves on Tuesday and arrives on Thursday. After the cut-off time it leaves on Wednesday and arrives on Friday. A holiday closed by the shop between the two moves the arrival by one day.

The calendar is read once for a window of 120 days (longer only when the count needs it, up to about 2.6 years). A calendar with no open day gives no estimate instead of looping.

`DeliveryDelay\Estimate\DeliveryEstimator` does the counting, with no state and no session or request: it works in a console command too.

## When the dates are written

- The options of a delivery module are filled by a listener of `MODULE_DELIVERY_GET_OPTIONS` at priority 100, after CustomDelivery (129) has written its empty dates.
- The interval of an order is frozen once, when the order is placed (`ORDER_BEFORE_PAYMENT`, before the confirmation e-mail is built), in `delivery_delay_order_estimate`. It is never recomputed, and it outlives the carrier. The confirmation e-mail prints it after the addresses (`email-html.order-confirmation.after-address` and its `email-txt` twin), in the language of the order.
- The product page estimate sets no cookie and no cache header: the page stays cacheable, so the dates can be up to the lifetime of the cache old.

## Tests

From the root of the Thelia project, after `ddev composer test:prepare` with the module active:

```
ddev exec composer phpc -- ./vendor/bin/phpunit -c local/modules/DeliveryDelay/phpunit.xml.dist
```

Suites: `unit` (the estimator), `integration` (options, frozen interval, e-mail, product page), `http` (back office).
