<?php

namespace App\Services\AI;

use App\Services\AI\Contracts\AIProviderInterface;
use App\Services\AI\Providers\AnthropicProvider;
use App\Services\AI\Providers\GoogleProvider;
use App\Services\AI\Providers\OpenAIProvider;
use InvalidArgumentException;

class AIManager
{
    protected array $providers = [];
    protected ?string $defaultProvider = null;

    public function __construct()
    {
        $this->defaultProvider = config('ai.default', 'claude');
    }

    /**
     * Get a provider instance.
     */
    public function provider(?string $name = null): AIProviderInterface
    {
        $name = $name ?? $this->defaultProvider;

        if (!isset($this->providers[$name])) {
            $this->providers[$name] = $this->createProvider($name);
        }

        return $this->providers[$name];
    }

    /**
     * Create a provider instance.
     */
    protected function createProvider(string $name): AIProviderInterface
    {
        $config = config("ai.providers.{$name}");

        if (!$config) {
            throw new InvalidArgumentException("AI provider [{$name}] is not configured.");
        }

        $driver = $config['driver'] ?? $name;

        return match ($driver) {
            'anthropic' => new AnthropicProvider($config),
            'openai' => new OpenAIProvider($config),
            'google' => new GoogleProvider($config),
            default => throw new InvalidArgumentException("Unsupported AI driver [{$driver}]."),
        };
    }

    /**
     * Get the default provider name.
     */
    public function getDefaultProvider(): string
    {
        return $this->defaultProvider;
    }

    /**
     * Set the default provider.
     */
    public function setDefaultProvider(string $name): void
    {
        $this->defaultProvider = $name;
    }

    /**
     * Get all configured provider names.
     */
    public function getAvailableProviders(): array
    {
        return array_keys(config('ai.providers', []));
    }

    /**
     * Check if a provider is configured.
     */
    public function hasProvider(string $name): bool
    {
        return config("ai.providers.{$name}") !== null;
    }
}
