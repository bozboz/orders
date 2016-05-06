<?php

namespace Bozboz\Ecommerce\Orders\Customers;

use Bozboz\Admin\Users\User;
use Bozboz\Ecommerce\Orders\Customers\Addresses\Address;
use Bozboz\Ecommerce\Orders\Order;

class Customer extends User
{
	public function addresses()
	{
		return $this->belongsToMany(Address::class)->withTimestamps();
	}

	public function orders()
	{
		return $this->hasMany(Order::class, 'user_id');
	}
}
