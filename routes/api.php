<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/admin/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $admin = Admin::where('email', $request->email)->first();

    if (! $admin || ! Hash::check($request->password, $admin->password)) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    // Create token with Spatie Role as a "ability"
    $token = $admin->createToken('admin-token', [$admin->getRoleNames()->first()])->plainTextToken;

    return response()->json([
        'token' => $token,
        'admin' => $admin,
        'role' => $admin->getRoleNames()->first()
    ]);
});