<?php

namespace App\Models;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['uuid', 'transaction_id', 'product_id', 'qty', 'subtotal'])]
class TransactionDetail extends Model
{
    use UUID;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'subtotal' => 'decimal:2',
        ];
    }

    /**
     * Relasi: Item ini bagian dari transaksi mana?
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'uuid');
    }

    /**
     * Relasi: Produk apa yang dibeli?
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'uuid');
    }
}