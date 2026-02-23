<?php

namespace App\Services\AI\Tools;

use App\Models\Client;
use App\Models\Lead;
use App\Models\Quote;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ToolExecutor
{
    /**
     * Execute a tool by name with given input.
     */
    public function execute(string $toolName, array $input): array
    {
        return match ($toolName) {
            'search_clients' => $this->searchClients($input),
            'search_leads' => $this->searchLeads($input),
            'get_client_details' => $this->getClientDetails($input),
            'get_lead_details' => $this->getLeadDetails($input),
            'list_tasks' => $this->listTasks($input),
            'create_task' => $this->createTask($input),
            'get_dashboard_stats' => $this->getDashboardStats($input),
            'search_quotes' => $this->searchQuotes($input),
            default => ['error' => "Herramienta desconocida: {$toolName}"],
        };
    }

    protected function searchClients(array $input): array
    {
        $query = Client::query();

        if (!empty($input['query'])) {
            $search = $input['query'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            });
        }

        if (!empty($input['status'])) {
            $query->where('status', $input['status']);
        }

        $limit = $input['limit'] ?? 10;
        $clients = $query->limit($limit)->get(['id', 'name', 'email', 'phone', 'company', 'status']);

        return [
            'success' => true,
            'count' => $clients->count(),
            'clients' => $clients->map(fn($c) => [
                'id' => $c->id,
                'nombre' => $c->name,
                'email' => $c->email,
                'telefono' => $c->phone,
                'empresa' => $c->company,
                'estado' => $c->status,
            ])->toArray(),
        ];
    }

    protected function searchLeads(array $input): array
    {
        $query = Lead::with(['client', 'stage']);

        if (!empty($input['query'])) {
            $search = $input['query'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (!empty($input['stage'])) {
            $query->whereHas('stage', function ($q) use ($input) {
                $q->where('name', 'like', "%{$input['stage']}%");
            });
        }

        if (!empty($input['source'])) {
            $query->where('source', $input['source']);
        }

        $limit = $input['limit'] ?? 10;
        $leads = $query->limit($limit)->get();

        return [
            'success' => true,
            'count' => $leads->count(),
            'leads' => $leads->map(fn($l) => [
                'id' => $l->id,
                'titulo' => $l->title,
                'cliente' => $l->client?->name,
                'etapa' => $l->stage?->name,
                'valor' => $l->value,
                'probabilidad' => $l->probability,
                'fuente' => $l->source,
            ])->toArray(),
        ];
    }

    protected function getClientDetails(array $input): array
    {
        $client = Client::with(['contacts', 'leads.stage', 'quotes'])
            ->find($input['client_id']);

        if (!$client) {
            return ['error' => 'Cliente no encontrado'];
        }

        return [
            'success' => true,
            'cliente' => [
                'id' => $client->id,
                'nombre' => $client->name,
                'email' => $client->email,
                'telefono' => $client->phone,
                'empresa' => $client->company,
                'direccion' => $client->address,
                'estado' => $client->status,
                'notas' => $client->notes,
                'creado' => $client->created_at->format('d/m/Y'),
            ],
            'contactos' => $client->contacts->map(fn($c) => [
                'nombre' => $c->name,
                'email' => $c->email,
                'telefono' => $c->phone,
                'cargo' => $c->position,
            ])->toArray(),
            'leads' => $client->leads->map(fn($l) => [
                'titulo' => $l->title,
                'etapa' => $l->stage?->name,
                'valor' => $l->value,
            ])->toArray(),
            'cotizaciones' => $client->quotes->count(),
        ];
    }

    protected function getLeadDetails(array $input): array
    {
        $lead = Lead::with(['client', 'stage', 'assignedTo'])
            ->find($input['lead_id']);

        if (!$lead) {
            return ['error' => 'Lead no encontrado'];
        }

        return [
            'success' => true,
            'lead' => [
                'id' => $lead->id,
                'titulo' => $lead->title,
                'descripcion' => $lead->description,
                'cliente' => $lead->client?->name,
                'etapa' => $lead->stage?->name,
                'valor' => $lead->value,
                'probabilidad' => $lead->probability,
                'fuente' => $lead->source,
                'asignado_a' => $lead->assignedTo?->name,
                'fecha_cierre_esperada' => $lead->expected_close_date?->format('d/m/Y'),
                'creado' => $lead->created_at->format('d/m/Y'),
            ],
        ];
    }

    protected function listTasks(array $input): array
    {
        $query = Task::with(['project', 'assignedTo']);

        if (!empty($input['project_id'])) {
            $query->where('project_id', $input['project_id']);
        }

        if (!empty($input['assigned_to'])) {
            $query->where('assigned_to', $input['assigned_to']);
        }

        if (!empty($input['status'])) {
            $query->where('status', $input['status']);
        }

        if (!empty($input['priority'])) {
            $query->where('priority', $input['priority']);
        }

        $limit = $input['limit'] ?? 10;
        $tasks = $query->orderBy('due_date')->limit($limit)->get();

        return [
            'success' => true,
            'count' => $tasks->count(),
            'tareas' => $tasks->map(fn($t) => [
                'id' => $t->id,
                'titulo' => $t->title,
                'proyecto' => $t->project?->name,
                'asignado_a' => $t->assignedTo?->name,
                'estado' => $t->status,
                'prioridad' => $t->priority,
                'vencimiento' => $t->due_date?->format('d/m/Y'),
            ])->toArray(),
        ];
    }

    protected function createTask(array $input): array
    {
        $task = Task::create([
            'title' => $input['title'],
            'description' => $input['description'] ?? null,
            'project_id' => $input['project_id'],
            'assigned_to' => $input['assigned_to'] ?? Auth::id(),
            'created_by' => Auth::id(),
            'priority' => $input['priority'] ?? 'medium',
            'due_date' => isset($input['due_date']) ? Carbon::parse($input['due_date']) : null,
            'status' => 'pending',
        ]);

        return [
            'success' => true,
            'mensaje' => 'Tarea creada exitosamente',
            'tarea' => [
                'id' => $task->id,
                'titulo' => $task->title,
                'proyecto' => $task->project?->name,
            ],
        ];
    }

    protected function getDashboardStats(array $input): array
    {
        $period = $input['period'] ?? 'month';
        $startDate = $this->getStartDate($period);

        $stats = [
            'clientes' => [
                'total' => Client::count(),
                'activos' => Client::where('status', 'active')->count(),
                'nuevos_periodo' => Client::where('created_at', '>=', $startDate)->count(),
            ],
            'leads' => [
                'total' => Lead::count(),
                'nuevos_periodo' => Lead::where('created_at', '>=', $startDate)->count(),
                'valor_pipeline' => Lead::sum('value'),
            ],
            'tareas' => [
                'pendientes' => Task::where('status', 'pending')->count(),
                'en_progreso' => Task::where('status', 'in_progress')->count(),
                'completadas_periodo' => Task::where('status', 'completed')
                    ->where('updated_at', '>=', $startDate)
                    ->count(),
            ],
            'cotizaciones' => [
                'total' => Quote::count(),
                'pendientes' => Quote::whereIn('status', ['draft', 'sent'])->count(),
                'aceptadas_periodo' => Quote::where('status', 'accepted')
                    ->where('updated_at', '>=', $startDate)
                    ->count(),
            ],
            'periodo' => $period,
        ];

        return [
            'success' => true,
            'estadisticas' => $stats,
        ];
    }

    protected function searchQuotes(array $input): array
    {
        $query = Quote::with(['client']);

        if (!empty($input['query'])) {
            $search = $input['query'];
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        if (!empty($input['client_id'])) {
            $query->where('client_id', $input['client_id']);
        }

        if (!empty($input['status'])) {
            $query->where('status', $input['status']);
        }

        $limit = $input['limit'] ?? 10;
        $quotes = $query->orderByDesc('created_at')->limit($limit)->get();

        return [
            'success' => true,
            'count' => $quotes->count(),
            'cotizaciones' => $quotes->map(fn($q) => [
                'id' => $q->id,
                'numero' => $q->number,
                'cliente' => $q->client?->name,
                'asunto' => $q->subject,
                'total' => $q->total,
                'estado' => $q->status,
                'fecha' => $q->created_at->format('d/m/Y'),
            ])->toArray(),
        ];
    }

    protected function getStartDate(string $period): Carbon
    {
        return match ($period) {
            'today' => Carbon::today(),
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            'quarter' => Carbon::now()->startOfQuarter(),
            'year' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth(),
        };
    }
}
