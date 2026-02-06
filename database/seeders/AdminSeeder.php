<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::create([
            'username'     => 'admin',
            'password'     => Hash::make('7rD7N1£Rx,Am@!u{lol_56s{6k)$|#Swh0;H!KV&'), // Ganti dengan password yang diinginkan
        ]);
    }
}