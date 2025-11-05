<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        // Kiểm tra nếu chưa tồn tại thì mới tạo (để tránh duplicate)
        if (!User::where('username', 'admin')->exists()) {
            User::create([
                'username' => 'admin',
                'email' => 'admin90@gmail.com',
                'password' => Hash::make('123123'),
                'role' => 'admin',
            ]);
        }
    }
}