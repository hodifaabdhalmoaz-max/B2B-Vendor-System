<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCampaignRequest;
use App\Http\Requests\UpdateCampaignRequest;
use App\Models\AdPlacement;
use App\Models\Campaign;
use App\Models\CampaignAsset;
use App\Models\CampaignCreative;
use App\Models\CampaignRule;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MarketingCampaignController extends Controller
{
    public function index(): View
    {
        $campaigns = Campaign::with(['placements', 'creative'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('admin.marketing.campaigns.index', compact('campaigns'));
    }

    public function create(): View
    {
        return view('admin.marketing.campaigns.create', $this->formData());
    }

    public function store(StoreCampaignRequest $request): RedirectResponse
    {
        $campaign = Campaign::create($this->campaignData($request->validated()));
        $campaign->creative()->create($this->creativeData($request->validated('creative', [])));
        $this->syncPlacements($campaign, $request->validated());
        $this->syncRules($campaign, $request->validated());
        $this->storeNewAssets($campaign, $request->validated('assets', []), $request);
        $this->clearMarketingCache();

        return redirect()->route('admin.marketing.campaigns.edit', $campaign)->with('status', 'تم إنشاء الحملة بنجاح.');
    }

    public function edit(Campaign $campaign): View
    {
        $campaign->load(['creative', 'assets.product', 'assets.category', 'rules', 'placements']);

        return view('admin.marketing.campaigns.edit', array_merge($this->formData(), compact('campaign')));
    }

    public function update(UpdateCampaignRequest $request, Campaign $campaign): RedirectResponse
    {
        $campaign->update($this->campaignData($request->validated(), $campaign));
        $campaign->creative()->updateOrCreate(
            ['campaign_id' => $campaign->id],
            $this->creativeData($request->validated('creative', []))
        );

        $this->syncPlacements($campaign, $request->validated());
        $this->syncRules($campaign, $request->validated());
        $this->syncExistingAssets($campaign, $request->validated('existing_assets', []), $request);
        $this->storeNewAssets($campaign, $request->validated('assets', []), $request);
        $this->clearMarketingCache();

        return redirect()->route('admin.marketing.campaigns.edit', $campaign)->with('status', 'تم تحديث الحملة بنجاح.');
    }

    public function destroy(Campaign $campaign): RedirectResponse
    {
        $campaign->load('assets');

        foreach ($campaign->assets as $asset) {
            $this->deleteAssetImage($asset->image_path);
        }

        $campaign->delete();
        $this->clearMarketingCache();

        return redirect()->route('admin.marketing.campaigns.index')->with('status', 'تم حذف الحملة بنجاح.');
    }

    private function formData(): array
    {
        return [
            'placements' => AdPlacement::active()->orderBy('name')->get(),
            'categories' => Category::active()->select('id', 'name', 'slug')->orderBy('name')->get(),
            'homeTabs' => $this->fixedHomeTabs(),
            'products' => Product::select('id', 'name', 'slug')->orderBy('name')->limit(300)->get(),
            'campaignTypes' => Campaign::TYPES,
            'campaignStatuses' => Campaign::STATUSES,
            'assetRoles' => CampaignAsset::ROLES,
            'linkTypes' => CampaignCreative::LINK_TYPES,
            'ruleTargetTypes' => CampaignRule::TARGET_TYPES,
        ];
    }

    private function fixedHomeTabs(): array
    {
        return [
            ['slug' => 'all', 'label' => 'الكل'],
            ['slug' => 'kids', 'label' => 'الأطفال'],
            ['slug' => 'gifts', 'label' => 'هدايا'],
            ['slug' => 'toys', 'label' => 'العاب وتعليم'],
            ['slug' => 'mother', 'label' => 'مستلزمات'],
            ['slug' => 'care', 'label' => 'الصحة والعناية'],
            ['slug' => 'accessories', 'label' => 'اكسسوارات'],
            ['slug' => 'shoes', 'label' => 'أحذية'],
        ];
    }

    private function campaignData(array $data, ?Campaign $campaign = null): array
    {
        $slug = $data['slug'] ?? null;
        $slug = $slug ? Str::slug($slug) : Str::slug($data['name']);
        $baseSlug = $slug ?: (string) Str::uuid();
        $uniqueSlug = $baseSlug;
        $counter = 2;

        while (Campaign::where('slug', $uniqueSlug)->when($campaign, fn ($query) => $query->where('id', '<>', $campaign->id))->exists()) {
            $uniqueSlug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return [
            'name' => $data['name'],
            'slug' => $uniqueSlug,
            'type' => $data['type'],
            'status' => $data['status'],
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'priority' => (int) $data['priority'],
            'is_active' => (bool) $data['is_active'],
        ];
    }

    private function creativeData(array $data): array
    {
        return [
            'title' => $data['title'] ?? null,
            'subtitle' => $data['subtitle'] ?? null,
            'description' => $data['description'] ?? null,
            'cta_text' => $data['cta_text'] ?? null,
            'link_type' => $data['link_type'] ?? 'url',
            'link_url' => $data['link_url'] ?? null,
            'product_id' => $data['product_id'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'background_color' => $data['background_color'] ?? null,
            'text_color' => $data['text_color'] ?? null,
            'alt_text' => $data['alt_text'] ?? null,
        ];
    }

    private function syncPlacements(Campaign $campaign, array $data): void
    {
        $sortOrders = $data['placement_sort_order'] ?? [];
        $campaign->placements()->sync(
            collect($data['placements'] ?? [])
                ->mapWithKeys(fn ($id) => [(int) $id => ['sort_order' => (int) ($sortOrders[$id] ?? 0)]])
                ->all()
        );
    }

    private function syncRules(Campaign $campaign, array $data): void
    {
        $campaign->rules()->delete();

        $targetType = $data['rule_target_type'] ?? null;

        if (! $targetType || $targetType === 'all') {
            $campaign->rules()->create([
                'target_type' => 'all',
                'operator' => 'equals',
                'value' => [],
            ]);

            return;
        }

        $value = match ($targetType) {
            'home_tab' => ['tab_slug' => $data['rule_home_tab_slug'] ?? null],
            'category' => ['category_id' => (int) ($data['rule_category_id'] ?? 0)],
            'product' => ['product_id' => (int) ($data['rule_product_id'] ?? 0)],
            'device' => ['device' => $data['rule_device'] ?? null],
            default => [],
        };

        $campaign->rules()->create([
            'target_type' => $targetType,
            'operator' => 'equals',
            'value' => $value,
        ]);
    }

    private function storeNewAssets(Campaign $campaign, array $assets, StoreCampaignRequest|UpdateCampaignRequest $request): void
    {
        foreach ($assets as $index => $assetData) {
            $file = $request->file("assets.{$index}.image");

            if (! $file) {
                continue;
            }

            $campaign->assets()->create($this->assetData($assetData, $file->store('campaigns', 'public')));
        }
    }

    private function syncExistingAssets(Campaign $campaign, array $assets, UpdateCampaignRequest $request): void
    {
        foreach ($assets as $index => $assetData) {
            $asset = $campaign->assets()->whereKey($assetData['id'])->first();

            if (! $asset) {
                continue;
            }

            if (! empty($assetData['delete'])) {
                $this->deleteAssetImage($asset->image_path);
                $asset->delete();

                continue;
            }

            $imagePath = $asset->image_path;
            $file = $request->file("existing_assets.{$index}.image");

            if ($file) {
                $imagePath = $file->store('campaigns', 'public');
                $this->deleteAssetImage($asset->image_path);
            }

            $asset->update($this->assetData($assetData, $imagePath));
        }
    }

    private function assetData(array $assetData, string $imagePath): array
    {
        return [
            'role' => $assetData['role'] ?? 'hero_product',
            'image_path' => $imagePath,
            'title' => $assetData['title'] ?? null,
            'subtitle' => $assetData['subtitle'] ?? null,
            'price_text' => $assetData['price_text'] ?? null,
            'link_url' => $assetData['link_url'] ?? null,
            'product_id' => $assetData['product_id'] ?? null,
            'category_id' => $assetData['category_id'] ?? null,
            'sort_order' => (int) ($assetData['sort_order'] ?? 0),
            'is_active' => (bool) ($assetData['is_active'] ?? true),
        ];
    }

    private function deleteAssetImage(?string $imagePath): void
    {
        if ($imagePath) {
            Storage::disk('public')->delete($imagePath);
        }
    }

    private function clearMarketingCache(): void
    {
        Cache::forever('marketing_cache_version', now()->timestamp);
        Cache::forget('home_tabs_categories');
    }
}
