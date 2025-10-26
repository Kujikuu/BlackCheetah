<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Send password reset link to user's email
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $user = \App\Models\User::where('email', $request->email)->first();

            if (!$user) {
                // Return success even if user not found (security best practice)
                return $this->successResponse(
                    null,
                    'If that email address exists in our system, we have sent a password reset link to it.'
                );
            }

            // Delete any existing tokens for this user
            \Illuminate\Support\Facades\DB::table('password_reset_tokens')
                ->where('email', $user->email)
                ->delete();

            // Generate new token
            $token = Str::random(64);

            // Store token in database
            \Illuminate\Support\Facades\DB::table('password_reset_tokens')->insert([
                'email' => $user->email,
                'token' => Hash::make($token),
                'created_at' => now(),
            ]);

            // Send notification
            $user->notify(new ResetPasswordNotification($token));

            return $this->successResponse(
                null,
                'If that email address exists in our system, we have sent a password reset link to it.'
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Password reset failed', [
                'error' => $e->getMessage(),
                'email' => $request->email,
            ]);

            return $this->errorResponse(
                'Failed to process password reset request. Please try again.',
                $e->getMessage(),
                500
            );
        }
    }

    /**
     * Reset user password
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            // Get the token record
            $tokenRecord = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->first();

            if (!$tokenRecord) {
                return $this->validationErrorResponse([
                    'email' => ['Invalid or expired reset token.'],
                ]);
            }

            // Check if token has expired (60 minutes)
            $createdAt = \Carbon\Carbon::parse($tokenRecord->created_at);
            if ($createdAt->addMinutes(60)->isPast()) {
                // Delete expired token
                \Illuminate\Support\Facades\DB::table('password_reset_tokens')
                    ->where('email', $request->email)
                    ->delete();

                return $this->validationErrorResponse([
                    'token' => ['Password reset token has expired. Please request a new one.'],
                ]);
            }

            // Verify token
            if (!Hash::check($request->token, $tokenRecord->token)) {
                return $this->validationErrorResponse([
                    'token' => ['Invalid reset token.'],
                ]);
            }

            // Update user password
            $user = \App\Models\User::where('email', $request->email)->first();

            if (!$user) {
                return $this->notFoundResponse('User not found.');
            }

            $user->update([
                'password' => Hash::make($request->password),
            ]);

            // Delete the used token
            \Illuminate\Support\Facades\DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            // Revoke all existing tokens for security
            $user->tokens()->delete();

            return $this->successResponse(
                null,
                'Password has been reset successfully. Please login with your new password.'
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Password reset failed', [
                'error' => $e->getMessage(),
                'email' => $request->email,
            ]);

            return $this->errorResponse(
                'Failed to reset password. Please try again.',
                $e->getMessage(),
                500
            );
        }
    }

    /**
     * Validate reset token
     */
    public function validateResetToken(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
        ]);

        try {
            $tokenRecord = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->first();

            if (!$tokenRecord) {
                return $this->errorResponse('Invalid or expired reset token.', null, 400);
            }

            // Check if token has expired (60 minutes)
            $createdAt = \Carbon\Carbon::parse($tokenRecord->created_at);
            if ($createdAt->addMinutes(60)->isPast()) {
                \Illuminate\Support\Facades\DB::table('password_reset_tokens')
                    ->where('email', $request->email)
                    ->delete();

                return $this->errorResponse('Password reset token has expired.', null, 400);
            }

            // Verify token
            if (!Hash::check($request->token, $tokenRecord->token)) {
                return $this->errorResponse('Invalid reset token.', null, 400);
            }

            return $this->successResponse(['valid' => true], 'Token is valid.');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to validate token.', $e->getMessage(), 500);
        }
    }
}

