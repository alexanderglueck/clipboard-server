<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ApiDeviceController extends Controller
{
    public function index(Request $request): Collection
    {
        return $request->user()->devices;
    }
}
