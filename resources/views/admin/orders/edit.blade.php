@extends('layouts.admin')
@section('content')
<div class="main-content-wrap">
    <div class="tf-section-2 mb-30">
        <div class="wg-box">
            <div class="flex items-center justify-between gap10 flex-wrap">
                <h5>تعديل الطلب #{{ $order->id }}</h5>
                <div class="flex gap10">
                    <a href="{{ route('admin.orders') }}" class="tf-button style-1 w208">
                        <i class="icon-arrow-left"></i>
                        العودة للطلبات
                    </a>
                </div>
            </div>
            
            <form class="form-new-product form-style-1" method="POST" action="{{ route('admin.order.update', $order) }}">
                @csrf
                @method('PUT')
                
                <fieldset class="name">
                    <div class="body-title">حالة الطلب <span class="tf-color-1">*</span></div>
                    <select class="mb-10" name="status" required>
                        <option value="ordered" {{ $order->status == 'ordered' ? 'selected' : '' }}>مطلوب</option>
                        <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>قيد المعالجة</option>
                        <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>تم الشحن</option>
                        <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>تم التسليم</option>
                        <option value="canceled" {{ $order->status == 'canceled' ? 'selected' : '' }}>ملغي</option>
                    </select>
                </fieldset>

                <fieldset class="name">
                    <div class="body-title">طريقة الشحن <span class="tf-color-1">*</span></div>
                    <select class="mb-10" name="shipping_method_id" required>
                        @foreach($shippingMethods as $method)
                            <option value="{{ $method->id }}" {{ $order->shipping_method_id == $method->id ? 'selected' : '' }}>
                                {{ $method->name }} - {{ format_price($method->cost) }}
                            </option>
                        @endforeach
                    </select>
                </fieldset>

                <fieldset class="name">
                    <div class="body-title">تعليق التتبع</div>
                    <textarea class="mb-10" name="tracking_comment" placeholder="أضف تعليق حول حالة الطلب..."></textarea>
                </fieldset>

                <fieldset class="name">
                    <div class="body-title">موقع التتبع</div>
                    <input class="mb-10" type="text" name="tracking_location" placeholder="الموقع الحالي للطلب">
                </fieldset>

                <div class="bot">
                    <div></div>
                    <button class="tf-button w208" type="submit">حفظ التغييرات</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
