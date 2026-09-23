<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Color;
use App\Models\Size;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\ProductAttribute;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display a listing of the products.
     */
    public function index()
    {
        $products = Product::with(['category', 'brand'])
            ->latest()
            ->paginate(10);
        
        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $colors = Color::active()->ordered()->get();
        $sizes = Size::active()->ordered()->get();
        $attributes = Attribute::with('values')->get();
        
        return view('admin.products.create', compact('categories', 'brands', 'colors', 'sizes', 'attributes'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products',
            'short_description' => 'required|string',
            'description' => 'required|string',
            'regular_price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'SKU' => 'required|string|max:100|unique:products',
            'stock_status' => 'required|in:instock,outofstock',
            'featured' => 'boolean',
            'quantity' => 'required|integer|min:0',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
        ]);
        
        $product = new Product();
        $product->name = $request->name;
        $product->slug = $request->slug;
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured ? true : false;
        $product->quantity = $request->quantity;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;
        
        // Handle main image
        if ($request->hasFile('image')) {
            $imageName = time() . '_' . $request->image->getClientOriginalName();
            $request->image->storeAs('public/products', $imageName);
            $product->image = $imageName;
            
            // Create thumbnail
            $thumbnail = Image::make($request->image->getRealPath())->resize(300, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $thumbnailPath = 'public/products/thumbnails/' . $imageName;
            Storage::put($thumbnailPath, $thumbnail->encode());
        }
        
        // Handle additional images
        if ($request->hasFile('images')) {
            $imagesArray = [];
            foreach ($request->file('images') as $image) {
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->storeAs('public/products', $imageName);
                $imagesArray[] = $imageName;
                
                // Create thumbnail
                $thumbnail = Image::make($image->getRealPath())->resize(300, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $thumbnailPath = 'public/products/thumbnails/' . $imageName;
                Storage::put($thumbnailPath, $thumbnail->encode());
            }
            $product->images = $imagesArray;
        }
        
        $product->save();
        
        // Handle colors
        if ($request->has('colors')) {
            foreach ($request->colors as $colorId => $data) {
                if (isset($data['selected']) && $data['selected']) {
                    $product->colors()->attach($colorId, [
                        'quantity' => $data['quantity'] ?? 0,
                        'price_adjustment' => $data['price_adjustment'] ?? 0,
                    ]);
                    
                    // Handle color image
                    if (isset($data['image']) && $data['image']) {
                        $imageName = time() . '_color_' . $colorId . '_' . $data['image']->getClientOriginalName();
                        $data['image']->storeAs('public/products/colors', $imageName);
                        
                        $product->colors()->updateExistingPivot($colorId, [
                            'image' => $imageName
                        ]);
                    }
                }
            }
        }
        
        // Handle sizes
        if ($request->has('sizes')) {
            foreach ($request->sizes as $sizeId => $data) {
                if (isset($data['selected']) && $data['selected']) {
                    $product->sizes()->attach($sizeId, [
                        'quantity' => $data['quantity'] ?? 0,
                        'price_adjustment' => $data['price_adjustment'] ?? 0,
                    ]);
                }
            }
        }
        
        // Handle attributes
        if ($request->has('attributes')) {
            foreach ($request->attributes as $attributeId => $values) {
                foreach ($values as $valueId => $data) {
                    if (isset($data['selected']) && $data['selected']) {
                        ProductAttribute::create([
                            'product_id' => $product->id,
                            'attribute_id' => $attributeId,
                            'attribute_value_id' => $valueId,
                            'price_adjustment' => $data['price_adjustment'] ?? 0,
                            'quantity' => $data['quantity'] ?? 0,
                        ]);
                    }
                }
            }
        }
        
        return redirect()->route('admin.products.index')->with('success', __('messages.product_created'));
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        $product->load(['category', 'brand', 'colors', 'sizes', 'productAttributes.attribute', 'productAttributes.attributeValue']);
        
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $product->load(['colors', 'sizes', 'productAttributes.attribute', 'productAttributes.attributeValue']);
        
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $colors = Color::active()->ordered()->get();
        $sizes = Size::active()->ordered()->get();
        $attributes = Attribute::with('values')->get();
        
        return view('admin.products.edit', compact('product', 'categories', 'brands', 'colors', 'sizes', 'attributes'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug,' . $product->id,
            'short_description' => 'required|string',
            'description' => 'required|string',
            'regular_price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'SKU' => 'required|string|max:100|unique:products,SKU,' . $product->id,
            'stock_status' => 'required|in:instock,outofstock',
            'featured' => 'boolean',
            'quantity' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
        ]);
        
        $product->name = $request->name;
        $product->slug = $request->slug;
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured ? true : false;
        $product->quantity = $request->quantity;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;
        
        // Handle main image
        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image && Storage::exists('public/products/' . $product->image)) {
                Storage::delete('public/products/' . $product->image);
                Storage::delete('public/products/thumbnails/' . $product->image);
            }
            
            $imageName = time() . '_' . $request->image->getClientOriginalName();
            $request->image->storeAs('public/products', $imageName);
            $product->image = $imageName;
            
            // Create thumbnail
            $thumbnail = Image::make($request->image->getRealPath())->resize(300, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $thumbnailPath = 'public/products/thumbnails/' . $imageName;
            Storage::put($thumbnailPath, $thumbnail->encode());
        }
        
        // Handle additional images
        if ($request->hasFile('images')) {
            // Delete old images
            if ($product->images) {
                foreach ($product->images as $image) {
                    if (Storage::exists('public/products/' . $image)) {
                        Storage::delete('public/products/' . $image);
                        Storage::delete('public/products/thumbnails/' . $image);
                    }
                }
            }
            
            $imagesArray = [];
            foreach ($request->file('images') as $image) {
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->storeAs('public/products', $imageName);
                $imagesArray[] = $imageName;
                
                // Create thumbnail
                $thumbnail = Image::make($image->getRealPath())->resize(300, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $thumbnailPath = 'public/products/thumbnails/' . $imageName;
                Storage::put($thumbnailPath, $thumbnail->encode());
            }
            $product->images = $imagesArray;
        }
        
        $product->save();
        
        // Handle colors
        $product->colors()->detach();
        if ($request->has('colors')) {
            foreach ($request->colors as $colorId => $data) {
                if (isset($data['selected']) && $data['selected']) {
                    $product->colors()->attach($colorId, [
                        'quantity' => $data['quantity'] ?? 0,
                        'price_adjustment' => $data['price_adjustment'] ?? 0,
                    ]);
                    
                    // Handle color image
                    if (isset($data['image']) && $data['image']) {
                        $imageName = time() . '_color_' . $colorId . '_' . $data['image']->getClientOriginalName();
                        $data['image']->storeAs('public/products/colors', $imageName);
                        
                        $product->colors()->updateExistingPivot($colorId, [
                            'image' => $imageName
                        ]);
                    }
                }
            }
        }
        
        // Handle sizes
        $product->sizes()->detach();
        if ($request->has('sizes')) {
            foreach ($request->sizes as $sizeId => $data) {
                if (isset($data['selected']) && $data['selected']) {
                    $product->sizes()->attach($sizeId, [
                        'quantity' => $data['quantity'] ?? 0,
                        'price_adjustment' => $data['price_adjustment'] ?? 0,
                    ]);
                }
            }
        }
        
        // Handle attributes
        ProductAttribute::where('product_id', $product->id)->delete();
        if ($request->has('attributes')) {
            foreach ($request->attributes as $attributeId => $values) {
                foreach ($values as $valueId => $data) {
                    if (isset($data['selected']) && $data['selected']) {
                        ProductAttribute::create([
                            'product_id' => $product->id,
                            'attribute_id' => $attributeId,
                            'attribute_value_id' => $valueId,
                            'price_adjustment' => $data['price_adjustment'] ?? 0,
                            'quantity' => $data['quantity'] ?? 0,
                        ]);
                    }
                }
            }
        }
        
        return redirect()->route('admin.products.index')->with('success', __('messages.product_updated'));
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        // Delete main image
        if ($product->image && Storage::exists('public/products/' . $product->image)) {
            Storage::delete('public/products/' . $product->image);
            Storage::delete('public/products/thumbnails/' . $product->image);
        }
        
        // Delete additional images
        if ($product->images) {
            foreach ($product->images as $image) {
                if (Storage::exists('public/products/' . $image)) {
                    Storage::delete('public/products/' . $image);
                    Storage::delete('public/products/thumbnails/' . $image);
                }
            }
        }
        
        // Delete color images
        foreach ($product->colors as $color) {
            $pivotData = $color->pivot;
            if ($pivotData->image && Storage::exists('public/products/colors/' . $pivotData->image)) {
                Storage::delete('public/products/colors/' . $pivotData->image);
            }
        }
        
        // Delete relationships
        $product->colors()->detach();
        $product->sizes()->detach();
        ProductAttribute::where('product_id', $product->id)->delete();
        
        // Delete product
        $product->delete();
        
        return redirect()->route('admin.products.index')->with('success', __('messages.product_deleted'));
    }
}
