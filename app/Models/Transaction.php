<?php

namespace App\Models;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'uuid', 'code', 'buyer_id', 'store_id', 'address_id', 'address', 
    'city', 'postal_code', 'shipping', 'shipping_type', 
    'shipping_cost', 'tracking_number', 'tax', 'grand_total', 'payment_status'
])]
class Transaction extends Model
{
    use UUID;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'shipping_cost' => 'decimal:2',
            'tax' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'address_id' => 'integer',
        ];
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class, 'buyer_id', 'uuid');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id', 'uuid');
    }

    /**
     * Relasi: Satu transaksi biasanya punya banyak item produk (tabel berikutnya)
     */
    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class, 'transaction_id', 'uuid');
    }
}