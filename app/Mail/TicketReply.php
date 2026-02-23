<?php

namespace App\Mail;

use App\Models\Ticket;
use App\Models\TicketReply as Reply;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketReply extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public Reply $reply
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Re: Ticket #{$this->ticket->number} - {$this->ticket->subject}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-reply',
            with: [
                'ticket' => $this->ticket,
                'reply' => $this->reply,
            ],
        );
    }
}
