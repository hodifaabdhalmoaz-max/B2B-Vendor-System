@extends('layouts.admin')

@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>{{ __('Resellers') }}</h3>
            <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                <li><a href="{{ route('admin.index') }}"><div class="text-tiny">{{ __('Dashboard') }}</div></a></li>
                <li><i data-lucide="chevron-right" style="width: 16px; height: 16px;"></i></li>
                <li><div class="text-tiny">{{ __('Resellers') }}</div></li>
            </ul>
        </div>

        <div class="wg-box">
            <div class="flex items-center justify-between gap10 flex-wrap">
                <h5>{{ __('Approved reseller accounts') }}</h5>
                <a class="tf-button style-1 w208" href="{{ route('admin.resellers.create') }}">
                    <i data-lucide="plus" style="width: 16px; height: 16px;"></i>{{ __('Add reseller') }}
                </a>
            </div>

            @if(session('status'))
                <p class="alert alert-success">{{ session('status') }}</p>
            @endif
            @error('reseller')
                <p class="alert alert-danger">{{ $message }}</p>
            @enderror

            <div class="wg-table table-all-user">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Username') }}</th>
                                <th>{{ __('Mobile / WhatsApp') }}</th>
                                <th>{{ __('Business / Group') }}</th>
                                <th>{{ __('Governorate') }}</th>
                                <th>{{ __('Account') }}</th>
                                <th>{{ __('Profile') }}</th>
                                <th>{{ __('Reservations') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($resellers as $reseller)
                                @php($profile = $reseller->resellerProfile)
                                <tr>
                                    <td>{{ $reseller->name }}</td>
                                    <td>{{ $reseller->username }}</td>
                                    <td>{{ $reseller->mobile }}<br><span class="text-tiny">{{ $profile?->whatsapp ?: '-' }}</span></td>
                                    <td>{{ $profile?->business_name ?: '-' }}<br><span class="text-tiny">{{ $profile?->group_name ?: '-' }}</span></td>
                                    <td>{{ $profile?->governorate ?: '-' }}</td>
                                    <td>{{ $reseller->is_active ? __('Active') : __('Inactive') }}</td>
                                    <td>{{ $profile?->status ?: __('Missing') }}</td>
                                    <td>
                                        {{ $profile?->reservation_enabled ? __('Enabled') : __('Disabled') }}
                                        @if($profile)
                                            <br><a href="{{ route('admin.b2b.reservations.index', ['reseller_profile_id' => $profile->id]) }}">{{ __('B2B Reservations') }}</a>
                                        @endif
                                        <br><span class="text-tiny">{{ $profile?->reservation_timeout_minutes ?: '-' }}</span>
                                    </td>
                                    <td>
                                        <div class="list-icon-function">
                                            <a href="{{ route('admin.resellers.edit', $reseller) }}" title="{{ __('Edit') }}">
                                                <div class="item edit"><i data-lucide="edit" style="width: 16px; height: 16px;"></i></div>
                                            </a>
                                            @if($reseller->is_active)
                                                <form method="POST" action="{{ route('admin.resellers.suspend', $reseller) }}">
                                                    @csrf
                                                    <button class="item text-warning border-0 bg-transparent" title="{{ __('Suspend') }}" type="submit">
                                                        <i data-lucide="pause-circle" style="width: 16px; height: 16px;"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.resellers.reactivate', $reseller) }}">
                                                    @csrf
                                                    <button class="item text-success border-0 bg-transparent" title="{{ __('Reactivate') }}" type="submit">
                                                        <i data-lucide="play-circle" style="width: 16px; height: 16px;"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">{{ __('No resellers found.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="divider"></div>
                <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                    {{ $resellers->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
