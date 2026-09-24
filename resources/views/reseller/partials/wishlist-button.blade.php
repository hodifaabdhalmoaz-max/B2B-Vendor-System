<form method="POST" action="{{ route($product->is_wishlisted ? 'reseller.wishlist.destroy' : 'reseller.wishlist.store', $product->id) }}">
    @csrf @if($product->is_wishlisted)@method('DELETE')@endif
    <button class="btn btn-outline-secondary w-100" type="submit">{{ $product->is_wishlisted ? __('Remove from wishlist') : __('Add to wishlist') }}</button>
</form>
