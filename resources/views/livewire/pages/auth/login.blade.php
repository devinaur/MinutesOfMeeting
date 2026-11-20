<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('home', absolute: false), navigate: true);
    }
}; ?>

<div>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-5 text-center sm:text-left">
        <h2 class="text-base sm:text-lg font-bold text-[#002878] tracking-tight">Masuk ke MoM Assistant</h2>
        <p class="text-xs text-slate-500 mt-0.5">Gunakan email korporat atau username Anda.</p>
    </div>

    <form wire:submit="login" class="space-y-4">
        <div>
            <x-input-label for="email" :value="__('Email Korporat / Username')" class="text-xs font-bold text-[#002878]" />
            <x-text-input wire:model="form.email" id="email" class="block mt-1 w-full text-xs rounded-xl border-slate-300 focus:border-[#002878] focus:ring-[#002878] py-2 px-3 text-[#002878]" type="text" name="email" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-1 text-xs" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" class="text-xs font-bold text-[#002878]" />
            <x-text-input wire:model="form.password" id="password" class="block mt-1 w-full text-xs rounded-xl border-slate-300 focus:border-[#002878] focus:ring-[#002878] py-2 px-3 text-[#002878]"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('form.password')" class="mt-1 text-xs" />
        </div>

        <div class="flex items-center justify-between text-xs pt-1">
            <label class="inline-flex items-center cursor-pointer">
                <input type="checkbox" wire:model="form.remember" class="rounded border-slate-300 text-[#002878] shadow-xs focus:ring-[#002878]">
                <span class="ms-2 text-slate-600 font-medium text-xs">Ingat saya</span>
            </label>
        </div>

        <div class="pt-2">
            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="login"
                class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-[#002878] hover:bg-[#00236f] active:bg-[#001d5a] text-white text-xs sm:text-sm font-bold transition-all shadow-sm cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <span wire:loading.remove wire:target="login">Masuk</span>
                <span wire:loading.inline-flex wire:target="login" class="items-center justify-center gap-2">
                    <span class="whitespace-nowrap">Memverifikasi...</span>
                </span>
            </button>
        </div>
    </form>
</div>
