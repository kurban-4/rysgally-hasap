<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sale;


class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $sales = [

        ];

        foreach ($sales as $sale) {
            \App\Models\Sale::create($sale);
        }
    }
}
