<?php

namespace Bozboz\Ecommerce\Orders;

interface OrderableFactory
{
    public function find($orderable);
}
