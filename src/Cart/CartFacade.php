<?php

namespace Bozboz\Ecommerce\Orders\Cart;

use Illuminate\Support\Facades\Facade;

class CartFacade extends Facade
{
	protected static function getFacadeAccessor()
	{
		return 'cart';
	}
}
