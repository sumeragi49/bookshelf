<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $param = [
            'name' => '小説',
        ];
        DB::table('genres')->insert($param);

        $param = [
            'name' => 'ビジネス',
        ];
        DB::table('genres')->insert($param);

        $param = [
            'name' => '技術書',
        ];
        DB::table('genres')->insert($param);

        $param = [
            'name' => '自己啓発',
        ];
        DB::table('genres')->insert($param);

        $param = [
            'name' => 'エッセイ',
        ];
        DB::table('genres')->insert($param);

        $param = [
            'name' => '歴史',
        ];
        DB::table('genres')->insert($param);

        $param = [
            'name' => '科学',
        ];
        DB::table('genres')->insert($param);

        $param = [
            'name' => '芸術',
        ];
        DB::table('genres')->insert($param);

        $param = [
            'name' => '料理',
        ];
        DB::table('genres')->insert($param);

        $param = [
            'name' => '旅行',
        ];
        DB::table('genres')->insert($param);
    }
}
