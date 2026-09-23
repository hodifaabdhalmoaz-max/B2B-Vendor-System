@php
    $profile = $reseller->resellerProfile ?? null;
@endphp

<fieldset class="name">
    <div class="body-title">{{ __('Name') }} <span class="tf-color-1">*</span></div>
    <input class="flex-grow" type="text" name="name" value="{{ old('name', $reseller->name) }}" required>
</fieldset>
@error('name') <span class="alert alert-danger text-center">{{ $message }}</span> @enderror

<fieldset class="name">
    <div class="body-title">{{ __('Username') }} <span class="tf-color-1">*</span></div>
    <input class="flex-grow" type="text" name="username" value="{{ old('username', $reseller->username) }}" required>
</fieldset>
@error('username') <span class="alert alert-danger text-center">{{ $message }}</span> @enderror

<fieldset class="name">
    <div class="body-title">{{ __('Mobile') }} <span class="tf-color-1">*</span></div>
    <input class="flex-grow" type="text" name="mobile" value="{{ old('mobile', $reseller->mobile) }}" required>
</fieldset>
@error('mobile') <span class="alert alert-danger text-center">{{ $message }}</span> @enderror

<fieldset class="name">
    <div class="body-title">{{ __('Email') }}</div>
    <input class="flex-grow" type="email" name="email" value="{{ old('email', $reseller->email) }}">
</fieldset>
@error('email') <span class="alert alert-danger text-center">{{ $message }}</span> @enderror

<fieldset class="name">
    <div class="body-title">{{ __('Business name') }}</div>
    <input class="flex-grow" type="text" name="business_name" value="{{ old('business_name', $profile?->business_name) }}">
</fieldset>
@error('business_name') <span class="alert alert-danger text-center">{{ $message }}</span> @enderror

<fieldset class="name">
    <div class="body-title">{{ __('WhatsApp') }}</div>
    <input class="flex-grow" type="text" name="whatsapp" value="{{ old('whatsapp', $profile?->whatsapp) }}">
</fieldset>
@error('whatsapp') <span class="alert alert-danger text-center">{{ $message }}</span> @enderror

<fieldset class="name">
    <div class="body-title">{{ __('Governorate') }}</div>
    <input class="flex-grow" type="text" name="governorate" value="{{ old('governorate', $profile?->governorate) }}">
</fieldset>
@error('governorate') <span class="alert alert-danger text-center">{{ $message }}</span> @enderror

<fieldset class="name">
    <div class="body-title">{{ __('Group') }}</div>
    <input class="flex-grow" type="text" name="group_name" value="{{ old('group_name', $profile?->group_name) }}">
</fieldset>
@error('group_name') <span class="alert alert-danger text-center">{{ $message }}</span> @enderror

<fieldset class="name">
    <div class="body-title">{{ __('Reservation timeout minutes') }}</div>
    <input class="flex-grow" type="number" min="1" max="10080" name="reservation_timeout_minutes" value="{{ old('reservation_timeout_minutes', $profile?->reservation_timeout_minutes) }}">
</fieldset>
@error('reservation_timeout_minutes') <span class="alert alert-danger text-center">{{ $message }}</span> @enderror

<fieldset class="name">
    <div class="body-title">{{ __('Reservation permission') }}</div>
    <label class="d-flex align-items-center gap10">
        <input type="hidden" name="reservation_enabled" value="0">
        <input type="checkbox" name="reservation_enabled" value="1" @checked(old('reservation_enabled', $profile?->reservation_enabled ?? true))>
        <span>{{ __('Enabled') }}</span>
    </label>
</fieldset>
@error('reservation_enabled') <span class="alert alert-danger text-center">{{ $message }}</span> @enderror

<fieldset class="name">
    <div class="body-title">{{ __('Notes') }}</div>
    <textarea class="flex-grow" name="notes" rows="4">{{ old('notes', $profile?->notes) }}</textarea>
</fieldset>
@error('notes') <span class="alert alert-danger text-center">{{ $message }}</span> @enderror
