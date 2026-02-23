<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default AI provider that will be used by the
    | application. You may set this to any of the providers defined below.
    |
    */

    'default' => env('AI_PROVIDER', 'claude'),

    /*
    |--------------------------------------------------------------------------
    | AI Providers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the AI providers used by your application.
    | Each provider has its own configuration options.
    |
    */

    'providers' => [

        'claude' => [
            'driver' => 'anthropic',
            'api_key' => env('ANTHROPIC_API_KEY'),
            'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-4-20250514'),
            'max_tokens' => 4096,
            'base_url' => 'https://api.anthropic.com/v1',
        ],

        'openai' => [
            'driver' => 'openai',
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4o'),
            'max_tokens' => 4096,
            'base_url' => 'https://api.openai.com/v1',
        ],

        'gemini' => [
            'driver' => 'google',
            'api_key' => env('GOOGLE_API_KEY'),
            'model' => env('GOOGLE_MODEL', 'gemini-1.5-pro'),
            'max_tokens' => 4096,
            'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | System Prompt
    |--------------------------------------------------------------------------
    |
    | The default system prompt used for the AI assistant.
    |
    */

    'system_prompt' => <<<'PROMPT'
Eres un asistente de IA para Tenanta CRM. Tu rol es ayudar a los usuarios con:

- Consultar información de clientes, contactos y leads
- Gestionar tareas y proyectos
- Crear cotizaciones y seguimientos
- Buscar información en el sistema
- Responder preguntas sobre el negocio

Siempre responde en español de manera profesional y concisa.
Cuando uses herramientas, explica brevemente qué estás haciendo.
Si no tienes acceso a cierta información, indícalo claramente.
PROMPT,

    /*
    |--------------------------------------------------------------------------
    | Tools Configuration
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific AI tools.
    |
    */

    'tools' => [
        'enabled' => true,
        'available' => [
            'search_clients',
            'search_leads',
            'get_client_details',
            'get_lead_details',
            'list_tasks',
            'create_task',
            'get_dashboard_stats',
            'search_quotes',
        ],
    ],

];
