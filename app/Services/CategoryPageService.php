<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Size;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CategoryPageService
{
    public function __construct(private SearchService $search) {}

    public function getPageData(Request $request, string $slug): array
    {
        $category = Category::select('id', 'name', 'slug', 'image', 'parent_id', 'status', 'storefront_contexts', 'storefront_order')
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        $categoryTree = $this->activeCategories();
        $categoryIds = $this->categoryAndDescendantIds($category, $categoryTree);
        $topCategories = $this->topCategoriesFor($category, $categoryTree);
        $params = $this->resolveSortParams($request, $request->all(), $categoryIds);

        if ($request->has('tab') && $request->tab !== 'all') {
            $params['search'] = $request->tab;
        }

        $products = $this->search->searchProducts($params);
        $categoryFilters = $this->categoryFilterOptions($categoryIds->all());

        return $this->buildViewData($request, $products, [
            'category' => $category,
            'selectedCategory' => $category,
            'relatedCategories' => $topCategories,
            'topCategories' => $topCategories,
            'categoryIds' => $categoryIds,
            'activeMarketCategory' => $this->activeMarketCategoryFor($category, $request),
            'brands' => $categoryFilters['brands'],
            'colors' => $categoryFilters['colors'],
            'sizes' => $categoryFilters['sizes'],
            'current_tab' => $request->query('tab', 'all'),
        ]);
    }

    private function buildViewData(Request $request, $products, array $extra = []): array
    {
        $filters = $this->search->getSearchFilters();

        return array_merge([
            'products' => $products,
            'brands' => $filters['brands'],
            'categories' => $filters['categories'],
            'colors' => $filters['colors'],
            'sizes' => $filters['sizes'],
            'min_price' => $request->query('min_price', 0),
            'max_price' => $request->query('max_price', 500000),
            'f_brands' => $request->query('brands', ''),
            'f_categories' => $request->query('categories', ''),
            'size' => $request->query('size', 12),
            'order' => $request->query('order', 'newest'),
            'order_dir' => $request->query('order_dir', 'desc'),
        ], $extra);
    }

    private function resolveSortParams(Request $request, array $params, Collection $categoryIds): array
    {
        $params['per_page'] = $request->query('size') ?: $request->query('per_page', 12);

        $sortBy = $request->query('order') ?: $request->query('sort_by', 'newest');
        $sortDir = $request->query('order_dir') ?: $request->query('sort_direction', 'desc');

        if (is_numeric($sortBy)) {
            $legacySort = (int) $sortBy;
            $sortBy = [1 => 'newest', 2 => 'oldest', 3 => 'price', 4 => 'price'][$legacySort] ?? 'newest';

            if ($sortBy === 'price') {
                $sortDir = $legacySort === 3 ? 'asc' : 'desc';
            }
        }

        $params['sort_by'] = $sortBy;
        $params['sort_direction'] = $sortDir;
        $params['categories'] = $categoryIds->implode(',');

        if (! empty($params['brand']) && empty($params['brands'])) {
            $brand = Brand::where('slug', $params['brand'])->first();
            $params['brands'] = $brand?->id;
        }

        return $params;
    }

    private function activeCategories(): Collection
    {
        return Category::select('id', 'name', 'slug', 'image', 'parent_id', 'status', 'storefront_contexts', 'storefront_order')
            ->withCount('products')
            ->active()
            ->orderBy('storefront_order')
            ->orderBy('name')
            ->get();
    }

    private function categoryAndDescendantIds(Category $category, Collection $categories): Collection
    {
        $ids = collect([(int) $category->id]);
        $children = $categories->where('parent_id', (int) $category->id);

        foreach ($children as $child) {
            $ids = $ids->merge($this->categoryAndDescendantIds($child, $categories));
        }

        return $ids->unique()->values();
    }

    private function topCategoriesFor(Category $category, Collection $categories): Collection
    {
        $familyTree = collect([$category])
            ->merge($this->ancestorCategoriesFor($category, $categories))
            ->merge($this->descendantCategoriesFor($category, $categories))
            ->merge($this->siblingCategoriesFor($category, $categories))
            ->unique('id')
            ->take(16)
            ->values();

        if ($familyTree->count() > 1) {
            return $familyTree;
        }

        $roots = $categories->filter(fn ($item) => empty($item->parent_id))->values();

        if ($roots->isNotEmpty()) {
            return $roots->prepend($category)->unique('id')->take(16)->values();
        }

        return $categories->where('id', '!=', $category->id)
            ->take(15)
            ->prepend($category)
            ->unique('id')
            ->values();
    }

    private function ancestorCategoriesFor(Category $category, Collection $categories): Collection
    {
        $ancestors = collect();
        $parentId = $category->parent_id;

        while (! empty($parentId)) {
            $parent = $categories->firstWhere('id', (int) $parentId);

            if (! $parent) {
                break;
            }

            $ancestors->push($parent);
            $parentId = $parent->parent_id;
        }

        return $ancestors;
    }

    private function descendantCategoriesFor(Category $category, Collection $categories): Collection
    {
        $descendants = collect();
        $queue = $categories->where('parent_id', (int) $category->id)->values();

        while ($queue->isNotEmpty()) {
            $child = $queue->shift();
            $descendants->push($child);

            $queue = $queue
                ->merge($categories->where('parent_id', (int) $child->id)->values())
                ->values();
        }

        return $descendants;
    }

    private function siblingCategoriesFor(Category $category, Collection $categories): Collection
    {
        if (empty($category->parent_id)) {
            return collect();
        }

        return $categories
            ->where('parent_id', (int) $category->parent_id)
            ->where('id', '!=', (int) $category->id)
            ->values();
    }

    private function categoryFilterOptions(array $categoryIds): array
    {
        $brands = Brand::select('id', 'name', 'slug')
            ->whereHas('products', fn ($query) => $this->applyProductCategoryScope($query, $categoryIds))
            ->withCount(['products' => fn ($query) => $this->applyProductCategoryScope($query, $categoryIds)])
            ->orderBy('name')
            ->get();

        $colors = Schema::hasTable('colors')
            ? Color::select('id', 'name', 'hex_code')
                ->whereHas('products', fn ($query) => $this->applyProductCategoryScope($query, $categoryIds))
                ->active()
                ->ordered()
                ->get()
            : collect();

        $sizes = Schema::hasTable('sizes')
            ? Size::select('id', 'name', 'code')
                ->whereHas('products', fn ($query) => $this->applyProductCategoryScope($query, $categoryIds))
                ->active()
                ->ordered()
                ->get()
            : collect();

        return compact('brands', 'colors', 'sizes');
    }

    private function applyProductCategoryScope(Builder $query, array $categoryIds): void
    {
        $query->where(function (Builder $categoryQuery) use ($categoryIds) {
            $categoryQuery->whereIn('category_id', $categoryIds);

            if (Schema::hasTable('category_product')) {
                $categoryQuery->orWhereHas('categories', fn ($query) => $query->whereIn('categories.id', $categoryIds));
            }
        });
    }

    private function activeMarketCategoryFor(Category $category, Request $request): string
    {
        $allowedTabs = ['all', 'kids', 'gifts', 'toys', 'mother', 'care', 'accessories', 'shoes'];
        $requestedTab = $request->query('market_tab');

        if (in_array($requestedTab, $allowedTabs, true)) {
            return $requestedTab;
        }

        $contexts = collect($category->storefront_contexts ?? []);

        foreach ($allowedTabs as $tab) {
            if ($tab !== 'all' && $contexts->contains(fn ($context) => Str::startsWith($context, "{$tab}."))) {
                return $tab;
            }
        }

        return 'all';
    }
}
