<?php

namespace App\Models;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'uuid',
    'store_ballance_id',
    'type',
    'reference_id',
    'reference_type',
    'amount',
    'remarks'
])]
class StoreBallanceHistory extends Model
{
    use UUID;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Relasi: Riwayat ini merujuk ke Saldo Toko mana?
     */
    public function storeBallance(): BelongsTo
    {
        return $this->belongsTo(StoreBallance::class, 'store_ballance_id', 'uuid');
    }
}