<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Everything that tells us where a visitor came from.
 *
 * The landing page is reached with ?gclid=…&utm_…= on the URL, but the form
 * may not be submitted until several page views later, and the thank-you page
 * carries none of those parameters. So the first request that carries them
 * writes them to the session, and every later request in the visit reads them
 * back. gclid additionally goes to a 90-day cookie, which covers the visitor
 * who clicks the ad today and fills the form out next week.
 *
 * Capturing this server-side (rather than in JavaScript, as the static version
 * of this page did) means an ad blocker or a JS error cannot cost you the
 * attribution that offline conversion imports depend on.
 */
class AdContext
{
    public const SESSION_KEY = 'ad_context';

    public const COOKIE = 'cbe_gclid';

    /** Click ids and campaign parameters, in the order they are stored. */
    public const FIELDS = [
        'gclid', 'gbraid', 'wbraid',
        'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
    ];

    /**
     * Merge anything on this request into what the session already holds.
     *
     * A parameter that is absent never blanks a value captured earlier — that
     * is the whole point, since the thank-you page has a bare URL.
     *
     * @return array<string, string>
     */
    public static function capture(Request $request): array
    {
        $stored = (array) session(self::SESSION_KEY, []);

        foreach (self::FIELDS as $field) {
            $value = trim((string) $request->query($field, ''));

            if ($value !== '') {
                $stored[$field] = mb_substr($value, 0, 255);
            }
        }

        // A returning visitor has no parameters on the URL but may still carry
        // the click id from the visit that first brought them here.
        if (empty($stored['gclid']) && $request->hasCookie(self::COOKIE)) {
            $stored['gclid'] = mb_substr((string) $request->cookie(self::COOKIE), 0, 255);
        }

        $stored['page_url'] = mb_substr($request->fullUrl(), 0, 512);

        session()->put(self::SESSION_KEY, $stored);

        return $stored;
    }

    /**
     * What to write onto a lead, including the request fingerprint.
     *
     * @return array<string, string|null>
     */
    public static function forLead(Request $request): array
    {
        // Read through the session helper rather than $request->session():
        // a Livewire action runs on its own request object, which does not
        // always carry the store even though the session itself is live.
        $stored = (array) session(self::SESSION_KEY, []);

        $attributes = [];

        foreach (self::FIELDS as $field) {
            $attributes[$field] = $stored[$field] ?? null;
        }

        return $attributes + [
            'page_url' => $stored['page_url'] ?? mb_substr($request->fullUrl(), 0, 512),
            'ip' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 512),
        ];
    }
}
