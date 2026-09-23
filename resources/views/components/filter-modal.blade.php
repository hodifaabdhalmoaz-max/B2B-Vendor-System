{{--
  Filter Modal Component — reusable across category-shop & search-results.

  Usage: <x-filter-modal :action="route('shop.category', $category->slug)"
                          :brands="$brands" :colors="$colors" :sizes="$sizes" />

  Props:
    - action        : form action URL
    - resetUrl      : URL to clear all filters
    - brands        : Collection of brands
    - colors        : Collection of colors (dynamic from DB)
    - sizes         : Collection of sizes (dynamic from DB)
    - extraInputs   : array of [name => value] for hidden fields
--}}

@props([
    'action',
    'resetUrl',
    'brands'      => collect(),
    'colors'      => collect(),
    'sizes'       => collect(),
    'extraInputs' => [],
])

@php
// Helper: determine contrast color for readability on colored backgrounds
if (!function_exists('contrastColor')) {
    function contrastColor(string $hex): string {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $luminance = ($r * 299 + $g * 587 + $b * 114) / 1000;
        return $luminance > 128 ? '#333' : '#fff';
    }
}

$activeCount = 0;
if (request('brands'))    $activeCount++;
if (request('min_price')) $activeCount++;
if (request('colors'))    $activeCount += count((array)request('colors'));
if (request('sizes'))     $activeCount += count((array)request('sizes'));
@endphp

<div class="modal fade custom-modal" id="filterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <a href="{{ $resetUrl }}" class="text-muted text-decoration-none small">حذف الكل</a>
          <h5 class="modal-title m-0">فرز المنتجات</h5>
          <div style="width: 50px;"></div>
        </div>
        
        <p class="text-end text-muted small mb-4">
          @if($activeCount > 0)
            <span class="badge bg-warning text-dark">{{ $activeCount }}</span> فلتر نشط
          @else
            خيارات الفرز التي تم تطبيقها
          @endif
        </p>
        
        <form action="{{ $action }}" method="GET" id="filterForm">
          {{-- Preserve sort & tab state --}}
          @foreach($extraInputs as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
          @endforeach
          <input type="hidden" name="order" value="{{ request('order') }}">
          <input type="hidden" name="order_dir" value="{{ request('order_dir', 'desc') }}">
          
          {{-- ═══════════════ Price Range ═══════════════ --}}
          <div class="mb-4">
            <label class="fw-bold mb-2">السعر</label>
            <div class="bg-light p-3 rounded-3">
              <div class="dual-range-wrapper position-relative" style="height: 40px;">
                <div class="dual-range-track"></div>
                <input type="range" class="dual-range dual-range-min" min="0" max="500000" step="500"
                       id="priceRangeMin" name="min_price" value="{{ request('min_price', 0) }}">
                <input type="range" class="dual-range dual-range-max" min="0" max="500000" step="500"
                       id="priceRangeMax" name="max_price" value="{{ request('max_price', 500000) }}">
              </div>
              <div class="text-center mt-2 fw-bold" style="font-size: 0.9rem;">
                السعر: <span id="priceVal">{{ request('min_price', 0) }} — {{ request('max_price', 500000) }}</span> ريال جديد
              </div>
            </div>
          </div>
          
          {{-- ═══════════════ Brand ═══════════════ --}}
          @if($brands->count() > 0)
          <div class="mb-4">
            <label class="fw-bold mb-2">الماركة</label>
            <select name="brands" class="form-select form-select-lg" style="border-radius: 8px; padding-left: 2.5rem; background-position: left 0.75rem center;">
              <option value="">اختار الماركة</option>
              @foreach($brands as $brand)
                <option value="{{ $brand->id }}" {{ request('brands') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
              @endforeach
            </select>
          </div>
          @endif
          
          {{-- ═══════════════ Colors (dynamic from DB) ═══════════════ --}}
          @if($colors->count() > 0)
          <div class="mb-4">
            <label class="fw-bold mb-2">اللون</label>
            <div class="d-flex flex-wrap gap-2">
              @foreach($colors as $color)
              <label class="color-filter-item position-relative" style="cursor: pointer;" title="{{ $color->name }}">
                <input type="checkbox" name="colors[]" value="{{ $color->id }}" class="d-none color-checkbox"
                       {{ in_array($color->id, (array)request('colors', [])) ? 'checked' : '' }}>
                <span class="color-swatch d-inline-flex align-items-center justify-content-center rounded-circle"
                      style="width: 36px; height: 36px; background-color: {{ $color->hex_code ?? '#ccc' }};
                             border: 3px solid {{ in_array($color->id, (array)request('colors', [])) ? '#d4a853' : 'transparent' }};
                             box-shadow: 0 0 0 1px #dee2e6; transition: all 0.2s;">
                  @if(in_array($color->id, (array)request('colors', [])))
                    <i class="bi bi-check2" style="color: {{ contrastColor($color->hex_code ?? '#ccc') }}; font-size: 1rem;"></i>
                  @endif
                </span>
                <small class="d-block text-center mt-1" style="font-size: 0.7rem; color: #6b7280;">{{ $color->name }}</small>
              </label>
              @endforeach
            </div>
          </div>
          @endif

          {{-- ═══════════════ Sizes (dynamic from DB) ═══════════════ --}}
          @if($sizes->count() > 0)
          <div class="mb-4">
            <label class="fw-bold mb-2">المقاس</label>
            <div class="d-flex flex-wrap gap-2">
              @foreach($sizes as $size)
              <label class="size-filter-item" style="cursor: pointer;">
                <input type="checkbox" name="sizes[]" value="{{ $size->id }}" class="d-none size-checkbox"
                       {{ in_array($size->id, (array)request('sizes', [])) ? 'checked' : '' }}>
                <span class="size-badge {{ in_array($size->id, (array)request('sizes', [])) ? 'active' : '' }}">
                  {{ $size->name }}
                </span>
              </label>
              @endforeach
            </div>
          </div>
          @endif
          
          <div class="d-flex gap-2 mt-4 pt-2">
            <button type="button" class="btn btn-outline-dark flex-grow-1" data-bs-dismiss="modal">إلغاء</button>
            <button type="submit" class="btn text-white flex-grow-1" style="background: linear-gradient(135deg, #f0c14b 0%, #d4a853 100%); border: none;">تطبيق</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

{{-- Interactive JS for color/size selection feedback --}}
<script>
(function() {
  document.querySelectorAll('.color-checkbox').forEach(cb => {
    cb.addEventListener('change', function() {
      const swatch = this.nextElementSibling;
      if (this.checked) {
        swatch.style.borderColor = '#d4a853';
        if (!swatch.querySelector('i')) {
          const icon = document.createElement('i');
          icon.className = 'bi bi-check2';
          icon.style.fontSize = '1rem';
          icon.style.color = '#fff';
          const bg = swatch.style.backgroundColor || '';
          if (bg.includes('255, 255, 255') || bg.includes('255, 255, 0') || bg.includes('yellow')) {
            icon.style.color = '#333';
          }
          swatch.appendChild(icon);
        }
      } else {
        swatch.style.borderColor = 'transparent';
        const icon = swatch.querySelector('i');
        if (icon) icon.remove();
      }
    });
  });

  document.querySelectorAll('.size-checkbox').forEach(cb => {
    cb.addEventListener('change', function() {
      this.nextElementSibling.classList.toggle('active', this.checked);
    });
  });

  const minSlider = document.getElementById('priceRangeMin');
  const maxSlider = document.getElementById('priceRangeMax');
  const priceVal  = document.getElementById('priceVal');
  if (!minSlider || !maxSlider) return;
  
  function updatePriceDisplay() {
    let minVal = parseInt(minSlider.value);
    let maxVal = parseInt(maxSlider.value);
    if (minVal > maxVal) { minSlider.value = maxVal; minVal = maxVal; }
    if (maxVal < minVal) { maxSlider.value = minVal; maxVal = minVal; }
    priceVal.innerText = minVal.toLocaleString('ar-SA') + ' — ' + maxVal.toLocaleString('ar-SA');
  }
  
  minSlider.addEventListener('input', updatePriceDisplay);
  maxSlider.addEventListener('input', updatePriceDisplay);
  updatePriceDisplay();
})();
</script>
