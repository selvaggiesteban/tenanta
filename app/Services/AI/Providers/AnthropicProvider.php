<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AIProviderInterface;
use App\Services\AI\Contracts\AIResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnthropicProvider implements AIProviderInterface
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl;
    protected int $maxTokens;

    public function __construct(array $config)
    {
        $this->apiKey = $config['api_key'] ?? '';
        $this->model = $config['model'] ?? 'claude-sonnet-4-20250514';
        $this->baseUrl = $config['base_url'] ?? 'https://api.anthropic.com/v1';
        $this->maxTokens = $config['max_tokens'] ?? 4096;
    }

    public function chat(array $messages, array $tools = [], ?string $systemPrompt = null): AIResponse
    {
        $payload = $this->buildPayload($messages, $tools, $systemPrompt);

        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->timeout(120)->post("{$this->baseUrl}/messages", $payload);

        if (!$response->successful()) {
            Log::error('Anthropic API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Error al comunicarse con Claude: ' . $response->body());
        }

        return $this->parseResponse($response->json());
    }

    public function streamChat(array $messages, array $tools = [], ?string $systemPrompt = null): \Generator
    {
        $payload = $this->buildPayload($messages, $tools, $systemPrompt);
        $payload['stream'] = true;

        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->timeout(120)->withOptions([
            'stream' => true,
        ])->post("{$this->baseUrl}/messages", $payload);

        if (!$response->successful()) {
            throw new \Exception('Error al comunicarse con Claude');
        }

        $body = $response->getBody();
        $buffer = '';
        $currentContent = '';
        $toolCalls = [];

        while (!$body->eof()) {
            $buffer .= $body->read(1024);

            while (($pos = strpos($buffer, "\n")) !== false) {
                $line = substr($buffer, 0, $pos);
                $buffer = substr($buffer, $pos + 1);

                if (str_starts_with($line, 'data: ')) {
                    $data = json_decode(substr($line, 6), true);

                    if ($data && isset($data['type'])) {
                        switch ($data['type']) {
                            case 'content_block_delta':
                                if (isset($data['delta']['text'])) {
                                    $currentContent .= $data['delta']['text'];
                                    yield [
                                        'type' => 'text',
                                        'content' => $data['delta']['text'],
                                    ];
                                }
                                break;

                            case 'content_block_start':
                                if (isset($data['content_block']['type']) && $data['content_block']['type'] === 'tool_use') {
                                    $toolCalls[] = [
                                        'id' => $data['content_block']['id'],
                                        'name' => $data['content_block']['name'],
                                        'input' => '',
                                    ];
                                }
                                break;

                            case 'message_stop':
                                yield [
                                    'type' => 'done',
                                    'content' => $currentContent,
                                    'tool_calls' => $toolCalls ?: null,
                                ];
                                break;
                        }
                    }
                }
            }
        }
    }

    public function getName(): string
    {
        return 'anthropic';
    }

    public function getModel(): string
    {
        return $this->model;
    }

    protected function buildPayload(array $messages, array $tools, ?string $systemPrompt): array
    {
        $payload = [
            'model' => $this->model,
            'max_tokens' => $this->maxTokens,
            'messages' => $this->formatMessages($messages),
        ];

        if ($systemPrompt) {
            $payload['system'] = $systemPrompt;
        }

        if (!empty($tools)) {
            $payload['tools'] = $this->formatTools($tools);
        }

        return $payload;
    }

    protected function formatMessages(array $messages): array
    {
        return array_map(function ($message) {
            $formatted = [
                'role' => $message['role'] === 'assistant' ? 'assistant' : 'user',
                'content' => $message['content'],
            ];

            if (isset($message['tool_results'])) {
                $formatted['content'] = array_map(function ($result) {
                    return [
                        'type' => 'tool_result',
                        'tool_use_id' => $result['tool_use_id'],
                        'content' => json_encode($result['content']),
                    ];
                }, $message['tool_results']);
            }

            return $formatted;
        }, $messages);
    }

    protected function formatTools(array $tools): array
    {
        return array_map(function ($tool) {
            return [
                'name' => $tool['name'],
                'description' => $tool['description'],
                'input_schema' => $tool['parameters'] ?? [
                    'type' => 'object',
                    'properties' => new \stdClass(),
                ],
            ];
        }, $tools);
    }

    protected function parseResponse(array $data): AIResponse
    {
        $content = '';
        $toolCalls = [];

        foreach ($data['content'] ?? [] as $block) {
            if ($block['type'] === 'text') {
                $content .= $block['text'];
            } elseif ($block['type'] === 'tool_use') {
                $toolCalls[] = [
                    'id' => $block['id'],
                    'name' => $block['name'],
                    'input' => $block['input'],
                ];
            }
        }

        return new AIResponse(
            content: $content,
            toolCalls: $toolCalls ?: null,
            stopReason: $data['stop_reason'] ?? 'end_turn',
            inputTokens: $data['usage']['input_tokens'] ?? 0,
            outputTokens: $data['usage']['output_tokens'] ?? 0,
            model: $data['model'] ?? $this->model,
        );
    }
}
