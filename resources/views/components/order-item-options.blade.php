@props([
    'options' => [],
    'compact' => false,
])

@php
    $optionData = $options;

    if (is_object($optionData) && method_exists($optionData, 'toArray')) {
        $optionData = $optionData->toArray();
    }

    $optionData = is_array($optionData) ? $optionData : [];
    $hasColor = ! empty($optionData['color_name']);
    $hasSize = ! empty($optionData['size_name']);
@endphp

@if($hasColor || $hasSize)
    <div class="order-item-options {{ $compact ? 'order-item-options--compact' : '' }}">
        @if($hasColor)
            <span class="order-item-options__item">
                @if(! empty($optionData['color_hex']))
                    <span class="order-item-options__swatch" style="background-color: {{ $optionData['color_hex'] }}"></span>
                @endif
                <span>اللون: {{ $optionData['color_name'] }}</span>
            </span>
        @endif

        @if($hasSize)
            <span class="order-item-options__item">
                <span>المقاس: {{ $optionData['size_name'] }}</span>
            </span>
        @endif
    </div>
@endif

@once
    @push('styles')
        <style>
            .order-item-options {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-top: 8px;
            }

            .order-item-options__item {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 5px 10px;
                border: 1px solid #f0e3c7;
                border-radius: 999px;
                background: #fffaf0;
                color: #4b5563;
                font-size: 12px;
                font-weight: 700;
                line-height: 1.4;
            }

            .order-item-options__swatch {
                width: 14px;
                height: 14px;
                border: 1px solid rgba(0, 0, 0, 0.12);
                border-radius: 50%;
                box-shadow: inset 0 0 0 2px rgba(255, 255, 255, 0.6);
            }

            .order-item-options--compact {
                gap: 5px;
                margin-top: 5px;
            }

            .order-item-options--compact .order-item-options__item {
                padding: 3px 8px;
                font-size: 11px;
            }
        </style>
    @endpush
@endonce
