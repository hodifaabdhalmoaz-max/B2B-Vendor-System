<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MutateInventoryRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;

class InventoryController extends Controller
{
    public function __construct(private readonly InventoryService $inventoryService) {}

    public function store(MutateInventoryRequest $request, Product $product, ProductVariant $variant): RedirectResponse
    {
        abort_unless((int) $variant->product_id === (int) $product->id, 404);

        $data = $request->validated();
        $context = [
            'reference_type' => 'admin_manual',
            'reference_id' => (int) $request->user()->id,
            'actor_user_id' => (int) $request->user()->id,
        ];

        match ($data['operation']) {
            'stock_in' => $this->inventoryService->stockIn($variant, (int) $data['quantity'], $data['idempotency_key'], $context),
            'stock_out' => $this->inventoryService->stockOut($variant, (int) $data['quantity'], $data['idempotency_key'], $context),
            'adjustment' => $this->inventoryService->adjustStockTo($variant, (int) $data['quantity'], $data['idempotency_key'], $context),
        };

        return redirect()->route('admin.products.variants.index', $product)
            ->with('status', __('Variant inventory updated.'));
    }
}
