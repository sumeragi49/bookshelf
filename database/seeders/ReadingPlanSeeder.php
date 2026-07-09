<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReadingPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $param = [
            'user_id' => '1',
            'book_id' => '1',
            'target_date' => '2026-08-06',
            'status' => '0',
        ];
        DB::table('reading_plan')->insert($param);

        $param = [
            'user_id' => '1',
            'book_id' => '2',
            'target_date' => '2026-08-06',
            'status' => '1',
        ];
        DB::table('reading_plan')->insert($param);

        $param = [
            'user_id' => '1',
            'book_id' => '3',
            'target_date' => '2026-08-06',
            'completed_at' => '2026-07-01',
            'status' => '2',
        ];
        DB::table('reading_plan')->insert($param);

        $param = [
            'user_id' => '2',
            'book_id' => '1',
            'target_date' => '2026-08-06',
            'status' => '0',
        ];
        DB::table('reading_plan')->insert($param);

        $param = [
            'user_id' => '3',
            'book_id' => '2',
            'target_date' => '2026-08-06',
            'status' => '1',
        ];
        DB::table('reading_plan')->insert($param);
    }
}
