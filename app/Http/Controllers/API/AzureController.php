<?php

namespace App\Http\Controllers\API;

use App\Azure\Notification;
use App\Azure\NotificationHub;
use App\Http\Controllers\Controller;
use App\Models\Device;
use Exception;
use Illuminate\Http\Request;
use function response;

class AzureController extends Controller
{
    public function store(Request $request)
    {
        $hub = new NotificationHub(config('clipboard.azure.connection_path'), config('clipboard.azure.hub_name'));

        $device = Device::find($request->input('device_id'));

        try {
            # https://docs.microsoft.com/en-us/previous-versions/windows/apps/hh465435(v=win.10)

            # Toast
            # $notification = new Notification("windows", $toast);
            # $notification->headers[] = 'X-WNS-Type: wns/toast';

            # Raw
            $notification = new Notification("windows", $request->input('content'));
            $notification->headers[] = 'X-WNS-Type: wns/raw';

        } catch (Exception $exception) {
            return response($exception->getMessage(), 500);
        }

        try {
            $hub->sendNotification($notification, $device->device_token);
        } catch (Exception $exception) {
            return response($exception->getMessage(), 500);
        }

        return response('', 201);
    }

}
