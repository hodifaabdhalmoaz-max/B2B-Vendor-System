<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdPlacementRequest;
use App\Models\AdPlacement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AdPlacementController extends Controller
{
    public function index(): View
    {
        $placements = AdPlacement::withCount('campaigns')
            ->orderBy('key')
            ->paginate(20);

        return view('admin.marketing.placements.index', compact('placements'));
    }

    public function create(): View
    {
        return view('admin.marketing.placements.create');
    }

    public function store(StoreAdPlacementRequest $request): RedirectResponse
    {
        AdPlacement::create($request->validated());
        $this->clearMarketingCache();

        return redirect()->route('admin.marketing.placements.index')->with('status', 'تم إنشاء مكان الظهور بنجاح.');
    }

    public function edit(AdPlacement $placement): View
    {
        return view('admin.marketing.placements.edit', compact('placement'));
    }

    public function update(StoreAdPlacementRequest $request, AdPlacement $placement): RedirectResponse
    {
        $placement->update($request->validated());
        $this->clearMarketingCache();

        return redirect()->route('admin.marketing.placements.index')->with('status', 'تم تحديث مكان الظهور بنجاح.');
    }

    public function destroy(AdPlacement $placement): RedirectResponse
    {
        $placement->delete();
        $this->clearMarketingCache();

        return redirect()->route('admin.marketing.placements.index')->with('status', 'تم حذف مكان الظهور بنجاح.');
    }

    private function clearMarketingCache(): void
    {
        Cache::forever('marketing_cache_version', now()->timestamp);
    }
}
