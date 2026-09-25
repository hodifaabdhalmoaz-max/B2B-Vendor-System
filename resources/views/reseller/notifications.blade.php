@extends('reseller.layout')
@section('title', __('Notifications'))
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <h1 class="h3 mb-0">{{ __('Notifications') }}</h1>
    <form method="POST" action="{{ route('reseller.notifications.read-all') }}">
        @csrf
        <button class="btn btn-outline-primary">{{ __('Mark all as read') }}</button>
    </form>
</div>
<div class="d-grid gap-3">
@forelse($notifications as $notification)
    <article class="card p-3 {{ $notification['unread'] ? 'border-primary' : '' }}">
        <div class="d-flex flex-wrap justify-content-between gap-2">
            <h2 class="h5"><span aria-hidden="true">&#128276;</span> {{ $notification['title'] }}</h2>
            <span class="badge {{ $notification['unread'] ? 'bg-primary' : 'bg-secondary' }}">{{ __($notification['unread'] ? 'Unread' : 'Read') }}</span>
        </div>
        <p>{{ $notification['description'] }}</p>
        @if($notification['created_at'])<time class="text-muted mb-3" datetime="{{ $notification['created_at']->toIso8601String() }}">{{ $notification['created_at']->format('Y-m-d H:i') }}</time>@endif
        <div class="d-flex flex-wrap gap-2">
            @if($notification['url'])<a class="btn btn-outline-primary" href="{{ $notification['url'] }}">{{ __('View reservation') }}</a>@endif
            @if($notification['unread'])
                <form method="POST" action="{{ route('reseller.notifications.read', $notification['id']) }}">
                    @csrf<button class="btn btn-outline-secondary">{{ __('Mark as read') }}</button>
                </form>
            @endif
        </div>
    </article>
@empty
    <p class="text-muted">{{ __('No notifications') }}</p>
@endforelse
</div>
<div class="mt-4">{{ $notifications->links() }}</div>
@endsection
