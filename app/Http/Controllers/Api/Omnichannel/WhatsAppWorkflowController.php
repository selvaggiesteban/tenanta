<?php

namespace App\Http\Controllers\Api\Omnichannel;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;

class WhatsAppWorkflowController extends Controller
{
    /**
     * Carga el flujo de trabajo de WhatsApp desde el JSON (Sección 6).
     */
    public function getWorkflow(): JsonResponse
    {
        $path = base_path('../template con script para crear landings de scrap de google business profile/whatsapp_workflow.json');
        
        if (!File::exists($path)) {
            return response()->json(['success' => false, 'message' => 'Configuración de flujo no encontrada.'], 404);
        }

        $workflow = json_decode(File::get($path), true);

        return response()->json([
            'success' => true,
            'data' => $workflow['funnel_steps']
        ]);
    }

    /**
     * Genera un link de wa.me codificado con reemplazo de etiquetas.
     */
    public function generateLink(string $phone, string $message, array $tags): string
    {
        $processedMessage = $message;
        foreach ($tags as $key => $value) {
            $processedMessage = str_replace("{{$key}}", $value, $processedMessage);
        }

        return "https://wa.me/" . preg_replace('/[^0-9]/', '', $phone) . "?text=" . urlencode($processedMessage);
    }
}
