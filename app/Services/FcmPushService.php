<?php

namespace App\Services;

use App\Models\Device;
use App\Models\Post;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Laravel\Firebase\Facades\Firebase;

class FcmPushService
{
    public function sendPostNotification(Device $device, Post $post): bool
    {
        // FCM v1 requires all data payload values to be strings
        $message = CloudMessage::new()
            ->toToken($device->device_token)
            ->withData([
                'content' => (string) $post->id,
                'user_id' => (string) $post->user_id,
            ])
            ->withAndroidConfig(AndroidConfig::fromArray([
                'ttl' => (60 * 20) . 's',
            ]));

        try {
            Firebase::messaging()->send($message);
        } catch (NotFound $exception) {
            Log::info('FCM token is no longer registered', [
                'device_id' => $device->id,
                'error' => $exception->getMessage(),
            ]);

            return false;
        } catch (FirebaseException $exception) {
            Log::info('Could not send FCM push notification: ' . $exception->getMessage());

            return false;
        }

        Log::info('Push sent', ['device_id' => $device->id]);

        return true;
    }
}
