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
            'target_date' => '2026-08-03',
            'status' => 'in_progress',
        ];
        DB::table('reading_plan')->insert($param);

        $param = [
            'user_id' => '1',
            'book_id' => '2',
            'target_date' => '2026-08-04',
            'status' => 'in_progress',
        ];
        DB::table('reading_plan')->insert($param);

        $param = [
            'user_id' => '1',
            'book_id' => '3',
            'target_date' => '2026-08-05',
            'status' => 'expired',
        ];
        DB::table('reading_plan')->insert($param);

        $param = [
            'user_id' => '1',
            'book_id' => '4',
            'target_date' => '2026-08-06',
            'status' => 'expired',
        ];
        DB::table('reading_plan')->insert($param);

        $param = [
            'user_id' => '1',
            'book_id' => '5',
            'target_date' => '2026-08-07',
            'completed_at' => '2026-07-01',
            'status' => 'completed',
        ];
        DB::table('reading_plan')->insert($param);

        $param = [
            'user_id' => '1',
            'book_id' => '5',
            'target_date' => '2026-08-10',
            'completed_at' => '2026-07-01',
            'status' => 'completed',
        ];
        DB::table('reading_plan')->insert($param);

        $param = [
            'user_id' => '2',
            'book_id' => '1',
            'target_date' => '2026-08-06',
            'status' => 'in_progress',
        ];
        DB::table('reading_plan')->insert($param);

        $param = [
            'user_id' => '3',
            'book_id' => '2',
            'target_date' => '2026-08-06',
            'status' => 'in_progress',
        ];
        DB::table('reading_plan')->insert($param);
    }
}
