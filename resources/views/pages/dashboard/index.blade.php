@extends('layouts.dashboard')

@section('title', 'Leads')

@section('body')
<header class="top">
  <div class="top__in">
    <div class="top__brand">
      <span class="top__mark">CB</span>
      <span>
        <strong>{{ config('dashboard.brand') }}</strong>
        <span>Leads dashboard</span>
      </span>
    </div>
    <div class="top__right">
      <span class="top__user">Signed in as {{ auth()->user()->username }}</span>
      <form method="POST" action="{{ route('dashboard.logout') }}">
        @csrf
        <button class="btn btn--ghost btn--sm" type="submit">Sign out</button>
      </form>
    </div>
  </div>
</header>

<livewire:dashboard.leads-table />
@endsection
