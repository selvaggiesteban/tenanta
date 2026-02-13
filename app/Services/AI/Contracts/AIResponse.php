<?php

namespace App\Services\AI\Contracts;

class AIResponse
{
    public function __construct(
        public readonly string $content,
        public readonly ?array $toolCalls = null,
        public readonly string $stopReason = 'end_turn',
        public readonly int $inputTokens = 0,
        public readonly int $outputTokens = 0,
        public readonly ?string $model = null,
    ) {}

    public function hasToolCalls(): bool
    {
        return !empty($this->toolCalls);
    }

    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'tool_calls' => $this->toolCalls,
            'stop_reason' => $this->stopReason,
            'usage' => [
                'input_tokens' => $this->inputTokens,
                'output_tokens' => $this->outputTokens,
            ],
            'model' => $this->model,
        ];
    }
}
