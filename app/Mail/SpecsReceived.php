<?php

namespace App\Mail;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Follow-up alert: the buyer went on to fill in the optional spec form, so
 * the quote can be exact. Sent as a second email rather than held back,
 * because the first one is what gets the phone call started.
 */
class SpecsReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Lead $lead) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Box Specs Added — {$this->lead->name} [{$this->lead->reference}]",
            replyTo: [new Address($this->lead->email, $this->lead->name)],
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.specs-received',
        );
    }
}
