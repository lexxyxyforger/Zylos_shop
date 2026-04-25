<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'stores' => Store::query()->count(),
            'products' => Product::query()->count(),
            'users' => User::query()->count(),
        ];

        $recentProducts = Product::query()
            ->select(['name', 'price', 'condition', 'created_at'])
            ->latest()
            ->take(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentProducts'));
    }
}
