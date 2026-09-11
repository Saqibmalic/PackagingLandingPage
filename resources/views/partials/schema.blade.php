@php
    $address = config('site.address');
    $orgId = rtrim(config('site.main_site'), '/').'/#org';

    $schema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Organization',
                '@id' => $orgId,
                'name' => config('site.company'),
                'url' => config('site.main_site'),
                'telephone' => config('site.phone_e164'),
                'email' => config('site.email'),
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => $address['street'],
                    'addressLocality' => $address['city'],
                    'addressRegion' => $address['region'],
                    'postalCode' => $address['postal_code'],
                    'addressCountry' => $address['country'],
                ],
                'contactPoint' => [
                    '@type' => 'ContactPoint',
                    'telephone' => config('site.phone_e164'),
                    'contactType' => 'sales',
                    'areaServed' => ['US', 'CA'],
                    'availableLanguage' => 'English',
                ],
            ],
            [
                '@type' => 'Service',
                'name' => 'Custom Rigid Box Manufacturing',
                'serviceType' => 'Custom rigid box packaging',
                'provider' => ['@id' => $orgId],
                'areaServed' => ['@type' => 'Country', 'name' => 'United States'],
                'description' => 'Custom printed rigid setup boxes in magnetic closure, drawer, shoulder neck, book style and two-piece constructions, with foil stamping, embossing, soft-touch lamination and custom inserts.',
            ],
            [
                '@type' => 'FAQPage',
                // Built from config/faq.php, so the rich result always matches
                // the questions actually answered on the page.
                'mainEntity' => collect(config('faq'))
                    ->filter(fn ($item) => $item['schema'] ?? true)
                    ->map(fn ($item) => [
                        '@type' => 'Question',
                        'name' => $item['q'],
                        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['a']],
                    ])
                    ->values()
                    ->all(),
            ],
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
