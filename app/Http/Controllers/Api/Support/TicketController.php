<?php

namespace App\Http\Controllers\Api\Support;

use App\Http\Controllers\Controller;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Ticket::with(['creator', 'assignee', 'client'])
            ->withCount('replies');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $tickets = $query->orderByDesc('created_at')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'data' => TicketResource::collection($tickets),
            'meta' => [
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'category' => 'nullable|string|max:100',
            'client_id' => 'nullable|exists:clients,id',
        ]);

        $ticket = Ticket::create([
            ...$validated,
            'tenant_id' => Auth::user()->tenant_id,
            'created_by' => Auth::id(),
            'status' => 'open',
            'priority' => $validated['priority'] ?? 'medium',
        ]);

        $ticket->load(['creator', 'client']);

        return response()->json([
            'data' => new TicketResource($ticket),
            'message' => 'Ticket creado exitosamente',
        ], 201);
    }

    public function show(Ticket $ticket): JsonResponse
    {
        $ticket->load(['creator', 'assignee', 'client', 'replies.user']);

        return response()->json([
            'data' => new TicketResource($ticket),
        ]);
    }

    public function update(Request $request, Ticket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'subject' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'category' => 'nullable|string|max:100',
            'status' => 'nullable|in:open,in_progress,waiting,resolved,closed',
        ]);

        $ticket->update($validated);
        $ticket->load(['creator', 'assignee', 'client']);

        return response()->json([
            'data' => new TicketResource($ticket),
            'message' => 'Ticket actualizado exitosamente',
        ]);
    }

    public function destroy(Ticket $ticket): JsonResponse
    {
        $ticket->delete();

        return response()->json(null, 204);
    }

    public function assign(Request $request, Ticket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $ticket->assign($user);
        $ticket->load(['creator', 'assignee', 'client']);

        return response()->json([
            'data' => new TicketResource($ticket),
            'message' => 'Ticket asignado exitosamente',
        ]);
    }

    public function reply(Request $request, Ticket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'is_internal' => 'nullable|boolean',
        ]);

        $reply = $ticket->addReply(
            $validated['content'],
            Auth::user(),
            $validated['is_internal'] ?? false
        );

        $reply->load('user');

        return response()->json([
            'data' => [
                'id' => $reply->id,
                'content' => $reply->content,
                'is_internal' => $reply->is_internal,
                'user' => [
                    'id' => $reply->user->id,
                    'name' => $reply->user->name,
                ],
                'created_at' => $reply->created_at->toISOString(),
            ],
            'message' => 'Respuesta agregada exitosamente',
        ], 201);
    }

    public function resolve(Ticket $ticket): JsonResponse
    {
        $ticket->resolve();
        $ticket->load(['creator', 'assignee', 'client']);

        return response()->json([
            'data' => new TicketResource($ticket),
            'message' => 'Ticket resuelto exitosamente',
        ]);
    }

    public function close(Ticket $ticket): JsonResponse
    {
        $ticket->close();
        $ticket->load(['creator', 'assignee', 'client']);

        return response()->json([
            'data' => new TicketResource($ticket),
            'message' => 'Ticket cerrado exitosamente',
        ]);
    }

    public function reopen(Ticket $ticket): JsonResponse
    {
        $ticket->reopen();
        $ticket->load(['creator', 'assignee', 'client']);

        return response()->json([
            'data' => new TicketResource($ticket),
            'message' => 'Ticket reabierto exitosamente',
        ]);
    }

    public function stats(): JsonResponse
    {
        $stats = [
            'total' => Ticket::count(),
            'open' => Ticket::where('status', 'open')->count(),
            'in_progress' => Ticket::where('status', 'in_progress')->count(),
            'waiting' => Ticket::where('status', 'waiting')->count(),
            'resolved' => Ticket::where('status', 'resolved')->count(),
            'closed' => Ticket::where('status', 'closed')->count(),
            'overdue' => Ticket::whereNotIn('status', ['resolved', 'closed'])
                ->where('due_at', '<', now())
                ->count(),
            'by_priority' => [
                'low' => Ticket::where('priority', 'low')->whereNotIn('status', ['resolved', 'closed'])->count(),
                'medium' => Ticket::where('priority', 'medium')->whereNotIn('status', ['resolved', 'closed'])->count(),
                'high' => Ticket::where('priority', 'high')->whereNotIn('status', ['resolved', 'closed'])->count(),
                'urgent' => Ticket::where('priority', 'urgent')->whereNotIn('status', ['resolved', 'closed'])->count(),
            ],
        ];

        return response()->json(['data' => $stats]);
    }
}
