<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Post;
use App\Services\FcmPushService;
use Illuminate\Http\Request;
use function response;

class AndroidController extends Controller
{
    public function store(Request $request, FcmPushService $fcm)
    {
        $device = Device::find($request->input('device_id'));
        $post = Post::find($request->input('content'));

        $fcm->sendPostNotification($device, $post);

        return response('', 201);
    }
}
