<?php

namespace Bozboz\Ecommerce\Orders\Customers\Addresses;

use Bozboz\Ecommerce\Orders\Customers\Customer;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
	protected $guarded = ['id', 'created_at', 'updated_at'];
	protected $hidden = ['created_at', 'updated_at', 'pivot'];

	public function customer()
	{
		return $this->belongsTo(Customer::class);
	}

	public function parts()
	{
		$attributes = $this->toArray();

		return array_except($attributes, 'id');
	}
}
