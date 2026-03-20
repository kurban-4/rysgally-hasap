<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
public function run(): void
    {
        $employees = [
            [
                
                "name" => "Omar",
                
                "phone" => "+99362675705",
                "schedule"=>"full-time",
                "salary"=> "5000",
                "position"=> "Son of the chef",
            ],
            [
                
                "name" => "Hydyr",
                
                "phone" => "+99365532463",
                "schedule"=> "part-time",
                "salary"=> "4500",
                "position"=> "Just bud",
            ]
        ];
        foreach ($employees as $employee) {
            \App\Models\Employee::create($employee);
        }
    }
}
