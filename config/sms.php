<?php

return [
    'sms_service' => env('SMS_SERVICE'),

    'sigmasms' => [
        'login' => env('SMS_SIGMASMS_LOGIN'),
        'password' => env('SMS_SIGMASMS_PASSWORD'),
        'time_cache' => 21600,
        'sender' => [
            'sms' => env('SMS_SIGMASMS_SENDER_SMS'),
            'viber' => env('SMS_SIGMASMS_SENDER_VIBER'),
            'vk' => env('SMS_SIGMASMS_SENDER_VK'),
            'whats_app' => env('SMS_SIGMASMS_SENDER_WHATS_APP')
        ],
    ],

];
