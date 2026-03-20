<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Medicine;
use App\Models\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index()
    {
        // 1. Достаем текущий чек (корзину) из сессии
        $cart = session()->get('pos_cart', []);
        
        // 2. Считаем ИТОГО для текущего клиента
        $cartTotal = array_sum(array_column($cart, 'total_price'));

        // 3. Статистика за сегодня (для верхней панели)
        $totalMoney = Sale::whereDate('created_at', today())->sum('total_price');
        $salesCount = Sale::whereDate('created_at', today())->sum('quantity');

        return view('sales.index', compact('cart', 'cartTotal', 'totalMoney', 'salesCount'));
    }

    // Добавляет отсканированный товар во временный чек
    public function addToCart(Request $request)
    {
        $medicine = Medicine::where('barcode', $request->barcode)->first();

        if (!$medicine) {
            if ($request->ajax()) return response()->json(['success' => false, 'message' => 'Товар не найден!'], 404);
            return back()->with('error', 'Product not found!');
        }

        $saleType = $request->sale_type ?? 'box'; 
        $qtySold = $request->quantity ?? 1;

        if ($saleType === 'box') {
            $basePrice = $medicine->price;
            $unitsToDeduct = $qtySold * ($medicine->units_per_box ?? 1);
        } else {
            $basePrice = $medicine->price_unit;
            $unitsToDeduct = $qtySold;
        }

        if (!$basePrice || $basePrice <= 0) {
            if ($request->ajax()) return response()->json(['success' => false, 'message' => 'Цена не установлена!'], 422);
        }

        $storage = Storage::where('medicine_id', $medicine->id)->first();
        if (!$storage || $storage->quantity < $unitsToDeduct) {
            if ($request->ajax()) return response()->json(['success' => false, 'message' => 'Недостаточно на складе!'], 422);
        }

        // Расчет со скидкой
        $discountPercent = $medicine->discount ?? 0;
        $finalPricePerItem = $basePrice * (1 - ($discountPercent / 100));
        $totalPrice = $finalPricePerItem * $qtySold;

        // Сохраняем в сессию
        $cart = session()->get('pos_cart', []);
        $cartId = uniqid(); // Уникальный ID для строки чека

        $cart[$cartId] = [
            'id' => $cartId,
            'medicine_id' => $medicine->id,
            'name' => $medicine->name,
            'sale_type' => $saleType,
            'quantity' => $qtySold,
            'total_price' => $totalPrice,
            'units_to_deduct' => $unitsToDeduct // Запоминаем, сколько списывать со склада
        ];

        session()->put('pos_cart', $cart);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Добавлено в чек']);
        }

        return redirect()->back();
    }

    // Удаляет ошибочно отсканированный товар из чека до оплаты
    public function removeFromCart($id)
    {
        $cart = session()->get('pos_cart', []);
        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('pos_cart', $cart);
        }
        return redirect()->back();
    }
public function checkout(Request $request) 
{
    $cart = session()->get('pos_cart', []);
    
    if (empty($cart)) {
        return redirect()->back()->with('error', 'Корзина пуста');
    }

    // Обернем всё в транзакцию
    DB::beginTransaction();

    try {
        $transactionId = 'ORD-' . date('His'); 

        foreach ($cart as $item) {
            // 1. Создаем запись о продаже
            Sale::create([
                'transaction_id' => $transactionId,
                'medicine_id'    => $item['medicine_id'],
                'quantity'       => $item['quantity'],
                'total_price'    => $item['total_price'],
                'sale_type'      => $item['sale_type'],
                
            ]);

            // 2. Списываем со склада
            $storage = Storage::where('medicine_id', $item['medicine_id'])->first();
            
            if (!$storage || $storage->quantity < $item['units_to_deduct']) {
                // Если товара вдруг не хватило (кто-то другой купил быстрее)
                throw new \Exception("Недостаточно товара: " . ($item['name'] ?? 'ID ' . $item['medicine_id']));
            }

            $storage->decrement('quantity', $item['units_to_deduct']);
        }

        // Если всё прошло успешно — сохраняем изменения в базе окончательно
        DB::commit();

        session()->forget('pos_cart');
        return redirect()->route('sales.index')->with('success', 'Продажа #' . $transactionId . ' успешно завершена!');

    } catch (\Exception $e) {
        // Если была хоть одна ошибка — отменяем ВСЕ записи в базе (как будто ничего не было)
        DB::rollBack();
        return redirect()->back()->with('error', 'Ошибка: ' . $e->getMessage());
    }
}
    public function closeShift(Request $request)
    {
        return redirect()->route('sales.report');
    }

public function showReport(Request $request)
{
    // 1. Получаем продажи за сегодня с данными о лекарствах
    $sales = Sale::with('medicine')->whereDate('created_at', today())->get();

    // 2. Считаем итог за месяц
    $monthlySalesTotal = Sale::whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->sum('total_price');

    // 3. Формируем массив данных для отчета
    $report = [
        'total_money'   => $sales->sum('total_price'),
        'monthly_total' => $monthlySalesTotal,
        'total_items'   => $sales->sum('quantity'),
        'start_time'    => $sales->min('created_at'), // Carbon объект или null
        'end_time'      => now(),
        // Группируем для таблицы внизу
        'medicines'     => $sales->groupBy('medicine_id'), 
    ];

    return view('sales.report', compact('report'));
}

    public function destroy($id)
    {
        $sale = Sale::findOrFail($id);
        $inventory = Storage::where('medicine_id', $sale->medicine_id)->first();
        if ($inventory) {
            $inventory->increment('quantity', $sale->quantity);
        }
        $sale->delete();
        return redirect()->back()->with('success', 'Sale removed and inventory restored.');
    }
}