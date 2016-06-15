<?php

namespace Bozboz\Ecommerce\Orders;

interface Orderable
{
	/**
	 * Return list of items associated with this orderable
	 *
	 * @return Illuminate\Database\Eloquent\Relations\MorphMany
	 */
	public function items();

	/**
	 * Determine whether Orderable model can have its quantity adjusted
	 *
	 * @return boolean
	 */
	public function canAdjustQuantity();

	/**
	 * Determine whether Orderable model can be deleted once set
	 *
	 * @return boolean
	 */
	public function canDelete();

	/**
	 * Validate item in context of order and quantity requested
	 *
	 * @param  int  $quantity
	 * @param  Bozboz\Ecommerce\Order\Order  $order
	 * @return void
	 */
	public function validate($quantity, Item $item, Order $order);

	/**
	 * Calculate price of item, based on quantity and order parameters
	 *
	 * @param  int  $quantity
	 * @param  Bozboz\Ecommerce\Order\Order  $order
	 * @return float
	 */
	public function calculatePrice($quantity, Order $order);

	/**
	 * Calculate weight of item, based on quantity
	 *
	 * @param  int  $quantity
	 * @return float
	 */
	public function calculateWeight($quantity);

	/**
	 * Consistent label identifier for orderable item
	 *
	 * @return string
	 */
	public function label();

	/**
	 * Get path to an image representing the orderable item
	 *
	 * @return string
	 */
	public function image();

	/**
	 * Calculate amount to refund, based on item and quantity
	 *
	 * @param  Bozboz\Ecommerce\Order\Item  $item
	 * @param  int  $quantity
	 * @return int
	 */
	public function calculateAmountToRefund(Item $item, $quantity);

	/**
	 * Determine if orderable is taxable or not
	 *
	 * @return boolean
	 */
	public function isTaxable();

	/**
	 * Perform any actions necessary upon successful purchase
	 *
	 * @param  int $quantity
	 * @return void
	 */
	public function purchased($quantity);
}