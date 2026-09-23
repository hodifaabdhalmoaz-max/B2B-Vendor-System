@extends('layouts.admin')
@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>تفاصيل الطلب #{{ $order->id }}</h3>
            <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                <li>
                    <a href="{{route('admin.index')}}">
                        <div class="text-tiny">لوحة التحكم</div>
                    </a>
                </li>
                <li>
                    <i class="icon-chevron-right"></i>
                </li>
                <li>
                    <a href="{{route('admin.orders')}}">
                        <div class="text-tiny">الطلبات</div>
                    </a>
                </li>
                <li>
                    <i class="icon-chevron-right"></i>
                </li>
                <li>
                    <div class="text-tiny">تفاصيل الطلب</div>
                </li>
            </ul>
        </div>

        <div class="wg-box">
            <div class="flex items-center justify-between gap10 flex-wrap">
                <h5>تفاصيل الطلب #{{ $order->id }}</h5>
                <a href="{{ route('admin.orders') }}" class="tf-button style-1 w208">
                    العودة للطلبات
                </a>
            </div>

            @if(Session::has('status'))
            <p class="alert alert-success mt-3">
                {{Session::get('status')}}
            </p>
            @endif

            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">معلومات الطلب</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-borderless table-striped">
                                <tr>
                                    <th>رقم الطلب:</th>
                                    <td>#{{ $order->id }}</td>
                                </tr>
                                <tr>
                                    <th>تاريخ الطلب:</th>
                                    <td>{{ $order->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>حالة الطلب:</th>
                                    <td>
                                        @if($order->status == 'ordered')
                                            <span class="badge bg-warning">مطلوب</span>
                                        @elseif($order->status == 'delivered')
                                            <span class="badge bg-success">مسلم</span>
                                        @elseif($order->status == 'canceled')
                                            <span class="badge bg-danger">ملغي</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>طريقة الدفع:</th>
                                    <td>{{ $order->transaction ? ucfirst($order->transaction->mode) : 'غير متوفر' }}</td>
                                </tr>
                                <tr>
                                    <th>حالة الدفع:</th>
                                    <td>
                                        @if($order->transaction)
                                            @if($order->transaction->status == 'pending')
                                                <span class="badge bg-warning">معلق</span>
                                            @elseif($order->transaction->status == 'approved')
                                                <span class="badge bg-success">موافق عليه</span>
                                            @elseif($order->transaction->status == 'declined')
                                                <span class="badge bg-danger">مرفوض</span>
                                            @elseif($order->transaction->status == 'refunded')
                                                <span class="badge bg-info">مسترد</span>
                                            @endif
                                        @else
                                            <span class="badge bg-secondary">غير متوفر</span>
                                        @endif
                                    </td>
                                </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">معلومات العميل</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-borderless table-striped">
                                <tr>
                                    <th>الاسم:</th>
                                    <td>{{ $order->name }}</td>
                                </tr>
                                <tr>
                                    <th>الهاتف:</th>
                                    <td>{{ $order->phone }}</td>
                                </tr>
                                <tr>
                                    <th>العنوان:</th>
                                    <td>{{ $order->address }}</td>
                                </tr>
                                <tr>
                                    <th>المدينة:</th>
                                    <td>{{ $order->city }}, {{ $order->state }}, {{ $order->country }}</td>
                                </tr>
                                <tr>
                                    <th>الرمز البريدي:</th>
                                    <td>{{ $order->zip }}</td>
                                </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">عناصر الطلب</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>المنتج</th>
                                    <th>السعر</th>
                                    <th>الكمية</th>
                                    <th>المجموع الفرعي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->orderItems as $item)
                                <tr>
                                    <td>
                                        @if($item->product)
                                            <div class="d-flex align-items-center">
                                                @if($item->product->image)
                                                <img src="{{ asset('uploads/products/thumbnails/' . $item->product->image) }}"
                                                    alt="{{ $item->product->name }}" class="me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                                @endif
                                                <div>
                                                    <h6 class="mb-0">{{ $item->product->name }}</h6>
                                                    <small class="text-muted">SKU: {{ $item->product->SKU }}</small>
                                                    <x-order-item-options :options="$item->options" compact />
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">المنتج غير متوفر</span>
                                        @endif
                                    </td>
                                    <td>{{ format_price($item->price) }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ format_price($item->price * $item->quantity) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>المجموع الفرعي:</strong></td>
                                    <td>{{ format_price($order->subtotal) }}</td>
                                </tr>
                                @if($order->discount > 0)
                                <tr>
                                    <td colspan="3" class="text-end"><strong>الخصم:</strong></td>
                                    <td>-{{ format_price($order->discount) }}</td>
                                </tr>
                                @endif

                                <tr>
                                    <td colspan="3" class="text-end"><strong>الإجمالي:</strong></td>
                                    <td>{{ format_price($order->total) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">تحديث حالة الطلب</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.order.update.status', ['id' => $order->id]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row align-items-end">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">حالة الطلب</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="ordered" {{ $order->status == 'ordered' ? 'selected' : '' }}>مطلوب</option>
                                        <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>مسلم</option>
                                        <option value="canceled" {{ $order->status == 'canceled' ? 'selected' : '' }}>ملغي</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <button type="submit" class="tf-button style-1">تحديث الحالة</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="divider"></div>
            <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
            </div>
        </div>
    </div>
</div>
@endsection
