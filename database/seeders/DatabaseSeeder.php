<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::query()->firstOrNew([
            'email' => 'adawong@zylos.local',
        ]);

        if (! $admin->exists) {
            $admin->uuid = (string) Str::uuid();
        }

        $admin->fill([
            'name' => 'AdaWong',
            'password' => 'AdaWong My Bini',
            'role' => 'admin',
        ])->save();

        foreach ([
            [
                'name' => 'Sepatu',
                'slug' => 'sepatu',
                'tagline' => 'Sneakers dan footwear pilihan',
                'description' => 'Koleksi sepatu original untuk kebutuhan kasual, sport, dan streetwear.',
            ],
            [
                'name' => 'Baju',
                'slug' => 'baju',
                'tagline' => 'Apparel harian dan streetwear',
                'description' => 'Koleksi baju pilihan untuk outfit harian, casual, dan lifestyle.',
            ],
        ] as $categoryData) {
            $category = ProductCategory::query()->firstOrNew([
                'slug' => $categoryData['slug'],
            ]);

            if (! $category->exists) {
                $category->uuid = (string) Str::uuid();
            }

            $category->fill($categoryData)->save();
        }
    }
}
