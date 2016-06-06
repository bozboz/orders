<?php

namespace Bozboz\Ecommerce\Orders;

use Bozboz\Admin\Base\Model;
use Bozboz\Admin\Reports\Downloadable;
use Bozboz\Ecommerce\Orders\Customers\Addresses\Address;
use Bozboz\Ecommerce\Orders\Customers\Customer;
use Bozboz\Ecommerce\Orders\OrderStateException;
use Exception;
use Finite\Loader\ArrayLoader;
use Finite\StateMachine\StateMachine;
use Finite\StatefulInterface;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model implements StatefulInterface
{
	use SoftDeletes;

	private $stateMachine;

	protected $table = 'orders';

	protected $paymentDataArray = null;

	public $fillable = [
		'customer_email',
		'customer_first_name',
		'customer_last_name',
		'customer_phone',
		'company',
		'state',
	];

	public function __construct($attributes = [])
	{
		parent::__construct($attributes);
		$this->initializeStateMachine();
	}

	protected function initializeStateMachine()
	{
		$stateMachine = new StateMachine;
		$loader = new ArrayLoader(array_merge(
			[
				'class' => static::class,
			],
			config('orders.finite_state')
		));

		$loader->load($stateMachine);
		$stateMachine->setObject($this);
		$stateMachine->initialize();

		$this->stateMachine = $stateMachine;
	}

	public function setFiniteState($state)
	{
		$this->attributes['state'] = $state;
	}

	public function getFiniteState()
	{
		if (array_key_exists('state', $this->attributes)) {
			return $this->attributes['state'];
		}
	}

	public function setStateAttribute($state)
	{
		throw new \Exception('Attempting to override order state directly');
	}

	public function getStateMachine()
	{
		if ( ! $this->stateMachine) {
			$this->initializeStateMachine();
		}
		return $this->stateMachine;
	}

	public function getFinalStates()
	{
		return $this->getStateMachine()->findStateWithProperty('user_complete', true);
	}

	public function newFromBuilder($attributes = [], $connection = null)
	{
		$instance = parent::newFromBuilder($attributes, $connection);
		$instance->getStateMachine()->initialize();
		return $instance;
	}

	/**
	 * Transition the order state using the given $transition string
	 *
	 * @param  string  $transition
	 * @return void
	 * @throws Bozboz\Ecommerce\Orders\OrderStateException
	 */
	public function transitionState($transition)
	{
		$this->stateMachine->apply($transition);
		$this->attributes['state'] = $this->getFiniteState();
		$this->save();
	}

	public function canTransition($transition)
	{
		return $this->stateMachine->can($transition);
	}

	public function isComplete()
	{
		return $this->getStateMachine()->getCurrentState()->isFinal();
	}

	public function scopeComplete($query)
	{
		$query->whereIn('state', $this->getFinalStates());
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
		$shippingCountry = $this->shippingAddress()->value('country');
		if ($shippingCountry) {
			$shippingRegion = $this->getConnection()->table('countries')
				->whereCode($shippingCountry)
				->value('region');
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
		// $item->image = $orderable->image();
		$item->tax_rate = $this->isTaxable() && $orderable->isTaxable() ? 0.2 : 0;
		$item->calculateNet($orderable, $this);
		$item->calculateGross();
		$this->items()->save($item);
		$orderable->items()->save($item);

		return $item;
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
