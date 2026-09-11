<?php

namespace App\Livewire\Dashboard;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Component;

class LoginForm extends Component
{
    #[Validate('required|string|max:60')]
    public string $username = '';

    #[Validate('required|string|max:120')]
    public string $password = '';

    public bool $remember = false;

    public function login()
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        $authenticated = config('dashboard.demo_mode')
            ? $this->signInForDemo()
            : Auth::attempt(['username' => $this->username, 'password' => $this->password], $this->remember);

        if (! $authenticated) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'username' => 'That username and password did not match.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        session()->regenerate();

        return $this->redirectIntended(route('dashboard'), navigate: false);
    }

    /**
     * Demo mode: any username and password signs in. It reuses a matching
     * account if one exists so the demo session looks like the real thing,
     * and creates one otherwise so the dashboard is reachable on a fresh
     * install before the seeder has ever been run.
     */
    protected function signInForDemo(): bool
    {
        $user = User::firstWhere('username', $this->username)
            ?? User::create([
                'name' => Str::title($this->username),
                'username' => $this->username,
                'email' => $this->username.'@demo.invalid',
                'password' => Str::random(32),
            ]);

        Auth::login($user, $this->remember);

        return true;
    }

    /**
     * Five attempts a minute per username and IP. The dashboard is a public
     * URL holding customer contact details; without this it is one long
     * password list away from being open.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => "Too many attempts. Try again in {$seconds} seconds.",
        ]);
    }

    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->username).'|'.request()->ip());
    }

    public function render()
    {
        return view('livewire.dashboard.login-form');
    }
}
