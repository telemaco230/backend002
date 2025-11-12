<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $prefix = __CLASS__ . '::' . __FUNCTION__ . ' -> ';
        Log::debug($prefix . " start");
        Log::debug($prefix . " request:\n" . json_encode($request->all(), JSON_PRETTY_PRINT));
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        try {
            $token = JWTAuth::fromUser($user);
        } catch (JWTException $e) {
            Log::error($prefix . " exception:\n" . $e->getMessage() . VarDumper::dumpAsString($request->all()));
            Log::error($prefix . " stop");
            return response()->json(['error' => 'Could not create token'], 500);
        }

        return response()->json([
            'token' => $token,
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $prefix = __CLASS__ . '::' . __FUNCTION__ . ' -> ';
        Log::debug($prefix . " start");
        Log::debug($prefix . " request:\n" . json_encode($request->all(), JSON_PRETTY_PRINT));

        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }
        } catch (JWTException $e) {
            Log::error($prefix . " exception:\n" . $e->getMessage());
            Log::error($prefix . " stop");
            return response()->json(['error' => 'Could not create token'], 500);
        }

        return response()->json([
            'token' => $token,
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ]);
    }

    public function logout()
    {
        $prefix = __CLASS__ . '::' . __FUNCTION__ . ' -> ';
        Log::debug($prefix . " start");
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (JWTException $e) {
            Log::error($prefix . " exception:\n" . $e->getMessage());
            Log::error($prefix . " stop");
            return response()->json(['error' => 'Failed to logout, please try again'], 500);
        }

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function getUser()
    {
        $prefix = __CLASS__ . '::' . __FUNCTION__ . ' -> ';
        Log::debug($prefix . " start");
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }
            return response()->json($user);
        } catch (JWTException $e) {
        Log::debug($prefix . " stop");
            Log::error($prefix . " exception:\n" . $e->getMessage());
            Log::error($prefix . " stop");
            return response()->json(['error' => 'Failed to fetch user profile'], 500);
        }
    }

    public function updateUser(Request $request)
    {
        $prefix = __CLASS__ . '::' . __FUNCTION__ . ' -> ';
        Log::debug($prefix . " start");
        Log::debug($prefix . " request:\n" . json_encode($request->all(), JSON_PRETTY_PRINT));
        try {
            $user = Auth::user();
            $user->update($request->only(['name', 'email']));
            Log::debug($prefix . " User updated succe");
            Log::debug($prefix . " stop");
            return response()->json($user);
        } catch (JWTException $e) {
            Log::error($prefix . " exception:\n" . $e->getMessage() . VarDumper::dumpAsString($request->all()));
            Log::error($prefix . " stop");
            return response()->json(['error' => 'Failed to update user'], 500);
        }
    }
}