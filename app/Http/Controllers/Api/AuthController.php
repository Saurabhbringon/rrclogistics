<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\IpAddress;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Exception;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'success' => false,
                'message' => 'Validation failed',
                'data' => ['errors' => $validator->errors()]
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 401,
                'success' => false,
                'message' => 'Invalid credentials',
                'data' => null
            ], 401);
        }

        if ($user->status !== '1') {
            return response()->json([
                'status' => 403,
                'success' => false,
                'message' => 'Account is not active',
                'data' => null
            ], 403);
        }

        // Skip IP validation for admin users
        if (strtolower($user->role) === 'admin') {
            // Admin users bypass IP security - proceed directly to OTP generation
            // Generate OTP
            $otp = rand(100000, 999999);
            $user->otp = $otp;
            $user->save();

            // Send OTP via email
            try {
                Mail::raw("Your OTP for login is: $otp\n\nThis OTP will expire in 10 minutes.", function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('Login OTP - Admin Access');
                });

                return response()->json([
                    'status' => 200,
                    'success' => true,
                    'message' => 'OTP sent successfully to your email (Admin bypass)',
                    'data' => [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'role' => $user->role,
                        'otp_sent' => true,
                        'admin_bypass' => true
                    ]
                ], 200);

            } catch (Exception $e) {
                return response()->json([
                    'status' => 500,
                    'success' => false,
                    'message' => 'Failed to send OTP email',
                    'data' => ['error' => $e->getMessage()]
                ], 500);
            }
        }

        // Get user's IP address with better detection
        $userIp = $this->getRealClientIp($request);

        // Check if security status is active (check for both 'active' and '1' values)
        $securityActive = IpAddress::where('security_status', 'active')
            ->orWhere('security_status', '1')
            ->exists();

        if ($securityActive) {
            // Security is active - check if user's IP is in database and has status 1
            $ipRecord = $this->findIpRecord($userIp);

            if (!$ipRecord) {
                return response()->json([
                    'status' => 403,
                    'success' => false,
                    'message' => 'Your IP address is not authorized. Please contact administrator.',
                    'data' => ['user_ip' => $userIp]
                ], 403);
            }

            if ($ipRecord->status !== '1') {
                return response()->json([
                    'status' => 403,
                    'success' => false,
                    'message' => 'Your IP address is blocked. Access denied.',
                    'data' => ['user_ip' => $userIp]
                ], 403);
            }
        } else {
            // Security is not active - but check if user's IP exists and is blocked (status 0)
            $ipRecord = $this->findIpRecord($userIp);

            if ($ipRecord && $ipRecord->status === '0') {
                return response()->json([
                    'status' => 403,
                    'success' => false,
                    'message' => 'You are blocked. Access denied.',
                    'data' => ['user_ip' => $userIp]
                ], 403);
            }
        }

        // Generate OTP
        $otp = rand(100000, 999999);
        $user->otp = $otp;
        $user->save();

        // Send OTP via email
        try {
            Mail::raw("Your OTP for login is: $otp\n\nThis OTP will expire in 10 minutes.", function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Login OTP Verification');
            });

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'OTP sent to your email address',
                'data' => ['user_id' => $user->id]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to send OTP email',
                'data' => null
            ], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'success' => false,
                'message' => 'Validation failed',
                'data' => ['errors' => $validator->errors()]
            ], 422);
        }

        $user = User::find($request->user_id);

        if ($user->otp !== $request->otp) {
            return response()->json([
                'status' => 401,
                'success' => false,
                'message' => 'Invalid OTP',
                'data' => null
            ], 401);
        }

        // Clear OTP after successful verification
        $user->otp = null;
        $user->save();

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Login successful',
            'data' => ['user' => $user]
        ], 200);
    }

    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'success' => false,
                'message' => 'Validation failed',
                'data' => ['errors' => $validator->errors()]
            ], 422);
        }

        $user = User::find($request->user_id);

        // Generate new OTP
        $otp = rand(100000, 999999);
        $user->otp = $otp;
        $user->save();

        // Send OTP via email
        try {
            Mail::raw("Your new OTP for login is: $otp\n\nThis OTP will expire in 10 minutes.", function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Login OTP Verification - Resent');
            });

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'New OTP sent to your email address',
                'data' => ['user_id' => $user->id]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to send OTP email',
                'data' => null
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logout successful'], 200);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'success' => false,
                'message' => 'Validation failed',
                'data' => ['errors' => $validator->errors()]
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if ($user->status !== '1') {
            return response()->json([
                'status' => 403,
                'success' => false,
                'message' => 'Account is not active',
                'data' => null
            ], 403);
        }

        // Generate OTP for password reset
        $otp = rand(100000, 999999);
        $user->otp = $otp;
        $user->save();

        // Send OTP via email
        try {
            Mail::raw("Your OTP for password reset is: $otp\n\nThis OTP will expire in 10 minutes.\n\nIf you didn't request this, please ignore this email.", function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Password Reset OTP');
            });

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Password reset OTP sent to your email address',
                'data' => ['user_id' => $user->id]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to send OTP email',
                'data' => null
            ], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'otp' => 'required|string|size:6',
            'new_password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'success' => false,
                'message' => 'Validation failed',
                'data' => ['errors' => $validator->errors()]
            ], 422);
        }

        $user = User::find($request->user_id);

        if ($user->otp !== $request->otp) {
            return response()->json([
                'status' => 401,
                'success' => false,
                'message' => 'Invalid OTP',
                'data' => null
            ], 401);
        }

        // Update password and clear OTP
        $user->password = Hash::make($request->new_password);
        $user->otp = null;
        $user->save();

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Password reset successfully',
            'data' => ['user_id' => $user->id]
        ], 200);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'success' => false,
                'message' => 'Validation failed',
                'data' => ['errors' => $validator->errors()]
            ], 422);
        }

        $user = User::find($request->user_id);

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 401,
                'success' => false,
                'message' => 'Current password is incorrect',
                'data' => null
            ], 401);
        }

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Password changed successfully',
            'data' => ['user_id' => $user->id]
        ], 200);
    }

    public function checkCurrentIp(Request $request)
    {
        $userIp = $this->getRealClientIp($request);

        // Check if IP exists in database
        $ipRecord = $this->findIpRecord($userIp);

        // Check if security is active (check for both 'active' and '1' values)
        $securityActive = IpAddress::where('security_status', 'active')
            ->orWhere('security_status', '1')
            ->exists();

        // Get all IP records for debugging
        $allIpRecords = IpAddress::all();

        $data = [
            'current_ip' => $userIp,
            'security_active' => $securityActive,
            'ip_in_database' => $ipRecord ? true : false,
            'debug_info' => [
                'total_ip_records' => $allIpRecords->count(),
                'all_ip_records' => $allIpRecords->map(function ($ip) {
                    return [
                        'ip_address' => $ip->ip_address,
                        'status' => $ip->status,
                        'security_status' => $ip->security_status,
                        'description' => $ip->description
                    ];
                }),
                'login_decision' => null
            ]
        ];

        if ($ipRecord) {
            $data['ip_record'] = [
                'id' => $ipRecord->id,
                'ip_address' => $ipRecord->ip_address,
                'status' => $ipRecord->status,
                'description' => $ipRecord->description,
                'security_status' => $ipRecord->security_status,
                'security_status_updated_at' => $ipRecord->security_status_updated_at,
                'can_login' => $securityActive ? ($ipRecord->status === '1') : ($ipRecord->status !== '0'),
            ];
        }

        // Simulate login decision logic
        if ($securityActive) {
            if (!$ipRecord) {
                $data['debug_info']['login_decision'] = 'BLOCKED: Security active but IP not in database';
            } elseif ($ipRecord->status !== '1') {
                $data['debug_info']['login_decision'] = 'BLOCKED: Security active and IP status is not 1 (current: ' . $ipRecord->status . ')';
            } else {
                $data['debug_info']['login_decision'] = 'ALLOWED: Security active and IP authorized';
            }
        } else {
            if ($ipRecord && $ipRecord->status === '0') {
                $data['debug_info']['login_decision'] = 'BLOCKED: IP is specifically blocked';
            } else {
                $data['debug_info']['login_decision'] = 'ALLOWED: Security not active' . ($ipRecord ? ' and IP not blocked' : ' and IP not in database');
            }
        }

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Current IP information retrieved successfully',
            'data' => $data
        ], 200);
    }

    /**
     * Get real client IP address with better detection
     */
    private function getRealClientIp($request)
    {
        // Try Laravel's default method first
        $ip = $request->ip();

        // Check for forwarded IP headers
        $headers = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_CLIENT_IP',
            'HTTP_X_REAL_IP',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                // Handle comma-separated IPs (take the first one)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                break;
            }
        }

        return $ip;
    }

    /**
     * Find IP record in database, checking for common variations
     */
    private function findIpRecord($userIp)
    {
        // First try exact match
        $ipRecord = IpAddress::where('ip_address', $userIp)->first();

        if ($ipRecord) {
            return $ipRecord;
        }

        // For local development, also check common local IPs
        if ($userIp === '127.0.0.1' || $userIp === '::1') {
            $localIps = ['192.168.1.4', 'fe80::98c:448c:891f:71d9%14'];

            foreach ($localIps as $localIp) {
                $ipRecord = IpAddress::where('ip_address', $localIp)->first();
                if ($ipRecord) {
                    return $ipRecord;
                }
            }
        }

        return null;
    }
}