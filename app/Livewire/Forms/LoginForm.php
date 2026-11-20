<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class LoginForm extends Form
{
    #[Validate('required|string')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $cleanedIdentifier = trim($this->email);
        $isEmail = filter_var($cleanedIdentifier, FILTER_VALIDATE_EMAIL) !== false;
        $loginType = $isEmail ? 'email' : 'username';
        $normalizedIdentifier = $isEmail ? Str::lower($cleanedIdentifier) : $cleanedIdentifier;

        if (! Auth::attempt([$loginType => $normalizedIdentifier, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'form.email' => trans('auth.failed'),
            ]);
        }

        /** @var User $authenticatedUser */
        $authenticatedUser = Auth::user();

        if (isset($authenticatedUser->is_active) && ! $authenticatedUser->is_active) {
            Auth::logout();
            Session::invalidate();
            Session::regenerateToken();

            RateLimiter::hit($this->throttleKey());

            logger()->warning("Login attempt for deactivated user: {$this->email}");

            throw ValidationException::withMessages([
                'form.email' => 'Akun Anda dinonaktifkan. Silakan hubungi tim IT Yusen.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        logger()->info("User [{$authenticatedUser->name} - {$authenticatedUser->username}] logged in successfully from IP: " . request()->ip());
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        logger()->warning("Login rate-limit locked for {$this->email} from IP " . request()->ip());

        throw ValidationException::withMessages([
            'form.email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email) . '|' . request()->ip());
    }
}
