<?php

namespace App\Services\AI;

use App\Contracts\AiProviderContract;
use App\Services\AI\Providers\GoogleProvider;

class GeminiAdapter extends GoogleProvider implements AiProviderContract
{
    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function generateCompletion(string $prompt, array $options = []): string
    {
        $response = $this->chat([
            ['role' => 'user', 'content' => $prompt]
        ]);

        return $response->content;
    }
}
