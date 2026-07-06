<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    public function sendToUser(User $user, string $title, string $body, array $data = []): void
    {
        if (! $user->fcm_token) {
            return;
        }

        $serverKey = config('services.fcm.server_key');

        if (! $serverKey) {
            Log::info('FCM skipped (no server key)', [
                'user_id' => $user->id,
                'title' => $title,
            ]);

            return;
        }

        Http::withHeaders([
            'Authorization' => 'key='.$serverKey,
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', [
            'to' => $user->fcm_token,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $data,
        ]);
    }
}
