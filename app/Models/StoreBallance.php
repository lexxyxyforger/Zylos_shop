<?php

namespace App\Models;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['uuid', 'store_id', 'balance'])]
class StoreBalance extends Model
{
    use UUID;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Konversi saldo ke tipe float/decimal di PHP Laravel
     */
    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
        ];
    }

    /**
     * Relasi: Saldo ini milik Toko mana?
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id', 'uuid');
    }
}