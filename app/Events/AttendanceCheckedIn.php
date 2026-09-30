<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendanceCheckedIn implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $userName;
    public $date;
    public $time;
    public $location;
    public $status;
    public $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct($userId, $userName, $date, $time, $location = 'Main Office', $status = 'Present')
    {
        $this->userId = $userId;
        $this->userName = $userName;
        $this->date = $date;
        $this->time = $time;
        $this->location = $location;
        $this->status = $status;
        $this->timestamp = now()->toIso8601String();
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('attendance'),
            new Channel('portal-notifications'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'attendance.checked.in';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'date' => $this->date,
            'time' => $this->time,
            'location' => $this->location,
            'status' => $this->status,
            'timestamp' => $this->timestamp,
            'message' => "{$this->userName} checked in at {$this->time} ({$this->location}).",
        ];
    }
}
