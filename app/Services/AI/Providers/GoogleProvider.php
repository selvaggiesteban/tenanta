<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AIProviderInterface;
use App\Services\AI\Contracts\AIResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleProvider implements AIProviderInterface
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl;
    protected int $maxTokens;

    public function __construct(array $config)
    {
        $this->apiKey = $config['api_key'] ?? '';
        $this->model = $config['model'] ?? 'gemini-1.5-pro';
        $this->baseUrl = $config['base_url'] ?? 'https://generativelanguage.googleapis.com/v1beta';
        $this->maxTokens = $config['max_tokens'] ?? 4096;
    }

    public function chat(array $messages, array $tools = [], ?string $systemPrompt = null): AIResponse
    {
        $payload = $this->buildPayload($messages, $tools, $systemPrompt);
        $url = "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}";

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->timeout(120)->post($url, $payload);

        if (!$response->successful()) {
            Log::error('Google AI API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Error al comunicarse con Gemini: ' . $response->body());
        }

        return $this->parseResponse($response->json());
    }

    public function streamChat(array $messages, array $tools = [], ?string $systemPrompt = null): \Generator
    {
        $payload = $this->buildPayload($messages, $tools, $systemPrompt);
        $url = "{$this->baseUrl}/models/{$this->model}:streamGenerateContent?key={$this->apiKey}";

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->timeout(120)->withOptions([
            'stream' => true,
        ])->post($url, $payload);

        if (!$response->successful()) {
            throw new \Exception('Error al comunicarse con Gemini');
        }

        $body = $response->getBody();
        $buffer = '';
        $currentContent = '';
        $toolCalls = [];

        while (!$body->eof()) {
            $buffer .= $body->read(1024);

            // Gemini streams JSON objects, one per line
            while (($pos = strpos($buffer, "\n")) !== false) {
                $line = trim(substr($buffer, 0, $pos));
                $buffer = substr($buffer, $pos + 1);

                if (empty($line) || $line === '[' || $line === ']' || $line === ',') {
                    continue;
                }

                // Remove trailing comma if present
                $line = rtrim($line, ',');

                $data = json_decode($line, true);

                if ($data && isset($data['candidates'][0]['content']['parts'])) {
                    foreach ($data['candidates'][0]['content']['parts'] as $part) {
                        if (isset($part['text'])) {
                            $currentContent .= $part['text'];
                            yield [
                                'type' => 'text',
                                'content' => $part['text'],
                            ];
                        }

                        if (isset($part['functionCall'])) {
                            $toolCalls[] = [
                                'id' => uniqid('tool_'),
                                'name' => $part['functionCall']['name'],
                                'input' => $part['functionCall']['args'] ?? [],
                            ];
                        }
                    }
                }

                if (isset($data['candidates'][0]['finishReason'])) {
                    yield [
                        'type' => 'done',
                        'content' => $currentContent,
                        'tool_calls' => $toolCalls ?: null,
                    ];
                }
            }
        }
    }

    public function getName(): string
    {
        return 'google';
    }

    public function getModel(): string
    {
        return $this->model;
    }

    protected function buildPayload(array $messages, array $tools, ?string $systemPrompt): array
    {
        $payload = [
            'contents' => $this->formatMessages($messages),
            'generationConfig' => [
                'maxOutputTokens' => $this->maxTokens,
            ],
        ];

        if ($systemPrompt) {
            $payload['systemInstruction'] = [
                'parts' => [['text' => $systemPrompt]],
            ];
        }

        if (!empty($tools)) {
            $payload['tools'] = [
                ['functionDeclarations' => $this->formatTools($tools)],
            ];
        }

        return $payload;
    }

    protected function formatMessages(array $messages): array
    {
        $contents = [];

        foreach ($messages as $message) {
            $role = $message['role'] === 'assistant' ? 'model' : 'user';
            $parts = [];

            if (is_string($message['content'])) {
                $parts[] = ['text' => $message['content']];
            }

            if (isset($message['tool_results'])) {
                foreach ($message['tool_results'] as $result) {
                    $parts[] = [
                        'functionResponse' => [
                            'name' => $result['name'] ?? 'tool',
                            'response' => [
                                'result' => $result['content'],
                            ],
                        ],
                    ];
                }
            }

            $contents[] = [
                'role' => $role,
                'parts' => $parts,
            ];
        }

        return $contents;
    }

    protected function formatTools(array $tools): array
    {
        return array_map(function ($tool) {
            return [
                'name' => $tool['name'],
                'description' => $tool['description'],
                'parameters' => $tool['parameters'] ?? [
                    'type' => 'object',
                    'properties' => new \stdClass(),
                ],
            ];
        }, $tools);
    }

    protected function parseResponse(array $data): AIResponse
    {
        $candidate = $data['candidates'][0] ?? [];
        $content = '';
        $toolCalls = [];

        foreach ($candidate['content']['parts'] ?? [] as $part) {
            if (isset($part['text'])) {
                $content .= $part['text'];
            }
            if (isset($part['functionCall'])) {
                $toolCalls[] = [
                    'id' => uniqid('tool_'),
                    'name' => $part['functionCall']['name'],
                    'input' => $part['functionCall']['args'] ?? [],
                ];
            }
        }

        $inputTokens = $data['usageMetadata']['promptTokenCount'] ?? 0;
        $outputTokens = $data['usageMetadata']['candidatesTokenCount'] ?? 0;

        return new AIResponse(
            content: $content,
            toolCalls: $toolCalls ?: null,
            stopReason: $candidate['finishReason'] ?? 'STOP',
            inputTokens: $inputTokens,
            outputTokens: $outputTokens,
            model: $this->model,
        );
    }
}
