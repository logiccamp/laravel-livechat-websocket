<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $name;
    public $message;
    public $time;
    public $chat_id;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($name, $message, $time, $chat_id)
    {
        $this->name= $name;
        $this->message = $message;
        $this->time = $time;
        $this->chat_id = $chat_id;
    }

    public function broadcastWith(){
        return [
            "name" => $this->name,
            "message" => $this->message,
            "time" => $this->time,
            "chat_id" => $this->chat_id
        ];
    }
    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel("SendMessageEvent");
    }
}
