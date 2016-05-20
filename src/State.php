<?php

namespace Bozboz\Ecommerce\Orders;

use Bozboz\Admin\Base\Model;

class State extends Model
{
	protected $table = 'order_states';
	protected $fillable = ['name'];

	public function order()
	{
		return $this->hasMany(Order::class);
	}

	public function getEventFriendlyName()
	{
		return 'order.' . strtolower(str_replace(' ', '.', $this->name));
	}
}
