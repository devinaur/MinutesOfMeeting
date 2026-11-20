<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Livewire\Volt\Volt;

Route::middleware('guest')->group(function () {
    Volt::route('login', 'pages.auth.login')
        ->name('login');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', function () {
        $user = Auth::user();
        if ($user) {
            logger()->info("User [{$user->name} - {$user->username}] logged out.");
        }

        Auth::guard('web')->logout();
        Session::invalidate();
        Session::regenerateToken();

        return redirect()->route('login');
    })->name('logout');
});
