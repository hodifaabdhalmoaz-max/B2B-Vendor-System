@php
    $productModel = $product ?? null;
    $selectedSections = old('storefront_sections', $productModel?->storefront_sections ?? []);
    $selectedSections = is_array($selectedSections) ? $selectedSections : [];
    $sectionGroups = collect(config('storefront.product_sections', []))->groupBy(
        fn ($label, $key) => str($key)->before('.')->toString(),
        preserveKeys: true
    );
    $groupLabels = [
        'home' => 'واجهة الكل',
        'kids' => 'الأطفال',
        'gifts' => 'الهدايا',
        'toys' => 'العاب وتعليم',
        'mother' => 'مستلزمات الأمومة',
        'care' => 'الصحة والعناية',
        'accessories' => 'الاكسسوارات',
        'shoes' => 'الأحذية',
        'messages' => 'الرسائل',
    ];
@endphp

<fieldset class="name">
    <div class="body-title mb-10">أماكن ظهور المنتج في واجهة الجوال</div>
    <div style="display: grid; gap: 14px; margin-top: 10px;">
        @foreach($sectionGroups as $group => $sections)
            <div style="border: 1px solid #e5e5e5; border-radius: 10px; padding: 12px; background: #fafafa;">
                <div class="body-title mb-10" style="font-size: 13px;">{{ $groupLabels[$group] ?? $group }}</div>
                <div class="d-flex flex-wrap gap-3" style="gap: 10px; flex-wrap: wrap;">
                    @foreach($sections as $key => $label)
                        <label style="display: inline-flex; align-items: center; gap: 6px; border: 1px solid #ddd; border-radius: 8px; padding: 7px 10px; background: #fff; cursor: pointer;">
                            <input type="checkbox" name="storefront_sections[]" value="{{ $key }}" @checked(in_array($key, $selectedSections, true))>
                            <span>{{ str($label)->after(' - ') }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
    <div class="text-tiny">يمكن اختيار أكثر من مكان. إذا تركتها فارغة سيبقى المنتج ظاهرًا حسب القواعد العامة مثل المميز أو العرض.</div>
</fieldset>
@error('storefront_sections') <span class="alert alert-danger text-center">{{ $message }}</span> @enderror

<fieldset class="name">
    <div class="body-title mb-10">ترتيب الظهور</div>
    <input class="mb-10" type="number" min="0" max="65535" name="storefront_order" value="{{ old('storefront_order', $productModel?->storefront_order ?? 0) }}">
    <div class="text-tiny">الأرقام الأصغر تظهر أولًا داخل القسم المخصص.</div>
</fieldset>
@error('storefront_order') <span class="alert alert-danger text-center">{{ $message }}</span> @enderror
