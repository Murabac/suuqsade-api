<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SendOtpRequest;
use App\Http\Requests\Api\VerifyOtpRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function sendOtp(SendOtpRequest $request, OtpService $otpService): JsonResponse
    {
        $otpService->send($request->validated('phone_number'));

        return response()->json([
            'message' => 'OTP sent successfully.',
        ]);
    }

    public function verifyOtp(VerifyOtpRequest $request, OtpService $otpService): JsonResponse
    {
        if (! $otpService->verify($request->validated('code'))) {
            return response()->json([
                'message' => 'Invalid verification code.',
            ], 422);
        }

        $phoneNumber = preg_replace('/\s+/', '', $request->validated('phone_number'));

        $user = User::query()->firstOrCreate(
            ['phone_number' => $phoneNumber],
            [
                'name' => $request->validated('name') ?? $phoneNumber,
                'language' => 'en',
            ],
        );

        if ($request->filled('name') && ! $user->wasRecentlyCreated) {
            $user->update(['name' => $request->validated('name')]);
        }

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user->fresh()),
        ]);
    }

    public function logout(): JsonResponse
    {
        request()->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}
