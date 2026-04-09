<?php

namespace App\Services\Marketing;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WordPressService
{
    /**
     * Conecta con la API de WordPress externa.
     * Implementa la Fase 2.6.1 (Localización LATAM).
     */
    public function connect(string $url, string $username, string $applicationPassword): bool
    {
        try {
            $response = Http::withBasicAuth($username, $applicationPassword)
                ->get(rtrim($url, '/') . '/wp-json/wp/v2/users/me');

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Error de conexión con WordPress: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Publica una entrada (Post) en WordPress.
     * Implementa la Fase 2.6.2.
     */
    public function publishPost(array $config, array $postData): array
    {
        $url = rtrim($config['url'], '/') . '/wp-json/wp/v2/posts';
        
        try {
            $response = Http::withBasicAuth($config['username'], $config['password'])
                ->post($url, [
                    'title'   => $postData['titulo'],
                    'content' => $postData['contenido'],
                    'status'  => $postData['estado'] ?? 'publish',
                    'categories' => $postData['categorias'] ?? [],
                    'tags'    => $postData['etiquetas'] ?? [],
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'wp_id'   => $response->json('id'),
                    'link'    => $response->json('link'),
                    'message' => 'Contenido publicado exitosamente en WordPress.'
                ];
            }

            return [
                'success' => false,
                'message' => 'Error de la API de WordPress: ' . $response->body()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Excepción al publicar en WordPress: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generador de Textos Legales (Fase 2.6.4).
     */
    public function generateLegalText(string $type, array $businessData): string
    {
        $name = $businessData['name'];
        $address = $businessData['address'];
        
        return match ($type) {
            'privacidad' => "Política de Privacidad para {$name}. En cumplimiento con el RGPD, informamos que los datos recogidos en {$address} serán tratados con la máxima confidencialidad...",
            'cookies' => "Este sitio utiliza cookies para mejorar la experiencia del usuario en {$name}...",
            default => "Aviso Legal para {$name}..."
        };
    }
}
