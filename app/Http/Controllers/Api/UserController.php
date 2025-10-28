<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'role' => 'required|string|max:50',
            'status' => 'nullable|string|max:50',
            'otp' => 'nullable|string|max:10',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
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
        $data['password'] = Hash::make($data['password']);
        if (!isset($data['status']) || $data['status'] === null) {
            $data['status'] = '1';
        }

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $image = $request->file('profile_image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/profile_images', $imageName);
            $data['profile_image'] = 'storage/profile_images/' . $imageName;
        } else {
            $data['profile_image'] = null;
        }

        $user = User::create($data);

        return response()->json([
            'status' => 201,
            'success' => true,
            'message' => 'User created successfully',
            'data' => ['user' => $user]
        ], 201);
    }

    public function index()
    {
        $users = User::all();
        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Users retrieved successfully',
            'data' => ['users' => $users]
        ], 200);
    }

    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 404,
                'success' => false,
                'message' => 'User not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'User retrieved successfully',
            'data' => ['user' => $user]
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

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 404,
                'success' => false,
                'message' => 'User not found',
                'data' => null
            ], 404);
        }

        $user->status = $request->status;
        $user->save();

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'User status updated successfully',
            'data' => ['user' => $user]
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 404,
                'success' => false,
                'message' => 'User not found',
                'data' => null
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'mobile' => 'nullable|string|max:20',
            'password' => 'sometimes|string|min:6',
            'role' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:50',
            'otp' => 'nullable|string|max:10',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
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

        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $image = $request->file('profile_image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/profile_images', $imageName);
            $data['profile_image'] = 'storage/profile_images/' . $imageName;
        }

        $user->update($data);

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'User updated successfully',
            'data' => ['user' => $user]
        ], 200);
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 404,
                'success' => false,
                'message' => 'User not found',
                'data' => null
            ], 404);
        }

        $user->delete();

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'User deleted successfully',
            'data' => ['user_id' => $id]
        ], 200);
    }
}