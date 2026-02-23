<?php

namespace App\Services\Marketing;

use App\Models\Marketing\EmailEvent;
use App\Models\Marketing\EmailRecipient;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class EmailTrackingService
{
    public function trackOpen(int $recipientId, string $hash, Request $request): bool
    {
        $recipient = EmailRecipient::find($recipientId);

        if (!$recipient || !$this->verifyHash($recipient, $hash)) {
            return false;
        }

        $data = $this->extractRequestData($request);

        // Record the open event
        EmailEvent::recordOpen($recipient, $data);

        // Update recipient stats
        $recipient->recordOpen();

        return true;
    }

    public function trackClick(int $recipientId, string $hash, string $encodedUrl, Request $request): ?string
    {
        $recipient = EmailRecipient::find($recipientId);

        if (!$recipient || !$this->verifyHash($recipient, $hash)) {
            return null;
        }

        $url = base64_decode($encodedUrl);

        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $data = $this->extractRequestData($request);

        // Record the click event
        EmailEvent::recordClick($recipient, $url, $data);

        // Update recipient stats
        $recipient->recordClick();

        return $url;
    }

    protected function extractRequestData(Request $request): array
    {
        $agent = new Agent();
        $agent->setUserAgent($request->userAgent());

        return [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_type' => $this->getDeviceType($agent),
            'client_name' => $agent->browser() ?: null,
            'client_os' => $agent->platform() ?: null,
            'country' => $this->getCountryFromIp($request->ip()),
            'city' => null, // Would require GeoIP lookup
        ];
    }

    protected function getDeviceType(Agent $agent): string
    {
        if ($agent->isTablet()) {
            return 'tablet';
        }

        if ($agent->isMobile()) {
            return 'mobile';
        }

        return 'desktop';
    }

    protected function getCountryFromIp(string $ip): ?string
    {
        // Skip for local/private IPs
        if ($this->isPrivateIp($ip)) {
            return null;
        }

        // Simple free GeoIP lookup (could be replaced with MaxMind or similar)
        try {
            $response = file_get_contents("http://ip-api.com/json/{$ip}?fields=country,countryCode");
            $data = json_decode($response, true);

            return $data['countryCode'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function isPrivateIp(string $ip): bool
    {
        return !filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }

    protected function verifyHash(EmailRecipient $recipient, string $hash): bool
    {
        $expectedHash = hash_hmac('sha256', $recipient->id . $recipient->email, config('app.key'));
        return hash_equals($expectedHash, $hash);
    }

    public function generateTrackingPixel(): string
    {
        // Return a 1x1 transparent GIF
        return base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
    }
}
