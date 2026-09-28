<?php

namespace App\Notifications;

use App\Models\Memo;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewMemoPublishedNotification extends Notification
{
    use Queueable;

    public Memo $memo;

    public function __construct(Memo $memo)
    {
        $this->memo = $memo;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $ref = $this->memo->reference_number ? " [{$this->memo->reference_number}]" : '';

        return [
            'title'   => 'New Memo: ' . $this->memo->title . $ref,
            'note'    => 'A new memo has been published. Please review and acknowledge.',
            'action'  => route('memos.show', $this->memo->id),
            'memo_id' => $this->memo->id,
        ];
    }

    public function toArray($notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
