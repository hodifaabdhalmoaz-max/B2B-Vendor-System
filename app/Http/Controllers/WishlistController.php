<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Surfsidemedia\Shoppingcart\Facades\Cart;
use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('message', 'يرجى تسجيل الدخول لعرض المفضلة');
        }

        $wishlistItems = Wishlist::with(['product.category'])
            ->where('user_id', Auth::id())
            ->get();

        return view('wishlist', compact('wishlistItems'));
    }
    public function add_to_wishlist(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1|max:100',
        ]);

        $product = Product::findOrFail($request->id);
        $price = ($product->sale_price > 0 && $product->sale_price < $product->regular_price) 
            ? $product->sale_price 
            : $product->regular_price;

        Cart::instance('wishlist')->add($product->id, $product->name, (int) $request->quantity, $price)->associate('App\Models\Product');
       
        if(Auth::check()) {
            Wishlist::updateOrCreate(
                ['user_id' => Auth::id(), 'product_id' => $product->id]
            );
        }
       
        return redirect()->back();
    }
    public function remove_item($rowId)
    {
       $item = Cart::instance('wishlist')->get($rowId);
       if(Auth::check() && $item) {
           Wishlist::where('user_id', Auth::id())->where('product_id', $item->id)->delete();
       }
       
       Cart::instance('wishlist')->remove($rowId);
       return redirect()->back();
    }
    
    public function remove_item_by_id($id)
    {
        if(Auth::check()) {
            Wishlist::where('user_id', Auth::id())->where('product_id', $id)->delete();
        }
        
        $item = Cart::instance('wishlist')->content()->where('id', $id)->first();
        if($item) {
            Cart::instance('wishlist')->remove($item->rowId);
        }
        
        return redirect()->back();
    }
    public function empty_wishlist()
    {
        if (Auth::check()) {
            Wishlist::where('user_id', Auth::id())->delete();
        }
        Cart::instance('wishlist')->destroy();
        return redirect()->back();
    }
    public function move_to_cart($rowId)
    {
       $item = Cart::instance('wishlist')->get($rowId);
       Cart::instance('wishlist')->remove($rowId);
       Cart::instance('cart')->add($item->id,$item->name,$item->qty,$item->price)->associate('App\Models\Product');
       return redirect()->back();
    }
}
