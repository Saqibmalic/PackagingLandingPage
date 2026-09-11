<?php

namespace Tests\Feature\Dashboard;

use App\Livewire\Dashboard\LeadsTable;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LeadsTableTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    #[Test]
    public function it_lists_leads_newest_first(): void
    {
        $older = Lead::factory()->create(['name' => 'Older Lead', 'created_at' => now()->subDays(3)]);
        $newer = Lead::factory()->create(['name' => 'Newer Lead', 'created_at' => now()]);

        Livewire::test(LeadsTable::class)
            ->assertSeeInOrder([$newer->name, $older->name]);
    }

    #[Test]
    public function it_searches_across_name_email_phone_and_reference(): void
    {
        $target = Lead::factory()->create(['name' => 'Amara Chen', 'email' => 'amara@northlight.co']);
        $other = Lead::factory()->create(['name' => 'Someone Else', 'email' => 'else@example.com']);

        $table = Livewire::test(LeadsTable::class);

        foreach (['Amara', 'northlight.co', $target->reference] as $term) {
            $table->set('search', $term)
                ->assertSee($target->email)
                ->assertDontSee($other->email);
        }
    }

    #[Test]
    public function it_filters_by_status_source_and_specs(): void
    {
        $won = Lead::factory()->fromAds()->create(['status' => 'won', 'email' => 'won@example.com']);
        $plain = Lead::factory()->create(['status' => 'new', 'email' => 'plain@example.com']);
        $withSpecs = Lead::factory()->withSpecs()->create(['email' => 'specs@example.com']);

        Livewire::test(LeadsTable::class)
            ->set('status', 'won')
            ->assertSee($won->email)->assertDontSee($plain->email)
            ->set('status', '')
            ->set('gclidOnly', true)
            ->assertSee($won->email)->assertDontSee($plain->email)
            ->set('gclidOnly', false)
            ->set('hasSpecs', true)
            ->assertSee($withSpecs->email)->assertDontSee($plain->email);
    }

    #[Test]
    public function it_filters_by_date_range(): void
    {
        $old = Lead::factory()->create(['email' => 'old@example.com', 'created_at' => now()->subDays(20)]);
        $recent = Lead::factory()->create(['email' => 'recent@example.com', 'created_at' => now()->subDay()]);

        Livewire::test(LeadsTable::class)
            ->set('from', now()->subDays(5)->toDateString())
            ->assertSee($recent->email)
            ->assertDontSee($old->email)
            ->set('from', '')
            ->set('to', now()->subDays(10)->toDateString())
            ->assertSee($old->email)
            ->assertDontSee($recent->email);
    }

    #[Test]
    public function a_status_a_value_and_notes_can_be_saved(): void
    {
        $lead = Lead::factory()->create();

        Livewire::test(LeadsTable::class)
            ->call('setStatus', $lead->id, 'won')
            ->call('setValue', $lead->id, '4,200.50')
            ->call('toggle', $lead->id)
            ->set('noteDrafts.'.$lead->id, 'Quoted $2.80/unit at 1,000.')
            ->call('saveNotes', $lead->id);

        $lead->refresh();

        $this->assertSame('won', $lead->status);
        $this->assertSame('4200.50', $lead->value);
        $this->assertSame('Quoted $2.80/unit at 1,000.', $lead->admin_notes);
    }

    #[Test]
    public function an_invalid_status_is_refused(): void
    {
        $lead = Lead::factory()->create(['status' => 'new']);

        Livewire::test(LeadsTable::class)
            ->call('setStatus', $lead->id, 'definitely-not-a-status')
            ->assertHasErrors('status');

        $this->assertSame('new', $lead->fresh()->status);
    }

    #[Test]
    public function a_lead_can_be_deleted(): void
    {
        $lead = Lead::factory()->create();

        Livewire::test(LeadsTable::class)->call('delete', $lead->id);

        $this->assertSame(0, Lead::count());
    }

    #[Test]
    public function the_headline_numbers_add_up(): void
    {
        Lead::factory()->count(3)->create();
        Lead::factory()->fromAds()->create();
        Lead::factory()->withSpecs()->create();
        Lead::factory()->create(['status' => 'won', 'value' => 4200]);

        Livewire::test(LeadsTable::class)
            ->assertSee('6')          // total
            ->assertSee('$4,200.00'); // won value
    }

    #[Test]
    public function clearing_the_filters_restores_the_full_list(): void
    {
        $lead = Lead::factory()->create(['email' => 'findme@example.com']);

        Livewire::test(LeadsTable::class)
            ->set('search', 'nothing-matches-this')
            ->assertDontSee($lead->email)
            ->call('clearFilters')
            ->assertSee($lead->email)
            ->assertSet('search', '');
    }
}
