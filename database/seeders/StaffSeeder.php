<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $staff = [
            [
                'username' => 'staff1',
                'name'     => 'Default Staff',
                'email'    => 'staff@bookstore.local',
                'password' => 'Staff@123',     // hashed by mutator
                'phone'    => '0111111111',
                'address'  => 'Bookstore HQ',
            ],
            // Add more default staff here if you like…
            // ['username'=>'staff2','name'=>'Staff Two','email'=>'staff2@bookstore.local','password'=>'Staff@123','phone'=>'01122223333','address'=>'Branch A'],
        ];

        foreach ($staff as $s) {
            User::updateOrCreate(
                ['username' => $s['username']],     // unique key
                $s + ['role' => 'staff', 'points' => 0]
            );
        }
    }
}
