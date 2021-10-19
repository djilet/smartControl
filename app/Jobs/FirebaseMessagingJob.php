<?php

namespace App\Jobs;

use App\Models\PushNotification;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FirebaseMessagingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $userId;
    private $title;
    private $description;
    private $data;

    private $serverKey;

    /**
     * Create a new job instance.
     *
     * @param $userId
     * @param $title
     * @param string $description
     * @param array $data
     */
    public function __construct($userId, $title, $description = '', array $data = [])
    {
        $this->userId = $userId;
        $this->title = $title;
        $this->description = $description;
        $this->data = $data;
        $this->serverKey = config('firebase.server_key');
    }

    public static function dispatchByNotification(PushNotification $push)
    {
        return self::dispatch(
            $push->user_id,
            $push->title,
            $push->description,
            [
                'id' => $push->id
            ]
        );
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $tokens = User::findOrFail($this->userId)->firebaseTokens->pluck('token')->toArray();
        if (empty($tokens)) {
            return;
        }

        $body = [
            'registration_ids' => $tokens,
            'notification' => [
                'title' => $this->title,
                'body' => $this->description,
            ]
        ];

        if (empty($this->data) === false) {
            $body['data'] = $this->data;
        }

        $client = new Client();
        $request = new Request(
            'POST',
            'https://fcm.googleapis.com/fcm/send',
            [
                'Content-Type' => 'application/json',
                'Authorization' => 'key='.$this->serverKey,
            ],
            json_encode($body)
        );
        $client->send($request);

    }
}
