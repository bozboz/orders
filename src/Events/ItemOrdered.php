<?php

namespace Bozboz\Ecommerce\Orders\Events;

use Bozboz\Ecommerce\Orders\Item;

class ItemOrdered
{
    public $item;

    public function __construct(Item $item)
    {
        $this->item = $item;
    }
}
