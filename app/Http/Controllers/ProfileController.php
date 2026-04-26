<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Store;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $store = Store::query()->firstOrCreate(
            ['user_id' => $request->user()->uuid],
            [
                'name' => 'ZYLOS Official',
                'logo' => 'https://img.sanishtech.com/u/7bff45bea5098b102ff2d2be40ee0b4d.png',
                'about' => 'Premium curated lifestyle products from ZYLOS.',
                'city' => 'Bandung',
                'address' => 'Jl. Braga No. 12',
                'postal_code' => '40111',
                'is_verified' => true,
            ]
        );

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'storeProfile' => [
                'name' => $store->name,
                'about' => $store->about,
                'phone' => $store->phone,
                'city' => $store->city,
                'address' => $store->address,
                'postal_code' => $store->postal_code,
                'logo' => $store->logo,
            ],
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
