@extends('layouts.app')

@section('content')
<div class="desktop-app-layout">
    @include('app.navbar')

    <main class="app-main bg-light-mesh">

        <header class="main-header d-print-none">
            <div class="header-info">
                <div class="brand-icon-box">
                    <i class="bi bi-box-seam-fill text-white"></i>
                </div>
                <div class="ms-3">
                    <h4 class="mb-0 fw-bold text-dark">{{__("app.storage_account")}}</h4>
                    <p class="text-muted small mb-0 d-none d-md-block">{{__("app.inventory_subtitle")}}</p>
                </div>
            </div>

            <div class="header-actions ms-auto">
                <div class="quick-stat-pill d-none d-lg-flex">
                    <span class="pill-label">{{__("app.total_stock")}}:</span>
                    <span class="pill-value">{{ $storage->sum('quantity') }}</span>
                </div>
                <a href="{{ route('medicine.create') }}" class="btn-add-medicine">
                    <i class="bi bi-plus-lg"></i>
                    <span class="btn-label">{{__("app.btn_add_medicine")}}</span>
                </a>
            </div>
        </header>

        <div class="workspace">
            <div class="workspace-inner">

                {{-- Stat cards --}}
                <div class="stat-row">
                    <div class="stat-card">
                        <div class="stat-icon bg-teal-soft"><i class="bi bi-capsule"></i></div>
                        <div class="stat-data">
                            <div class="stat-label">{{__("app.stat_unique_pills")}}</div>
                            <div class="stat-value">{{ $storage->total() }}</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon bg-danger-soft"><i class="bi bi-exclamation-triangle"></i></div>
                        <div class="stat-data">
                            <div class="stat-label">{{__("app.stat_low_stock")}}</div>
                            <div class="stat-value text-danger">{{ $lowStockCount }}</div>
                        </div>
                    </div>
                </div>

                {{-- Search & Filters --}}
                <div class="filter-card">
                    <div class="filter-grid">
                        {{-- Live search --}}
                        <div class="search-wrapper">
                            <label class="field-label">{{__("app.search_label")}}</label>
                            <div class="search-input-group">
                                <i class="bi bi-search search-icon"></i>
                                <input type="text" id="medicine-search"
                                       class="search-input"
                                       placeholder="{{ __('app.search_placeholder') }}"
                                       autocomplete="off">
                            </div>
                            <div id="search-results" class="search-dropdown d-none"></div>
                        </div>

                        {{-- Category / Status filters --}}
                        <form action="{{ route('storage.index') }}" method="GET" class="filters-form">
                            <div class="filter-field">
                                <label class="field-label">{{__("app.filter_category")}}</label>
                                <select name="category" class="custom-select">
                                    <option value="">{{__("app.all_categories")}}</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat }}" @selected(request('category')==$cat)>{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="filter-field">
                                <label class="field-label">{{__("app.filter_status")}}</label>
                                <select name="status" class="custom-select">
                                    <option value="">{{__("app.all_stock")}}</option>
                                    <option value="low" @selected(request('status')=='low')>{{__("app.stat_low_stock")}}</option>
                                </select>
                            </div>
                            <div class="filter-actions">
                                <button type="submit" class="btn-filter">
                                    <i class="bi bi-funnel"></i>
                                </button>
                                <a href="{{ route('storage.index') }}" class="btn-reset">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Table (desktop) --}}
                <div class="table-card d-none d-md-block">
                    <div class="table-responsive">
                        <table class="table inventory-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">{{__("app.table_reference")}}</th>
                                    <th>{{__("app.table_identity")}}</th>
                                    <th>{{__("app.table_category")}}</th>
                                    <th class="text-center">{{__("app.table_quantity")}}</th>
                                    <th class="text-center">{{__("app.table_status")}}</th>
                                    <th class="text-end pe-4">Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($storage as $item)
                                <tr>
                                    <td class="ps-4">
                                        <span class="ref-id">#{{ str_pad($item->medicine_id, 4, '0', STR_PAD_LEFT) }}</span>
                                    </td>
                                    <td>
                                        <div class="med-identity">
                                            <div>
                                                <div class="fw-bold text-dark">{{ $item->medicine->name ?? '—' }}</div>
                                                <small class="text-muted">{{__("app.unit_managment")}}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="category-badge">{{ $item->category ?? 'Pharma' }}</span>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $boxes  = floor($item->quantity / ($item->medicine->units_per_box ?? 1));
                                            $sheets = $item->quantity % ($item->medicine->units_per_box ?? 1);
                                        @endphp
                                        <div class="qty-display">
                                            <span class="qty-boxes">{{ $boxes }} <small>box</small></span>
                                            @if($sheets > 0)
                                                <span class="qty-sheets">+{{ $sheets }} <small>u</small></span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="status-pill {{ $item->quantity < 10 ? 'low' : 'ok' }}">
                                            {{ $item->quantity < 10 ? __('app.status_low') : __('app.status_sufficient') }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="row-actions">
                                            <a href="{{ route('storage.edit', $item->id) }}" class="act-btn edit" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <form action="{{ route('storage.destroy', $item->id) }}" method="POST"
                                                  onsubmit="return confirm('Delete item?')" class="m-0">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="act-btn delete" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Card list (mobile) --}}
                <div class="mobile-list d-md-none">
                    @foreach($storage as $item)
                    @php
                        $boxes  = floor($item->quantity / ($item->medicine->units_per_box ?? 1));
                        $sheets = $item->quantity % ($item->medicine->units_per_box ?? 1);
                    @endphp
                    <div class="mobile-card">
                        <div class="mobile-card-left">
                            <div class="mobile-card-info">
                                <div class="fw-bold text-dark">{{ $item->medicine->name ?? '—' }}</div>
                                <span class="ref-id">#{{ str_pad($item->medicine_id, 4, '0', STR_PAD_LEFT) }}</span>
                                <div class="mt-1">
                                    <span class="category-badge">{{ $item->category ?? 'Pharma' }}</span>
                                    <span class="status-pill ms-1 {{ $item->quantity < 10 ? 'low' : 'ok' }}">
                                        {{ $item->quantity < 10 ? __('app.status_low') : __('app.status_sufficient') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="mobile-card-right">
                            <div class="qty-display mb-2">
                                <span class="qty-boxes">{{ $boxes }} <small>box</small></span>
                                @if($sheets > 0)
                                    <span class="qty-sheets">+{{ $sheets }}</span>
                                @endif
                            </div>
                            <div class="row-actions">
                                <a href="{{ route('storage.edit', $item->id) }}" class="act-btn edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('storage.destroy', $item->id) }}" method="POST"
                                      onsubmit="return confirm('Delete?')" class="m-0">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="act-btn delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="pagination-wrap">
                    {{ $storage->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </div>
    </main>
</div>

<style>
/* ── Variables ──────────────────────────────── */
:root {
    --primary: #107A84;
    --primary-soft: rgba(16,122,132,0.1);
    --bg-main: #f4f7f7;
    --border: #e8edf2;
    --text-dark: #2d3748;
    --text-muted: #718096;
}

/* ── Base ───────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; }
body { margin: 0; padding: 0; font-family: 'Inter', sans-serif; background: var(--bg-main); }

/* ── Desktop app layout — Tauri-safe ───────── */
/*
 * position:fixed + inset:0 is used instead of height:100vh
 * because Tauri's WebView calculates 100vh inconsistently
 * (may exclude titlebar or add bounce area on macOS).
 * fixed+inset is guaranteed to fill exactly the usable window.
 */
.desktop-app-layout {
    position: fixed;
    inset: 0;
    display: flex;
    overflow: hidden;
}

.desktop-app-layout .sidebar-wrapper {
    position: relative !important;
    flex-shrink: 0;
    height: 100%;
}

.app-main {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    height: 100%;
}

/* ── Mesh background ────────────────────────── */
.bg-light-mesh {
    background-color: var(--bg-main);
    background-size: 30px 30px;
}

/* ── Header ─────────────────────────────────── */
.main-header {
    height: 72px;
    background: rgba(244,247,247,0.85);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border-bottom: 1px solid rgba(0,0,0,0.05);
    display: flex; align-items: center;
    padding: 0 24px; gap: 16px;
    flex-shrink: 0; z-index: 10;
}

.header-info { display: flex; align-items: center; }

.brand-icon-box {
    width: 44px; height: 44px;
    background: var(--primary); border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 6px 14px rgba(16,122,132,0.22);
    flex-shrink: 0;
}

.header-actions { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }

.quick-stat-pill {
    background: white; padding: 0 18px; height: 40px;
    border-radius: 50px; display: flex; align-items: center; gap: 8px;
    border: 1px solid var(--border);
}
.pill-label { font-size: 0.62rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; }
.pill-value { font-weight: 800; color: var(--primary); font-size: 1.05rem; }

.btn-add-medicine {
    background: var(--primary); color: white;
    border: none; border-radius: 11px; padding: 9px 18px;
    font-weight: 700; font-size: 0.85rem;
    display: flex; align-items: center; gap: 6px;
    text-decoration: none; transition: 0.2s; white-space: nowrap;
}
.btn-add-medicine:hover { background: #0c5e66; color: white; transform: translateY(-1px); }

/* ── Workspace ──────────────────────────────── */
.workspace { flex: 1; overflow-y: auto; }
.workspace-inner { padding: 20px 24px; max-width: 1400px; margin: 0 auto; }

/* ── Stat row ───────────────────────────────── */
.stat-row { display: flex; gap: 16px; margin-bottom: 20px; }

.stat-card {
    background: white; border-radius: 18px; padding: 20px 24px;
    display: flex; align-items: center; gap: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    border: 1px solid var(--border); flex: 1;
}
.stat-icon {
    width: 48px; height: 48px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; flex-shrink: 0;
}
.bg-teal-soft { background: var(--primary-soft); color: var(--primary); }
.bg-danger-soft { background: rgba(220,53,69,0.1); color: #dc3545; }
.stat-label { font-size: 0.62rem; text-transform: uppercase; font-weight: 800; color: var(--text-muted); margin-bottom: 2px; }
.stat-value { font-size: 1.5rem; font-weight: 800; color: var(--text-dark); line-height: 1; }

/* ── Filter card ────────────────────────────── */
.filter-card {
    background: white; border-radius: 18px; padding: 20px 24px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    border: 1px solid var(--border); margin-bottom: 20px;
}
.filter-grid {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 20px; align-items: end;
}
.field-label {
    display: block; font-size: 0.62rem; text-transform: uppercase;
    font-weight: 800; color: var(--primary); letter-spacing: 0.5px; margin-bottom: 6px;
}

/* Search */
.search-wrapper { position: relative; }
.search-input-group {
    display: flex; align-items: center;
    background: #f8fafc; border: 1.5px solid var(--border);
    border-radius: 12px; overflow: hidden;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.search-input-group:focus-within {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(16,122,132,0.12);
    background: white;
}
.search-icon { padding: 0 12px; color: var(--text-muted); font-size: 0.9rem; }
.search-input {
    flex: 1; border: none; background: transparent;
    padding: 10px 12px 10px 0; font-size: 0.9rem; outline: none;
}

/* Filters form */
.filters-form {
    display: grid;
    grid-template-columns: 1fr 1fr auto;
    gap: 12px; align-items: end;
}
.filter-field {}
.filter-actions { display: flex; gap: 8px; }

.custom-select {
    border: 1.5px solid var(--border); border-radius: 12px;
    padding: 9px 14px; background: #f8fafc; font-size: 0.875rem;
    color: var(--text-dark); width: 100%; outline: none;
    transition: 0.2s;
}
.custom-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(16,122,132,0.12); background: white; }

.btn-filter, .btn-reset {
    height: 40px; width: 44px; border-radius: 11px; border: none;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; cursor: pointer; transition: 0.2s; text-decoration: none;
}
.btn-filter { background: var(--primary); color: white; }
.btn-filter:hover { background: #0c5e66; }
.btn-reset { background: #f1f5f9; color: var(--text-muted); border: 1.5px solid var(--border); }
.btn-reset:hover { background: #e2e8f0; }

/* ── Table card ─────────────────────────────── */
.table-card {
    background: white; border-radius: 18px; overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    border: 1px solid var(--border); margin-bottom: 20px;
}

.inventory-table thead th {
    background: #f8fafc; color: #a0aec0;
    text-transform: uppercase; font-size: 0.7rem;
    font-weight: 800; padding: 16px; border: none; white-space: nowrap;
}
.inventory-table tbody tr { transition: background 0.15s; }
.inventory-table tbody tr:hover { background: #fbfcfe; }
.inventory-table td { border-bottom: 1px solid #f4f7f7; padding: 14px 16px; }

.ref-id { font-size: 0.75rem; font-weight: 800; color: #94a3b8; font-family: monospace; }

.med-identity { display: flex; align-items: center; gap: 12px; }

.med-avatar .avatar-placeholder {
    width: 42px; height: 42px; border-radius: 11px; object-fit: cover; flex-shrink: 0;
}
.med-avatar .avatar-placeholder {
    background: #f1f5f9; color: #94a3b8;
    display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
}

.category-badge {
    display: inline-block;
    background: var(--primary); color: white;
    font-size: 0.7rem; font-weight: 700;
    padding: 4px 10px; border-radius: 7px;
}

.qty-display { display: flex; align-items: center; gap: 6px; justify-content: center; }
.qty-boxes { font-weight: 800; color: var(--primary); font-size: 1rem; }
.qty-sheets {
    background: var(--primary); color: white;
    padding: 2px 7px; border-radius: 5px; font-size: 0.7rem; font-weight: 700;
}

.status-pill {
    display: inline-block;
    padding: 4px 11px; border-radius: 50px;
    font-size: 0.7rem; font-weight: 800; text-transform: uppercase;
}
.status-pill.ok  { background: rgba(16,185,129,0.1); color: #10b981; }
.status-pill.low { background: rgba(239,68,68,0.1);  color: #ef4444; }

.row-actions { display: flex; gap: 6px; justify-content: flex-end; }

.act-btn {
    width: 34px; height: 34px; border-radius: 9px; border: none;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 0.85rem; cursor: pointer; transition: 0.15s; text-decoration: none;
}
.act-btn.edit   { background: #e0f2f1; color: var(--primary); }
.act-btn.delete { background: #fee2e2; color: #ef4444; }
.act-btn:hover  { transform: scale(1.1); }

/* ── Search dropdown ────────────────────────── */
.search-dropdown {
    position: absolute; width: 100%; top: calc(100% + 8px); left: 0;
    background: white; border-radius: 14px; z-index: 1000; overflow: hidden;
    border: 1px solid var(--border);
    box-shadow: 0 12px 30px rgba(0,0,0,0.1);
}

/* ── Mobile cards ───────────────────────────── */
.mobile-list { margin-bottom: 20px; }

.mobile-card {
    background: white; border-radius: 16px; padding: 14px 16px;
    display: flex; justify-content: space-between; align-items: center;
    gap: 12px; margin-bottom: 10px;
    border: 1px solid var(--border);
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
}
.mobile-card-left { display: flex; align-items: center; gap: 12px; min-width: 0; }
.mobile-card-info { min-width: 0; }
.mobile-card-info .fw-bold { font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 160px; }
.mobile-card-right { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; flex-shrink: 0; }

/* ── Pagination ─────────────────────────────── */
.pagination-wrap {
    display: flex; justify-content: center; padding: 8px 0 24px;
}
.pagination-wrap .page-link {
    border-radius: 50% !important; margin: 0 3px;
    width: 38px; height: 38px;
    display: flex; align-items: center; justify-content: center;
    color: var(--primary); border: none; background: white;
    font-weight: 600;
}
.pagination-wrap .page-item.active .page-link {
    background: var(--primary) !important; color: white;
}

/* ── Scrollbar ──────────────────────────────── */
.workspace::-webkit-scrollbar { width: 6px; }
.workspace::-webkit-scrollbar-track { background: transparent; }
.workspace::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 10px; }

/* ══════════════════════════════════════════════
   RESPONSIVE — TABLET (768–1023px)
══════════════════════════════════════════════ */
@media (max-width: 1023px) {
    .filter-grid { grid-template-columns: 1fr; gap: 14px; }
    .filters-form { grid-template-columns: 1fr 1fr auto; }
    .workspace-inner { padding: 16px; }
}

@media (max-width: 900px) {
    .filters-form { grid-template-columns: 1fr; }
    .filter-actions { justify-content: flex-start; }
}

/* ══════════════════════════════════════════════
   RESPONSIVE — MOBILE (<768px)
══════════════════════════════════════════════ */
@media (max-width: 767px) {
    /* On mobile, let the page scroll naturally */
    .desktop-app-layout {
        position: relative !important;
        inset: auto !important;
        min-height: 100vh;
        height: auto !important;
        flex-direction: column;
        overflow: auto !important;
    }
    .desktop-app-layout .sidebar-wrapper { position: fixed !important; }
    .app-main { height: auto !important; overflow: auto !important; }

    /* Header — make room for hamburger */
    .main-header { padding: 0 14px 0 68px; height: 60px; }
    .main-header h4 { font-size: 1rem; }
    .btn-label { display: none; }
    .btn-add-medicine { padding: 9px 12px; }

    .workspace { overflow: visible; }
    .workspace-inner { padding: 12px; }

    .stat-row { gap: 10px; }
    .stat-card { padding: 14px 16px; }
    .stat-value { font-size: 1.2rem; }

    .filter-card { padding: 14px; }
    .filter-grid { grid-template-columns: 1fr; gap: 12px; }
    .filters-form { grid-template-columns: 1fr 1fr; gap: 10px; }
    .filter-actions { grid-column: 1 / -1; }
}

/* ── Print ──────────────────────────────────── */
@media print {
    .sidebar-wrapper, .main-header, .filter-card { display: none !important; }
    .desktop-app-layout { display: block; }
    .app-main { overflow: visible; }
    body { background: white; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input   = document.getElementById('medicine-search');
    const results = document.getElementById('search-results');

    if (!input) return;

    input.addEventListener('input', function() {
        const query = this.value.trim();
        if (query.length < 2) { results.classList.add('d-none'); return; }

        fetch(`/admin/medicine/search?search=${encodeURIComponent(query)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            results.innerHTML = '';
            if (data.length > 0) {
                results.classList.remove('d-none');
                data.forEach(item => {
                    const btn = document.createElement('button');
                    btn.className = 'list-group-item list-group-item-action border-0 py-3 px-4';
                    btn.style.cssText = 'background:white; cursor:pointer; width:100%; text-align:left;';
                    btn.innerHTML = `
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-weight:700;">${item.name}</span>
                            <span style="background:var(--primary);color:white;padding:3px 10px;border-radius:6px;font-size:0.75rem;">${item.price} TMT</span>
                        </div>`;
                    btn.addEventListener('click', () => {
                        window.location.href = `/admin/inventory/${item.id}/edit`;
                    });
                    results.appendChild(btn);
                });
            } else {
                results.classList.add('d-none');
            }
        })
        .catch(() => results.classList.add('d-none'));
    });

    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !results.contains(e.target)) {
            results.classList.add('d-none');
        }
    });
});
</script>
@endsection