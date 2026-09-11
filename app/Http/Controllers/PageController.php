<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function landing(): View
    {
        return view('pages.landing');
    }

    /**
     * Only reachable by someone who just submitted the quote form: the lead
     * reference lives in the session, never in the URL, so the page cannot be
     * shared, guessed or crawled into.
     */
    public function thankYou(Request $request): View|RedirectResponse
    {
        $lead = Lead::where('reference', $request->session()->get('lead.reference'))->first();

        if (! $lead) {
            return redirect()->route('home');
        }

        return view('pages.thank-you', [
            'lead' => $lead,
            // pull(), not get(): reading the flag also clears it, so a reload
            // or a back-button return cannot count the same lead twice.
            'fireConversion' => (bool) $request->session()->pull('lead.fire_conversion', false),
        ]);
    }

    public function privacy(): View
    {
        return view('pages.privacy');
    }

    public function terms(): View
    {
        return view('pages.terms');
    }
}
