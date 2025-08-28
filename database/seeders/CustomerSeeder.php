<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'username' => 'customer1',
                'name'     => 'Default Customer',
                'email'    => 'customer@bookstore.local',
                'password' => 'Customer@123',   // hashed by mutator
                'phone'    => '0198765432',
                'address'  => '123 Customer Street',
                'points'   => 100,              // starter member points
            ],
            // Add more default customers if needed…
        ];

        foreach ($customers as $c) {
            // Use email as the unique key for customers
            User::updateOrCreate(['email' => $c['email']], $c + ['role' => 'customer']);
        }
    }
}
