@extends('layouts.app')

@section('content')
<div class="desktop-app-layout">
    @include('app.navbar')

    <main class="app-main">
        <header class="main-header">
            <div class="header-info">
                <div class="stat-icon bg-teal-light">
                    <i class="bi bi-clock-history text-teal"></i>
                </div>
                <div class="ms-3">
                    <h4 class="mb-0 fw-bold">История заказов</h4>
                    <p class="text-muted small mb-0 d-none d-md-block">Мониторинг всех транзакций SirinNabat</p>
                </div>
            </div>

            <div class="header-stats ms-auto">
                <div class="mini-stat">
                    <span class="label">Заказов</span>
                    <span class="value">{{ count($orders) }}</span>
                </div>
                <div class="mini-stat d-none d-md-flex">
                    <span class="label">Выручка</span>
                    <span class="value text-teal">{{ number_format($orders->sum('total_sum'), 2) }} <small>TMT</small></span>
                </div>
            </div>
            
            <div class="system-status">
                <span class="dot pulse"></span>
                <span class="d-none d-lg-inline">LIVE</span>
            </div>
        </header>

        <div class="workspace">
            <div class="orders-container">
                
                <div class="panel-card shadow-sm">
                    <div class="panel-header">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-list-ul me-2 text-teal"></i>
                            Журнал транзакций
                        </h5>
                        <div class="search-box">
                            <i class="bi bi-search"></i>
                            <input type="text" id="searchInput" placeholder="Поиск по ID..." class="form-control form-control-sm">
                        </div>
                    </div>

                    {{-- Mobile: card list view --}}
                    <div class="orders-card-list d-md-none">
                        @forelse($orders as $order)
                        <div class="order-mobile-card">
                            <div class="order-mobile-left">
                                <div class="transaction-id">
                                    <span class="hash">#</span>{{ $order->transaction_id }}
                                </div>
                                <div class="order-time">
                                    {{ \Carbon\Carbon::parse($order->order_time)->format('H:i • d M Y') }}
                                </div>
                            </div>
                            <div class="order-mobile-right">
                                <div class="fw-bold text-teal">{{ number_format($order->total_sum, 2) }} <small>TMT</small></div>
                                <span class="badge badge-status-success mt-1">Выполнено</span>
                                @if($order->transaction_id)
                                <a href="{{ route('sales.customers.show', ['transaction_id' => $order->transaction_id]) }}" class="btn btn-detail btn-sm mt-2 d-block text-center">
                                    Детали <i class="bi bi-arrow-right-short"></i>
                                </a>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="empty-state text-center py-5">
                            <i class="bi bi-folder-x display-1 opacity-10 text-teal d-block mb-3"></i>
                            <p class="text-muted fw-medium">История заказов пока пуста</p>
                        </div>
                        @endforelse
                    </div>

                    {{-- Desktop: table view --}}
                    <div class="table-scroll-container d-none d-md-block">
                        <table class="table pos-table align-middle m-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">ID Транзакции</th>
                                    <th>Дата и время</th>
                                    <th class="text-center">Статус</th>
                                    <th class="text-center">Сумма чека</th>
                                    <th class="text-end pe-4">Действие</th>
                                </tr>
                            </thead>
                            <tbody id="ordersTableBody">
                                @forelse($orders as $order)
                                <tr>
                                    <td class="ps-4">
                                        <div class="transaction-id">
                                            <span class="hash">#</span>{{ $order->transaction_id }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ \Carbon\Carbon::parse($order->order_time)->format('H:i:s') }}</div>
                                        <div class="text-muted extra-small">{{ \Carbon\Carbon::parse($order->order_time)->format('d M Y') }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-status-success">
                                            <i class="bi bi-check-circle-fill me-1"></i> Выполнено
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold text-teal fs-5">
                                            {{ number_format($order->total_sum, 2) }} <small>TMT</small>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        @if($order->transaction_id)
                                        <a href="{{ route('sales.customers.show', ['transaction_id' => $order->transaction_id]) }}" class="btn btn-detail">
                                            Детали <i class="bi bi-arrow-right-short ms-1"></i>
                                        </a>
                                        @else
                                        <span class="badge bg-danger-soft text-danger">Ошибка ID</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <i class="bi bi-folder-x display-1 opacity-10 text-teal d-block mb-3"></i>
                                        <p class="text-muted fw-medium">История заказов пока пуста</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            // Filter table rows
            document.querySelectorAll('#ordersTableBody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
            });
            // Filter mobile cards
            document.querySelectorAll('.order-mobile-card').forEach(card => {
                card.style.display = card.textContent.toLowerCase().includes(query) ? '' : 'none';
            });
        });
    }
});
</script>

<style>
:root { --primary: #107A84; --primary-dark: #0c5e66; --teal-light: rgba(16, 122, 132, 0.1); }

/* ── Layout ─────────────────────────────────────────── */
.desktop-app-layout { display: flex; width: 100%; overflow: hidden; }
.app-main { flex: 1; display: flex; flex-direction: column; min-width: 0; background: #f4f7f7; overflow: hidden; }

/* ── Header ─────────────────────────────────────────── */
.main-header {
    height: 80px; background: white;
    display: flex; align-items: center; padding: 0 24px; gap: 16px;
    border-bottom: 1px solid #e2e8f0; flex-shrink: 0;
}

.header-info { display: flex; align-items: center; }
.header-stats { display: flex; gap: 20px; }
.mini-stat { display: flex; flex-direction: column; border-left: 2px solid #edf2f7; padding-left: 16px; }
.mini-stat .label { font-size: 0.65rem; text-transform: uppercase; color: #a0aec0; font-weight: 800; }
.mini-stat .value { font-size: 1rem; font-weight: 800; color: #2d3748; }

.bg-teal-light { background: var(--teal-light); border-radius: 12px; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.text-teal { color: var(--primary); }

.system-status { display: flex; align-items: center; gap: 6px; font-size: 0.8rem; font-weight: 700; color: #48bb78; background: rgba(72,187,120,0.1); padding: 7px 12px; border-radius: 20px; }
.dot.pulse { width: 8px; height: 8px; background: #48bb78; border-radius: 50%; animation: pulse-green 2s infinite; }

/* ── Workspace ──────────────────────────────────────── */
.workspace { flex: 1; overflow-y: auto; padding: 20px; }
.orders-container { height: 100%; }

/* ── Panel Card ─────────────────────────────────────── */
.panel-card { background: white; border-radius: 20px; overflow: hidden; }

.panel-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 16px 20px; border-bottom: 1px solid #f1f5f9;
    flex-wrap: wrap; gap: 12px;
}

/* ── Table ──────────────────────────────────────────── */
.table-scroll-container { overflow-y: auto; max-height: calc(100vh - 230px); }

.pos-table thead th {
    background: #f8fafc; color: #718096;
    font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px;
    padding: 14px 10px; border-bottom: 1px solid #edf2f7; position: sticky; top: 0; z-index: 2;
}
.pos-table tbody tr { transition: background 0.2s; }
.pos-table tbody tr:hover { background: #f1f7f7; }

.transaction-id { font-weight: 700; color: #4a5568; font-family: 'Monaco', 'Courier New', monospace; }
.transaction-id .hash { color: var(--primary); opacity: 0.5; margin-right: 2px; }
.extra-small { font-size: 0.72rem; }

.badge-status-success {
    background: #e6fffa; color: #319795;
    font-weight: 700; font-size: 0.72rem;
    padding: 5px 10px; border-radius: 8px;
}

.btn-detail {
    background: #edf2f7; color: #4a5568;
    font-weight: 700; font-size: 0.78rem;
    padding: 7px 14px; border-radius: 10px; border: none;
    transition: 0.2s; text-decoration: none; white-space: nowrap;
    display: inline-flex; align-items: center;
}
.btn-detail:hover { background: var(--primary); color: white; transform: translateY(-1px); }

/* ── Search ─────────────────────────────────────────── */
.search-box { position: relative; }
.search-box i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #a0aec0; z-index: 1; }
.search-box input { padding-left: 32px; border-radius: 10px; border: 1px solid #e2e8f0; width: 200px; }

/* ── Mobile cards ───────────────────────────────────── */
.orders-card-list { padding: 8px; }
.order-mobile-card {
    display: flex; justify-content: space-between; align-items: flex-start;
    padding: 14px 12px; border-bottom: 1px solid #f4f7f7;
    gap: 12px;
}
.order-mobile-card:last-child { border-bottom: none; }
.order-time { font-size: 0.72rem; color: #a0aec0; margin-top: 3px; }

@keyframes pulse-green {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(72, 187, 120, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(72, 187, 120, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(72, 187, 120, 0); }
}

/* ── Scrollbar ──────────────────────────────────────── */
.table-scroll-container::-webkit-scrollbar { width: 6px; }
.table-scroll-container::-webkit-scrollbar-track { background: transparent; }
.table-scroll-container::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 10px; }

/* ══════════════════════════════════════════════════════
   RESPONSIVE — TABLET
══════════════════════════════════════════════════════ */
@media (max-width: 1023px) {
    .main-header { padding: 0 16px; height: 70px; }
    .search-box input { width: 150px; }
}

/* ══════════════════════════════════════════════════════
   RESPONSIVE — MOBILE
══════════════════════════════════════════════════════ */
@media (max-width: 767px) {
    .app-main { overflow-y: auto; }
    .desktop-app-layout { overflow: auto; }
    .main-header { padding: 0 15px 0 70px; height: 60px; }
    .workspace { padding: 12px; overflow: visible; }
    .orders-container { height: auto; }
    .table-scroll-container { max-height: none; }
    .panel-header { padding: 12px; }
    .search-box input { width: 140px; }
}
</style>
@endsection