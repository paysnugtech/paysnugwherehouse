<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


DB::table('countries')->insert([
    ['id' => Uuid::uuid4()->toString(), 'name' => 'Nigeria', 'currency_code' => 'NGN', 'currency_symbol' => '₦', 'status' => 1],
    ['id' => Uuid::uuid4()->toString(), 'name' => 'United States', 'currency_code' => 'USD', 'currency_symbol' => '$', 'status' => 1],
    // Add more country data as needed
]);

    }
}
