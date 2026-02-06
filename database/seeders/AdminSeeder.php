<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin - Full access (Dashboard, Master Data, 360 Degree, Transaction, Financial)
        Admin::create([
            'name' => 'Admin Kampung Telaga',
            'email' => 'admin@kampungtelaga.com',
            'password' => Hash::make('admin123'),
            'phone' => '081234567890',
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Pengelola - Limited access (Dashboard & Financial only)
        Admin::create([
            'name' => 'Pengelola Keuangan',
            'email' => 'pengelola@kampungtelaga.com',
            'password' => Hash::make('pengelola123'),
            'phone' => '081234567891',
            'role' => 'pengelola',
            'is_active' => true,
        ]);
    }
}