<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Services\ResellerPortalService;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function __construct(private readonly ResellerPortalService $portal) {}

    public function index(Request $request)
    {
        return view('reseller.wishlist', ['products' => $this->portal->wishlist($request->user()->id)]);
    }

    public function store(Request $request, int $product)
    {
        $this->portal->saveWishlist($request->user()->id, $product);

        return back()->with('status', __('Wishlist updated.'));
    }

    public function destroy(Request $request, int $product)
    {
        $this->portal->removeWishlist($request->user()->id, $product);

        return back()->with('status', __('Wishlist updated.'));
    }
}
