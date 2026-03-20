<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WholesaleStorage;
use App\Models\Storage;
use App\Models\Medicine;
use Illuminate\Support\Facades\DB;

class WholesaleStorageController extends Controller
{
    public function index()
    {
        $inventory = WholesaleStorage::with('medicine')->latest()->paginate(20);
        return view('wholesale_storage.index', compact('inventory'));
    }

    public function create()
    {
        $medicines = Medicine::orderBy('name')->get();
        return view('wholesale_storage.create', compact('medicines'));
    }

public function store(Request $request)
{
    $validated = $request->validate([
        'medicine_id'    => 'required|exists:medicines,id',
        'quantity'       => 'required|integer|min:1', // это коробки
        'received_price' => 'required|numeric|min:0',
        'selling_price'  => 'required|numeric|min:0',
        'discount'       => 'nullable|numeric|min:0|max:100',
        'batch_number'   => 'nullable|string',
        'expiry_date'    => 'nullable|date',
    ]);

    try {
        DB::transaction(function () use ($validated) {
            // 1. Создаем запись на оптовом складе
            WholesaleStorage::create($validated);

            // 2. Находим само лекарство
            $medicine = Medicine::findOrFail($validated['medicine_id']);

            // 3. Считаем штуки: коробки * штук_в_коробке
            $unitsInBox = (int)($medicine->units_per_box ?? 1);
            $totalUnitsToAdd = $validated['quantity'] * $unitsInBox;

            // 4. ОБНОВЛЯЕМ главную таблицу, чтобы трансфер видел остаток!
            $medicine->increment('total_quantity_units', $totalUnitsToAdd);
        });

        return redirect()->route('wholesale_storage.index')
            ->with('success', 'New batch added to Wholesale and Total Stock updated!');
            
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Error adding batch: ' . $e->getMessage());
    }
}


    // app/Http/Controllers/WholesaleStorageController.php
public function transferToPharmacy(Request $request)
{
    $request->validate([
        'medicine_id' => 'required|exists:medicines,id',
        'transfer_qty' => 'required|integer|min:1' 
    ]);

    $medicine = Medicine::findOrFail($request->medicine_id);
    
    // Ищем запись на опте, где остаток больше или равен запрошенному
    $wholesale = WholesaleStorage::where('medicine_id', $request->medicine_id)
        ->where('quantity', '>', 0)
        ->first();

    if (!$wholesale || $wholesale->quantity < $request->transfer_qty) {
        return redirect()->back()->with('error', 'Ошибка: недостаточно упаковок на выбранном складе!');
    }

    $boxes = (int)$request->transfer_qty;
    $unitsPerBox = (int)($medicine->units_per_box ?? 1);
    
    // Математика: $TotalUnits = Boxes \times UnitsPerBox$
    $totalUnits = $boxes * $unitsPerBox;

    try {
        DB::transaction(function () use ($wholesale, $medicine, $boxes, $totalUnits) {
            // 1. Уменьшаем количество коробок на Опте
            $wholesale->decrement('quantity', $boxes);

            // 2. Добавляем штуки в Аптеку (Storage)
            $pharmacyStock = \App\Models\Storage::where('medicine_id', $medicine->id)->first();

            if ($pharmacyStock) {
                $pharmacyStock->increment('quantity', $totalUnits);
            } else {
                \App\Models\Storage::create([
                    'medicine_id' => $medicine->id,
                    'quantity'    => $totalUnits,
                    
                    'category'    => $medicine->category ?? 'Analgesic',
                    
                ]);
            }

            // 3. Обновляем глобальный остаток в таблице Medicines
            // Важно: мы вычитаем только то, что ушло из опта (в единицах)
            $medicine->decrement('total_quantity_units', $totalUnits);
        });

        return redirect()->back()->with('success', "Успешно переведено $boxes кор. ($totalUnits шт.) в аптеку!");
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Ошибка транзакции: ' . $e->getMessage());
    }
}

    public function destroy($id)
    {
        WholesaleStorage::findOrFail($id)->delete();
        return back()->with('success', 'Batch removed from Wholesale Warehouse.');
    }
}
