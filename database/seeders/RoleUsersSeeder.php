<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@garmen.com',
                'password' => Hash::make('password'),
                'role' => User::ROLE_SUPERADMIN,
                'toko_id' => null,
            ],
            [
                'name' => 'Admin Garmen',
                'email' => 'admingarmen@garmen.com',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN_GUDANG,
                'toko_id' => null,
            ],
            [
                'name' => 'Admin Kantor',
                'email' => 'kantor@garmen.com',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN_KANTOR,
                'toko_id' => null,
            ],
            [
                'name' => 'Admin Jomei',
                'email' => 'jomei@garmen.com',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN_JOMEI,
                'toko_id' => null, // Set manually later if needed
            ],
            [
                'name' => 'Admin Kamiko',
                'email' => 'kamiko@garmen.com',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN_KAMIKO,
                'toko_id' => null, // Set manually later if needed
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        $this->command->info('✅ Users with roles created successfully!');
        $this->command->info('');
        $this->command->info('Login credentials:');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('Super Admin    : superadmin@garmen.com / password');
        $this->command->info('Admin Garmen   : admingarmen@garmen.com / password');
        $this->command->info('Admin Kantor   : kantor@garmen.com / password');
        $this->command->info('Admin Jomei    : jomei@garmen.com / password');
        $this->command->info('Admin Kamiko   : kamiko@garmen.com / password');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }
}
