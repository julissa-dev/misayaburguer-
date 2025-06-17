<?php
/**
 * PayPal SDK configuration for srmklive/paypal
 *
 * IMPORTANT: You need to set your PayPal credentials in your .env file.
 *
 * For Sandbox (testing):
 * PAYPAL_MODE=sandbox
 * PAYPAL_SANDBOX_CLIENT_ID=YOUR_SANDBOX_CLIENT_ID
 * PAYPAL_SANDBOX_CLIENT_SECRET=YOUR_SANDBOX_CLIENT_SECRET
 *
 * For Live (production):
 * PAYPAL_MODE=live
 * PAYPAL_LIVE_CLIENT_ID=YOUR_LIVE_CLIENT_ID
 * PAYPAL_LIVE_CLIENT_SECRET=YOUR_LIVE_CLIENT_SECRET
 */
return [
    'mode'    => env('PAYPAL_MODE', 'sandbox'), // 'sandbox' or 'live'

    'sandbox' => [
        'client_id'         => env('PAYPAL_SANDBOX_CLIENT_ID', ''),
        'client_secret'     => env('PAYPAL_SANDBOX_CLIENT_SECRET', ''),
        'app_id'            => 'APP-80W284485P519543T', // Default for sandbox
    ],

    'live' => [
        'client_id'         => env('PAYPAL_LIVE_CLIENT_ID', ''),
        'client_secret'     => env('PAYPAL_LIVE_CLIENT_SECRET', ''),
        'app_id'            => '', // Leave empty or set your Live App ID if you have one
    ],

    'payment_action' => 'Sale', // Can be 'Sale', 'Authorization', or 'Order'
    'currency'       => 'USD',  // Set your default currency (e.g., 'USD', 'PEN', 'EUR')
    'notify_url'     => '',     // Optional: URL to receive IPN notifications
    'locale'         => 'es_PE',// Optional: Set locale (e.g., 'en_US', 'es_PE')
    'validate_ssl'   => true,   // Validate SSL certificates for API calls
];