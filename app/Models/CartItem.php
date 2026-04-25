<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['uuid', 'cart_id', 'product_id', 'product_size_id', 'quantity'];

    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id', 'uuid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'uuid');
    }

    public function size()
    {
        return $this->belongsTo(ProductSize::class, 'product_size_id', 'uuid');
    }
}