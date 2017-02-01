<?php

namespace Bozboz\Ecommerce\Orders\Customers\Addresses;

use App\Ecommerce\Orders\Order;
use Bozboz\Admin\Base\Model;
use Bozboz\Ecommerce\Orders\Customers\Customer;

class Address extends Model
{
	protected $guarded = ['id', 'created_at', 'updated_at'];
	protected $hidden = ['created_at', 'updated_at', 'customer_id'];
	protected $nullable = ['customer_id'];

	public function customer()
	{
		return $this->belongsTo(Customer::class);
	}

	public function orders()
	{
		return $this->hasMany(Order::class, 'billing_address_id')->orWhere('shipping_address_id', $this->id);
	}

	public function parts()
	{
		$attributes = $this->toArray();

		return array_except($attributes, 'id');
	}
}
