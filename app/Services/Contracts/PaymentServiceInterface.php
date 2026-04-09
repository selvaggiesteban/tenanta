<?php

namespace App\Services\Contracts;

interface PaymentServiceInterface
{
    public function createPreference(array $data): string;
    public function handleWebhook(array $payload): void;
}
