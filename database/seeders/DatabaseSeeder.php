<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(DivisiSeeder::class);
        $this->call(DepartmentSeeder::class);
        $this->call(SubdepartmentSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(DocumentSeeder::class);
    }
}
