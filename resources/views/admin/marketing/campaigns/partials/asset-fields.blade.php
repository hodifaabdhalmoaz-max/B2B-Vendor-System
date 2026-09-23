@php
    $asset = $asset ?? null;
    $prefix = $prefix ?? 'assets[0]';
@endphp

<div class="row">
    <div class="col-md-3">
        <fieldset class="name">
            <div class="body-title">نوع الصورة</div>
            <select name="{{ $prefix }}[role]">
                @foreach($assetRoles as $role)
                    <option value="{{ $role }}" @selected(old($prefix.'.role', $asset?->role ?? 'hero_product') === $role)>{{ $role }}</option>
                @endforeach
            </select>
        </fieldset>
    </div>
    <div class="col-md-3">
        <fieldset class="name">
            <div class="body-title">الصورة</div>
            <input type="file" name="{{ $prefix }}[image]" accept=".webp,.jpg,.jpeg,.png">
        </fieldset>
    </div>
    <div class="col-md-2">
        <fieldset class="name">
            <div class="body-title">الترتيب</div>
            <input type="number" name="{{ $prefix }}[sort_order]" value="{{ old($prefix.'.sort_order', $asset?->sort_order ?? 0) }}" min="0">
        </fieldset>
    </div>
    <div class="col-md-2">
        <fieldset class="name">
            <div class="body-title">مفعل؟</div>
            <select name="{{ $prefix }}[is_active]">
                <option value="1" @selected((string) old($prefix.'.is_active', $asset?->is_active ?? 1) === '1')>نعم</option>
                <option value="0" @selected((string) old($prefix.'.is_active', $asset?->is_active ?? 1) === '0')>لا</option>
            </select>
        </fieldset>
    </div>
    <div class="col-md-2">
        <fieldset class="name">
            <div class="body-title">السعر/النص</div>
            <input type="text" name="{{ $prefix }}[price_text]" value="{{ old($prefix.'.price_text', $asset?->price_text) }}" placeholder="29 ر.ي">
        </fieldset>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <fieldset class="name">
            <div class="body-title">العنوان</div>
            <input type="text" name="{{ $prefix }}[title]" value="{{ old($prefix.'.title', $asset?->title) }}">
        </fieldset>
    </div>
    <div class="col-md-4">
        <fieldset class="name">
            <div class="body-title">النص الفرعي</div>
            <input type="text" name="{{ $prefix }}[subtitle]" value="{{ old($prefix.'.subtitle', $asset?->subtitle) }}">
        </fieldset>
    </div>
    <div class="col-md-4">
        <fieldset class="name">
            <div class="body-title">رابط مباشر</div>
            <input type="url" name="{{ $prefix }}[link_url]" value="{{ old($prefix.'.link_url', $asset?->link_url) }}" placeholder="https://example.com">
        </fieldset>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <fieldset class="name">
            <div class="body-title">منتج مرتبط</div>
            <select name="{{ $prefix }}[product_id]">
                <option value="">بدون</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" @selected((int) old($prefix.'.product_id', $asset?->product_id ?? 0) === $product->id)>{{ $product->name }}</option>
                @endforeach
            </select>
        </fieldset>
    </div>
    <div class="col-md-6">
        <fieldset class="name">
            <div class="body-title">فئة مرتبطة</div>
            <select name="{{ $prefix }}[category_id]">
                <option value="">بدون</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((int) old($prefix.'.category_id', $asset?->category_id ?? 0) === $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </fieldset>
    </div>
</div>
