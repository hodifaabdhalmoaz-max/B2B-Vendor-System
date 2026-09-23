<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Store a new review for a product.
     */
    public function store(Request $request, $product_id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'required|string|max:255',
            'comment' => 'required|string|min:5|max:1000',
        ]);

        Product::findOrFail($product_id);

        $existingReview = Review::where('product_id', $product_id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingReview) {
            return redirect()->back()->with('error', __('messages.review_already_added'));
        }

        Review::create([
            'product_id' => $product_id,
            'user_id' => Auth::id(),
            'rating' => $request->integer('rating'),
            'title' => $request->string('title')->toString(),
            'comment' => $request->string('comment')->toString(),
            'status' => true,
        ]);

        return redirect()->back()->with('success', __('messages.review_added'));
    }
}
