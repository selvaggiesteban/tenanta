<?php

namespace App\Services\AI\Tools;

class ToolDefinitions
{
    /**
     * Get all available tool definitions.
     */
    public static function all(): array
    {
        return [
            self::searchClients(),
            self::searchLeads(),
            self::getClientDetails(),
            self::getLeadDetails(),
            self::listTasks(),
            self::createTask(),
            self::getDashboardStats(),
            self::searchQuotes(),
        ];
    }

    /**
     * Get enabled tools based on configuration.
     */
    public static function enabled(): array
    {
        if (!config('ai.tools.enabled', true)) {
            return [];
        }

        $available = config('ai.tools.available', []);
        $allTools = self::all();

        return array_values(array_filter($allTools, function ($tool) use ($available) {
            return in_array($tool['name'], $available);
        }));
    }

    public static function searchClients(): array
    {
        return [
            'name' => 'search_clients',
            'description' => 'Buscar clientes en el CRM por nombre, email, teléfono o cualquier otro criterio. Devuelve una lista de clientes que coinciden con la búsqueda.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'query' => [
                        'type' => 'string',
                        'description' => 'Término de búsqueda (nombre, email, teléfono, etc.)',
                    ],
                    'status' => [
                        'type' => 'string',
                        'enum' => ['active', 'inactive', 'prospect'],
                        'description' => 'Filtrar por estado del cliente',
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Número máximo de resultados (default: 10)',
                    ],
                ],
                'required' => ['query'],
            ],
        ];
    }

    public static function searchLeads(): array
    {
        return [
            'name' => 'search_leads',
            'description' => 'Buscar leads/oportunidades en el pipeline de ventas. Permite filtrar por etapa, fuente o estado.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'query' => [
                        'type' => 'string',
                        'description' => 'Término de búsqueda',
                    ],
                    'stage' => [
                        'type' => 'string',
                        'description' => 'Filtrar por etapa del pipeline',
                    ],
                    'source' => [
                        'type' => 'string',
                        'description' => 'Filtrar por fuente del lead',
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Número máximo de resultados (default: 10)',
                    ],
                ],
            ],
        ];
    }

    public static function getClientDetails(): array
    {
        return [
            'name' => 'get_client_details',
            'description' => 'Obtener información detallada de un cliente específico, incluyendo contactos, leads y cotizaciones asociadas.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'client_id' => [
                        'type' => 'integer',
                        'description' => 'ID del cliente',
                    ],
                ],
                'required' => ['client_id'],
            ],
        ];
    }

    public static function getLeadDetails(): array
    {
        return [
            'name' => 'get_lead_details',
            'description' => 'Obtener información detallada de un lead específico, incluyendo historial de actividades y notas.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'lead_id' => [
                        'type' => 'integer',
                        'description' => 'ID del lead',
                    ],
                ],
                'required' => ['lead_id'],
            ],
        ];
    }

    public static function listTasks(): array
    {
        return [
            'name' => 'list_tasks',
            'description' => 'Listar tareas pendientes o filtradas por proyecto, asignación o estado.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'project_id' => [
                        'type' => 'integer',
                        'description' => 'Filtrar por proyecto',
                    ],
                    'assigned_to' => [
                        'type' => 'integer',
                        'description' => 'Filtrar por usuario asignado',
                    ],
                    'status' => [
                        'type' => 'string',
                        'enum' => ['pending', 'in_progress', 'review', 'completed'],
                        'description' => 'Filtrar por estado',
                    ],
                    'priority' => [
                        'type' => 'string',
                        'enum' => ['low', 'medium', 'high', 'urgent'],
                        'description' => 'Filtrar por prioridad',
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Número máximo de resultados (default: 10)',
                    ],
                ],
            ],
        ];
    }

    public static function createTask(): array
    {
        return [
            'name' => 'create_task',
            'description' => 'Crear una nueva tarea en un proyecto.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'title' => [
                        'type' => 'string',
                        'description' => 'Título de la tarea',
                    ],
                    'description' => [
                        'type' => 'string',
                        'description' => 'Descripción detallada de la tarea',
                    ],
                    'project_id' => [
                        'type' => 'integer',
                        'description' => 'ID del proyecto',
                    ],
                    'assigned_to' => [
                        'type' => 'integer',
                        'description' => 'ID del usuario a asignar',
                    ],
                    'priority' => [
                        'type' => 'string',
                        'enum' => ['low', 'medium', 'high', 'urgent'],
                        'description' => 'Prioridad de la tarea',
                    ],
                    'due_date' => [
                        'type' => 'string',
                        'description' => 'Fecha de vencimiento (YYYY-MM-DD)',
                    ],
                ],
                'required' => ['title', 'project_id'],
            ],
        ];
    }

    public static function getDashboardStats(): array
    {
        return [
            'name' => 'get_dashboard_stats',
            'description' => 'Obtener estadísticas generales del negocio: clientes activos, leads en pipeline, tareas pendientes, cotizaciones, etc.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'period' => [
                        'type' => 'string',
                        'enum' => ['today', 'week', 'month', 'quarter', 'year'],
                        'description' => 'Período para las estadísticas (default: month)',
                    ],
                ],
            ],
        ];
    }

    public static function searchQuotes(): array
    {
        return [
            'name' => 'search_quotes',
            'description' => 'Buscar cotizaciones por cliente, número o estado.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'query' => [
                        'type' => 'string',
                        'description' => 'Término de búsqueda',
                    ],
                    'client_id' => [
                        'type' => 'integer',
                        'description' => 'Filtrar por cliente',
                    ],
                    'status' => [
                        'type' => 'string',
                        'enum' => ['draft', 'sent', 'accepted', 'rejected', 'expired'],
                        'description' => 'Filtrar por estado',
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Número máximo de resultados (default: 10)',
                    ],
                ],
            ],
        ];
    }
}
