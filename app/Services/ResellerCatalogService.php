<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\ResellerPortalRepository;
use App\Support\DecimalMoney;

class ResellerCatalogService
{
    public function __construct(private readonly ResellerPortalRepository $repository) {}

    public function catalog(int $userId, array $filters): array
    {
        $products = $this->repository->catalog($userId, $filters)->latest('products.created_at')->orderByDesc('products.id')->paginate(12)->withQueryString();
        $products->getCollection()->each(fn ($product) => $this->present($product));

        return ['products' => $products, 'categories' => $this->repository->categories(), 'filters' => $filters];
    }

    public function product(int $userId, int $id): Product
    {
        return $this->present($this->repository->products($userId)->with('colorImages.color')->findOrFail($id));
    }

    public function present(Product $product): Product
    {
        $prices = [];
        $available = false;
        foreach ($product->variants as $variant) {
            $variant->setRelation('product', $product);
            $prices[] = DecimalMoney::toCents($variant->effectivePrice());
            $available = $available || ($variant->inventoryItem?->available_quantity ?? 0) > 0;
        }
        $product->setAttribute('portal_price_min', DecimalMoney::formatCents(min($prices)));
        $product->setAttribute('portal_price_max', DecimalMoney::formatCents(max($prices)));
        $product->setAttribute('portal_available', $available);

        return $product;
    }
}
