<div class="cart__container wrapper">

@if ($cart && $cart->items->count())

	{{ Form::open(array('route' => 'cart')) }}

		<header class="cart-products__heading">
			<div class="grid__item medium-12 cart-errors">{{ $errors->first() }}</div><!--

			--><div class="grid__item large-1 cart-product__image"></div><!--
			--><div class="grid__item medium-6 large-8 hide-on-mobile"></div><!--
			--><div class="grid__item medium-2 large-1 hide-on-mobile">Price</div><!--
			--><div class="grid__item medium-2 large-1 hide-on-mobile">Quantity</div><!--
			--><div class="grid__item medium-2 large-1 hide-on-mobile">Total</div>
		</header>

		<div class="grid cart-products">
			@foreach($cart->items as $item)
			   <div class="cart-product__item">
				<div class="grid__item cart-product__image medium-2 large-1">
					{{ HTML::image($item->image, $item->name) }}
				</div><!--
				--><div class="grid__item col-sm-10 medium-4 large-8">
						<h4 class="cart-product__title">{{ $item->name }}</h4>
						<a href="{{ URL::route('cart.remove-item', [$item->id, Session::token()]) }}" class="btn--remove"><i class="fa fa-remove"></i> Remove</a>
				</div><!--
				--><div class="grid__item cart-product__price col-sm-2 medium-2 large-1">{{ format_money($item->price_pence_ex_vat) }}</div><!--
				--><div class="grid__item cart-product__quantity col-sm-4 medium-2 large-1">
					@if ($item->orderable->canAdjustQuantity())
						{{ Form::text('quantity[' . $item->id . ']', $item->quantity, array('class' => 'cart__input')) }}
					@else
						{{ Form::hidden('quantity[' . $item->id . ']', 1 )}}
					@endif
				</div><!--
				--><div class="grid__item cart-product__total col-sm-8 medium-2 large-1">{{ format_money($item->total_price_pence_ex_vat) }}</div>
			  </div>
			@endforeach
			<div class="cart-product__item">
				<div class="grid__item cart-product__image medium-2 large-1"></div><!--
				--><div class="grid__item col-sm-10 medium-4 large-8">VAT</div><!--
				--><div class="grid__item cart-product__price col-sm-2 medium-2 large-1"></div><!--
				--><div class="grid__item cart-product__quantity col-sm-4 medium-2 large-1"></div><!--
				--><div class="grid__item cart-product__total col-sm-8 medium-2 large-1">{{ format_money($cart->totalTax()) }}</div>
			</div>
		</div>

		<div class="order-summary"><!--
			--><div class="grid__item medium-8 large-10 cart__buttons--action">
					<button type="submit" value="update" name="update" class="btn--outlined--small">Update</button>
					<button type="submit" value="clear" name="clear" class="btn--outlined--small">Clear Basket</button>
			</div><!--
			--><div class="grid__item medium-2 large-1 hide-on-mobile">{{ $cart->totalQuantity() }}</div><!--
			--><div class="grid__item medium-2 large-1 order-summary__total">{{ format_money($cart->totalPrice()) }}</div>
		</div>

	{{ Form::close() }}

	<div class="checkout__btn">
		{{ Form::open(array('route' => 'checkout')) }}
			<a class="btn--alt--small" href="{{ route('events.listing') }}">Continue Shopping</a>
			<button type="submit" value="checkout" name="checkout" class="btn--small">Checkout</button>
		{{ Form::close() }}
	</div>

@else
	<div class="empty-cart">

		@if ($errors->has('expired'))
			<h1>Timed Out</h1>
			<p>Sorry, you have run out of time to complete your order</p>
			<p><a href="/">Return to home page</a></p>
		@else
			<h1>Empty Basket</h1>
			<p>You have nothing in your basket at the moment, add some items to continue.</p>
			<p><a href="/">Return to home page</a></p>
		@endif

	</div>
@endif

</div>
