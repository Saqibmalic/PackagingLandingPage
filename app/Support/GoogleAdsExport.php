<?php

namespace App\Support;

use App\Models\Lead;
use Illuminate\Support\Collection;

/**
 * Builds the three CSV files the dashboard offers.
 *
 * Two of them are upload formats Google Ads accepts as-is; get a column name
 * or a date format wrong and the import is rejected with no explanation, so
 * the layouts here are deliberately literal.
 */
class GoogleAdsExport
{
    public const TYPES = ['gclid', 'enhanced', 'all'];

    /**
     * Offline conversion import keyed on the click id.
     *
     * Google requires the time zone declared on the very first line, before
     * the header row, and conversion times formatted for that zone.
     *
     * @param  Collection<int, Lead>  $leads
     * @return array<int, array<int, string>>
     */
    public function gclidRows(Collection $leads): array
    {
        $rows = [['Parameters:TimeZone='.config('leads.ads_timezone')]];
        $rows[] = ['Google Click ID', 'Conversion Name', 'Conversion Time', 'Conversion Value', 'Conversion Currency'];

        foreach ($leads as $lead) {
            if (! $lead->gclid) {
                continue;
            }

            $rows[] = [
                $lead->gclid,
                config('leads.conversion_name'),
                $lead->created_at->setTimezone(config('leads.ads_timezone'))->format('Y-m-d H:i:s'),
                $this->value($lead),
                config('leads.currency'),
            ];
        }

        return $rows;
    }

    /**
     * Enhanced conversions for leads: the buyer's email and phone, hashed.
     *
     * Google matches on SHA-256 of the normalised value, so normalisation is
     * the whole game — lowercase and trim the email, and put the phone in
     * E.164 before hashing, or nothing matches and the import looks broken.
     *
     * @param  Collection<int, Lead>  $leads
     * @return array<int, array<int, string>>
     */
    public function enhancedRows(Collection $leads): array
    {
        $rows = [['Email', 'Phone Number', 'Conversion Name', 'Conversion Time', 'Conversion Value', 'Conversion Currency']];

        foreach ($leads as $lead) {
            $rows[] = [
                $this->hashEmail($lead->email),
                $this->hashPhone($lead->phone),
                config('leads.conversion_name'),
                $lead->created_at->setTimezone(config('leads.ads_timezone'))->format('Y-m-d H:i:s'),
                $this->value($lead),
                config('leads.currency'),
            ];
        }

        return $rows;
    }

    /**
     * Everything held about each lead — for a CRM import or a spreadsheet,
     * not for Google Ads.
     *
     * @param  Collection<int, Lead>  $leads
     * @return array<int, array<int, string>>
     */
    public function allRows(Collection $leads): array
    {
        $rows = [[
            'Reference', 'Received', 'Name', 'Email', 'Phone', 'Quantity', 'Status', 'Value',
            'Size', 'Style', 'Board', 'Wrap', 'Insert', 'Finishing', 'Compare quantity', 'Needed by',
            'Notes', 'Artwork files', 'Admin notes',
            'GCLID', 'GBRAID', 'WBRAID', 'UTM source', 'UTM medium', 'UTM campaign', 'UTM term', 'UTM content',
            'Landing page', 'IP',
        ]];

        foreach ($leads as $lead) {
            $rows[] = [
                $lead->reference,
                $lead->created_at->format('Y-m-d H:i:s'),
                $lead->name,
                $lead->email,
                $lead->phone,
                $lead->quantity,
                $lead->status,
                (string) $lead->value,
                $lead->dimensions(),
                (string) $lead->style,
                (string) $lead->board,
                (string) $lead->wrap,
                (string) $lead->insert,
                implode(' | ', $lead->finish ?? []),
                (string) $lead->second_quantity,
                $lead->need_by?->format('Y-m-d') ?? '',
                (string) $lead->notes,
                collect($lead->files ?? [])->pluck('original')->implode(' | '),
                (string) $lead->admin_notes,
                (string) $lead->gclid,
                (string) $lead->gbraid,
                (string) $lead->wbraid,
                (string) $lead->utm_source,
                (string) $lead->utm_medium,
                (string) $lead->utm_campaign,
                (string) $lead->utm_term,
                (string) $lead->utm_content,
                (string) $lead->page_url,
                (string) $lead->ip,
            ];
        }

        return $rows;
    }

    /**
     * A lead marked won with a real figure exports that figure; everything
     * else exports the default. Feeding Smart Bidding true revenue on the
     * leads that closed is the entire point of the upload.
     */
    protected function value(Lead $lead): string
    {
        $value = (float) $lead->value;

        return number_format($value > 0 ? $value : (float) config('leads.default_value'), 2, '.', '');
    }

    public function hashEmail(?string $email): string
    {
        $email = mb_strtolower(trim((string) $email));

        return $email === '' ? '' : hash('sha256', $email);
    }

    /**
     * E.164 before hashing. A bare 10-digit US number gets its country code,
     * which is the case for practically every number this form collects.
     */
    public function hashPhone(?string $phone): string
    {
        $digits = preg_replace('/\D/', '', (string) $phone) ?? '';

        if ($digits === '') {
            return '';
        }

        if (strlen($digits) === 10) {
            $digits = '1'.$digits;
        }

        return hash('sha256', '+'.$digits);
    }
}
