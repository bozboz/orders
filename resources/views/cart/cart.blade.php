@extends('layouts.default')

@section('page_title', 'Your Cart')

@section('main')

    <section>
        @if ($cart && $cart->items->count())

            {{ Form::open(array('route' => 'cart')) }}

                <header>
                    <div class="grid__item medium-12">{{ $errors->first() }}</div><!--

                 --><div class="grid__item large-1"></div><!--
                 --><div class="grid__item medium-6 large-8"></div><!--
                 --><div class="grid__item medium-2 large-1">Price</div><!--
                 --><div class="grid__item medium-2 large-1">Quantity</div><!--
                 --><div class="grid__item medium-2 large-1">Total</div>
                </header>

                <div class="grid">
                    @foreach($cart->items as $item)
                        <div>
                            <div class="grid__item medium-2 large-1">
                                {{ HTML::image($item->image, $item->name) }}
                            </div><!--
                         --><div class="grid__item small-10 medium-4 large-8">
                                <h4 class="cart-product__title">{{ $item->name }}</h4>
                                <a href="{{ URL::route('cart.remove-item', [$item->id, Session::token()]) }}" class="btn"><i class="fa fa-remove"></i> Remove</a>
                            </div><!--
                         --><div class="grid__item small-2 medium-2 large-1">{{ format_money($item->price_pence_ex_vat) }}</div><!--
                         --><div class="grid__item small-4 medium-2 large-1">
                                @if ($item->orderable->canAdjustQuantity())
                                    {{ Form::text('quantity[' . $item->id . ']', $item->quantity) }}
                                @else
                                    {{ Form::hidden('quantity[' . $item->id . ']', 1 )}}
                                @endif
                            </div><!--
                         --><div class="grid__item small-8 medium-2 large-1">{{ format_money($item->total_price_pence_ex_vat) }}</div>
                        </div>
                    @endforeach
                    <div>
                        <div class="grid__item medium-2 large-1"></div><!--
                     --><div class="grid__item small-10 medium-4 large-8">VAT</div><!--
                     --><div class="grid__item small-2 medium-2 large-1"></div><!--
                     --><div class="grid__item small-4 medium-2 large-1"></div><!--
                     --><div class="grid__item small-8 medium-2 large-1">{{ format_money($cart->totalTax()) }}</div>
                    </div>
                </div>

                <div><!--
                 --><div class="grid__item medium-8 large-10">
                        <button type="submit" value="update" name="update" class="btn--outlined--small">Update</button>
                        <button type="submit" value="clear" name="clear" class="btn--outlined--small">Clear Basket</button>
                    </div><!--
                 --><div class="grid__item medium-2 large-1">{{ $cart->totalQuantity() }}</div><!--
                 --><div class="grid__item medium-2 large-1">{{ format_money($cart->totalPrice()) }}</div>
                </div>

            {{ Form::close() }}

            {{ Form::open(array('route' => 'checkout')) }}
                <a class="btn--alt--small" href="#">Continue Shopping</a>
                <button type="submit" value="checkout" name="checkout" class="btn--small">Checkout</button>
            {{ Form::close() }}

        @else

            @if ($errors->has('expired'))
                <h1>Timed Out</h1>
                <p>Sorry, you have run out of time to complete your order</p>
                <p><a href="/">Return to home page</a></p>
            @else
                <h1>Empty Basket</h1>
                <p>You have nothing in your basket at the moment, add some items to continue.</p>
                <p><a href="/">Return to home page</a></p>
            @endif

        @endif
    </section>

@stop
