NEW RIGID BOX LEAD — contact captured
==============================================

Reference: {{ $lead->reference }}
Name:      {{ $lead->name }}
Email:     {{ $lead->email }}
Phone:     {{ $lead->phone }}
Quantity:  {{ $lead->quantity }}

Box specs have not been submitted yet. Call this lead now —
do not wait for the spec form.

----------------------------------------------
CAMPAIGN DATA
GCLID:     {{ $lead->gclid ?: '—' }}
@if ($lead->gbraid)
GBRAID:    {{ $lead->gbraid }}
@endif
@if ($lead->wbraid)
WBRAID:    {{ $lead->wbraid }}
@endif
Source:    {{ $lead->utm_source ?: '—' }}
Medium:    {{ $lead->utm_medium ?: '—' }}
Campaign:  {{ $lead->utm_campaign ?: '—' }}
Keyword:   {{ $lead->utm_term ?: '—' }}
Ad:        {{ $lead->utm_content ?: '—' }}
Page:      {{ $lead->page_url ?: '—' }}

Submitted: {{ $lead->created_at->format('Y-m-d H:i:s T') }}
IP:        {{ $lead->ip ?: '—' }}

Open in the dashboard:
{{ route('dashboard', ['q' => $lead->reference]) }}
