<?php

namespace App\Http\Controllers\API;

use App\Azure\Notification;
use App\Azure\NotificationHub;
use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Post;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use LaravelFCM\Facades\FCM;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use function response;

class AndroidController extends Controller
{
    public function store(Request $request)
    {
        $device = Device::find($request->input('device_id'));
        $post = Post::find($request->input('content'));

        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60 * 20);

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData([
            'content' => $post->id,
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
        return response('', 201);
    }
}
