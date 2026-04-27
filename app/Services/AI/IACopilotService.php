<?php

namespace App\Services\AI;

use App\Models\Omnichannel\Conversation;
use App\Models\KnowledgeIndex;
use App\Services\AI\AIManager;
use Illuminate\Support\Facades\DB;

class IACopilotService
{
    protected AIManager $aiManager;

    public function __construct(AIManager $aiManager)
    {
        $this->aiManager = $aiManager;
    }

    /**
     * Sugiere una respuesta basada en el contexto de la conversación y el conocimiento indexado.
     */
    public function suggestResponse(Conversation $conversation): string
    {
        $tenantId = $conversation->tenant_id;
        
        // 1. Obtener los últimos mensajes para contexto
        $history = $conversation->messages()->orderBy('created_at', 'desc')->limit(5)->get();
        $lastMessage = $history->first()?->content ?? '';

        // 2. Buscar conocimiento relevante (Búsqueda simple por texto/keywords)
        // En una fase pro, aquí usaríamos vectores. Por ahora, búsqueda de texto optimizada.
        $relevantKnowledge = KnowledgeIndex::where('tenant_id', $tenantId)
            ->where(function($q) use ($lastMessage) {
                $words = explode(' ', $lastMessage);
                foreach ($words as $word) {
                    if (strlen($word) > 3) {
                        $q->orWhere('content', 'like', "%{$word}%");
                    }
                }
            })
            ->limit(3)
            ->get();

        $contextString = $relevantKnowledge->map(fn($k) => "[Fuente: {$k->source_type}] {$k->content}")->implode("\n\n");

        // 3. Construir Prompt para el Copiloto
        $prompt = "Eres un asistente copilot para un agente de soporte. 
        Tu objetivo es sugerir una respuesta cordial y profesional basada en el CONOCIMIENTO suministrado.
        
        CONOCIMIENTO DE LA EMPRESA:
        {$contextString}
        
        ULTIMOS MENSAJES:
        " . $history->reverse()->map(fn($m) => "{$m->sender_name}: {$m->content}")->implode("\n") . "
        
        Sugiéreme la mejor respuesta para el cliente. Sé breve y directo. Si el conocimiento no es suficiente, admítelo discretamente.";

        // 4. Llamar a la IA
        return $this->aiManager->complete($prompt);
    }

    /**
     * Detecta el sentimiento de un texto y devuelve un puntaje entre -1 y 1.
     */
    public function detectSentiment(string $text): float
    {
        $prompt = "Analiza el sentimiento del siguiente texto y responde ÚNICAMENTE con un número entre -1 y 1, donde -1 es muy negativo, 0 es neutral y 1 es muy positivo.
        
        Texto: \"{$text}\"";

        try {
            $response = $this->aiManager->complete($prompt);
            // Extraer solo el número (por si la IA devuelve texto adicional)
            preg_match('/-?\d+(\.\d+)?/', $response, $matches);
            $score = $matches[0] ?? 0;
            return (float) $score;
        } catch (\Exception $e) {
            return 0.0;
        }
    }
}
