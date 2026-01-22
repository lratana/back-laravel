<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\UploadMethod;
use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;


class AuthController extends Controller
{
    use UploadMethod;
    function signup(Request $request)
    {

        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|confirmed',
            'password_confirmation' => 'required|string|same:password',
        ]);
        try {
            DB::beginTransaction();
            // Your DB operations here
            $user = User::create($fields);
            $user->sendEmailVerificationNotification();
            $token = $user->createToken('AUTH-TOKEN');
            DB::commit();
        } catch (Exception  $e) {
            DB::rollBack();
            throw $e;
        }

        return response([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token->plainTextToken,
        ], 201);
    }
    function signin(Request $request)
    {
        $request->ip();
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);
        $user = User::where('email', $request->email)->first();

        if (empty($user)) {
            throw ValidationException::withMessages([
                'email' => ['Email does not exist.'],
            ]);
        }
        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages(
                [
                    'password' => ['Password does not match.'],
                ]
            );
        }
        $token  = $user->createToken('AUTH-TOKEN');
        $response = [
            'message' => 'User signed in successfully',
            'user' => $user,
            'token' => $token->plainTextToken,
        ];
        return response($response, 200);
    }
    function signout(Request $request)
    {
        $user = $request->user();
        if ($user && ! $user->currentAccessToken()) {
            return response([
                'message' => 'No authenticated user found.'
            ], 401);
        }
        //method1: Revoke all tokens
        $user->currentAccessToken()->delete();
        return response([
            'message' => 'User signed out successfully'
        ], 200);
        //mehtod2: Revoke specific token
        // $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();


    }

    /**
     * Verify user email address.
     */
    function verifyEmail(Request $request)
    {
        $userID = $request->route('id');
        $user = User::findOrFail($userID);

        if (empty($user)) {
            return response([
                'message' => 'User not found.'
            ], 422);
        }

        if ($user->hasVerifiedEmail()) {
            return response([
                'message' => 'Email already verified.'
            ], 200);
        }

        $user->markEmailAsVerified();

        return response([
            'message' => 'Email verified successfully.'
        ], 200);
    }
    /**
     * Resend email verification notification.
     */
    function resendVerificationMail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (empty($user)) {
            throw ValidationException::withMessages([
                'email' => 'Email does not exist.',
            ]);
        }

        if ($user->hasVerifiedEmail()) {
            return response([
                'message' => 'Email already verified.'
            ], 200);
        }

        $user->sendEmailVerificationNotification();

        return response([
            'message' => 'Verification email resent.'
        ], 200);
    }
    /**
     * Send password reset email.
     */
    function sendResetPasswordMail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (empty($user)) {
            throw ValidationException::withMessages([
                'email' => 'Email does not exist.',
            ]);
        }
        // Use Laravel's built-in password reset system
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response([
                'message' => 'Password reset link sent to your email'
            ], 200);
        }

        return response([
            'message' => 'Password reset link sent to your email'
        ], 200);
    }


    /**
     * Reset user password using Laravel's built-in system.
     */
    function setNewPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:10|confirmed'
        ]);

        // Method 1: Using Password facade (recommended)
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
            }
        );

        // Check status and respond accordingly
        if ($status === Password::PASSWORD_RESET) {
            return response([
                'message' => 'Password has been reset successfully.'
            ], 200);
        }

        /* 
        // Method 2: Manual implementation (for educational purposes)
        // 1. Find the user by email
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response([
                'message' => 'User not found.'
            ], 404);
        }

        // 2. Check if the token is valid and not expired
        $record = DB::table('password_resets')->where('email', $request->email)->first();
        if (!$record) {
            return response([
                'message' => 'Invalid or expired token.'
            ], 400);
        }

        // Check token match (Laravel hashes the token in the DB)
        if (!Hash::check($request->token, $record->token)) {
            return response([
                'message' => 'Invalid or expired token.'
            ], 400);
        }

        // Check expiration (default: 60 minutes)
        $expires = 60 * 60; // 60 minutes in seconds
        if (strtotime($record->created_at) + $expires < time()) {
            return response([
                'message' => 'Token has expired.'
            ], 400);
        }

        // 3. Hash and update the password
        $user->forceFill([
            'password' => Hash::make($request->password)
        ])->setRememberToken(Str::random(60));
        $user->save();

        // 4. Invalidate the token
        DB::table('password_resets')->where('email', $request->email)->delete();

        return response([
            'message' => 'Password has been reset successfully.'
        ], 200);
        */

        return response([
            'message' => 'Failed to reset password.'
        ], 500);
    }

    function refreshToken(Request $request)
    {
        // Get authenticated user
        $user = $request->user();
        if (!$user || !$user->currentAccessToken()) {
            return response([
                'message' => 'No authenticated user found.'
            ], 401);
        }

        // Revoke the current token
        $user->currentAccessToken()->delete();

        // Create a new token
        $newToken = $user->createToken('AUTH-TOKEN');

        return response([
            'message' => 'Token refreshed successfully',
            'token' => $newToken->plainTextToken,
        ], 200);
    }
    // Verify account validity
    function verifyAccount(Request $request)
    {
        // Get authenticated user
        $user = $request->user();

        return response([
            'message' => 'Account is valid.',
            'user' => $user
        ], 200);
    }
    // Create password for users without password (e.g., social login users)
    function createPassword(Request $request)
    {
        // Validate input
        $request->validate([
            'new_password' => 'required|string|min:6|max:10|confirmed',
            'terminate_sessions' => 'required|boolean'
        ]);
        // Get authenticated user
        $user = $request->user();

        try {
            // Start transaction
            DB::beginTransaction();
            $user->password = Hash::make($request->new_password);
            $user->save();

            if ($request->terminate_sessions) {
                // delete all tokens
                $user->tokens()->delete();
            } else {
                // delete current token only
                $currentToken = $user->currentAccessToken();
                $user->tokens()->where('id', $currentToken->id)->delete();
            }
            // Commit transaction
            DB::commit();
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
        // Return success response
        return response([
            'message' => 'Password created successfully.'
        ], 200);
    }
    // Change existing password
    function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:6|max:10|confirmed',
            'terminate_sessions' => 'required|boolean'
        ]);

        $user = $request->user();

        if (!Hash::check($request->old_password, $user->password)) {
            throw ValidationException::withMessages([
                'old_password' => 'Old password does not match.',
            ]);
        }

        try {
            DB::beginTransaction();
            $user->password = Hash::make($request->new_password);
            $user->save();

            if ($request->terminate_sessions) {
                // Delete all tokens
                $user->tokens()->delete();
            } else {
                // Delete current token only
                $currentToken = $user->currentAccessToken();
                $user->tokens()->where('id', $currentToken->id)->delete();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }

        return response([
            'message' => 'Password changed successfully.'
        ], 200);
    }

    // Update user photo
    function updateUserPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'nullable|base64image|base64mimes:png,jpg,jpeg|base64dimensions:width=454,height=454'
        ]);

        $user = $request->user();

        try {
            DB::beginTransaction();
            UploadMethod::discardImage($user->getRawOriginal('photo'), 'profile');
            $user->photo = null;
            if (!empty($request->photo)) {
                $user->photo = UploadMethod::storeImage($request->photo, 'profile');
            }
            $user->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }

        return response([
            'message' => 'User photo updated successfully.',
            'photo' => $user->photo
        ], 200);
    }
}
