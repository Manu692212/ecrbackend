<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\Admin;
use App\Models\OtpToken;
use App\Services\OtpService;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;

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

        return $this->respondWithToken($admin);
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

        $token->delete();

        return $this->respondWithToken($admin);
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

        $token->delete();

        return $this->respondWithToken($admin, 'Password reset successful');
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

        $token->delete();

        return $this->respondWithToken($admin, 'Password updated successfully');
    }

    public function requestEmailChangeOtp(Request $request, OtpService $otpService)
    {
        $admin = $request->user();

        if (!$admin) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'new_email' => [
                'required',
                'email',
                Rule::unique('admins', 'email')->ignore($admin->id),
            ],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $newEmail = $request->new_email;

        if ($newEmail === $admin->email) {
            return response()->json(['message' => 'New email must be different from current email'], 422);
        }

        $issued = $otpService->issue(
            $newEmail,
            'email_change',
            $admin,
            OtpService::DEFAULT_TTL_SECONDS,
            ['intent' => 'email_change', 'ip' => $request->ip(), 'new_email' => $newEmail, 'old_email' => $admin->email]
        );

        try {
            Mail::to($newEmail)
                ->send(new OtpMail(
                    $issued['code'],
                    'Email Change',
                    OtpService::DEFAULT_TTL_SECONDS,
                    $issued['token']->metadata
                ));
        } catch (\Throwable $e) {
            Log::error('Failed to send email change OTP email', [
                'admin_id' => $admin->id,
                'email' => $newEmail,
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
            'message' => 'OTP sent to new email',
            'otp_token' => $issued['token']->id,
            'expires_in' => OtpService::DEFAULT_TTL_SECONDS,
        ]);
    }

    public function changeEmailWithOtp(Request $request, OtpService $otpService)
    {
        $admin = $request->user();

        if (!$admin) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'otp_token' => 'required|string',
            'code' => 'required|string|min:4|max:10',
            'new_email' => [
                'required',
                'email',
                Rule::unique('admins', 'email')->ignore($admin->id),
            ],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        /** @var OtpToken|null $token */
        $token = OtpToken::where('id', $request->otp_token)
            ->where('context', 'email_change')
            ->first();

        if (
            !$token ||
            $token->admin_id !== $admin->id
        ) {
            return response()->json(['message' => 'OTP expired or invalid'], 410);
        }

        if ($token->email !== $request->new_email) {
            return response()->json(['message' => 'OTP was issued for a different email address'], 422);
        }

        if (!$otpService->verify($token, $request->code)) {
            return response()->json(['message' => 'Invalid or expired OTP'], 422);
        }

        $admin->update([
            'email' => $request->new_email,
        ]);

        $token->delete();

        return $this->respondWithToken($admin, 'Email updated successfully');
    }

    protected function respondWithToken(Admin $admin, string $message = 'Login successful')
    {
        $token = $this->issueJwt($admin);

        return response()->json([
            'message' => $message,
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $this->jwtConfig()['ttl'],
            'admin' => $admin->only(['id', 'name', 'email', 'role'])
        ]);
    }

    protected function issueJwt(Admin $admin): string
    {
        $config = $this->jwtConfig();

        $issuedAt = now();
        $expiresAt = $issuedAt->copy()->addSeconds($config['ttl']);

        $payload = [
            'iss' => config('app.url'),
            'sub' => $admin->id,
            'role' => $admin->role,
            'iat' => $issuedAt->timestamp,
            'exp' => $expiresAt->timestamp,
        ];

        return JWT::encode($payload, $config['secret'], $config['algo']);
    }

    protected function jwtConfig(): array
    {
        $config = config('admin.jwt');

        if (empty($config['secret'])) {
            throw new RuntimeException('Admin JWT secret is not configured.');
        }

        return [
            'secret' => $config['secret'],
            'ttl' => (int) ($config['ttl'] ?? 86400),
            'algo' => $config['algo'] ?? 'HS256',
        ];
    }
}
