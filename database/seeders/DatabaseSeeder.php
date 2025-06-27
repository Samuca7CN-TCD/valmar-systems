<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        /*User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);*/

        /*$files = Storage::files('public/img/items');
        foreach ($files as $file)
        {
            Storage::delete($file);
        }

        $files = Storage::files('public/payment_records');
        foreach ($files as $file)
        {
            Storage::delete($file);
        }*/

        $this->call([
            // UserSeeder::class,
            // MeasurementUnitSeeder::class,
            // CategorySeeder::class,
            // ActionSeeder::class,
            DepartmentSeeder::class,
            // PaymentMethodSeeder::class,
            // AccountTypeSeeder::class,
            // BankSeeder::class,
            // ItemSeeder::class,
            // EmployeeSeeder::class,
            // UpdateMovementStatusSeeder::class,
        ]);
    }
}
