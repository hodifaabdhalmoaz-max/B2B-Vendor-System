<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Services\InventoryService;
use App\Services\ResellerReservationCartService;
use Illuminate\Http\Request;

class ReservationCartController extends Controller
{
    public function __construct(private readonly ResellerReservationCartService $cart) {}

    public function index(Request $request)
    {
        return view('reseller.cart', $this->cart->preview($request->user()->resellerProfile->id));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['product_variant_id' => ['required', 'integer', 'min:1', 'max:'.PHP_INT_MAX], 'quantity' => ['required', 'integer', 'min:1', 'max:'.InventoryService::MAX_QUANTITY]]);
        $this->cart->put($request->user()->resellerProfile->id, (int) $data['product_variant_id'], (int) $data['quantity'], true);

        return redirect()->route('reseller.cart.index')->with('status', __('Reservation cart updated.'));
    }

    public function update(Request $request, int $variant)
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:'.InventoryService::MAX_QUANTITY]]);
        $this->cart->put($request->user()->resellerProfile->id, $variant, (int) $data['quantity']);

        return redirect()->route('reseller.cart.index');
    }

    public function destroy(Request $request, int $variant)
    {
        $this->cart->remove($request->user()->resellerProfile->id, $variant);

        return redirect()->route('reseller.cart.index');
    }

    public function clear(Request $request)
    {
        $this->cart->clear($request->user()->resellerProfile->id);

        return redirect()->route('reseller.cart.index');
    }
}
