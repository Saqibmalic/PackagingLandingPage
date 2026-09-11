<?php

namespace App\Livewire;

use App\Mail\SpecsReceived;
use App\Models\Lead;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Stage two: the optional box specification, offered on the thank-you page.
 *
 * It updates the lead created by the quote form rather than creating a second
 * record, so one enquiry is always one row in the dashboard.
 */
class SpecForm extends Component
{
    use WithFileUploads;

    public Lead $lead;

    public string $length = '';

    public string $width = '';

    public string $depth = '';

    public string $units = 'in';

    public string $style = '';

    public string $board = '';

    public string $wrap = '';

    public string $insert = '';

    /** @var array<int, string> */
    public array $finish = [];

    public string $second_quantity = '';

    public string $need_by = '';

    public string $notes = '';

    /** @var array<int, TemporaryUploadedFile> */
    public array $files = [];

    /** Set once the specs are in, or when the buyer declines to add them. */
    public bool $done = false;

    public function mount(Lead $lead): void
    {
        $this->lead = $lead;
        $this->done = $lead->hasSpecs();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'length' => ['nullable', 'numeric', 'min:0', 'max:999'],
            'width' => ['nullable', 'numeric', 'min:0', 'max:999'],
            'depth' => ['nullable', 'numeric', 'min:0', 'max:999'],
            'units' => ['required', 'in:in,cm,mm'],
            'style' => ['nullable', 'string', 'max:60'],
            'board' => ['nullable', 'string', 'max:60'],
            'wrap' => ['nullable', 'string', 'max:80'],
            'insert' => ['nullable', 'string', 'max:80'],
            'finish' => ['array', 'max:10'],
            'finish.*' => ['string', 'max:60'],
            'second_quantity' => ['nullable', 'string', 'max:40'],
            'need_by' => ['nullable', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'files' => ['array', 'max:'.config('leads.max_files')],
            // Extension rather than MIME: .ai and .eps are routinely sniffed as
            // application/pdf and application/postscript, which would reject
            // exactly the artwork we most want. Nothing uploaded is reachable
            // over HTTP or executable, so the extension is the useful check.
            'files.*' => ['file', 'max:'.config('leads.max_file_kb'), 'extensions:'.implode(',', config('leads.allowed_extensions'))],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'files.max' => 'Please attach no more than :max files — email the rest to '.config('site.email').' and we will match them to your quote.',
            'files.*.max' => 'Each file needs to be under 20MB. Please email anything larger to '.config('site.email').'.',
            'files.*.extensions' => 'We can read JPG, PNG, PDF, AI, EPS and ZIP files.',
            'need_by.after_or_equal' => 'Please choose a date in the future.',
        ];
    }

    public function submit(): void
    {
        $this->validate();

        $this->lead->update([
            'specs_added_at' => now(),
            'length' => $this->length ?: null,
            'width' => $this->width ?: null,
            'depth' => $this->depth ?: null,
            'units' => $this->units,
            'style' => $this->style ?: null,
            'board' => $this->board ?: null,
            'wrap' => $this->wrap ?: null,
            'insert' => $this->insert ?: null,
            'finish' => $this->finish ?: null,
            'second_quantity' => $this->second_quantity ?: null,
            'need_by' => $this->need_by ?: null,
            'notes' => $this->notes ?: null,
            'files' => $this->storeFiles() ?: null,
        ]);

        try {
            Mail::to(config('leads.notify'))
                ->cc(config('leads.notify_cc'))
                ->send(new SpecsReceived($this->lead->fresh()));
        } catch (\Throwable $e) {
            Log::error('Spec alert email failed', ['reference' => $this->lead->reference, 'error' => $e->getMessage()]);
        }

        $this->done = true;
    }

    public function skip(): void
    {
        $this->done = true;
    }

    /**
     * Artwork goes to the private disk under a per-lead folder, keeping the
     * buyer's original filename alongside the stored one so sales can talk
     * about "the dieline" rather than a random hash.
     *
     * @return array<int, array{stored: string, original: string, size: int}>
     */
    protected function storeFiles(): array
    {
        $saved = $this->lead->files ?? [];

        foreach ($this->files as $file) {
            $name = Str::random(16).'.'.Str::lower($file->getClientOriginalExtension());

            $file->storeAs(
                config('leads.upload_path').'/'.$this->lead->reference,
                $name,
                config('leads.upload_disk')
            );

            $saved[] = [
                'stored' => $name,
                'original' => Str::limit($file->getClientOriginalName(), 120, ''),
                'size' => $file->getSize(),
            ];
        }

        return $saved;
    }

    /**
     * @return list<string>
     */
    public function styles(): array
    {
        return ['Magnetic closure', 'Two-piece lid & base', 'Drawer / slide-out', 'Shoulder neck',
            'Book style', 'Telescoping', 'Collapsible rigid', 'Rigid mailer'];
    }

    /**
     * @return list<string>
     */
    public function boards(): array
    {
        return ['1.5mm greyboard', '2mm greyboard (standard)', '2.5mm greyboard', '3mm greyboard'];
    }

    /**
     * @return list<string>
     */
    public function wraps(): array
    {
        return ['Printed art paper (CMYK)', 'Specialty — linen / felt / leatherette',
            'Uncoated colorplan', 'Kraft / recycled', 'FSC®-certified stock'];
    }

    /**
     * @return list<string>
     */
    public function inserts(): array
    {
        return ['EVA or PU foam', 'Paperboard platform or divider', 'Molded pulp (plastic-free)',
            'Satin or velvet lined', 'Thermoformed PET'];
    }

    /**
     * @return list<string>
     */
    public function finishes(): array
    {
        return ['Foil stamping', 'Emboss / deboss', 'Soft-touch matte', 'Spot UV',
            'Gloss lamination', 'Ribbon pull'];
    }

    public function render()
    {
        return view('livewire.spec-form');
    }
}
