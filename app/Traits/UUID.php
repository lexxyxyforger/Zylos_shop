<?php 
namespace App\Traits;

use Illuminate\Support\Str;

trait UUID
{
    protected static function bootUUID()
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}