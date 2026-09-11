<?php

/*
|--------------------------------------------------------------------------
| Buyer questions
|--------------------------------------------------------------------------
|
| One source of truth: the landing page renders these as <details> blocks and
| partials/schema.blade.php emits the same list as FAQPage structured data, so
| the rich result can never drift from what the page actually says.
|
| 'schema' => false keeps an entry off the structured data while still showing
| it on the page — useful for anything whose answer is a range rather than a
| fact Google should quote.
|
*/

return [
    [
        'q' => 'What is the minimum order for custom rigid boxes?',
        'a' => 'Our standard minimum is 100 units per size and style. Rigid boxes are hand-assembled, so smaller runs are possible on request — they simply carry a higher unit cost. Tell us your quantity and we’ll tell you honestly whether it makes sense.',
    ],
    [
        'q' => 'How long does production take?',
        'a' => 'Standard turnaround is 12–15 business days once artwork and the pre-production sample are approved. Rush production of 8–10 business days is available on most styles — ask when you request your quote and we’ll confirm the date in writing.',
    ],
    [
        'q' => 'How much do custom rigid boxes cost?',
        'a' => 'Size, board thickness, wrap stock, finishing and quantity all move the price. Most projects land between $1.20 and $6.00 per unit. Use the ranges above as a budgeting start, then send your specs for an exact figure within one business hour.',
    ],
    [
        'q' => 'Do you charge for dies, plates or design work?',
        'a' => 'No. Die and plate charges, structural design, dieline preparation and your 3D mockup are all included in the quoted price. No setup fees appear later.',
    ],
    [
        'q' => 'What board thickness should I choose?',
        'a' => '2mm greyboard is standard for most retail rigid boxes and is what we quote by default. Move to 2.5mm–3mm for large formats, heavy products or boxes that need to survive repeated handling; 1.5mm suits small jewelry and cosmetic boxes.',
    ],
    [
        'q' => 'Can I get a physical sample before the full run?',
        'a' => 'Yes, and we insist on it. Before mass production we build a pre-production sample of your exact box — correct board, wrap, print, foil and insert — and ship it to you. Production only starts once you approve it.',
    ],
    [
        'q' => 'Do you ship free within the USA?',
        'a' => 'Yes. Ground shipping to a single address in the contiguous United States and Canada is included in every quote. Expedited freight, split shipments and 3PL delivery can be quoted on request.',
    ],
    [
        'q' => 'Can you match a Pantone color or replicate a box I already have?',
        'a' => 'Yes. Send a physical sample or clear reference photos and we’ll match the structure, stock and Pantone colors. The match is confirmed on your pre-production sample before anything runs.',
    ],
    [
        'q' => 'What artwork files do you need?',
        'a' => 'Print-ready vector artwork (AI, EPS or PDF) at 300dpi with CMYK or Pantone values, laid out on the dieline we supply. No artwork ready? Our design team places your logo and assets on the dieline at no charge.',
        'schema' => false,
    ],
    [
        'q' => 'Do you offer eco-friendly rigid packaging?',
        'a' => 'Yes. FSC®-certified boards, recycled greyboard, soy-based inks, kraft and uncoated wraps, and plastic-free molded pulp or honeycomb inserts. Ask for the sustainable spec and we’ll quote it alongside the standard build.',
        'schema' => false,
    ],
];
