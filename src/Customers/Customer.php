<?php

namespace Bozboz\Ecommerce\Orders\Customers;

use Bozboz\Admin\Users\User;
use Bozboz\Ecommerce\Orders\Customers\Addresses\Address;
use Bozboz\Ecommerce\Orders\Order;

class Customer extends User implements CustomerInterface
{
	public function addresses()
	{
		return $this->hasMany(Address::class);
	}

	public function orders()
	{
		return $this->hasMany(Order::class, 'user_id');
	}

    public function getValidator()
    {
        return new CustomerValidator;
    }
}
