<?php

namespace Bozboz\Ecommerce\Orders\Customers\Addresses;

use Illuminate\Validation\Factory as Validator;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Bozboz\Ecommerce\Orders\Order;
use Bozboz\Ecommerce\Checkout\ValidationException;

class AddressBook
{
	/**
	 * @param  Illuminate\Validation\Factory  $validator
	 * @param  Bozboz\Ecommerce\Address\Address  $address
	 */
	public function __construct(Validator $validator, Address $address)
	{
		$this->validator = $validator;
		$this->address = $address;
	}

	/**
	 * Find an address by ID or fail
	 * @param  int  $id
	 * @return  Bozboz\Ecommerce\Address\Address
	 */
	public function findOrFail($id)
	{
		return $this->address->findOrFail($id);
	}

	/**
	 * Find or create a new address
	 *
	 * @param  int  $id
	 * @return  Bozboz\Ecommerce\Address\Address
	 */
	public function findOrNew($id)
	{
		return $this->address->findOrNew($id);
	}

	/**
	 * Validate an address
	 *
	 * @param  array  $data
	 * @return Illuminate\Validation\Validator
	 */
	protected function validate($data)
	{
		if ( ! is_array($data)) return false;

		$validator = $this->validator->make($data, [
			'name'             => ['required', 'regex:/[^\s]\s+[^\s]/'],
			'address_1'        => 'required',
			'city'             => 'required',
			'country'          => 'required',
			'postcode'         => 'required'
		], [
			'name.regex' => 'Please enter your full-name',
			'address_1.required' => 'Please provide the first line of your address',
			'city.required' => 'Please provide the town/city of your address',
			'country.required' => 'Please provide the country of your address',
			'postcode.required' => 'Please provide the postcode of your address'
		]);

		if ($validator->fails()) {
			throw new ValidationException($validator);
		}
	}

	/**
	 * Store the delivery address on an order
	 *
	 * @param  Bozboz\Ecommerce\Order\Order  $order
	 * @param  mixed  $address
	 * @return Bozboz\Ecommerce\Address\Address
	 */
	public function storeDeliveryAddress(Order $order, $address)
	{
		$this->validate($address);

		$currentCountry = $order->shippingAddress ? $order->shippingAddress->country : null;

		$shippingAddress = $this->storeAddress($order->shippingAddress(), $address);
		$order->save();

		if ($currentCountry && $currentCountry !== $address['country']) {
			// TODO: Should probably inject event dispatcher
			\Event::fire('order.shipping_country_changed', $shippingAddress, $order);
		}

		return $shippingAddress;
	}

	/**
	 * Store the billing address on an order
	 *
	 * @param  Bozboz\Ecommerce\Order\Order  $order
	 * @param  mixed  $address
	 * @return void
	 */
	public function storeBillingAddress(Order $order, $address)
	{
		$this->validate($address);

		$this->storeAddress($order->billingAddress(), $address);
		$order->save();
	}

	/**
	 * Store or update shipping address with given $dataOrAddress
	 *
	 * @param  Illuminate\Database\Eloquent\Relations\BelongsTo  $relation
	 * @param  mixed  $dataOrAddress
	 * @return Bozboz\Ecommerce\Address\Address
	 */
	protected function storeAddress(BelongsTo $relation, $dataOrAddress)
	{
		if ($dataOrAddress instanceof Address) {
			$address = $dataOrAddress;
			$relation->associate($address);
		} elseif (array_key_exists('id', $dataOrAddress)) {
			$relation->update($dataOrAddress);
			$address = $relation->first();
		} else {
			$address = $this->address->create($dataOrAddress);
			$relation->associate($address)->save();
		}

		if ($dataOrAddress instanceof Address) return $dataOrAddress;

		return $address;
	}
}