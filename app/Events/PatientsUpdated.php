<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
// If you want immediate broadcasting (no queue), implement ShouldBroadcastNow instead
// use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PatientsUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $version;

    /**
     * Create a new event instance.
     */
    public function __construct(int $version = 0)
    {
        $this->version = $version ?: time();
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs()
    {
        return 'PatientsUpdated';
    }

    /**
     * Get the channels the event should broadcast on.
     * We'll use a simple public channel named `patients` so clients can subscribe.
     */
    public function broadcastOn()
    {
        return new Channel('patients');
    }

    /**
     * Data to broadcast with the event.
     */
    public function broadcastWith()
    {
        return ['version' => $this->version];
    }
}
