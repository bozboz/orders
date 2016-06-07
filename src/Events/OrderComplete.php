<?php

namespace Bozboz\Ecommerce\Orders\Events;

class OrderComplete
{
    public $order;

    public function __construct($order)
    {
        $this->order = $order;
    }
}
