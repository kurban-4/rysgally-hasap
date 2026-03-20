<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Medicine;
use App\Models\Storage;

class MedicineSeeder extends Seeder
{
    public function run(): void
    {
        // Наш единый список данных
        $medicines = [
            ['name' => 'Paracetamol', 'cat' => 'Analgesic', 'price' => 15.50, 'qty' => 50],
            ['name' => 'Ibuprofen', 'cat' => 'Anti-inflammatory', 'price' => 22.00, 'qty' => 80],
            ['name' => 'Amoxicillin', 'cat' => 'Antibiotic', 'price' => 45.00, 'qty' => 45],
            ['name' => 'Cough Syrup', 'cat' => 'Respiratory', 'price' => 30.00, 'qty' => 60],
            ['name' => 'Vitamin C 500mg', 'cat' => 'Vitamins', 'price' => 12.00, 'qty' => 200],
            ['name' => 'Loratadine', 'cat' => 'Antihistamine', 'price' => 18.50, 'qty' => 120],
            ['name' => 'Omeprazole', 'cat' => 'Gastrointestinal', 'price' => 35.00, 'qty' => 95],
            ['name' => 'Metformin', 'cat' => 'Antidiabetic', 'price' => 25.00, 'qty' => 110],
            ['name' => 'Amlodipine', 'cat' => 'Blood Pressure', 'price' => 28.00, 'qty' => 70],
            ['name' => 'Aspirin Cardio', 'cat' => 'Heart', 'price' => 20.00, 'qty' => 300],
            ['name' => 'Aspirin Cardio', 'cat' => 'Heart', 'price' => 20.00, 'qty' => 300],
            ['name' => 'Aspirin Cardio', 'cat' => 'Heart', 'price' => 20.00, 'qty' => 300],
            ['name' => 'Aspirin Cardio', 'cat' => 'Heart', 'price' => 20.00, 'qty' => 300],
            ['name' => 'Aspirin Cardio', 'cat' => 'Heart', 'price' => 20.00, 'qty' => 300],
            ['name' => 'Aspirin Cardio', 'cat' => 'Heart', 'price' => 20.00, 'qty' => 300],
            ['name' => 'Aspirin Cardio', 'cat' => 'Heart', 'price' => 20.00, 'qty' => 300],
            ['name' => 'Aspirin Cardio', 'cat' => 'Heart', 'price' => 20.00, 'qty' => 300],
            ['name' => 'Aspirin Cardio', 'cat' => 'Heart', 'price' => 20.00, 'qty' => 300],
            ['name' => 'Aspirin Cardio', 'cat' => 'Heart', 'price' => 20.00, 'qty' => 300],
            ['name' => 'Aspirin Cardio', 'cat' => 'Heart', 'price' => 20.00, 'qty' => 300],
            ['name' => 'Aspirin Cardio', 'cat' => 'Heart', 'price' => 20.00, 'qty' => 300],
            ['name' => 'Aspirin Cardio', 'cat' => 'Heart', 'price' => 20.00, 'qty' => 300],
            ['name' => 'Aspirin Cardio', 'cat' => 'Heart', 'price' => 20.00, 'qty' => 300],
            ['name' => 'Aspirin Cardio', 'cat' => 'Heart', 'price' => 20.00, 'qty' => 300],
            ['name' => 'Aspirin Cardio', 'cat' => 'Heart', 'price' => 20.00, 'qty' => 300],
            ['name' => 'Aspirin Cardio', 'cat' => 'Heart', 'price' => 20.00, 'qty' => 300],
            ['name' => 'Aspirin Cardio', 'cat' => 'Heart', 'price' => 20.00, 'qty' => 300],
            ['name' => 'Aspirin Cardio', 'cat' => 'Heart', 'price' => 20.00, 'qty' => 300],
            ['name' => 'Aspirin Cardio', 'cat' => 'Heart', 'price' => 20.00, 'qty' => 300],
            ['name' => 'Aspirin Cardio', 'cat' => 'Heart', 'price' => 20.00, 'qty' => 300],
            ['name' => 'Aspirin Cardio', 'cat' => 'Heart', 'price' => 20.00, 'qty' => 300],
            ['name' => 'Aspirin Cardio', 'cat' => 'Heart', 'price' => 20.00, 'qty' => 300],
            ['name' => 'Aspirin Cardio', 'cat' => 'Heart', 'price' => 20.00, 'qty' => 300],
            ['name' => 'Aspirin Cardio', 'cat' => 'Heart', 'price' => 20.00, 'qty' => 300],
            ['name' => 'Aspirin Cardio', 'cat' => 'Heart', 'price' => 20.00, 'qty' => 300],
            ['name' => 'Aspirin Cardio', 'cat' => 'Heart', 'price' => 20.00, 'qty' => 300],
        ];

foreach ($medicines as $index => $item) {
    $medicine = \App\Models\Medicine::create([
        'name' => $item['name'],
        'barcode'       => 'BAR' . str_pad($index + 1, 5, '0', STR_PAD_LEFT),
        'category' => $item['cat'],
        'price' => $item['price'],
        'description' => 'Standard dosage for ' . $item['cat'],
        'manufacturer' => 'Global Pharma Industries',
        'produced_date' => '2025-01-01',
        'expiry_date' => '2027-01-01',
        'barcode' => (string)(1000 + $index), 
    ]);
    \App\Models\Storage::create([
        'medicine_id' => $medicine->id,
        
        'quantity'    => $item['qty'] ?? 50,
        'category'    => $item['cat'],
    ]);
}
    }
}