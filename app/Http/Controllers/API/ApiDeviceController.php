<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DeviceType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiDeviceController extends Controller
{
    public function index(Request $request): Collection
    {
        return $request->user()->devices;
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required',
            'token' => 'required',
            'device_type' => 'required'
        ]);

        Log::info('input', $request->all());

        $device = $request->user()->devices()->create([
            'name' => $request->input('name'),
            'device_token' => $request->input('token'),
            'device_type_id' => DeviceType::Windows
        ]);

        return response()->json([
            "success" => true,
            "id" => $device->id
        ]);
    }


}
