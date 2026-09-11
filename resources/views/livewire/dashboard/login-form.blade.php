<div class="login">
  <div class="login__card">
    <div class="login__brand">
      <span class="login__mark">CB</span>
      <span>
        <strong>{{ config('dashboard.brand') }}</strong>
        <span>Leads dashboard</span>
      </span>
    </div>

    <form wire:submit="login">
      @error('username') <p class="alert alert--bad">{{ $message }}</p> @enderror

      <label for="u">Username</label>
      <input id="u" wire:model="username" type="text" autocomplete="username" autofocus>

      <label for="p">Password</label>
      <input id="p" wire:model="password" type="password" autocomplete="current-password">
      @error('password') <p class="alert alert--bad">{{ $message }}</p> @enderror

      <label class="login__remember">
        <input type="checkbox" wire:model="remember"> Keep me signed in
      </label>

      <button class="btn btn--primary btn--block" type="submit" wire:loading.attr="disabled">
        <span wire:loading.remove wire:target="login">Sign in</span>
        <span wire:loading wire:target="login">Signing in&hellip;</span>
      </button>
    </form>

    @if (config('dashboard.demo_mode'))
      <p class="login__demo">
        <strong>Demo mode is on</strong> &mdash; any username and password will sign in.
        Set <code>DASHBOARD_DEMO_MODE=false</code> in <code>.env</code> before this site
        takes live traffic; these are real customers&rsquo; contact details.
      </p>
    @endif
  </div>
</div>
