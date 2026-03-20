<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medicine;
use App\Models\Storage;


class MedicineController extends Controller
{
    public function index()
    {
        $medicines = Medicine::paginate(50);
        $storage = Storage::paginate(50);
        $categories = Medicine::distinct()->pluck('category');
        $lowStockCount = Storage::where('quantity', '<', 10)->count();
        
        return view('storage.index', compact('medicines', 'storage', 'categories', 'lowStockCount'));
    }

    public function show($id)
    {
        $medicine = Medicine::findOrFail($id);
        $storage = Storage::where('medicine_id', $id)->first();

        // Считаем коробки и остаток
        $totalUnits = $storage ? $storage->quantity : 0;
        $unitsPerBox = $medicine->units_per_box ?: 1; 

        $boxes = floor($totalUnits / $unitsPerBox);
        $remainingUnits = $totalUnits % $unitsPerBox;

        return view("medicine.show", compact('medicine', 'storage', 'boxes', 'remainingUnits'));
    }

    public function create()
    {
        $categories = Medicine::distinct()->pluck('category');
        return view('medicine.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            
            'price'          => 'required|numeric',
            'quantity'       => 'required|integer',
            'category'       => 'nullable',
            'units_per_box'  => 'nullable|integer|min:1',
            'price_unit'     => 'nullable|numeric',
            
        ]);

 
        // Логика упаковок
        $upb = $request->units_per_box ?: 1;
        $totalUnits = $request->quantity * $upb;

        // Создание лекарства
        $medicine = Medicine::create([
            'name'          => $request->name,
            
            'barcode'       => $request->barcode,
            'description'   => $request->description ?? '',
            'price'         => $request->price,
            'discount'      => $request->discount ?? 0,
            'category'      => $request->category ?? 'General',
            'manufacturer'  => $request->manufacturer ?? 'Unknown',
            'produced_date' => $request->produced_date,
            'expiry_date'   => $request->expiry_date,
            'price_unit'    => $request->price_unit,
            'units_per_box' => $upb,
        ]);

        // Дополнительные фото (если есть)
       
        Storage::create([
            'medicine_id' => $medicine->id,
            'quantity'    => $totalUnits,
            'category'    => $request->category ?? 'General',
            
        ]);

        return redirect()->route('storage.index')->with('success', 'Medicine added successfully!');
    }

    public function search(Request $request)
    {
        $query = $request->get('search');

        if (empty($query)) {
            return response()->json([]);
        }

        $medicines = Medicine::where('name', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get();

        return response()->json($medicines);
    }
}