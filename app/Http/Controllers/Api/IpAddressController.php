<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IpAddress;
use Illuminate\Support\Facades\Validator;

class IpAddressController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ip_address' => 'required|ip|unique:ip_addresses,ip_address',
            'status' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'security_status' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'success' => false,
                'message' => 'Validation failed',
                'data' => ['errors' => $validator->errors()]
            ], 422);
        }

        $data = $validator->validated();
        if (!isset($data['status']) || $data['status'] === null) {
            $data['status'] = '1';
        }

        $ipAddress = IpAddress::create($data);

        return response()->json([
            'status' => 201,
            'success' => true,
            'message' => 'IP Address added successfully',
            'data' => ['ip_address' => $ipAddress]
        ], 201);
    }

    public function index()
    {
        $ipAddresses = IpAddress::all();
        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'IP Addresses retrieved successfully',
            'data' => ['ip_addresses' => $ipAddresses]
        ], 200);
    }

    public function show($id)
    {
        $ipAddress = IpAddress::find($id);

        if (!$ipAddress) {
            return response()->json([
                'status' => 404,
                'success' => false,
                'message' => 'IP Address not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'IP Address retrieved successfully',
            'data' => ['ip_address' => $ipAddress]
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $ipAddress = IpAddress::find($id);

        if (!$ipAddress) {
            return response()->json([
                'status' => 404,
                'success' => false,
                'message' => 'IP Address not found',
                'data' => null
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'ip_address' => 'sometimes|required|ip|unique:ip_addresses,ip_address,' . $id,
            'status' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'security_status' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'success' => false,
                'message' => 'Validation failed',
                'data' => ['errors' => $validator->errors()]
            ], 422);
        }

        $data = $validator->validated();

        // If security_status is being updated, add timestamp
        if (isset($data['security_status'])) {
            $data['security_status_updated_at'] = now();
        }

        $ipAddress->update($data);

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'IP Address updated successfully',
            'data' => ['ip_address' => $ipAddress]
        ], 200);
    }

    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'success' => false,
                'message' => 'Validation failed',
                'data' => ['errors' => $validator->errors()]
            ], 422);
        }

        $ipAddress = IpAddress::find($id);

        if (!$ipAddress) {
            return response()->json([
                'status' => 404,
                'success' => false,
                'message' => 'IP Address not found',
                'data' => null
            ], 404);
        }

        $ipAddress->status = $request->status;
        $ipAddress->save();

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'IP Address status updated successfully',
            'data' => ['ip_address' => $ipAddress]
        ], 200);
    }

    public function destroy($id)
    {
        $ipAddress = IpAddress::find($id);

        if (!$ipAddress) {
            return response()->json([
                'status' => 404,
                'success' => false,
                'message' => 'IP Address not found',
                'data' => null
            ], 404);
        }

        $ipAddress->delete();

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'IP Address deleted successfully',
            'data' => ['ip_address_id' => $id]
        ], 200);
    }

    public function updateAllSecurityStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'security_status' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'success' => false,
                'message' => 'Validation failed',
                'data' => ['errors' => $validator->errors()]
            ], 422);
        }

        // Update security_status for all IP addresses
        $updatedAt = now();
        $updatedCount = IpAddress::query()->update([
            'security_status' => $request->security_status,
            'security_status_updated_at' => $updatedAt
        ]);

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Security status updated for all IP addresses successfully',
            'data' => [
                'security_status' => $request->security_status,
                'updated_count' => $updatedCount,
                'updated_at' => $updatedAt->format('Y-m-d H:i:s')
            ]
        ], 200);
    }

    public function getActiveIps()
    {
        $activeIps = IpAddress::where('status', '1')->get();
        $count = $activeIps->count();

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Active IP addresses retrieved successfully',
            'data' => [
                'active_ips' => $activeIps,
                'total_count' => $count
            ]
        ], 200);
    }

    public function getInactiveIps()
    {
        $inactiveIps = IpAddress::where('status', '!=', '1')->get();
        $count = $inactiveIps->count();

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Inactive IP addresses retrieved successfully',
            'data' => [
                'inactive_ips' => $inactiveIps,
                'total_count' => $count
            ]
        ], 200);
    }

    public function getIpsByStatus()
    {
        $activeIps = IpAddress::where('status', '1')->get();
        $inactiveIps = IpAddress::where('status', '!=', '1')->get();

        $activeCount = $activeIps->count();
        $inactiveCount = $inactiveIps->count();
        $totalCount = $activeCount + $inactiveCount;

        // Get the latest security status update timestamp
        $latestSecurityUpdate = IpAddress::whereNotNull('security_status_updated_at')
            ->orderBy('security_status_updated_at', 'desc')
            ->value('security_status_updated_at');

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'IP addresses grouped by status retrieved successfully',
            'data' => [
                'active_ips' => [
                    'count' => $activeCount,
                    'ips' => $activeIps
                ],
                'inactive_ips' => [
                    'count' => $inactiveCount,
                    'ips' => $inactiveIps
                ],
                'summary' => [
                    'total_ips' => $totalCount,
                    'active_count' => $activeCount,
                    'inactive_count' => $inactiveCount,
                    'security_status_last_updated_at' => $latestSecurityUpdate ?
                        \Carbon\Carbon::parse($latestSecurityUpdate)->format('Y-m-d H:i:s') : null
                ]
            ]
        ], 200);
    }
}
