@extends('layouts.app')

@section('content')
@include('app.navbar')

<style>
    :root {
        --main-teal: #147a7d;
        --main-teal-hover: #0d5e60;
    }

    .btn-main {
        background-color: var(--main-teal);
        border-color: var(--main-teal);
        color: white;
    }

    .btn-main:hover {
        background-color: var(--main-teal-hover);
        color: white;
    }

    .text-main {
        color: var(--main-teal);
    }

    .form-control:focus {
        border-color: var(--main-teal);
        box-shadow: 0 0 0 0.25 dashed rgba(20, 122, 125, 0.25);
    }
</style>

<div class="container py-5">
    <div class="card shadow-lg border-0 rounded-4 mx-auto" style="max-width: 900px;">
        <div class="card-header bg-white border-0 pt-4 ps-4">
            <h3 class="fw-bold text-main mb-0">
                <i class="fas fa-plus-circle me-2"></i>Add New Stock Batch
            </h3>
        </div>

        <div class="card-body p-4">
            <form action="{{ route('wholesale_storage.store') }}" method="POST">
                @csrf
                <div class="row g-4">
                    <div class="col-12">
                        <label class="form-label fw-bold">Select Medicine</label>
                        <select name="medicine_id" class="form-select form-control-lg bg-light border-0">
                            @foreach($medicines as $medicine)
                            <option value="{{ $medicine->id }}">{{ $medicine->name }} ({{ $medicine->manufacturer }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Quantity</label>
                        <input type="number" name="quantity" class="form-control bg-light border-0" placeholder="500" required>
                    </div>

                    <div class="row g-4 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Batch Number</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-0 text-muted">#</span>
                                <input type="text" name="batch_number" class="form-control bg-light border-0" placeholder="B-1234">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-danger">Expiry Date (Srok)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-0"><i class="bi bi-calendar-event text-danger"></i></span>
                                <input type="date" name="expiry_date" class="form-control bg-light border-0" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Received Price (Internal)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-0 text-muted">$</span>
                            <input type="number" step="0.01" name="received_price" class="form-control bg-light border-0" placeholder="90">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Selling Price (For Clients)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-0 text-muted">$</span>
                            <input type="number" step="0.01" id="selling_price" name="selling_price" class="form-control bg-light border-0" placeholder="60" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-main">Default Discount (%)</label>
                        <input type="number" id="discount" name="discount" class="form-control bg-light border-0" value="0" min="0" max="100">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-success">Final Price (After Discount)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-0 text-success">$</span>
                            <input type="text" id="final_price" class="form-control bg-white border-0 fw-bold text-success fs-5" readonly value="0.00">
                        </div>
                    </div>

                    <div class="col-12 mt-4 text-center">
                        <button type="submit" class="btn btn-main btn-lg w-100 rounded-pill py-3 fw-bold shadow">
                            <i class="fas fa-check-circle me-2"></i>Add to Inventory
                        </button>
                        <a href="{{ route('wholesale_storage.index') }}" class="btn btn-link text-muted mt-2 text-decoration-none">
                            <i class="fas fa-arrow-left me-1"></i> Back to Inventory
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sellingPriceInput = document.getElementById('selling_price');
        const discountInput = document.getElementById('discount');
        const finalPriceInput = document.getElementById('final_price');

        function calculateFinal() {
            const price = parseFloat(sellingPriceInput.value) || 0;
            const discount = parseFloat(discountInput.value) || 0;
            const final = price - (price * (discount / 100));
            finalPriceInput.value = final.toFixed(2);
        }

        if (sellingPriceInput && discountInput && finalPriceInput) {
            sellingPriceInput.addEventListener('input', calculateFinal);
            discountInput.addEventListener('input', calculateFinal);
        }
    });
</script>
@endsection
