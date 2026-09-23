<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Models\UserActivity;
use App\Models\Wishlist;
use App\Models\Transaction;
use App\Models\Address;

class UserController extends Controller
{
    public function index()
    {
        return $this->dashboard();
    }

    public function dashboard()
    {
        $user = Auth::user();
        
        $totalOrders = Order::where('user_id', $user->id)->count();
        $pendingOrders = Order::where('user_id', $user->id)->where('status', 'ordered')->count();
        $completedOrders = Order::where('user_id', $user->id)->where('status', 'delivered')->count();
        
        // Assuming Wishlist is a model or we check the table
        $wishlistCount = Wishlist::where('user_id', $user->id)->count();
        
        $recentOrders = Order::where('user_id', $user->id)
            ->with(['transaction.paymentMethod'])
            ->latest()
            ->take(5)
            ->get();

        return view('user.dashboard', compact(
            'user', 
            'totalOrders', 
            'pendingOrders', 
            'completedOrders', 
            'wishlistCount', 
            'recentOrders'
        ));
    }

    public function profile()
    {
        $user = Auth::user();
        return view('user.profile', compact('user'));
    }

    public function profileUpdate(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255|unique:users,email,' . $user->id,
            'mobile'        => 'nullable|string|max:20',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $user->name   = $request->name;
        $user->email  = $request->email;
        $user->mobile = $request->mobile;

        if ($request->hasFile('profile_photo')) {
            // حذف الصورة القديمة إن وجدت
            if ($user->profile_photo && \Storage::disk('public')->exists('profile_photos/' . $user->profile_photo)) {
                \Storage::disk('public')->delete('profile_photos/' . $user->profile_photo);
            }

            $file     = $request->file('profile_photo');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('profile_photos', $filename, 'public');
            $user->profile_photo = $filename;
        }

        $user->save();

        return redirect()->route('user.profile')->with('success', 'تم تحديث الملف الشخصي بنجاح ✓');
    }

    public function orders()
    {
        $user = Auth::user();
        $orders = Order::where('user_id', $user->id)
            ->with(['transaction.paymentMethod'])
            ->latest()
            ->paginate(10);

        return view('user.orders', compact('orders'));
    }

    public function orderDetails($id)
    {
        $user = Auth::user();
        $order = Order::where('user_id', $user->id)
            ->with(['orderItems.product', 'transaction.paymentMethod', 'shippingMethod'])
            ->findOrFail($id);

        return view('user.order-details', compact('order'));
    }

    public function wishlist()
    {
        $user = Auth::user();
        $wishlistItems = Wishlist::where('user_id', $user->id)
            ->with(['product.category'])
            ->latest()
            ->get();
            
        return view('user.wishlist', compact('wishlistItems'));
    }

    public function addresses()
    {
        $user = Auth::user();
        $addresses = Address::where('user_id', $user->id)->latest()->get();
        return view('user.addresses', compact('addresses'));
    }

    public function addressAdd()
    {
        return view('user.address-add');
    }

    public function addressStore(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'phone'    => 'required|string|max:20',
            'locality' => 'required|string|max:255',
            'address'  => 'required|string|max:255',
            'city'     => 'required|string|max:100',
            'state'    => 'required|string|max:100',
            'country'  => 'required|string|max:100',
            'landmark' => 'nullable|string|max:255',
            'zip'      => 'required|string|max:10',
            'type'     => 'required|in:home,office,other',
        ]);

        $isDefault = $request->has('isdefault');
        if ($isDefault) {
            Address::where('user_id', Auth::id())->update(['isdefault' => false]);
        }

        Address::create([
            'user_id'   => Auth::id(),
            'name'      => $request->name,
            'phone'     => $request->phone,
            'locality'  => $request->locality,
            'address'   => $request->address,
            'city'      => $request->city,
            'state'     => $request->state,
            'country'   => $request->country,
            'landmark'  => $request->landmark,
            'zip'       => $request->zip,
            'type'      => $request->type,
            'isdefault' => $isDefault,
        ]);

        return redirect()->route('user.addresses')->with('success', 'تم إضافة العنوان بنجاح ✓');
    }

    public function addressEdit($id)
    {
        $address = Address::where('user_id', Auth::id())->findOrFail($id);
        return view('user.address-edit', compact('address'));
    }

    public function addressUpdate(Request $request, $id)
    {
        $address = Address::where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'name'     => 'required|string|max:255',
            'phone'    => 'required|string|max:20',
            'locality' => 'required|string|max:255',
            'address'  => 'required|string|max:255',
            'city'     => 'required|string|max:100',
            'state'    => 'required|string|max:100',
            'country'  => 'required|string|max:100',
            'landmark' => 'nullable|string|max:255',
            'zip'      => 'required|string|max:10',
            'type'     => 'required|in:home,office,other',
        ]);

        $isDefault = $request->has('isdefault');
        if ($isDefault) {
            Address::where('user_id', Auth::id())->update(['isdefault' => false]);
        }

        $address->update([
            'name'      => $request->name,
            'phone'     => $request->phone,
            'locality'  => $request->locality,
            'address'   => $request->address,
            'city'      => $request->city,
            'state'     => $request->state,
            'country'   => $request->country,
            'landmark'  => $request->landmark,
            'zip'       => $request->zip,
            'type'      => $request->type,
            'isdefault' => $isDefault,
        ]);

        return redirect()->route('user.addresses')->with('success', 'تم تحديث العنوان بنجاح ✓');
    }

    public function addressDelete($id)
    {
        $address = Address::where('user_id', Auth::id())->findOrFail($id);
        $address->delete();
        return redirect()->route('user.addresses')->with('success', 'تم حذف العنوان بنجاح ✓');
    }

    public function addressSetDefault($id)
    {
        $address = Address::where('user_id', Auth::id())->findOrFail($id);
        Address::where('user_id', Auth::id())->update(['isdefault' => false]);
        $address->update(['isdefault' => true]);
        return redirect()->route('user.addresses')->with('success', 'تم تعيين العنوان كافتراضي ✓');
    }
}
