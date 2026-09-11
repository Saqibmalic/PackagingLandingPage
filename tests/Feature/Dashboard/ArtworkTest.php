<?php

namespace Tests\Feature\Dashboard;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ArtworkTest extends TestCase
{
    use RefreshDatabase;

    protected Lead $lead;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->lead = Lead::factory()->create([
            'files' => [['stored' => 'abc123.pdf', 'original' => 'dieline.pdf', 'size' => 4096]],
        ]);

        Storage::disk('local')->put('artwork/'.$this->lead->reference.'/abc123.pdf', 'pretend pdf');
    }

    #[Test]
    public function a_signed_in_user_can_download_artwork_under_its_original_name(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard.artwork', [$this->lead, 'abc123.pdf']))
            ->assertOk()
            ->assertDownload('dieline.pdf');
    }

    #[Test]
    public function artwork_is_not_reachable_without_signing_in(): void
    {
        $this->get(route('dashboard.artwork', [$this->lead, 'abc123.pdf']))
            ->assertRedirect(route('dashboard.login'));
    }

    #[Test]
    public function a_filename_that_is_not_on_the_lead_is_refused(): void
    {
        // The stored name is matched against the lead's own file list, so a
        // guessed or traversing path cannot reach anything.
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard.artwork', [$this->lead, 'someone-elses.pdf']))
            ->assertNotFound();
    }

    #[Test]
    public function artwork_belonging_to_another_lead_is_refused(): void
    {
        $other = Lead::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('dashboard.artwork', [$other, 'abc123.pdf']))
            ->assertNotFound();
    }
}
