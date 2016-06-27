<?php

namespace Bozboz\Ecommerce\Orders\Events;

class OrderStateTransition
{
    public $order;
    public $transition;

    public function __construct($order, $transition)
    {
        $this->order = $order;
        $this->transition = $transition;
    }
}