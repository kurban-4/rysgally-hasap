@extends('layouts.app')

@section('content')
<div class="container d-flex align-items-center justify-content-center" style="min-height: 80vh;">
    <div class="col-md-5">

        <div class="text-center mb-4">
            <h2 class="fw-bold" style="color: #107A84;">SirinNabat</h2>
            <p class="text-muted">{{__("app.login_subtitle")}}</p>
        </div>

        <div class="card border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="card-body p-5">

                <h4 class="mb-4 fw-bold text-dark">{{__("app.login_title")}}</h4>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="username" class="form-label small fw-bold text-uppercase" style="color: #107A84;">{{__("app.label_username")}}</label>
                        <div class="input-group">
                            <span class="input-group-text border-0 bg-light ps-3" style="border-top-left-radius: 12px; border-bottom-left-radius: 12px;">
                                <i class="bi bi-person text-muted"></i> </span>
                            <input id="username" type="text"
                                class="form-control border-0 bg-light py-3 @error('username') is-invalid @enderror"
                                name="username" value="{{ old('username') }}"
                                required autofocus placeholder='{{__("app.placeholder_username")}}'
                                style="border-top-right-radius: 12px; border-bottom-right-radius: 12px;">
                        </div>
                        @error('username')
                        <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <label for="password" class="form-label small fw-bold text-uppercase" style="color: #107A84;">{{__("app.label_password")}}</label>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text border-0 bg-light ps-3" style="border-top-left-radius: 12px; border-bottom-left-radius: 12px;">
                                <i class="bi bi-lock text-muted"></i>
                            </span>
                            <input id="password" type="password"
                                class="form-control border-0 bg-light py-3 @error('password') is-invalid @enderror"
                                name="password" required autocomplete="current-password"
                                placeholder="••••••••"
                                style="border-top-right-radius: 12px; border-bottom-right-radius: 12px;">
                        </div>
                        @error('password')
                        <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="remember_me" name="remember"
                            style="cursor: pointer; border-color: #107A84;">
                        <label class="form-check-label text-muted small" for="remember_me">
                            {{__("app.checkbox_remember")}}
                        </label>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn text-white py-3 shadow-sm hover-effect"
                            style="background-color: #107A84; border-radius: 12px; font-weight: 600; letter-spacing: 0.5px;">
                            {{__("app.btn_login")}} <i class="bi bi-box-arrow-in-right ms-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="text-center mt-4 text-muted small opacity-50">
            &copy; {{ date('Y') }} SirinNabat Storage System. Secure Login.
        </div>
    </div>
</div>

<style>
    .hover-effect:hover {
        opacity: 0.95;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(16, 122, 132, 0.3) !important;
        transition: all 0.3s ease;
    }
    .form-control:focus,
    .form-check-input:focus {
        box-shadow: none;
        background-color: #fff !important;
        border: 1px solid #107A84 !important;
    }
    .form-check-input:checked {
        background-color: #107A84;
        border-color: #107A84;
    }
</style>
@endsection