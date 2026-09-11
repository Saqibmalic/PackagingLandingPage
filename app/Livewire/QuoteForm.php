<?php

namespace App\Livewire;

use App\Mail\LeadReceived;
use App\Models\Lead;
use App\Support\AdContext;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * Stage one of the quote flow: the contact details.
 *
 * This is the lead. It is saved and emailed the moment this form succeeds, so
 * a buyer who never gets round to the optional spec form on the thank-you page
 * is still a lead you can call.
 */
class QuoteForm extends Component
{
    #[Validate('required|string|max:120')]
    public string $name = '';

    #[Validate('required|email:rfc|max:160')]
    public string $email = '';

    public string $phone = '';

    public string $quantity = '';

    /**
     * Honeypot. Bots fill every field they find; this one is hidden from
     * people, so anything in it means the submission is not human.
     */
    public string $website = '';

    /** Renders inside the quote modal rather than the hero column. */
    public bool $modal = false;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // 10 digits after everything that is not one is stripped — that
            // accepts "(555) 010-2233", "555.010.2233" and "+1 555 010 2233"
            // alike, and rejects a number nobody could call back.
            'phone' => ['required', 'string', 'max:40', 'regex:/^\D*(\d\D*){10,15}$/'],
            'quantity' => ['required', Rule::in($this->quantities())],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please enter your name.',
            'email.required' => 'Please enter a valid work email address.',
            'email.email' => 'Please enter a valid work email address.',
            'phone.required' => 'Please enter a phone number we can reach you on.',
            'phone.regex' => 'Please enter a phone number we can reach you on.',
            'quantity.required' => 'Please choose an approximate quantity.',
            'quantity.in' => 'Please choose an approximate quantity.',
        ];
    }

    /**
     * @return list<string>
     */
    public function quantities(): array
    {
        return ['100 – 250', '250 – 500', '500 – 1,000', '1,000 – 5,000', '5,000 – 10,000', '10,000+', 'Not sure yet'];
    }

    public function submit()
    {
        // Answer a bot exactly as though it had worked, so it does not retry.
        if ($this->website !== '') {
            return $this->redirectRoute('thank-you', navigate: false);
        }

        // Trim before validating, not after: a pasted email with a trailing
        // space is a real thing people do, and it should not read as invalid.
        $this->name = trim($this->name);
        $this->email = mb_strtolower(trim($this->email));
        $this->phone = trim($this->phone);

        $this->validate();

        $lead = Lead::create([
            'reference' => Lead::newReference(),
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'quantity' => $this->quantity,
        ] + AdContext::forLead(request()));

        $this->notify($lead);

        // The thank-you page needs to know which lead to attach specs to, and
        // which email to hand Google for enhanced conversions. Both travel in
        // the session rather than the URL: no PII in a shareable link, and no
        // way to load someone else's lead by guessing a query string.
        session()->put('lead.reference', $lead->reference);
        // Consumed and removed by the thank-you page, so the conversion fires
        // exactly once no matter how many times that page is reloaded.
        session()->put('lead.fire_conversion', true);

        return $this->redirectRoute('thank-you', navigate: false);
    }

    /**
     * An email problem must never cost you the lead — it is already saved by
     * the time this runs, so a failure is logged and swallowed.
     */
    protected function notify(Lead $lead): void
    {
        try {
            Mail::to(config('leads.notify'))
                ->cc(config('leads.notify_cc'))
                ->send(new LeadReceived($lead));
        } catch (\Throwable $e) {
            Log::error('Lead alert email failed', ['reference' => $lead->reference, 'error' => $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('livewire.quote-form');
    }
}
