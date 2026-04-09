<?php

namespace App\Services\Payment;

use App\Services\Contracts\PaymentServiceInterface;
use App\Models\Courses\Subscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MercadoPagoService implements PaymentServiceInterface
{
    private string $accessToken;

    public function __construct()
    {
        $this->accessToken = config('services.mercadopago.access_token', '');
    }

    public function createPreference(array $data): string
    {
        $response = Http::withToken($this->accessToken)
            ->post('https://api.mercadopago.com/checkout/preferences', [
                'items' => [
                    [
                        'title' => $data['title'] ?? 'Subscription',
                        'quantity' => 1,
                        'unit_price' => (float) ($data['price'] ?? 0),
                        'currency_id' => 'ARS',
                    ]
                ],
                'back_urls' => [
                    'success' => $data['success_url'] ?? url('/payment/success'),
                    'failure' => $data['failure_url'] ?? url('/payment/failure'),
                    'pending' => $data['pending_url'] ?? url('/payment/pending'),
                ],
                'auto_return' => 'approved',
                'external_reference' => $data['reference'] ?? null,
                'notification_url' => route('payments.webhook', ['gateway' => 'mercadopago']),
            ]);

        if ($response->failed()) {
            Log::error('MercadoPago create preference failed', [
                'response' => $response->json(),
            ]);
            throw new \Exception('Failed to create MercadoPago preference.');
        }

        return $response->json('init_point');
    }

    public function handleWebhook(array $payload): void
    {
        Log::info('MercadoPago Webhook received', $payload);
        
        $type = $payload['type'] ?? $payload['topic'] ?? null;
        if ($type === 'payment') {
            $paymentId = $payload['data']['id'] ?? null;
            if ($paymentId) {
                // Verify the payment with MP API
                $response = Http::withToken($this->accessToken)
                    ->get("https://api.mercadopago.com/v1/payments/{$paymentId}");
                
                if ($response->successful()) {
                    $paymentData = $response->json();
                    $status = $paymentData['status'] ?? 'pending';
                    $reference = $paymentData['external_reference'] ?? null;

                    Log::info('Payment verified', [
                        'id' => $paymentId, 
                        'status' => $status, 
                        'reference' => $reference
                    ]);

                    if ($reference) {
                        $subscription = Subscription::withoutTenantScope()->where('external_reference', $reference)->first();
                        if ($subscription) {
                            if ($status === 'approved') {
                                $subscription->update([
                                    'status' => Subscription::STATUS_ACTIVE,
                                    'payment_id' => (string) $paymentId,
                                    'payment_data' => $paymentData,
                                    'last_payment_at' => now(),
                                ]);
                                Log::info("Subscription {$subscription->id} activated via MercadoPago");
                            } elseif ($status === 'rejected' || $status === 'cancelled') {
                                $subscription->update([
                                    'status' => Subscription::STATUS_CANCELLED,
                                    'payment_id' => (string) $paymentId,
                                    'payment_data' => $paymentData,
                                ]);
                                Log::info("Subscription {$subscription->id} cancelled via MercadoPago (status: {$status})");
                            }
                        } else {
                            Log::warning("Subscription with reference {$reference} not found.");
                        }
                    }
                } else {
                    Log::error('Failed to verify payment with MercadoPago API', [
                        'payment_id' => $paymentId,
                        'response' => $response->json()
                    ]);
                }
            }
        }
    }
}
