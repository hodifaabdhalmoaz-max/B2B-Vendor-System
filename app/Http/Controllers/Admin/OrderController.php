<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderTracking;
use App\Models\PaymentMethod;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Models\UserActivity;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct()
    {
        // Middleware is handled at route level
    }

    /**
     * Display a listing of the orders.
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'shippingMethod', 'transaction.paymentMethod']);

        // Apply status filter
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        // Apply date range filter
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Apply search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $query->latest()->paginate(10);

        // Get order status counts efficiently with single query
        $statusCountsRaw = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $statusCounts = [
            'all' => Order::count(),
            'ordered' => $statusCountsRaw['ordered'] ?? 0,
            'processing' => $statusCountsRaw['processing'] ?? 0,
            'shipped' => $statusCountsRaw['shipped'] ?? 0,
            'delivered' => $statusCountsRaw['delivered'] ?? 0,
            'canceled' => $statusCountsRaw['canceled'] ?? 0,
        ];

        return view('admin.orders.index', compact('orders', 'statusCounts'));
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        $order->load(['orderItems.product', 'user', 'shippingMethod', 'shippingAddress', 'transaction.paymentMethod', 'tracking']);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified order.
     */
    public function edit(Order $order)
    {
        $order->load(['orderItems.product', 'user', 'shippingMethod', 'shippingAddress', 'transaction.paymentMethod', 'tracking']);

        $shippingMethods = ShippingMethod::active()->ordered()->get();
        $paymentMethods = PaymentMethod::active()->ordered()->get();

        return view('admin.orders.edit', compact('order', 'shippingMethods', 'paymentMethods'));
    }

    /**
     * Update the specified order in storage.
     */
    public function update(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:ordered,processing,shipped,delivered,canceled',
            'shipping_method_id' => 'required|exists:shipping_methods,id',
        ]);

        // Update order status
        $oldStatus = $order->status;
        $order->status = $request->status;
        $order->shipping_method_id = $request->shipping_method_id;

        // Update delivered or cancelled date
        if ($request->status == 'delivered' && $oldStatus != 'delivered') {
            $order->delivered_date = Carbon::now();
        } elseif ($request->status == 'canceled' && $oldStatus != 'canceled') {
            $order->canceled_date = Carbon::now();
        }

        $order->save();

        // Add tracking information
        if ($request->has('tracking_comment') && ! empty($request->tracking_comment)) {
            OrderTracking::create([
                'order_id' => $order->id,
                'status' => $request->status,
                'comment' => $request->tracking_comment,
                'location' => $request->tracking_location,
                'updated_by' => Auth::id(),
            ]);
        }

        // Log order update
        UserActivity::log(Auth::id(), 'order_update', [
            'order_id' => $order->id,
            'old_status' => $oldStatus,
            'new_status' => $request->status,
        ]);

        return redirect()->route('admin.orders.show', $order)->with('success', __('messages.order_updated'));
    }

    /**
     * Update order status only.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:ordered,processing,shipped,delivered,canceled',
        ]);

        $order = Order::findOrFail($id);
        $oldStatus = $order->status;
        $order->status = $request->status;

        // Update delivered or cancelled date
        if ($request->status == 'delivered' && $oldStatus != 'delivered') {
            $order->delivered_date = Carbon::now();
        } elseif ($request->status == 'canceled' && $oldStatus != 'canceled') {
            $order->canceled_date = Carbon::now();
        }

        $order->save();

        // Log order update
        UserActivity::log(Auth::id(), 'order_status_update', [
            'order_id' => $order->id,
            'old_status' => $oldStatus,
            'new_status' => $request->status,
        ]);

        return redirect()->route('admin.order.details', $order)->with('status', 'تم تحديث حالة الطلب بنجاح');
    }

    /**
     * Generate invoice for the specified order.
     */
    public function invoice(Order $order)
    {
        $order->load(['orderItems.product', 'user', 'shippingMethod', 'shippingAddress', 'transaction.paymentMethod']);

        $pdf = PDF::loadView('admin.orders.invoice', compact('order'));

        return $pdf->download('invoice-'.$order->id.'.pdf');
    }

    /**
     * Display order tracking information.
     */
    public function tracking(Order $order)
    {
        $order->load(['tracking.updatedBy']);

        return view('admin.orders.tracking', compact('order'));
    }

    /**
     * Add tracking information to the specified order.
     */
    public function addTracking(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:ordered,processing,shipped,delivered,canceled',
            'comment' => 'required|string|max:500',
            'location' => 'nullable|string|max:255',
        ]);

        // Add tracking information
        OrderTracking::create([
            'order_id' => $order->id,
            'status' => $request->status,
            'comment' => $request->comment,
            'location' => $request->location,
            'updated_by' => Auth::id(),
        ]);

        // Update order status if needed
        if ($order->status != $request->status) {
            $oldStatus = $order->status;
            $order->status = $request->status;

            // Update delivered or cancelled date
            if ($request->status == 'delivered' && $oldStatus != 'delivered') {
                $order->delivered_date = Carbon::now();
            } elseif ($request->status == 'canceled' && $oldStatus != 'canceled') {
                $order->canceled_date = Carbon::now();
            }

            $order->save();

            // Log order update
            UserActivity::log(Auth::id(), 'order_update', [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
            ]);
        }

        return redirect()->route('admin.order.tracking', $order)->with('success', 'تم إضافة معلومات التتبع بنجاح');
    }

    /**
     * Display order statistics.
     */
    public function statistics(Request $request)
    {
        // Set default date range to last 30 days
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();

        // Get total orders and sales
        $totalOrders = Order::whereBetween('created_at', [$startDate, $endDate])->count();
        $totalSales = Order::where('status', 'delivered')->whereBetween('created_at', [$startDate, $endDate])->sum('total');

        // Get orders by status
        $ordersByStatus = Order::whereBetween('created_at', [$startDate, $endDate])
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        // Get sales by day
        $salesByDay = Order::where('status', 'delivered')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as total_sales'),
                DB::raw('COUNT(*) as order_count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get top customers
        $topCustomers = Order::whereBetween('created_at', [$startDate, $endDate])
            ->select('user_id', DB::raw('COUNT(*) as order_count'), DB::raw('SUM(total) as total_spent'))
            ->groupBy('user_id')
            ->orderByDesc('total_spent')
            ->take(10)
            ->get()
            ->map(function ($item) {
                $user = User::find($item->user_id);

                return [
                    'user' => $user,
                    'order_count' => $item->order_count,
                    'total_spent' => $item->total_spent,
                ];
            });

        return view('admin.orders.statistics', compact(
            'startDate',
            'endDate',
            'totalOrders',
            'totalSales',
            'ordersByStatus',
            'salesByDay',
            'topCustomers'
        ));
    }
}
