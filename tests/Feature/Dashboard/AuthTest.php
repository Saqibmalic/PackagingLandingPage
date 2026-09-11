<?php

namespace Tests\Feature\Dashboard;

use App\Livewire\Dashboard\LoginForm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('admin|127.0.0.1');
    }

    #[Test]
    public function the_dashboard_is_closed_to_anyone_not_signed_in(): void
    {
        $this->get('/dashboard')->assertRedirect(route('dashboard.login'));
        $this->get('/dashboard/export?type=all')->assertRedirect(route('dashboard.login'));
    }

    #[Test]
    public function demo_mode_accepts_any_credentials(): void
    {
        config(['dashboard.demo_mode' => true]);

        Livewire::test(LoginForm::class)
            ->set('username', 'anyone')
            ->set('password', 'anything')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
    }

    #[Test]
    public function demo_mode_still_refuses_an_empty_password(): void
    {
        config(['dashboard.demo_mode' => true]);

        Livewire::test(LoginForm::class)
            ->set('username', 'anyone')
            ->set('password', '')
            ->call('login')
            ->assertHasErrors(['password' => 'required']);

        $this->assertGuest();
    }

    #[Test]
    public function with_demo_mode_off_only_the_real_password_works(): void
    {
        config(['dashboard.demo_mode' => false]);

        User::factory()->create(['username' => 'admin', 'password' => 'boxes123']);

        Livewire::test(LoginForm::class)
            ->set('username', 'admin')
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors(['username']);

        $this->assertGuest();

        Livewire::test(LoginForm::class)
            ->set('username', 'admin')
            ->set('password', 'boxes123')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
    }

    #[Test]
    public function repeated_failures_are_rate_limited(): void
    {
        config(['dashboard.demo_mode' => false]);
        User::factory()->create(['username' => 'admin', 'password' => 'boxes123']);

        foreach (range(1, 5) as $attempt) {
            Livewire::test(LoginForm::class)
                ->set('username', 'admin')
                ->set('password', 'wrong')
                ->call('login')
                ->assertHasErrors(['username']);
        }

        // The sixth attempt is refused before the password is even checked —
        // so the correct password does not get in either.
        Livewire::test(LoginForm::class)
            ->set('username', 'admin')
            ->set('password', 'boxes123')
            ->call('login')
            ->assertHasErrors(['username']);

        $this->assertGuest();
    }

    #[Test]
    public function signing_out_ends_the_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.logout'))
            ->assertRedirect(route('dashboard.login'));

        $this->assertGuest();
    }
}
