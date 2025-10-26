<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\ResendVerificationRequest;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    /**
     * Send verification email to user
     */
    public function sendVerificationEmail(ResendVerificationRequest $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->hasVerifiedEmail()) {
                return $this->errorResponse('Email address is already verified.', null, 400);
            }

            $user->sendEmailVerificationNotification();

            return $this->successResponse(
                null,
                'Verification link has been sent to your email address.'
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send verification email', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id,
            ]);

            return $this->errorResponse(
                'Failed to send verification email. Please try again.',
                $e->getMessage(),
                500
            );
        }
    }

    /**
     * Verify email address using signed URL
     */
    public function verify(Request $request): JsonResponse
    {
        try {
            $user = \App\Models\User::findOrFail($request->route('id'));

            // Check if signature is valid
            if (!$request->hasValidSignature()) {
                return $this->errorResponse('Invalid or expired verification link.', null, 400);
            }

            // Check if email is already verified
            if ($user->hasVerifiedEmail()) {
                return $this->successResponse(
                    ['already_verified' => true],
                    'Email address is already verified.'
                );
            }

            // Mark email as verified
            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }

            return $this->successResponse(
                ['verified' => true],
                'Email address has been verified successfully.'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('User not found.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Email verification failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->route('id'),
            ]);

            return $this->errorResponse(
                'Failed to verify email address. Please try again.',
                $e->getMessage(),
                500
            );
        }
    }

    /**
     * Check verification status
     */
    public function checkVerificationStatus(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return $this->successResponse([
                'is_verified' => $user->hasVerifiedEmail(),
                'email' => $user->email,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to check verification status.',
                $e->getMessage(),
                500
            );
        }
    }
}

