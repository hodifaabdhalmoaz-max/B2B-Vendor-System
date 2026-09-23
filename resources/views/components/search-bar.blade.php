@props(['placeholder' => 'ابحث عن المنتجات...', 'action' => route('shop.search'), 'name' => 'search', 'value' => request('search')])

<form action="{{ $action }}" method="GET" class="search-field position-relative {{ $attributes->get('class') }}" id="{{ $attributes->get('id') }}">
    <div class="position-relative">
        <input class="search-field__input w-100 border rounded-1 js-quick-search-input fw-medium" style="padding-right: 45px; padding-left: 45px; height: 50px;" type="text" name="{{ $name }}"
            placeholder="{{ $placeholder }}" value="{{ $value }}" autocomplete="off" />
        <button class="btn-icon search-popup__submit pb-0 position-absolute border-0 bg-transparent" style="right: 10px; top: 50%; transform: translateY(-50%); z-index: 5;" type="submit">
            <svg class="d-block" width="20" height="20" viewBox="0 0 20 20" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <use href="#icon_search" />
            </svg>
        </button>
        <button class="btn-icon btn-close-lg search-popup__reset pb-0 position-absolute border-0 bg-transparent js-quick-search-reset" style="left: 10px; top: 50%; transform: translateY(-50%); z-index: 5;" type="reset"></button>
    </div>

    <div class="position-absolute start-0 top-100 m-0 w-100 z-3 text-end" style="text-align: right;">
        <div class="search-result js-quick-search-results bg-white border rounded shadow-sm" style="display: none; max-height: 400px; overflow-y: auto;"></div>
    </div>
</form>
