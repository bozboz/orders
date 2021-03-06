<?php

namespace Bozboz\Ecommerce\Orders\Customers\Addresses\Listeners;

use Bozboz\Ecommerce\Orders\Customers\Addresses\Address;
use Bozboz\Ecommerce\Orders\Customers\Customer;
use Bozboz\Ecommerce\Orders\Events\OrderComplete;

class LinkAddressToCustomer
{
	/**
	 * Add billing address (if new) and shipping address (if new, and unique) to
	 * customer
	 *
	 * @param  Bozboz\Ecommerce\Order\Order  $order
	 * @return void
	 */
	public function handle(OrderComplete $event)
	{
		$order = $event->order;

		$user = $order->user;

		if ( ! $user) return false;

		if ($order->billingAddress) {
			$this->addAddressToCustomer($user, $order->billingAddress);
		}

		if ($order->shippingAddress && ! $order->areAddressesSame()) {
			$this->addAddressToCustomer($user, $order->shippingAddress);
		}
	}

	/**
	 * Add address to customer's stored addresses
	 *
	 * @param  Bozboz\Ecommerce\Customer\Customer  $customer
	 * @param  Bozboz\Ecommerce\Address\Address  $address
	 * @return void
	 */
	protected function addAddressToCustomer(Customer $customer, Address $address)
	{
		if ( ! $customer->addresses->contains($address)) {
			$address->customer()->associate($customer)->save();
		}
	}
}
