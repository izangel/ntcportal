<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Import the DB facade
use App\Models\Department; // Import the Department model

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Option 1: Using the DB Facade (more basic)
        DB::table('departments')->insert([
            ['name' => 'Computer Science', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Human Resources', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Finance', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Marketing', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Electrical Engineering', 'created_at' => now(), 'updated_at' => now()],
            // Add more departments as needed
        ]);

        // Option 2: Using the Eloquent Model (recommended if you have a model)
        // Department::create(['name' => 'Computer Science']);
        // Department::create(['name' => 'Human Resources']);
        // Department::create(['name' => 'Finance']);
        // Department::create(['name' => 'Marketing']);
        // Department::create(['name' => 'Electrical Engineering']);
        // // Make sure your Department model has 'name' in its $fillable array
    }
}