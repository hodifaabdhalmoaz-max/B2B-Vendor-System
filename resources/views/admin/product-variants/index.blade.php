@extends('layouts.admin')

@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>{{ __('Product variants') }}</h3>
            <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                <li><a href="{{ route('admin.index') }}"><div class="text-tiny">{{ __('Dashboard') }}</div></a></li>
                <li><i class="icon-chevron-right"></i></li>
                <li><a href="{{ route('admin.products') }}"><div class="text-tiny">{{ __('Products') }}</div></a></li>
                <li><i class="icon-chevron-right"></i></li>
                <li><div class="text-tiny">{{ $product->name }}</div></li>
            </ul>
        </div>

        @if(session('status'))
            <p class="alert alert-success">{{ session('status') }}</p>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="wg-box mb-24">
            <div class="flex items-center justify-between gap20 flex-wrap">
                <div>
                    <h5>{{ $product->name }}</h5>
                    <div class="text-tiny">{{ __('Base SKU') }}: {{ $product->SKU ?: '-' }}</div>
                </div>
                <div class="flex gap20 flex-wrap">
                    <div>
                        <div class="text-tiny">{{ __('Base price') }}</div>
                        <div class="body-title">{{ number_format((float) $product->current_price, 2) }}</div>
                    </div>
                    <div>
                        <div class="text-tiny">{{ __('Legacy quantity') }}</div>
                        <div class="body-title">{{ $product->quantity }}</div>
                    </div>
                    <a class="tf-button style-1 w208" href="{{ route('admin.product.edit', ['id' => $product->id]) }}">
                        <i data-lucide="edit" style="width: 16px; height: 16px;"></i>{{ __('Edit product') }}
                    </a>
                </div>
            </div>
            <p class="text-tiny mt-10">{{ __('Legacy quantities are shown for reference only and are not copied into variant inventory.') }}</p>
        </div>

        <div class="wg-box mb-24">
            <div class="flex items-center justify-between gap10 flex-wrap mb-20">
                <h5>{{ __('Existing variants') }}</h5>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>{{ __('Color') }}</th>
                            <th>{{ __('Size') }}</th>
                            <th>{{ __('Variant SKU') }}</th>
                            <th>{{ __('Price adjustment') }}</th>
                            <th>{{ __('Effective price') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Inventory') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($variants as $variant)
                            <tr>
                                <td>
                                    <form id="variant-update-{{ $variant->id }}" method="POST" action="{{ route('admin.products.variants.update', [$product, $variant]) }}">
                                        @csrf
                                        @method('PUT')
                                    </form>
                                    <div class="select">
                                        <select name="color_id" form="variant-update-{{ $variant->id }}">
                                            <option value="">{{ __('No color') }}</option>
                                            @if($variant->color_id && ! $product->colors->contains('id', $variant->color_id))
                                                <option value="{{ $variant->color_id }}" selected>{{ $variant->color?->name ?: $variant->color_id }} — {{ __('Detached from product') }}</option>
                                            @endif
                                            @foreach($product->colors as $color)
                                                <option value="{{ $color->id }}" @selected((int) $variant->color_id === (int) $color->id)>{{ $color->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>
                                <td>
                                    <div class="select">
                                        <select name="size_id" form="variant-update-{{ $variant->id }}">
                                            <option value="">{{ __('No size') }}</option>
                                            @if($variant->size_id && ! $product->sizes->contains('id', $variant->size_id))
                                                <option value="{{ $variant->size_id }}" selected>{{ $variant->size?->name ?: $variant->size_id }} — {{ __('Detached from product') }}</option>
                                            @endif
                                            @foreach($product->sizes as $size)
                                                <option value="{{ $size->id }}" @selected((int) $variant->size_id === (int) $size->id)>{{ $size->name }} ({{ $size->code }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" name="sku" value="{{ $variant->sku }}" form="variant-update-{{ $variant->id }}">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="price_adjustment" value="{{ $variant->price_adjustment }}" form="variant-update-{{ $variant->id }}">
                                </td>
                                <td>{{ $variant->effectivePrice() }}</td>
                                <td>
                                    <input type="hidden" name="is_active" value="0" form="variant-update-{{ $variant->id }}">
                                    <label class="d-inline-flex align-items-center gap-2">
                                        <input type="checkbox" name="is_active" value="1" form="variant-update-{{ $variant->id }}" @checked($variant->is_active)>
                                        <span class="badge {{ $variant->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $variant->is_active ? __('Active') : __('Inactive') }}
                                        </span>
                                    </label>
                                </td>
                                <td>
                                    @if($variant->inventoryItem)
                                        <div class="text-tiny">{{ __('On hand') }}: {{ $variant->inventoryItem->stock_on_hand }}</div>
                                        <div class="text-tiny">{{ __('Reserved') }}: {{ $variant->inventoryItem->reserved_quantity }}</div>
                                        <div class="text-tiny">{{ __('Available') }}: {{ $variant->inventoryItem->available_quantity }}</div>
                                    @else
                                        <div class="text-tiny">{{ __('Not initialized') }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($variant->inventoryItem)
                                        <form method="POST" action="{{ route('admin.products.variants.inventory.store', [$product, $variant]) }}" class="mb-10">
                                            @csrf
                                            <input type="hidden" name="variant_form_id" value="{{ $variant->id }}">
                                            <input type="hidden" name="idempotency_key" value="{{ old('variant_form_id') == $variant->id ? old('idempotency_key') : (string) \Illuminate\Support\Str::uuid() }}">
                                            <select name="operation" aria-label="{{ __('Inventory operation') }}">
                                                <option value="stock_in" @selected(old('variant_form_id') == $variant->id && old('operation') === 'stock_in')>{{ __('Stock In') }}</option>
                                                <option value="stock_out" @selected(old('variant_form_id') == $variant->id && old('operation') === 'stock_out')>{{ __('Stock Out') }}</option>
                                                <option value="adjustment" @selected(old('variant_form_id') == $variant->id && old('operation') === 'adjustment')>{{ __('Adjust Physical Stock To') }}</option>
                                            </select>
                                            <input type="number" name="quantity" min="0" max="4294967295" step="1" required aria-label="{{ __('Quantity or target on hand') }}" value="{{ old('variant_form_id') == $variant->id ? old('quantity') : '' }}">
                                            <button class="tf-button" type="submit">{{ __('Update stock') }}</button>
                                        </form>
                                    @endif
                                    <div class="list-icon-function">
                                        <button class="item edit border-0 bg-transparent" type="submit" form="variant-update-{{ $variant->id }}" title="{{ __('Save') }}">
                                            <i data-lucide="save" style="width: 16px; height: 16px;"></i>
                                        </button>
                                        @if($variant->is_active)
                                            <form method="POST" action="{{ route('admin.products.variants.deactivate', [$product, $variant]) }}">
                                                @csrf
                                                <button class="item border-0 bg-transparent" type="submit" title="{{ __('Deactivate') }}">
                                                    <i data-lucide="pause-circle" style="width: 16px; height: 16px;"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.products.variants.activate', [$product, $variant]) }}">
                                                @csrf
                                                <button class="item text-success border-0 bg-transparent" type="submit" title="{{ __('Activate') }}">
                                                    <i data-lucide="play-circle" style="width: 16px; height: 16px;"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.products.variants.destroy', [$product, $variant]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="item text-danger delete border-0 bg-transparent" type="submit" title="{{ __('Delete') }}">
                                                <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">{{ __('No variants found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tf-section-2">
            <div class="wg-box">
                <h5 class="mb-20">{{ __('Manual variant') }}</h5>
                <form class="form-new-product form-style-1" method="POST" action="{{ route('admin.products.variants.store', $product) }}">
                    @csrf
                    <div class="cols gap22">
                        <fieldset>
                            <div class="body-title mb-10">{{ __('Color') }}</div>
                            <div class="select">
                                <select name="color_id">
                                    <option value="">{{ __('No color') }}</option>
                                    @foreach($product->colors as $color)
                                        <option value="{{ $color->id }}" @selected(old('color_id') == $color->id)>{{ $color->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </fieldset>
                        <fieldset>
                            <div class="body-title mb-10">{{ __('Size') }}</div>
                            <div class="select">
                                <select name="size_id">
                                    <option value="">{{ __('No size') }}</option>
                                    @foreach($product->sizes as $size)
                                        <option value="{{ $size->id }}" @selected(old('size_id') == $size->id)>{{ $size->name }} ({{ $size->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </fieldset>
                    </div>
                    <div class="cols gap22">
                        <fieldset>
                            <div class="body-title mb-10">{{ __('Variant SKU') }}</div>
                            <input type="text" name="sku" value="{{ old('sku') }}" placeholder="{{ __('Auto-generate when blank') }}">
                        </fieldset>
                        <fieldset>
                            <div class="body-title mb-10">{{ __('Price adjustment') }}</div>
                            <input type="number" step="0.01" name="price_adjustment" value="{{ old('price_adjustment', '0.00') }}" required>
                        </fieldset>
                    </div>
                    <input type="hidden" name="is_active" value="1">
                    <button class="tf-button w-full" type="submit">{{ __('Create variant') }}</button>
                </form>
            </div>

            <div class="wg-box">
                <h5 class="mb-20">{{ __('Candidate combinations') }}</h5>
                <form method="POST" action="{{ route('admin.products.variants.bulk-store', $product) }}">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __('Select') }}</th>
                                    <th>{{ __('Color') }}</th>
                                    <th>{{ __('Size') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($candidateCombinations as $candidate)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="combinations[]" value="{{ $candidate['key'] }}" @disabled($candidate['exists'])>
                                        </td>
                                        <td>{{ $candidate['color_name'] ?: __('No color') }}</td>
                                        <td>{{ $candidate['size_name'] ?: __('No size') }}</td>
                                        <td>
                                            @if($candidate['exists'])
                                                <span class="badge bg-secondary">{{ __('Already created') }}</span>
                                            @else
                                                <span class="badge bg-success">{{ __('Available') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <button class="tf-button w-full" type="submit">{{ __('Create selected variants') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        $(function(){
            $('.delete').on('click', function(e){
                e.preventDefault();
                const button = $(this);
                const form = button.closest('form');
                swal({
                    title: "{{ __('Are you sure?') }}",
                    text: "{{ __('Delete this variant only if it has no history or stock.') }}",
                    type: "warning",
                    buttons: ["{{ __('No') }}", "{{ __('Yes') }}"],
                    confirmButtonColor: "#dc3545"
                }).then(function(result){
                    if(result){
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
