<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AIProviderInterface;
use App\Services\AI\Contracts\AIResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIProvider implements AIProviderInterface
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl;
    protected int $maxTokens;

    public function __construct(array $config)
    {
        $this->apiKey = $config['api_key'] ?? '';
        $this->model = $config['model'] ?? 'gpt-4o';
        $this->baseUrl = $config['base_url'] ?? 'https://api.openai.com/v1';
        $this->maxTokens = $config['max_tokens'] ?? 4096;
    }

    public function chat(array $messages, array $tools = [], ?string $systemPrompt = null): AIResponse
    {
        $payload = $this->buildPayload($messages, $tools, $systemPrompt);

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'Content-Type' => 'application/json',
        ])->timeout(120)->post("{$this->baseUrl}/chat/completions", $payload);

        if (!$response->successful()) {
            Log::error('OpenAI API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Error al comunicarse con OpenAI: ' . $response->body());
        }

        return $this->parseResponse($response->json());
    }

    public function streamChat(array $messages, array $tools = [], ?string $systemPrompt = null): \Generator
    {
        $payload = $this->buildPayload($messages, $tools, $systemPrompt);
        $payload['stream'] = true;

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'Content-Type' => 'application/json',
        ])->timeout(120)->withOptions([
            'stream' => true,
        ])->post("{$this->baseUrl}/chat/completions", $payload);

        if (!$response->successful()) {
            throw new \Exception('Error al comunicarse con OpenAI');
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
                    $jsonStr = substr($line, 6);
                    if ($jsonStr === '[DONE]') {
                        yield [
                            'type' => 'done',
                            'content' => $currentContent,
                            'tool_calls' => $toolCalls ?: null,
                        ];
                        continue;
                    }

                    $data = json_decode($jsonStr, true);

                    if ($data && isset($data['choices'][0]['delta'])) {
                        $delta = $data['choices'][0]['delta'];

                        if (isset($delta['content'])) {
                            $currentContent .= $delta['content'];
                            yield [
                                'type' => 'text',
                                'content' => $delta['content'],
                            ];
                        }

                        if (isset($delta['tool_calls'])) {
                            foreach ($delta['tool_calls'] as $tc) {
                                $index = $tc['index'] ?? 0;
                                if (!isset($toolCalls[$index])) {
                                    $toolCalls[$index] = [
                                        'id' => $tc['id'] ?? '',
                                        'name' => $tc['function']['name'] ?? '',
                                        'input' => '',
                                    ];
                                }
                                if (isset($tc['function']['arguments'])) {
                                    $toolCalls[$index]['input'] .= $tc['function']['arguments'];
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function getName(): string
    {
        return 'openai';
    }

    public function getModel(): string
    {
        return $this->model;
    }

    protected function buildPayload(array $messages, array $tools, ?string $systemPrompt): array
    {
        $formattedMessages = [];

        if ($systemPrompt) {
            $formattedMessages[] = [
                'role' => 'system',
                'content' => $systemPrompt,
            ];
        }

        $formattedMessages = array_merge($formattedMessages, $this->formatMessages($messages));

        $payload = [
            'model' => $this->model,
            'max_tokens' => $this->maxTokens,
            'messages' => $formattedMessages,
        ];

        if (!empty($tools)) {
            $payload['tools'] = $this->formatTools($tools);
        }

        return $payload;
    }

    protected function formatMessages(array $messages): array
    {
        return array_map(function ($message) {
            $formatted = [
                'role' => $message['role'],
                'content' => $message['content'],
            ];

            if (isset($message['tool_calls'])) {
                $formatted['tool_calls'] = array_map(function ($tc) {
                    return [
                        'id' => $tc['id'],
                        'type' => 'function',
                        'function' => [
                            'name' => $tc['name'],
                            'arguments' => is_string($tc['input']) ? $tc['input'] : json_encode($tc['input']),
                        ],
                    ];
                }, $message['tool_calls']);
            }

            if (isset($message['tool_call_id'])) {
                $formatted['role'] = 'tool';
                $formatted['tool_call_id'] = $message['tool_call_id'];
                $formatted['content'] = is_string($message['content']) ? $message['content'] : json_encode($message['content']);
            }

            return $formatted;
        }, $messages);
    }

    protected function formatTools(array $tools): array
    {
        return array_map(function ($tool) {
            return [
                'type' => 'function',
                'function' => [
                    'name' => $tool['name'],
                    'description' => $tool['description'],
                    'parameters' => $tool['parameters'] ?? [
                        'type' => 'object',
                        'properties' => new \stdClass(),
                    ],
                ],
            ];
        }, $tools);
    }

    protected function parseResponse(array $data): AIResponse
    {
        $choice = $data['choices'][0] ?? [];
        $message = $choice['message'] ?? [];
        $content = $message['content'] ?? '';
        $toolCalls = [];

        if (!empty($message['tool_calls'])) {
            foreach ($message['tool_calls'] as $tc) {
                $toolCalls[] = [
                    'id' => $tc['id'],
                    'name' => $tc['function']['name'],
                    'input' => json_decode($tc['function']['arguments'], true) ?? [],
                ];
            }
        }

        return new AIResponse(
            content: $content,
            toolCalls: $toolCalls ?: null,
            stopReason: $choice['finish_reason'] ?? 'stop',
            inputTokens: $data['usage']['prompt_tokens'] ?? 0,
            outputTokens: $data['usage']['completion_tokens'] ?? 0,
            model: $data['model'] ?? $this->model,
        );
    }
}
