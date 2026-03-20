@extends('layouts.app')

@section('content')
<div class="desktop-app-layout">
@include('app.navbar')
   
    <main class="app-main">
        
        <header class="main-header">
            {{-- Mobile: hamburger spacer --}}
            <div class="mobile-header-spacer d-none"></div>
            
            <div class="stat-card">
                <div class="stat-icon bg-teal-light"><i class="bi bi-currency-dollar text-teal"></i></div>
                <div class="stat-data">
                    <span class="label">Выручка за сегодня</span>
                    <span class="value">{{ number_format($totalMoney ?? 0, 2) }} <small>TMT</small></span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon bg-blue-light"><i class="bi bi-cart-check text-primary"></i></div>
                <div class="stat-data">
                    <span class="label">Продано за сегодня</span>
                    <span class="value">{{ $salesCount ?? 0 }} <small>шт.</small></span>
                </div>
            </div>

            <div class="stat-card stat-card-hide-mobile">
                <div class="stat-icon bg-red-light"><i class="bi bi-exclamation-octagon text-danger"></i></div>
                <div class="stat-data">
                    <span class="label">{{__("app.stat_low_stock_pos")}}</span>
                    <span class="value text-danger">{{ \App\Models\Storage::where('quantity', '<', 10)->count() }} <small>{{__("app.unit_pcs")}}.</small></span>
                </div>
            </div>

            <div class="system-status ms-auto">
                <span class="dot pulse"></span>
                <span class="status-label">{{__("app.status_system_ready")}}</span>
            </div>
        </header>

        <div class="workspace">
            
            <div class="workspace-left">
                <div class="scanner-panel panel-card">
                    <form id="main-sales-form" class="scanner-form">
                        @csrf
                        <div class="type-toggles">
                            <input type="radio" class="btn-check" name="sale_type" id="box_node" value="box" checked>
                            <label class="toggle-btn" for="box_node">
                                КОРОБКА <span class="hotkey">F2</span>
                            </label>

                            <input type="radio" class="btn-check" name="sale_type" id="unit_node" value="unit">
                            <label class="toggle-btn" for="unit_node">
                                ПЛАСТИНКА <span class="hotkey">F3</span>
                            </label>
                        </div>

                        <div class="barcode-wrapper">
                            <i class="bi bi-upc-scan scan-icon"></i>
                            <input type="text" name="barcode" id="barcode-focus" class="barcode-input" autofocus required placeholder="Сканируйте штрих-код..." autocomplete="off">
                            <button type="submit" class="btn-submit-scan">
                                В ЧЕК <i class="bi bi-arrow-return-left ms-1"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="table-panel panel-card">
                    <div class="panel-header">
                        <h5>Текущий чек клиента</h5>
                        <span class="badge-custom"><strong id="last-scan-display">В корзине: {{ count($cart) }} поз.</strong></span>
                    </div>
                    <div class="table-scroll-container">
                        <table class="table pos-table">
                            <thead>
                                <tr>
                                    <th>{{__("app.table_preparation")}}</th>
                                    <th class="text-center">{{__("app.table_quantity_short")}} / ТИП</th>
                                    <th class="text-end">{{__("app.table_price")}}</th>
                                    <th class="text-center"><i class="bi bi-x-circle"></i></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cart as $id => $item)
                                <tr>
                                    <td>
                                        <div class="med-name">
                                            <i class="bi bi-capsule text-teal me-2"></i>
                                            {{ $item['name'] }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="qty-badge">
                                            {{ $item['quantity'] }} {{ $item['sale_type'] == 'box' ? 'кор.' : 'пласт.' }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold text-teal fs-5">{{ number_format($item['total_price'], 2) }}</td>
                                    <td class="text-center">
                                        <form action="{{ route('sales.cart.remove', $id) }}" method="POST" class="m-0">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-delete-row text-danger" title="Убрать из чека">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="bi bi-cart-x fs-1 d-block mb-2 opacity-25"></i>
                                        Чек пуст. Сканируйте товары.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- 
                RIGHT PANEL: On mobile this slides up as a drawer.
                On tablet/desktop it stays as a sidebar column.
            --}}
            <div class="workspace-right" id="workspaceRight">
                
                <div class="cart-summary-panel panel-card mb-3 p-4 bg-teal-light text-center" style="border: 2px solid var(--primary);">
                    <h6 class="text-uppercase fw-bold mb-2" style="color: var(--primary-dark);">Итого к оплате</h6>
                    <h1 class="total-amount fw-bold text-teal mb-4">{{ number_format($cartTotal ?? 0, 2) }} <small class="total-currency">TMT</small></h1>
                    
                    <form action="{{ route('sales.cart.checkout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="action-btn btn-checkout text-white" id="btn-pay">
                            <i class="bi bi-wallet2 fs-4"></i> ОПЛАТИТЬ <span class="hotkey dark ms-2">F12</span>
                        </button>
                    </form>
                </div>

                <div class="action-panel panel-card mb-3">
                    <h6 class="panel-title">{{__("app.shift_management_title")}}</h6>
                    <button class="action-btn btn-print mb-3" onclick="window.print()">
                        <i class="bi bi-printer"></i> {{__("app.btn_print_report")}} <span class="hotkey dark">F10</span>
                    </button>
                    <form action="{{ route('sales.close') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="action-btn btn-close-shift">
                            <i class="bi bi-door-closed"></i> {{__("app.btn_close_shift")}}
                        </button>
                    </form>
                </div>

                <div class="alert-panel bg-dark">
                    <h6 class="text-uppercase small fw-bold opacity-50 mb-3 text-white">{{__("app.inventory_card_title")}}</h6>
                    <div class="d-flex align-items-center">
                        <div class="alert-icon"><i class="bi bi-exclamation-triangle-fill text-warning"></i></div>
                        <div class="ms-3">
                            <h2 class="display-6 fw-bold mb-0 text-warning lh-1">{{ \App\Models\Storage::where('quantity', '<', 10)->count() }}</h2>
                            <p class="mb-0 fw-bold text-white">{{__("app.label_out_of_stock")}}</p>
                        </div>
                    </div>
                    <a href="{{ route('storage.index') }}" class="btn-go-storage mt-3">{{__("app.link_go_to_storage")}} <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </div>

        </div>

        {{-- Mobile: floating checkout button (visible only on small screens) --}}
        <div class="mobile-checkout-bar d-none" id="mobileCheckoutBar">
            <div class="mobile-checkout-total">
                <span class="label">Итого</span>
                <span class="value">{{ number_format($cartTotal ?? 0, 2) }} TMT</span>
            </div>
            <button class="mobile-checkout-btn" onclick="toggleRightPanel()">
                <i class="bi bi-wallet2"></i> Оплатить
            </button>
        </div>

    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('barcode-focus');
    const form = document.getElementById('main-sales-form');

    // 1. Aggressive autofocus for barcode scanner
    if (input) {
        input.focus();
        document.addEventListener('click', (e) => {
            if (e.target.tagName !== 'BUTTON' && e.target.tagName !== 'A' && e.target.tagName !== 'INPUT') {
                // Only refocus if not on mobile (would interfere with keyboard UX)
                if (window.innerWidth > 767) {
                    input.focus();
                }
            }
        });
    }

    // 2. Real-time clock
    setInterval(() => {
        const now = new Date();
        const clock = document.getElementById('realtime-clock');
        if (clock) clock.innerText = now.toLocaleTimeString('ru-RU');
    }, 1000);

    // 3. AJAX cart add
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); 
            const formData = new FormData(form);

            fetch("{{ route('sales.cart.add') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    input.value = '';
                    
                    fetch(window.location.href)
                        .then(res => res.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            
                            const oldTable = document.querySelector('.table-scroll-container');
                            const newTable = doc.querySelector('.table-scroll-container');
                            if (oldTable && newTable) oldTable.innerHTML = newTable.innerHTML;

                            const oldSummary = document.querySelector('.cart-summary-panel');
                            const newSummary = doc.querySelector('.cart-summary-panel');
                            if (oldSummary && newSummary) oldSummary.innerHTML = newSummary.innerHTML;

                            // Update mobile checkout bar total
                            updateMobileTotal();
                            
                            if (input && window.innerWidth > 767) input.focus();
                        });
                } else {
                    alert(data.message || 'Ошибка добавления товара');
                }
            })
            .catch(error => {
                console.error('AJAX Error:', error);
                location.reload();
            });
        });
    }

    // 4. HOTKEYS
    window.addEventListener('keydown', function(e) {
        if (e.key === 'F2' || e.keyCode === 113) {
            e.preventDefault();
            const nodeBox = document.getElementById('box_node');
            if(nodeBox) { nodeBox.checked = true; if (input) input.focus(); }
        } 
        else if (e.key === 'F3' || e.keyCode === 114) {
            e.preventDefault();
            const nodeUnit = document.getElementById('unit_node');
            if(nodeUnit) { nodeUnit.checked = true; if (input) input.focus(); }
        }
        else if (
            e.key === 'F9' || e.keyCode === 120 || 
            e.key === 'F12' || e.keyCode === 123 ||
            (e.key === 'Enter' && (e.metaKey || e.ctrlKey))
        ) {
            e.preventDefault();
            e.stopPropagation();
            const btnPay = document.getElementById('btn-pay');
            if (btnPay) {
                const payForm = btnPay.closest('form');
                if (payForm) payForm.submit();
                else btnPay.click();
            }
        }
    }, true); 

    // 5. Responsive setup
    setupResponsive();
    window.addEventListener('resize', setupResponsive);
});

// Toggle right panel as drawer on mobile
window.toggleRightPanel = function() {
    const panel = document.getElementById('workspaceRight');
    if (panel) {
        panel.classList.toggle('panel-open');
        document.body.style.overflow = panel.classList.contains('panel-open') ? 'hidden' : '';
    }
};

function setupResponsive() {
    const isMobile = window.innerWidth < 768;
    const mobileCheckoutBar = document.getElementById('mobileCheckoutBar');
    const mobileHeaderSpacer = document.querySelector('.mobile-header-spacer');

    if (mobileCheckoutBar) {
        mobileCheckoutBar.style.display = isMobile ? 'flex' : 'none';
    }
    if (mobileHeaderSpacer) {
        mobileHeaderSpacer.style.display = isMobile ? 'block' : 'none';
    }
    
    updateMobileTotal();
}

function updateMobileTotal() {
    const totalEl = document.querySelector('.cart-summary-panel h1, .cart-summary-panel .total-amount');
    const mobileValueEl = document.querySelector('.mobile-checkout-total .value');
    if (totalEl && mobileValueEl) {
        mobileValueEl.textContent = totalEl.textContent.trim();
    }
}
</script>

<style>
:root {
    --primary: #107A84;
    --primary-dark: #0c5e66;
    --bg-color: #eef2f3;
    --panel-bg: #ffffff;
    --text-main: #2d3748;
    --text-muted: #718096;
    --border-color: #e2e8f0;
}

/* ============================================================
   BASE / RESET
   ============================================================ */
body { margin: 0; padding: 0; font-family: 'Inter', sans-serif; background: var(--bg-color); }

.desktop-app-layout { display: flex; width: 100vw; overflow: hidden; }

/* ============================================================
   MAIN AREA
   ============================================================ */
.app-main { 
    flex: 1; 
    display: flex; 
    flex-direction: column; 
    overflow: hidden; 
    min-width: 0;
}

/* ============================================================
   HEADER STATS
   ============================================================ */
.main-header { 
    height: 80px; 
    background: white; 
    display: flex; 
    align-items: center; 
    padding: 0 25px; 
    gap: 20px; 
    border-bottom: 1px solid var(--border-color); 
    flex-shrink: 0;
    overflow: hidden;
}

.stat-card { 
    display: flex; 
    align-items: center; 
    gap: 15px; 
    padding-right: 20px; 
    border-right: 1px solid #eee; 
    flex-shrink: 0;
}

.stat-icon { 
    width: 45px; height: 45px; 
    border-radius: 12px; 
    display: flex; justify-content: center; align-items: center; 
    font-size: 1.3rem; 
    flex-shrink: 0;
}

.bg-teal-light { background: rgba(16, 122, 132, 0.1); }
.text-teal { color: var(--primary); }
.bg-blue-light { background: rgba(13, 110, 253, 0.1); }
.bg-red-light { background: rgba(220, 53, 69, 0.1); }
.stat-data .label { display: block; font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; white-space: nowrap; }
.stat-data .value { font-size: 1.2rem; font-weight: 800; color: var(--text-main); white-space: nowrap; }

.system-status { 
    display: flex; 
    align-items: center; 
    gap: 10px; 
    font-size: 0.8rem; 
    font-weight: 600; 
    color: #48bb78; 
    background: rgba(72,187,120,0.1); 
    padding: 8px 15px; 
    border-radius: 20px; 
    white-space: nowrap;
    flex-shrink: 0;
}

.dot.pulse { 
    width: 8px; height: 8px; 
    background: #48bb78; 
    border-radius: 50%; 
    animation: pulse-green 2s infinite; 
    flex-shrink: 0;
}

/* ============================================================
   WORKSPACE LAYOUT
   ============================================================ */
.workspace { 
    flex: 1; 
    display: flex; 
    gap: 20px; 
    padding: 20px; 
    overflow: hidden; 
}

.workspace-left { 
    flex: 1; 
    display: flex; 
    flex-direction: column; 
    gap: 20px; 
    min-width: 0; 
    overflow: hidden;
}

.workspace-right { 
    width: 320px; 
    display: flex; 
    flex-direction: column; 
    overflow-y: auto;
    gap: 0;
    flex-shrink: 0;
}

.panel-card { 
    background: white; 
    border-radius: 16px; 
    box-shadow: 0 4px 6px rgba(0,0,0,0.02); 
    border: 1px solid var(--border-color); 
}

/* ============================================================
   SCANNER FORM
   ============================================================ */
.scanner-panel { padding: 20px; flex-shrink: 0; }

.type-toggles { display: flex; gap: 10px; margin-bottom: 15px; }

.toggle-btn { 
    flex: 1; text-align: center; padding: 10px; 
    border: 2px solid #edf2f7; border-radius: 12px; 
    cursor: pointer; font-weight: 700; color: var(--text-muted); 
    transition: 0.2s; font-size: 0.85rem; 
}

.btn-check:checked + .toggle-btn { 
    border-color: var(--primary); 
    background: rgba(16, 122, 132, 0.05); 
    color: var(--primary); 
}

.hotkey { 
    background: #e2e8f0; color: #4a5568; 
    padding: 2px 6px; border-radius: 4px; 
    font-size: 0.7rem; margin-left: 5px; 
}

.btn-check:checked + .toggle-btn .hotkey { background: var(--primary); color: white; }

.barcode-wrapper { 
    display: flex; 
    background: #f8fafc; 
    border: 2px solid #cbd5e0; 
    border-radius: 14px; 
    overflow: hidden; 
    transition: 0.2s; 
}

.barcode-wrapper:focus-within { 
    border-color: var(--primary); 
    box-shadow: 0 0 0 3px rgba(16, 122, 132, 0.2); 
    background: white; 
}

.scan-icon { padding: 15px; font-size: 1.5rem; color: var(--primary); }

.barcode-input { 
    flex: 1; border: none; background: transparent; 
    font-size: 1.2rem; font-weight: 600; color: var(--text-main); 
    outline: none; min-width: 0;
}

.btn-submit-scan { 
    background: var(--primary); color: white; border: none; 
    padding: 0 25px; font-weight: 700; cursor: pointer; 
    transition: 0.2s; white-space: nowrap; flex-shrink: 0;
}

.btn-submit-scan:hover { background: var(--primary-dark); }

/* ============================================================
   TABLE PANEL
   ============================================================ */
.table-panel { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-height: 0; }

.panel-header { 
    padding: 15px 20px; 
    border-bottom: 1px solid var(--border-color); 
    display: flex; justify-content: space-between; align-items: center; 
    flex-shrink: 0;
}

.panel-header h5 { margin: 0; font-weight: 700; color: var(--text-main); }

.badge-custom { 
    background: #f1f5f9; padding: 6px 12px; 
    border-radius: 8px; font-size: 0.8rem; color: var(--text-muted); 
    white-space: nowrap;
}

.table-scroll-container { flex: 1; overflow-y: auto; }

.pos-table th { 
    position: sticky; top: 0; background: #f8fafc; 
    font-size: 0.75rem; text-transform: uppercase; 
    color: var(--text-muted); padding: 12px 20px; 
    border-bottom: 1px solid var(--border-color); z-index: 2; 
    white-space: nowrap;
}

.pos-table td { padding: 12px 20px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }

.med-name { font-weight: 600; color: var(--text-main); }

.qty-badge { 
    background: #edf2f7; color: #4a5568; 
    padding: 4px 10px; border-radius: 20px; 
    font-size: 0.85rem; font-weight: 600; white-space: nowrap;
}

.btn-delete-row { 
    background: none; border: none; color: #a0aec0; 
    cursor: pointer; transition: 0.2s; font-size: 1.2rem; 
}
.btn-delete-row:hover { color: #e53e3e; transform: scale(1.1); }

/* ============================================================
   RIGHT PANEL — ACTIONS
   ============================================================ */
.total-amount { font-size: 2.5rem; line-height: 1; }
.total-currency { font-size: 1rem; }

.action-panel { padding: 20px; }

.panel-title { 
    font-weight: 800; color: var(--text-muted); 
    text-transform: uppercase; margin-bottom: 15px; font-size: 0.8rem; 
}

.action-btn { 
    width: 100%; padding: 15px; border-radius: 12px; border: none; 
    font-weight: 700; font-size: 1rem; cursor: pointer; 
    display: flex; justify-content: center; align-items: center; 
    gap: 10px; transition: 0.2s; 
}

.btn-checkout { background: var(--primary); }
.btn-checkout:hover { background: var(--primary-dark); }

.btn-print { background: var(--bg-color); color: var(--text-main); border: 1px solid var(--border-color); }
.btn-print:hover { background: #e2e8f0; }
.btn-print .hotkey.dark { background: #cbd5e0; color: #2d3748; }
.btn-close-shift { background: #fff5f5; color: #e53e3e; border: 1px solid #feb2b2; }
.btn-close-shift:hover { background: #e53e3e; color: white; }

.alert-panel { 
    padding: 25px; border-radius: 16px; 
    background: #2d3748; color: white; 
    box-shadow: 0 10px 25px rgba(0,0,0,0.1); 
}

.alert-icon { 
    width: 50px; height: 50px; 
    background: rgba(255, 193, 7, 0.2); border-radius: 12px; 
    display: flex; justify-content: center; align-items: center; font-size: 1.5rem; 
    flex-shrink: 0;
}

.btn-go-storage { 
    display: inline-block; padding: 10px 20px; 
    background: rgba(255,255,255,0.1); color: white; 
    text-decoration: none; border-radius: 8px; 
    font-size: 0.85rem; font-weight: 600; transition: 0.2s; 
}
.btn-go-storage:hover { background: rgba(255,255,255,0.2); color: white; }

/* Mobile bottom checkout bar */
.mobile-checkout-bar {
    display: none;
    position: fixed;
    bottom: 0; left: 0; right: 0;
    background: white;
    border-top: 2px solid var(--primary);
    padding: 12px 20px;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    z-index: 900;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
}

.mobile-checkout-total .label {
    display: block; font-size: 0.7rem; text-transform: uppercase; 
    color: var(--text-muted); font-weight: 700;
}

.mobile-checkout-total .value {
    font-size: 1.3rem; font-weight: 800; color: var(--primary);
}

.mobile-checkout-btn {
    background: var(--primary); color: white; border: none;
    padding: 12px 24px; border-radius: 12px; 
    font-weight: 700; font-size: 1rem; cursor: pointer;
    display: flex; align-items: center; gap: 8px;
    white-space: nowrap;
}

@keyframes pulse-green {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(72, 187, 120, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(72, 187, 120, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(72, 187, 120, 0); }
}

/* ============================================================
   RESPONSIVE — TABLET (768px – 1023px)
   ============================================================ */
@media (max-width: 1023px) and (min-width: 768px) {
    .main-header { padding: 0 15px; gap: 12px; }
    
    .stat-data .value { font-size: 1rem; }
    
    .workspace { padding: 15px; gap: 15px; }
    
    .workspace-right { width: 280px; }
    
    .total-amount { font-size: 2rem; }
    
    .hotkey { display: none; } /* Hide hotkey hints on tablet */
    
    .status-label { display: none; } /* Hide "System ready" text, keep dot */
    
    .system-status { padding: 8px; }

    /* Compact scanner */
    .scan-icon { padding: 12px; font-size: 1.2rem; }
    .barcode-input { font-size: 1rem; }
    .btn-submit-scan { padding: 0 15px; font-size: 0.85rem; }
}

/* ============================================================
   RESPONSIVE — MOBILE (< 768px)
   Stack layout vertically, right panel as bottom sheet
   ============================================================ */
@media (max-width: 767px) {
    /* Body needs to scroll on mobile */
    body.desktop-app-mode {
        overflow: auto !important;
    }

    .desktop-app-layout { 
        flex-direction: column; 
        height: auto !important;
        min-height: 100vh;
        overflow: auto !important;
    }

    .app-main { 
        overflow: auto !important; 
        height: auto;
    }

    /* Header: compact, accommodate hamburger space */
    .main-header { 
        height: auto;
        min-height: 64px;
        padding: 10px 15px 10px 70px; /* Left padding for hamburger */
        flex-wrap: wrap;
        gap: 10px;
    }

    .stat-card { 
        padding-right: 12px; 
        gap: 8px; 
    }

    .stat-card-hide-mobile { display: none !important; }
    
    .stat-icon { width: 36px; height: 36px; font-size: 1rem; }
    .stat-data .label { font-size: 0.6rem; }
    .stat-data .value { font-size: 0.95rem; }

    .status-label { display: none; }
    .system-status { padding: 6px 10px; }

    /* Workspace stacks vertically */
    .workspace { 
        flex-direction: column; 
        padding: 12px; 
        gap: 12px;
        overflow: visible !important;
        padding-bottom: 90px; /* Space for mobile checkout bar */
    }

    .workspace-left { overflow: visible; }

    /* Table panel has fixed height on mobile */
    .table-panel { 
        height: 350px; 
        flex: none;
    }

    /* Right panel: slide-up bottom sheet on mobile */
    .workspace-right {
        position: fixed;
        bottom: 0; left: 0; right: 0;
        width: 100% !important;
        max-height: 80vh;
        background: var(--bg-color);
        border-radius: 24px 24px 0 0;
        box-shadow: 0 -8px 30px rgba(0,0,0,0.15);
        z-index: 950;
        overflow-y: auto;
        padding: 20px 15px 30px;
        transform: translateY(100%);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .workspace-right.panel-open {
        transform: translateY(0);
    }

    /* Pull indicator for bottom sheet */
    .workspace-right::before {
        content: '';
        display: block;
        width: 40px; height: 4px;
        background: #cbd5e0;
        border-radius: 2px;
        margin: 0 auto 20px;
    }

    .total-amount { font-size: 2.5rem; }

    /* Hotkeys hidden on mobile */
    .hotkey { display: none; }

    /* Scanner adjustments */
    .scanner-panel { padding: 15px; }
    .type-toggles { gap: 8px; }
    .toggle-btn { padding: 10px 6px; font-size: 0.75rem; }
    .scan-icon { padding: 12px; font-size: 1.2rem; }
    .barcode-input { font-size: 1rem; }
    .btn-submit-scan { padding: 0 12px; font-size: 0.8rem; }

    /* Table adjustments */
    .panel-header { padding: 12px 15px; }
    .panel-header h5 { font-size: 0.95rem; }
    .pos-table th { padding: 10px 12px; font-size: 0.65rem; }
    .pos-table td { padding: 10px 12px; }
    .med-name { font-size: 0.85rem; }

    
    .mobile-checkout-bar { display: flex !important; }
}

@media print {
    .sidebar-wrapper,
    .scanner-panel,
    .main-header,
    .workspace-right,
    .mobile-checkout-bar,
    .sidebar-hamburger { display: none !important; }

    .workspace { padding: 0; }
    .workspace-left { width: 100%; }
    .table-panel { height: auto; overflow: visible; }
    .table-scroll-container { overflow: visible; }
}
</style>
@endsection