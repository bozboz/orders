<?php

namespace Bozboz\Ecommerce\Orders\Cart;

use Bozboz\Ecommerce\Orders\Cart\CartStorageInterface;
use Illuminate\Session\Store as Session;

class SessionStorage implements CartStorageInterface
{
	protected $session;

	public function __construct(Session $session)
	{
		$this->session = $session;
	}

	public function getCart()
	{
		return Cart::whereId($this->getIdentifier())->whereIn('state', ['cart', 'checkout'])->first();
	}

	public function getCartOrFail()
	{
		$cart = $this->getCart();

		if ( ! $cart) throw new CartMissingException;

		return $cart;
	}

	public function getOrCreateCart()
	{
		$cart = null;
		if ($this->session->has('cart')) {
			$cart = $this->getCart();
		}

		if ( ! $cart) {
			$cart = Cart::create();
			$cart->setFiniteState('cart');
			$cart->save();

			$this->session->put('cart', $cart->getKey());
		}

		return $cart;
	}

	public function getIdentifier()
	{
		return $this->session->get('cart');
	}
}
