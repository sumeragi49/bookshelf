<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\InfoNotification;

class InformationNotification extends Notification
{
    use Queueable;

    protected $type;
    protected $record;

    /**
     * Create a new notification instance.
     */
    public function __construct($type, $record)
    {
        $this->type = $type;
        $this->record = $record;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        //return ['mail'];
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    //public function toMail(object $notifiable): MailMessage
    //{
        //return (new MailMessage)
                    //->line('The introduction to the notification.')
                    //->action('Notification Action', url('/'))
                    //->line('Thank you for using our application!');
    //}

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase($notifiable): array
    {
        $message = match ($this->type) {
            'three_days_before' => "「{$this->record->book->title}」の目標読了日まであと3日です",
            'on_due_date' => "「{$this->record->book->title}」の目標読了日です",
            'three_days_after' => "「{$this->record->book->title}」の目標読了日から3日過ぎています",
            default => "「{$this->record->book->title}」に関するお知らせ",
        };
        //dataカラムに全て入る。
        return [
            'reading_plan_id' => $this->record->id,
            'body' => $message,
            'title' => $this->record->book->title,
        ];
    }
}
