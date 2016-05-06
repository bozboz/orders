<?php

namespace Bozboz\Ecommerce\Orders\Http\Controllers;

use Bozboz\Ecommerce\Orders\Cart\CartStorageInterface;
use Bozboz\Ecommerce\Orders\Orderable;
use Bozboz\Ecommerce\Orders\OrderableException;
use Bozboz\Ecommerce\Voucher\OrderableVoucher;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;

class CartController extends Controller
{
	protected $storage;

	public function __construct(CartStorageInterface $storage)
	{
		$this->storage = $storage;

		$this->beforeFilter('cart-redirect', ['except' => ['index', 'add', 'addVoucher']]);
		$this->beforeFilter('basket-timeout');
	}

	public function index()
	{
		$cart = $this->storage->getCart();

		return View::make('orders::cart.cart')->with([
			'cart' => $cart
		]);
	}

	public function addVoucher()
	{
		try {
			$voucherCode = Input::get('voucher_code');
			$voucher = OrderableVoucher::whereCode($voucherCode)->firstOrFail();
			$this->cart->add($voucherCode);
		} catch (Exception $e) {
			return Redirect::route('cart')->withErrors($e->getErrors());
		} catch (ModelNotFoundException $e) {
			return Redirect::route('cart')->withErrors(sprintf('Voucher code "%s" not recognised', $voucherCode));
		}
		return Redirect::back();
	}

	public function add(Orderable $defaultOrderable)
	{
		$cart = $this->storage->getOrCreateCart();

		try {
			$model = Input::get('orderable_type', $defaultOrderable);
			$item = $cart->add(
				$model::find(Input::get('orderable_id')),
				Input::get('quantity', 1)
			);
		} catch (OrderableException $e) {
			return Redirect::route('cart')->withErrors($e->getErrors());
		}

		return Redirect::route('cart')->with('product_added_to_cart', $item->name);
	}

	public function remove($id)
	{
		$this->storage->getCartOrFail()->removeById($id);

		return $this->redirectBack();
	}

	public function update()
	{
		$cart = $this->storage->getCartOrFail();

		if (Input::has('remove')) {
			foreach(Input::get('remove') as $id) {
				$cart->removeById($id);
			}
			return $this->redirectBack();
		}

		if (Input::has('clear')) {
			return $this->destroy();
		}

		try {
			$cart->updateQuantities(Input::get('quantity'));
		} catch (OrderableException $e) {
			return Redirect::route('cart')->withErrors($e->getErrors());
		}

		return $this->redirectBack();
	}

	public function destroy()
	{
		$this->storage->getCartOrFail()->clearItems();

		return Redirect::home();
	}

	protected function redirectBack()
	{
		if (Request::header('referer')) {
			return Redirect::back();
		} else {
			return Redirect::route('cart');
		}
	}
}
