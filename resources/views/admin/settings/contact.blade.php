@extends('layouts.admin')

@section('title', __('admin.contact_settings'))

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>{{ __('admin.contact_settings') }}</h6>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    <form action="{{ route('admin.settings.contact.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-4">{{ __('admin.contact_information') }}</h5>

                                <div class="mb-3">
                                    <label for="address" class="form-label">{{ __('admin.address') }}</label>
                                    <input type="text" class="form-control" id="address" name="address" value="{{ old('address', $settings['address'] ?? 'صنعاء، اليمن') }}">
                                </div>

                                <div class="mb-3">
                                    <label for="phone_1" class="form-label">{{ __('admin.phone_1') }}</label>
                                    <input type="text" class="form-control" id="phone_1" name="phone_1" value="{{ old('phone_1', $settings['phone_1'] ?? '777548421 967+') }}">
                                </div>

                                <div class="mb-3">
                                    <label for="phone_2" class="form-label">{{ __('admin.phone_2') }}</label>
                                    <input type="text" class="form-control" id="phone_2" name="phone_2" value="{{ old('phone_2', $settings['phone_2'] ?? '718706242 967+') }}">
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">{{ __('admin.email') }}</label>
                                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $settings['email'] ?? 'hodifaabdhalmoaz@gmail.com') }}">
                                </div>

                                <div class="mb-3">
                                    <label for="working_hours" class="form-label">{{ __('admin.working_hours') }}</label>
                                    <textarea class="form-control" id="working_hours" name="working_hours" rows="3">{{ old('working_hours', $settings['working_hours'] ?? 'السبت - الخميس: 9:00 صباحًا - 8:00 مساءً
الجمعة: 2:00 مساءً - 8:00 مساءً') }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="map_embed_code" class="form-label">{{ __('admin.map_embed_code') }}</label>
                                    <textarea class="form-control" id="map_embed_code" name="map_embed_code" rows="3">{{ old('map_embed_code', $settings['map_embed_code'] ?? '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15333.084909285506!2d44.19958!3d15.35937!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1603dbdcc30d6c8d%3A0x7ae35e746a3c98f!2sSana&#39;a%2C%20Yemen!5e0!3m2!1sen!2s!4v1623825289123!5m2!1sen!2s" allowfullscreen="" loading="lazy"></iframe>') }}</textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h5 class="mb-4">{{ __('admin.social_media') }}</h5>

                                <div class="mb-3">
                                    <label for="facebook" class="form-label">{{ __('admin.facebook') }}</label>
                                    <input type="url" class="form-control" id="facebook" name="facebook" value="{{ old('facebook', $settings['facebook'] ?? 'https://www.facebook.com/share/1E3T83a8KD/') }}">
                                </div>

                                <div class="mb-3">
                                    <label for="twitter" class="form-label">{{ __('admin.twitter') }}</label>
                                    <input type="url" class="form-control" id="twitter" name="twitter" value="{{ old('twitter', $settings['twitter'] ?? 'https://x.com/moaz_abdh') }}">
                                </div>

                                <div class="mb-3">
                                    <label for="instagram" class="form-label">{{ __('admin.instagram') }}</label>
                                    <input type="url" class="form-control" id="instagram" name="instagram" value="{{ old('instagram', $settings['instagram'] ?? 'https://www.instagram.com/invites/contact/?utm_source=ig_contact_invite&utm_medium=copy_link&utm_content=mwfgwqx') }}">
                                </div>

                                <div class="mb-3">
                                    <label for="linkedin" class="form-label">{{ __('admin.linkedin') }}</label>
                                    <input type="url" class="form-control" id="linkedin" name="linkedin" value="{{ old('linkedin', $settings['linkedin'] ?? 'https://www.linkedin.com/in/hodifa-al-hodify-30644b289') }}">
                                </div>

                                <div class="mb-3">
                                    <label for="github" class="form-label">{{ __('admin.github') }}</label>
                                    <input type="url" class="form-control" id="github" name="github" value="{{ old('github', $settings['github'] ?? 'https://github.com/HA1234098765') }}">
                                </div>

                                <div class="mb-3">
                                    <label for="whatsapp" class="form-label">{{ __('admin.whatsapp') }}</label>
                                    <input type="text" class="form-control" id="whatsapp" name="whatsapp" value="{{ old('whatsapp', $settings['whatsapp'] ?? '967718706242') }}">
                                    <div class="form-text">{{ __('admin.whatsapp_number_format') }}</div>
                                </div>

                                <h5 class="mt-4 mb-4">{{ __('admin.payment_settings') }}</h5>

                                <div class="mb-3">
                                    <label for="bank_name" class="form-label">{{ __('admin.bank_name') }}</label>
                                    <input type="text" class="form-control" id="bank_name" name="bank_name" value="{{ old('bank_name', $settings['bank_name'] ?? 'اليمن الدولي') }}">
                                </div>

                                <div class="mb-3">
                                    <label for="account_name" class="form-label">{{ __('admin.account_name') }}</label>
                                    <input type="text" class="form-control" id="account_name" name="account_name" value="{{ old('account_name', $settings['account_name'] ?? 'حذيفة عبدالمعز الحذيفي') }}">
                                </div>

                                <div class="mb-3">
                                    <label for="account_number" class="form-label">{{ __('admin.account_number') }}</label>
                                    <input type="text" class="form-control" id="account_number" name="account_number" value="{{ old('account_number', $settings['account_number'] ?? '123456789') }}">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>{{ __('admin.save_changes') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection