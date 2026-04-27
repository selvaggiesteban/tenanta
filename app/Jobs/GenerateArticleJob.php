<?php

namespace App\Jobs;

use App\Contracts\AiProviderContract;
use App\Models\Tenant;
use App\Models\CmsArticle;
use App\Services\AI\GeminiAdapter;
use App\Services\AI\OpenAiAdapter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class GenerateArticleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Tenant $tenant,
        protected array $cluster,
        protected string $providerName
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $provider = $this->resolveProvider();

            $prompt = $this->buildPrompt();

            $response = $provider->generateCompletion($prompt);

            // Clean response in case AI includes markdown code blocks
            $cleanResponse = preg_replace('/^```json\s*|\s*```$/', '', trim($response));
            
            $content = json_decode($cleanResponse, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("Failed to decode AI response for article generation", [
                    'tenant_id' => $this->tenant->id,
                    'error' => json_last_error_msg(),
                    'response' => $response
                ]);
                throw new \Exception("Invalid JSON response from AI");
            }

            CmsArticle::create([
                'tenant_id' => $this->tenant->id,
                'title' => $this->cluster['title'] ?? ($this->cluster['keywords'][0] ?? 'Generated Article'),
                'slug' => Str::slug($this->cluster['title'] ?? ($this->cluster['keywords'][0] ?? 'generated-article-' . time())),
                'content' => $content,
                'status' => 'draft',
                'metadata' => [
                    'keywords' => $this->cluster['keywords'] ?? [],
                    'provider' => $this->providerName,
                    'generated_at' => now()->toDateTimeString(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error("Error in GenerateArticleJob", [
                'tenant_id' => $this->tenant->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function buildPrompt(): string
    {
        $keywords = implode(', ', $this->cluster['keywords'] ?? []);
        $title = $this->cluster['title'] ?? 'a relevant topic';

        return "Write a professional blog post about '{$title}' using the following keywords: {$keywords}.
        The content must be structured for Editor.js version 2.11.10.
        Return ONLY a valid JSON object with 'time', 'blocks', and 'version' fields.
        Each block should have 'type' (e.g., 'header', 'paragraph', 'list') and 'data'.
        Include a main title as a header level 1, several paragraphs, and a list if appropriate.
        Do not include any text outside of the JSON structure.";
    }

    protected function resolveProvider(): AiProviderContract
    {
        $providerName = strtolower($this->providerName);

        if (in_array($providerName, ['google', 'gemini'])) {
            $adapter = new GeminiAdapter(['api_key' => $this->tenant->gemini_key]);
            return $adapter->setApiKey($this->tenant->gemini_key ?? '');
        }

        if ($providerName === 'openai') {
            $adapter = new OpenAiAdapter(['api_key' => $this->tenant->openai_key]);
            return $adapter->setApiKey($this->tenant->openai_key ?? '');
        }

        throw new \Exception("Unsupported AI provider: {$this->providerName}");
    }
}
