@extends('layouts.app')

@section('content')
<div class="desktop-app-layout">
    @include('app.navbar')

    <main class="app-main">
        <header class="main-header">
            <div class="header-left">
                <h4 class="fw-bold mb-0">Wholesale Summary</h4>
                <p class="text-muted small mb-0 d-none d-md-block">Wholesale Invoices</p>
            </div>
            <div class="header-right ms-auto">
                <button type="button" class="btn-outline-teal d-none d-md-flex"
                    data-bs-toggle="modal" data-bs-target="#transferToPharmacyModal">
                    <i class="bi bi-arrow-left-right me-2"></i> Transfer to Pharmacy
                </button>
                <a href="{{ route('wholesale.create') }}" class="btn-teal">
                    <i class="bi bi-plus-circle me-1 me-md-2"></i>
                    <span class="d-none d-md-inline">Create New Invoice</span>
                </a>
            </div>
        </header>

        <div class="workspace custom-scrollbar">
            <div class="ws-inner">

                {{-- KPI row --}}
                <div class="kpi-row">
                    <div class="kpi-card">
                        <div class="kpi-icon" style="background:rgba(16,122,132,0.1);color:#107A84;"><i class="bi bi-files"></i></div>
                        <div><div class="kpi-label">Total Invoices</div><div class="kpi-value">{{ $invoices->count() }}</div></div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon" style="background:rgba(255,193,7,0.1);color:#ffc107;"><i class="bi bi-cash-stack"></i></div>
                        <div><div class="kpi-label">Total Revenue</div><div class="kpi-value">${{ number_format($invoices->sum('total_amount'),2) }}</div></div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon" style="background:rgba(13,202,240,0.1);color:#0dcaf0;"><i class="bi bi-people"></i></div>
                        <div><div class="kpi-label">Customers</div><div class="kpi-value">{{ $invoices->unique('customer_name')->count() }}</div></div>
                    </div>
                </div>

                <div class="filter-card no-print">
                    <form action="{{ route('wholesale.index') }}" method="GET" id="filterForm">
                        <div class="filter-grid">
                            <div class="filter-field position-relative">
                                <label class="field-label">Search</label>
                                <div class="input-row">
                                    <i class="bi bi-search input-icon"></i>
                                    <input type="text" name="search" id="searchInput" class="field-input-inner"
                                           placeholder="Type medicine..." value="{{ request('search') }}" autocomplete="off">
                                </div>
                                <ul id="autocomplete-results" class="autocomplete-list d-none"></ul>
                            </div>
                            <div class="filter-field">
                                <label class="field-label">Single Day</label>
                                <input type="date" name="date" class="field-date" value="{{ request('date') }}">
                            </div>
                            <div class="filter-field">
                                <label class="field-label">Date Range</label>
                                <div class="date-range">
                                    <input type="date" name="from_date" class="field-date" value="{{ request('from_date') }}">
                                    <input type="date" name="to_date"   class="field-date" value="{{ request('to_date') }}">
                                </div>
                            </div>
                            <div class="filter-actions">
                                <button type="submit" class="btn-teal-sm">APPLY</button>
                                <a href="{{ route('wholesale.index') }}" class="btn-light-sm"><i class="bi bi-arrow-clockwise"></i></a>
                                <button type="button" onclick="window.print()" class="btn-light-sm"><i class="bi bi-printer-fill"></i></button>
                                <button type="button" onclick="exportData()" class="btn-light-sm text-success"><i class="bi bi-file-earmark-excel-fill"></i></button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="table-card d-none d-md-block">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Invoice No</th>
                                    <th>Customer Details</th>
                                    <th>Status</th>
                                    <th>Total Sum</th>
                                    <th>Date</th>
                                    <th class="pe-4 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoices as $invoice)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="inv-icon"><i class="bi bi-file-earmark-text"></i></div>
                                            <span class="fw-bold text-dark">{{ $invoice->invoice_no }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $invoice->customer_name }}</div>
                                        <small class="text-muted">{{ $invoice->items->count() }} items</small>
                                        <div class="mt-1 d-flex flex-wrap gap-1">
                                            @foreach($invoice->items as $item)
                                            <span class="item-pill"><i class="bi bi-capsule me-1" style="color:#107A84;"></i>{{ $item->medicine->name }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td><span class="status-pill">Completed</span></td>
                                    <td><span class="fw-bold" style="color:#107A84;font-size:1.05rem;">${{ number_format($invoice->total_amount,2) }}</span></td>
                                    <td>
                                        <div class="small text-muted">
                                            <i class="bi bi-calendar3 me-1"></i>{{ $invoice->created_at->format('M d, Y') }}<br>
                                            <i class="bi bi-clock me-1"></i>{{ $invoice->created_at->format('H:i') }}
                                        </div>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('wholesale.show', $invoice->id) }}" class="act-btn view">
                                                <i class="bi bi-eye-fill"></i>
                                            </a>
                                            <form action="{{ route('wholesale.destroy', $invoice->id) }}" method="POST" class="m-0">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="act-btn delete" onclick="return confirm('Void this transaction?')">
                                                    <i class="bi bi-x-circle"></i>
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

                {{-- Mobile cards --}}
                <div class="d-md-none mobile-list">
                    @foreach($invoices as $invoice)
                    <div class="mobile-invoice-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="fw-bold">{{ $invoice->invoice_no }}</div>
                                <div class="text-muted small">{{ $invoice->customer_name }}</div>
                            </div>
                            <span class="fw-bold" style="color:#107A84;">${{ number_format($invoice->total_amount,2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">{{ $invoice->created_at->format('M d, Y') }} · {{ $invoice->items->count() }} items</small>
                            <div class="d-flex gap-2">
                                <a href="{{ route('wholesale.show', $invoice->id) }}" class="act-btn view"><i class="bi bi-eye-fill"></i></a>
                                <form action="{{ route('wholesale.destroy', $invoice->id) }}" method="POST" class="m-0">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="act-btn delete" onclick="return confirm('Void?')"><i class="bi bi-x-circle"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="d-md-none mb-4">
                    <button type="button" class="btn-outline-teal w-100" data-bs-toggle="modal" data-bs-target="#transferToPharmacyModal">
                        <i class="bi bi-arrow-left-right me-2"></i> Transfer to Pharmacy
                    </button>
                </div>

            </div>
        </div>
    </main>
</div>

{{-- Transfer modal --}}
<div class="modal fade" id="transferToPharmacyModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:20px;">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" style="color:#107A84;"><i class="bi bi-arrow-left-right me-2"></i>Transfer to Pharmacy Storage</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('wholesale_storage.transfer') }}" method="POST" id="transferForm">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="field-label">SELECT MEDICINE FROM WHOLESALE</label>
                        <select name="medicine_id" id="medicine_id_transfer" class="field-select-modal" required>
                            <option value="">Select Medicine</option>
                            @foreach($medicines as $medicine)
                                @php $stockItem = $medicine->wholesaleStorage->first(); @endphp
                                @if($stockItem)
                                <option value="{{ $medicine->id }}" data-stock="{{ $stockItem->quantity }}">{{ $medicine->name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <p class="mt-2 mb-0 small">Available Stock: <span id="available_stock_badge" class="fw-bold text-primary">0</span></p>
                    </div>
                    <div class="mb-3">
                        <label class="field-label">TRANSFER QUANTITY</label>
                        <input type="number" name="transfer_qty" id="transfer_qty" class="field-select-modal" placeholder="Enter amount" min="1" required>
                        <div id="stock-warning" class="form-text text-danger d-none">Quantity exceeds available stock!</div>
                    </div>
                    <div class="alert border-0 small mb-0" style="background:#eef9fa;color:#107A84;border-radius:12px;">
                        <i class="bi bi-info-circle me-2"></i> This will move items to the main pharmacy shelf.
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                    <button type="submit" class="btn text-white px-4" style="background:#107A84;border-radius:10px;">Confirm Transfer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sInput = document.getElementById('searchInput');
    const sResults = document.getElementById('autocomplete-results');
    if (sInput) {
        sInput.addEventListener('input', function() {
            if (this.value.length < 2) { sResults.classList.add('d-none'); return; }
            fetch(`{{ route('wholesale.autocomplete') }}?term=${this.value}`)
                .then(r => r.json()).then(data => {
                    sResults.innerHTML = '';
                    if (data.length) {
                        sResults.classList.remove('d-none');
                        data.forEach(item => {
                            const li = document.createElement('li');
                            li.className = 'autocomplete-item';
                            li.innerHTML = `<i class="bi bi-capsule me-2" style="color:#107A84;"></i>${item}`;
                            li.addEventListener('click', () => { sInput.value = item; sResults.classList.add('d-none'); });
                            sResults.appendChild(li);
                        });
                    } else { sResults.classList.add('d-none'); }
                });
        });
        document.addEventListener('click', e => { if (!sInput.contains(e.target)) sResults.classList.add('d-none'); });
    }

    const medSel = document.getElementById('medicine_id_transfer');
    const badge  = document.getElementById('available_stock_badge');
    const qtyIn  = document.getElementById('transfer_qty');
    const warn   = document.getElementById('stock-warning');
    if (medSel) {
        medSel.addEventListener('change', function() {
            const stock = this.options[this.selectedIndex].getAttribute('data-stock')||0;
            badge.textContent = stock; qtyIn.max = stock; qtyIn.value = ''; warn.classList.add('d-none');
        });
        qtyIn.addEventListener('input', function() {
            parseInt(this.value) > parseInt(badge.textContent) ? warn.classList.remove('d-none') : warn.classList.add('d-none');
        });
    }
});
function exportData() {
    const params = new URLSearchParams(new FormData(document.getElementById('filterForm'))).toString();
    window.location.href = "{{ route('wholesale.export') }}?" + params;
}
</script>

<style>
*, *::before, *::after { box-sizing:border-box; }
body { margin:0; padding:0; font-family:'Inter',sans-serif; background:#f4f7f7; }
.desktop-app-layout { position:fixed; inset:0; display:flex; overflow:hidden; }
.desktop-app-layout .sidebar-wrapper { position:relative !important; flex-shrink:0; height:100%; }
.app-main { flex:1; min-width:0; display:flex; flex-direction:column; overflow:hidden; height:100%; }

.main-header { height:68px; background:white; border-bottom:1px solid #e8edf2; display:flex; align-items:center; padding:0 24px; gap:14px; flex-shrink:0; }
.header-right { display:flex; align-items:center; gap:10px; flex-shrink:0; }

.btn-teal { background:#107A84; color:white; border:none; border-radius:11px; padding:9px 16px; font-weight:700; font-size:0.82rem; display:flex; align-items:center; text-decoration:none; transition:0.2s; white-space:nowrap; }
.btn-teal:hover { background:#0c5e66; color:white; }
.btn-outline-teal { background:white; color:#107A84; border:1.5px solid #107A84; border-radius:11px; padding:9px 16px; font-weight:700; font-size:0.82rem; display:flex; align-items:center; transition:0.2s; white-space:nowrap; cursor:pointer; }
.btn-outline-teal:hover { background:#107A84; color:white; }

.workspace { flex:1; overflow-y:auto; padding:20px 24px; }
.ws-inner { max-width:1200px; margin:0 auto; }

.kpi-row { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:20px; }
.kpi-card { background:white; border-radius:16px; padding:20px; display:flex; align-items:center; gap:14px; border:1px solid #e8edf2; box-shadow:0 2px 8px rgba(0,0,0,0.03); }
.kpi-icon { width:46px; height:46px; border-radius:13px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0; }
.kpi-label { font-size:0.62rem; text-transform:uppercase; font-weight:800; color:#a0aec0; margin-bottom:2px; }
.kpi-value { font-size:1.3rem; font-weight:800; color:#2d3748; }

.filter-card { background:white; border-radius:16px; padding:18px 20px; border:1px solid #e8edf2; border-top:3px solid #107A84; box-shadow:0 2px 8px rgba(0,0,0,0.03); margin-bottom:20px; }
.filter-grid { display:grid; grid-template-columns:1fr auto auto auto; gap:14px; align-items:end; }
.filter-field { }
.field-label { display:block; font-size:0.62rem; text-transform:uppercase; font-weight:800; color:#107A84; margin-bottom:6px; }
.input-row { display:flex; align-items:center; background:#f8fafc; border:1.5px solid #e8edf2; border-radius:10px; overflow:hidden; }
.input-row:focus-within { border-color:#107A84; background:white; }
.input-icon { padding:0 10px; color:#107A84; font-size:0.85rem; }
.field-input-inner { flex:1; border:none; background:transparent; padding:9px 10px 9px 0; font-size:0.85rem; outline:none; }
.field-date { width:100%; border:1.5px solid #e8edf2; border-radius:10px; padding:9px 12px; font-size:0.85rem; background:#f8fafc; outline:none; }
.field-date:focus { border-color:#107A84; background:white; }
.date-range { display:flex; gap:8px; }
.filter-actions { display:flex; gap:8px; align-items:flex-end; }
.btn-teal-sm { background:#107A84; color:white; border:none; border-radius:9px; padding:9px 16px; font-weight:700; font-size:0.78rem; cursor:pointer; }
.btn-light-sm { background:#f1f5f9; color:#4a5568; border:1px solid #e2e8f0; border-radius:9px; padding:9px 12px; cursor:pointer; transition:0.15s; text-decoration:none; display:flex; align-items:center; }
.btn-light-sm:hover { background:#e2e8f0; }

.table-card { background:white; border-radius:16px; overflow:hidden; border:1px solid #e8edf2; box-shadow:0 2px 8px rgba(0,0,0,0.03); }
.table thead th { background:#f8fafc; color:#a0aec0; font-size:0.7rem; text-transform:uppercase; font-weight:800; padding:14px; border:none; white-space:nowrap; }
.table tbody tr:hover { background:#f8fbfb; }

.inv-icon { width:36px; height:36px; background:#eef9fa; color:#107A84; border-radius:9px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.item-pill { background:#f1f5f9; color:#64748b; font-size:0.68rem; padding:3px 8px; border-radius:6px; font-weight:600; }
.status-pill { background:#eef9fa; color:#107A84; border:1px solid #c8e9eb; padding:4px 10px; border-radius:20px; font-size:0.72rem; font-weight:700; }

.act-btn { width:32px; height:32px; border-radius:8px; border:none; display:inline-flex; align-items:center; justify-content:center; font-size:0.8rem; cursor:pointer; transition:0.15s; text-decoration:none; }
.act-btn.view  { background:#eef9fa; color:#107A84; border:1px solid #c8e9eb; }
.act-btn.delete{ background:#fee2e2; color:#ef4444; border:1px solid #fecaca; }
.act-btn:hover { transform:scale(1.08); }

.mobile-list { margin-bottom:16px; }
.mobile-invoice-card { background:white; border-radius:14px; padding:14px 16px; margin-bottom:10px; border:1px solid #e8edf2; }

.autocomplete-list { position:absolute; top:100%; left:0; right:0; background:white; border:1px solid #e8edf2; border-radius:12px; z-index:1050; list-style:none; padding:6px; margin:4px 0 0; box-shadow:0 8px 24px rgba(0,0,0,0.08); }
.autocomplete-item { padding:8px 12px; border-radius:8px; cursor:pointer; font-size:0.85rem; }
.autocomplete-item:hover { background:#f0f9fa; }

.field-select-modal { width:100%; border:1.5px solid #e8edf2; border-radius:11px; padding:10px 14px; font-size:0.875rem; background:#f8fafc; outline:none; }
.field-select-modal:focus { border-color:#107A84; background:white; }

.custom-scrollbar::-webkit-scrollbar { width:6px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background:#cbd5e0; border-radius:10px; }

@media (max-width:1100px) { .kpi-row { grid-template-columns:repeat(2,1fr); } .filter-grid { grid-template-columns:1fr 1fr; } .filter-actions { grid-column:1/-1; } }
@media (max-width:767px) {
    .desktop-app-layout { position:relative; inset:auto; min-height:100vh; height:auto !important; flex-direction:column; overflow:auto !important; }
    .desktop-app-layout .sidebar-wrapper { position:fixed !important; }
    .app-main { height:auto !important; overflow:auto !important; }
    .main-header { padding:0 14px 0 68px; height:60px; }
    .workspace { padding:12px; }
    .kpi-row { grid-template-columns:1fr 1fr; }
    .filter-grid { grid-template-columns:1fr; }
    .date-range { flex-direction:column; }
}
@media print { .no-print { display:none !important; } .sidebar-wrapper,.main-header { display:none !important; } .desktop-app-layout { display:block; } body { background:white; } }
</style>
@endsection