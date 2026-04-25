<?php

namespace App\Models;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'name',
    'logo',
    'about',
    'phone',
    'address_id',
    'city',
    'address',
    'postal_code',
    'is_verified',
    'uuid' // Tambahkan uuid ke fillable
])]
class Store extends Model
{
    use UUID;

    //UUID sebagai Primary Key
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Konversi tipe data otomatis
     */
    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'address_id' => 'integer',
        ];
    }

    /**
     * Relationship: Satu toko dimiliki oleh satu user
     */
    public function user(): BelongsTo
    {
        // Parameter kedua dan ketiga memastikan relasi nyambung via UUID
        return $this->belongsTo(User::class, 'user_id', 'uuid');
    }

    // store balance akan kita buat di model terpisah (StoreBalance) untuk menjaga single responsibility
    public function storeBalance()
    {
        return $this->hasOne(StoreBalance::class, 'store_id', 'uuid');
    }
}