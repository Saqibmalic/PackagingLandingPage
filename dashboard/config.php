<?php
/**
 * Dashboard settings. Everything you are likely to change lives here.
 */

declare(strict_types=1);

return [

    /* ── Login ────────────────────────────────────────────────
       DEMO_MODE = true  → any non-empty username and password gets in.
                           Handy while you are testing; it means anyone who
                           finds the URL can read your leads.
       DEMO_MODE = false → only the accounts in 'users' below can log in.

       To add a real account, generate a hash on your VPS with:
           php -r 'echo password_hash("your-password", PASSWORD_DEFAULT), "\n";'
       and paste it in as the value.                                        */
    'demo_mode' => true,

    'users' => [
        // username => password hash. The seeded account is  admin / boxes123
        'admin' => '$2y$12$sfsmzaf30VUMbqCc0ozXkuY6SePPGJSa0OFwEgwYQFIhx422EBmlW',
    ],

    /* Minutes of inactivity before the dashboard logs you out. */
    'session_minutes' => 240,

    /* ── Google Ads export defaults ───────────────────────────
       'conversion_name' must match the conversion action name in Google Ads
       exactly (Goals → Conversions → Summary). 'timezone' must match the
       time zone of your Google Ads account, not your server.               */
    'conversion_name' => 'Quote Form Submit',
    'timezone'        => 'America/New_York',
    'currency'        => 'USD',

    /* Value written for a lead that has no value of its own set. */
    'default_value'   => 0,

    /* Brand strip at the top of the dashboard. */
    'brand'           => 'Custom Boxes Experts',
];
