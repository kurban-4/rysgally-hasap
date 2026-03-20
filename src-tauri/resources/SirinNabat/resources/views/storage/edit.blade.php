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
                <h4 class="fw-bold mb-0" style="color:#107A84;">Edit Stock & Package</h4>
                <p class="text-muted small mb-0 d-none d-md-block">{{ $storage->medicine->name }}</p>
            </div>
            <span class="id-badge ms-auto">ID #{{ str_pad($storage->id, 4, '0', STR_PAD_LEFT) }}</span>
        </header>

        <div class="workspace custom-scrollbar">
            <div class="form-container">
                <form action="{{ route('storage.update', $storage->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')

                    <div class="edit-grid">
                        {{-- LEFT --}}
                        <div class="edit-left">

                            <div class="section-card">
                                <h6 class="section-title"><i class="bi bi-upc-scan me-2"></i>Barcode</h6>
                                <div class="input-row">
                                    <i class="bi bi-upc-scan input-icon"></i>
                                    <input type="text" name="barcode" class="field-input"
                                           value="{{ old('barcode', $storage->medicine->barcode) }}"
                                           placeholder="Scan or enter barcode">
                                </div>
                            </div>

                            <div class="section-card">
                                <h6 class="section-title"><i class="bi bi-box-seam me-2"></i>Stock Calculation</h6>
                                <div class="two-col">
                                    <div>
                                        <label class="field-label">Boxes (Коробки)</label>
                                        <div class="input-row">
                                            <i class="bi bi-box-seam input-icon"></i>
                                            <input type="number" name="boxes" id="input-boxes" class="field-input"
                                                   value="{{ $storage->medicine->units_per_box > 0 ? floor($storage->quantity / $storage->medicine->units_per_box) : 0 }}" required>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="field-label">Units per Box</label>
                                        <div class="input-row">
                                            <i class="bi bi-layers input-icon"></i>
                                            <input type="number" name="units_per_box" id="input-units-per-box" class="field-input"
                                                   value="{{ old('units_per_box', $storage->medicine->units_per_box) }}" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="calc-preview">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-calculator-fill" style="color:#107A84;"></i>
                                        <span class="text-muted small">Formula: Boxes × Capacity</span>
                                    </div>
                                    <div class="text-end">
                                        <div class="calc-label">TOTAL STOCK</div>
                                        <div class="calc-value"><span id="live-total">0</span> units</div>
                                    </div>
                                </div>
                            </div>

                            

                        </div>

                        {{-- RIGHT --}}
                        <div class="edit-right">

                            <div class="section-card">
                                <h6 class="section-title"><i class="bi bi-tag-fill me-2"></i>Pricing</h6>
                                <div class="two-col mb-3">
                                    <div>
                                        <label class="field-label">Price ($)</label>
                                        <input type="number" name="price" id="edit-price-input" class="field-input"
                                               value="{{ old('price', $storage->medicine->price) }}" step="0.01">
                                    </div>
                                    <div>
                                        <label class="field-label">Discount (%)</label>
                                        <input type="number" name="discount" id="edit-discount-input" class="field-input"
                                               value="{{ old('discount', $storage->medicine->discount) }}" min="0" max="100">
                                    </div>
                                </div>
                                <div class="price-result">
                                    <div id="final-price-display" class="final-price">$0.00</div>
                                    <div id="badge-area"></div>
                                </div>
                            </div>

                            <div class="section-card">
                                <h6 class="section-title"><i class="bi bi-tag me-2"></i>Category</h6>
                                <select name="category" class="field-input field-select">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat }}" {{ old('category', $storage->category) == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="btn-save">
                                <i class="bi bi-check2-circle me-2"></i> Update All Data
                            </button>

                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bInput = document.getElementById('input-boxes');
    const uInput = document.getElementById('input-units-per-box');
    const totalDisplay = document.getElementById('live-total');
    const pInput = document.getElementById('edit-price-input');
    const dInput = document.getElementById('edit-discount-input');
    const priceDisplay = document.getElementById('final-price-display');
    const badgeArea = document.getElementById('badge-area');

    function update() {
        const total = (parseInt(bInput?.value)||0) * (parseInt(uInput?.value)||0);
        if (totalDisplay) totalDisplay.textContent = total;

        const price = parseFloat(pInput?.value)||0;
        const disc  = parseFloat(dInput?.value)||0;
        const final = price * (1 - disc/100);
        if (priceDisplay) priceDisplay.textContent = '$' + final.toFixed(2);
        if (badgeArea) badgeArea.innerHTML = disc > 0
            ? `<span class="badge bg-danger">-${disc}% OFF</span>` : '';
    }

    [bInput, uInput, pInput, dInput].forEach(el => el?.addEventListener('input', update));
    update();
});
</script>

<style>
*, *::before, *::after { box-sizing: border-box; }
body { margin:0; padding:0; font-family:'Inter',sans-serif; background:#f4f7f7; }

.desktop-app-layout { position:fixed; inset:0; display:flex; overflow:hidden; }
.desktop-app-layout .sidebar-wrapper { position:relative !important; flex-shrink:0; height:100%; }
.app-main { flex:1; min-width:0; display:flex; flex-direction:column; overflow:hidden; height:100%; }

.main-header {
    height:68px; background:white;
    border-bottom:1px solid #e8edf2;
    display:flex; align-items:center; padding:0 24px; gap:14px; flex-shrink:0;
}
.btn-back {
    width:38px; height:38px; min-width:38px; border-radius:11px;
    background:white; border:1px solid #e8edf2; color:#718096;
    display:flex; align-items:center; justify-content:center;
    text-decoration:none; transition:0.2s; flex-shrink:0;
}
.btn-back:hover { color:#107A84; transform:translateX(-2px); }
.id-badge {
    background:#107A84; color:white;
    padding:5px 14px; border-radius:20px; font-size:0.75rem; font-weight:700;
    white-space:nowrap; flex-shrink:0;
}

.workspace { flex:1; overflow-y:auto; padding:24px; }
.custom-scrollbar::-webkit-scrollbar { width:6px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background:#cbd5e0; border-radius:10px; }

.form-container { max-width:900px; margin:0 auto; }

.edit-grid { display:grid; grid-template-columns:1fr 320px; gap:20px; align-items:start; }

.section-card {
    background:white; border-radius:16px; padding:20px;
    border:1px solid #e8edf2; margin-bottom:16px;
    box-shadow:0 2px 8px rgba(0,0,0,0.03);
}
.section-title { font-weight:700; color:#107A84; margin-bottom:14px; font-size:0.9rem; }

.field-label { display:block; font-size:0.62rem; text-transform:uppercase; font-weight:800; color:#a0aec0; letter-spacing:0.5px; margin-bottom:6px; }

.input-row { display:flex; align-items:center; background:#f8fafc; border:1.5px solid #e8edf2; border-radius:11px; overflow:hidden; transition:0.2s; }
.input-row:focus-within { border-color:#107A84; box-shadow:0 0 0 3px rgba(16,122,132,0.1); background:white; }
.input-icon { padding:0 12px; color:#107A84; font-size:0.95rem; flex-shrink:0; }
.field-input { flex:1; border:none; background:transparent; padding:11px 12px 11px 0; font-size:0.875rem; color:#2d3748; outline:none; width:100%; }
.field-select { border:1.5px solid #e8edf2; border-radius:11px; padding:11px 14px; background:#f8fafc; font-size:0.875rem; color:#2d3748; }
.field-select:focus { border-color:#107A84; box-shadow:0 0 0 3px rgba(16,122,132,0.1); outline:none; background:white; }

.two-col { display:grid; grid-template-columns:1fr 1fr; gap:12px; }

.calc-preview {
    display:flex; justify-content:space-between; align-items:center;
    background:#f0f9fa; border:1px solid #c8e9eb; border-radius:11px;
    padding:12px 16px; margin-top:14px;
}
.calc-label { font-size:0.58rem; text-transform:uppercase; font-weight:800; color:#a0aec0; }
.calc-value { font-weight:800; color:#107A84; font-size:1rem; }

.price-result {
    background:#f0f9fa; border-radius:11px; padding:14px 16px;
    display:flex; justify-content:space-between; align-items:center;
    border:1px solid #c8e9eb;
}
.final-price { font-size:1.4rem; font-weight:800; color:#107A84; }

.btn-save {
    width:100%; padding:14px; border-radius:14px; border:none;
    background:#107A84; color:white; font-weight:700; font-size:0.95rem;
    cursor:pointer; transition:0.2s; display:flex; align-items:center; justify-content:center; gap:8px;
}
.btn-save:hover { background:#0c5e66; transform:translateY(-1px); box-shadow:0 6px 16px rgba(16,122,132,0.2); }

@media (max-width:1023px) { .edit-grid { grid-template-columns:1fr; } }
@media (max-width:767px) {
    .desktop-app-layout { position:relative; inset:auto; min-height:100vh; height:auto !important; flex-direction:column; overflow:auto !important; }
    .desktop-app-layout .sidebar-wrapper { position:fixed !important; }
    .app-main { height:auto !important; overflow:auto !important; }
    .main-header { padding:0 14px 0 68px; height:60px; }
    .workspace { padding:12px; }
    .two-col { grid-template-columns:1fr; }
}
@media print { .sidebar-wrapper,.main-header { display:none !important; } .desktop-app-layout { display:block; } }
</style>
@endsection