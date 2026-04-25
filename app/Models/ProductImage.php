<?php

namespace App\Models;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['uuid', 'product_id', 'image', 'is_thumbnail'])]
class ProductImage extends Model
{
    use UUID;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'is_thumbnail' => 'boolean',
        ];
    }

    /**
     * Relasi: Foto ini milik produk yang mana?
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'uuid');
    }
}