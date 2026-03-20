<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'EuroPharma Distribution',
                'email' => 'contact@europharma.com',
                'address' => 'Berlin, Germany',
                'phone' => '+49 30 123456'
            ],
            [
                'name' => 'Global Med Supply',
                'email' => 'sales@globalmed.com',
                'address' => 'New York, USA',
                'phone' => '+1 212 555 0199'
            ],
            [
                'name' => 'Asia Care Logistics',
                'email' => 'info@asiacare.jp',
                'address' => 'Tokyo, Japan',
                'phone' => '+81 3 9876 5432'
            ],
            [
                'name' => 'BioHealth Solutions',
                'email' => 'support@biohealth.uk',
                'address' => 'London, UK',
                'phone' => '+44 20 7946 0000'
            ],
            [
                'name' => 'Central City Pharma',
                'email' => null, 
                'address' => 'Local Warehouse St. 5',
                'phone' => '555-10-20'
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}