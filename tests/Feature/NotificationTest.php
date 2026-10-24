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
use App\Models\Book;
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

    public function test_user_can_index_notification()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
        $notification = InfoNotification::factory()->create([
            'notifiable_id' => $user->id,
            'data' => json_encode([
                'reading_plan_id' => $readingPlan->id,
                'body' => $book->title . 'に関するお知らせ',
                'title' => $book->title,
            ], JSON_UNESCAPED_UNICODE)
        ]);

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertStatus(200);
        $response->assertSee('通知一覧')
                 ->assertSee('未読')
                 ->assertSee($book->title)
                 ->assertSee($book->title . 'に関するお知らせ');
    }

    public function test_guest_cannot_index_notification()
    {
        $response = $this->get(route('notifications.index'));

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_user_can_store_notification()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
        $notification = InfoNotification::factory()->create([
            'notifiable_id' => $user->id,
            'data' => json_encode([
                'reading_plan_id' => $readingPlan->id,
                'body' => $book->title . 'に関するお知らせ',
                'title' => $book->title,
            ], JSON_UNESCAPED_UNICODE)
        ]);

        $readAt = now();

        $updateNotification = [
            'read_at' => $readAt,
        ];

        $response = $this->actingAs($user)->post(route('notifications.read', $notification->id), $updateNotification);

        $response->assertStatus(302);
        $this->assertDatabaseHas('notifications', [
            'read_at' => $readAt,
            'updated_at' => $readAt,
        ]);
    }
    //テストが通過するときと通過しないときがある
    public function test_server_can_send_notification()
    {
        Notification::fake();

        $user = User::factory()->create();
        $book = Book::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
        //通知をサーバーから受け取る(通知の作成も行われる)のに通知を手動で作成している。
        /*
        $notification = InfoNotification::factory()->create([
            'notifiable_id' => $user->id,
            'data' => json_encode([
                'reading_plan_id' => $readingPlan->id,
                'body' => $book->title . 'に関するお知らせ',
                'title' => $book->title,
            ], JSON_UNESCAPED_UNICODE)
        ]);
        */

        $this->artisan('app:send-notifications');

        Notification::assertSentTo(
            $user,
            InformationNotification::class
        );
    }

    public function test_send_three_days_before_notification()
    {
        Notification::fake();
        //このtest内でnow()の時間を固定化する
        $this->travelTo(now()->startOfDay());

        $user = User::factory()->create();
        $book = Book::factory()->create();
        /*
        The expected [App\Notifications\InformationNotification] notification was not sent.
        Failed asserting that false is true.のエラー
        */
        /*PHPUnit::assertTrue(
        90▕             $this->sent($notifiable, $notification, $callback)->count() > 0,
        91▕             "The expected [{$notification}] notification was not sent.
        */
        /*
        'user_id'と'book_id'が二重登録になるためエラーが起きる'user_id'は$user->idだが'book_id'は$book->idと新しく作成されたbookのidで競合しエラーが起きる
        $user->readingPlans()がエラーの元
        */
        //$readingPlan = $user->readingPlans()->create([
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->addDays(3)->toDateString(),
            'completed_at'=> null,
            'status' => 'in_progress',
        ]);

        $this->artisan('app:send-notifications');

        Notification::assertSentTo(
            $user,
            InformationNotification::class,
            function ($notification) use ($user, $book) {
                
                $data = $notification->toDatabase($user);

                //dd($data);
                //str_containsは「$data['body']の中に$book->titleが含まれることを確認する」というニュアンス
                return isset($data['body']) &&
                        str_contains($data['body'], $book->title) &&
                        str_contains($data['body'], 'の目標読了日まであと3日です');
            }
        );

        //時間の固定化をなくす
        $this->travelBack();
    }

    public function test_send_on_days_notification()
    {
        Notification::fake();

        $this->travelTo(now()->startOfDay());

        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->toDateString(),
            'completed_at'=> null,
            'status' => 'in_progress',
        ]);

        $this->artisan('app:send-notifications');

        Notification::assertSentTo(
            $user,
            InformationNotification::class,
            function ($notification) use ($user, $book) {
                
                $data = $notification->toDatabase($user);

                //dd($data);
                //str_containsは「$data['body']の中に$book->titleが含まれることを確認する」というニュアンス
                return isset($data['body']) &&
                        str_contains($data['body'], $book->title) &&
                        str_contains($data['body'], 'の目標読了日です');
            }
        );

        $this->travelBack();
    }

    public function test_send_three_days_after_notification()
    {
        Notification::fake();

        $this->travelTo(now()->startOfDay());

        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = $user->readingPlans()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->subDays(3),
            'completed_at'=> null,
            'status' => 'in_progress',
        ]);

        $this->artisan('app:send-notifications');

        Notification::assertSentTo(
            $user,
            InformationNotification::class,
            function ($notification) use ($user, $book) {
                
                $data = $notification->toDatabase($user);

                //dd($data);
                //str_containsは「$data['body']の中に$book->titleが含まれることを確認する」というニュアンス
                return isset($data['body']) &&
                        str_contains($data['body'], $book->title) &&
                        str_contains($data['body'], 'の目標読了日から3日過ぎています');
            }
        );

        $this->travelBack();
    }

    public function test_user_can_prune_expired_notification()
    {
        $this->travelTo(now()->startOfDay());

        $user = User::factory()->create();
        $book = Book::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
        $notification = InfoNotification::factory()->create([
            'notifiable_id' => $user->id,
            'data' => json_encode([
                'reading_plan_id' => $readingPlan->id,
                'body' => $book->title . 'に関するお知らせ',
                'title' => $book->title,
            ], JSON_UNESCAPED_UNICODE),
            'created_at' => now()->subDays(21)
        ]);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id
        ]);

        $this->artisan('app:prune-notifications');

        $this->assertDatabaseMissing('notifications', [
            'id' => $notification->id,
        ]);

        $this->travelBack();
    }

    public function test_send_notification_schedule_eight_pm()
    {
        $schedule = app(Schedule::class);

        $this->travelTo(now()->setTime(20, 0, 0));

        $events = collect($schedule->events())->filter(function ($event) {
            return $event->isDue(app());
        });

        $this->assertTrue(
            $events->contains(fn ($event) => str_contains($event->command, 'app:send-notifications')),
        );
    }

    public function test_prune_notification_schedule_eight_pm()
    {
        $schedule = app(Schedule::class);

        $this->travelTo(now()->setTime(20, 0, 0));

        $events = collect($schedule->events())->filter(function ($event) {
            return $event->isDue(app());
        });

        $this->assertTrue(
            $events->contains(fn ($event) => str_contains($event->command, 'app:prune-notifications')),
        );
    }

    public function test_send_notification_three_before_days_store()
    {
        $user = user::factory()->create();
        $book = Book::factory()->create();

        $record = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->subDays(3),
            'completed_at'=> null,
            'status' => 'in_progress',
        ]);

        $type = 'three_days_before';

        $user->notify(new InformationNotification($type, $record));

        $notification = [
            'reading_plan_id' => $record->id,
            'body' => '「' . $book->title . '」の目標読了日まであと3日です',
            'title' => $book->title,
        ];

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'data' =>json_encode($notification),
        ]);
    }

    public function test_send_notification_on_day_store()
    {
        $user = user::factory()->create();
        $book = Book::factory()->create();

        $record = $user->readingPlans()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now(),
            'completed_at'=> null,
            'status' => 'in_progress',
        ]);

        $type = 'on_due_date';

        $user->notify(new InformationNotification($type, $record));

        $notification = [
            'reading_plan_id' => $record->id,
            'body' => '「' . $book->title . '」の目標読了日です',
            'title' => $book->title,
        ];

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'data' =>json_encode($notification),
        ]);
    }

    public function test_send_notification_three_after_days_store()
    {
        $user = user::factory()->create();
        $book = Book::factory()->create();

        $record = $user->readingPlans()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->addDays(3),
            'completed_at'=> null,
            'status' => 'in_progress',
        ]);

        $type = 'three_days_after';

        $user->notify(new InformationNotification($type, $record));

        $notification = [
            'reading_plan_id' => $record->id,
            'body' => '「' . $book->title . '」の目標読了日から3日過ぎています',
            'title' => $book->title,
        ];

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'data' =>json_encode($notification),
        ]);
    }
}
