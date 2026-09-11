<?php

namespace App\Livewire\Dashboard;

use App\Models\Lead;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class LeadsTable extends Component
{
    use WithPagination;

    /*
     * Filters live in the query string, so a filtered view is a link: sales
     * can bookmark "won leads this month" or paste it to a colleague, and the
     * export buttons carry the same parameters.
     */
    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $from = '';

    #[Url(except: '')]
    public string $to = '';

    #[Url(except: false)]
    public bool $gclidOnly = false;

    #[Url(except: false)]
    public bool $hasSpecs = false;

    /** The lead whose detail row is expanded, by id. */
    public ?int $expanded = null;

    /** Per-lead note drafts, keyed by lead id. */
    public array $noteDrafts = [];

    public ?int $justSaved = null;

    /**
     * Any filter change starts the results again from page one — otherwise a
     * narrowed list can land you on a page that no longer exists.
     */
    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'status', 'from', 'to', 'gclidOnly', 'hasSpecs'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'from', 'to', 'gclidOnly', 'hasSpecs']);
        $this->resetPage();
    }

    public function toggle(int $leadId): void
    {
        $this->expanded = $this->expanded === $leadId ? null : $leadId;

        if ($this->expanded && ! isset($this->noteDrafts[$leadId])) {
            $this->noteDrafts[$leadId] = (string) Lead::find($leadId)?->admin_notes;
        }
    }

    /**
     * Note this validates the argument, not $this->status — that property is
     * the filter above, and validating it here would test the wrong value.
     */
    public function setStatus(int $leadId, string $status): void
    {
        Validator::make(
            ['status' => $status],
            ['status' => ['required', Rule::in(config('leads.statuses'))]]
        )->validate();

        Lead::whereKey($leadId)->update(['status' => $status]);
        $this->flash($leadId);
    }

    public function setValue(int $leadId, string $value): void
    {
        $amount = round((float) preg_replace('/[^0-9.\-]/', '', $value), 2);

        Lead::whereKey($leadId)->update(['value' => max(0, $amount)]);
        $this->flash($leadId);
    }

    public function saveNotes(int $leadId): void
    {
        Lead::whereKey($leadId)->update([
            'admin_notes' => mb_substr((string) ($this->noteDrafts[$leadId] ?? ''), 0, 5000),
        ]);
        $this->flash($leadId);
    }

    public function delete(int $leadId): void
    {
        Lead::whereKey($leadId)->delete();

        if ($this->expanded === $leadId) {
            $this->expanded = null;
        }
    }

    protected function flash(int $leadId): void
    {
        $this->justSaved = $leadId;
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return [
            'q' => $this->search,
            'status' => $this->status,
            'from' => $this->from,
            'to' => $this->to,
            'gclid_only' => $this->gclidOnly,
            'has_specs' => $this->hasSpecs,
        ];
    }

    /**
     * The export links carry the filters currently on screen, so the file you
     * download is the list you are looking at.
     *
     * @return array<string, mixed>
     */
    public function exportParams(string $type): array
    {
        return array_filter([
            'type' => $type,
            'q' => $this->search,
            'status' => $this->status,
            'from' => $this->from,
            'to' => $this->to,
            'gclidOnly' => $this->gclidOnly ? 1 : null,
            'hasSpecs' => $this->hasSpecs ? 1 : null,
        ], fn ($value) => $value !== '' && $value !== null);
    }

    /**
     * @return LengthAwarePaginator<int, Lead>
     */
    public function leads(): LengthAwarePaginator
    {
        return Lead::query()
            ->filter($this->filters())
            ->latest('created_at')
            ->paginate(config('dashboard.per_page'));
    }

    /**
     * Headline numbers are deliberately unfiltered: they answer "how is the
     * page doing?", which the current filter should not change.
     *
     * @return array<string, int|float>
     */
    public function stats(): array
    {
        return [
            'total' => Lead::count(),
            'today' => Lead::whereDate('created_at', today())->count(),
            'week' => Lead::where('created_at', '>=', now()->subDays(7))->count(),
            'with_gclid' => Lead::whereNotNull('gclid')->where('gclid', '!=', '')->count(),
            'with_specs' => Lead::whereNotNull('specs_added_at')->count(),
            'won' => Lead::where('status', 'won')->count(),
            'won_value' => (float) Lead::where('status', 'won')->sum('value'),
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.leads-table', [
            'leads' => $this->leads(),
            'stats' => $this->stats(),
        ]);
    }
}
