<?php

namespace App\Http\Controllers\API;

use App\Azure\Notification;
use App\Azure\NotificationHub;
use App\Http\Controllers\Controller;
use App\Http\Requests\PostStoreRequest;
use App\Models\Device;
use App\Models\DeviceType;
use App\Post;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use LaravelFCM\Facades\FCM;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;

class ApiPostController extends Controller
{
    public function store(PostStoreRequest $request): Response
    {
        $post = new Post($request->validated());
        $post->device_id = $request->input('device_id');

        $request->user()->posts()->save($post);

        Log::info("device Id " . $post->device_id);

        foreach ($request->user()->devices as $device) {
            Log::info("attempting " . $device->id);
            if ($post->device_id == $device->id) {
                Log::info("continueing" . $post->device_id . " - " . $device->id);
                continue;
            }

            if ($device->device_type_id == DeviceType::Android->value) {
                $this->sendToDevice($device, $post);
                Log::info("sent to android");
            } else if ($device->device_type_id == DeviceType::Windows->value) {
                $this->sendToWindows($device, $post);
                Log::info("sent to windows");
            } else {
                Log::info("no device info type");
            }
        }

        return response("", 201);
    }

    private function sendToDevice(Device $device, Post $post): void
    {
        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60 * 20);

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData([
            'content' => $post->content,
            'user_id' => $post->user_id
        ]);

        $option = $optionBuilder->build();
        $data = $dataBuilder->build();

        $downstreamResponse = FCM::sendTo($device->device_token, $option, null, $data);

        Log::info('Push response', [
            'numSuccess' => $downstreamResponse->numberSuccess(),
            'numFailure' => $downstreamResponse->numberFailure(),
            'numModification' => $downstreamResponse->numberModification(),
            'delete' => $downstreamResponse->tokensToDelete(),
            'modify' => $downstreamResponse->tokensToModify(),
            'retry' => $downstreamResponse->tokensToRetry(),
            'error' => $downstreamResponse->tokensWithError()
        ]);
    }

    public function sendToWindows(Device $device, Post $url): bool
    {
        $hub = new NotificationHub(config('clipboard.azure.connection_path'), config('clipboard.azure.hub_name'));

        try {
            # https://docs.microsoft.com/en-us/previous-versions/windows/apps/hh465435(v=win.10)

            # Raw
            $notification = new Notification("windows", $url->content);
            $notification->headers[] = 'X-WNS-Type: wns/raw';

            //  $notification->headers[] = 'ServiceBusNotification-DeviceHandle: ' . ;
        } catch (Exception $exception) {
            Log::info('Invalid format for Windows push notification: ' . $exception->getMessage());
            return false;
        }

        try {
            $hub->sendNotification($notification, $device->device_token);
        } catch (Exception $exception) {
            Log::info('Could not send Windows push notification: ' . $exception->getMessage());

            return false;
        }

        return true;
    }

}
