@extends('layouts.app')

@section('content')
<div class="desktop-app-layout">
    @include('app.navbar')

    <main class="app-main">
        <header class="main-header">
            <a href="{{ route('storage.index') }}" class="btn-back">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h4 class="fw-bold mb-0" style="color: #107A84;">{{__("app.new_medicine_title")}}</h4>
                <p class="text-muted small mb-0 d-none d-md-block">{{__("app.new_medicine_subtitle")}}</p>
            </div>
        </header>

        <div class="workspace custom-scrollbar">
            <div class="form-container">
                <div class="card form-card border-0 shadow-lg">
                    <form action="{{ route('medicine.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="form-grid">
                            {{-- ── LEFT COLUMN ── --}}
                            <div class="form-col-left">
                                <h5 class="section-title">
                                    <i class="bi bi-info-circle me-2"></i>{{__("app.add_medicine_title")}}
                                </h5>

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-muted">{{__("app.label_medicine_name")}}</label>
                                    <input type="text" name="name" class="form-control form-control-lg custom-input"
                                        placeholder='{{__("app.placeholder_med_name")}}' required>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-muted">{{__("app.label_description")}}</label>
                                    <textarea name="description" class="form-control custom-input"
                                        rows="3" placeholder='{{__("app.placeholder_med_desc")}}'></textarea>
                                </div>

                                {{-- Inventory grid --}}
                                <div id="image-preview-container" class="d-flex flex-wrap gap-2 mt-2 mb-3"></div>

                                <div class="inventory-grid mb-4">
                                    <div class="premium-input-group">
                                        <label class="input-label">
                                            <i class="bi bi-box-seam me-1 text-primary"></i> Количество коробок
                                        </label>
                                        <div class="input-modern-wrapper">
                                            <input type="number" name="quantity" class="form-control" placeholder="10" required>
                                            <span class="unit-badge">кор.</span>
                                        </div>
                                    </div>
                                    <div class="premium-input-group">
                                        <label class="input-label">
                                            <i class="bi bi-layers me-1 text-primary"></i> Пластинок в коробке
                                        </label>
                                        <div class="input-modern-wrapper">
                                            <input type="number" name="units_per_box" class="form-control" value="10">
                                            <span class="unit-badge">шт.</span>
                                        </div>
                                    </div>
                                    <div class="premium-input-group">
                                        <label class="input-label">
                                            <i class="bi bi-tag me-1 text-primary"></i> Цена за 1 пластинку
                                        </label>
                                        <div class="input-modern-wrapper price-input">
                                            <input type="number" step="0.01" name="price_unit" class="form-control" placeholder="0.00">
                                            <span class="currency-badge">TMT</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="two-col mb-4">
                                    <div>
                                        <label class="form-label fw-bold text-muted">{{__("app.label_manufacturer")}}</label>
                                        <input type="text" name="manufacturer" class="form-control form-control-lg custom-input"
                                            placeholder='{{__("app.placeholder_manufacturer")}}' required>
                                    </div>
                                    <div>
                                        <label class="form-label fw-bold text-muted">{{ __('app.label_category') }}</label>
                                        <select name="category" class="form-select form-select-lg custom-input">
                                            <option value="" selected disabled>{{ __('app.all_categories') }}</option>
                                            @foreach($categories as $categoryName)
                                            <option value="{{ $categoryName }}">
                                                {{ __('app.cat_' . strtolower(str_replace(' ', '_', $categoryName))) }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- ── RIGHT COLUMN ── --}}
                            <div class="form-col-right">
                                <div class="right-panel-card mb-4">
                                    <h5 class="section-title">
                                        <div class="section-icon"><i class="bi bi-truck" style="color: #107A84;"></i></div>
                                        Logistics & Pricing
                                    </h5>

                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-muted small text-uppercase">Original Price ($)</label>
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text bg-white border-end-0 rounded-start-3" style="color: #107A84;">$</span>
                                            <input type="number" name="price" id="price-input"
                                                class="form-control border-start-0 bg-light rounded-end-3"
                                                placeholder="0.00" step="0.01" required style="font-weight: 600;">
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-muted small text-uppercase">Discount (%)</label>
                                        <div class="input-group input-group-lg">
                                            <input type="number" name="discount" id="discount-input"
                                                class="form-control border-end-0 bg-light rounded-start-3"
                                                placeholder="0" min="0" max="100" value="0" style="font-weight: 600;">
                                            <span class="input-group-text bg-white border-start-0 rounded-end-3" style="color: #107A84;">%</span>
                                        </div>
                                    </div>

                                    <div class="price-calc-box mb-4">
                                        <div>
                                            <p class="text-muted small mb-0">Final Customer Price</p>
                                            <h3 class="fw-bold mb-0" style="color: #107A84;" id="calculated-value">$0.00</h3>
                                        </div>
                                        <div id="discount-status">
                                            <span class="badge rounded-pill bg-white text-muted border px-3 py-2 fw-normal">No Sale</span>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-muted">{{__("app.label_prod_date")}}</label>
                                        <div class="input-group">
                                            <span class="input-group-text border-0 bg-light rounded-start-3"><i class="bi bi-calendar-event"></i></span>
                                            <input type="date" name="produced_date" class="form-control border-0 bg-light rounded-end-3" required>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-muted">{{__("app.label_exp_date")}}</label>
                                        <div class="input-group">
                                            <span class="input-group-text border-0 bg-light rounded-start-3"><i class="bi bi-calendar-x"></i></span>
                                            <input type="date" name="expiry_date" class="form-control border-0 bg-light rounded-end-3" required>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-muted">{{__("app.label_barcode")}}</label>
                                        <div class="input-group">
                                            <span class="input-group-text border-0 bg-light rounded-start-3"><i class="bi bi-upc-scan"></i></span>
                                            <input type="text" name="barcode"
                                                class="form-control border-0 bg-light rounded-end-3"
                                                placeholder='{{__("app.help_barcode")}}'
                                                value="{{ old('barcode') }}">
                                        </div>
                                        <small class="text-muted">{{__("app.help_barcode")}}</small>
                                    </div>
                                </div>

                                <button type="submit" class="btn-save-medicine">
                                    <i class="bi bi-check2-circle me-2"></i> {{__("app.btn_save_medicine")}}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const priceInput = document.getElementById('price-input');
        const discountInput = document.getElementById('discount-input');
        const resultDisplay = document.getElementById('calculated-value');
        const discountStatus = document.getElementById('discount-status');

        function calculateDiscount() {
            const originalPrice = parseFloat(priceInput.value) || 0;
            const discountPercent = parseInt(discountInput.value) || 0;

            if (discountPercent > 100) discountInput.value = 100;
            if (discountPercent < 0) discountInput.value = 0;

            if (discountPercent > 0 && originalPrice > 0) {
                const finalPrice = originalPrice * (1 - discountPercent / 100);
                resultDisplay.innerText = `$${finalPrice.toFixed(2)}`;
                discountStatus.innerHTML = `<span class="badge rounded-pill" style="background-color: #107A84;"><i class="bi bi-fire me-1"></i> ${discountPercent}% OFF</span>`;
            } else {
                resultDisplay.innerText = `$${originalPrice.toFixed(2)}`;
                discountStatus.innerHTML = `<span class="badge rounded-pill bg-white text-muted border px-3 py-2 fw-normal">No Sale</span>`;
            }
        }

        if (priceInput) priceInput.addEventListener('input', calculateDiscount);
        if (discountInput) discountInput.addEventListener('input', calculateDiscount);
    });
</script>

<style>
    /* ── Layout ─────────────────────────────────────────── */
    .desktop-app-layout {
        display: flex;
        width: 100%;
        overflow: hidden;
    }

    .app-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        background-color: #f4f7f6;
        min-width: 0;
        overflow: hidden;
    }

    /* ── Header ─────────────────────────────────────────── */
    .main-header {
        height: 70px;
        background: white;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        flex-shrink: 0;
        display: flex;
        align-items: center;
        padding: 0 24px;
        gap: 16px;
    }

    .btn-back {
        width: 40px;
        height: 40px;
        min-width: 40px;
        border-radius: 12px;
        background: white;
        border: 1px solid rgba(0, 0, 0, 0.08);
        color: #718096;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: 0.2s;
        flex-shrink: 0;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.03);
    }

    .btn-back:hover {
        color: #107A84;
        transform: translateX(-2px);
    }

    /* ── Workspace ──────────────────────────────────────── */
    .workspace {
        flex: 1;
        overflow-y: auto;
        padding: 24px;
    }

    .form-container {
        max-width: 1100px;
        margin: 0 auto;
    }

    .form-card {
        border-radius: 24px;
        padding: 32px;
    }

    /* ── Form grid ──────────────────────────────────────── */
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 32px;
        align-items: start;
    }

    .section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 700;
        color: #107A84;
        margin-bottom: 24px;
        font-size: 1rem;
    }

    .section-icon {
        width: 36px;
        height: 36px;
        background: #f0f9fa;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* ── Inputs ─────────────────────────────────────────── */
    .custom-input {
        border: 0;
        background: #f8fafc;
        border-radius: 14px !important;
        padding: 12px 16px;
    }

    .custom-input:focus {
        background: #fff;
        box-shadow: 0 0 0 3px rgba(16, 122, 132, 0.2);
    }

    .two-col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    /* Inventory grid */
    .inventory-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    .premium-input-group {
        background: #fff;
        padding: 14px;
        border-radius: 16px;
        border: 1px solid #f1f3f5;
        transition: all 0.3s ease;
    }

    .premium-input-group:focus-within {
        border-color: #107A84;
        box-shadow: 0 6px 20px rgba(16, 122, 132, 0.08);
        transform: translateY(-2px);
    }

    .input-label {
        display: block;
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        color: #a0aec0;
        margin-bottom: 8px;
    }

    .text-primary {
        color: #107A84 !important;
    }

    .input-modern-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-modern-wrapper .form-control {
        border: 2px solid #edf2f7;
        border-radius: 10px;
        padding: 10px 14px;
        font-weight: 600;
        font-size: 0.95rem;
        color: #2d3748;
        transition: all 0.2s;
    }

    .input-modern-wrapper .form-control:focus {
        background: rgba(16, 122, 132, 0.03);
        border-color: #107A84;
        box-shadow: none;
    }

    .unit-badge,
    .currency-badge {
        position: absolute;
        right: 10px;
        background: #f8fafc;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 0.7rem;
        font-weight: 800;
        color: #718096;
        border: 1px solid #e2e8f0;
        pointer-events: none;
    }

    /* Right panel */
    .right-panel-card {
        background: #fdfdfd;
        border: 1px solid #f0f0f0;
        border-radius: 20px;
        padding: 24px;
    }

    .price-calc-box {
        background: #f8fbfb;
        border-radius: 14px;
        padding: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .btn-save-medicine {
        width: 100%;
        padding: 16px;
        background: #107A84;
        color: white;
        border: none;
        border-radius: 16px;
        font-size: 1.05rem;
        font-weight: 700;
        cursor: pointer;
        transition: 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-save-medicine:hover {
        background: #0c5e66;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(16, 122, 132, 0.2);
    }

    /* ── Scrollbar ──────────────────────────────────────── */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: 10px;
    }

    /* ══════════════════════════════════════════════════════
   RESPONSIVE — TABLET
══════════════════════════════════════════════════════ */
    @media (max-width: 1100px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-col-right {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .btn-save-medicine {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 900px) {
        .inventory-grid {
            grid-template-columns: 1fr 1fr;
        }

        .form-col-right {
            grid-template-columns: 1fr;
        }
    }

    /* ══════════════════════════════════════════════════════
   RESPONSIVE — MOBILE
══════════════════════════════════════════════════════ */
    @media (max-width: 767px) {
        .app-main {
            overflow-y: auto;
        }

        .desktop-app-layout {
            overflow: auto;
        }

        .main-header {
            padding: 0 15px 0 70px;
            height: 60px;
        }

        .workspace {
            padding: 12px;
        }

        .form-card {
            padding: 16px;
            border-radius: 16px;
        }

        .form-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .two-col {
            grid-template-columns: 1fr;
        }

        .inventory-grid {
            grid-template-columns: 1fr;
        }

        .right-panel-card {
            padding: 16px;
        }
    }

    @media print {

        .sidebar-wrapper,
        .main-header {
            display: none !important;
        }
    }
</style>
@endsection