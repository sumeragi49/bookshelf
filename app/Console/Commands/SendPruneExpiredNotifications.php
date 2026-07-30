<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\InfoNotification;

class SendPruneExpiredNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:prune-notifications {--days=20}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '期限切れの古い通知データを削除します';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $expirationDate = now()->subDay($days);

        $deletedCount = InfoNotification::where('created_at', '<', $expirationDate)
                     -> delete();

        $this->info("{$deletedCount}件の通知を削除しました。");
    }
}
