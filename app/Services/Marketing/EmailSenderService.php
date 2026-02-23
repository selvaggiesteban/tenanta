<?php

namespace App\Services\Marketing;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailSenderService
{
    protected string $provider;

    public function __construct()
    {
        $this->provider = config('mail.default', 'smtp');
    }

    public function send(array $data): array
    {
        $result = match ($this->provider) {
            'ses' => $this->sendViaSes($data),
            'sendgrid' => $this->sendViaSendgrid($data),
            'mailgun' => $this->sendViaMailgun($data),
            default => $this->sendViaSmtp($data),
        };

        Log::info('Email sent', [
            'provider' => $this->provider,
            'to' => $data['to'],
            'subject' => $data['subject'],
            'message_id' => $result['message_id'],
        ]);

        return $result;
    }

    protected function sendViaSmtp(array $data): array
    {
        $messageId = $this->generateMessageId();

        Mail::send([], [], function ($message) use ($data, $messageId) {
            $message->to($data['to'], $data['to_name'] ?? null)
                ->from($data['from'], $data['from_name'] ?? null)
                ->subject($data['subject'])
                ->html($data['html']);

            if (!empty($data['text'])) {
                $message->text($data['text']);
            }

            if (!empty($data['reply_to'])) {
                $message->replyTo($data['reply_to']);
            }

            $message->getHeaders()->addTextHeader('X-Message-ID', $messageId);

            foreach ($data['headers'] ?? [] as $key => $value) {
                $message->getHeaders()->addTextHeader($key, (string)$value);
            }
        });

        return [
            'message_id' => $messageId,
            'provider' => 'smtp',
        ];
    }

    protected function sendViaSes(array $data): array
    {
        // AWS SES implementation
        $ses = new \Aws\Ses\SesClient([
            'version' => 'latest',
            'region' => config('services.ses.region', 'us-east-1'),
            'credentials' => [
                'key' => config('services.ses.key'),
                'secret' => config('services.ses.secret'),
            ],
        ]);

        $params = [
            'Source' => $data['from_name']
                ? "\"{$data['from_name']}\" <{$data['from']}>"
                : $data['from'],
            'Destination' => [
                'ToAddresses' => [$data['to']],
            ],
            'Message' => [
                'Subject' => [
                    'Data' => $data['subject'],
                    'Charset' => 'UTF-8',
                ],
                'Body' => [
                    'Html' => [
                        'Data' => $data['html'],
                        'Charset' => 'UTF-8',
                    ],
                ],
            ],
        ];

        if (!empty($data['text'])) {
            $params['Message']['Body']['Text'] = [
                'Data' => $data['text'],
                'Charset' => 'UTF-8',
            ];
        }

        if (!empty($data['reply_to'])) {
            $params['ReplyToAddresses'] = [$data['reply_to']];
        }

        $result = $ses->sendEmail($params);

        return [
            'message_id' => $result->get('MessageId'),
            'provider' => 'ses',
        ];
    }

    protected function sendViaSendgrid(array $data): array
    {
        $email = new \SendGrid\Mail\Mail();
        $email->setFrom($data['from'], $data['from_name'] ?? null);
        $email->setSubject($data['subject']);
        $email->addTo($data['to'], $data['to_name'] ?? null);
        $email->addContent('text/html', $data['html']);

        if (!empty($data['text'])) {
            $email->addContent('text/plain', $data['text']);
        }

        if (!empty($data['reply_to'])) {
            $email->setReplyTo($data['reply_to']);
        }

        foreach ($data['headers'] ?? [] as $key => $value) {
            $email->addHeader($key, (string)$value);
        }

        $sendgrid = new \SendGrid(config('services.sendgrid.api_key'));
        $response = $sendgrid->send($email);

        if ($response->statusCode() >= 400) {
            throw new \Exception('SendGrid error: ' . $response->body());
        }

        $headers = $response->headers();
        $messageId = $headers['X-Message-Id'] ?? $this->generateMessageId();

        return [
            'message_id' => is_array($messageId) ? $messageId[0] : $messageId,
            'provider' => 'sendgrid',
        ];
    }

    protected function sendViaMailgun(array $data): array
    {
        $mgClient = \Mailgun\Mailgun::create(config('services.mailgun.secret'));

        $params = [
            'from' => $data['from_name']
                ? "{$data['from_name']} <{$data['from']}>"
                : $data['from'],
            'to' => $data['to'],
            'subject' => $data['subject'],
            'html' => $data['html'],
        ];

        if (!empty($data['text'])) {
            $params['text'] = $data['text'];
        }

        if (!empty($data['reply_to'])) {
            $params['h:Reply-To'] = $data['reply_to'];
        }

        foreach ($data['headers'] ?? [] as $key => $value) {
            $params["h:$key"] = $value;
        }

        $result = $mgClient->messages()->send(
            config('services.mailgun.domain'),
            $params
        );

        return [
            'message_id' => $result->getId(),
            'provider' => 'mailgun',
        ];
    }

    protected function generateMessageId(): string
    {
        return sprintf(
            '<%s.%s@%s>',
            bin2hex(random_bytes(8)),
            time(),
            parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost'
        );
    }

    public function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function setProvider(string $provider): self
    {
        $this->provider = $provider;
        return $this;
    }
}
