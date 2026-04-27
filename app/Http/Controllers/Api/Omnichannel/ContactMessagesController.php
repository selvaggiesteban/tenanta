<?php

namespace App\Http\Controllers\Api\Omnichannel;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Omnichannel\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactMessagesController extends Controller
{
    /**
     * Obtiene todos los mensajes de todas las conversaciones vinculadas a un contacto.
     */
    public function index(Contact $contact): JsonResponse
    {
        // El aislamiento por tenant ya está manejado por el Trait BelongsToTenant en los modelos
        $messages = Message::whereHas('conversation', function($q) use ($contact) {
                $q->where('contact_id', $contact->id);
            })
            ->with('conversation.channel')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $messages->items(),
            'meta' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'total' => $messages->total(),
            ]
        ]);
    }
}
