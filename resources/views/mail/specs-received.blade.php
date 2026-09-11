BOX SPECS ADDED — {{ $lead->reference }}
==============================================

{{ $lead->name }} · {{ $lead->email }} · {{ $lead->phone }}

Size:        {{ $lead->dimensions() ?: '—' }}
Box style:   {{ $lead->style ?: '— (asked us to recommend)' }}
Board:       {{ $lead->board ?: '— (asked us to recommend)' }}
Wrap:        {{ $lead->wrap ?: '—' }}
Insert:      {{ $lead->insert ?: '—' }}
Finishing:   {{ $lead->finish ? implode(', ', $lead->finish) : '—' }}
Compare qty: {{ $lead->second_quantity ?: '—' }}
Needed by:   {{ $lead->need_by?->format('j M Y') ?: '—' }}

Notes:
{{ $lead->notes ?: '—' }}

Artwork:     {{ $lead->files ? count($lead->files).' file(s) uploaded' : 'none' }}
@foreach ($lead->files ?? [] as $file)
  - {{ $file['original'] }} ({{ number_format($file['size'] / 1024) }} KB)
@endforeach

----------------------------------------------
Campaign: {{ $lead->utm_campaign ?: '—' }} | Keyword: {{ $lead->utm_term ?: '—' }} | GCLID: {{ $lead->gclid ?: '—' }}
Specs added: {{ $lead->specs_added_at?->format('Y-m-d H:i:s T') }}

Open in the dashboard (artwork downloads there):
{{ route('dashboard', ['q' => $lead->reference]) }}
