<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Executive electronic signing
    |--------------------------------------------------------------------------
    |
    | مفتاح مستقل لتواقيع المدير العام ونائب المدير العام.
    | في الإنتاج: EXECUTIVE_SIGNING_KEY=base64:... (32+ bytes)
    |
    */

    'executive_key' => env('EXECUTIVE_SIGNING_KEY', env('APP_KEY')),

    'verification_prefix' => env('EXECUTIVE_SIGNATURE_PREFIX', 'ESIG'),

];
