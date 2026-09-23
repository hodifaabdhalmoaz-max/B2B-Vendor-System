<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::all();
        return response()->json(['success' => true, 'data' => $brands]);
    }

    public function show($slug)
    {
        $brand = Brand::where('slug', $slug)->first();
        if (!$brand) {
            return response()->json(['success' => false, 'message' => 'Brand not found'], 404);
        }
        return response()->json(['success' => true, 'data' => $brand]);
    }

    public function products($slug)
    {
        $brand = Brand::where('slug', $slug)->first();
        if (!$brand) {
            return response()->json(['success' => false, 'message' => 'Brand not found'], 404);
        }
        $products = $brand->products()->paginate(12);
        return response()->json(['success' => true, 'data' => $products]);
    }
}
