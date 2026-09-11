<?php

namespace App\Models;

use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Guarded(['id'])]
class Lead extends Model
{
    /** @use HasFactory<LeadFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'finish' => 'array',
            'files' => 'array',
            'need_by' => 'date',
            'specs_added_at' => 'datetime',
            'exported_at' => 'datetime',
            'value' => 'decimal:2',
        ];
    }

    /**
     * A short, human-speakable id. Sales reads it down the phone and types it
     * into the dashboard search, so it avoids characters that are ambiguous
     * out loud (0/O, 1/I) and stays eight characters.
     */
    public static function newReference(): string
    {
        do {
            $reference = Str::upper(Str::random(8));
            $reference = strtr($reference, ['O' => 'X', '0' => 'X', 'I' => 'Y', '1' => 'Y', 'L' => 'Z']);
        } while (static::where('reference', $reference)->exists());

        return $reference;
    }

    public function hasSpecs(): bool
    {
        return $this->specs_added_at !== null;
    }

    public function dimensions(): string
    {
        if (! $this->length && ! $this->width && ! $this->depth) {
            return '';
        }

        return trim(sprintf(
            '%s × %s × %s %s',
            $this->length ?: '?',
            $this->width ?: '?',
            $this->depth ?: '?',
            $this->units ?: ''
        ));
    }

    /**
     * Every dashboard list and every CSV export runs through this one filter,
     * so what you see on screen is exactly what the export contains.
     *
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        $term = trim((string) ($filters['q'] ?? ''));

        return $query
            ->when($term !== '', function (Builder $q) use ($term) {
                $like = '%'.$term.'%';
                $q->where(fn (Builder $w) => $w
                    ->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhere('reference', 'like', $like)
                    ->orWhere('notes', 'like', $like)
                    ->orWhere('utm_campaign', 'like', $like)
                );
            })
            ->when(($filters['status'] ?? '') !== '', fn (Builder $q) => $q->where('status', $filters['status']))
            ->when(($filters['from'] ?? '') !== '', fn (Builder $q) => $q->whereDate('created_at', '>=', $filters['from']))
            ->when(($filters['to'] ?? '') !== '', fn (Builder $q) => $q->whereDate('created_at', '<=', $filters['to']))
            ->when(! empty($filters['gclid_only']), fn (Builder $q) => $q->whereNotNull('gclid')->where('gclid', '!=', ''))
            ->when(! empty($filters['has_specs']), fn (Builder $q) => $q->whereNotNull('specs_added_at'));
    }
}
