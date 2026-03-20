<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
public function index()
{
    // Используем groupBy правильно
    $orders = Sale::select('transaction_id')
        ->selectRaw('SUM(total_price) as total_sum')
        ->selectRaw('MAX(created_at) as order_time')
        ->groupBy('transaction_id')
        ->orderBy('order_time', 'desc')
        ->get();

    return view('sales.customers.index', compact('orders'));
}
    public function show($transaction_id)
    {
        $items = Sale::where('transaction_id', $transaction_id)
            ->with('medicine') 
            ->get();

        if ($items->isEmpty()) {
            return redirect()->route('sales.customers.index');
        }

        $total = $items->sum('total_price');
        $orderDate = $items->first()->created_at;

        return view('sales.customers.show', compact('items', 'transaction_id', 'total', 'orderDate'));
    } // <--- ПРОВЕРЬТЕ ЭТУ СКОБКУ! Она закрывает метод show
} // <--- ПРОВЕРЬТЕ ЭТУ СКОБКУ! Она закрывает сам класс