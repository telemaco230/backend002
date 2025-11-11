<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $clearPassword = fake()->password(8, 16);
        
        $user = User::factory()->create([
            'name' => 'sistema',
            'email' => 'sistema@backend.com',
            'password' => bcrypt($clearPassword),
        ]);

        $this->command->info('Usuario creado:');
        $this->command->info('Email: ' . $user->email);
        $this->command->info('Contraseña: ' . $clearPassword);
    }
}
