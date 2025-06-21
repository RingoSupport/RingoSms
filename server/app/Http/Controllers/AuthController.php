<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    //
    /**
     * Register a new user and send OTP to email.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'       => 'nullable|string',
            'email'      => 'required|string|email|unique:users',
            'password'   => 'required|string|min:6',
            'phone'      => 'nullable|string',
            'organization_id' => 'nullable|string',
            'role'       => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Generate OTP
        $otp = rand(100000, 999999);

        // Create user
        $user = User::create([
            'name'             => $request->name,
            'email'            => $request->email,
            'password'         => bcrypt($request->password),
            'phone'            => $request->phone,
            'organization_id'       => $request->organization_id,
            'role'             => strtolower($request->role),
            'otp'              => $otp,
            'otp_expires_at'   => now()->addMinutes(10),
        ]);

        // Send OTP email
        try {
            Mail::to($user->email)->send(new OtpMail($otp));
        } catch (\Exception $e) {
            // Log error but don't fail registration
            Log::error('Failed to send OTP email: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'User registered but failed to send OTP email. Please contact support.',
                'user_id' => $user->id
            ], 201);
        }

        return response()->json([
            'message' => 'User registered successfully. Please check your email for the OTP.'
        ], 201);
    }

      /**
     * Login user using email/password and return JWT token.
     * Only allows login if email has been verified.
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $validator = Validator::make($credentials, [
            'email'    => 'required|string|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
              return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = auth()->user();

        // Check if email is verified
        if (!$user->email_verified_at) {
            // Invalidate the token since user shouldn't be logged in
            JWTAuth::invalidate($token);
            
            return response()->json([
                'message' => 'Please verify your email before logging in.',
                'requires_verification' => true
            ], 403);
        }

        // Log login manually using AuditLog model
         \App\Models\AuditLog::create([
        'auditable_type' => get_class($user),
        'auditable_id'   => $user->id,
        'user_id'        => $user->id,
        'action'         => 'login',
        'ip_address'     => request()->ip(),
        'user_agent'     => request()->userAgent(),
        'new_values'     => ['login_at' => now()],
    ]);



        return $this->respondWithToken($token);
    }

       /**
     * Verify OTP sent to email and activate account.
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp'   => 'required|string',
        ]);

           if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)
                    ->where('otp', $request->otp)
                    ->first();
            if (!$user) {
            return response()->json([
                'message' => 'Invalid OTP or email'
            ], 401);
        }

        // Check if OTP has expired
        if (Carbon::now()->gt($user->otp_expires_at)) {
            return response()->json([
                'message' => 'OTP has expired. Please request a new one.',
                'expired' => true
            ], 400);
        }

       $user->update([
            'otp' => null,
            'otp_expires_at' => null,
            'email_verified_at' => now()
        ]);

         return response()->json([
            'message' => 'Email verified successfully. You can now log in.',
            'verified' => true
        ]);
    }


    /**
     * Resend OTP to user email.
     */
    public function resendOtp(Request $request)
    {
         $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
         $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

          if ($user->email_verified_at) {
            return response()->json([
                'message' => 'Email already verified'
            ], 200);
        }

           // Rate limiting: prevent spam
        if ($user->otp_expires_at && Carbon::now()->lt($user->otp_expires_at->subMinutes(8))) {
            return response()->json([
                'message' => 'Please wait before requesting another OTP',
                'retry_after' => $user->otp_expires_at->subMinutes(8)->diffInSeconds(Carbon::now())
            ], 429);
        }

       
        $otp = rand(100000, 999999);

        $user->update([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10)
        ]);

       
        try {
            Mail::to($user->email)->send(new OtpMail($otp));
        } catch (\Exception $e) {
            Log::error('Failed to resend OTP email: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to send OTP email. Please try again later.'
            ], 500);
        }

        return response()->json(['message' => 'OTP resent successfully.']);
    }



    /**
     * Log out the authenticated user (invalidate token).
     */
    public function logout()
    {
         try {
            JWTAuth::invalidate(JWTAuth::getToken());
            
            return response()->json([
                'message' => 'Successfully logged out'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to logout, please try again'
            ], 500);
        }
    }


       /**
     * Refresh JWT token.
     */
    public function refresh()
    {
        try {
            $token = JWTAuth::refresh();
            
            return $this->respondWithToken($token);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Could not refresh token'
            ], 401);
        }
    }

    /**
     * Get the authenticated user.
     */
    public function me()
    {
        return response()->json(auth()->user());
    }


     protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => JWTAuth::factory()->getTTL() * 60,
            'user'         => auth()->user()
        ]);
    }

}
