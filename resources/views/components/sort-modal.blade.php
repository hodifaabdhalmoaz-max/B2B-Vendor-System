{{--
  Sort Modal Component — reusable across category-shop & search-results.

  Usage: <x-sort-modal :action="route('shop.category', $category->slug)" />
  
  Props:
    - action       : form action URL
    - extraInputs  : array of [name => value] for hidden fields to preserve
--}}

@props(['action', 'extraInputs' => []])

<div class="modal fade custom-modal" id="sortModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h5 class="modal-title m-0">ترتيب المنتجات</h5>
        </div>
        
        <form action="{{ $action }}" method="GET" id="sortForm">
          {{-- Preserve current filter/tab state --}}
          @foreach($extraInputs as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
          @endforeach
          <input type="hidden" name="brands" value="{{ request('brands') }}">
          <input type="hidden" name="min_price" value="{{ request('min_price') }}">
          <input type="hidden" name="max_price" value="{{ request('max_price') }}">
          @if(is_array(request('colors')))
            @foreach(request('colors', []) as $colorId)
              <input type="hidden" name="colors[]" value="{{ $colorId }}">
            @endforeach
          @endif
          @if(is_array(request('sizes')))
            @foreach(request('sizes', []) as $sizeId)
              <input type="hidden" name="sizes[]" value="{{ $sizeId }}">
            @endforeach
          @endif

          {{-- Direction toggle --}}
          <div class="sort-directions">
            <button type="button" 
                    class="sort-dir-btn {{ request('order_dir', 'desc') === 'asc' ? 'active' : '' }}" 
                    onclick="setSortDir('asc', this)">ترتيب تصاعدي</button>
            <button type="button" 
                    class="sort-dir-btn {{ request('order_dir', 'desc') === 'desc' ? 'active' : '' }}" 
                    onclick="setSortDir('desc', this)">ترتيب تنازلي</button>
          </div>
          
          <input type="hidden" name="order_dir" id="orderDirInput" value="{{ request('order_dir', 'desc') }}">

          <label class="custom-radio">
            <span>الأكثر مشاهدة</span>
            <input type="radio" name="order" value="viewed" {{ request('order') === 'viewed' ? 'checked' : '' }}>
          </label>
          <label class="custom-radio">
            <span>الأكثر مبيعاً</span>
            <input type="radio" name="order" value="bestselling" {{ request('order') === 'bestselling' ? 'checked' : '' }}>
          </label>
          <label class="custom-radio">
            <span>السعر</span>
            <input type="radio" name="order" value="price" {{ request('order') === 'price' ? 'checked' : '' }}>
          </label>
          <label class="custom-radio">
            <span>الاسم</span>
            <input type="radio" name="order" value="name" {{ request('order') === 'name' ? 'checked' : '' }}>
          </label>
          <label class="custom-radio border-0">
            <span>وصل حديثاً</span>
            <input type="radio" name="order" value="newest" {{ request('order', 'newest') === 'newest' ? 'checked' : '' }}>
          </label>

          <div class="d-flex gap-2 mt-4">
            <button type="button" class="btn btn-light flex-grow-1" data-bs-dismiss="modal">إلغاء</button>
            <button type="submit" class="btn text-white flex-grow-1" style="background: linear-gradient(135deg, #f0c14b 0%, #d4a853 100%); border: none;">تطبيق</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  function setSortDir(dir, el) {
    document.querySelectorAll('.sort-dir-btn').forEach(btn => btn.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('orderDirInput').value = dir;
  }
</script>
