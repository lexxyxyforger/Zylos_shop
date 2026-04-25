@extends('layouts.app')

@section('title', $product->name . ' | ZYLOS')

@section('content')
    <div id="product-show-app"></div>

    <script type="application/json" id="product-show-props">
        {!! json_encode([
            'product' => $productPayload,
            'images' => $images,
            'sizes' => $sizeOptions,
            'urls' => [
                'store' => route('store.index'),
                'cart' => route('cart.index'),
                'addToCart' => route('cart.items.store'),
            ],
        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
    </script>
@endsection
