<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $param = [
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ];
        DB::table('users')->insert($param);

        $param = [
            'name' => '鈴木花子',
            'email' => 'suzuki@example.com',
            'password' => Hash::make('password'),
        ];
        DB::table('users')->insert($param);

        $param = [
            'name' => '田中一郎',
            'email' => 'tanaka@example.com',
            'password' => Hash::make('password'),
        ];
        DB::table('users')->insert($param);

        $param = [
            'name' => '佐藤美咲',
            'email' => 'sato@example.com',
            'password' => Hash::make('password'),
        ];
        DB::table('users')->insert($param);

        $param = [
            'name' => '高橋健太',
            'email' => 'takahashi@example.com',
            'password' => Hash::make('password'),
        ];
        DB::table('users')->insert($param);
    }
}
