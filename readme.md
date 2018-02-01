# Orders package

<!-- MarkdownTOC -->

- Installation
- Usage
    - Cart
    - Order States
    - OrderRepository
    - Order Items
    - Customers
    - Addresses
    - Refunds
    - Events
    - Notification Emails

<!-- /MarkdownTOC -->

## Installation

1. See http://gitlab.lab/laravel-packages/ecommerce
2. Run `php artisan vendor:publish && php artisan migrate` 
3. Edit `config/orders.php` (see [Order States](#order-states))

## Usage

### Cart

To add an item to the cart you must post the following info to `/cart/items`:

```php
<?php
[
    'orderable_factory' => '[Fully qualified class name or alias of the class
        responsible for looking up the orderable items. Must implement 
        `Bozboz\Ecommerce\Orders\OrderableFactory`]',
    'orderable' => '[Whatever information is needed to find the orderable item]',
    'quantity' => '[Defaults to 1 if left blank]',
]
```

To add multiple items send an array containing multiples of the above data in `products`.

e.g. 

    products[0][orderable_factory]
    products[0][orderable]
    products[0][quantity]

### Order States

The state of each order is handled by a finite state machine. See http://yohan.giarel.li/Finite/ for details on how the state machine functions. 

The states available to the order are configured in `config/orders.php`. There are a couple of custom properties that can be set on each state:

- __show_in_default_filter:__ When set to false then the state will be excluded from the default filter in the order listing. (Default: true)
- __disallow_manual_transition:__ When set to true then the state will be removed from the states that a CMS user can transition an order to even if the current state has a transition to that state. (Default: false)

### OrderRepository

The order repository is responsible for fetching the order for the checkout process. The default repo will fetch the order ID from the session and return the order instance. The first screen in the order process is responsible for saving the order ID to the session. The repo must return an instance the implements the Checkoutable interface in the checkout package. There is a CheckoutableOrder model for this purpose that extends the base Order model.

### Order Items

Adding items to an order requires that the item implement the Orderable interface. 

### Customers

The customer model extends the user model and adds relations for orders and addresses. This allows the site to save customer data, allowing them to login and use their saved data/addresses to speed up the checkout process.

In order to use saved addresses the address must be linked to the customer. There is an event listener for this (`Bozboz\Ecommerce\Orders\Customers\Addresses\Listeners\LinkAddressToCustomer`) that can be triggered by the `Bozboz\Ecommerce\Orders\Events\OrderComplete` event. This has to be set up manually in the app.

For an example implementation of registered customers see http://gitlab.lab/bozboz/finecut/tree/master/screens

### Addresses

For an example of how to implement saved addresses see http://gitlab.lab/bozboz/finecut/blob/master/screens/AddressSelection.php (though you can ignore the stuff about account addresses, that was specific to Finecut)

### Refunds

The Refund class will create a copy of the order record in the database and link it to the master record via the `parent_order_id` column. This allows the original order to remain unchanged to maintain the history.

### Events

- `Bozboz\Ecommerce\Orders\Events\OrderStateTransition` - fired every time an order's state via the `transitionState` method.
- `Bozboz\Ecommerce\Orders\Events\OrderComplete` - This should be fired by the final screen of the checkout process when the order has completed.
- `Bozboz\Ecommerce\Orders\Events\ItemOrdered` - This should be fired by the final screen of the checkout process with each item in the order after the order is completed. 

### Notification Emails

The `Bozboz\Ecommerce\Orders\Listeners\Notify` will trigger every time `Bozboz\Ecommerce\Orders\Events\OrderStateTransition` is fired. This listener will check to see if a view exists in `ecommerce::emails.notifications...` with a name the same as the transition. If a view is present then that will be used to send an email to the user who is placing the order along with the email set in 'ecommerce.order_cc_email_address' if present. 
