<?php

namespace App\Services\AI\Contracts;

interface AIProviderInterface
{
    /**
     * Send a message and get a response.
     */
    public function chat(array $messages, array $tools = [], ?string $systemPrompt = null): AIResponse;

    /**
     * Send a message and stream the response.
     */
    public function streamChat(array $messages, array $tools = [], ?string $systemPrompt = null): \Generator;

    /**
     * Get the provider name.
     */
    public function getName(): string;

    /**
     * Get the model being used.
     */
    public function getModel(): string;
}
