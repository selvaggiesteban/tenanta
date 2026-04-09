<?php

return [
    'inicial' => [
        'name' => 'Plan Inicial',
        'price_monthly' => 57000,
        'price_yearly' => 57000 * 10, // 2 meses de ahorro
        'features' => [
            'Landing Page SEO automática',
            'CRM Financiero Básico',
            'Gestión de Tareas',
            'Soporte por Tickets',
        ],
    ],
    'crecimiento' => [
        'name' => 'Plan Crecimiento',
        'price_monthly' => 100000,
        'price_yearly' => 100000 * 10,
        'features' => [
            'Todo en Inicial',
            'Módulo de WhatsApp Directo',
            'Análisis SEO Avanzado',
            'Integración con WordPress',
        ],
    ],
    'dominacion' => [
        'name' => 'Plan Dominación',
        'price_monthly' => 200000,
        'price_yearly' => 200000 * 10,
        'features' => [
            'Todo en Crecimiento',
            'Dashboard Financiero Full (Dizteku/Piblo/CMO)',
            'Onboarding Masivo Garantizado',
            'Prioridad en Soporte',
        ],
    ],
];
