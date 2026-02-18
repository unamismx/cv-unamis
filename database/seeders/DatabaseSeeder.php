<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CatalogSeeder::class);

        User::updateOrCreate(
            ['email' => 'admin@unamis.mx'],
            [
                'name' => 'Administrador UNAMIS',
                'password' => Hash::make('Admin12345!'),
                'role' => 'admin',
                'active' => true,
            ]
        );
    }
}
