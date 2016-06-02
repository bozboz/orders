<?php

namespace Bozboz\Ecommerce\Orders\Cart;

use Bozboz\Ecommerce\Orders\Cart\CartStorageInterface;

class CartComposer
{
    private $cart;

    public function __construct(CartStorageInterface $cart)
    {
        $this->cart = $cart;
    }

    public function compose($view)
    {
        $cart = $this->cart->getCart();
        $view->withCart($cart);
    }
}