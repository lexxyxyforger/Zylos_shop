<?php

namespace App\Models;

use App\Traits\UUID;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'uuid',
    'parent_id',
    'image',
    'name',
    'slug',
    'tagline',
    'description'
])]
class ProductCategory extends Model
{
    use UUID;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Auto-generate slug dari nama
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    /**
     * Relasi: Mengambil kategori induk
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id', 'uuid');
    }

    /**
     * Relasi: Mengambil semua sub-kategori di bawahnya
     */
    public function children(): HasMany
    {
        return $this->hasMany(ProductCategory::class, 'parent_id', 'uuid');
    }
}