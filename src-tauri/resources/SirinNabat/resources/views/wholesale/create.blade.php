@extends('layouts.app')

@section('content')
@include('app.navbar')

<div class="container-fluid py-4" style="background-color: #f4f7f6; min-height: 100vh;">
    <form action="{{ route('wholesale.store') }}" method="POST" id="invoice-form">
        @csrf
        
        {{-- БЛОК ОШИБОК: Теперь он один и на видном месте --}}
        @if ($errors->any())
            <div class="row justify-content-center mb-3">
                <div class="col-xl-11">
                    <div class="alert alert-danger shadow-sm border-0" style="border-radius: 15px;">
                        <h6 class="fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i> Ошибка заполнения:</h6>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="row justify-content-center">
            <div class="col-xl-11">
                <div class="card border-0 shadow-lg p-4" style="border-radius: 25px;">

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h3 class="fw-bold mb-0" style="color: #107A84;">
                                <i class="bi bi-receipt-cutoff me-2"></i> Create Invoice
                            </h3>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="d-inline-block text-start">
                                <label class="small fw-bold text-muted text-uppercase">Customer (Musderi)</label>
                                <input type="text" name="customer_name" class="form-control border-0 bg-light px-3 py-2"
                                    placeholder="Enter name" required value="{{ old('customer_name') }}" style="border-radius: 12px; min-width: 250px;">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4 p-4 rounded-4 shadow-sm" style="background-color: #ffffff; border: 1px solid #eef2f2;">
                        <label class="form-label fw-bold" style="color: #2c3e50;"><i class="bi bi-search me-1"></i> Select Medicine to Add</label>
                        <select id="medicine-adder" class="form-select">
                            <option value="">Search for a medicine...</option>
                            @foreach($medicines as $medicine)
                                {{-- В контроллере мы уже посчитали total_stock через withSum --}}
                                @if($medicine->total_stock > 0)
                                    @php $firstBatch = $medicine->wholesaleStorage->first(); @endphp
                                    <option value="{{ $medicine->id }}"
                                        data-price="{{ $firstBatch->selling_price ?? 0 }}"
                                        data-name="{{ $medicine->name }}"
                                        data-stock="{{ $medicine->total_stock }}"
                                        data-expiry="{{ $firstBatch->expiry_date ?? 'N/A' }}">
                                        {{ $medicine->name }} — ${{ number_format($firstBatch->selling_price ?? 0, 2) }} (Total Stock: {{ $medicine->total_stock }})
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table align-middle" id="items-table">
                            <thead>
                                <tr class="text-muted small uppercase">
                                    <th class="border-0">Medicine Item</th>
                                    <th class="border-0 text-center" width="160">Mukdar (Qty)</th>
                                    <th class="border-0 text-center" width="160">Expiry Date</th>
                                    <th class="border-0 text-center" width="160">Bahasy (Price)</th>
                                    <th class="border-0 text-center" width="130">Arzanlyk (%)</th>
                                    <th class="border-0 text-end" width="180">Total</th>
                                    <th class="border-0" width="50"></th>
                                </tr>
                            </thead>
                            <tbody id="invoice-items-list">
                                {{-- Сюда JS будет добавлять строки --}}
                            </tbody>
                        </table>
                    </div>

                    <div class="row pt-4 border-top g-4">
                        <div class="col-md-7 text-muted small">
                            <i class="bi bi-info-circle me-1"></i> Prices are calculated automatically based on quantity and discount percentage.
                        </div>
                        <div class="col-md-5 text-end">
                            <h5 class="text-muted mb-1">Grand Total Amount:</h5>
                            <h2 class="fw-bold mb-3" style="color: #107A84;">$<span id="total-display">0.00</span></h2>
                            <input type="hidden" name="grand_total" id="total-input" value="0">

                            <button type="submit" class="btn btn-lg w-100 text-white shadow" style="background-color: #107A84; border-radius: 15px; padding: 15px;">
                                <i class="bi bi-check2-all me-2"></i> Finalize & Record Sale
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const adder = document.getElementById('medicine-adder');
    const list = document.getElementById('invoice-items-list');
    let itemIndex = 0;

    adder.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (!opt.value) return;

        const id = opt.value;
        const name = opt.dataset.name;
        const price = opt.dataset.price;
        const stock = opt.dataset.stock;
        const expiry = opt.dataset.expiry || 'N/A';

        if (document.querySelector(`input[value="${id}"].med-id-input`)) {
            alert('This medicine is already in the list!');
            this.value = "";
            return;
        }

        const row = document.createElement('tr');
        row.innerHTML = `
        <td>
            <input type="hidden" name="items[${itemIndex}][medicine_id]" value="${id}" class="med-id-input">
            <div class="fw-bold text-dark">${name}</div>
            <small class="text-muted">Available: ${stock}</small>
        </td>
        <td>
            <input type="number" name="items[${itemIndex}][qty]" class="form-control qty-input text-center" value="1" min="1" max="${stock}" required>
        </td>
        <td class="text-center">
            <span class="badge bg-light text-secondary border px-2 py-1">${expiry}</span>
        </td>
        <td>
            <input type="number" step="0.01" name="items[${itemIndex}][unit_price]" class="form-control price-input bg-light text-center" value="${price}" readonly>
        </td>
        <td>
            <input type="number" name="items[${itemIndex}][discount]" class="form-control discount-input text-center" value="0" min="0" max="100">
        </td>
        <td class="text-end fw-bold">
            $<span class="row-total-display">${price}</span>
            <input type="hidden" class="row-total-hidden" value="${price}">
        </td>
        <td class="text-end">
            <button type="button" class="btn btn-sm btn-outline-danger border-0 remove-row">
                <i class="bi bi-trash"></i>
            </button>
        </td>
        `;
        list.appendChild(row);
        row.querySelector('.remove-row').addEventListener('click', function() {
            row.remove();
            calculateGrandTotal();
        });

        itemIndex++;
        this.value = "";
        calculateGrandTotal();
    });

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('qty-input') || e.target.classList.contains('discount-input')) {
            const row = e.target.closest('tr');
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const discount = parseFloat(row.querySelector('.discount-input').value) || 0;

            const total = (qty * price) * (1 - discount / 100);

            row.querySelector('.row-total-display').innerText = total.toFixed(2);
            row.querySelector('.row-total-hidden').value = total.toFixed(2);

            calculateGrandTotal();
        }
    });

    function calculateGrandTotal() {
        let grand = 0;
        document.querySelectorAll('.row-total-hidden').forEach(input => {
            grand += parseFloat(input.value) || 0;
        });
        document.getElementById('total-display').innerText = grand.toFixed(2);
        document.getElementById('total-input').value = grand.toFixed(2);
    }
});
</script>
@endsection