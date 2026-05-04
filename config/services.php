<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'whatsapp' => [
        'driver'       => env('WHATSAPP_DRIVER', 'log'),
        'zenvia_token' => env('WHATSAPP_ZENVIA_TOKEN'),
        'zenvia_from'  => env('WHATSAPP_ZENVIA_FROM'),
        'zenvia_country' => env('WHATSAPP_ZENVIA_COUNTRY', '57'),
    ],

    'email' => [
        'driver'                 => env('EMAIL_DRIVER', 'log'),
        // Zenvia
        'zenvia_token'           => env('EMAIL_ZENVIA_TOKEN'),
        'zenvia_from_name'       => env('EMAIL_ZENVIA_FROM_NAME', 'Promocion'),
        'zenvia_from_address'    => env('EMAIL_ZENVIA_FROM_ADDRESS'),
        // Infobip
        'infobip_api_key'        => env('EMAIL_INFOBIP_API_KEY'),
        'infobip_base_url'       => env('EMAIL_INFOBIP_BASE_URL', 'https://api.infobip.com'),
        'infobip_from_name'      => env('EMAIL_INFOBIP_FROM_NAME', 'Promocion'),
        'infobip_from_address'   => env('EMAIL_INFOBIP_FROM_ADDRESS'),
    ],

    'sms' => [
        'driver'            => env('SMS_DRIVER', 'log'),
        'from'              => env('SMS_FROM', 'Promocion'),
        // Infobip
        'infobip_api_key'   => env('SMS_INFOBIP_API_KEY'),
        'infobip_base_url'  => env('SMS_INFOBIP_BASE_URL', 'https://api.infobip.com'),
        'infobip_from'        => env('SMS_INFOBIP_FROM', 'Promocion'),
        // LabsMobile
        'labsmobile_username' => env('SMS_LABSMOBILE_USERNAME'),
        'labsmobile_token'    => env('SMS_LABSMOBILE_TOKEN'),
        'labsmobile_tpoa'     => env('SMS_LABSMOBILE_TPOA', 'Promocion'),
        'labsmobile_country'  => env('SMS_LABSMOBILE_COUNTRY', '57'),
        // Twilio
        'twilio_sid'        => env('SMS_TWILIO_SID'),
        'twilio_token'      => env('SMS_TWILIO_TOKEN'),
        'twilio_from'       => env('SMS_TWILIO_FROM'),
        // Zenvia
        'zenvia_token'      => env('SMS_ZENVIA_TOKEN'),
        'zenvia_from'       => env('SMS_ZENVIA_FROM', 'Promocion'),
        'zenvia_country'    => env('SMS_ZENVIA_COUNTRY', '57'), // Colombia por defecto
    ],

];
