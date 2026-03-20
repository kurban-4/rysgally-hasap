<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Storage;
use App\Models\Medicine;

class StorageController extends Controller
{
    public function index(Request $request)
    {
        $f_category = $request->category;
        $f_status = $request->status;
        
        $lowStockCount = Storage::where('quantity', '<', 10)->count();
        $query = Storage::with('medicine');

        if ($f_category) {
            $query->where('category', $f_category);
        }
        if ($f_status == 'low') {
            $query->where('quantity', '<', 10);
        }

        $storage = $query->latest()->paginate(50)->withQueryString();
        
        // Расчет коробок и листов только для отображения в списке
        $storage->getCollection()->transform(function ($item) {
            $perBox = $item->medicine->units_per_box ?: 10;
            $item->display_boxes = floor($item->quantity / $perBox);
            $item->display_sheets = $item->quantity % $perBox;
            return $item;
        });

        $categories = Storage::distinct()->pluck('category');

        return view('storage.index', compact('storage', 'categories', 'lowStockCount'));
    }

    public function edit($id)
    {
        $storage = Storage::with('medicine')->findOrFail($id);
        $categories = Medicine::distinct()->pluck('category');

        // Берем логику упаковки из модели Medicine
        $sheetsPerBox = $storage->medicine->units_per_box ?: 10; 
        
        $boxes = floor($storage->quantity / $sheetsPerBox);
        $sheets = $storage->quantity % $sheetsPerBox;

        return view('storage.edit', compact('storage', 'categories', 'boxes', 'sheets', 'sheetsPerBox'));
    }
public function update(Request $request, $id)
{
    $storageEntry = Storage::findOrFail($id);
    $medicine = $storageEntry->medicine;

    // 1. Correct the validation array
    $request->validate([
        'boxes'         => 'required|integer|min:0',
        'units_per_box' => 'required|integer|min:1',
        'barcode'       => 'nullable|string',
        'category'      => 'required',
        'price'         => 'nullable|numeric',
        'discount'      => 'nullable|integer|min:0|max:100',
        
    ]);

    $totalQuantity = $request->boxes * $request->units_per_box;

    
    $medicine->update([
        'barcode'       => $request->barcode,
        'units_per_box' => $request->units_per_box,
        'price'         => $request->price,
        'discount'      => $request->discount,
    ]);

    
    $storageEntry->update([
        'quantity' => $totalQuantity,
        'category' => $request->category
    ]);

    return redirect()->route('storage.index')->with('success', 'Data updated successfully!');
}
    public function destroy($id)
    {
        Storage::findOrFail($id)->delete();
        return redirect()->route('storage.index')->with('success', 'Deleted!');
    }
}