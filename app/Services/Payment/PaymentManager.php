<?php

namespace App\Services\Payment;

use App\Services\Contracts\PaymentServiceInterface;
use InvalidArgumentException;

class PaymentManager
{
    public function __construct(
        private readonly MercadoPagoService $mercadoPagoService
    ) {}

    public function driver(string $driver = 'mercadopago'): PaymentServiceInterface
    {
        return match ($driver) {
            'mercadopago' => $this->mercadoPagoService,
            default => throw new InvalidArgumentException("Payment driver [{$driver}] not supported."),
        };
    }
}
