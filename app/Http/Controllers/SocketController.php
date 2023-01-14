<?php

declare(strict_types=1);
namespace App\Http\Controllers;

use Illuminate\Broadcasting\Broadcasters\PusherBroadcaster;
use Pusher\Pusher;
use Illuminate\Http\Request;

class SocketController {
    public function connect(Request $request){
        $push = new Pusher(env('PUSHER_APP_KEY'), env('PUSHER_APP_SECRET'), env('PUSHER_APP_ID'), []);
        $broadcaster = new PusherBroadcaster($push);
        return $broadcaster->validAuthenticationResponse($request, []);
    }
}