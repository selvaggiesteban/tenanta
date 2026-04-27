<?php

namespace App;

class ToolDefinitions
{
    /**
     * Get the available AI tools definitions for the conversational engine.
     */
    public static function getTools(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_crm_metrics',
                    'description' => 'Retrieve summary metrics for the CRM module (leads, clients, quotes).',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'create_support_ticket',
                    'description' => 'Create a new support ticket in the helpdesk module.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'subject' => ['type' => 'string', 'description' => 'Short summary of the issue'],
                            'description' => ['type' => 'string', 'description' => 'Detailed explanation of the issue'],
                        ],
                        'required' => ['subject', 'description'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_lms_enrollments',
                    'description' => 'Retrieve student enrollments for courses.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'course_id' => ['type' => 'integer', 'description' => 'Optional course ID to filter enrollments'],
                        ],
                    ],
                ],
            ],
        ];
    }
}
