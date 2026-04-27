<?php

namespace App\Contracts;

interface AiProviderContract
{
    /**
     * Generate a completion based on a prompt.
     *
     * @param string $prompt
     * @param array $options
     * @return string
     */
    public function generateCompletion(string $prompt, array $options = []): string;

    /**
     * Set the API key for the provider.
     *
     * @param string $apiKey
     * @return self
     */
    public function setApiKey(string $apiKey): self;

    /**
     * Get the provider name.
     *
     * @return string
     */
    public function getName(): string;
}
