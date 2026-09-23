<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\Order;
use App\Models\Product;
use App\Services\Marketing\PromotionResolver;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MessageCenterService
{
    public const RECOMMENDED_PRODUCTS_SECTION = 'messages.recommended';

    private const SECTION_PLACEMENTS = [
        'activity' => 'messages_activity',
        'promo' => 'messages_promo',
        'news' => 'messages_news',
    ];

    public function __construct(private PromotionResolver $promotionResolver) {}

    public function overview(Request $request): array
    {
        return [
            'tabs' => $this->tabs(),
            'recommendations' => $this->recommendedProducts(),
        ];
    }

    public function section(Request $request, string $section): array
    {
        return [
            'section' => $section,
            'sectionMeta' => $this->tabs()->firstWhere('key', $section),
            'tabs' => $this->tabs(),
            'orders' => $section === 'orders' ? $this->ordersFor($request) : collect(),
            'campaigns' => $this->campaignsFor($request, $section),
            'recommendations' => $this->recommendedProducts(),
        ];
    }

    public function isValidSection(string $section): bool
    {
        return $this->tabs()->contains('key', $section);
    }

    public function tabs(): Collection
    {
        return collect([
            ['key' => 'orders', 'label' => 'الطلبات', 'title' => 'الطلبات', 'icon' => 'bi-receipt-cutoff'],
            ['key' => 'activity', 'label' => 'النشاط', 'title' => 'النشاطات', 'icon' => 'bi-gift'],
            ['key' => 'promo', 'label' => 'العروض', 'title' => 'العروض', 'icon' => 'bi-tag'],
            ['key' => 'news', 'label' => 'الأخبار', 'title' => 'الأخبار', 'icon' => 'bi-megaphone'],
        ]);
    }

    private function ordersFor(Request $request): Collection
    {
        $user = $request->user();

        if (! $user) {
            return collect();
        }

        return Order::query()
            ->select(['id', 'user_id', 'status', 'total', 'created_at'])
            ->withCount('orderItems')
            ->where('user_id', $user->id)
            ->latest()
            ->take(12)
            ->get();
    }

    private function campaignsFor(Request $request, string $section): Collection
    {
        $placement = self::SECTION_PLACEMENTS[$section] ?? null;

        if (! $placement) {
            return collect();
        }

        $campaigns = $this->promotionResolver->getForPlacement($placement, [
            'device' => $this->deviceFrom($request),
            'user' => $request->user(),
            'guest' => ! $request->user(),
        ]);

        $clearedAt = $this->clearedAt($request);

        if (! $clearedAt) {
            return $campaigns;
        }

        return $campaigns
            ->filter(fn (Campaign $campaign) => $campaign->created_at?->gt($clearedAt))
            ->values();
    }

    private function recommendedProducts(): Collection
    {
        $configured = $this->baseProductQuery()
            ->whereNotNull('storefront_sections')
            ->orderBy('storefront_order')
            ->orderByDesc('created_at')
            ->take(240)
            ->get()
            ->filter(fn (Product $product) => in_array(self::RECOMMENDED_PRODUCTS_SECTION, $product->storefront_sections ?? [], true))
            ->take(12)
            ->values();

        if ($configured->count() >= 8) {
            return $configured;
        }

        return $configured
            ->merge($this->fallbackProducts($configured->pluck('id')->all()))
            ->unique('id')
            ->take(12)
            ->values();
    }

    private function fallbackProducts(array $excludedIds): Collection
    {
        return $this->baseProductQuery()
            ->whereNotIn('id', $excludedIds)
            ->orderByDesc('featured')
            ->orderByDesc('is_offer')
            ->orderByDesc('created_at')
            ->take(12)
            ->get();
    }

    private function baseProductQuery()
    {
        return Product::active()
            ->select([
                'id',
                'name',
                'slug',
                'short_description',
                'regular_price',
                'sale_price',
                'image',
                'images',
                'category_id',
                'brand_id',
                'featured',
                'is_offer',
                'quantity',
                'storefront_sections',
                'storefront_order',
                'created_at',
            ])
            ->withCount(['reviews as active_reviews_count' => fn ($query) => $query->where('status', true)])
            ->withAvg(['reviews as active_reviews_avg' => fn ($query) => $query->where('status', true)], 'rating');
    }

    private function clearedAt(Request $request): ?Carbon
    {
        $value = $request->session()->get('messages_cleared_at');

        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function deviceFrom(Request $request): string
    {
        return Str::contains(Str::lower($request->userAgent() ?? ''), ['mobile', 'android', 'iphone'])
            ? 'mobile'
            : 'desktop';
    }
}
