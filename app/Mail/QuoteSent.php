<?php

namespace App\Mail;

use App\Models\Quote;
use App\Services\PDF\QuotePdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteSent extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Quote $quote
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Cotización {$this->quote->number} - {$this->quote->tenant->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.quote-sent',
            with: [
                'quote' => $this->quote,
                'client' => $this->quote->client,
                'tenant' => $this->quote->tenant,
            ],
        );
    }

    public function attachments(): array
    {
        $pdfService = app(QuotePdfService::class);
        $pdfContent = $pdfService->generate($this->quote);

        return [
            Attachment::fromData(fn() => $pdfContent, "cotizacion-{$this->quote->number}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
