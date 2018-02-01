<?php

namespace Bozboz\Ecommerce\Orders;

use Bozboz\Ecommerce\Checkout\CheckoutableRepository;
use Bozboz\Ecommerce\Orders\CheckoutableOrder;
use Session;

class OrderRepository implements CheckoutableRepository
{
    protected $order;

    public function __construct(CheckoutableOrder $order)
    {
        $this->order = $order;
    }

    public function getCheckoutable()
    {
        return $this->order->find(Session::get('order'));
    }

    public function hasCheckoutable()
    {
        return Session::has('order');
    }
}
