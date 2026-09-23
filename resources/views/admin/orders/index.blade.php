@extends('layouts.admin')
@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>جميع الطلبات</h3>
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
                    <div class="text-tiny">الطلبات</div>
                </li>
            </ul>
        </div>

        <div class="wg-box">
            <div class="flex items-center justify-between gap10 flex-wrap">
                <div class="wg-filter flex-grow">
                    <form class="form-search" method="GET" action="{{ route('admin.orders') }}">
                        <fieldset class="name">
                            <input type="text" placeholder="البحث برقم الطلب أو اسم العميل..." class="" name="search"
                                tabindex="2" value="{{ request('search') }}" aria-required="true">
                        </fieldset>
                        <div class="button-submit">
                            <button class="" type="submit"><i class="icon-search"></i></button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="table-responsive">
                @if(Session::has('status'))
                <p class="alert alert-success">{{Session::get('status')}}</p>
                @endif
                @if(Session::has('error'))
                <p class="alert alert-danger">{{Session::get('error')}}</p>
                @endif
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>رقم الطلب</th>
                            <th>العميل</th>
                            <th>الهاتف</th>
                            <th>المجموع الفرعي</th>

                            <th>الإجمالي</th>
                            <th>الحالة</th>
                            <th>تاريخ الطلب</th>
                            <th>العناصر</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td>{{$order->id}}</td>
                            <td>{{$order->name}}</td>
                            <td>{{$order->phone}}</td>
                            <td>{{ format_price($order->subtotal) }}</td>

                            <td>{{ format_price($order->total) }}</td>
                            <td>
                                @if($order->status == 'ordered')
                                    <span class="badge bg-warning">مطلوب</span>
                                @elseif($order->status == 'processing')
                                    <span class="badge bg-info">قيد المعالجة</span>
                                @elseif($order->status == 'shipped')
                                    <span class="badge bg-primary">تم الشحن</span>
                                @elseif($order->status == 'delivered')
                                    <span class="badge bg-success">مسلم</span>
                                @elseif($order->status == 'canceled')
                                    <span class="badge bg-danger">ملغي</span>
                                @endif
                            </td>
                            <td>{{$order->created_at->format('Y-m-d H:i:s')}}</td>
                            <td>{{$order->orderItems->count()}}</td>
                            <td>
                                <div class="list-icon-function">
                                    <a href="{{ route('admin.order.details', $order) }}" title="التفاصيل">
                                        <div class="item eye">
                                            <i data-lucide="eye" style="width: 16px; height: 16px;"></i>
                                        </div>
                                    </a>
                                    <a href="{{ route('admin.order.tracking', $order->id) }}" title="تتبع الطلب">
                                        <div class="item edit">
                                            <i data-lucide="map-pin" style="width: 16px; height: 16px;"></i>
                                        </div>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center">لا توجد طلبات</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="divider"></div>
            <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                {{$orders->links('pagination::bootstrap-5')}}
            </div>
        </div>
    </div>
</div>
@endsection
