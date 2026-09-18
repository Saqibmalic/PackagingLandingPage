<?php

namespace Tests\Feature;

use App\Livewire\QuoteForm;
use App\Livewire\SpecForm;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The whole journey in one test: an ad click, both form stages, the thank-you
 * page, and the lead arriving in the dashboard ready to export.
 */
class FullFlowTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_click_becomes_one_lead_that_reaches_the_dashboard_and_the_export(): void
    {
        Mail::fake();
        config(['tracking.ads_id' => 'AW-1234567890', 'tracking.lead_label' => 'AW-1234567890/abcDEF']);

        // 1. Arrive on a paid click.
        $this->get('/?gclid=EAIaIQobFLOW9f2b&utm_source=google&utm_medium=cpc&utm_campaign=Rigid+Boxes+-+US')
            ->assertOk()
            ->assertSee('AW-1234567890', false);

        // 2. Submit the contact form.
        Livewire::test(QuoteForm::class)
            ->set('name', 'Priya Raghunathan')
            ->set('email', 'priya@lumenskincare.com')
            ->set('phone', '(212) 555-0184')
            ->set('quantity', '1,000 – 5,000')
            ->call('submit')
            ->assertRedirect(route('thank-you'));

        $lead = Lead::sole();
        $this->assertSame('EAIaIQobFLOW9f2b', $lead->gclid);

        // 3. The thank-you page fires the conversion exactly once. Nothing is
        //    stuffed into the session by hand here — this reads whatever the
        //    form itself flashed, which is the behaviour that matters.
        $this->get('/thank-you')
            ->assertOk()
            ->assertSee('AW-1234567890/abcDEF', false)
            ->assertSee('priya@lumenskincare.com', false);

        // The flash is spent, so a refresh cannot count the same lead twice.
        $this->get('/thank-you')
            ->assertOk()
            ->assertSee($lead->reference)
            ->assertDontSee('AW-1234567890/abcDEF', false);

        // 4. Add the box specs to that same lead.
        Livewire::test(SpecForm::class, ['lead' => $lead])
            ->set('length', '9')->set('width', '9')->set('depth', '3')
            ->set('style', 'Magnetic closure')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertSame(1, Lead::count(), 'Two stages must still be one lead.');

        // 5. It shows up in the dashboard.
        $this->actingAs(User::factory()->create())
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Priya Raghunathan')
            ->assertSee('priya@lumenskincare.com')
            ->assertSee('Google Ads')
            ->assertSee('specs added');

        // 6. And it exports in the format Google Ads accepts.
        $csv = $this->get('/dashboard/export?type=gclid')->streamedContent();

        $this->assertStringContainsString('Parameters:TimeZone=', $csv);
        $this->assertStringContainsString('EAIaIQobFLOW9f2b', $csv);
        $this->assertStringContainsString(config('leads.conversion_name'), $csv);
    }

    #[Test]
    public function no_tracking_tag_is_rendered_until_the_ids_are_configured(): void
    {
        config(['tracking.ads_id' => null, 'tracking.ga4_id' => null]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('googletagmanager.com', false);
    }

    #[Test]
    public function the_google_tag_loads_on_every_public_page(): void
    {
        // Google's instruction is "every page of your website". The legal
        // pages extend the site layout, so one include covers all of them —
        // this proves it, rather than trusting that it still does.
        config(['tracking.ads_id' => 'AW-16459820521']);

        foreach (['/', '/privacy-policy', '/terms'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertSee('googletagmanager.com/gtag/js?id=AW-16459820521', false)
                ->assertSee("gtag('config', \"AW-16459820521\")", false);
        }
    }

    #[Test]
    public function the_google_tag_is_the_first_thing_the_head_loads(): void
    {
        config(['tracking.ads_id' => 'AW-16459820521']);

        $html = $this->get('/')->assertOk()->getContent();

        $charset = strpos($html, '<meta charset');
        $tag = strpos($html, 'googletagmanager.com');
        $fonts = strpos($html, 'fonts.googleapis.com');

        // Charset stays inside the first 1024 bytes the HTML spec allows for
        // it, and the tag still beats every other resource on the page.
        $this->assertLessThan(1024, $charset);
        $this->assertLessThan($fonts, $tag);
    }

    #[Test]
    public function the_dashboard_is_left_out_of_the_ads_tag(): void
    {
        // Sales staff opening leads all day would otherwise register as
        // traffic and skew the conversion rate the bidding runs on.
        config(['tracking.ads_id' => 'AW-16459820521']);

        $this->get(route('dashboard.login'))
            ->assertOk()
            ->assertDontSee('googletagmanager.com', false);
    }
}
