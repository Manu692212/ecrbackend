<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\Admin;
use App\Models\OtpToken;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function index()
    {
        $admins = Admin::select('id', 'name', 'email', 'role', 'is_active', 'created_at')
            ->paginate(10);
        return response()->json($admins);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:8',
            'role' => 'string|in:admin,super_admin',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'admin',
        ]);

        return response()->json([
            'message' => 'Admin created successfully',
            'admin' => $admin->only(['id', 'name', 'email', 'role', 'is_active'])
        ], 201);
    }

    public function show(string $id)
    {
        $admin = Admin::findOrFail($id);
        return response()->json($admin->only(['id', 'name', 'email', 'role', 'is_active', 'created_at']));
    }

    public function update(Request $request, string $id)
    {
        $admin = Admin::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:admins,email,' . $id,
            'password' => 'sometimes|string|min:8',
            'role' => 'sometimes|string|in:admin,super_admin',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $updateData = $request->only(['name', 'email', 'role', 'is_active']);
        if ($request->has('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $admin->update($updateData);

        return response()->json([
            'message' => 'Admin updated successfully',
            'admin' => $admin->only(['id', 'name', 'email', 'role', 'is_active'])
        ]);
    }

    public function destroy(string $id)
    {
        $admin = Admin::findOrFail($id);
        $admin->delete();

        return response()->json(['message' => 'Admin deleted successfully']);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (!$admin->is_active) {
            return response()->json(['message' => 'Account is deactivated'], 401);
        }

        // Create a token for the admin
        $token = $admin->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'admin' => $admin->only(['id', 'name', 'email', 'role'])
        ]);
    }

    public function verifyLoginOtp(Request $request, OtpService $otpService)
    {
        $validator = Validator::make($request->all(), [
            'otp_token' => 'required|string',
            'code' => 'required|string|min:4|max:10',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        /** @var OtpToken|null $token */
        $token = OtpToken::where('id', $request->otp_token)
            ->where('context', 'login')
            ->first();

        if (!$token) {
            return response()->json(['message' => 'OTP expired or invalid'], 410);
        }

        if (!$otpService->verify($token, $request->code)) {
            return response()->json(['message' => 'Invalid or expired OTP'], 422);
        }

        $admin = $token->admin ?? Admin::where('email', $token->email)->first();

        if (!$admin) {
            return response()->json(['message' => 'Account not found'], 404);
        }

        if (!$admin->is_active) {
            return response()->json(['message' => 'Account is deactivated'], 401);
        }

        $sanctumToken = $admin->createToken('admin-token')->plainTextToken;
        $token->delete();

        return response()->json([
            'message' => 'Login successful',
            'admin' => $admin->only(['id', 'name', 'email', 'role']),
            'token' => $sanctumToken,
        ]);
    }

    public function requestPasswordReset(Request $request, OtpService $otpService)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin) {
            return response()->json([
                'message' => 'If the email exists, an OTP has been sent',
            ]);
        }

        $issued = $otpService->issue(
            $admin->email,
            'password_reset',
            $admin,
            OtpService::DEFAULT_TTL_SECONDS,
            ['intent' => 'forgot_password', 'ip' => $request->ip()]
        );

        try {
            Mail::to($admin->email)
                ->send(new OtpMail(
                    $issued['code'],
                    'Password Reset',
                    OtpService::DEFAULT_TTL_SECONDS,
                    $issued['token']->metadata
                ));
        } catch (\Throwable $e) {
            Log::error('Failed to send password reset OTP email', [
                'admin_id' => $admin->id,
                'email' => $admin->email,
                'otp_token' => $issued['token']->id,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            $issued['token']->delete();

            return response()->json([
                'message' => 'Unable to send OTP email at this time',
            ], 503);
        }

        return response()->json([
            'message' => 'OTP sent to registered email',
            'otp_token' => $issued['token']->id,
            'expires_in' => OtpService::DEFAULT_TTL_SECONDS,
        ]);
    }

    public function resetPassword(Request $request, OtpService $otpService)
    {
        $validator = Validator::make($request->all(), [
            'otp_token' => 'required|string',
            'code' => 'required|string|min:4|max:10',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        /** @var OtpToken|null $token */
        $token = OtpToken::where('id', $request->otp_token)
            ->where('context', 'password_reset')
            ->first();

        if (!$token || $token->email !== $request->email) {
            return response()->json(['message' => 'OTP expired or invalid'], 410);
        }

        if (!$otpService->verify($token, $request->code)) {
            return response()->json(['message' => 'Invalid or expired OTP'], 422);
        }

        $admin = $token->admin ?? Admin::where('email', $token->email)->first();

        if (!$admin) {
            return response()->json(['message' => 'Account not found'], 404);
        }

        $admin->update([
            'password' => Hash::make($request->password),
        ]);

        // Revoke all existing tokens for security
        $admin->tokens()->delete();
        $newToken = $admin->createToken('admin-token')->plainTextToken;
        $token->delete();

        return response()->json([
            'message' => 'Password reset successful',
            'token' => $newToken,
            'admin' => $admin->only(['id', 'name', 'email', 'role']),
        ]);
    }

    public function requestPasswordChangeOtp(Request $request, OtpService $otpService)
    {
        $admin = $request->user();

        if (!$admin) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $issued = $otpService->issue(
            $admin->email,
            'password_change',
            $admin,
            OtpService::DEFAULT_TTL_SECONDS,
            ['intent' => 'password_change', 'ip' => $request->ip()]
        );

        try {
            Mail::to($admin->email)
                ->send(new OtpMail(
                    $issued['code'],
                    'Password Change',
                    OtpService::DEFAULT_TTL_SECONDS,
                    $issued['token']->metadata
                ));
        } catch (\Throwable $e) {
            Log::error('Failed to send password change OTP email', [
                'admin_id' => $admin->id,
                'email' => $admin->email,
                'otp_token' => $issued['token']->id,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            $issued['token']->delete();

            return response()->json([
                'message' => 'Unable to send OTP email at this time',
            ], 503);
        }

        return response()->json([
            'message' => 'OTP sent to registered email',
            'otp_token' => $issued['token']->id,
            'expires_in' => OtpService::DEFAULT_TTL_SECONDS,
        ]);
    }

    public function changePasswordWithOtp(Request $request, OtpService $otpService)
    {
        $validator = Validator::make($request->all(), [
            'otp_token' => 'required|string',
            'code' => 'required|string|min:4|max:10',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $admin = $request->user();

        if (!$admin) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        /** @var OtpToken|null $token */
        $token = OtpToken::where('id', $request->otp_token)
            ->where('context', 'password_change')
            ->first();

        if (
            !$token ||
            $token->admin_id !== $admin->id
        ) {
            return response()->json(['message' => 'OTP expired or invalid'], 410);
        }

        if (!$otpService->verify($token, $request->code)) {
            return response()->json(['message' => 'Invalid or expired OTP'], 422);
        }

        $admin->update([
            'password' => Hash::make($request->password),
        ]);

        $admin->tokens()->delete();
        $newToken = $admin->createToken('admin-token')->plainTextToken;
        $token->delete();

        return response()->json([
            'message' => 'Password updated successfully',
            'token' => $newToken,
            'admin' => $admin->only(['id', 'name', 'email', 'role']),
        ]);
    }
}
