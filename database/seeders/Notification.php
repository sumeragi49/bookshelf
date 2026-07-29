<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;

class Notification extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $param = [
            'id' => Str::uuid(),
            'type' => 'App\Notifications\InformationNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => '1',
            'data' => '{"reading_plan_id":1,"body":"「吾輩は猫である」に関するお知らせ","title":"吾輩は猫である"}',
            'created_at' =>now()
        ];
        DB::table('notifications')->insert($param);
    }
}
