<?php

namespace App\Services\Marketing;

use App\Models\Campaign;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PromotionResolver
{
    /**
     * @return \Illuminate\Support\Collection<int, \App\Models\Campaign>
     */
    public function getForPlacement(string $placementKey, array $context = []): Collection
    {
        $cacheable = empty($context['user']) && empty($context['customer']) && empty($context['cart_total']);

        if (! $cacheable) {
            return $this->resolve($placementKey, $context);
        }

        $version = Cache::get('marketing_cache_version', 1);
        $cacheKey = 'promotions.'.$version.'.'.md5($placementKey.'|'.serialize($this->cacheableContext($context)));

        return Cache::remember($cacheKey, 300, fn () => $this->resolve($placementKey, $context));
    }

    public function getGroupedForHomeTab(string $homeTabSlug, array $context = []): array
    {
        $context = array_merge($context, ['home_tab_slug' => $homeTabSlug]);
        $placements = [
            'home_tab_hero',
            'home_tab_offer_strip',
            'home_tab_promo_cards',
            'home_tab_circle_categories',
            'home_tab_bottom_banner',
        ];

        $resolved = [];

        foreach ($placements as $placement) {
            $resolved[$placement] = $this->getForPlacement($placement, $context);
        }

        return $resolved;
    }

    private function resolve(string $placementKey, array $context): Collection
    {
        return Campaign::query()
            ->select('campaigns.*')
            ->selectRaw('campaign_placement.sort_order as placement_sort_order')
            ->currentlyActive()
            ->join('campaign_placement', 'campaigns.id', '=', 'campaign_placement.campaign_id')
            ->join('ad_placements', 'ad_placements.id', '=', 'campaign_placement.ad_placement_id')
            ->where('ad_placements.key', $placementKey)
            ->where('ad_placements.is_active', true)
            ->with([
                'creative.product',
                'creative.category',
                'assets' => fn ($query) => $query->active()->with(['product', 'category']),
                'rules',
                'placements' => fn ($query) => $query->where('key', $placementKey),
            ])
            ->orderByDesc('campaigns.priority')
            ->orderBy('campaign_placement.sort_order')
            ->orderBy('campaigns.id')
            ->get()
            ->filter(fn (Campaign $campaign) => $this->passesRules($campaign, $context))
            ->values();
    }

    private function passesRules(Campaign $campaign, array $context): bool
    {
        $rules = $campaign->rules;

        if ($rules->isEmpty()) {
            return true;
        }

        foreach ($rules as $rule) {
            if ($rule->target_type === 'all') {
                continue;
            }

            if (! $this->passesRule($rule->target_type, $rule->operator, $rule->value ?? [], $context)) {
                return false;
            }
        }

        return true;
    }

    private function passesRule(string $targetType, string $operator, array $value, array $context): bool
    {
        return match ($targetType) {
            'home_tab' => $this->compare($context['home_tab_slug'] ?? null, $operator, $value['tab_slug'] ?? $value['tab_slugs'] ?? null),
            'category' => $this->compare((int) ($context['category_id'] ?? 0), $operator, $value['category_id'] ?? $value['category_ids'] ?? null),
            'product' => $this->compare((int) ($context['product_id'] ?? 0), $operator, $value['product_id'] ?? $value['product_ids'] ?? null),
            'device' => $this->compare($context['device'] ?? null, $operator, $value['device'] ?? $value['devices'] ?? null),
            'guest_only' => empty($context['user']) && empty($context['customer']),
            'logged_in_only' => ! empty($context['user']) || ! empty($context['customer']),
            default => true,
        };
    }

    private function compare(mixed $actual, string $operator, mixed $expected): bool
    {
        return match ($operator) {
            'in' => in_array($actual, (array) $expected, true),
            'greater_than' => is_numeric($actual) && is_numeric($expected) && $actual > $expected,
            'less_than' => is_numeric($actual) && is_numeric($expected) && $actual < $expected,
            default => (string) $actual === (string) $expected,
        };
    }

    private function cacheableContext(array $context): array
    {
        return collect($context)
            ->only(['home_tab_slug', 'category_id', 'product_id', 'device'])
            ->sortKeys()
            ->all();
    }
}
