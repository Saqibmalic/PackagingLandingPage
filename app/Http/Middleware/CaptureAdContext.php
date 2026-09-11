<?php

namespace App\Http\Middleware;

use App\Support\AdContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Records the ad click behind every page view, and refreshes the gclid cookie
 * so attribution survives a visitor who leaves and comes back.
 */
class CaptureAdContext
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET')) {
            $context = AdContext::capture($request);

            if (! empty($context['gclid'])) {
                // 90 days matches the default Google Ads click-through
                // conversion window, so the cookie never outlives its usefulness.
                Cookie::queue(AdContext::COOKIE, $context['gclid'], 60 * 24 * 90);
            }
        }

        return $next($request);
    }
}
