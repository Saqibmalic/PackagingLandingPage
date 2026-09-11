<?php

namespace Tests\Feature;

use App\Livewire\QuoteForm;
use App\Mail\LeadReceived;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QuoteFormTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_landing_page_renders_the_quote_form(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Custom Rigid Boxes That Feel Like the Brand')
            ->assertSee('Get your free 3D mockup')
            ->assertSee('Get My Free Quote &amp; 3D Mockup', false);
    }

    #[Test]
    public function a_valid_submission_creates_a_lead_and_emails_it(): void
    {
        Mail::fake();

        Livewire::test(QuoteForm::class)
            ->set('name', 'Priya Raghunathan')
            ->set('email', '  Priya@LumenSkincare.com ')
            ->set('phone', '(212) 555-0184')
            ->set('quantity', '1,000 – 5,000')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect(route('thank-you'));

        $lead = Lead::sole();

        $this->assertSame('Priya Raghunathan', $lead->name);
        // Normalised on the way in, because Google hashes the lowercase,
        // trimmed form for enhanced conversions.
        $this->assertSame('priya@lumenskincare.com', $lead->email);
        $this->assertSame('new', $lead->status);
        $this->assertNull($lead->specs_added_at);
        $this->assertSame(8, strlen($lead->reference));

        Mail::assertSent(LeadReceived::class, fn ($mail) => $mail->lead->is($lead)
            && $mail->hasTo(config('leads.notify')));
    }

    #[Test]
    public function the_ad_click_is_captured_from_the_landing_url_and_stored_on_the_lead(): void
    {
        Mail::fake();

        // The visitor arrives on a paid click…
        $this->get('/?gclid=EAIaIQobTEST123&utm_source=google&utm_medium=cpc'
            .'&utm_campaign=Rigid+Boxes+-+US&utm_term=custom+rigid+boxes&utm_content=rsa-3')
            ->assertOk();

        // …and submits the form some time later, from a URL with no parameters.
        Livewire::test(QuoteForm::class)
            ->set('name', 'Marcus Webb')
            ->set('email', 'marcus@harborandpine.com')
            ->set('phone', '4155550148')
            ->set('quantity', '500 – 1,000')
            ->call('submit')
            ->assertHasNoErrors();

        $lead = Lead::sole();

        $this->assertSame('EAIaIQobTEST123', $lead->gclid);
        $this->assertSame('google', $lead->utm_source);
        $this->assertSame('cpc', $lead->utm_medium);
        $this->assertSame('Rigid Boxes - US', $lead->utm_campaign);
        $this->assertSame('custom rigid boxes', $lead->utm_term);
        $this->assertSame('rsa-3', $lead->utm_content);
    }

    #[Test]
    public function every_required_field_is_validated(): void
    {
        Livewire::test(QuoteForm::class)
            ->call('submit')
            ->assertHasErrors(['name' => 'required', 'email' => 'required', 'phone' => 'required', 'quantity' => 'required']);

        $this->assertSame(0, Lead::count());
    }

    #[Test]
    public function a_phone_number_with_too_few_digits_is_rejected(): void
    {
        Livewire::test(QuoteForm::class)
            ->set('name', 'Test')
            ->set('email', 'test@example.com')
            ->set('phone', '555-0148')
            ->set('quantity', 'Not sure yet')
            ->call('submit')
            ->assertHasErrors(['phone']);

        $this->assertSame(0, Lead::count());
    }

    #[Test]
    public function a_quantity_outside_the_offered_list_is_rejected(): void
    {
        Livewire::test(QuoteForm::class)
            ->set('name', 'Test')
            ->set('email', 'test@example.com')
            ->set('phone', '(212) 555-0184')
            ->set('quantity', 'one billion boxes')
            ->call('submit')
            ->assertHasErrors(['quantity']);
    }

    #[Test]
    public function the_honeypot_swallows_a_bot_without_saving_anything(): void
    {
        Mail::fake();

        Livewire::test(QuoteForm::class)
            ->set('name', 'Bot')
            ->set('email', 'bot@spam.test')
            ->set('phone', '(212) 555-0184')
            ->set('quantity', 'Not sure yet')
            ->set('website', 'http://spam.example')
            ->call('submit')
            ->assertRedirect(route('thank-you'));

        $this->assertSame(0, Lead::count());
        Mail::assertNothingSent();
    }

    #[Test]
    public function a_failing_mail_server_never_costs_you_the_lead(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP is down'));

        Livewire::test(QuoteForm::class)
            ->set('name', 'Dana Olsen')
            ->set('email', 'dana@example.com')
            ->set('phone', '(212) 555-0184')
            ->set('quantity', 'Not sure yet')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect(route('thank-you'));

        $this->assertSame(1, Lead::count());
    }
}
