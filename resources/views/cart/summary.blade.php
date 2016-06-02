<a href="{{ route('cart') }}">
    <i class="icon icon-basket"></i>

    {{ $cart->totalQuantity() }}
    {{ format_money($cart->totalPrice()) }}
</a>
