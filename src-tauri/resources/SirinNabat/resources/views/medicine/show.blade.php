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
                <h4 class="fw-bold mb-0" style="color: #107A84; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 40vw;">{{ $medicine->name }}</h4>
                <p class="text-muted small mb-0 d-none d-md-block">{{ __('app.medicine_details_subtitle') }}</p>
            </div>
            <div class="header-actions ms-auto">
                <a href="{{ route('storage.edit', $storage->id) }}" class="btn btn-teal-sm">
                    <i class="bi bi-pencil-square me-1"></i>
                    <span class="d-none d-md-inline">{{ __('app.btn_edit_medicine') }}</span>
                </a>
            </div>
        </header>

        <div class="workspace custom-scrollbar">
            <div class="detail-container">

                {{-- ── TOP SECTION ── --}}
                <div class="detail-grid">

                    {{-- LEFT: Main info card --}}
                    <div class="main-info-card panel-card">
                        <div class="med-profile">
                            <div class="med-price-block">
                                @if($medicine->discount > 0)
                                    <h2 class="text-danger fw-bold mb-1">${{ $medicine->discounted_price }}</h2>
                                    <span class="text-muted text-decoration-line-through d-block">${{ number_format($medicine->price, 2) }}</span>
                                    <small class="text-uppercase fw-bold text-muted d-block mb-1">
                                        <i class="bi bi-capsule-pill me-1" style="color: #107A84;"></i> {{ $medicine->price_unit }}
                                    </small>
                                    <span class="badge bg-danger">-{{ $medicine->discount }}% OFF</span>
                                @else
                                    <h2 class="fw-bold text-dark mb-0">${{ number_format($medicine->price, 2) }}</h2>
                                @endif
                                <span class="badge category-badge mt-2">{{ $medicine->category }}</span>
                            </div>
                        </div>

                        <div class="med-description">
                            <h5 class="fw-bold mb-3">{{ __('app.label_description_title') }}</h5>
                            <p class="text-secondary lh-lg">
                                {{ $medicine->description ?? 'Описание для данного препарата временно отсутствует в базе данных.' }}
                            </p>

                            <div class="meta-grid">
                                <div class="meta-item">
                                    <span class="meta-label">{{ __('app.label_manufacturer_caps') }}</span>
                                    <div class="meta-value"><i class="bi bi-building me-2" style="color: #107A84;"></i>{{ $medicine->manufacturer }}</div>
                                </div>
                                <div class="meta-item">
                                    <span class="meta-label">ID {{ __('app.label_registration_id') }}</span>
                                    <div class="meta-value"><i class="bi bi-hash me-1" style="color: #107A84;"></i>#{{ str_pad($medicine->id, 6, '0', STR_PAD_LEFT) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT: Sidebar info --}}
                    <div class="side-info">

                        {{-- Stock card --}}
                        <div class="panel-card stock-card">
                            <span class="card-label">In Stock</span>
                            <div class="stock-display">
                                <i class="bi bi-box-seam stock-icon"></i>
                                <div>
                                    <span class="stock-number">{{ $boxes }}</span>
                                    <span class="stock-unit">boxes</span>
                                    @if($remainingUnits > 0)
                                        <span class="mx-1 text-muted">+</span>
                                        <span class="stock-number text-info">{{ $remainingUnits }}</span>
                                        <span class="stock-unit">units</span>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-1 small text-muted">Total: {{ $storage->quantity }} pcs</div>
                        </div>

                        {{-- Dates card --}}
                        <div class="panel-card dates-card">
                            <h6 class="fw-bold text-teal mb-3">{{ __('app.sidebar_dates_title') }}</h6>

                            <div class="mb-3">
                                <span class="card-label">{{ __('app.label_production_date_short') }}</span>
                                <div class="date-box date-box-green">
                                    <i class="bi bi-calendar-check fs-5 text-success"></i>
                                    <span class="fw-bold">{{ $medicine->produced_date }}</span>
                                </div>
                            </div>

                            <div>
                                <span class="card-label">{{ __('app.label_expiry_date_short') }}</span>
                                <div class="date-box date-box-red">
                                    <i class="bi bi-calendar-x fs-5 text-danger"></i>
                                    <span class="fw-bold text-danger">{{ $medicine->expiry_date }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="action-buttons">
                            <a href="{{ route('storage.edit', $storage->id) }}" class="btn-primary-action">
                                <i class="bi bi-pencil-square me-2"></i> {{ __('app.btn_edit_medicine') }}
                            </a>
                            <button class="btn-danger-action">
                                <i class="bi bi-trash3 me-2"></i> {{ __('app.btn_remove_medicine') }}
                            </button>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </main>
</div>

<style>
/* ── Layout ─────────────────────────────────────────── */
.desktop-app-layout { display: flex; width: 100%; overflow: hidden; }
.app-main { flex: 1; display: flex; flex-direction: column; background-color: #f4f7f6; min-width: 0; overflow: hidden; }

/* ── Header ─────────────────────────────────────────── */
.main-header {
    height: 70px; background: white;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    flex-shrink: 0; display: flex; align-items: center;
    padding: 0 24px; gap: 16px;
}

.btn-back {
    width: 40px; height: 40px; min-width: 40px;
    border-radius: 12px; background: white;
    border: 1px solid rgba(0,0,0,0.08); color: #718096;
    display: flex; align-items: center; justify-content: center;
    text-decoration: none; transition: 0.2s; flex-shrink: 0;
}
.btn-back:hover { color: #107A84; transform: translateX(-2px); }

.btn-teal-sm {
    background: #107A84; color: white;
    border: none; border-radius: 10px;
    padding: 8px 16px; font-weight: 600; font-size: 0.85rem;
    display: flex; align-items: center; text-decoration: none;
    transition: 0.2s; white-space: nowrap;
}
.btn-teal-sm:hover { background: #0c5e66; color: white; }

/* ── Workspace ──────────────────────────────────────── */
.workspace { flex: 1; overflow-y: auto; padding: 24px; }
.detail-container { max-width: 1100px; margin: 0 auto; }

.detail-grid {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 20px;
    align-items: start;
}

/* ── Panel Card ─────────────────────────────────────── */
.panel-card {
    background: white; border-radius: 20px;
    border: 1px solid rgba(0,0,0,0.04);
    box-shadow: 0 4px 15px rgba(0,0,0,0.04);
    animation: fadeInUp 0.5s ease;
}

/* ── Main Info Card ─────────────────────────────────── */
.main-info-card { padding: 0; overflow: hidden; }

.med-profile {
    display: flex;
    align-items: center;
    gap: 24px;
    padding: 32px;
    border-bottom: 1px solid #f4f7f7;
}


.med-icon-placeholder { font-size: 60px; color: #cbd5e0; }

.med-description { padding: 28px 32px; }

.meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 20px; }
.meta-item {}
.meta-label { font-size: 0.62rem; text-transform: uppercase; font-weight: 800; color: #a0aec0; letter-spacing: 0.5px; display: block; margin-bottom: 4px; }
.meta-value { font-size: 0.95rem; font-weight: 700; color: #2d3748; display: flex; align-items: center; }

/* ── Side cards ─────────────────────────────────────── */
.side-info { display: flex; flex-direction: column; gap: 16px; }

.stock-card { padding: 20px; border-left: 4px solid #107A84; }
.card-label { font-size: 0.62rem; text-transform: uppercase; font-weight: 800; color: #a0aec0; letter-spacing: 0.5px; display: block; margin-bottom: 10px; }
.stock-display { display: flex; align-items: center; gap: 12px; }
.stock-icon { font-size: 1.8rem; color: #107A84; }
.stock-number { font-size: 1.6rem; font-weight: 800; color: #2d3748; }
.stock-unit { font-size: 0.8rem; color: #718096; margin-left: 4px; }

.dates-card { padding: 20px; }
.text-teal { color: #107A84; }

.date-box {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 16px; border-radius: 12px;
}
.date-box-green { background: rgba(72, 187, 120, 0.08); }
.date-box-red { background: rgba(220, 53, 69, 0.08); }

/* Badges */
.category-badge { background: #107A84; color: white; padding: 6px 14px; border-radius: 20px; font-size: 0.8rem; display: inline-block; }

/* Action buttons */
.action-buttons { display: flex; flex-direction: column; gap: 10px; }

.btn-primary-action {
    display: flex; align-items: center; justify-content: center;
    width: 100%; padding: 14px;
    background: #107A84; color: white;
    border: none; border-radius: 14px;
    font-weight: 700; font-size: 0.95rem;
    cursor: pointer; transition: 0.2s;
    text-decoration: none;
}
.btn-primary-action:hover { background: #0c5e66; color: white; transform: translateY(-1px); box-shadow: 0 6px 15px rgba(16,122,132,0.2); }

.btn-danger-action {
    display: flex; align-items: center; justify-content: center;
    width: 100%; padding: 14px;
    background: #fff5f5; color: #e53e3e;
    border: 1.5px solid #feb2b2; border-radius: 14px;
    font-weight: 700; font-size: 0.95rem;
    cursor: pointer; transition: 0.2s;
}
.btn-danger-action:hover { background: #e53e3e; color: white; }

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(16px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ── Scrollbar ──────────────────────────────────────── */
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 10px; }

/* ══════════════════════════════════════════════════════
   RESPONSIVE — TABLET
══════════════════════════════════════════════════════ */
@media (max-width: 1023px) {
    .detail-grid { grid-template-columns: 1fr; }
    .side-info { display: grid; grid-template-columns: 1fr 1fr; }
    .action-buttons { grid-column: 1 / -1; flex-direction: row; }
    .med-profile { padding: 20px; }
    .med-description { padding: 20px; }
}

/* ══════════════════════════════════════════════════════
   RESPONSIVE — MOBILE
══════════════════════════════════════════════════════ */
@media (max-width: 767px) {
    .app-main { overflow-y: auto; }
    .desktop-app-layout { overflow: auto; }
    .main-header { padding: 0 15px 0 70px; height: 60px; }
    .workspace { padding: 12px; }

    .med-profile { flex-direction: column; text-align: center; padding: 20px; }
    
    .med-icon-placeholder { font-size: 44px; }
    .meta-grid { grid-template-columns: 1fr; }

    .side-info { display: flex; flex-direction: column; }
    .action-buttons { flex-direction: column; }
}

@media print {
    .sidebar-wrapper, .main-header { display: none !important; }
    .desktop-app-layout { display: block; }
    body { background: white; }
}
</style>
@endsection