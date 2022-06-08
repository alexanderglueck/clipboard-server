<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostStoreRequest;
use App\Post;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use LaravelFCM\Facades\FCM;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;

class ApiPostController extends Controller
{
    public function store(PostStoreRequest $request): Response
    {
        $url = new Post($request->validated());
        $url->device_id = $request->input('device_id');

        $request->user()->posts()->save($url);

        $this->sendToDevice($url);

        return response("", 201);
    }

    private function sendToDevice(Post $url): void
    {
        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60 * 20);

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData([
            'content' => $url->content,
            'user_id' => $url->user_id
        ]);

        $option = $optionBuilder->build();
        $data = $dataBuilder->build();

        $downstreamResponse = FCM::sendTo($url->device->device_token, $option, null, $data);

        Log::debug('Push response', [
            'numSuccess' => $downstreamResponse->numberSuccess(),
            'numFailure' => $downstreamResponse->numberFailure(),
            'numModification' => $downstreamResponse->numberModification(),
            'delete' => $downstreamResponse->tokensToDelete(),
            'modify' => $downstreamResponse->tokensToModify(),
            'retry' => $downstreamResponse->tokensToRetry(),
            'error' => $downstreamResponse->tokensWithError()
        ]);
    }
}
