<?php

namespace App\Http\Controllers\APIV1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseApiController
{
    public function registration(RegisterRequest $request)
    {
        try {

            $validated = $request->validated();
            $user = User::create($validated);
            return $this->success(new UserResource($user), 'User successfully created', 201);
        } catch (\Throwable $error) {
            return $this->error('Internal server error', 500, $error);
        }
    }

    public function login(LoginRequest $request)
    {
        try {

            $user = User::where('email', $request->email)->first();
            if (!$user) return $this->error('User not found', 404);
            if (!Hash::check($request->password, $user->password)) {
                return $this->error('Password do not match', 404);
            }
            $token =   $user->createToken('auth_token')->plainTextToken;
            return $this->success(
                [
                    'user' => new UserResource($user),
                    'token' => $token,
                ],
                'User logged in successfully',
                200
            );
        } catch (\Throwable $err) {
            return $this->error('Internal server error', 500, $err);
        }
    }
    public function me(Request $request)
    {
        $user  = Auth::user();
        $key = "user_profile_{$user->id}";
        $cached = Cache::remember($key, now()->addHour(), function () use ($user) {
            return new UserResource($user);
        });
        return $this->success($cached, 'User profile retrieved successfully');
    }

    public function logout(Request $request)
    {
        try {

            $user = $request->user();
            $key = "user_profile_{$user->id}";
            Cache::forget($key);
            $user->currentAccessToken()->delete();
            return $this->success(null, 'User logged out successfully');
        } catch (\Throwable $err) {
            return $this->error('Something went wrong', 500, $err);
        }
    }
}
