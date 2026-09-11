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

    'hours' => [
        'Mon–Fri: 9:00am – 7:00pm PST',
        'Saturday: 10:00am – 4:00pm PST',
        'Sunday: Closed',
    ],

    'main_site' => 'https://www.customboxesexperts.com/',

    /*
    | The public URL of the rigid boxes landing page, used for the canonical
    | tag and og:url. Keep the trailing slash.
    */
    'canonical' => env('SITE_CANONICAL', 'https://www.customboxesexperts.com/custom-rigid-boxes/'),

    /*
    | Your own YouTube Shorts. Paste full URLs or bare video IDs, optionally
    | followed by "| a caption". Empty means the section hides itself.
    */
    'shorts' => array_filter(explode(',', (string) env('SITE_SHORTS', ''))),
    'shorts_channel' => env('SITE_SHORTS_CHANNEL', ''),

];
