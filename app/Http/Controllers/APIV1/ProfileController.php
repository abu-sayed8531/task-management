<?php

namespace App\Http\Controllers\APIV1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\ProfileUpdateRequest;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProfileController extends BaseApiController
{
    public function update(ProfileUpdateRequest $request)
    {
        try {
            $user = $request->user();
            $validated = $request->validated();
            $key = "user_profile_{$user->id}";
            $user->update($validated);
            Cache::put($key, new UserResource($user), now()->addHour());
            return $this->success(new UserResource($user), 'Profile updated successfully', 200);
        } catch (\Throwable $err) {
            return $this->error('There is something wrong', 500, $err);
        }
    }
}
