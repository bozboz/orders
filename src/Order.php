<?php

namespace Bozboz\Ecommerce\Orders;

use Bozboz\Admin\Base\Model;
use Bozboz\Admin\Reports\Downloadable;
use Bozboz\Ecommerce\Order\State as OrderState;
use Bozboz\Ecommerce\Orders\Customers\Addresses\Address;
use Bozboz\Ecommerce\Orders\Customers\Customer;
use Exception;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;

class Order extends Model
{
	use SoftDeletes;

	protected $paymentDataArray = null;

	public $fillable = array('customer_email', 'customer_first_name', 'customer_last_name', 'customer_phone', 'company', 'state_id');

	public function state()
	{
		return $this->belongsTo(State::class);
	}

	public function items()
	{
		return $this->hasMany(Item::class, 'order_id');
	}

	public function billingAddress()
	{
		return $this->belongsTo(Address::class);
	}

	public function shippingAddress()
	{
		return $this->belongsTo(Address::class);
	}

	public function user()
	{
		return $this->belongsTo(Customer::class);
	}

	public function parent()
	{
		return $this->belongsTo(Order::class, 'parent_order_id');
	}

	public function relatedOrders()
	{
		return $this->hasMany(Order::class, 'parent_order_id');
	}

	public function scopeCompleted($query)
	{
		$query->whereStateId(3);
	}

	/**
	 * @return Boolean
	 */
	public function areAddressesSame()
	{
		return $this->billing_address_id === $this->shipping_address_id;
	}

	/**
	 * @return int
	 */
	public function totalPrice()
	{
		$items = array_key_exists('items', $this->getRelations()) ? $this->items : $this->items();

		return $items->sum('total_price_pence');
	}

	/**
	 * @return int
	 */
	public function totalQuantity()
	{
		return $this->items()->sum('quantity');
	}

	/**
	 * @return int
	 */
	public function totalWeight()
	{
		return $this->items()->sum('total_weight');
	}

	/**
	 * @return int
	 */
	// public function shippingPrice()
	// {
	// 	return $this->items()->where('orderable_type', 'Bozboz\Ecommerce\Shipping\OrderableShippingMethod')->pluck('total_price_pence');
	// }

	/**
	 * @return int
	 */
	public function subTotal()
	{
		return $this->items()->sum('total_price_pence_ex_vat');
	}

	/**
	 * @return int
	 */
	public function totalTax()
	{
		return $this->items()->sum('total_tax_pence');
	}

	/**
	 * @return Boolean
	 */
	public function isTaxable()
	{
		$shippingCountry = $this->shippingAddress()->pluck('country');
		if ($shippingCountry) {
			$shippingRegion = $this->getConnection()->table('countries')
				->whereCode($shippingCountry)
				->pluck('region');
			return $shippingRegion === 'EU';
		} else {
			return true;
		}
	}

	/**
	 * @param  Bozboz\Ecommerce\Order\Orderable  $orderable
	 * @param  int  $quantity
	 * @return Bozboz\Ecommerce\Order\Item
	 */
	public function addItem(Orderable $orderable, $quantity = 1)
	{
		$item = new Item;

		$orderable->validate($quantity, $item, $this);

		$item->name = $orderable->label();
		$item->total_weight = $orderable->calculateWeight($quantity);
		$item->quantity = $quantity;
		$item->image = $orderable->image();
		$item->tax_rate = $this->isTaxable() && $orderable->isTaxable() ? 0.2 : 0;
		$item->calculateNet($orderable, $this);
		$item->calculateGross();
		$this->items()->save($item);
		$orderable->items()->save($item);

		return $item;
	}

	/**
	 * Change state of order to a state matching the given $state string
	 *
	 * @param  string  $state
	 * @return void
	 * @throws Illuminate\Database\Eloquent\ModelNotFoundException
	 */
	public function changeState($state)
	{
		$orderState = OrderState::whereName($state)->firstOrFail();
		$this->state()->associate($orderState);
		$this->save();
	}

	/**
	 * Fire an event when an order state changes
	 *
	 * @param  int  $id
	 */
	public function setStateIdAttribute($id)
	{
		$state = OrderState::findOrFail($id);

		$this->attributes['state_id'] = $id;

		static::$dispatcher->fire($state->getEventFriendlyName(), $this);
	}

	/**
	 * Determine if an order requires shipping
	 *
	 * @return boolean
	 */
	public function requiresShipping()
	{
		return ! $this->items()->with('orderable')->get([
			'orderable_id', 'orderable_type'
		])->filter(function($item) {
			return $item->orderable->shipping_band_id > 0;
		})->isEmpty();
	}

	/**
	 * Determine if an order requires payment
	 *
	 * @return boolean
	 */
	public function requiresPayment()
	{
		return $this->totalPrice() > 0;
	}


	public function getValidator()
	{
		return new OrderValidator;
	}

	public function contains(Orderable $orderable)
	{
		return $this->items()->where([
			'orderable_id' => $orderable->id,
			'orderable_type' => get_class($orderable)
		])->first();
	}

	public function generateReference()
	{
		if (!empty($this->reference)) {
			throw new Exception('Cannot regenerate reference');
		}

		$unique = false;
		while (!$unique) {
			$reference = generate_random_alphanumeric_string(4) . '-' . generate_random_alphanumeric_string(4);
			$unique = empty($this->whereRaw('BINARY `reference` = ?', [$reference])->first()); //Case sensitive lookup
		}

		$this->reference = $reference;
	}

	public function getPaymentDataAttribute()
	{
		if (is_null($this->paymentDataArray)) {
			$this->paymentDataArray = json_decode($this->attributes['payment_data'], true) ?: [];
		}

		return $this->paymentDataArray;
	}

	public function hasPaymentData($key)
	{
		return array_key_exists($key, $this->payment_data);
	}

	public function getPaymentData($key)
	{
		return $this->payment_data[$key];
	}

	public function setPaymentData($key, $value)
	{
		$this->paymentDataArray[$key] = $value;
		$this->payment_data = json_encode($this->paymentDataArray);
	}

	public function removePaymentData($key)
	{
		unset($this->paymentDataArray[$key]);
		$this->payment_data = json_encode($this->paymentDataArray);
	}

	/**
	 * Parse the transaction ID to determine ID of order
	 *
	 * @param  string  $id
	 * @return Bozboz\Ecommerce\Order\Order
	 */
	public function findByTransactionId($id)
	{
		return static::where('transaction_id', $id)->first();
	}

	/**
	 * Populate a new transaction ID on the order, in format:
	 *
	 *     c<id>-<timestamp>
	 *
	 * @return void
	 */
	public function generateTransactionId()
	{
		$this->transaction_id = 'i' . $this->id . '-' . time();
	}

	/**
	 * Retrieve transaction ID for order
	 *
	 * @return string
	 */
	public function getTransactionId()
	{
		return $this->transaction_id;
	}
}
