@if($orders->isEmpty())
    @include('messages.partials.empty-state', ['label' => 'لا توجد طلبات حالية'])
@else
    <section class="message-center-list" aria-label="طلبات العميل">
        @foreach($orders as $order)
            <article class="message-center-order">
                <div>
                    <span class="message-center-order__number">طلب #{{ $order->id }}</span>
                    <span class="message-center-order__date">{{ $order->created_at?->format('Y/m/d') }}</span>
                </div>
                <div>
                    <span class="message-center-order__status">{{ $order->status }}</span>
                    <strong>{{ number_format((float) $order->total, 2) }} ر.ي</strong>
                </div>
                <small>{{ $order->order_items_count }} منتج</small>

                @auth
                    <a href="{{ route('user.order.details', $order->id) }}" aria-label="تفاصيل الطلب #{{ $order->id }}"></a>
                @endauth
            </article>
        @endforeach
    </section>
@endif
