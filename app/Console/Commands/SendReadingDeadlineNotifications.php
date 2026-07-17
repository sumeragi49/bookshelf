<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\User;
use App\Models\ReadingPlan;
use App\Notifications\InformationNotification;

class SendReadingDeadlineNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-reading-notifications';
    protected $description = '目標読了日に応じた通知を自動送信します';

    /**
     * The console command description.
     *
     * @var string
     */
    //protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        //3日後が目標日(3日前)
        $threeDaysBefore = $today->copy()->addDays(3);
        //三日前が目標日(3日後)
        $threeDaysAfter = $today->copy()->subDays(3);

        $this->info("=== 判定基準日 ===");
        $this->info("3日前判定: " . $threeDaysBefore->toDateString());
        $this->info("当日判定: " . $today->toDateString());
        $this->info("3日後判定: " . $threeDaysAfter->toDateString());
        $this->info("======");
        //作成した「読書計画」とそれの作成者のデータを取得
        $records = ReadingPlan::with('user', 'book')->get();

        $this->info("取得できた件数: " . $records->count() . "件");

        foreach ($records as $record) {

            if (!$record->user) {
                continue;
            }

            $targetDate = Carbon::parse($record->target_date)->startOfDay();

            $this->info("[{$record->id}]の目標日: " . $targetDate->toDateString());

            $type = '';

            if ($targetDate->equalTo($threeDaysBefore)) {
                $this->info("-> 3日前にマッチ");
                $type = 'three_days_before';
            } elseif ($targetDate->equalTo($today)) {
                $this->info("-> 当日にマッチ");
                $type = 'on_due_date';
            } elseif ($targetDate->equalTo($threeDaysAfter)) {
                $this->info("-> 3日後にマッチ");
                $type = 'three_days_after';
            } else {
                $this->info("-> bell");
                $type = 'default';
            } //else {
                //条件以外は通知をスキップする場合
                //$this->error("-> 条件に合致無");
                //continue;
            //}

            if (!$record->user) {
                $this->error("-> ユーザーに紐づきなし");
                continue;
            }

            $record->user->notify(new InformationNotification($type, $record));
            $this->info("-> [成功] [{$record->user->id}]への通知を保存");
        }

        $this->info('通知の送信が完了しました');
    }
}
