<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminAuthController {

    public function login(Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $admin = Admin::where('email', $request->email)->first();

    if (!$admin || !Hash::check($request->password, $admin->password)) {
        return response()->json(['message' => 'Invalid Credentials'], 401);
    }

    // Issue token with role info
    $token = $admin->createToken('admin-token', [$admin->getRoleNames()->first()])->plainTextToken;

    return response()->json([
        'admin' => $admin,
        'token' => $token,
        'role' => $admin->getRoleNames()
    ]);
    }
}