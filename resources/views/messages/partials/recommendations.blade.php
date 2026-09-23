<section class="message-center-recommendations" aria-label="أشياء قد تعجبك">
    <div class="message-center-title">
        <span aria-hidden="true"></span>
        <h2>أشياء قد تعجبك</h2>
        <span aria-hidden="true"></span>
    </div>

    <div class="message-center-product-grid">
        @forelse($recommendations as $product)
            <x-shop.market-product-card :product="$product" />
        @empty
            <div class="message-center-muted">لم يتم تخصيص منتجات لهذا القسم بعد.</div>
        @endforelse
    </div>
</section>
