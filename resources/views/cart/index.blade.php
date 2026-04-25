@extends('layouts.app')

@section('title', 'Keranjang | ZYLOS')

@section('content')
    <div id="cart-page-app"></div>

    <script type="application/json" id="cart-page-props">
        {!! json_encode([
            'cart' => $cartPayload,
            'urls' => [
                'store' => route('store.index'),
                'checkout' => route('checkout.index'),
                'updateBase' => url('/cart/items'),
                'clear' => route('cart.clear'),
            ],
        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
    </script>
@endsection
