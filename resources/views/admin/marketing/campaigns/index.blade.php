@extends('layouts.admin')

@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>التسويق والإعلانات</h3>
            <a class="tf-button style-1 w208" href="{{ route('admin.marketing.campaigns.create') }}">إضافة حملة</a>
        </div>

        <div class="wg-box">
            @if(Session::has('status'))
                <p class="alert alert-success">{{ Session::get('status') }}</p>
            @endif

            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الحملة</th>
                            <th>النوع</th>
                            <th>الحالة</th>
                            <th>الأولوية</th>
                            <th>أماكن الظهور</th>
                            <th>الفترة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($campaigns as $campaign)
                            <tr>
                                <td>{{ $campaign->id }}</td>
                                <td>
                                    <strong>{{ $campaign->name }}</strong>
                                    <div><code>{{ $campaign->slug }}</code></div>
                                </td>
                                <td>{{ $campaign->type }}</td>
                                <td>
                                    <span class="badge {{ $campaign->is_active && $campaign->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $campaign->status }}
                                    </span>
                                </td>
                                <td>{{ $campaign->priority }}</td>
                                <td>{{ $campaign->placements->pluck('key')->implode(', ') }}</td>
                                <td>
                                    <small>{{ $campaign->starts_at?->format('Y-m-d H:i') ?? 'فوري' }}</small>
                                    <br>
                                    <small>{{ $campaign->ends_at?->format('Y-m-d H:i') ?? 'بدون نهاية' }}</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        <a class="btn btn-sm btn-primary" href="{{ route('admin.marketing.campaigns.edit', $campaign) }}">
                                            <i class="icon-edit-3"></i>
                                            تعديل
                                        </a>
                                        <form action="{{ route('admin.marketing.campaigns.destroy', $campaign) }}" method="POST" class="m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger" type="submit" onclick="return confirm('حذف الحملة؟')">
                                                <i class="icon-trash-2"></i>
                                                حذف
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">لا توجد حملات بعد.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $campaigns->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
