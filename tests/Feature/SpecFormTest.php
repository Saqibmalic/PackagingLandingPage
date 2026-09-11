<?php

namespace Tests\Feature;

use App\Livewire\SpecForm;
use App\Mail\SpecsReceived;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SpecFormTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_thank_you_page_is_unreachable_without_a_lead_in_the_session(): void
    {
        $this->get('/thank-you')->assertRedirect(route('home'));
    }

    #[Test]
    public function the_thank_you_page_shows_the_lead_that_was_just_submitted(): void
    {
        $lead = Lead::factory()->create();

        $this->withSession(['lead.reference' => $lead->reference])
            ->get('/thank-you')
            ->assertOk()
            ->assertSee('your quote is being built now')
            ->assertSee($lead->reference)
            ->assertSee('Make your quote exact');
    }

    #[Test]
    public function specs_update_the_same_lead_rather_than_creating_a_second_one(): void
    {
        Mail::fake();
        $lead = Lead::factory()->create();

        Livewire::test(SpecForm::class, ['lead' => $lead])
            ->set('length', '9')
            ->set('width', '9')
            ->set('depth', '3')
            ->set('units', 'in')
            ->set('style', 'Magnetic closure')
            ->set('board', '2mm greyboard (standard)')
            ->set('finish', ['Foil stamping', 'Soft-touch matte'])
            ->set('second_quantity', '2,000')
            ->set('notes', 'Skincare launch kit. Soft-touch black with rose gold foil.')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('done', true);

        // One enquiry is still one row.
        $this->assertSame(1, Lead::count());

        $lead->refresh();
        $this->assertNotNull($lead->specs_added_at);
        $this->assertSame('9 × 9 × 3 in', $lead->dimensions());
        $this->assertSame('Magnetic closure', $lead->style);
        $this->assertSame(['Foil stamping', 'Soft-touch matte'], $lead->finish);
        // The contact details from stage one are untouched.
        $this->assertNotNull($lead->name);

        Mail::assertSent(SpecsReceived::class);
    }

    #[Test]
    public function artwork_is_stored_off_the_web_root_and_listed_on_the_lead(): void
    {
        Mail::fake();
        Storage::fake('local');
        $lead = Lead::factory()->create();

        Livewire::test(SpecForm::class, ['lead' => $lead])
            ->set('files', [UploadedFile::fake()->create('dieline.pdf', 400, 'application/pdf')])
            ->call('submit')
            ->assertHasNoErrors();

        $files = $lead->fresh()->files;

        $this->assertCount(1, $files);
        $this->assertSame('dieline.pdf', $files[0]['original']);
        Storage::disk('local')->assertExists('artwork/'.$lead->reference.'/'.$files[0]['stored']);
    }

    #[Test]
    public function an_executable_upload_is_refused(): void
    {
        Storage::fake('local');
        $lead = Lead::factory()->create();

        // Refused the moment it lands, rather than after the buyer has filled
        // in the rest of the form and pressed submit.
        Livewire::test(SpecForm::class, ['lead' => $lead])
            ->set('files', [UploadedFile::fake()->create('shell.php', 10)])
            ->assertHasErrors(['files.0'])
            ->call('submit');

        $this->assertNull($lead->fresh()->files);
        Storage::disk('local')->assertDirectoryEmpty(config('leads.upload_path'));
    }

    #[Test]
    public function more_than_five_files_are_refused(): void
    {
        Storage::fake('local');
        $lead = Lead::factory()->create();

        $files = collect(range(1, 6))
            ->map(fn ($i) => UploadedFile::fake()->image("art-{$i}.jpg"))
            ->all();

        Livewire::test(SpecForm::class, ['lead' => $lead])
            ->set('files', $files)
            ->call('submit')
            ->assertHasErrors(['files']);
    }

    #[Test]
    public function a_refused_file_is_dropped_instead_of_blocking_every_later_submit(): void
    {
        Storage::fake('local');
        $lead = Lead::factory()->create();

        // Livewire appends on a multi-file input. Left in the array, one bad
        // file would fail validation on every subsequent submit, and there was
        // no way to take it out — the buyer could never finish.
        $component = Livewire::test(SpecForm::class, ['lead' => $lead])
            ->set('files', [UploadedFile::fake()->create('shell.php', 10)])
            ->assertHasErrors(['files.0'])
            ->assertSet('files', []);

        $component->set('files', [UploadedFile::fake()->create('dieline.pdf', 400, 'application/pdf')])
            ->assertHasNoErrors()
            ->call('submit')
            ->assertHasNoErrors();

        $files = $lead->fresh()->files;
        $this->assertCount(1, $files);
        $this->assertSame('dieline.pdf', $files[0]['original']);
    }

    #[Test]
    public function a_buyer_can_take_an_uploaded_file_back_out(): void
    {
        Storage::fake('local');
        $lead = Lead::factory()->create();

        Livewire::test(SpecForm::class, ['lead' => $lead])
            ->set('files', [
                UploadedFile::fake()->image('wrong.jpg'),
                UploadedFile::fake()->image('right.jpg'),
            ])
            ->call('removeFile', 0)
            ->assertCount('files', 1)
            ->call('submit');

        $files = $lead->fresh()->files;
        $this->assertCount(1, $files);
        $this->assertSame('right.jpg', $files[0]['original']);
    }

    #[Test]
    public function skipping_the_spec_form_leaves_the_lead_intact(): void
    {
        $lead = Lead::factory()->create();

        Livewire::test(SpecForm::class, ['lead' => $lead])
            ->call('skip')
            ->assertSet('done', true)
            ->assertSee('we have what we need');

        $this->assertNull($lead->fresh()->specs_added_at);
        $this->assertSame(1, Lead::count());
    }

    #[Test]
    public function a_delivery_date_in_the_past_is_refused(): void
    {
        $lead = Lead::factory()->create();

        Livewire::test(SpecForm::class, ['lead' => $lead])
            ->set('need_by', now()->subWeek()->toDateString())
            ->call('submit')
            ->assertHasErrors(['need_by']);
    }
}
