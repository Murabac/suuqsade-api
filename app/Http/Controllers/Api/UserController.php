<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\FcmTokenRequest;
use App\Http\Requests\Api\UpdateUserRequest;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function show(): UserResource
    {
        return new UserResource(request()->user());
    }

    public function update(UpdateUserRequest $request): UserResource
    {
        $user = $request->user();
        $user->update($request->validated());

        return new UserResource($user->fresh());
    }

    public function storeFcmToken(FcmTokenRequest $request): JsonResponse
    {
        $request->user()->update([
            'fcm_token' => $request->validated('fcm_token'),
        ]);

        return response()->json([
            'message' => 'FCM token saved.',
        ]);
    }

    public function notifications(): AnonymousResourceCollection
    {
        $notifications = request()->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return NotificationResource::collection($notifications);
    }
}
