<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Support\GoogleAdsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function __invoke(Request $request, GoogleAdsExport $export): StreamedResponse
    {
        $validated = Validator::make($request->all(), [
            'type' => ['required', 'in:'.implode(',', GoogleAdsExport::TYPES)],
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:'.implode(',', config('leads.statuses'))],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ])->validate();

        // The same filters the dashboard list uses, so the file contains
        // exactly the rows on screen when the button was pressed.
        $leads = Lead::query()
            ->filter([
                'q' => $validated['q'] ?? '',
                'status' => $validated['status'] ?? '',
                'from' => $validated['from'] ?? '',
                'to' => $validated['to'] ?? '',
                'gclid_only' => $request->boolean('gclidOnly') || $validated['type'] === 'gclid',
                'has_specs' => $request->boolean('hasSpecs'),
            ])
            ->latest('created_at')
            ->get();

        $rows = match ($validated['type']) {
            'gclid' => $export->gclidRows($leads),
            'enhanced' => $export->enhancedRows($leads),
            'all' => $export->allRows($leads),
        };

        $filename = match ($validated['type']) {
            'gclid' => 'google-ads-conversions-'.now()->format('Y-m-d').'.csv',
            'enhanced' => 'google-ads-enhanced-conversions-'.now()->format('Y-m-d').'.csv',
            'all' => 'leads-'.now()->format('Y-m-d').'.csv',
        };

        // Mark what went to Google, so you can tell at a glance which leads
        // have already been uploaded.
        if ($validated['type'] !== 'all' && $leads->isNotEmpty()) {
            Lead::whereKey($leads->pluck('id'))->update(['exported_at' => now()]);
        }

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            foreach ($rows as $row) {
                fputcsv($handle, $row, escape: '');
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
