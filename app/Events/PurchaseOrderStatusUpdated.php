<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PurchaseOrderStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $poId;
    public $poNumber;
    public $status;
    public $total;
    public $updatedBy;
    public $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct($poId, $poNumber, $status, $total = 0, $updatedBy = 'System')
    {
        $this->poId = $poId;
        $this->poNumber = $poNumber;
        $this->status = $status;
        $this->total = $total;
        $this->updatedBy = $updatedBy;
        $this->timestamp = now()->toIso8601String();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('purchase-orders'),
            new Channel('portal-notifications'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'po.status.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'po_id' => $this->poId,
            'po_number' => $this->poNumber,
            'status' => $this->status,
            'total' => $this->total,
            'updated_by' => $this->updatedBy,
            'timestamp' => $this->timestamp,
            'message' => "Purchase Order #{$this->poNumber} status changed to {$this->status}.",
        ];
    }
}
