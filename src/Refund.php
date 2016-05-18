<?php

namespace Bozboz\Ecommerce\Orders;

use Bozboz\Ecommerce\Payment\Exception;
use Bozboz\Ecommerce\Payment\GatewayFactory;
use Bozboz\Ecommerce\Payment\PaymentGateway;
use Bozboz\Ecommerce\Payment\Refundable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Events\Dispatcher;
use Illuminate\Log\Writer;
use ReflectionException;

class Refund
{
	protected $paymentGateway;
	protected $logger;
	protected $events;

	public function __construct(GatewayFactory $paymentGateway, Writer $logger, Dispatcher $events)
	{
		$this->paymentGateway = $paymentGateway;
		$this->logger = $logger;
		$this->events = $events;
	}

	/**
	 * Process refund for given $order
	 *
	 * @param  Bozboz\Ecommerce\Order\Order  $order
	 * @param  array  $itemQuantities
	 * @throws Bozboz\Ecommerce\Payment\Exception
	 * @return Bozboz\Ecommerce\Order\Order
	 */
	public function process(Order $order, array $itemQuantities = array())
	{
		$gateway = $this->determinePaymentGateway($order);

		$this->validateGateway($gateway);

		$newOrder = $this->generateRefundedOrder($order, $itemQuantities);

		$response = $gateway->refund([
			'transactionReference' => $order->payment_ref
		], $newOrder);

		if ( ! $response->isSuccessful()) {
			$newOrder->delete();
			$msg = 'Error Refunding Order #' . $order->id;
			$this->logger->error($msg . ': ' . $response->getMessage());
			throw new Exception($msg);
		}

		$newOrder->changeState('Refunded');

		foreach($newOrder->items()->get() as $item) {
			$this->events->fire('item.refunded: ' . $item->orderable_type, $item);
		}

		return $newOrder;
	}

	/**
	 * Determine payment gateway from $order
	 *
	 * @param  Bozboz\Ecommerce\Order\Order  $order
	 * @throws Bozboz\Ecommerce\Payment\Exception
	 * @return Bozboz\Ecommerce\Payment\PaymentGateway
	 */
	private function determinePaymentGateway(Order $order)
	{
		try {
			$gateway = $this->paymentGateway->make($order->payment_method);
		} catch (ReflectionException $e) {
			throw new Exception(sprintf(
				'The payment gateway for this order (%s) is invalid',
				$order->payment_method
			));
		}

		return $gateway;
	}

	/**
	 * Validate payment $gateway
	 *
	 * @param  PaymentGateway $gateway
	 * @throws Bozboz\Ecommerce\Payment\Exception
	 * @return void
	 */
	private function validateGateway(PaymentGateway $gateway)
	{
		if ( ! $gateway instanceof Refundable) {
			throw new Exception(sprintf(
				'The payment gateway for this order (%s) does not support refunds',
				get_class($gateway)
			));
		}
	}

	/**
	 * Generate new refunded order from original $order instance
	 *
	 * @param  Bozboz\Ecommerce\Order\Order  $order
	 * @param  array  $itemQuantities
	 * @return Bozboz\Ecommerce\Order\Order
	 */
	private function generateRefundedOrder(Order $order, array $itemQuantities)
	{
		$refundedOrder = $order->replicate();

		$order->relatedOrders()->save($refundedOrder);

		$items = $order->items()->with('orderable')->whereIn('order_items.id', array_keys($itemQuantities))->get();

		$refundedItems = $this->generateRefundedItems($items, $itemQuantities);

		$refundedOrder->items()->saveMany($refundedItems->all());

		return $refundedOrder;
	}

	/**
	 * Generate refunded items from given $items
	 *
	 * @param  Illuminate\Database\Eloquent\Collection  $items
	 * @param  array  $itemQuantities
	 * @return Illuminate\Database\Eloquent\Collection
	 */
	private function generateRefundedItems(Collection $items, array $itemQuantities)
	{
		return $items->map(function($item) use ($itemQuantities)
		{
			$refundedItem = $item->generateRefundedItem($itemQuantities[$item->id]);
			$refundedItem->save();

			return $refundedItem;
		});
	}
}
