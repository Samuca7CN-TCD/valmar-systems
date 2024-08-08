<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');
        DB::table('users')->insert([
            [
                'name' => 'Samuel',
                'surname' => 'Correia Nascimento',
                'email' => 'samuca.7cn@gmail.com',
                'hierarchy' => 1,
                'password' => Hash::make('Lms1928$&valmar-system'),
                'profile_photo_path' => '/storage/app/public/img/samuel-correia-nascimento.jpeg',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Valdinei',
                'surname' => 'dos Santos Nascimento',
                'email' => 'valdinei@gmail.com',
                'hierarchy' => 1,
                'password' => Hash::make('197701'),
                'profile_photo_path' => '/storage/app/public/img/valdinei-dos-santos-nascimento.jpeg',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Maria',
                'surname' => 'Aparecida Santos',
                'email' => 'valdinei0733@gmail.com',
                'hierarchy' => 2,
                'password' => Hash::make('s1t3n3m'),
                'profile_photo_path' => '/storage/app/public/img/maria-aparecida-santos.jpeg',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Beatriz',
                'surname' => 'Cruz de Souza',
                'email' => 'valmarmetalurgica.itabuna@gmail.com',
                'hierarchy' => 3,
                'password' => Hash::make('s1t3n3m'),
                'profile_photo_path' => '/storage/app/public/img/beatriz-cruz-de-souza.jpeg',
                'created_at' => $now,
                'updated_at' => $now
            ],
        ]);
    }
}
