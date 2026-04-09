<?php

namespace App\Services\SEO;

use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SEOAnalyzerService
{
    /**
     * Analiza el SEO de una landing page de un inquilino.
     * Implementa los indicadores de la Fase 2.5.1 (Localización LATAM).
     *
     * @param Tenant $tenant
     * @return array Resultados del análisis y puntaje global.
     */
    public function analyze(Tenant $tenant): array
    {
        // En un entorno real, haríamos un request a la URL de la landing
        // Como estamos en desarrollo local, simulamos o analizamos los datos guardados en el modelo
        
        $results = [
            'metadatos' => $this->analyzeMetadata($tenant),
            'estructura_on_page' => $this->analyzeStructure($tenant),
            'optimizacion_imagenes' => $this->analyzeImages($tenant),
            'enlaces' => $this->analyzeLinks($tenant),
            'seo_tecnico' => $this->analyzeTechnical($tenant),
        ];

        // Calcular puntaje global (0-100)
        $totalScore = 0;
        foreach ($results as $category) {
            $totalScore += $category['puntaje'];
        }
        $results['puntaje_global'] = round($totalScore / count($results));
        $results['analizado_en'] = now()->toDateTimeString();

        return $results;
    }

    private function analyzeMetadata(Tenant $tenant): array
    {
        $score = 0;
        $recommendations = [];

        // Título SEO
        if (!empty($tenant->meta_title)) {
            $len = strlen($tenant->meta_title);
            if ($len >= 30 && $len <= 60) {
                $score += 50;
            } else {
                $score += 20;
                $recommendations[] = "El título SEO tiene una longitud no óptima ({$len} caracteres). Se recomienda entre 30 y 60.";
            }
        } else {
            $recommendations[] = "Falta el título SEO (Meta Title).";
        }

        // Meta Descripción
        if (!empty($tenant->meta_description)) {
            $len = strlen($tenant->meta_description);
            if ($len >= 120 && $len <= 160) {
                $score += 50;
            } else {
                $score += 25;
                $recommendations[] = "La meta descripción debe tener entre 120 y 160 caracteres. Actual: {$len}.";
            }
        } else {
            $recommendations[] = "Falta la meta descripción.";
        }

        return [
            'puntaje' => $score,
            'recomendaciones' => $recommendations,
            'estado' => $score >= 80 ? 'Excelente' : ($score >= 50 ? 'Mejorable' : 'Crítico')
        ];
    }

    private function analyzeStructure(Tenant $tenant): array
    {
        $score = 100;
        $recommendations = [];

        if (empty($tenant->hero_title)) {
            $score -= 30;
            $recommendations[] = "El título principal (H1) está vacío o no definido.";
        }

        if (empty($tenant->about_text) || strlen($tenant->about_text) < 300) {
            $score -= 20;
            $recommendations[] = "El contenido textual es insuficiente (menos de 300 palabras). El SEO prefiere textos informativos.";
        }

        return [
            'puntaje' => max(0, $score),
            'recomendaciones' => $recommendations,
        ];
    }

    private function analyzeImages(Tenant $tenant): array
    {
        $score = 100;
        $recommendations = [];

        // Simulación: verificamos si las imágenes tienen descripciones o si el logo está presente
        if (empty($tenant->logo_url)) {
            $score -= 20;
            $recommendations[] = "No se detectó un logotipo optimizado.";
        }

        if (empty($tenant->hero_image)) {
            $score -= 10;
            $recommendations[] = "Falta la imagen destacada del Hero.";
        }

        return [
            'puntaje' => max(0, $score),
            'recomendaciones' => $recommendations,
        ];
    }

    private function analyzeLinks(Tenant $tenant): array
    {
        $score = 100;
        $recommendations = [];

        if (empty($tenant->social_links) || count($tenant->social_links) < 2) {
            $score -= 20;
            $recommendations[] = "Faltan enlaces a redes sociales para mejorar la autoridad del sitio.";
        }

        if (empty($tenant->google_map_url)) {
            $score -= 20;
            $recommendations[] = "Falta el enlace a Google Maps para SEO Local.";
        }

        return [
            'puntaje' => max(0, $score),
            'recomendaciones' => $recommendations,
        ];
    }

    private function analyzeTechnical(Tenant $tenant): array
    {
        $score = 100;
        $recommendations = [];

        if (empty($tenant->slug)) {
            $score -= 50;
            $recommendations[] = "URL no amigable (Slug ausente).";
        }

        return [
            'puntaje' => max(0, $score),
            'recomendaciones' => $recommendations,
        ];
    }
}
