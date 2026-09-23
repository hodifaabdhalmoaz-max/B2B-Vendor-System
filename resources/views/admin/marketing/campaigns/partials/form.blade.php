@php
    $campaign = $campaign ?? null;
    $creative = $campaign?->creative;
    $selectedPlacements = collect(old('placements', $campaign?->placements?->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id)->all();
    $placementSorts = old('placement_sort_order', $campaign?->placements?->mapWithKeys(fn ($placement) => [$placement->id => $placement->pivot->sort_order])->all() ?? []);
    $rule = $campaign?->rules?->first();
    $ruleValue = old('rule_value', $rule?->value ?? []);
    $assets = old('existing_assets') ? $campaign?->assets ?? collect() : $campaign?->assets ?? collect();
@endphp

<form class="form-new-product form-style-1" action="{{ $action }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if(($method ?? 'POST') !== 'POST')
        @method($method)
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>يرجى مراجعة الحقول المطلوبة.</strong>
        </div>
    @endif

    <div class="wg-box mb-30">
        <h5>بيانات الحملة</h5>

        <fieldset class="name">
            <div class="body-title">اسم الحملة <span class="tf-color-1">*</span></div>
            <input type="text" name="name" value="{{ old('name', $campaign?->name) }}" required>
            @error('name')<span class="text-danger">{{ $message }}</span>@enderror
        </fieldset>

        <fieldset class="name">
            <div class="body-title">Slug</div>
            <input type="text" name="slug" value="{{ old('slug', $campaign?->slug) }}" placeholder="يولد تلقائيا عند تركه فارغا">
            @error('slug')<span class="text-danger">{{ $message }}</span>@enderror
        </fieldset>

        <div class="row">
            <div class="col-md-4">
                <fieldset class="name">
                    <div class="body-title">نوع الحملة</div>
                    <select name="type">
                        @foreach($campaignTypes as $type)
                            <option value="{{ $type }}" @selected(old('type', $campaign?->type ?? 'home_tab_banner') === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                    @error('type')<span class="text-danger">{{ $message }}</span>@enderror
                </fieldset>
            </div>
            <div class="col-md-4">
                <fieldset class="name">
                    <div class="body-title">الحالة</div>
                    <select name="status">
                        @foreach($campaignStatuses as $status)
                            <option value="{{ $status }}" @selected(old('status', $campaign?->status ?? 'active') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                    @error('status')<span class="text-danger">{{ $message }}</span>@enderror
                </fieldset>
            </div>
            <div class="col-md-4">
                <fieldset class="name">
                    <div class="body-title">مفعلة؟</div>
                    <select name="is_active">
                        <option value="1" @selected((string) old('is_active', $campaign?->is_active ?? 1) === '1')>نعم</option>
                        <option value="0" @selected((string) old('is_active', $campaign?->is_active ?? 1) === '0')>لا</option>
                    </select>
                    @error('is_active')<span class="text-danger">{{ $message }}</span>@enderror
                </fieldset>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <fieldset class="name">
                    <div class="body-title">تبدأ في</div>
                    <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $campaign?->starts_at?->format('Y-m-d\TH:i')) }}">
                    @error('starts_at')<span class="text-danger">{{ $message }}</span>@enderror
                </fieldset>
            </div>
            <div class="col-md-4">
                <fieldset class="name">
                    <div class="body-title">تنتهي في</div>
                    <input type="datetime-local" name="ends_at" value="{{ old('ends_at', $campaign?->ends_at?->format('Y-m-d\TH:i')) }}">
                    @error('ends_at')<span class="text-danger">{{ $message }}</span>@enderror
                </fieldset>
            </div>
            <div class="col-md-4">
                <fieldset class="name">
                    <div class="body-title">الأولوية</div>
                    <input type="number" name="priority" value="{{ old('priority', $campaign?->priority ?? 0) }}" min="0">
                    @error('priority')<span class="text-danger">{{ $message }}</span>@enderror
                </fieldset>
            </div>
        </div>
    </div>

    <div class="wg-box mb-30">
        <h5>أماكن الظهور</h5>
        @foreach($placements as $placement)
            <div class="d-flex align-items-center gap-3 mb-3">
                <label class="d-flex align-items-center gap-2 mb-0" style="min-width: 280px;">
                    <input type="checkbox" name="placements[]" value="{{ $placement->id }}" @checked(in_array($placement->id, $selectedPlacements, true))>
                    <span><strong>{{ $placement->key }}</strong> - {{ $placement->name }}</span>
                </label>
                <input type="number" name="placement_sort_order[{{ $placement->id }}]" value="{{ $placementSorts[$placement->id] ?? 0 }}" min="0" placeholder="ترتيب" style="max-width: 120px;">
            </div>
        @endforeach
        @error('placements')<span class="text-danger">{{ $message }}</span>@enderror
    </div>

    <div class="wg-box mb-30">
        <h5>قاعدة الظهور</h5>
        <div class="row">
            <div class="col-md-3">
                <fieldset class="name">
                    <div class="body-title">الاستهداف</div>
                    <select name="rule_target_type">
                        @foreach($ruleTargetTypes as $targetType)
                            <option value="{{ $targetType }}" @selected(old('rule_target_type', $rule?->target_type ?? 'all') === $targetType)>{{ $targetType }}</option>
                        @endforeach
                    </select>
                </fieldset>
            </div>
            <div class="col-md-3">
                <fieldset class="name">
                    <div class="body-title">تبويب الصفحة الرئيسية</div>
                    <select name="rule_home_tab_slug">
                        <option value="">عام</option>
                        @foreach($homeTabs as $homeTab)
                            <option value="{{ $homeTab['slug'] }}" @selected(old('rule_home_tab_slug', $ruleValue['tab_slug'] ?? '') === $homeTab['slug'])>
                                {{ $homeTab['label'] }}
                            </option>
                        @endforeach
                    </select>
                </fieldset>
            </div>
            <div class="col-md-3">
                <fieldset class="name">
                    <div class="body-title">الفئة</div>
                    <select name="rule_category_id">
                        <option value="">بدون</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) old('rule_category_id', $ruleValue['category_id'] ?? 0) === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </fieldset>
            </div>
            <div class="col-md-3">
                <fieldset class="name">
                    <div class="body-title">الجهاز</div>
                    <select name="rule_device">
                        <option value="">الكل</option>
                        <option value="mobile" @selected(old('rule_device', $ruleValue['device'] ?? '') === 'mobile')>mobile</option>
                        <option value="desktop" @selected(old('rule_device', $ruleValue['device'] ?? '') === 'desktop')>desktop</option>
                    </select>
                </fieldset>
            </div>
        </div>
        <fieldset class="name">
            <div class="body-title">منتج مستهدف</div>
            <select name="rule_product_id">
                <option value="">بدون</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" @selected((int) old('rule_product_id', $ruleValue['product_id'] ?? 0) === $product->id)>{{ $product->name }}</option>
                @endforeach
            </select>
        </fieldset>
    </div>

    <div class="wg-box mb-30">
        <h5>محتوى الإعلان</h5>
        <div class="row">
            <div class="col-md-6">
                <fieldset class="name">
                    <div class="body-title">العنوان</div>
                    <input type="text" name="creative[title]" value="{{ old('creative.title', $creative?->title) }}">
                </fieldset>
            </div>
            <div class="col-md-6">
                <fieldset class="name">
                    <div class="body-title">النص الفرعي</div>
                    <input type="text" name="creative[subtitle]" value="{{ old('creative.subtitle', $creative?->subtitle) }}">
                </fieldset>
            </div>
        </div>

        <fieldset class="description">
            <div class="body-title">الوصف</div>
            <textarea name="creative[description]">{{ old('creative.description', $creative?->description) }}</textarea>
        </fieldset>

        <div class="row">
            <div class="col-md-3">
                <fieldset class="name">
                    <div class="body-title">نص الزر</div>
                    <input type="text" name="creative[cta_text]" value="{{ old('creative.cta_text', $creative?->cta_text ?? 'تسوق الآن') }}">
                </fieldset>
            </div>
            <div class="col-md-3">
                <fieldset class="name">
                    <div class="body-title">نوع الرابط</div>
                    <select name="creative[link_type]">
                        @foreach($linkTypes as $linkType)
                            <option value="{{ $linkType }}" @selected(old('creative.link_type', $creative?->link_type ?? 'url') === $linkType)>{{ $linkType }}</option>
                        @endforeach
                    </select>
                </fieldset>
            </div>
            <div class="col-md-6">
                <fieldset class="name">
                    <div class="body-title">الرابط</div>
                    <input type="url" name="creative[link_url]" value="{{ old('creative.link_url', $creative?->link_url) }}" placeholder="https://example.com أو اتركه مع product/category">
                </fieldset>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <fieldset class="name">
                    <div class="body-title">منتج مرتبط</div>
                    <select name="creative[product_id]">
                        <option value="">بدون</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" @selected((int) old('creative.product_id', $creative?->product_id ?? 0) === $product->id)>{{ $product->name }}</option>
                        @endforeach
                    </select>
                </fieldset>
            </div>
            <div class="col-md-3">
                <fieldset class="name">
                    <div class="body-title">فئة مرتبطة</div>
                    <select name="creative[category_id]">
                        <option value="">بدون</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) old('creative.category_id', $creative?->category_id ?? 0) === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </fieldset>
            </div>
            <div class="col-md-2">
                <fieldset class="name">
                    <div class="body-title">لون الخلفية</div>
                    <input type="text" name="creative[background_color]" value="{{ old('creative.background_color', $creative?->background_color) }}" placeholder="#6fb8c4">
                </fieldset>
            </div>
            <div class="col-md-2">
                <fieldset class="name">
                    <div class="body-title">لون النص</div>
                    <input type="text" name="creative[text_color]" value="{{ old('creative.text_color', $creative?->text_color) }}" placeholder="#ffffff">
                </fieldset>
            </div>
            <div class="col-md-2">
                <fieldset class="name">
                    <div class="body-title">Alt text</div>
                    <input type="text" name="creative[alt_text]" value="{{ old('creative.alt_text', $creative?->alt_text) }}">
                </fieldset>
            </div>
        </div>
    </div>

    @if($assets->isNotEmpty())
        <div class="wg-box mb-30">
            <h5>الصور الحالية</h5>
            @foreach($assets as $asset)
                <div class="border rounded p-3 mb-3">
                    <input type="hidden" name="existing_assets[{{ $asset->id }}][id]" value="{{ $asset->id }}">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="{{ $asset->imageUrl() }}" alt="{{ $asset->title }}" style="width: 76px; height: 76px; object-fit: cover; border-radius: 8px;">
                        <label class="d-flex align-items-center gap-2 mb-0">
                            <input type="checkbox" name="existing_assets[{{ $asset->id }}][delete]" value="1">
                            حذف هذا العنصر
                        </label>
                    </div>
                    @include('admin.marketing.campaigns.partials.asset-fields', [
                        'prefix' => "existing_assets[$asset->id]",
                        'asset' => $asset,
                        'assetRoles' => $assetRoles,
                        'products' => $products,
                        'categories' => $categories,
                        'imageRequired' => false,
                    ])
                </div>
            @endforeach
        </div>
    @endif

    <div class="wg-box mb-30">
        <h5>صور وعناصر جديدة</h5>
        @for($i = 0; $i < 5; $i++)
            <div class="border rounded p-3 mb-3">
                <h6>عنصر #{{ $i + 1 }}</h6>
                @include('admin.marketing.campaigns.partials.asset-fields', [
                    'prefix' => "assets[$i]",
                    'asset' => null,
                    'assetRoles' => $assetRoles,
                    'products' => $products,
                    'categories' => $categories,
                    'imageRequired' => false,
                ])
            </div>
        @endfor
    </div>

    <div class="bot">
        <button class="tf-button w208" type="submit">حفظ الحملة</button>
        <a class="tf-button style-1 w208" href="{{ route('admin.marketing.campaigns.index') }}">إلغاء</a>
    </div>
</form>
