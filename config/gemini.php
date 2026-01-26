<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Gemini API Key
    |--------------------------------------------------------------------------
    |
    | The API key used to authenticate with the Gemini API.
    |
    */
    'api_key' => env('GEMINI_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The maximum number of seconds to wait for a response from the API.
    |
    */
    'request_timeout' => env('GEMINI_REQUEST_TIMEOUT', 30),
];
