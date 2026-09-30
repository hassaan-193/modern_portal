<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MemoAcknowledged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $memoId;
    public $memoTitle;
    public $userId;
    public $userName;
    public $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct($memoId, $memoTitle, $userId, $userName)
    {
        $this->memoId = $memoId;
        $this->memoTitle = $memoTitle;
        $this->userId = $userId;
        $this->userName = $userName;
        $this->timestamp = now()->toIso8601String();
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('memos'),
            new Channel('portal-notifications'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'memo.acknowledged';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'memo_id' => $this->memoId,
            'memo_title' => $this->memoTitle,
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'timestamp' => $this->timestamp,
            'message' => "{$this->userName} signed and acknowledged \"{$this->memoTitle}\".",
        ];
    }
}
