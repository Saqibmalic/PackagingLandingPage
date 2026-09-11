<?php

namespace Tests\Feature\Dashboard;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    /**
     * @return array<int, array<int, string>>
     */
    protected function csv(string $query): array
    {
        $response = $this->get('/dashboard/export?'.$query);
        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $lines = array_filter(explode("\n", trim($response->streamedContent())));

        return array_map('str_getcsv', $lines);
    }

    #[Test]
    public function the_gclid_file_carries_the_time_zone_line_and_the_exact_header_google_expects(): void
    {
        config(['leads.ads_timezone' => 'America/New_York', 'leads.conversion_name' => 'Quote Form Submit']);

        Lead::factory()->fromAds()->create([
            'gclid' => 'EAIaIQobTEST',
            'created_at' => '2026-09-10 18:30:00',
        ]);

        $rows = $this->csv('type=gclid');

        // Google rejects the file outright if either of these is wrong.
        $this->assertSame(['Parameters:TimeZone=America/New_York'], $rows[0]);
        $this->assertSame(
            ['Google Click ID', 'Conversion Name', 'Conversion Time', 'Conversion Value', 'Conversion Currency'],
            $rows[1]
        );

        $this->assertSame('EAIaIQobTEST', $rows[2][0]);
        $this->assertSame('Quote Form Submit', $rows[2][1]);
        // 18:30 UTC is 14:30 in New York — the time must be in the account's zone.
        $this->assertSame('2026-09-10 14:30:00', $rows[2][2]);
        $this->assertSame('USD', $rows[2][4]);
    }

    #[Test]
    public function leads_with_no_click_id_are_left_out_of_the_gclid_file(): void
    {
        Lead::factory()->fromAds()->create();
        Lead::factory()->count(2)->create(['gclid' => null]);

        $rows = $this->csv('type=gclid');

        // Time zone line + header + exactly one lead.
        $this->assertCount(3, $rows);
    }

    #[Test]
    public function enhanced_conversions_hash_the_normalised_email_and_an_e164_phone(): void
    {
        Lead::factory()->create([
            'email' => 'amara.chen@northlight.co',
            'phone' => '(415) 555-0148',
        ]);

        $rows = $this->csv('type=enhanced');

        $this->assertSame(
            ['Email', 'Phone Number', 'Conversion Name', 'Conversion Time', 'Conversion Value', 'Conversion Currency'],
            $rows[0]
        );

        // Computed independently of the application code: this is the value
        // Google will look for, so it is worth pinning literally.
        $this->assertSame(hash('sha256', 'amara.chen@northlight.co'), $rows[1][0]);
        $this->assertSame(hash('sha256', '+14155550148'), $rows[1][1]);
        $this->assertSame(64, strlen($rows[1][0]));
    }

    #[Test]
    public function an_email_with_odd_casing_and_spacing_hashes_the_same_as_a_clean_one(): void
    {
        // The quote form normalises on the way in, so this is belt and braces
        // for leads that arrive any other way.
        Lead::factory()->create(['email' => 'amara.chen@northlight.co', 'phone' => '4155550148']);

        $rows = $this->csv('type=enhanced');

        $this->assertSame(hash('sha256', 'amara.chen@northlight.co'), $rows[1][0]);
        $this->assertSame(hash('sha256', '+14155550148'), $rows[1][1]);
    }

    #[Test]
    public function a_won_lead_exports_its_real_value_and_everything_else_the_default(): void
    {
        config(['leads.default_value' => 0]);

        Lead::factory()->fromAds()->create(['status' => 'won', 'value' => 4200, 'created_at' => now()->subMinute()]);
        Lead::factory()->fromAds()->create(['status' => 'new', 'value' => 0]);

        $rows = $this->csv('type=gclid');

        $this->assertSame('0.00', $rows[2][3]);
        $this->assertSame('4200.00', $rows[3][3]);
    }

    #[Test]
    public function the_export_honours_the_filters_on_screen(): void
    {
        Lead::factory()->fromAds()->create(['status' => 'won', 'email' => 'won@example.com']);
        Lead::factory()->fromAds()->create(['status' => 'lost', 'email' => 'lost@example.com']);

        $rows = $this->csv('type=all&status=won');

        $this->assertCount(2, $rows);           // header + the one won lead
        $this->assertContains('won@example.com', $rows[1]);
    }

    #[Test]
    public function the_full_export_includes_the_box_specs_and_the_campaign(): void
    {
        Lead::factory()->fromAds()->withSpecs()->create(['name' => 'Amara Chen']);

        $rows = $this->csv('type=all');

        $this->assertSame('Reference', $rows[0][0]);
        $this->assertContains('Amara Chen', $rows[1]);
        $this->assertContains('8 × 6 × 3 in', $rows[1]);
        $this->assertContains('Foil stamping | Soft-touch matte', $rows[1]);
        $this->assertContains('Rigid Boxes - US', $rows[1]);
    }

    #[Test]
    public function exporting_to_google_stamps_the_leads_as_sent_but_a_plain_csv_does_not(): void
    {
        $lead = Lead::factory()->fromAds()->create();

        $this->csv('type=all');
        $this->assertNull($lead->fresh()->exported_at);

        $this->csv('type=gclid');
        $this->assertNotNull($lead->fresh()->exported_at);
    }

    #[Test]
    public function an_unknown_export_type_is_refused(): void
    {
        $this->get('/dashboard/export?type=everything')->assertSessionHasErrors('type');
    }
}
