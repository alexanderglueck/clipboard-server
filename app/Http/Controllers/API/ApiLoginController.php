<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ApiLoginController extends Controller
{
    public function login(Request $request): JsonResponse|User
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        if (User::where('email', $request->get('email'))->exists()) {
            $user = User::where('email', $request->get('email'))->first();
            $auth = Hash::check($request->get('password'), $user->password);
            if ($user && $auth) {
                return $user;
            }
        }

        return response()->json([
            'message' => 'Unauthorized, check your credentials.',
        ], 401);
    }
}
