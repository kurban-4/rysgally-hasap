<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    use WithoutModelEvents;
    public function run(): void
    {
        $this->call([
        
        EmployeeSeeder::class,
        MedicineSeeder::class,
        SaleSeeder::class,
        StorageSeeder::class,
        SupplierSeeder::class
        ]);

        
        User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }


}


