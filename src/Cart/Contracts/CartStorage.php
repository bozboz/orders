<?php

namespace Bozboz\Ecommerce\Orders\Cart\Contracts;

interface CartStorage
{
	/**
	 * Retrieve Cart instance
	 *
	 * @return Bozboz\Ecommerce\Cart\Cart
	 */
	public function getCart();

	/**
	 * Retrieve cart and fail if one doesn't exist
	 *
	 * @throws Bozboz\Ecommerce\Cart\CartMissingException
	 * @return Bozboz\Ecommerce\Cart\Cart
	 */
	public function getCartOrFail();

	/**
	 * Retrieve, or create a Cart instance
	 *
	 * @return Bozboz\Ecommerce\Cart\Cart
	 */
	public function getOrCreateCart();

	/**
	 * Retrieve cart identifier
	 *
	 * @return mixed
	 */
	public function getIdentifier();
}
