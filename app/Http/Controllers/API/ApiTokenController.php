<?php

namespace App\Http\Controllers\API;

use App\Models\Device;
use App\Http\Controllers\Controller;
use App\Models\DeviceType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiTokenController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required',
            'token' => 'required'
        ]);

        Log::info('input', $request->all());

        Device::where('user_id', $request->user()->id)
            ->where('id', $request->input('id'))
            ->update([
                'device_token' => $request->input('token')
            ]);

        return response()->json([
            "success" => true
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required'
        ]);

        Log::info('input', $request->all());

        Device::where('device_token', $request->input('token'))
            ->update([
                'device_token' => null
            ]);

        return response()->json([
            "success" => true
        ]);
    }
}
