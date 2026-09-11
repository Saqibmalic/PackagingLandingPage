<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Google Ads and GA4
    |--------------------------------------------------------------------------
    |
    | Leave any of these empty and the matching tag is simply not rendered, so
    | the site runs clean in local development and in tests. Fill them in .env
    | before spending a cent on traffic — without 'ads_id' and 'lead_label'
    | Google Ads cannot count a single conversion.
    |
    |   ads_id      AW-XXXXXXXXXX      Google Ads > Admin > Account settings
    |   ga4_id      G-XXXXXXXXXX       GA4 > Admin > Data streams
    |   lead_label  AW-XXXXXXXXXX/AbC… Goals > Conversions > "Quote Form Submit"
    |   call_label  AW-XXXXXXXXXX/AbC… the click-to-call action (mark SECONDARY)
    |
    */

    'ads_id' => env('GOOGLE_ADS_ID'),
    'ga4_id' => env('GA4_MEASUREMENT_ID'),
    'lead_label' => env('GOOGLE_ADS_LEAD_LABEL'),
    'call_label' => env('GOOGLE_ADS_CALL_LABEL'),

];
