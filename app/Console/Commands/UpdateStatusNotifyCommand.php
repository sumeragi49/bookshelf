<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Enums\ReadingPlanStatus;
use App\Models\User;
use App\Models\ReadingPlan;
use App\Notifications\InformationNotification;

use Illuminate\Console\Command;

class UpdateStatusNotifyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-notifications';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '期日になった読書計画の状態を失効に変更しました';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = Carbon::today();
        $expirationDate = $days->copy()->addDay();

        $updateCount = 0 ;
        
        ReadingPlan:: where('status', '!=', 'completed')
                   -> where('target_date', '<', $expirationDate)
                   -> where('status', '!=', ReadingPlanStatus::Expired)
                   -> chunk(100, function ($plans) use (&$updateCount) {
                        foreach ($plans as $plan) {
                            $plan->update([
                                'status' => 'expired',
                            ]);
                        $updateCount++;
                        }
                   });

        $this->info("{$updateCount}件のstatusをexpiredに変更しました。");
    }
}
