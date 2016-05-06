<?php

namespace Bozboz\Ecommerce\Orders\Cart;

use Bozboz\Ecommerce\Orders\Order;
use Bozboz\Ecommerce\Orders\Item;
use Bozboz\Ecommerce\Orders\Orderable;

class Cart extends Order
{
	protected $table = 'orders';

	/**
	 * Add $orderable to the cart, with an optional quantity
	 *
	 * @param  Bozboz\Ecommerce\Order\Orderable  $orderable
	 * @param  int  $quantity
	 * @return void
	 */
	public function add(Orderable $orderable, $quantity = 1)
	{
		$event = static::$dispatcher;

		if ($item = $this->contains($orderable)) {
			if ($orderable->canAdjustQuantity()) {
				$item->updateQuantity($item->quantity + $quantity);
				$event->fire('cart.item.updated', [$item, $this]);
			} else {
				$this->remove($item);
				$item = $this->addItem($orderable, $quantity);
				$event->fire('cart.item.added', [$item, $this]);
			}
		} else {
			$item = $this->addItem($orderable, $quantity);
			$event->fire('cart.item.added', $item);
		}

		$item->save();

		return $item;
	}

	/**
	 * Remove $item from the cart
	 *
	 * @param  Bozboz\Ecommerce\Order\Item  $item
	 * @return void
	 */
	public function remove(Item $item)
	{
		$item->delete();
		static::$dispatcher->fire('cart.item.removed', $item);
	}

	/**
	 * Remove item from the cart with given $id
	 *
	 * @param  int  $id
	 * @throws Illuminate\Database\Eloquent\ModelNotFoundException
	 * @return void
	 */
	public function removeById($id)
	{
		$this->remove($this->items()->findOrFail($id));
	}

	/**
	 * Update quantites of existing items based on given $quantities
	 *
	 * @param  array  $quantities
	 * @return void
	 */
	public function updateQuantities(array $quantities)
	{
		foreach($this->items as $item) {
			if (array_key_exists($item->id, $quantities)) {
				$quantity = $quantities[$item->id];
				if ($quantity === '0') {
					$this->remove($item);
				} else {
					$item->updateQuantity($quantity);
					static::$dispatcher->fire('cart.item.updated', [$item, $this]);
					$item->save();
				}
			}
		}
	}

	/**
	 * Return all items from the cart
	 *
	 * @return void
	 */
	public function clearItems()
	{
		$this->items()->delete();

		static::$dispatcher->fire('cart.items.cleared', $this);
	}
}
