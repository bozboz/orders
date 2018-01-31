# Ecommerce package

## Installation

1. See http://gitlab.lab/laravel-packages/ecommerce
2. Run `php artisan vendor:publish && php artisan migrate` 
3. Edit `config/orders.php` (see [Order States](#order-states))

## Usage

### Order States

The state of each order is handled by a finite state machine. See http://yohan.giarel.li/Finite/ for details on how the state machine functions. 

The states available to the order are configured in `config/orders.php`. There are a couple of custom properties that can be set on each state:

- __show_in_default_filter:__ When set to false then the state will be excluded from the default filter in the order listing. (Default: true)
- __disallow_manual_transition:__ When set to true then the state will be removed from the states that a CMS user can transition an order to even if the current state has a transition to that state. (Default: false)

### Events

- `Bozboz\Ecommerce\Orders\Events\OrderStateTransition` - fired every time an order's state via the `transitionState` method.
- `Bozboz\Ecommerce\Orders\Events\OrderComplete` - This should be fired by the final screen of the checkout process when the order has completed.
- `Bozboz\Ecommerce\Orders\Events\ItemOrdered` - This should be fired by the final screen of the checkout process with each item in the order after the order is completed. 

### Notification Emails

The `Bozboz\Ecommerce\Orders\Listeners\Notify` will trigger every time `Bozboz\Ecommerce\Orders\Events\OrderStateTransition` is fired. This listener will check to see if a view exists in `ecommerce::emails.notifications...` with a name the same as the transition. If a view is present then that will be used to send an email to the user who is placing the order along with the email set in 'ecommerce.order_cc_email_address' if present. 
