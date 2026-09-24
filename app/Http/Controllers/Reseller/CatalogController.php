<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Services\ResellerCatalogService;
use App\Services\ResellerProductMediaService;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function __construct(private readonly ResellerCatalogService $catalog, private readonly ResellerProductMediaService $media) {}

    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:200'], 'category' => ['nullable', 'integer', 'min:1'],
            'in_stock' => ['nullable', 'boolean'], 'offers' => ['nullable', 'boolean'],
        ]);

        return view('reseller.catalog', $this->catalog->catalog($request->user()->id, $filters));
    }

    public function show(Request $request, int $product)
    {
        $product = $this->catalog->product($request->user()->id, $product);

        return view('reseller.product', ['product' => $product, 'images' => $this->media->images($product), 'description' => $this->media->description($product)]);
    }

    public function download(Request $request, int $product, int $image)
    {
        $product = $this->catalog->product($request->user()->id, $product);

        return response()->download($this->media->downloadPath($product, $image));
    }
}
