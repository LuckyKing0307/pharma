<?php

namespace App\Notifications;

use App\Models\ExportFiles;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Orchid\Platform\Notifications\DashboardChannel;
use Orchid\Platform\Notifications\DashboardMessage;

class TaskCompleted extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $file;
    public $time;
    public function __construct($file_id , $time)
    {
        $this->file = ExportFiles::find($file_id);
        $this->file->uploaded = 1;
        $this->file->save();
        $this->time = $time/60;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [DashboardChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toDashboard(object $notifiable): DashboardMessage
    {
        return (new DashboardMessage)
            ->title('Your New Report '.$this->file->file_url. ' Taken Time: '. $this->time. 'Minutes')
            ->message('Completed')
            ->action(url('/'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
