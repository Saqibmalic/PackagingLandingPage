<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ArtworkController extends Controller
{
    /**
     * Streams one uploaded file back to a signed-in user.
     *
     * Artwork is stored outside the web root, so this route is the only way
     * to reach it — and it is behind the dashboard's auth middleware. The
     * stored name is matched against the lead's own file list rather than
     * trusted from the URL, which rules out path traversal.
     */
    public function __invoke(Lead $lead, string $file): StreamedResponse
    {
        $match = collect($lead->files ?? [])->firstWhere('stored', $file);

        abort_if(! $match, 404);

        $path = config('leads.upload_path').'/'.$lead->reference.'/'.$match['stored'];
        $disk = Storage::disk(config('leads.upload_disk'));

        abort_unless($disk->exists($path), 404);

        return $disk->download($path, $match['original']);
    }
}
