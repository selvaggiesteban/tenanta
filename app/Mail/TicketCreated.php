<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketCreated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ticket $ticket
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Ticket #{$this->ticket->number} - {$this->ticket->subject}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-created',
            with: [
                'ticket' => $this->ticket,
            ],
        );
    }
}
