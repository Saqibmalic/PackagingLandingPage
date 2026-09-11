<?php

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference' => Lead::newReference(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('(###) ###-####'),
            'quantity' => fake()->randomElement(['100 – 250', '250 – 500', '500 – 1,000', '1,000 – 5,000']),
            'status' => 'new',
            'value' => 0,
            'page_url' => 'https://www.customboxesexperts.com/',
            'ip' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }

    /**
     * A lead that arrived from a paid click, so it can be exported to Google Ads.
     */
    public function fromAds(): static
    {
        return $this->state(fn () => [
            'gclid' => 'EAIaIQobCh'.fake()->regexify('[A-Za-z0-9_-]{22}'),
            'utm_source' => 'google',
            'utm_medium' => 'cpc',
            'utm_campaign' => 'Rigid Boxes - US',
            'utm_term' => 'custom rigid boxes wholesale',
            'utm_content' => 'rsa-1',
        ]);
    }

    /**
     * A lead that went on to complete the optional spec form.
     */
    public function withSpecs(): static
    {
        return $this->state(fn () => [
            'specs_added_at' => now(),
            'length' => '8',
            'width' => '6',
            'depth' => '3',
            'units' => 'in',
            'style' => 'Magnetic closure',
            'board' => '2mm greyboard (standard)',
            'wrap' => 'Printed art paper (CMYK)',
            'insert' => 'EVA or PU foam',
            'finish' => ['Foil stamping', 'Soft-touch matte'],
            'second_quantity' => '1,000',
            'notes' => fake()->sentence(12),
        ]);
    }
}
