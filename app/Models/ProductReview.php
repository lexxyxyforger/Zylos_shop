<?php

namespace App\Models;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['uuid', 'transaction_id', 'product_id', 'rating', 'review'])]
class ProductReview extends Model
{
    use UUID;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    /**
     * Relasi: Review ini berasal dari transaksi mana?
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'uuid');
    }

    /**
     * Relasi: Review ini ditujukan untuk produk apa?
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'uuid');
    }
}