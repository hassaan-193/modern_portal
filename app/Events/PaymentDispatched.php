<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentDispatched implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $bookingId;
    public $amount;
    public $vendorName;
    public $paymentMethod;
    public $status;
    public $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct($bookingId, $amount, $vendorName, $paymentMethod = 'Bank Transfer', $status = 'Dispatched')
    {
        $this->bookingId = $bookingId;
        $this->amount = $amount;
        $this->vendorName = $vendorName;
        $this->paymentMethod = $paymentMethod;
        $this->status = $status;
        $this->timestamp = now()->toIso8601String();
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('payments'),
            new Channel('portal-notifications'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'payment.dispatched';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'booking_id' => $this->bookingId,
            'amount' => $this->amount,
            'vendor_name' => $this->vendorName,
            'payment_method' => $this->paymentMethod,
            'status' => $this->status,
            'timestamp' => $this->timestamp,
            'message' => "Payment of AED " . number_format($this->amount, 2) . " dispatched to {$this->vendorName}.",
        ];
    }
}
