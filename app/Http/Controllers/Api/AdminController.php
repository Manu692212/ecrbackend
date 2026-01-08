<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

        $token = $admin->createToken('admin-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'admin' => $admin->only(['id', 'name', 'email', 'role']),
            'token' => $token
        ]);
    }
}
