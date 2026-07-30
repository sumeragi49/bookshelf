<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;
use App\Notifications\InformationNotification;
use App\Models\User;
use App\Models\InfoNotification;
use App\Models\ReadingPlan;
use Carbon\Carbon;

class NotificationTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    protected $seed = true;

    public function test_index_notification()
    {
        $user = User::find(1);

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertStatus(200);
        $response->assertSee('通知一覧')
                 ->assertSee('未読')
                 ->assertSee('吾輩は猫である')
                 ->assertSee('「吾輩は猫である」に関するお知らせ');
    }

    public function test_store_notification()
    {
        $user = User::find(1);

        $notification = InfoNotification::first();

        $readAt = now();

        $updateNotification = [
            'read_at' => $readAt,
        ];

        $response = $this->actingAs($user)->post(route('notifications.read', $notification->id), $updateNotification);

        $response->assertStatus(302);
        $this->assertDatabaseHas('Notifications', [
            'read_at' => $readAt,
            'updated_at' => $readAt,
        ]);
    }

    public function test_send_notification()
    {
        $user = User::find(1);

        Notification::fake();

        $this->artisan('app:send-notifications');

        Notification::assertSentTo(
            $user, 
            InformationNotification::class
        );
    }

    public function test_send_three_days_before_notification()
    {
        $user = User::find(1);

        $readingPlan = $user->readingPlans()->create([
            'user_id' => $user->id,
            'book_id' => 1,
            'target_date' => now()->subDays(3),
            'completed_at'=> null,
            'status' => 0,
        ]);

        Notification::fake();

        $this->artisan('app:send-notifications');

        Notification::assertSentTo(
            $user,
            InformationNotification::class
        );
    }

    public function test_send_on_days_notification()
    {
        $user = User::find(1);

        $readingPlan = $user->readingPlans()->create([
            'user_id' => $user->id,
            'book_id' => 1,
            'target_date' => now(),
            'completed_at'=> null,
            'status' => 0,
        ]);

        Notification::fake();

        $this->artisan('app:send-notifications');

        Notification::assertSentTo(
            $user,
            InformationNotification::class
        );
    }

    public function test_send_three_days_after_notification()
    {
        $user = User::find(1);

        $readingPlan = $user->readingPlans()->create([
            'user_id' => $user->id,
            'book_id' => 1,
            'target_date' => now()->addDays(3),
            'completed_at'=> null,
            'status' => 0,
        ]);

        Notification::fake();

        $this->artisan('app:send-notifications');

        Notification::assertSentTo(
            $user,
            InformationNotification::class
        );
    }

    public function test_prune_notification()
    {
        $user = User::find(1);

        $notification = $user->notifications()->create([
            'id' => Str::uuid(),
            'type' => 'App\Notifications\InformationNotification',
            'data' => [],
            'read_at' => null,
            'created_at' => now()->subDay(21),
        ]);

        $this->artisan('app:prune-notifications');

        $this->assertDatabaseMissing('notifications', [
            'id' => $notification->id,
        ]);
    }

    public function test_send_notification_schedule_midnight()
    {
        $schedule = app(Schedule::class);

        $this->travelTo(now()->setTime(0, 0, 0));

        $events = collect($schedule->events())->filter(function ($event) {
            return $event->isDue(app());
        });

        $this->assertTrue(
            $events->contains(fn ($event) => str_contains($event->command, 'app:send-notifications')),
        );
    }

    public function test_prune_notification_schedule_one_am()
    {
        $schedule = app(Schedule::class);

        $this->travelTo(now()->setTime(1, 0, 0));

        $events = collect($schedule->events())->filter(function ($event) {
            return $event->isDue(app());
        });

        $this->assertTrue(
            $events->contains(fn ($event) => str_contains($event->command, 'app:prune-notifications')),
        );
    }

    public function test_send_notification_store()
    {
        $user = User::find(1);

        $record = ReadingPlan::with('user', 'book')
               -> find(1);

        $type = 'default';

        $user->notify(new InformationNotification($type, $record));

        $notification = [
            'reading_plan_id' => 1,
            'body' => "「吾輩は猫である」に関するお知らせ",
            'title' => '吾輩は猫である',
        ];

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'data' =>json_encode($notification),
        ]);
    }

    public function test_send_notification_three_before_days_store()
    {
        $user = user::find(1);

        $record = $user->readingPlans()->create([
            'user_id' => $user->id,
            'book_id' => 1,
            'target_date' => now()->subDays(3),
            'completed_at'=> null,
            'status' => 0,
        ]);

        $type = 'three_days_before';

        $user->notify(new InformationNotification($type, $record));

        $notification = [
            'reading_plan_id' => $record->id,
            'body' => "「吾輩は猫である」の目標読了日まであと3日です",
            'title' => '吾輩は猫である',
        ];

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'data' =>json_encode($notification),
        ]);
    }

    public function test_send_notification_on_day_store()
    {
        $user = user::find(1);

        $record = $user->readingPlans()->create([
            'user_id' => $user->id,
            'book_id' => 1,
            'target_date' => now(),
            'completed_at'=> null,
            'status' => 0,
        ]);

        $type = 'on_due_date';

        $user->notify(new InformationNotification($type, $record));

        $notification = [
            'reading_plan_id' => $record->id,
            'body' => "「吾輩は猫である」の目標読了日です",
            'title' => '吾輩は猫である',
        ];

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'data' =>json_encode($notification),
        ]);
    }

    public function test_send_notification_three_after_days_store()
    {
        $user = user::find(1);

        $record = $user->readingPlans()->create([
            'user_id' => $user->id,
            'book_id' => 1,
            'target_date' => now()->addDays(3),
            'completed_at'=> null,
            'status' => 0,
        ]);

        $type = 'three_days_after';

        $user->notify(new InformationNotification($type, $record));

        $notification = [
            'reading_plan_id' => $record->id,
            'body' => "「吾輩は猫である」の目標読了日から3日過ぎています",
            'title' => '吾輩は猫である',
        ];

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'data' =>json_encode($notification),
        ]);
    }
}
