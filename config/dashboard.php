<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Demo mode
    |--------------------------------------------------------------------------
    |
    | While this is true ANY username and password combination signs in, which
    | is useful for showing the dashboard to someone before real accounts
    | exist. Set DASHBOARD_DEMO_MODE=false in .env before the site takes live
    | traffic — leads are customer data.
    |
    */

    'demo_mode' => filter_var(env('DASHBOARD_DEMO_MODE', true), FILTER_VALIDATE_BOOL),

    /*
    |--------------------------------------------------------------------------
    | The account created by the seeder
    |--------------------------------------------------------------------------
    |
    | `php artisan db:seed` creates this user. Change the password in .env and
    | re-run the seeder, or use `php artisan dashboard:user` to add more.
    |
    */

    'seed_user' => [
        'name' => env('DASHBOARD_USER_NAME', 'Sales'),
        'username' => env('DASHBOARD_USERNAME', 'admin'),
        'email' => env('DASHBOARD_USER_EMAIL', 'info@customboxesexperts.com'),
        'password' => env('DASHBOARD_PASSWORD', 'boxes123'),
    ],

    'brand' => env('DASHBOARD_BRAND', 'Custom Boxes Experts'),

    'per_page' => 50,

];
