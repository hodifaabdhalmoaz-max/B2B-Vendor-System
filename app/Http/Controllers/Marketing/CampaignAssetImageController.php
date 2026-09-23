<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\CampaignAsset;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CampaignAssetImageController extends Controller
{
    public function show(CampaignAsset $asset): StreamedResponse
    {
        abort_unless($asset->is_active, 404);
        abort_unless(Storage::disk('public')->exists($asset->image_path), 404);

        return Storage::disk('public')->response($asset->image_path, null, [
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }
}
