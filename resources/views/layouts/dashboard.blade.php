<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>@yield('title', 'Leads') &middot; {{ config('dashboard.brand') }}</title>
{{-- No dashboard JavaScript of its own: expanding a lead, saving a status and
     deleting are all Livewire actions now. --}}
@vite(['resources/css/dashboard.css'])
</head>
<body class="@yield('body-class')">
@yield('body')
@livewireScripts
</body>
</html>
