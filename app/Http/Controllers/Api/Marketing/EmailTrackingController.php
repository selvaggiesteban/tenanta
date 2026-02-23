<?php

namespace App\Http\Controllers\Api\Marketing;

use App\Actions\Marketing\HandleUnsubscribeAction;
use App\Actions\Marketing\ProcessEmailWebhookAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Marketing\UnsubscribeRequest;
use App\Services\Marketing\EmailTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmailTrackingController extends Controller
{
    public function __construct(
        protected EmailTrackingService $trackingService
    ) {}

    /**
     * Track email open (via tracking pixel).
     */
    public function trackOpen(Request $request, int $recipient, string $hash): Response
    {
        $this->trackingService->trackOpen($recipient, $hash, $request);

        // Return 1x1 transparent GIF
        $pixel = $this->trackingService->generateTrackingPixel();

        return response($pixel, 200, [
            'Content-Type' => 'image/gif',
            'Content-Length' => strlen($pixel),
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Track link click and redirect.
     */
    public function trackClick(Request $request, int $recipient, string $hash, string $url): Response
    {
        $targetUrl = $this->trackingService->trackClick($recipient, $hash, $url, $request);

        if (!$targetUrl) {
            return response('Invalid tracking link', 400);
        }

        return response('', 302, [
            'Location' => $targetUrl,
        ]);
    }

    /**
     * Show unsubscribe form data.
     */
    public function unsubscribeForm(int $recipient, string $hash): JsonResponse
    {
        $action = app(HandleUnsubscribeAction::class);

        return response()->json([
            'reasons' => $action->getUnsubscribeReasons(),
        ]);
    }

    /**
     * Process unsubscribe request.
     */
    public function unsubscribe(
        UnsubscribeRequest $request,
        int $recipient,
        string $hash,
        HandleUnsubscribeAction $action
    ): JsonResponse {
        $success = $action->execute(
            $recipient,
            $hash,
            $request->reason,
            $request->feedback,
            [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        if (!$success) {
            return response()->json([
                'message' => 'No se pudo procesar la solicitud',
            ], 400);
        }

        return response()->json([
            'message' => 'Te has dado de baja exitosamente',
        ]);
    }

    /**
     * Handle email provider webhooks (SES, SendGrid, Mailgun).
     */
    public function webhook(
        Request $request,
        string $provider,
        ProcessEmailWebhookAction $action
    ): JsonResponse {
        // Verify webhook signature based on provider
        if (!$this->verifyWebhookSignature($request, $provider)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $payload = $request->all();

        // Handle SNS confirmation for AWS SES
        if ($provider === 'ses' && isset($payload['Type']) && $payload['Type'] === 'SubscriptionConfirmation') {
            // Confirm SNS subscription
            file_get_contents($payload['SubscribeURL']);
            return response()->json(['message' => 'Subscription confirmed']);
        }

        // For SES, the actual notification is in the 'Message' field
        if ($provider === 'ses' && isset($payload['Message'])) {
            $payload = json_decode($payload['Message'], true);
        }

        $action->execute($provider, $payload);

        return response()->json(['message' => 'Webhook processed']);
    }

    protected function verifyWebhookSignature(Request $request, string $provider): bool
    {
        switch ($provider) {
            case 'ses':
                // AWS SNS signature verification would go here
                // For now, just check if it comes from AWS
                return str_contains($request->userAgent() ?? '', 'Amazon');

            case 'sendgrid':
                $signature = $request->header('X-Twilio-Email-Event-Webhook-Signature');
                $timestamp = $request->header('X-Twilio-Email-Event-Webhook-Timestamp');

                if (!$signature || !$timestamp) {
                    return false;
                }

                $payload = $timestamp . $request->getContent();
                $expectedSignature = base64_encode(
                    hash_hmac('sha256', $payload, config('services.sendgrid.webhook_key'), true)
                );

                return hash_equals($expectedSignature, $signature);

            case 'mailgun':
                $signature = $request->input('signature');

                if (!$signature) {
                    return false;
                }

                $timestamp = $signature['timestamp'] ?? '';
                $token = $signature['token'] ?? '';
                $providedSignature = $signature['signature'] ?? '';

                $expectedSignature = hash_hmac(
                    'sha256',
                    $timestamp . $token,
                    config('services.mailgun.webhook_key')
                );

                return hash_equals($expectedSignature, $providedSignature);

            default:
                return false;
        }
    }
}
