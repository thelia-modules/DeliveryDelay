# Delivery Delay

This module allows to inform customers of the date of delivery planned for a product.

## Installation

### Manually

* Copy the module into ```<thelia_root>/local/modules/``` directory and be sure that the name of the module is DeliveryDelay.
* Activate it in your thelia administration panel

### Composer

Add it in your main thelia composer.json file

```
composer require thelia/delivery-delay-module:~1.0
```

## Usage

This module have two different configuration :

1. Global and default configuration
-----------------------------------

In module configuration you can set the minimum and the maximum delay by default it would be applied for each product without specific delay.
You have 2 different, delivery and restock. Delivery is displayed when product have stock, and restock when product doesn't have stock.

In this page you can also choose if the weekend is not deliverable in this case saturdays and sundays will be excluded fot count of delivery date.
You can do the same thing for the easter who is calculated dynamically in function of the year.

And last things, you can exculde specific date like 1st January, 25 December or other dates.

2. Product configuration
------------------------

For each product you can specify different delay for delivery and restock, these delay will override default delay.
Two others option are available for products, first you can set a pre order date with that the delay will be computed from this date and you can show a different message, 
when the pre order date is passed the delivery date will be computed normally.
And you can also said the product is only available on order and in this case don't display a delivery date just show a message with some explication.

3. Usage
--------

You have different way to show delivery delay.
The module will automatically show delivery delay on the ```product.details-bottom``` hook.
But you can deactivate it in back office and use the specific hook ```product.delivery-delay``` to put him where you want.
Or you can use the loop describe below.

## Hook

### backoffice :
- product.tab-content
- product.edit-js

### frontoffice :
- product.details-bottom
- product.delivery-delay

## Loop

[delivery_delay_product]
------------------------

### Input arguments

|Argument |Description |
|---      |--- |
|**product_id** | The id of the product |

### Output arguments

|Variable                   |Description |
|---                        |--- |
|$DELIVERY_TYPE             | The type of delivery (normal, pre-order, on order, ...) |
|$DELIVERY_START_DATE       | The date from when the product delivery delay calcul start |
|$DATE_MIN                  | The minimum date of delivery |
|$DATE_MAX                  | The maximum date of delivery |

### Exemple

```smarty
    {loop type="delivery_delay_product" name="delivery_delay_product" product_id={$product_id}}
        {if $PRE_ORDER }
            {intl l="This product is on pre-order and will be only available from : "}{format_date date={$PRE_ORDER} output="date"}
        {elseif $ON_ORDER === 1}
            {intl l="This product is only available on order, contact us for more informations"}
        {else}
            {intl l="Delivery planned between %min and %max" min={format_date date={$DATE_MIN} output="date"} max={format_date date={$DATE_MAX} output="date"}}
        {/if}
    {/loop}
 ```