<form class="form-new-product form-style-1" action="{{ $action }}" method="POST">
    @csrf
    @if(($method ?? 'POST') !== 'POST')
        @method($method)
    @endif

    <div class="wg-box">
        <fieldset class="name">
            <div class="body-title">Key <span class="tf-color-1">*</span></div>
            <input type="text" name="key" value="{{ old('key', $placement?->key) }}" required placeholder="home_tab_hero">
            @error('key')<span class="text-danger">{{ $message }}</span>@enderror
        </fieldset>

        <fieldset class="name">
            <div class="body-title">الاسم <span class="tf-color-1">*</span></div>
            <input type="text" name="name" value="{{ old('name', $placement?->name) }}" required>
            @error('name')<span class="text-danger">{{ $message }}</span>@enderror
        </fieldset>

        <fieldset class="name">
            <div class="body-title">المقاس المقترح</div>
            <input type="text" name="recommended_size" value="{{ old('recommended_size', $placement?->recommended_size) }}" placeholder="مثال: mobile 375x260">
            @error('recommended_size')<span class="text-danger">{{ $message }}</span>@enderror
        </fieldset>

        <fieldset class="name">
            <div class="body-title">مفعل؟</div>
            <select name="is_active">
                <option value="1" @selected((string) old('is_active', $placement?->is_active ?? 1) === '1')>نعم</option>
                <option value="0" @selected((string) old('is_active', $placement?->is_active ?? 1) === '0')>لا</option>
            </select>
            @error('is_active')<span class="text-danger">{{ $message }}</span>@enderror
        </fieldset>

        <div class="bot">
            <button class="tf-button w208" type="submit">حفظ</button>
            <a class="tf-button style-1 w208" href="{{ route('admin.marketing.placements.index') }}">إلغاء</a>
        </div>
    </div>
</form>
