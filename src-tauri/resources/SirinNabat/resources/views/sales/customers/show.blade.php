@extends('layouts.app')

@section('content')
<div class="desktop-app-layout">
    @include('app.navbar')

    <main class="app-main bg-light-mesh">
        <header class="main-header d-print-none">
            <a href="{{ route('sales.customers.index') }}" class="btn-back">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div class="lh-sm">
                <h4 class="mb-0 fw-bold text-dark">Детали транзакции</h4>
                <p class="text-muted small mb-0 d-none d-md-block">Просмотр и печать чека #{{ $transaction_id }}</p>
            </div>
            <div class="ms-auto">
                <button onclick="window.print()" class="btn-print-action">
                    <i class="bi bi-printer-fill me-1 me-md-2"></i>
                    <span class="d-none d-md-inline">Распечатать</span>
                </button>
            </div>
        </header>

        <div class="workspace overflow-auto">
            <div class="receipt-container">
                <div class="receipt-card animate-slide-up">

                    {{-- Receipt Header --}}
                    <div class="receipt-header">
                        <div class="receipt-brand">
                            <i class="bi bi-heart-pulse-fill me-2"></i>SirinNabat
                        </div>
                        <div class="receipt-meta-grid">
                            <div>
                                <div class="receipt-label">ID ТРАНЗАКЦИИ</div>
                                <div class="receipt-value text-teal">#{{ $transaction_id }}</div>
                            </div>
                            <div class="text-md-end">
                                <div class="receipt-label">ДАТА</div>
                                <div class="receipt-value">{{ $orderDate->format('d.m.Y') }}</div>
                                <div class="receipt-time">{{ $orderDate->format('H:i:s') }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Items table --}}
                    <div class="receipt-body">
                        {{-- Desktop --}}
                        <table class="table receipt-table m-0 d-none d-sm-table">
                            <thead>
                                <tr>
                                    <th class="ps-4">Наименование товара</th>
                                    <th class="text-center">Кол-во</th>
                                    <th class="text-center">Цена</th>
                                    <th class="text-end pe-4">Итого</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $item->medicine->name ?? 'Товар удален' }}</div>
                                        <div class="text-muted extra-small">Артикул: {{ $item->medicine_id }}</div>
                                    </td>
                                    <td class="text-center fw-medium">{{ $item->quantity }}</td>
                                    <td class="text-center text-muted">{{ number_format($item->price, 2) }}</td>
                                    <td class="text-end pe-4 fw-bold text-dark">{{ number_format($item->total_price, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        {{-- Mobile --}}
                        <div class="d-sm-none receipt-items-mobile">
                            @foreach($items as $item)
                            <div class="receipt-item-row">
                                <div class="item-name">{{ $item->medicine->name ?? 'Товар удален' }}</div>
                                <div class="item-meta">{{ $item->quantity }} шт × {{ number_format($item->price, 2) }}</div>
                                <div class="item-total fw-bold text-teal">{{ number_format($item->total_price, 2) }} TMT</div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Receipt Footer --}}
                    <div class="receipt-footer">
                        <div class="totals-block">
                            <div class="total-row">
                                <span class="text-muted">Промежуточный итог:</span>
                                <span class="fw-medium">{{ number_format($total, 2) }}</span>
                            </div>
                            <div class="total-row">
                                <span class="text-muted">Скидка:</span>
                                <span class="fw-medium text-danger">0.00</span>
                            </div>
                            <div class="total-divider"></div>
                            <div class="total-row grand-total">
                                <span class="fw-bold text-uppercase">К ОПЛАТЕ:</span>
                                <span class="grand-amount text-teal">{{ number_format($total, 2) }} <small>TMT</small></span>
                            </div>
                        </div>

                        <div class="receipt-watermark">
                            <p class="mb-0 opacity-25 small fw-bold text-uppercase">Благодарим за покупку!</p>
                            <div class="receipt-barcode opacity-50">|| ||| || |||| || ||| |</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>
</div>

<style>
:root { --primary: #107A84; --primary-dark: #0c5e66; --text-muted-color: #718096; }

/* ── Layout ─────────────────────────────────────────── */
.desktop-app-layout { display: flex; width: 100%; overflow: hidden; }
.app-main { flex: 1; display: flex; flex-direction: column; min-width: 0; position: relative; overflow: hidden; }

.bg-light-mesh {
    background-color: #f4f7f7;
    
    background-size: 30px 30px;
}

/* ── Header ─────────────────────────────────────────── */
.main-header {
    height: 70px;
    background: rgba(244, 247, 247, 0.85);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    display: flex; align-items: center;
    padding: 0 24px; gap: 14px;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    flex-shrink: 0; z-index: 10;
}

.btn-back {
    width: 40px; height: 40px; min-width: 40px;
    border-radius: 12px; background: white;
    border: 1px solid rgba(0,0,0,0.06); color: var(--text-muted-color);
    display: flex; align-items: center; justify-content: center;
    text-decoration: none; transition: 0.2s;
    box-shadow: 0 2px 5px rgba(0,0,0,0.04); flex-shrink: 0;
}
.btn-back:hover { color: var(--primary); transform: translateX(-2px); }

.btn-print-action {
    background: var(--primary); color: white;
    font-weight: 700; border-radius: 12px;
    padding: 9px 20px; border: none; transition: 0.3s;
    display: flex; align-items: center; gap: 6px; white-space: nowrap;
}
.btn-print-action:hover { background: var(--primary-dark); transform: translateY(-2px); box-shadow: 0 6px 16px rgba(16,122,132,0.2); }

/* ── Workspace ──────────────────────────────────────── */
.workspace { flex: 1; padding: 24px; }
.receipt-container { max-width: 780px; margin: 0 auto; }

/* ── Receipt Card ───────────────────────────────────── */
.receipt-card {
    background: white; border-radius: 28px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.07);
    overflow: hidden;
}

.receipt-header {
    padding: 36px 40px;
    border-bottom: 2px dashed #edf2f7;
}

.receipt-brand {
    font-size: 1.3rem; font-weight: 800; color: var(--primary);
    margin-bottom: 20px; display: flex; align-items: center;
}

.receipt-meta-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: 16px;
}

.receipt-label { font-size: 0.62rem; text-transform: uppercase; font-weight: 800; color: #a0aec0; letter-spacing: 0.5px; margin-bottom: 4px; }
.receipt-value { font-size: 1.1rem; font-weight: 800; }
.receipt-time { font-size: 0.85rem; color: #a0aec0; }
.text-teal { color: var(--primary); }

/* ── Table ──────────────────────────────────────────── */
.receipt-table thead th {
    background: #fcfdfe; color: #a0aec0;
    text-transform: uppercase; font-size: 0.68rem; font-weight: 800;
    padding: 18px; border: none;
}
.receipt-table td { padding: 20px; border-bottom: 1px solid #f8fafc; }
.extra-small { font-size: 0.72rem; }

/* ── Mobile items ───────────────────────────────────── */
.receipt-items-mobile { padding: 0 16px; }
.receipt-item-row {
    display: grid; grid-template-columns: 1fr auto;
    grid-template-rows: auto auto;
    gap: 2px 8px; padding: 14px 0;
    border-bottom: 1px solid #f8fafc;
}
.item-name { font-weight: 700; font-size: 0.9rem; grid-column: 1; }
.item-meta { font-size: 0.75rem; color: #a0aec0; grid-column: 1; }
.item-total { font-size: 0.9rem; grid-column: 2; grid-row: 1 / 3; align-self: center; text-align: right; }

/* ── Footer ─────────────────────────────────────────── */
.receipt-footer { padding: 28px 40px; }

.totals-block { max-width: 340px; margin-left: auto; }

.total-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
.grand-total { padding-top: 4px; }
.total-divider { border-top: 2px solid #edf2f7; margin: 12px 0; }
.grand-amount { font-size: 1.5rem; font-weight: 800; }

.receipt-watermark { text-align: center; margin-top: 32px; }
.receipt-barcode { font-family: monospace; font-size: 0.7rem; letter-spacing: 2px; }

/* ── Animation ──────────────────────────────────────── */
.animate-slide-up { animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1); }
@keyframes slideUp { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }

/* ══════════════════════════════════════════════════════
   RESPONSIVE — TABLET
══════════════════════════════════════════════════════ */
@media (max-width: 1023px) {
    .receipt-header { padding: 24px 28px; }
    .receipt-footer { padding: 24px 28px; }
}

/* ══════════════════════════════════════════════════════
   RESPONSIVE — MOBILE
══════════════════════════════════════════════════════ */
@media (max-width: 767px) {
    .app-main { overflow-y: auto; }
    .desktop-app-layout { overflow: auto; }
    .main-header { padding: 0 15px 0 70px; height: 60px; }
    .workspace { padding: 12px; }

    .receipt-card { border-radius: 18px; }
    .receipt-header { padding: 20px; }
    .receipt-meta-grid { grid-template-columns: 1fr; gap: 12px; }
    .receipt-footer { padding: 16px 20px; }
    .totals-block { max-width: 100%; }
}

/* ── Print ──────────────────────────────────────────── */
@media print {
    .sidebar-wrapper, .main-header { display: none !important; }
    .desktop-app-layout { display: block; }
    body { background: white; }
    .receipt-card { box-shadow: none; border: 1px solid #eee; border-radius: 0; }
    .bg-light-mesh { background: white; }
}
</style>
@endsection