<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Where lead alerts are sent
    |--------------------------------------------------------------------------
    |
    | Every completed quote form emails this address. The reply-to is set to
    | the buyer, so hitting reply in your inbox answers them directly.
    |
    */

    'notify' => env('LEADS_NOTIFY_TO', 'info@customboxesexperts.com'),

    'notify_cc' => array_filter(explode(',', (string) env('LEADS_NOTIFY_CC', ''))),

    /*
    |--------------------------------------------------------------------------
    | Google Ads
    |--------------------------------------------------------------------------
    |
    | 'conversion_name' must match the offline conversion action in Google Ads
    | character for character, or the import is rejected. 'timezone' must match
    | the time zone of the Google Ads ACCOUNT, not the server — it is written
    | into the first line of the GCLID upload file.
    |
    */

    'conversion_name' => env('ADS_CONVERSION_NAME', 'Quote Form Submit'),
    'ads_timezone' => env('ADS_TIMEZONE', 'America/New_York'),
    'currency' => env('ADS_CURRENCY', 'USD'),
    'default_value' => (float) env('ADS_DEFAULT_VALUE', 0),

    /*
    |--------------------------------------------------------------------------
    | Artwork uploads
    |--------------------------------------------------------------------------
    |
    | Files land on the 'local' disk under storage/app/private, which sits
    | outside the web root — nothing uploaded can ever be requested, let alone
    | executed. The dashboard streams them back through an authenticated route.
    |
    */

    'upload_disk' => env('LEADS_UPLOAD_DISK', 'local'),
    'upload_path' => 'artwork',
    'max_files' => 5,
    'max_file_kb' => 20480,
    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf', 'ai', 'eps', 'zip'],

    /*
    |--------------------------------------------------------------------------
    | Pipeline statuses
    |--------------------------------------------------------------------------
    */

    'statuses' => ['new', 'contacted', 'quoted', 'won', 'lost', 'spam'],

];
