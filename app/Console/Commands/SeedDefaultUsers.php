<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Toko;
use Illuminate\Support\Facades\Hash;

class SeedDefaultUsers extends Command
{
    protected $signature = 'garmen:seed-default-users {--force : Overwrite existing users passwords and roles}';

    protected $description = 'Create or update default users (superadmin, admingarmen, adminkantor, adminjomei, adminkamiko)';

    public function handle(): int
    {
        $force = $this->option('force');

        $defaults = [
            ['name' => 'Super Admin', 'email' => 'superadmin@garmen.com', 'role' => User::ROLE_SUPERADMIN],
            ['name' => 'Admin Garmen', 'email' => 'admingarmen@garmen.com', 'role' => User::ROLE_ADMIN_GUDANG],
            ['name' => 'Admin Kantor', 'email' => 'kantor@garmen.com', 'role' => User::ROLE_ADMIN_KANTOR],
            ['name' => 'Admin Jomei', 'email' => 'jomei@garmen.com', 'role' => User::ROLE_ADMIN_JOMEI, 'toko_code' => 'JMI'],
            ['name' => 'Admin Kamiko', 'email' => 'kamiko@garmen.com', 'role' => User::ROLE_ADMIN_KAMIKO, 'toko_code' => 'KMK'],
        ];

        foreach ($defaults as $d) {
            $tokoId = null;
            if (!empty($d['toko_code'])) {
                $tokoId = Toko::where('kode_toko', $d['toko_code'])->first()?->id;
            }

            $user = User::where('email', $d['email'])->first();

            if (!$user) {
                User::create([
                    'name' => $d['name'],
                    'email' => $d['email'],
                    'password' => Hash::make('password'),
                    'role' => $d['role'],
                    'toko_id' => $tokoId,
                ]);

                $this->info("Created: {$d['email']}");
            } else {
                $user->name = $d['name'];
                $user->role = $d['role'];
                $user->toko_id = $tokoId;
                if ($force) {
                    $user->password = Hash::make('password');
                }
                $user->save();

                $this->info("Updated: {$d['email']}" . ($force ? ' (password reset)' : ''));
            }
        }

        $this->newLine();
        $this->info('Default users seeded. Default password: "password"');

        return 0;
    }
}
