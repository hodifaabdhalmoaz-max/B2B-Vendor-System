@php
    $categoryModel = $category ?? null;
    $selectedContexts = old(
        'storefront_contexts',
        $categoryModel ? ($categoryModel->storefront_contexts ?? []) : ['home.all']
    );
    $selectedContexts = is_array($selectedContexts) ? $selectedContexts : [];
    $contextGroups = collect(config('storefront.category_contexts', []))->groupBy(
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
    ];
@endphp

<fieldset class="name">
    <div class="body-title">أماكن ظهور الفئة في واجهة الجوال</div>
    <div style="display: grid; gap: 14px; margin-top: 10px;">
        @foreach($contextGroups as $group => $contexts)
            <div style="border: 1px solid #e5e5e5; border-radius: 10px; padding: 12px; background: #fafafa;">
                <div class="body-title mb-10" style="font-size: 13px;">{{ $groupLabels[$group] ?? $group }}</div>
                <div class="d-flex flex-wrap gap-3" style="gap: 10px; flex-wrap: wrap;">
                    @foreach($contexts as $key => $label)
                        <label style="display: inline-flex; align-items: center; gap: 6px; border: 1px solid #ddd; border-radius: 8px; padding: 7px 10px; background: #fff; cursor: pointer;">
                            <input type="checkbox" name="storefront_contexts[]" value="{{ $key }}" @checked(in_array($key, $selectedContexts, true))>
                            <span>{{ str($label)->after(' - ') }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
    <div class="text-tiny">اتركها فارغة إذا كنت لا تريد تخصيص ظهور هذه الفئة في تبويبات الجوال.</div>
</fieldset>
@error('storefront_contexts') <span class="alert alert-danger text-center">{{ $message }}</span> @enderror

<fieldset class="name">
    <div class="body-title">ترتيب الظهور</div>
    <input class="flex-grow" type="number" min="0" max="65535" name="storefront_order" value="{{ old('storefront_order', $categoryModel?->storefront_order ?? 0) }}">
    <div class="text-tiny">الأرقام الأصغر تظهر أولًا داخل نفس القسم.</div>
</fieldset>
@error('storefront_order') <span class="alert alert-danger text-center">{{ $message }}</span> @enderror
