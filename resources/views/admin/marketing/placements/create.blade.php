@extends('layouts.admin')

@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>إضافة مكان ظهور</h3>
            <a class="tf-button style-1 w208" href="{{ route('admin.marketing.placements.index') }}">كل أماكن الظهور</a>
        </div>

        @include('admin.marketing.placements.partials.form', [
            'placement' => null,
            'action' => route('admin.marketing.placements.store'),
            'method' => 'POST',
        ])
    </div>
</div>
@endsection
