<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkStoreProductVariantsRequest;
use App\Http\Requests\Admin\StoreProductVariantRequest;
use App\Http\Requests\Admin\UpdateProductVariantRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\ProductVariantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductVariantController extends Controller
{
    public function __construct(private readonly ProductVariantService $productVariantService) {}

    public function index(Product $product): View
    {
        $product->load([
            'colors' => fn ($query) => $query->orderBy('order')->orderBy('name'),
            'sizes' => fn ($query) => $query->orderBy('order')->orderBy('name'),
            'variants.color',
            'variants.size',
            'variants.inventoryItem',
        ]);

        $variants = $product->variants->sortBy([
            ['color.name', 'asc'],
            ['size.name', 'asc'],
            ['sku', 'asc'],
        ]);

        return view('admin.product-variants.index', [
            'product' => $product,
            'variants' => $variants,
            'candidateCombinations' => $this->productVariantService->candidateCombinations($product),
        ]);
    }

    public function create(Product $product): RedirectResponse
    {
        return redirect()->route('admin.products.variants.index', $product);
    }

    public function store(StoreProductVariantRequest $request, Product $product): RedirectResponse
    {
        $this->productVariantService->createVariant($product, $request->validated());

        return redirect()
            ->route('admin.products.variants.index', $product)
            ->with('status', __('Product variant created.'));
    }

    public function bulkStore(BulkStoreProductVariantsRequest $request, Product $product): RedirectResponse
    {
        $created = $this->productVariantService->createSelectedCombinations($product, $request->validated('combinations'));

        return redirect()
            ->route('admin.products.variants.index', $product)
            ->with('status', __(':count product variants created.', ['count' => $created->count()]));
    }

    public function update(UpdateProductVariantRequest $request, Product $product, ProductVariant $variant): RedirectResponse
    {
        $this->productVariantService->updateVariant($product, $variant, $request->validated());

        return redirect()
            ->route('admin.products.variants.index', $product)
            ->with('status', __('Product variant updated.'));
    }

    public function activate(Product $product, ProductVariant $variant): RedirectResponse
    {
        $this->productVariantService->activate($product, $variant);

        return redirect()
            ->route('admin.products.variants.index', $product)
            ->with('status', __('Product variant activated.'));
    }

    public function deactivate(Product $product, ProductVariant $variant): RedirectResponse
    {
        $this->productVariantService->deactivate($product, $variant);

        return redirect()
            ->route('admin.products.variants.index', $product)
            ->with('status', __('Product variant deactivated.'));
    }

    public function destroy(Product $product, ProductVariant $variant): RedirectResponse
    {
        $this->productVariantService->deleteIfSafe($product, $variant);

        return redirect()
            ->route('admin.products.variants.index', $product)
            ->with('status', __('Product variant deleted.'));
    }
}
