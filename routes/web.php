<?php

use App\Events\SendMessage;
use BeyondCode\LaravelWebSockets\Apps\AppProvider;
use BeyondCode\LaravelWebSockets\Dashboard\DashboardLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', function () {
    dd("Here");
    return view('welcome');
});
Route::get("/chat/{id}", function(AppProvider $appProvider, $chat){
    $data = [
        "host" => env('LARAVEL_WEBSOCKETS_HOST'),
        "port" => env('LARAVEL_WEBSOCKETS_PORT'),
        "authEndPoint" => "/api/sockets/connect/".$chat,
        "logChannel" => DashboardLogger::LOG_CHANNEL_PREFIX,
        "apps" => $appProvider->all(),
        "chat_id" => $chat,
    ];
    return view("chatapp", $data);
});

Route::post("/chat/send/{chat_id}", function(Request $request, $chat_id){
    $message = $request->input('msg', null);
    $name = $request->input('name', 'anonymous');
    $time = (new DateTime(now()))->format(DateTime::ATOM);

    if($name == null){
        $name = 'anonymous';
    }
    SendMessage::dispatch($name, $message, $time, $chat_id);
});

Route::get("/b", function(){dd('b');});