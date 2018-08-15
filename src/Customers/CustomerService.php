<?php

namespace Bozboz\Ecommerce\Orders\Customers;

use Bozboz\Ecommerce\Orders\Order;

use Bozboz\Ecommerce\Checkout\ValidationException;

use Bozboz\Admin\Users\UserInterface;
use Illuminate\Validation\Factory as Validator;
use Bozboz\Ecommerce\Orders\Customers\CustomerInterface;

class CustomerService
{
	protected $validator;
	protected $customer;

	/**
	 * @param Illuminate\Validation\Factory $validator
	 */
	public function __construct(Validator $validator, CustomerInterface $customer)
	{
		$this->validator = $validator;
		$this->customer = $customer;
	}

	/**
	 * Store customer details on order from the given $user instance
	 *
	 * @param  Bozboz\Ecommerce\Order\Order  $order
	 * @param  Bozboz\Admin\Users\UserInterface  $user
	 * @return void
	 */
	public function storeUserDetailsOnOrder(Order $order, UserInterface $user)
	{
		$order->update([
			'customer_first_name' => $user->first_name,
			'customer_last_name' => $user->last_name,
			'customer_email' => $user->email,
			'customer_phone' => $user->phone
		]);
	}

	/**
	 * Validate and store customer details on given $order
	 *
	 * @param  Bozboz\Ecommerce\Order\Order  $order
	 * @param  array  $data
	 * @return void
	 */
	public function storeDetailsOnOrder(Order $order, array $data)
	{
		$validator = $this->validator->make($data, [
			'customer_first_name' => 'required',
			'customer_last_name' => 'required',
			'customer_email' => 'required|email',
			'terms_and_conditions' => 'required'
		], [
			'customer_first_name.required' => 'Please provide your first name',
			'customer_last_name.required' => 'Please provide your last name',
			'customer_email.required' => 'Please provide your email address',
			'customer_email.unique' => 'Email address is already used. Click <a class="js-account-btn" href="' . route('checkout.login') . '">here</a> to login',
			'terms_and_conditions.required' => 'Please agree to our terms and conditions'
		]);

		if ($validator->fails()) throw new ValidationException($validator);

		$order->update($data);
	}

	/**
	 * Populate a given order with the credentials from the given user
	 *
	 * @param  Bozboz\Ecommerce\Orders\Order  $order
	 * @param  Bozboz\Admin\Users\UserInterface  $user
	 * @return void
	 */
	public function populateOrderFromCustomer(Order $order, UserInterface $user)
	{
		$mapping = [
			'customer_first_name' => 'first_name',
			'customer_last_name' => 'last_name',
			'customer_email' => 'email',
			'customer_phone' => 'phone',
		];

		foreach ($mapping as $orderProp => $userProp) {
			$order->$orderProp = $order->$orderProp ?: $user->$userProp;
		}
	}

	/**
	 * Create or update user info from $order and assign to $order
	 *
	 * @param  mixed  $authUser
	 * @param  Bozboz\Ecommerce\Order\Order  $order
	 * @param  array  $data
	 * @return Illuminate\Auth\UserInterface
	 */
	public function storeUserInfo($authUser, Order $order, array $data)
	{
		if (!$authUser) {
			$authUser = $this->createNewUser($data, $order);
		} else {
			$this->updateUserInfo($authUser, $order);
		}

		$order->user()->associate($authUser);
		$order->save();

		return $authUser;
	}

	/**
	 * Create a new user from the given $data array and $order
	 *
	 * @param  array  $data
	 * @param  Bozboz\Ecommerce\Order\Order  $order
	 * @return Illuminate\Auth\UserInterface
	 */
	protected function createNewUser($data, Order $order)
	{
		$validator = $this->validator->make($data, [
			'customer_email' => 'required|unique:users,email',
			'password' => 'required|confirmed'
		], [
			'customer_email.required' => 'Please provide your email address',
			'customer_email.unique' => 'Email address is already used. Click <a class="js-account-btn" href="' . route('checkout.login') . '">here</a> to login',
			'password.required' => 'Please enter a password'
		]);

		if ($validator->fails()) throw new ValidationException($validator);

		return $this->customer->create([
			'email' => $order->customer_email,
			'first_name' => $order->customer_first_name,
			'last_name' => $order->customer_last_name,
			'phone' => $order->customer_phone,
			'password' => $data['password']
		]);
	}

	/**
	 * Update an existing $user, with information on given $order
	 *
	 * @param  array  $data
	 * @param  Bozboz\Ecommerce\Order\Order  $order
	 * @return void
	 */
	protected function updateUserInfo($user, Order $order)
	{
		$user->update([
			'email' => $order->customer_email,
			'first_name' => $order->customer_first_name,
			'last_name' => $order->customer_last_name,
			'phone' => $order->customer_phone
		]);
	}
}
