@extends('layouts.dashboard')

@section('title', 'Sign in')
@section('body-class', 'login-body')

@section('body')
  <livewire:dashboard.login-form />
@endsection
