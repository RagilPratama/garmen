<?php

namespace Database\Seeders;

use App\Models\Toko;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TokoSeeder extends Seeder
{
    public function run(): void
    {
        // Create Toko Jomei
        $jomei = Toko::create([
            'nama_toko' => 'Jomei',
            'kode_toko' => 'JMI',
            'alamat' => 'Alamat Toko Jomei',
            'telepon' => '081234567890',
            'is_active' => true,
        ]);

        // Create Toko Kamiko
        $kamiko = Toko::create([
            'nama_toko' => 'Kamiko',
            'kode_toko' => 'KMK',
            'alamat' => 'Alamat Toko Kamiko',
            'telepon' => '081234567891',
            'is_active' => true,
        ]);

        // Create Admin Toko Jomei
        User::create([
            'name' => 'Admin Jomei',
            'email' => 'jomei@garmen.com',
            'password' => Hash::make('password'),
            'role' => 'toko_jomei',
            'toko_id' => $jomei->id,
        ]);

        // Create Admin Toko Kamiko
        User::create([
            'name' => 'Admin Kamiko',
            'email' => 'kamiko@garmen.com',
            'password' => Hash::make('password'),
            'role' => 'toko_kamiko',
            'toko_id' => $kamiko->id,
        ]);

        // Update existing admin user
        $admin = User::where('email', 'admin@garmen.com')->first();
        if ($admin) {
            $admin->update(['role' => 'admin']);
        } else {
            // Create admin if not exists
            User::create([
                'name' => 'Super Admin',
                'email' => 'admin@garmen.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'toko_id' => null,
            ]);
        }
    }
}
