<?php

namespace App\Models;

use App\Traits\UUID;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'uuid',
    'store_id',
    'product_category_id',
    'name',
    'slug',
    'about',
    'condition',
    'price',
    'weight',
    'stock'
])]
class Product extends Model
{
    use UUID;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'weight' => 'integer',
            'stock' => 'integer',
        ];
    }

    /**
     * Auto-generate slug saat membuat produk
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    /**
     * Relasi ke Toko
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id', 'uuid');
    }

    /**
     * Relasi ke Kategori
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id', 'uuid');
    }
}