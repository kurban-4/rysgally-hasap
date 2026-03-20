<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WholesaleInvoice;
use App\Models\WholesaleItem;
use App\Models\WholesaleStorage;
use App\Models\Medicine;
use Illuminate\Support\Facades\DB;

class WholesaleController extends Controller
{

public function index(Request $request)
{
    // 1. Инициализируем запрос инвойсов с подгрузкой товаров
    $query = WholesaleInvoice::with(['items.medicine']);

    // 2. Глобальный поиск (По имени клиента, номеру инвойса или названию лекарства)
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('customer_name', 'LIKE', "%{$search}%")
                ->orWhere('invoice_no', 'LIKE', "%{$search}%")
                ->orWhereHas('items.medicine', function ($mq) use ($search) {
                    $mq->where('name', 'LIKE', "%{$search}%");
                });
        });
    }

    // 3. Фильтрация по датам
    if ($request->filled('from_date') && $request->filled('to_date')) {
        $query->whereBetween('created_at', [
            $request->from_date . ' 00:00:00',
            $request->to_date . ' 23:59:59'
        ]);
    } elseif ($request->filled('date')) {
        $query->whereDate('created_at', $request->date);
    } else {
        // Если фильтры не заданы, можно оставить "за сегодня" или убрать это ограничение
        $query->whereDate('created_at', today());
    }

    // Получаем результаты инвойсов
    $invoices = $query->latest()->get();

    // --- ДОБАВЛЕНО ДЛЯ МОДАЛЬНОГО ОКНА TRANSFER ---
    // Получаем список всех лекарств, у которых есть остаток на оптовом складе
    $medicines = Medicine::with(['wholesaleStorage' => function($q) {
        $q->where('quantity', '>', 0);
    }])->get();
    // ----------------------------------------------

    // Считаем статистику для карточек
    $totalRevenue = $invoices->sum('total_amount');
    $totalInvoices = $invoices->count();
    $uniqueCustomers = $invoices->pluck('customer_name')->unique()->count();

    // Передаем всё в Blade, включая новую переменную $medicines
    return view('wholesale.index', compact(
        'invoices',
        'totalRevenue',
        'totalInvoices',
        'uniqueCustomers',
        'medicines' // Теперь переменная доступна в шаблоне
    ));
}
    public function autocomplete(Request $request)
    {
        $search = $request->term;

        // Ищем уникальные имена клиентов
        $customers = WholesaleInvoice::where('customer_name', 'LIKE', "%{$search}%")
            ->distinct()
            ->limit(5)
            ->pluck('customer_name')
            ->toArray();

        // Ищем названия лекарств (через связь с таблицей medicines)
        $medicines = Medicine::where('name', 'LIKE', "%{$search}%")
            ->limit(5)
            ->pluck('name')
            ->toArray();

        // Объединяем результаты
        $results = array_unique(array_merge($customers, $medicines));

        return response()->json($results);
    }
    public function exportExcel(Request $request)
{
    // 1. Повторяем логику фильтрации из index
    $query = WholesaleInvoice::with(['items.medicine']);

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('customer_name', 'LIKE', "%{$search}%")
              ->orWhereHas('items.medicine', function($mq) use ($search) {
                  $mq->where('name', 'LIKE', "%{$search}%");
              });
        });
    }

    if ($request->filled('from_date') && $request->filled('to_date')) {
        $query->whereBetween('created_at', [$request->from_date.' 00:00:00', $request->to_date.' 23:59:59']);
    } elseif ($request->filled('date')) {
        $query->whereDate('created_at', $request->date);
    }

    $invoices = $query->latest()->get();

    // 2. Настройка заголовков CSV
    $fileName = 'wholesale_report_' . date('Y-m-d') . '.csv';
    $headers = [
        "Content-type"        => "text/csv",
        "Content-Disposition" => "attachment; filename=$fileName",
        "Pragma"              => "no-cache",
        "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
        "Expires"             => "0"
    ];

    $columns = ['Invoice No', 'Customer', 'Date', 'Medicines', 'Total Amount', 'Status'];

    $callback = function() use($invoices, $columns) {
        $file = fopen('php://output', 'w');
        fputcsv($file, $columns);

        foreach ($invoices as $invoice) {
            // Собираем названия лекарств в одну строку
            $medicines = $invoice->items->map(function($item) {
                return $item->medicine->name . "(" . $item->quantity . ")";
            })->implode(', ');

            fputcsv($file, [
                $invoice->invoice_no,
                $invoice->customer_name,
                $invoice->created_at->format('Y-m-d H:i'),
                $medicines,
                $invoice->total_amount,
                $invoice->status
            ]);
        }
        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}
public function create()
{
    // Загружаем лекарства и считаем сумму всех их партий на опте
    $medicines = Medicine::whereHas('wholesaleStorage', function ($query) {
        $query->where('quantity', '>', 0);
    })->withSum('wholesaleStorage as total_stock', 'quantity')->get();

    return view('wholesale.create', compact('medicines'));
}
   public function store(Request $request) {
    $request->validate([
        'customer_name' => 'required|string',
        'items' => 'required|array|min:1',
    ]);

    try {
        return DB::transaction(function () use ($request) {
            $invoice = WholesaleInvoice::create([
                'invoice_no' => 'INV-' . strtoupper(uniqid()),
                'customer_name' => $request->customer_name,
                'total_amount' => 0,
            ]);

            $grandTotal = 0;

            foreach ($request->items as $itemData) {
                $medicine = Medicine::findOrFail($itemData['medicine_id']);
                $qtyToSell = (int)$itemData['qty'];
                
                // 1. Проверяем, хватает ли вообще товара на всех партиях
                $totalStock = WholesaleStorage::where('medicine_id', $medicine->id)->sum('quantity');
                if ($totalStock < $qtyToSell) {
                    throw new \Exception("Not enough stock for {$medicine->name}. Available: {$totalStock}");
                }

                // 2. Списываем из партий (FIFO)
                $batches = WholesaleStorage::where('medicine_id', $medicine->id)
                    ->where('quantity', '>', 0)
                    ->orderBy('expiry_date', 'asc')
                    ->get();

                $remaining = $qtyToSell;
                $unitPrice = 0;

                foreach ($batches as $batch) {
                    if ($remaining <= 0) break;

                    $take = min($batch->quantity, $remaining);
                    $batch->decrement('quantity', $take);
                    $unitPrice = $batch->selling_price; // Берем цену из партии
                    $remaining -= $take;
                }

                // 3. Синхронизируем общие штуки в таблице Medicine
                $totalUnits = $qtyToSell * ($medicine->units_per_box ?? 1);
                $medicine->decrement('total_quantity_units', $totalUnits);

                // 4. Считаем сумму строки
                $discount = $itemData['discount'] ?? 0;
                $rowTotal = ($unitPrice * $qtyToSell) * (1 - ($discount / 100));
                $grandTotal += $rowTotal;

                WholesaleItem::create([
                    'wholesale_invoice_id' => $invoice->id,
                    'medicine_id' => $medicine->id,
                    'quantity' => $qtyToSell,
                    'unit_price' => $unitPrice,
                    'discount_percent' => $itemData['discount'] ?? 0, // Добавь это!
                    'row_total' => $rowTotal,
                ]);
            }

            $invoice->update(['total_amount' => $grandTotal]);

            return redirect()->route('wholesale.index')->with('success', 'Wholesale invoice created!');
        });
    } catch (\Exception $e) {
        return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
    }
}
    public function show($id)
    {
        $invoice = WholesaleInvoice::with('items.medicine')->findOrFail($id);
        return view('wholesale.show', compact('invoice'));
    }
    public function edit($id)
    {
        $invoice = WholesaleInvoice::with('items.medicine')->findOrFail($id);

        $medicines = Medicine::whereHas('wholesaleStock', function ($query) {
            $query->where('quantity', '>', 0);
        })->get();

        return view('wholesale.edit', compact('invoice', 'medicines'));
    }
    public function update(Request $request, $id)
    {
        $invoice = WholesaleInvoice::with('items')->findOrFail($id);

        try {
            DB::transaction(function () use ($request, $invoice) {
                $newTotal = 0;

                foreach ($request->items as $itemData) {
                    $item = WholesaleItem::findOrFail($itemData['id']);
                    $storageEntry = \App\Models\WholesaleStorage::where('medicine_id', $item->medicine_id)->first();
                    $newQuantity = $itemData['quantity'];
                    $oldQuantity = $item->quantity;
                    $diff = $newQuantity - $oldQuantity;

                    if ($storageEntry) {
                        if ($diff > 0) {
                            if ($storageEntry->quantity < $diff) {
                                throw new \Exception("Not enough stock for medicine: " . $item->medicine->name);
                            }
                            $storageEntry->decrement('quantity', $diff);
                        } elseif ($diff < 0) {
                            $storageEntry->increment('quantity', abs($diff));
                        }
                    }
                    $unitPrice = $storageEntry ? $storageEntry->selling_price : $item->unit_price;
                    $discount = $itemData['discount'] ?? $item->discount_percent;
                    $rowTotal = ($newQuantity * $unitPrice) * (1 - ($discount / 100));

                    $item->update([
                        'quantity' => $newQuantity,
                        'unit_price' => $unitPrice,
                        'discount_percent' => $discount,
                        'row_total' => $rowTotal
                    ]);

                    $newTotal += $rowTotal;
                }

                $invoice->update([
                    'customer_name' => $request->customer_name,
                    'total_amount' => $newTotal
                ]);
            });

            return redirect()->route('wholesale.index')->with('success', 'Invoice Updated Successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating invoice: ' . $e->getMessage());
        }
    }
    public function destroy($id)
    {
        $invoice = WholesaleInvoice::with('items')->findOrFail($id);

        DB::transaction(function () use ($invoice) {
            foreach ($invoice->items as $item) {
                $storageEntry = WholesaleStorage::where('medicine_id', $item->medicine_id)->first();
                if ($storageEntry) {
                    $storageEntry->increment('quantity', $item->quantity);
                }
            }
            $invoice->delete();
        });
        return redirect()->back()->with('success', 'Invoice cancelled. Stock restored to wholesale warehouse.');
    }
}
