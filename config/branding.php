<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Branding Configuration
    |--------------------------------------------------------------------------
    |
    | These values are used when a tenant doesn't have custom branding configured.
    | They serve as fallback values for the branding service.
    |
    */

    'defaults' => [
        // Logos
        'logo_light' => '/images/logo-light.png',
        'logo_dark' => '/images/logo-dark.png',
        'favicon' => '/favicon.ico',

        // Colors
        'primary_color' => '#696cff',
        'secondary_color' => '#8592a3',

        // Contact
        'contact_email' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'contact_phone' => null,
        'contact_address' => null,

        // Social Links
        'social_links' => [
            'facebook' => null,
            'twitter' => null,
            'instagram' => null,
            'linkedin' => null,
            'youtube' => null,
        ],

        // SEO
        'meta_title' => env('APP_NAME', 'Tenanta'),
        'meta_description' => 'Plataforma SaaS multi-tenant para gestión empresarial',
        'meta_keywords' => 'crm, gestión, proyectos, cursos, saas',

        // Regional
        'locale' => 'es_AR',
        'timezone' => 'America/Argentina/Buenos_Aires',
        'currency' => 'ARS',
        'date_format' => 'DD/MM/YYYY',
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency Symbols
    |--------------------------------------------------------------------------
    */

    'currency_symbols' => [
        'ARS' => '$',
        'USD' => 'US$',
        'EUR' => '€',
        'BRL' => 'R$',
        'CLP' => 'CLP$',
        'MXN' => 'MX$',
        'COP' => 'COP$',
    ],

    /*
    |--------------------------------------------------------------------------
    | Available Locales
    |--------------------------------------------------------------------------
    */

    'locales' => [
        'es_AR' => [
            'name' => 'Español (Argentina)',
            'native' => 'Español',
            'flag' => '🇦🇷',
        ],
        'en_US' => [
            'name' => 'English (US)',
            'native' => 'English',
            'flag' => '🇺🇸',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Available Timezones (South America focus)
    |--------------------------------------------------------------------------
    */

    'timezones' => [
        'America/Argentina/Buenos_Aires' => 'Argentina (Buenos Aires)',
        'America/Sao_Paulo' => 'Brasil (São Paulo)',
        'America/Santiago' => 'Chile (Santiago)',
        'America/Bogota' => 'Colombia (Bogotá)',
        'America/Lima' => 'Perú (Lima)',
        'America/Mexico_City' => 'México (Ciudad de México)',
        'America/New_York' => 'Estados Unidos (Nueva York)',
        'America/Los_Angeles' => 'Estados Unidos (Los Ángeles)',
        'Europe/Madrid' => 'España (Madrid)',
        'UTC' => 'UTC',
    ],
];
