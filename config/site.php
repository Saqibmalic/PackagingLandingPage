<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Business details
    |--------------------------------------------------------------------------
    |
    | Every page, the schema.org block and the lead alert email read these, so
    | a change of phone number or address is a one-line edit here rather than a
    | hunt through the markup.
    |
    */

    'company' => 'Custom Boxes Experts',
    'phone' => '(888) 716-1078',
    'phone_e164' => '+18887161078',
    'email' => 'info@customboxesexperts.com',

    'address' => [
        'street' => '1227 Solano Ave #9',
        'city' => 'Albany',
        'region' => 'CA',
        'postal_code' => '94706',
        'country' => 'US',
    ],

    /*
    | Both time zones are stated deliberately. The ad schedule is set in
    | Eastern time but the office runs on Pacific, and a buyer clicking at
    | 9am in New York needs to know someone is already at a desk. Weekday
    | cover starts at 6:00am Pacific precisely so it opens at 9:00am
    | Eastern, matching the first hour ads are allowed to serve.
    */
    'hours' => [
        'Mon–Fri: 6:00am – 7:00pm PT (9:00am – 10:00pm ET)',
        'Saturday: 10:00am – 4:00pm PT (1:00pm – 7:00pm ET)',
        'Sunday: Closed',
    ],

    'main_site' => 'https://www.customboxesexperts.com/',

    /*
    | The public URL of the rigid boxes landing page, used for the canonical
    | tag and og:url. Keep the trailing slash.
    |
    | This must point at the page the visitor is actually on. Pointing it at
    | the main site told Google that this page was a duplicate of a different
    | URL, which suppresses it in search and hands any shared link the wrong
    | title card.
    */
    'canonical' => env('SITE_CANONICAL', 'https://offers.customboxesexperts.com/'),

    /*
    | Your own YouTube Shorts. Paste full URLs or bare video IDs, optionally
    | followed by "| a caption". Empty means the section hides itself.
    */
    'shorts' => array_filter(explode(',', (string) env('SITE_SHORTS', ''))),
    'shorts_channel' => env('SITE_SHORTS_CHANNEL', ''),

];
